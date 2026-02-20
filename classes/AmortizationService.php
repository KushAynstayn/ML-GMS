<?php
namespace Cadc20239999\MlGms;

class AmortizationService {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect('LOAN');
    }

    /**
     * Fetches loan details and its complete amortization schedule
     */
    public function getAmortizationDetails($loanId) {
        try {
            // 1. Get Main Loan Details
            $sqlLoan = "SELECT * FROM loans WHERE loan_id = ?";
            $stmtLoan = $this->db->prepare($sqlLoan);
            $stmtLoan->execute([$loanId]);
            $loan = $stmtLoan->fetch(\PDO::FETCH_ASSOC);

            if (!$loan) return null;

            // 2. Get Schedule
            $sqlSched = "SELECT * FROM amortization_schedule WHERE loan_id = ? ORDER BY payment_number ASC";
            $stmtSched = $this->db->prepare($sqlSched);
            $stmtSched->execute([$loanId]);
            $schedule = $stmtSched->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'loan' => $loan,
                'schedule' => $schedule
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}