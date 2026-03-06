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

    public function importFile($filePath){
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $reader = new Xlsx();
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();

        foreach ($sheetNames as $sheetName) {
            $reader->setLoadSheetsOnly([$sheetName]);
            $sheet = $reader->load($filePath)->getActiveSheet();

            // 🔹 UPDATED HEADER DATA MAPPING
            $firstName    = strtoupper(trim($sheet->getCell('B5')->getValue()));
            $middleName   = strtoupper(trim($sheet->getCell('D5')->getValue()));
            $lastName     = strtoupper(trim($sheet->getCell('F5')->getValue()));
            $fullName     = trim("$firstName $middleName $lastName"); // Combined for your DB if needed
            
            $contactNumber   = $sheet->getCell('B6')->getValue();
            $referenceNumber = $sheet->getCell('B7')->getValue();
            
            // Principal, Term, Interest, Monthly Amortization
            $principal    = (float)$sheet->getCell('D6')->getCalculatedValue();
            $term         = (int)$sheet->getCell('D7')->getValue();
            $interestRate = (float)$sheet->getCell('D8')->getCalculatedValue();
            $monthly      = (float)$sheet->getCell('D9')->getCalculatedValue();

            // PN Date (B8)
            $rawPnDate = $sheet->getCell('B8')->getValue();
            $pnDate = is_numeric($rawPnDate) 
                ? ExcelDate::excelToDateTimeObject($rawPnDate)->format('Y-m-d') 
                : ($rawPnDate ? date('Y-m-d', strtotime($rawPnDate)) : null);

            // PN Maturity (B9)
            $rawPnMaturity = $sheet->getCell('B9')->getValue();
            $maturityDate = is_numeric($rawPnMaturity) 
                ? ExcelDate::excelToDateTimeObject($rawPnMaturity)->format('Y-m-d') 
                : ($rawPnMaturity ? date('Y-m-d', strtotime($rawPnMaturity)) : null);

            // Date Installed (B4 - based on typical layout, though not explicitly in your list)
            $rawDateInstalled = $sheet->getCell('B4')->getValue();
            $dateInstalled = is_numeric($rawDateInstalled) 
                ? ExcelDate::excelToDateTimeObject($rawDateInstalled)->format('Y-m-d') 
                : ($rawDateInstalled ? date('Y-m-d', strtotime($rawDateInstalled)) : null);

            if (!$lastName || !$principal) {
                continue;
            }

            // 🔹 Determine loan type from A1 or A2
            $loanTypeRaw = strtoupper(trim($sheet->getCell('A1')->getValue()));
            $loanTypeId = (strpos($loanTypeRaw, 'CAR') !== false) ? 1 : 2;
            $loanTypeText = ($loanTypeId === 1) ? 'CAR LOAN' : 'MOTOR LOAN';

            $loanData = [
                'reference_number'     => $referenceNumber,
                'loan_type_id'         => $loanTypeId,
                'first_name'           => $firstName,
                'middle_name'          => $middleName,
                'last_name'            => $lastName,
                'account_name'         => $fullName, // Kept for backward compatibility
                'contact_number'       => $contactNumber,
                'pn_date'              => $pnDate,
                'pn_maturity_date'     => $maturityDate,
                'principal_amount'     => $principal,
                'term_months'          => $term,
                'interest_rate'        => $interestRate,
                'monthly_amortization' => $monthly,
                'region_id'            => 1,
                'branch_id'            => 1,
                'loan_type_text'       => $loanTypeText,
                'date_installed'       => $dateInstalled,
            ];

            // 🔹 START OF MONTHLY SCHEDULE CHANGED TO ROW 14
            $amortizationRows = $this->extractAmortization($sheet);

            $result = $this->loanService->saveImportedRecord($loanData, $amortizationRows);

            if ($result['status'] !== 'success') {
                throw new Exception($result['message']);
            }
        }
    }

    private function extractAmortization($sheet) {
        $rows = [];
        $row = 14; // ✅ Start of monthly schedule

        while ($sheet->getCell("A$row")->getValue() != "") {
            $rawDueDate = $sheet->getCell("B$row")->getValue();
            $dueDate = is_numeric($rawDueDate) 
                ? ExcelDate::excelToDateTimeObject($rawDueDate)->format('Y-m-d') 
                : ($rawDueDate ? date('Y-m-d', strtotime($rawDueDate)) : null);

            $principal    = (float)$sheet->getCell("C$row")->getCalculatedValue();
            $interest     = (float)$sheet->getCell("D$row")->getCalculatedValue();
            $monthlyAmort = (float)$sheet->getCell("E$row")->getCalculatedValue();
            $endingBalance = (float)$sheet->getCell("F$row")->getCalculatedValue();

            // Beginning Balance logic
            $beginningBalance = ($row == 14)
                ? $endingBalance + $principal
                : $rows[count($rows) - 1]['ending_balance'];

            $rows[] = [
                'payment_number'       => (int)$sheet->getCell("A$row")->getValue(),
                'due_date'             => $dueDate,
                'principal'            => $principal,
                'interest'             => $interest,
                'monthly_amortization' => $monthlyAmort,
                'beginning_balance'    => $beginningBalance,
                'ending_balance'       => $endingBalance,
                'status'               => strtoupper(trim($sheet->getCell("G$row")->getValue())) === 'PAID' ? 'PAID' : 'UNPAID'
            ];

            $row++;
        }

        return $rows;
    }
}