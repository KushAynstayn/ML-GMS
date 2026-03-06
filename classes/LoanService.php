<?php
namespace Cadc20239999\MlGms;

use Exception;
use DateTime;

class LoanService {

    private $db;

    public function __construct() {
        $this->db = (new Database())->connect('LOAN');
    }

    /* ============================================================
       SAVE MANUAL RECORD
    ============================================================ */
    public function saveManualRecord($data) {
        try {
            $this->db->beginTransaction();

            $creatorFirstName = $_SESSION['first_name'] ?? 'System';
            $creatorLastName  = $_SESSION['last_name'] ?? 'User';
            $currentUserName  = strtoupper("{$creatorLastName}, {$creatorFirstName}");

            // Detect GMS (if date_installed exists)
            $isGMS = !empty($data['date_installed']);

            $principal = round((float)$data['principal'], 2);

            $dealerIncentive = $isGMS 
                ? round($principal * 0.05, 2) 
                : 0.00;

            $netProceeds = $isGMS 
                ? round($principal - $dealerIncentive, 2) 
                : $principal;
            $eir = $isGMS ? (float)$data['eir'] : null;


            $secondaryMonthly = $isGMS
                ? round((float)($data['secondary_monthly'] ?? 0), 2)
                : null;

            /* ============================
               INSERT INTO LOANS
            ============================ */
            $sqlLoan = "INSERT INTO loans (
                reference_number,
                loan_type_id,
                first_name,
                middle_name,
                last_name,
                contact_number,
                pn_date,
                pn_maturity_date,
                principal_amount,
                term_months,
                dealer_incentive,
                net_proceeds,
                interest_rate,
                eir,
                monthly_amortization,
                secondary_monthly,
                region_name,
                source,
                status,
                date_created,
                created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'manual', 'active', NOW(), ?)";

            $stmt = $this->db->prepare($sqlLoan);
            $stmt->execute([
                $data['ref_no'],
                $data['loan_type_id'],
                strtoupper($data['first_name']),
                strtoupper($data['middle_name'] ?? ''),
                strtoupper($data['last_name']),
                $data['contact'],
                $data['date_granted'],
                $data['maturity_date'],
                $principal,
                $data['term'],
                $dealerIncentive,
                $netProceeds,
                $data['aor'],
                $eir,
                $data['monthly_amortization'],
                $secondaryMonthly,
                $data['region_name'] ?? '',
                $currentUserName
            ]);

            $loanId = $this->db->lastInsertId();

            /* ============================
               INSERT VEHICLE TABLE
            ============================ */
            if ($data['loan_type_text'] === 'MOTOR LOAN') {
                $stmtMotor = $this->db->prepare("
                    INSERT INTO motor_loans (loan_id, type, date_installed, gps_provider, date_created)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmtMotor->execute([
                    $loanId,
                    $data['vehicle_type'],
                    $data['date_installed'] ?: null,
                    $isGMS ? 'GMS' : null
                ]);
            }

            if ($data['loan_type_text'] === 'CAR LOAN') {
                $stmtCar = $this->db->prepare("
                    INSERT INTO car_loans (loan_id, date_installed, gps_provider, date_created)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmtCar->execute([
                    $loanId,
                    $data['date_installed'] ?: null,
                    $isGMS ? 'GMS' : null
                ]);
            }

            /* ============================
               GENERATE PRIMARY LEDGER
            ============================ */
            $this->generatePrimaryLedger($loanId, $data);

            /* ============================
               GENERATE SECONDARY (IF GMS)
            ============================ */
            if ($isGMS) {
                $this->generateSecondaryLedger($loanId, [
                    'net_proceeds'       => $netProceeds,
                    'secondary_monthly'  => $secondaryMonthly,
                    'term'               => $data['term'],
                    'date_granted'       => $data['date_granted']
                ]);
            }

            $this->db->commit();

            return ['status' => 'success', 'loan_id' => $loanId];

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /* ============================================================
    PRIMARY LEDGER (AMORTIZATION MATCHING EXCEL)
    ============================================================ */
    private function generatePrimaryLedger($loanId, $data) {
        $balance = round((float)$data['principal'], 2);
        $monthly = round((float)$data['monthly_amortization'], 2);
        $term = (int)$data['term']; // e.g., 48, 36, 24
        $startDate = new DateTime($data['date_granted']);
        
        // To match your Excel, the rate must be applied to the REMAINING balance
        $monthlyRate = 0.02; 

        for ($i = 1; $i <= $term; $i++) {
            $startDate->modify('+1 month');

            // If balance is already 0 from previous overpayment, everything else is 0
            if ($balance <= 0) {
                $interest = 0.00;
                $principal = 0.00;
                $ending_balance = 0.00;
            } else {
                // 1. Interest is calculated on current balance (decreases over time)
                $interest = round($balance * $monthlyRate, 2);
                
                // 2. Principal is the rest of the fixed monthly payment (increases over time)
                $principal = round($monthly - $interest, 2);

                // 3. Final Month or Early Payoff Check
                if ($i === $term || $principal >= $balance) {
                    $principal = $balance; 
                    $ending_balance = 0.00;
                    // Re-calculate interest to keep the 'Total Amount' consistent in the final row
                    $interest = round($monthly - $principal, 2);
                    if ($interest < 0) $interest = 0.00;
                } else {
                    $ending_balance = round($balance - $principal, 2);
                }
            }

            $this->saveToLedger('primary_ledger', $loanId, $i, $startDate->format('Y-m-d'), $balance, $principal, $interest, $ending_balance);

            $balance = $ending_balance;
        }
    }

    /* ============================================================
    SECONDARY LEDGER (AMORTIZATION MATCHING EXCEL)
    ============================================================ */
    private function generateSecondaryLedger($loanId, $data) {

        $balance = round((float)$data['net_proceeds'], 2);
        $monthly = round((float)$data['secondary_monthly'], 2);
        $term = (int)$data['term'];
        $startDate = new DateTime($data['date_granted']);

        $monthlyRate = $this->getEIRRate(
            $term,
            -$monthly,
            $balance
        );

        for ($i = 1; $i <= $term; $i++) {

            $startDate->modify('+1 month');

            if ($balance <= 0) {

                $interest = 0.00;
                $principal = 0.00;
                $ending_balance = 0.00;

            } else {

                $interest = round($balance * $monthlyRate, 2);

                $principal = round($monthly - $interest, 2);

                if ($i === $term || $principal >= $balance) {

                    $principal = $balance;
                    $ending_balance = 0.00;

                    $interest = round($monthly - $principal, 2);

                    if ($interest < 0) {
                        $interest = 0.00;
                    }

                } else {

                    $ending_balance = round($balance - $principal, 2);

                }
            }

            $this->saveToLedger(
                'secondary_ledger',
                $loanId,
                $i,
                $startDate->format('Y-m-d'),
                $balance,
                $principal,
                $interest,
                $ending_balance
            );

            $balance = $ending_balance;
        }
    }

    private function saveToLedger($table, $loanId, $instNo, $dueDate, $begBal, $principal, $interest, $endBal) {
        $sql = "INSERT INTO $table 
                (loan_id, installment_no, due_date, beginning_balance, principal, interest, ending_balance, status, date_created) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'unpaid', NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$loanId, $instNo, $dueDate, $begBal, $principal, $interest, $endBal]);
    }


    public function saveImportedRecord($data, $amortizationRows): array{
        try {
            $this->db->beginTransaction();

            $creatorFirstName = $_SESSION['first_name'] ?? 'System';
            $creatorLastName  = $_SESSION['last_name'] ?? 'User';
            $currentUserName  = strtoupper("{$creatorLastName}, {$creatorFirstName}");

            // Detect GMS tagging from Excel
            $isGMS = !empty($data['gms']) || !empty($data['date_installed']);

            /* ============================================================
            SANITIZE NUMERIC VALUES
            ============================================================ */

            $principal = round((float)$data['principal_amount'], 2);
            $term      = (int)$data['term_months'];
            $monthly   = round((float)$data['monthly_amortization'], 2);

            /* ============================================================
            DEALER INCENTIVE
            If not provided in Excel, compute 5%
            ============================================================ */

            if ($isGMS) {

                if (!empty($data['dealer_incentive'])) {
                    $dealerIncentive = round((float)$data['dealer_incentive'], 2);
                } else {
                    $dealerIncentive = round($principal * 0.05, 2);
                }

            } else {
                $dealerIncentive = 0.00;
            }

            /* ============================================================
            NET PROCEEDS
            ============================================================ */

            $netProceeds = $isGMS
                ? round($principal - $dealerIncentive, 2)
                : null;

            /* ============================================================
            SECONDARY MONTHLY (IF GMS)
            Formula used in ML computation:
            (((net proceeds * 2%) * term) + net proceeds) / term
            ============================================================ */

            if ($isGMS) {

                if (!empty($data['secondary_monthly'])) {

                    $secondaryMonthly = round((float)$data['secondary_monthly'], 2);

                } else {

                    $interestPerMonth = $netProceeds * 0.02;
                    $totalInterest = $interestPerMonth * $term;

                    $secondaryMonthly = round(
                        ($netProceeds + $totalInterest) / $term,
                        2
                    );
                }

            } else {

                $secondaryMonthly = null;

            }

            /* ============================================================
            INTEREST RATE
            ============================================================ */

            $interestRate = isset($data['interest_rate'])
                ? round((float)$data['interest_rate'], 6)
                : 0.00;

            /* ============================================================
            EIR CALCULATION (using Newton-Raphson solver)
            ============================================================ */

            if ($isGMS) {

                if (!empty($data['eir'])) {

                    $eir = round((float)$data['eir'], 6);

                } else {

                    $monthlyRate = $this->getEIRRate(
                        $term,
                        -$secondaryMonthly,
                        $netProceeds
                    );

                    $eir = round($monthlyRate, 10);

                }

            } else {

                $eir = null;

            }

            /* ============================================================
            SAVE SECONDARY MONTHLY BACK TO DATA
            ============================================================ */

            $data['secondary_monthly'] = $secondaryMonthly;

            /* ============================================================
            INSERT INTO LOANS
            ============================================================ */

            $sqlLoan = "INSERT INTO loans (
                reference_number,
                loan_type_id,
                first_name,
                middle_name,
                last_name,
                contact_number,
                pn_date,
                pn_maturity_date,
                principal_amount,
                term_months,
                dealer_incentive,
                net_proceeds,
                interest_rate,
                eir,
                monthly_amortization,
                secondary_monthly,
                region_name,
                source,
                status,
                date_created,
                created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'import', 'active', NOW(), ?)";

            $stmt = $this->db->prepare($sqlLoan);
            $stmt->execute([
                $data['reference_number'],
                $data['loan_type_id'],
                strtoupper($data['first_name']),
                strtoupper($data['middle_name'] ?? ''),
                strtoupper($data['last_name']),
                $data['contact_number'],
                $data['pn_date'],
                $data['pn_maturity_date'],
                $principal,
                $term,
                $dealerIncentive,
                $netProceeds,
                $interestRate,
                $eir,
                $monthly,
                $secondaryMonthly,
                $data['region_name'] ?? '',
                $currentUserName
            ]);

            $loanId = $this->db->lastInsertId();

            /* ============================================================
            INSERT VEHICLE TABLES
            ============================================================ */

            if ($data['loan_type_text'] === 'MOTOR LOAN') {

                $stmtMotor = $this->db->prepare("
                    INSERT INTO motor_loans
                    (loan_id, type, date_installed, gps_provider, date_created)
                    VALUES (?, ?, ?, ?, NOW())
                ");

                $stmtMotor->execute([
                    $loanId,
                    $data['vehicle_type'] ?? null,
                    $data['date_installed'] ?? null,
                    $isGMS ? 'GMS' : null
                ]);
            }

            if ($data['loan_type_text'] === 'CAR LOAN') {

                $stmtCar = $this->db->prepare("
                    INSERT INTO car_loans
                    (loan_id, date_installed, gps_provider, date_created)
                    VALUES (?, ?, ?, NOW())
                ");

                $stmtCar->execute([
                    $loanId,
                    $data['date_installed'] ?? null,
                    $isGMS ? 'GMS' : null
                ]);
            }

            /* ============================================================
            INSERT PRIMARY LEDGER (FROM EXCEL — NO RECOMPUTE)
            ============================================================ */

            foreach ($amortizationRows as $row) {

                $installmentNo     = (int)$row['payment_number'];
                $dueDate           = $row['due_date'];
                $beginningBalance  = round((float)$row['beginning_balance'], 2);
                $principalPayment  = round((float)$row['principal'], 2);
                $interestPayment   = round((float)$row['interest'], 2);
                $endingBalance     = round((float)$row['ending_balance'], 2);
                $status            = strtolower($row['status'] ?? 'unpaid');

                $stmtPrimary = $this->db->prepare("
                    INSERT INTO primary_ledger
                    (loan_id, installment_no, due_date, beginning_balance,
                    principal, interest, ending_balance, status, date_created)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");

                $stmtPrimary->execute([
                    $loanId,
                    $installmentNo,
                    $dueDate,
                    $beginningBalance,
                    $principalPayment,
                    $interestPayment,
                    $endingBalance,
                    $status
                ]);
            }

            /* ============================================================
            GENERATE SECONDARY LEDGER (ONLY IF GMS)
            THIS IS CALCULATED SERVER-SIDE
            ============================================================ */

            if ($isGMS) {

                $this->generateSecondaryLedger($loanId, [
                    'net_proceeds'        => $netProceeds,
                    'secondary_monthly'=> $data['secondary_monthly'] ?? $monthly,
                    'term'                => $term,
                    'date_granted'        => $data['pn_date']
                ]);
            }

            $this->db->commit();

            return [
                'status' => 'success',
                'loan_id' => $loanId
            ];

        } catch (Exception $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        } 
    }


    private function getEIRRate($nper, $pmt, $pv, $guess = 0.01){
        $tol = 1e-10;
        $maxIter = 100;
        $rate = $guess;

        for ($i = 0; $i < $maxIter; $i++) {

            $f = $pv;
            $df = 0;

            for ($t = 1; $t <= $nper; $t++) {

                $f += $pmt / pow(1 + $rate, $t);
                $df += -$t * $pmt / pow(1 + $rate, $t + 1);

            }

            $newRate = $rate - $f / $df;

            if (abs($newRate - $rate) < $tol) {
                return $newRate;
            }

            $rate = $newRate;
        }

        return $rate;
    }
}


