<?php
session_start();

require_once '../vendor/autoload.php';
require_once '../classes/ImportService.php';
require_once '../classes/Database.php';

use Cadc20239999\MlGms\Database;

// Define directories at the top level to prevent "Undefined variable" errors
$importsDir   = '../storage/imports/';
$processedDir = '../storage/processed/';
$failedDir    = '../storage/failed/';

// 2. AUTO-CLEANUP FUNCTION: Wipe old files to keep storage "clean free"
foreach ([$importsDir, $processedDir, $failedDir] as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '*'); // Get all files in the directory
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file); // Delete it
            }
        }
    } else {
        mkdir($dir, 0777, true);
    }
}

if (!isset($_FILES['loan_file'])) {
    echo "No file received.";
    exit;
}

$file = $_FILES['loan_file'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($extension, ['xlsx', 'xls'])) {
    echo "Only Excel files are allowed.";
    exit;
}

if ($file['size'] > 20 * 1024 * 1024) {
    echo "File too large. Maximum size is 20MB.";
    exit;
}

foreach ([$importsDir, $processedDir, $failedDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

$db = new Database();
$conn = $db->connect('LOAN'); // Using the correct connect method

$storedName = uniqid('import_', true) . '.' . $extension;

// ✅ Insert record into imports table (processing)
$stmt = $conn->prepare("
    INSERT INTO imports (original_name, stored_name, status, created_at)
    VALUES (:original_name, :stored_name, 'processing', NOW())
");

$stmt->execute([
    ':original_name' => $file['name'],
    ':stored_name'   => $storedName
]);

$importId = $conn->lastInsertId();

$filePath = $importsDir . $storedName;

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    $stmt = $conn->prepare("
        UPDATE imports 
        SET status = 'failed', message = 'Upload failed.'
        WHERE id = :id
    ");
    $stmt->execute([':id' => $importId]);

    echo "Upload failed.";
    exit;
}

try {
    // 1. Process the Excel file
    $importService = new \Cadc20239999\MlGms\ImportService();
    $importService->importFile($filePath);

    // 2. Update the Database record to 'completed'
    $stmt = $conn->prepare("
        UPDATE imports 
        SET status = 'completed', 
            message = 'Import completed successfully.' 
        WHERE id = :id
    ");
    $stmt->execute([':id' => $importId]);

    // 3. SUCCESS CLEANUP: Delete the file from /storage/imports/ immediately
    // This keeps your processed folder "clean free" as requested.
    if (file_exists($filePath)) {
        unlink($filePath); 
    }

    // 4. Signal success to the JavaScript fetch
    if (ob_get_length()) ob_clean(); // Clear warnings to prevent "HTML in popup" errors
    echo "success"; 
    exit;

} catch (Exception $e) {
    // 5. FAILURE HANDLING: Move to failed folder for temporary debugging
    if (file_exists($filePath)) {
        rename($filePath, $failedDir . $storedName);
    }

    // 6. Update the Database record with the error message
    $stmt = $conn->prepare("
        UPDATE imports 
        SET status = 'failed', 
            message = :message 
        WHERE id = :id
    ");
    $stmt->execute([
        ':message' => $e->getMessage(),
        ':id' => $importId
    ]);

    // 7. Signal the error message to the Modal
    if (ob_get_length()) ob_clean();
    echo "Import failed: " . $e->getMessage();
    exit;
}