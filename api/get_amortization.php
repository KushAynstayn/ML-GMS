<?php
require_once '../vendor/autoload.php'; // Adjust path based on your structure
header('Content-Type: application/json');

use Cadc20239999\MlGms\AmortizationService;

if (isset($_GET['loan_id'])) {
    $service = new AmortizationService();
    $data = $service->getAmortizationDetails($_GET['loan_id']);
    
    if ($data) {
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Record not found']);
    }
}