<?php
require_once __DIR__ . '/../includes/init.php';

use Cadc20239999\MlGms\LoanService;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $service = new LoanService();

    function clean($val) {
        return str_replace([',', '%'], '', $val ?? '');
    }

    $cleanAOR = clean($_POST['aor'] ?? '0');

    $_POST['interest_rate'] = $cleanAOR;
    $_POST['aor'] = $cleanAOR;

    $_POST['principal'] = clean($_POST['principal'] ?? '');
    $_POST['monthly_amortization'] = clean($_POST['monthly_amortization'] ?? '');
    $_POST['net_proceeds'] = clean($_POST['net_proceeds'] ?? '');
    $_POST['incentive'] = clean($_POST['incentive'] ?? '');
    $_POST['eir'] = clean($_POST['eir'] ?? '');
    $_POST['ey'] = clean($_POST['ey'] ?? '');
    $_POST['monthly_factor'] = clean($_POST['monthly_factor'] ?? '');

    try {
        $response = $service->saveManualRecord($_POST);
        echo json_encode($response);
    } catch (Throwable $e) {
        echo json_encode([
            'status'  => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit;
}

echo json_encode([
    'status'  => 'error',
    'message' => 'Invalid request method'
]);
exit;