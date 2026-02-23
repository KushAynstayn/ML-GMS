<?php
namespace Cadc20239999\MlGms;

use Exception;
use DateTime;

class LoanService {
    private $db;

    public function __construct() {
        // Ensure Database class is available or required
        $this->db = (new Database())->connect('LOAN');
    }


    public function saveManualRecord($data) {
        try {
            $this->db->beginTransaction();

        // 1. Fixed SQL: removed NOW() because created_by is an INT (User ID)
        $sqlLoan = "INSERT INTO loans (
            reference_number, loan_type_id, account_name, contact_number, 
            pn_date, pn_maturity_date, principal_amount, term_months, 
            dealer_incentive, net_proceeds, interest_rate, eir, 
            monthly_amortization, region_id, branch_id, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // Added a ? for created_by

        $fullName = strtoupper("{$data['last_name']}, {$data['first_name']} {$data['middle_name']}");
        
        // 2. Get the User ID from the session (ensure session_start() is called in init.php)
        $currentUserId = $_SESSION['user_id'] ?? 1; // Fallback to 1 if not found

        $stmt = $this->db->prepare($sqlLoan);
        $stmt->execute([
            $data['ref_no'], 
            $data['loan_type_id'], 
            $fullName, 
            $data['contact'],
            $data['date_granted'], 
            $data['maturity_date'], 
            $data['principal'], 
            $data['term'],
            $data['incentive'], 
            $data['net_proceeds'], 
            $data['aor'], 
            $data['eir'],
            // 3. Match the key from your save_loan.php cleaning logic
            $data['monthly_amortization'] ?? $data['monthly_amortization'], 
            $data['region_id'], 
            $data['branch_id'],
            $currentUserId // This satisfies the 'created_by' INT requirement
        ]);

        $loanId = $this->db->lastInsertId();

            // 2. Insert into 'car_loans' ONLY if it is a Car Loan
            // Note: your JS sets 'is_car_loan' to '1' or '0'
            if (isset($data['is_car_loan']) && $data['is_car_loan'] == '1') {
                $sqlCar = "INSERT INTO car_loans (
                    loan_id, classification, device_installed, date_installed
                ) VALUES (?, ?, ?, ?)";
                
                $stmtCar = $this->db->prepare($sqlCar);
                $stmtCar->execute([
                    $loanId, 
                    $data['classification'], 
                    $data['device_installed'], 
                    ($data['device_installed'] === 'YES' ? $data['date_installed'] : null)
                ]);
            }

            // 3. Generate Schedule
            $this->generateAmortization($loanId, $data);

            $this->db->commit();
            return ['status' => 'success', 'loan_id' => $loanId, 'message' => 'Record saved successfully!'];

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function generateAmortization($loan_id, $data) {
        $balance = round((float)$data['net_proceeds'], 2);
        $monthly = round((float)$data['monthly_amortization'], 2);
        $startDate = new DateTime($data['date_granted']);
        $monthly_eir = (float)$data['eir'] / 100;

        for ($i = 1; $i <= (int)$data['term']; $i++) {
            $startDate->modify('+1 month');
            
            // Interest based on fixed AOR logic
            $interest = round($balance * $monthly_eir, 2);
            $principal = round($monthly - $interest, 2);
            $ending_balance = round($balance - $principal, 2);

            // On the last month, force the balance to zero to handle rounding cents
            if ($i == (int)$data['term']) {
                $ending_balance = 0.00;
                $principal = $balance;
            }

            $stmt = $this->db->prepare("INSERT INTO amortization_schedule 
                (loan_id, payment_number, due_date, beginning_balance, principal, interest, ending_balance) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $loan_id, 
                $i, 
                $startDate->format('Y-m-d'), 
                $balance, 
                $principal, 
                $interest, 
                $ending_balance
            ]);
            
            $balance = $ending_balance;
        }
    }
}