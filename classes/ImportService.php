<?php
namespace Cadc20239999\MlGms;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate; // ✅ ADDED
use Exception;

class ImportService
{
    private $loanService;

    public function __construct()
    {
        $this->loanService = new LoanService();
    }

    public function importFile($filePath)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $reader = new Xlsx();
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();

        foreach ($sheetNames as $sheetName) {
            $reader->setLoadSheetsOnly([$sheetName]);
            $sheet = $reader->load($filePath)->getActiveSheet();

            // 🔹 HEADER DATA
            $accountName = strtoupper(trim($sheet->getCell('C9')->getValue()));
            $contactNumber = $sheet->getCell('C10')->getValue();

            // ✅ FIX: use getCalculatedValue for formula cells
            $principal = (float)$sheet->getCell('B5')->getCalculatedValue();
            $dealerIncentive = (float)$sheet->getCell('B6')->getCalculatedValue();
            $eir = (float)$sheet->getCell('B7')->getCalculatedValue();

            // ✅ FIX: Proper PN Date conversion (C12)
            $rawPnDate = $sheet->getCell('C12')->getValue();
            if (is_numeric($rawPnDate)) {
                $pnDate = ExcelDate::excelToDateTimeObject($rawPnDate)->format('Y-m-d');
            } else {
                $pnDate = $rawPnDate ? date('Y-m-d', strtotime($rawPnDate)) : null;
            }

            // ✅ FIX: PN Maturity Date directly from C13 (as you said)
            $rawPnMaturity = $sheet->getCell('C13')->getValue();
            if (is_numeric($rawPnMaturity)) {
                $maturityDate = ExcelDate::excelToDateTimeObject($rawPnMaturity)->format('Y-m-d');
            } else {
                $maturityDate = $rawPnMaturity ? date('Y-m-d', strtotime($rawPnMaturity)) : null;
            }

            // ✅ FIX: Monthly amortization formula cell
            $rawMonthly = $sheet->getCell('F14')->getCalculatedValue();
            $cleanMonthly = str_replace(',', '', $rawMonthly);
            $monthly = (float)$cleanMonthly;

            $term = (int)$sheet->getCell('E12')->getValue();
            $referenceNumber = $sheet->getCell('C11')->getValue();
            $interestRate = (float)$sheet->getCell('E13')->getCalculatedValue();

            // ✅ FIX: Date Installed (B8)
            $rawDateInstalled = $sheet->getCell('B8')->getValue();
            if (is_numeric($rawDateInstalled)) {
                $dateInstalled = ExcelDate::excelToDateTimeObject($rawDateInstalled)->format('Y-m-d');
            } else {
                $dateInstalled = $rawDateInstalled ? date('Y-m-d', strtotime($rawDateInstalled)) : null;
            }

            if (!$accountName || !$principal) {
                continue;
            }

            $netProceeds = $principal - $dealerIncentive;

            // 🔹 Determine loan type from A4
            $loanTypeRaw = strtoupper(trim($sheet->getCell('A4')->getValue()));
            if (strpos($loanTypeRaw, 'MOTOR') !== false) {
                $loanTypeText = 'MOTOR LOAN';
                $loanTypeId = 2;

                $vehicleType = strtoupper(trim($sheet->getCell('B12')->getValue()));
                if (!in_array($vehicleType, ['2-WHEELS', '3-WHEELS'])) {
                    $vehicleType = '2-WHEELS';
                }

            } elseif (strpos($loanTypeRaw, 'CAR') !== false) {
                $loanTypeText = 'CAR LOAN';
                $loanTypeId = 1;
                $vehicleType = null;
            } else {
                throw new Exception("Invalid loan type in sheet: $sheetName");
            }

            $loanData = [
                'reference_number' => $referenceNumber,
                'loan_type_id' => $loanTypeId,
                'account_name' => $accountName,
                'contact_number' => $contactNumber,
                'pn_date' => $pnDate,
                'pn_maturity_date' => $maturityDate,
                'principal_amount' => $principal,
                'term_months' => $term,
                'dealer_incentive' => $dealerIncentive,
                'net_proceeds' => $netProceeds,
                'interest_rate' => $interestRate,
                'eir' => $eir,
                'monthly_amortization' => $monthly,
                'region_id' => 1,
                'branch_id' => 1,
                'loan_type_text' => $loanTypeText,
                'vehicle_type' => $vehicleType,
                'date_installed' => $dateInstalled,
                'gps_provider' => null
            ];

            $amortizationRows = $this->extractAmortization($sheet);

            $result = $this->loanService->saveImportedRecord($loanData, $amortizationRows);

            if ($result['status'] !== 'success') {
                throw new Exception($result['message']);
            }
        }
    }

    private function extractAmortization($sheet){
        $rows = [];
        $row = 18;

        while ($sheet->getCell("A$row")->getValue()) {

            // ✅ FIX: Due date must be inside loop
            $rawDueDate = $sheet->getCell("B$row")->getValue();

            if (is_numeric($rawDueDate)) {
                $dueDate = ExcelDate::excelToDateTimeObject($rawDueDate)->format('Y-m-d');
            } else {
                $dueDate = $rawDueDate ? date('Y-m-d', strtotime($rawDueDate)) : null;
            }

            $principal = (float)$sheet->getCell("C$row")->getCalculatedValue();
            $interest = (float)$sheet->getCell("D$row")->getCalculatedValue();
            $monthlyAmort = (float)$sheet->getCell("E$row")->getCalculatedValue();

            // Column F already contains correct running balance from Excel
            $endingBalance = (float)$sheet->getCell("F$row")->getCalculatedValue();

            // First row beginning balance = net proceeds
            // Next rows beginning balance = previous ending balance
            $beginningBalance = ($row == 18)
                ? $endingBalance + $principal   // restore original starting balance
                : $rows[count($rows) - 1]['ending_balance'];

            $rows[] = [
                'payment_number' => (int)$sheet->getCell("A$row")->getValue(),
                'due_date' => $dueDate,
                'principal' => $principal,
                'interest' => $interest,
                'monthly_amortization' => $monthlyAmort,
                'beginning_balance' => $beginningBalance,
                'ending_balance' => $endingBalance,
                'status' => strtoupper(trim($sheet->getCell("G$row")->getValue())) === 'PAID'
                            ? 'PAID'
                            : 'UNPAID'
            ];

            $row++;
        }

        return $rows;
    }
}