<?php
// Use the full namespace path
use Cadc20239999\MlGms\Database;

// Adjust path to reach classes folder and vendor for autoloading
require_once __DIR__ . '/../vendor/autoload.php'; 
require_once __DIR__ . '/../classes/database.php';

header('Content-Type: application/json');

$ref = $_GET['ref'] ?? '';
$response = ['exists' => false];

if (!empty($ref)) {
    try {
        $dbClass = new Database();
        // Your method is named connect(), and it returns a PDO object
        $pdo = $dbClass->connect('LOAN'); 

        if ($pdo) {
            // Using PDO syntax (since your class uses PDO)
            $stmt = $pdo->prepare("SELECT loan_id FROM loans WHERE reference_number = :ref LIMIT 1");
            $stmt->execute(['ref' => $ref]);
            
            if ($stmt->fetch()) {
                $response['exists'] = true;
            }
        }
    } catch (Exception $e) {
        $response['error'] = $e->getMessage();
    }
}

echo json_encode($response);