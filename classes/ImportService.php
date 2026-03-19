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
            $sheet = $spreadsheet->getSheetByName($sheetName);

            // 🔹 HEADER DATA MAPPING BASED ON EXCEL IMAGE
            $loanTypeRaw = strtoupper(trim($sheet->getCell('A1')->getValue())); 
            
            // ✅ CRITICAL FIX: Explicitly check for GMS to determine ledger routing
            $isGMS = strtoupper(trim($sheet->getCell('A2')->getValue())) === 'GMS';
            
            $regionName = trim($sheet->getCell('B3')->getValue()); 
            
            $rawDateInstalled = $sheet->getCell('B4')->getValue(); 
            $dateInstalled = is_numeric($rawDateInstalled) 
                ? ExcelDate::excelToDateTimeObject($rawDateInstalled)->format('Y-m-d') 
                : ($rawDateInstalled ? date('Y-m-d', strtotime($rawDateInstalled)) : null);

            $classification = strtoupper(trim($sheet->getCell('B5')->getValue())); 

            // Row 6: Names
            $firstName  = strtoupper(trim($sheet->getCell('B6')->getValue()));
            $middleName = strtoupper(trim($sheet->getCell('D6')->getValue()));
            $lastName   = strtoupper(trim($sheet->getCell('F6')->getValue()));
            
            // Row 7: Contact & Loan Amount
            $contactNumber = $sheet->getCell('B7')->getValue();
            $principal     = (float)$sheet->getCell('D7')->getCalculatedValue();
            
            // Row 8: Ref No & Term
            $referenceNumber = $sheet->getCell('B8')->getValue();
            $term            = (int)$sheet->getCell('D8')->getValue();

            // Row 9: PN Date & Interest Rate
            $rawPnDate = $sheet->getCell('B9')->getValue();
            $pnDate = is_numeric($rawPnDate) 
                ? ExcelDate::excelToDateTimeObject($rawPnDate)->format('Y-m-d') 
                : ($rawPnDate ? date('Y-m-d', strtotime($rawPnDate)) : null);
            $interestRate = (float)$sheet->getCell('D9')->getCalculatedValue();

            $monthlyFactor = ($term > 0 && $interestRate > 0)
            ? round($interestRate / $term, 6)
            : 0.00;

            // Row 10: PN Maturity & Monthly Amortization
            $rawPnMaturity = $sheet->getCell('B10')->getValue();
            $maturityDate = is_numeric($rawPnMaturity) 
                ? ExcelDate::excelToDateTimeObject($rawPnMaturity)->format('Y-m-d') 
                : ($rawPnMaturity ? date('Y-m-d', strtotime($rawPnMaturity)) : null);
            $monthly = (float)$sheet->getCell('D10')->getCalculatedValue();

            if (!$lastName || !$principal) {
                continue;
            }

            $loanTypeId = (strpos($loanTypeRaw, 'CAR') !== false) ? 1 : 2;
            $loanTypeText = ($loanTypeId === 1) ? 'CAR LOAN' : 'MOTOR LOAN';

            $loanData = [
                'reference_number'     => $referenceNumber,
                'loan_type_id'         => $loanTypeId,
                'first_name'           => $firstName,
                'middle_name'          => $middleName,
                'last_name'            => $lastName,
                'contact_number'       => $contactNumber,
                'pn_date'              => $pnDate,
                'pn_maturity_date'     => $maturityDate,
                'principal_amount'     => $principal,
                'monthly_factor'       => $monthlyFactor,
                'term_months'          => $term,
                'interest_rate'        => $interestRate,
                'monthly_amortization' => $monthly,
                'region_name'          => $regionName,
                'loan_type_text'       => $loanTypeText,
                'date_installed'       => $dateInstalled,
                'gms'                  => $isGMS, // Passed to Service to control ledger display
                'classification'       => $classification,
                'vehicle_type'         => ($loanTypeId === 2) ? $classification : null,
            ];

            // 🔹 AMORTIZATION SCHEDULE STARTS AT ROW 15
            $amortizationRows = $this->extractAmortization($sheet);

            // Pass the data to the service. The $isGMS flag inside $loanData 
            // should be used by your LoanService to skip secondary ledger creation.
            $result = $this->loanService->saveImportedRecord($loanData, $amortizationRows);

            if ($result['status'] !== 'success') {
                throw new Exception($result['message']);
            }
        }
    }

    private function extractAmortization($sheet) {
        $rows = [];
        $row = 15; 

        while ($sheet->getCell("A$row")->getValue() != "") {
            $rawDueDate = $sheet->getCell("B$row")->getValue();
            $dueDate = is_numeric($rawDueDate) 
                ? ExcelDate::excelToDateTimeObject($rawDueDate)->format('Y-m-d') 
                : ($rawDueDate ? date('Y-m-d', strtotime($rawDueDate)) : null);

            $principal     = (float)$sheet->getCell("C$row")->getCalculatedValue();
            $interest      = (float)$sheet->getCell("D$row")->getCalculatedValue();
            $totalAmount   = (float)$sheet->getCell("E$row")->getCalculatedValue(); 
            $endingBalance = (float)$sheet->getCell("F$row")->getCalculatedValue(); 

            $beginningBalance = ($row == 15)
                ? (float)$sheet->getCell("F14")->getValue()
                : $rows[count($rows) - 1]['ending_balance'];

            $rows[] = [
                'payment_number'    => (int)$sheet->getCell("A$row")->getValue(),
                'due_date'          => $dueDate,
                'principal'         => $principal,
                'interest'          => $interest,
                'monthly_amortization' => $totalAmount,
                'beginning_balance' => $beginningBalance,
                'ending_balance'    => $endingBalance,
                'status'            => strtoupper(trim($sheet->getCell("G$row")->getValue())) === 'PAID' ? 'PAID' : 'UNPAID'
            ];

            $row++;
        }

        return $rows;
    }
}