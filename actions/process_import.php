<?php
session_start();

require_once '../vendor/autoload.php';
require_once '../classes/ImportService.php';
require_once '../classes/PaymentImportService.php';
require_once '../classes/Database.php';

use Cadc20239999\MlGms\Database;
use Cadc20239999\MlGms\ImportService;
use Cadc20239999\MlGms\PaymentImportService;

$importsDir   = '../storage/imports/';
$processedDir = '../storage/processed/';
$failedDir    = '../storage/failed/';

foreach ([$importsDir, $processedDir, $failedDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

$importType = $_POST['import_type'] ?? 'ledger';
$isPaymentImport = ($importType === 'payment');

$fileField = $isPaymentImport ? 'payment_file' : 'loan_file';

if (!isset($_FILES[$fileField])) {
    echo "No file received.";
    exit;
}

$file = $_FILES[$fileField];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($extension, ['xlsx', 'xls'])) {
    echo "Only Excel files are allowed.";
    exit;
}

if ($file['size'] > 20 * 1024 * 1024) {
    echo "File too large. Maximum size is 20MB.";
    exit;
}

$db = new Database();
$conn = $db->connect('LOAN');

if (!$conn) {
    echo "Database connection failed.";
    exit;
}

$storedName = uniqid($isPaymentImport ? 'payment_import_' : 'import_', true) . '.' . $extension;
$filePath = $importsDir . $storedName;

if ($isPaymentImport) {
    $stmt = $conn->prepare("
        INSERT INTO payment_imports (
            original_name,
            stored_name,
            status,
            imported_by,
            started_at,
            created_at
        ) VALUES (
            :original_name,
            :stored_name,
            'processing',
            :imported_by,
            NOW(),
            NOW()
        )
    ");

    $importedBy = trim(
        strtoupper(
            ($_SESSION['last_name'] ?? 'SYSTEM') . ', ' . ($_SESSION['first_name'] ?? 'USER')
        )
    );

    $stmt->execute([
        ':original_name' => $file['name'],
        ':stored_name'   => $storedName,
        ':imported_by'   => $importedBy
    ]);

    $importId = (int)$conn->lastInsertId();
} else {
    $stmt = $conn->prepare("
        INSERT INTO imports (original_name, stored_name, status, created_at)
        VALUES (:original_name, :stored_name, 'processing', NOW())
    ");

    $stmt->execute([
        ':original_name' => $file['name'],
        ':stored_name'   => $storedName
    ]);

    $importId = (int)$conn->lastInsertId();
}

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    if ($isPaymentImport) {
        $stmt = $conn->prepare("
            UPDATE payment_imports
            SET status = 'failed',
                message = 'Upload failed.',
                completed_at = NOW()
            WHERE import_id = :id
        ");
    } else {
        $stmt = $conn->prepare("
            UPDATE imports
            SET status = 'failed',
                message = 'Upload failed.'
            WHERE id = :id
        ");
    }

    $stmt->execute([':id' => $importId]);

    echo "Upload failed.";
    exit;
}

try {
    if ($isPaymentImport) {
        $paymentImportService = new PaymentImportService();
        $stats = $paymentImportService->importFile($filePath, $importId);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        if (ob_get_length()) {
            ob_clean();
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Payment import completed successfully.',
            'stats' => $stats
        ]);
        exit;
    }

    $importService = new ImportService();
    $importService->importFile($filePath);

    $stmt = $conn->prepare("
        UPDATE imports
        SET status = 'completed',
            message = 'Import completed successfully.'
        WHERE id = :id
    ");
    $stmt->execute([':id' => $importId]);

    if (file_exists($filePath)) {
        unlink($filePath);
    }

    if (ob_get_length()) {
        ob_clean();
    }

    echo "success";
    exit;

} catch (Exception $e) {
    if (file_exists($filePath)) {
        @rename($filePath, $failedDir . $storedName);
    }

    if ($isPaymentImport) {
        $stmt = $conn->prepare("
            UPDATE payment_imports
            SET status = 'failed',
                message = :message,
                completed_at = NOW()
            WHERE import_id = :id
        ");
    } else {
        $stmt = $conn->prepare("
            UPDATE imports
            SET status = 'failed',
                message = :message
            WHERE id = :id
        ");
    }

    $stmt->execute([
        ':message' => $e->getMessage(),
        ':id'      => $importId
    ]);

    if (ob_get_length()) {
        ob_clean();
    }

    echo "Import failed: " . $e->getMessage();
    exit;
}