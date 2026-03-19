<?php
namespace Cadc20239999\MlGms;

use Exception;
use DateTime;

class LoanService {

    private $db;
    private $masterDb;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect('LOAN');
        $this->masterDb = $database->connect('MASTER');
    }

    private function getRegionNameById($regionId) {
        if (empty($regionId)) {
            return '';
        }

        $stmt = $this->masterDb->prepare("
            SELECT region_description 
            FROM region_masterfile 
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$regionId]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row['region_description'] ?? '';
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
            $term = (int)$data['term'];
            $aor = (float)$data['aor'];

            $monthlyAmortization = ($principal > 0 && $term > 0 && $aor > 0)
                ? (float) ceil(($principal * (($aor * 0.01) + 1)) / $term)
                : 0.00;

            $dealerIncentive = $isGMS 
                ? round($principal * 0.05, 2) 
                : 0.00;

            $netProceeds = $isGMS 
                ? round($principal - $dealerIncentive, 2) 
                : 0.00;
            $ey = isset($data['ey']) ? (float)$data['ey'] : 0.00;
            $eir = $isGMS ? (float)$data['eir'] : 0.00;


            $secondaryMonthly = $isGMS
                ? round((float)($data['secondary_monthly'] ?? 0), 2)
                : 0.00;
            
            $regionName = $this->getRegionNameById($data['region_id'] ?? null);

            /* ============================
               INSERT INTO LOANS
            ============================ */
            $sqlLoan = "INSERT INTO loans (
            reference_number,
            loan_type_id,
            monthly_factor,
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
            ey,
            eir,
            monthly_amortization,
            secondary_monthly,
            region_name,
            source,
            status,
            date_created,
            created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'manual', 'active', NOW(), ?)";

            $stmt = $this->db->prepare($sqlLoan);
            $stmt->execute([
            $data['ref_no'],
            $data['loan_type_id'],
            $data['monthly_factor'] ?? 0,
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
            $ey,
            $eir,
            $monthlyAmortization,
            $secondaryMonthly,
            $regionName,
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
                    INSERT INTO car_loans (loan_id, classification, date_installed, gps_provider, date_created)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmtCar->execute([
                    $loanId,
                    strtoupper($data['classification'] ?? ''),
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
        $term = (int)$data['term'];
        $aor = (float)$data['aor'];

        $monthly = ($balance > 0 && $term > 0 && $aor > 0)
            ? (float) ceil(($balance * (($aor * 0.01) + 1)) / $term)
            : 0.00;
        $startDate = new DateTime($data['date_granted']);

        // EY is stored as annual percentage (example: 18.123456)
        // convert to decimal annual, then to monthly
        $annualEYDecimal = ((float)($data['ey'] ?? 0)) / 100;
        $monthlyRate = $annualEYDecimal / 12;

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
                'primary_ledger',
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

    /* ============================================================
    SECONDARY LEDGER (AMORTIZATION MATCHING EXCEL)
    ============================================================ */
    private function generateSecondaryLedger($loanId, $data, $amortizationRows = []) {

        $balance = round((float)$data['net_proceeds'], 2);
        $monthly = round((float)$data['secondary_monthly'], 2);
        $term = (int)$data['term'];
        $startDate = new DateTime($data['date_granted']);

        $monthlyRate = $this->getEIRRate(
            $term,
            -$monthly,
            $balance
        );

        // ✅ Create a map of status from $amortizationRows for quick lookup
        $statusMap = [];
        foreach ($amortizationRows as $row) {
            $statusMap[(int)$row['payment_number']] = strtolower($row['status'] ?? 'unpaid');
        }

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

            // ✅ Use the status from the map if it exists, otherwise default to 'unpaid'
            $status = $statusMap[$i] ?? 'unpaid';

            $this->saveToLedger(
                'secondary_ledger',
                $loanId,
                $i,
                $startDate->format('Y-m-d'),
                $balance,
                $principal,
                $interest,
                $ending_balance,
                $status
            );

            $balance = $ending_balance;
        }
    }

    private function saveToLedger($table, $loanId, $instNo, $dueDate, $begBal, $principal, $interest, $endBal, $status = 'unpaid') {
        $sql = "INSERT INTO $table 
                (loan_id, installment_no, due_date, beginning_balance, principal, interest, ending_balance, status, date_created) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$loanId, $instNo, $dueDate, $begBal, $principal, $interest, $endBal, $status]);
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

            $interestRate = isset($data['interest_rate'])
                ? round((float)$data['interest_rate'], 6)
                : 0.00;

            // ✅ COMPUTE / NORMALIZE MONTHLY FACTOR
            // Stored format example: 1.50
            $monthlyFactor = isset($data['monthly_factor']) && $data['monthly_factor'] !== null
                ? round((float)$data['monthly_factor'], 6)
                : (($term > 0 && $interestRate > 0) ? round($interestRate / $term, 6) : 0.00);

            // Decimal form for computation example: 0.015
            $monthlyFactorDecimal = $monthlyFactor / 100;

            /* ============================================================
            DEALER INCENTIVE
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
                : 0.00;

            /* ============================================================
            SECONDARY MONTHLY (IF GMS)
            Formula:
            (((net proceeds * monthly factor) * term) + net proceeds) / term
            monthly factor used here must be decimal, ex. 1.50 -> 0.015
            ============================================================ */
            if ($isGMS) {
                if (!empty($data['secondary_monthly'])) {
                    $secondaryMonthly = round((float)$data['secondary_monthly'], 2);
                } else {
                    $secondaryMonthly = round(
                        ((($netProceeds * $monthlyFactorDecimal) * $term) + $netProceeds) / $term,
                        2
                    );
                }
            } else {
                $secondaryMonthly = 0.00;
            }

            /* ============================================================
            EY
            ============================================================ */
            $ey = isset($data['ey'])
                ? round((float)$data['ey'], 6)
                : 0.00;

            /* ============================================================
            EIR CALCULATION
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
                $eir = 0.00;
            }

            $data['secondary_monthly'] = $secondaryMonthly;
            $data['monthly_factor'] = $monthlyFactor;

            /* ============================================================
            INSERT INTO LOANS
            ============================================================ */
            $sqlLoan = "INSERT INTO loans (
                reference_number,
                loan_type_id,
                monthly_factor,
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
                ey,
                eir,
                monthly_amortization,
                secondary_monthly,
                region_name,
                source,
                status,
                date_created,
                created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'import', 'active', NOW(), ?)";

            $stmt = $this->db->prepare($sqlLoan);
            $stmt->execute([
                $data['reference_number'],
                $data['loan_type_id'],
                $monthlyFactor, // ✅ ADDED
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
                $ey,
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
                    (loan_id, classification, date_installed, gps_provider, date_created)
                    VALUES (?, ?, ?, ?, NOW())
                ");

                $stmtCar->execute([
                    $loanId,
                    strtoupper($data['classification'] ?? ''),
                    $data['date_installed'] ?? null,
                    $isGMS ? 'GMS' : null
                ]);
            }

            /* ============================================================
            INSERT PRIMARY LEDGER FROM EXCEL
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
            GENERATE SECONDARY LEDGER ONLY IF GMS
            ============================================================ */
            if ($isGMS) {
                $this->generateSecondaryLedger($loanId, [
                    'net_proceeds'      => $netProceeds,
                    'secondary_monthly' => $secondaryMonthly,
                    'term'              => $term,
                    'date_granted'      => $data['pn_date']
                ], $amortizationRows);
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