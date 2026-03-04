<?php
namespace Cadc20239999\MlGms;

class AmortizationService {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect('LOAN');
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
            $stmtLoan->execute([$loanId]);
            $loan = $stmtLoan->fetch(\PDO::FETCH_ASSOC);

            if (!$loan) return null;

            // 2. Fetch Primary Ledger (all loans)
            $sqlPrimary = "SELECT * FROM primary_ledger WHERE loan_id = ? ORDER BY payment_number ASC";
            $stmtPrimary = $this->db->prepare($sqlPrimary);
            $stmtPrimary->execute([$loanId]);
            $primarySchedule = $stmtPrimary->fetchAll(\PDO::FETCH_ASSOC);

            // 3. Fetch Secondary Ledger (only if borrower is GMS)
            $secondarySchedule = [];
            if (strtoupper($loan['borrower_type'] ?? '') === 'GMS') {
                $sqlSecondary = "SELECT * FROM secondary_ledger WHERE loan_id = ? ORDER BY payment_number ASC";
                $stmtSecondary = $this->db->prepare($sqlSecondary);
                $stmtSecondary->execute([$loanId]);
                $secondarySchedule = $stmtSecondary->fetchAll(\PDO::FETCH_ASSOC);
            }

            return [
                'loan' => $loan,
                'primary_schedule' => $primarySchedule,
                'secondary_schedule' => $secondarySchedule
            ];
        } catch (\Exception $e) {
            error_log("AmortizationService Error: " . $e->getMessage());
            return null;
        }
    }
}