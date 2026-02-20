<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Cadc20239999\MlGms\LoanService;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service = new LoanService();
    
    // Helper to strip formatting (commas, percentages)
    function clean($val) {
        return str_replace([',', '%'], '', $val);
    }

    // Clean all numeric fields sent from loan-calculator.js
    $_POST['principal'] = clean($_POST['principal']);
    $_POST['monthly_amort'] = clean($_POST['monthly_amort']);
    $_POST['net_proceeds'] = clean($_POST['net_proceeds']);
    $_POST['incentive'] = clean($_POST['incentive']);
    $_POST['aor'] = clean($_POST['aor']);
    $_POST['eir'] = clean($_POST['eir']);
    
    $response = $service->saveManualRecord($_POST);
    echo json_encode($response);
}