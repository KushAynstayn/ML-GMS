<?php

namespace Cadc20239999\MlGms;

use PDO;
use Exception;
use PDOException;
use DateTime;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PaymentImportService
{
    private PDO $db;
    private PaymentService $paymentService;

    public function __construct()
    {
        $database = new Database();
        $conn = $database->connect('LOAN');

        if (!$conn instanceof PDO) {
            throw new Exception('Unable to connect to LOAN database.');
        }

        $this->db = $conn;
        $this->paymentService = new PaymentService();
    }

    public function importFile(string $filePath, int $importId): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("Payment file not found: {$filePath}");
        }

        $reader = $this->createReader($filePath);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            throw new Exception('The uploaded payment file is empty.');
        }

        $headerRowIndex = $this->findHeaderRowIndex($rows);
        if ($headerRowIndex === null) {
            throw new Exception('Could not find the required payment file headers.');
        }

        $headerMap = $this->buildHeaderMap($rows[$headerRowIndex]);
        $this->validateRequiredHeaders($headerMap);

        $stats = [
            'total_rows' => 0,
            'matched_rows' => 0,
            'unmatched_rows' => 0,
            'ambiguous_rows' => 0,
            'applied_rows' => 0,
            'partially_applied_rows' => 0,
            'duplicate_rows' => 0,
        ];

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $rawRow = $rows[$i];

            if ($this->isRowEmpty($rawRow)) {
                continue;
            }

            $normalized = $this->normalizeRow($rawRow, $headerMap);

            if (!$this->isValidDataRow($normalized)) {
                continue;
            }

            $stats['total_rows']++;

            try {
                $paymentId = $this->insertRawPayment($normalized, $importId);

                $result = $this->paymentService->processImportedPayment($paymentId);

                if (($result['status'] ?? '') === 'unmatched') {
                    $stats['unmatched_rows']++;
                } elseif (($result['status'] ?? '') === 'partially_applied') {
                    $stats['matched_rows']++;
                    $stats['partially_applied_rows']++;
                } elseif (($result['status'] ?? '') === 'fully_applied') {
                    $stats['matched_rows']++;
                    $stats['applied_rows']++;
                } elseif (($result['status'] ?? '') === 'unapplied') {
                    $stats['matched_rows']++;
                } else {
                    $stats['matched_rows']++;
                }
            } catch (PDOException $e) {
                if ($this->isDuplicateKeyException($e)) {
                    $stats['duplicate_rows']++;
                    continue;
                }
                throw $e;
            }
        }

        $this->updateImportBatchSummary($importId, $stats);

        return $stats;
    }

    private function createReader(string $filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return $extension === 'xls' ? new Xls() : new Xlsx();
    }

    private function findHeaderRowIndex(array $rows): ?int
    {
        $requiredHeaderHits = [
            "borrower's name",
            'reference no.',
            'date',
            'principal',
            'interest',
            'total'
        ];

        $limit = min(20, count($rows));

        for ($i = 0; $i < $limit; $i++) {
            $headers = array_map([$this, 'normalizeHeader'], $rows[$i]);
            $hits = 0;

            foreach ($requiredHeaderHits as $needle) {
                if (in_array($needle, $headers, true)) {
                    $hits++;
                }
            }

            if ($hits >= 5) {
                return $i;
            }
        }

        return null;
    }

    private function buildHeaderMap(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $index => $header) {
            $normalized = $this->normalizeHeader($header);
            if ($normalized !== '') {
                $map[$normalized] = $index;
            }
        }

        return $map;
    }

    private function validateRequiredHeaders(array $headerMap): void
    {
        $required = [
            "borrower's name",
            'reference no.',
            'date',
            'principal',
            'interest',
            'total'
        ];

        foreach ($required as $header) {
            if (!array_key_exists($header, $headerMap)) {
                throw new Exception("Missing required header: {$header}");
            }
        }
    }

    private function normalizeRow(array $row, array $headerMap): array
    {
        $borrowerName = $this->getCellByHeader($row, $headerMap, "borrower's name");
        $referenceNumber = $this->getCellByHeader($row, $headerMap, 'reference no.');
        $rawDate = $this->getCellByHeader($row, $headerMap, 'date');
        $dateOfTransaction = $this->parseExcelDate($rawDate);    
        $settlement = $this->getCellByHeader($row, $headerMap, 'settlement');
        $partial = $this->getCellByHeader($row, $headerMap, 'partial');
        $origin = $this->getCellByHeader($row, $headerMap, 'origin');
        $paymentReferenceNo = $this->getCellByHeader($row, $headerMap, 'or. no. / payment reference no.');

        $principal = $this->toDecimal($this->getCellByHeader($row, $headerMap, 'principal'));
        $interest = $this->toDecimal($this->getCellByHeader($row, $headerMap, 'interest'));
        $vat = $this->toDecimal($this->getCellByHeader($row, $headerMap, 'vat'));
        $penalty = $this->toDecimal($this->getCellByHeader($row, $headerMap, 'penalty'));
        $interestDiff = $this->toDecimal($this->getCellByHeader($row, $headerMap, 'interest diff.'));
        $partialPaymentBalance = $this->toDecimal($this->getCellByHeader($row, $headerMap, 'partial payment bal.'));
        $total = $this->toDecimal($this->getCellByHeader($row, $headerMap, 'total'));

        $importHash = $this->generateImportHash(
            (string)$referenceNumber,
            (string)$dateOfTransaction,
            (string)$paymentReferenceNo,
            (float)$principal,
            (float)$interest,
            (float)$vat,
            (float)$penalty,
            (float)$interestDiff,
            (float)$partialPaymentBalance,
            (float)$total,
            (string)$borrowerName
        );

        return [
            'borrower_name' => $this->cleanString($borrowerName),
            'reference_number' => $this->cleanString($referenceNumber),
            'payment_date' => $dateOfTransaction,
            'date_of_transaction' => $dateOfTransaction,
            'settlement' => $this->cleanString($settlement),
            'partial' => $this->cleanString($partial),
            'origin' => $this->cleanString($origin),
            'payment_reference_no' => $this->cleanString($paymentReferenceNo),
            'principal' => $principal,
            'interest' => $interest,
            'vat' => $vat,
            'penalty' => $penalty,
            'interest_diff' => $interestDiff,
            'partial_payment_balance' => $partialPaymentBalance,
            'total' => $total,
            'import_hash' => $importHash,
        ];
    }

    private function insertRawPayment(array $row, int $importId): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO payments (
                import_id,
                reference_number,
                loan_id,
                borrower_name,
                payment_reference_no,
                payment_date,
                date_of_transaction,
                amount,
                settlement,
                partial,
                origin,
                principal,
                interest,
                vat,
                penalty,
                interest_diff,
                partial_payment_balance,
                total,
                unmatched_reference,
                match_status,
                application_status,
                excess_amount,
                source,
                date_imported,
                import_hash,
                remarks
            ) VALUES (
                :import_id,
                :reference_number,
                NULL,
                :borrower_name,
                :payment_reference_no,
                :payment_date,
                :date_of_transaction,
                :amount,
                :settlement,
                :partial,
                :origin,
                :principal,
                :interest,
                :vat,
                :penalty,
                :interest_diff,
                :partial_payment_balance,
                :total,
                :unmatched_reference,
                'unmatched',
                'pending',
                0.00,
                'PAYMENT_IMPORT',
                NOW(),
                :import_hash,
                NULL
            )
        ");

        $stmt->execute([
            ':import_id' => $importId,
            ':reference_number' => $row['reference_number'],
            ':borrower_name' => $row['borrower_name'],
            ':payment_reference_no' => $row['payment_reference_no'],
            ':payment_date' => $row['payment_date'],
            ':date_of_transaction' => $row['date_of_transaction'],
            ':amount' => $row['total'],
            ':settlement' => $row['settlement'],
            ':partial' => $row['partial'],
            ':origin' => $row['origin'],
            ':principal' => $row['principal'],
            ':interest' => $row['interest'],
            ':vat' => $row['vat'],
            ':penalty' => $row['penalty'],
            ':interest_diff' => $row['interest_diff'],
            ':partial_payment_balance' => $row['partial_payment_balance'],
            ':total' => $row['total'],
            ':unmatched_reference' => $row['reference_number'],
            ':import_hash' => $row['import_hash'],
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function updateImportBatchSummary(int $importId, array $stats): void
    {
        $stmt = $this->db->prepare("
            UPDATE payment_imports
            SET total_rows = :total_rows,
                matched_rows = :matched_rows,
                unmatched_rows = :unmatched_rows,
                ambiguous_rows = :ambiguous_rows,
                applied_rows = :applied_rows,
                partially_applied_rows = :partially_applied_rows,
                duplicate_rows = :duplicate_rows,
                completed_at = NOW(),
                status = 'completed',
                message = :message
            WHERE import_id = :import_id
        ");

        $message = sprintf(
            'Import completed. Total: %d, Matched: %d, Unmatched: %d, Applied: %d, Partially Applied: %d, Duplicates: %d',
            $stats['total_rows'],
            $stats['matched_rows'],
            $stats['unmatched_rows'],
            $stats['applied_rows'],
            $stats['partially_applied_rows'],
            $stats['duplicate_rows']
        );

        $stmt->execute([
            ':total_rows' => $stats['total_rows'],
            ':matched_rows' => $stats['matched_rows'],
            ':unmatched_rows' => $stats['unmatched_rows'],
            ':ambiguous_rows' => $stats['ambiguous_rows'],
            ':applied_rows' => $stats['applied_rows'],
            ':partially_applied_rows' => $stats['partially_applied_rows'],
            ':duplicate_rows' => $stats['duplicate_rows'],
            ':message' => $message,
            ':import_id' => $importId
        ]);
    }

    private function getCellByHeader(array $row, array $headerMap, string $header): mixed
    {
        $index = $headerMap[$header] ?? null;
        if ($index === null) {
            return null;
        }

        return $row[$index] ?? null;
    }

    private function normalizeHeader(mixed $header): string
    {
        $header = trim((string)$header);
        $header = preg_replace('/\s+/', ' ', $header);

        return strtolower($header);
    }

    private function parseExcelDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Excel numeric serial date
        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (Exception $e) {
                return null;
            }
        }

        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        // Remove excess spaces
        $value = preg_replace('/\s+/', ' ', $value);

        // STRICTLY prioritize MM/DD/YYYY because this is the required file format
        $formats = [
            'm/d/Y',
            'n/j/Y',
            'm-d-Y',
            'n-j-Y',
            'Y-m-d',
            'Y/m/d',
        ];

        foreach ($formats as $format) {
            $dt = DateTime::createFromFormat($format, $value);
            if ($dt instanceof DateTime) {
                $errors = DateTime::getLastErrors();

                if (
                    $errors['warning_count'] === 0 &&
                    $errors['error_count'] === 0
                ) {
                    return $dt->format('Y-m-d');
                }
            }
        }

        return null;
    }

    private function toDecimal(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.00;
        }

        if (is_numeric($value)) {
            return round((float)$value, 2);
        }

        $value = str_replace(',', '', (string)$value);
        $value = preg_replace('/[^\d\.\-]/', '', $value);

        if ($value === '' || !is_numeric($value)) {
            return 0.00;
        }

        return round((float)$value, 2);
    }

    private function cleanString(mixed $value): string
    {
        $value = trim((string)$value);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string)$cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function isValidDataRow(array $row): bool
    {
        $borrowerName = strtoupper(trim((string)($row['borrower_name'] ?? '')));
        $referenceNumber = trim((string)($row['reference_number'] ?? ''));
        $paymentReferenceNo = trim((string)($row['payment_reference_no'] ?? ''));
        $total = (float)($row['total'] ?? 0);

        if (in_array($borrowerName, ['TOTAL', 'GRAND TOTAL', 'TOTALS'], true)) {
            return false;
        }

        if (
            str_contains($borrowerName, 'TOTAL') &&
            $referenceNumber === '' &&
            $paymentReferenceNo === ''
        ) {
            return false;
        }

        if (
            $borrowerName === '' &&
            $referenceNumber === '' &&
            $paymentReferenceNo === '' &&
            $total <= 0
        ) {
            return false;
        }

        return true;
    }

    private function generateImportHash(
        string $referenceNumber,
        string $dateOfTransaction,
        string $paymentReferenceNo,
        float $principal,
        float $interest,
        float $vat,
        float $penalty,
        float $interestDiff,
        float $partialPaymentBalance,
        float $total,
        string $borrowerName = ''
    ): string {
        $raw = strtoupper(trim($referenceNumber)) . '|' .
            trim($dateOfTransaction) . '|' .
            strtoupper(trim($paymentReferenceNo)) . '|' .
            number_format($principal, 2, '.', '') . '|' .
            number_format($interest, 2, '.', '') . '|' .
            number_format($vat, 2, '.', '') . '|' .
            number_format($penalty, 2, '.', '') . '|' .
            number_format($interestDiff, 2, '.', '') . '|' .
            number_format($partialPaymentBalance, 2, '.', '') . '|' .
            number_format($total, 2, '.', '') . '|' .
            strtoupper(trim($borrowerName));

        return hash('sha256', $raw);
    }

    private function isDuplicateKeyException(PDOException $e): bool
    {
        return isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062;
    }
    
}