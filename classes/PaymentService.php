<?php

namespace Cadc20239999\MlGms;

use PDO;
use Exception;

class PaymentService
{
    private PDO $db;

    public function __construct()
    {
        $database = new Database();
        $conn = $database->connect('LOAN');

        if (!$conn instanceof PDO) {
            throw new Exception('Unable to connect to LOAN database.');
        }

        $this->db = $conn;
    }

    public function processImportedPayment(int $paymentId): array
    {
        $payment = $this->getPaymentById($paymentId);

        if (!$payment) {
            throw new Exception("Payment ID {$paymentId} not found.");
        }

        $this->db->beginTransaction();

        try {
            $loan = $this->matchLoan(
                (string)($payment['reference_number'] ?? ''),
                (string)($payment['borrower_name'] ?? '')
            );

            if (!$loan) {
                $this->markPaymentAsUnmatched($paymentId, 'No matching loan found.');
                $this->db->commit();

                return [
                    'status' => 'unmatched',
                    'message' => 'No matching loan found.'
                ];
            }

            $loanId = (int)$loan['loan_id'];

            $this->assignPaymentToLoan($paymentId, $loanId);

            $amountForSchedule = $this->getAmountAvailableForSchedule($payment);

            if ($amountForSchedule <= 0) {
                $this->updatePaymentApplicationStatus(
                    $paymentId,
                    'unapplied',
                    0.00,
                    'Payment saved but no allocatable principal/interest amount found.'
                );

                $this->db->commit();

                return [
                    'status' => 'unapplied',
                    'message' => 'Payment has no allocatable amount.'
                ];
            }

            $primaryResult = $this->applyToPrimaryLedger($payment, $loanId, $amountForSchedule);

            if ($this->hasSecondaryLedger($loanId) && !empty($primaryResult['primary_allocations'])) {
                $this->mirrorStatusesToSecondaryLedger($payment, $loanId, $primaryResult['primary_allocations']);
            }

            $remaining = (float)$primaryResult['remaining_amount'];

            $applicationStatus = 'fully_applied';
            if ((float)$primaryResult['applied_amount'] <= 0) {
                $applicationStatus = 'unapplied';
            } elseif ($remaining > 0.0001) {
                $applicationStatus = 'partially_applied';
            }

            $remarks = $remaining > 0.0001
                ? 'Payment applied with excess/unallocated amount remaining.'
                : 'Payment applied successfully.';

            $this->updatePaymentApplicationStatus(
                $paymentId,
                $applicationStatus,
                round($remaining, 2),
                $remarks
            );

            $this->db->commit();

            return [
                'status' => $applicationStatus,
                'message' => $remarks,
                'loan_id' => $loanId,
                'applied_amount' => round((float)$primaryResult['applied_amount'], 2),
                'remaining_amount' => round($remaining, 2)
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function matchLoan(string $referenceNumber, string $borrowerName = ''): ?array
    {
        $referenceNumber = trim($referenceNumber);
        $borrowerName = $this->normalizeName($borrowerName);

        if ($referenceNumber !== '') {
            $stmt = $this->db->prepare("
                SELECT
                    loan_id,
                    reference_number,
                    first_name,
                    middle_name,
                    last_name,
                    monthly_amortization,
                    secondary_monthly,
                    status
                FROM loans
                WHERE reference_number = :reference_number
                LIMIT 2
            ");
            $stmt->execute([
                ':reference_number' => $referenceNumber
            ]);

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) === 1) {
                if ($borrowerName !== '') {
                    $dbName = $this->buildNormalizedLoanName($rows[0]);

                    if ($dbName !== '' && $dbName !== $borrowerName) {
                        return null;
                    }
                }

                return $rows[0];
            }

            if (count($rows) > 1) {
                return null;
            }
        }

        if ($borrowerName !== '') {
            $stmt = $this->db->query("
                SELECT
                    loan_id,
                    reference_number,
                    first_name,
                    middle_name,
                    last_name,
                    monthly_amortization,
                    secondary_monthly,
                    status
                FROM loans
            ");

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $matched = [];
            foreach ($rows as $row) {
                $dbName = $this->buildNormalizedLoanName($row);

                if ($dbName === $borrowerName) {
                    $matched[] = $row;
                }
            }

            if (count($matched) === 1) {
                return $matched[0];
            }

            if (count($matched) > 1) {
                return null;
            }
        }

        return null;
    }

    private function getPaymentById(int $paymentId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM payments
            WHERE payment_id = :payment_id
            LIMIT 1
        ");
        $stmt->execute([':payment_id' => $paymentId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function assignPaymentToLoan(int $paymentId, int $loanId): void
    {
        $stmt = $this->db->prepare("
            UPDATE payments
            SET loan_id = :loan_id,
                match_status = 'matched'
            WHERE payment_id = :payment_id
        ");
        $stmt->execute([
            ':loan_id' => $loanId,
            ':payment_id' => $paymentId
        ]);
    }

    private function markPaymentAsUnmatched(int $paymentId, string $remarks = ''): void
    {
        $stmt = $this->db->prepare("
            UPDATE payments
            SET loan_id = NULL,
                match_status = 'unmatched',
                application_status = 'unapplied',
                remarks = :remarks
            WHERE payment_id = :payment_id
        ");
        $stmt->execute([
            ':remarks' => $remarks,
            ':payment_id' => $paymentId
        ]);
    }

    private function updatePaymentApplicationStatus(
        int $paymentId,
        string $applicationStatus,
        float $excessAmount,
        string $remarks = ''
    ): void {
        $stmt = $this->db->prepare("
            UPDATE payments
            SET application_status = :application_status,
                excess_amount = :excess_amount,
                remarks = :remarks
            WHERE payment_id = :payment_id
        ");
        $stmt->execute([
            ':application_status' => $applicationStatus,
            ':excess_amount' => round($excessAmount, 2),
            ':remarks' => $remarks,
            ':payment_id' => $paymentId
        ]);
    }

    private function getAmountAvailableForSchedule(array $payment): float
    {
        $principal = (float)($payment['principal'] ?? 0);
        $interest = (float)($payment['interest'] ?? 0);
        $total = (float)($payment['total'] ?? 0);

        $allocatable = round($principal + $interest, 2);

        if ($allocatable <= 0 && $total > 0) {
            $allocatable = round($total, 2);
        }

        return $allocatable;
    }

    private function applyToPrimaryLedger(array $payment, int $loanId, float $amountAvailable): array
    {
        $paymentId = (int)$payment['payment_id'];
        $paymentDate = !empty($payment['date_of_transaction'])
            ? $payment['date_of_transaction']
            : ($payment['payment_date'] ?? date('Y-m-d'));

        $rows = $this->getOpenPrimaryLedgerRows($loanId);

        if (empty($rows)) {
            return [
                'applied_amount' => 0.00,
                'remaining_amount' => $amountAvailable,
                'primary_allocations' => []
            ];
        }

        $remaining = round($amountAvailable, 2);
        $appliedTotal = 0.00;
        $allocations = [];

        foreach ($rows as $row) {
            if ($remaining <= 0) {
                break;
            }

            $primaryLedgerId = (int)$row['primary_ledger_id'];
            $scheduledAmount = round((float)$row['amount_due'], 2);
            $alreadyPaid = round((float)$row['amount_paid'], 2);
            $remainingDue = round((float)$row['remaining_due'], 2);

            if ($remainingDue <= 0) {
                continue;
            }

            $before = $remainingDue;
            $apply = min($remaining, $remainingDue);
            $after = round($remainingDue - $apply, 2);
            $newAmountPaid = round($alreadyPaid + $apply, 2);

            $newStatus = 'partial';
            if ($after <= 0.0001) {
                $after = 0.00;
                $newStatus = 'paid';
            }

            $this->updatePrimaryLedgerRow(
                $primaryLedgerId,
                $newAmountPaid,
                $after,
                $newStatus,
                $paymentDate,
                $paymentId
            );

            [$appliedPrincipal, $appliedInterest] = $this->splitAppliedAmountProportionally(
                (float)$row['principal'],
                (float)$row['interest'],
                $scheduledAmount,
                $apply
            );

            $this->insertPaymentAllocation([
                'payment_id' => $paymentId,
                'loan_id' => $loanId,
                'ledger_type' => 'primary',
                'primary_ledger_id' => $primaryLedgerId,
                'secondary_ledger_id' => null,
                'installment_no' => (int)$row['installment_no'],
                'due_date' => $row['due_date'],
                'scheduled_amount' => $scheduledAmount,
                'amount_before_allocation' => $before,
                'amount_applied' => $apply,
                'applied_principal' => $appliedPrincipal,
                'applied_interest' => $appliedInterest,
                'applied_penalty' => 0.00,
                'applied_vat' => 0.00,
                'applied_interest_diff' => 0.00,
                'amount_after_allocation' => $after,
                'allocation_status' => $newStatus === 'paid' ? 'full' : 'partial',
            ]);

            $allocations[] = [
                'installment_no' => (int)$row['installment_no'],
                'due_date' => $row['due_date'],
                'status' => $newStatus,
                'paid_date' => $paymentDate,
                'payment_id' => $paymentId,
            ];

            $remaining = round($remaining - $apply, 2);
            $appliedTotal = round($appliedTotal + $apply, 2);
        }

        return [
            'applied_amount' => $appliedTotal,
            'remaining_amount' => $remaining,
            'primary_allocations' => $allocations
        ];
    }

    /**
     * Mirror-only behavior for GMS secondary ledger.
     * The secondary ledger does NOT receive an independent monetary allocation.
     * It only mirrors the status of the same installment in primary.
     */
    private function mirrorStatusesToSecondaryLedger(array $payment, int $loanId, array $primaryAllocations): void
    {
        $paymentId = (int)$payment['payment_id'];
        $paymentDate = !empty($payment['date_of_transaction'])
            ? $payment['date_of_transaction']
            : ($payment['payment_date'] ?? date('Y-m-d'));

        foreach ($primaryAllocations as $allocation) {
            $secondaryRow = $this->getSecondaryRowByInstallmentNo($loanId, (int)$allocation['installment_no']);

            if (!$secondaryRow) {
                continue;
            }

            $secondaryLedgerId = (int)$secondaryRow['secondary_ledger_id'];
            $scheduledAmount = round((float)$secondaryRow['amount_due'], 2);
            $status = $allocation['status'];

            $mirroredAmountPaid = 0.00;
            $mirroredRemainingDue = $scheduledAmount;

            if ($status === 'paid') {
                $mirroredAmountPaid = $scheduledAmount;
                $mirroredRemainingDue = 0.00;
            } elseif ($status === 'partial') {
                // For mirror-only behavior, partial in primary means partial in secondary.
                // We keep a light mirrored state without treating this as an independent cash allocation.
                $existingAmountPaid = round((float)($secondaryRow['amount_paid'] ?? 0), 2);
                $existingRemainingDue = round((float)($secondaryRow['remaining_due'] ?? $scheduledAmount), 2);

                // Preserve current partial tracking if already present, otherwise set a minimal partial state.
                if ($existingAmountPaid > 0 && $existingRemainingDue > 0 && $existingRemainingDue < $scheduledAmount) {
                    $mirroredAmountPaid = $existingAmountPaid;
                    $mirroredRemainingDue = $existingRemainingDue;
                } else {
                    $mirroredAmountPaid = 0.01;
                    $mirroredRemainingDue = max(round($scheduledAmount - 0.01, 2), 0.00);
                }
            }

            $this->updateSecondaryLedgerRow(
                $secondaryLedgerId,
                $mirroredAmountPaid,
                $mirroredRemainingDue,
                $status,
                $paymentDate,
                $paymentId
            );

            $this->insertPaymentAllocation([
                'payment_id' => $paymentId,
                'loan_id' => $loanId,
                'ledger_type' => 'secondary',
                'primary_ledger_id' => null,
                'secondary_ledger_id' => $secondaryLedgerId,
                'installment_no' => (int)$secondaryRow['installment_no'],
                'due_date' => $secondaryRow['due_date'],
                'scheduled_amount' => $scheduledAmount,
                'amount_before_allocation' => $scheduledAmount,
                'amount_applied' => 0.00,
                'applied_principal' => 0.00,
                'applied_interest' => 0.00,
                'applied_penalty' => 0.00,
                'applied_vat' => 0.00,
                'applied_interest_diff' => 0.00,
                'amount_after_allocation' => $mirroredRemainingDue,
                'allocation_status' => $status === 'paid' ? 'full' : 'partial',
            ]);
        }
    }

    private function getOpenPrimaryLedgerRows(int $loanId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                primary_ledger_id,
                loan_id,
                installment_no,
                due_date,
                principal,
                interest,
                amount_due,
                amount_paid,
                remaining_due,
                status
            FROM primary_ledger
            WHERE loan_id = :loan_id
              AND status IN ('unpaid', 'partial')
            ORDER BY installment_no ASC
        ");
        $stmt->execute([':loan_id' => $loanId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getSecondaryRowByInstallmentNo(int $loanId, int $installmentNo): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                secondary_ledger_id,
                loan_id,
                installment_no,
                due_date,
                principal,
                interest,
                amount_due,
                amount_paid,
                remaining_due,
                status
            FROM secondary_ledger
            WHERE loan_id = :loan_id
              AND installment_no = :installment_no
            LIMIT 1
        ");
        $stmt->execute([
            ':loan_id' => $loanId,
            ':installment_no' => $installmentNo
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function hasSecondaryLedger(int $loanId): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM secondary_ledger
            WHERE loan_id = :loan_id
            LIMIT 1
        ");
        $stmt->execute([':loan_id' => $loanId]);

        return (bool)$stmt->fetchColumn();
    }

    private function updatePrimaryLedgerRow(
        int $primaryLedgerId,
        float $amountPaid,
        float $remainingDue,
        string $status,
        string $paidDate,
        int $paymentId
    ): void {
        $stmt = $this->db->prepare("
            UPDATE primary_ledger
            SET amount_paid = :amount_paid,
                remaining_due = :remaining_due,
                status = :status,
                paid_date = :paid_date,
                last_payment_id = :last_payment_id,
                modified_date = NOW()
            WHERE primary_ledger_id = :primary_ledger_id
        ");
        $stmt->execute([
            ':amount_paid' => round($amountPaid, 2),
            ':remaining_due' => round($remainingDue, 2),
            ':status' => $status,
            ':paid_date' => $paidDate,
            ':last_payment_id' => $paymentId,
            ':primary_ledger_id' => $primaryLedgerId
        ]);
    }

    private function updateSecondaryLedgerRow(
        int $secondaryLedgerId,
        float $amountPaid,
        float $remainingDue,
        string $status,
        string $paidDate,
        int $paymentId
    ): void {
        $stmt = $this->db->prepare("
            UPDATE secondary_ledger
            SET amount_paid = :amount_paid,
                remaining_due = :remaining_due,
                status = :status,
                paid_date = :paid_date,
                last_payment_id = :last_payment_id,
                modified_date = NOW()
            WHERE secondary_ledger_id = :secondary_ledger_id
        ");
        $stmt->execute([
            ':amount_paid' => round($amountPaid, 2),
            ':remaining_due' => round($remainingDue, 2),
            ':status' => $status,
            ':paid_date' => $paidDate,
            ':last_payment_id' => $paymentId,
            ':secondary_ledger_id' => $secondaryLedgerId
        ]);
    }

    private function insertPaymentAllocation(array $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO payment_allocations (
                payment_id,
                loan_id,
                ledger_type,
                primary_ledger_id,
                secondary_ledger_id,
                installment_no,
                due_date,
                scheduled_amount,
                amount_before_allocation,
                amount_applied,
                applied_principal,
                applied_interest,
                applied_penalty,
                applied_vat,
                applied_interest_diff,
                amount_after_allocation,
                allocation_status,
                created_at
            ) VALUES (
                :payment_id,
                :loan_id,
                :ledger_type,
                :primary_ledger_id,
                :secondary_ledger_id,
                :installment_no,
                :due_date,
                :scheduled_amount,
                :amount_before_allocation,
                :amount_applied,
                :applied_principal,
                :applied_interest,
                :applied_penalty,
                :applied_vat,
                :applied_interest_diff,
                :amount_after_allocation,
                :allocation_status,
                NOW()
            )
        ");
        $stmt->execute([
            ':payment_id' => $data['payment_id'],
            ':loan_id' => $data['loan_id'],
            ':ledger_type' => $data['ledger_type'],
            ':primary_ledger_id' => $data['primary_ledger_id'],
            ':secondary_ledger_id' => $data['secondary_ledger_id'],
            ':installment_no' => $data['installment_no'],
            ':due_date' => $data['due_date'],
            ':scheduled_amount' => round((float)$data['scheduled_amount'], 2),
            ':amount_before_allocation' => round((float)$data['amount_before_allocation'], 2),
            ':amount_applied' => round((float)$data['amount_applied'], 2),
            ':applied_principal' => round((float)$data['applied_principal'], 2),
            ':applied_interest' => round((float)$data['applied_interest'], 2),
            ':applied_penalty' => round((float)$data['applied_penalty'], 2),
            ':applied_vat' => round((float)$data['applied_vat'], 2),
            ':applied_interest_diff' => round((float)$data['applied_interest_diff'], 2),
            ':amount_after_allocation' => round((float)$data['amount_after_allocation'], 2),
            ':allocation_status' => $data['allocation_status'],
        ]);
    }

    private function splitAppliedAmountProportionally(
        float $scheduledPrincipal,
        float $scheduledInterest,
        float $scheduledAmount,
        float $appliedAmount
    ): array {
        if ($scheduledAmount <= 0 || $appliedAmount <= 0) {
            return [0.00, 0.00];
        }

        $principalRatio = $scheduledPrincipal / $scheduledAmount;
        $appliedPrincipal = round($appliedAmount * $principalRatio, 2);
        $appliedInterest = round($appliedAmount - $appliedPrincipal, 2);

        return [$appliedPrincipal, $appliedInterest];
    }

    private function buildNormalizedLoanName(array $loanRow): string
    {
        $parts = [
            $loanRow['first_name'] ?? '',
            $loanRow['middle_name'] ?? '',
            $loanRow['last_name'] ?? '',
        ];

        $fullName = implode(' ', array_filter(array_map('trim', $parts), fn($v) => $v !== ''));

        return $this->normalizeName($fullName);
    }

    private function normalizeName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name);

        return strtoupper($name);
    }
}