<?php
namespace Cadc20239999\MlGms;

class AmortizationService {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect('LOAN'); // connect to LOAN DB
    }

    /**
     * Fetch loan details and ledger schedules
     * Returns both primary and secondary ledger schedules if applicable
     */
    public function getAmortizationDetails($loanId) {
        try {
            // 1. Get Main Loan Details
            $sqlLoan = "SELECT * FROM loans WHERE loan_id = ?";
            $stmtLoan = $this->db->prepare($sqlLoan);
            $stmtLoan->execute([(int)$loanId]); // cast to int for safety
            $loan = $stmtLoan->fetch(\PDO::FETCH_ASSOC);

            if (!$loan) return null; // loan not found

            // 2. Fetch Primary Ledger
            $sqlPrimary = "SELECT * FROM primary_ledger WHERE loan_id = ? ORDER BY installment_no ASC";
            $stmtPrimary = $this->db->prepare($sqlPrimary);
            $stmtPrimary->execute([(int)$loanId]);
            $primarySchedule = $stmtPrimary->fetchAll(\PDO::FETCH_ASSOC);

            // 3. Fetch Secondary Ledger if GPS provider = 'GMS'
            $secondarySchedule = [];
            $gpsProvider = '';

            // Check if loan is car or motor to get gps_provider
            if ((int)$loan['loan_type_id'] === 1) { // Car
                $stmt = $this->db->prepare("SELECT gps_provider FROM car_loans WHERE loan_id = ?");
                $stmt->execute([(int)$loanId]);
                $gpsProvider = $stmt->fetchColumn() ?: '';
            } elseif ((int)$loan['loan_type_id'] === 2) { // Motor
                $stmt = $this->db->prepare("SELECT gps_provider FROM motor_loans WHERE loan_id = ?");
                $stmt->execute([(int)$loanId]);
                $gpsProvider = $stmt->fetchColumn() ?: '';
            }

            if (strtoupper($gpsProvider) === 'GMS') {
                $sqlSecondary = "SELECT * FROM secondary_ledger WHERE loan_id = ? ORDER BY installment_no ASC";
                $stmtSecondary = $this->db->prepare($sqlSecondary);
                $stmtSecondary->execute([(int)$loanId]);
                $secondarySchedule = $stmtSecondary->fetchAll(\PDO::FETCH_ASSOC);
            }

            // Return structured data
            return [
                'loan' => $loan,
                'primary_ledger' => $primarySchedule,
                'secondary_ledger' => $secondarySchedule
            ];

        } catch (\Exception $e) {
            error_log("AmortizationService Error: " . $e->getMessage());
            return null;
        }
    }
}