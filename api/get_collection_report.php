<?php
require_once __DIR__ . '/../includes/init.php';

use Cadc20239999\MlGms\Database;

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->connect('LOAN');

    $search = trim($_GET['search'] ?? '');
    $vehicleType = trim($_GET['vehicle_type'] ?? 'all');
    $startDate = trim($_GET['start_date'] ?? '');
    $endDate = trim($_GET['end_date'] ?? '');

    $sql = "
        SELECT
            l.loan_id,
            l.loan_type_id,
            l.reference_number,
            l.first_name,
            l.middle_name,
            l.last_name,
            l.pn_date,
            l.term_months,
            l.secondary_monthly,

            c.date_installed AS car_date_installed,
            c.gps_provider AS car_gps_provider,

            m.date_installed AS motor_date_installed,
            m.type AS motor_type,
            m.gps_provider AS motor_gps_provider,

            sl.secondary_ledger_id,
            sl.installment_no,
            sl.due_date,
            sl.principal AS ledger_principal,
            sl.interest AS ledger_interest,
            sl.paid_date,
            sl.status AS ledger_status,
            sl.last_payment_id,

            p.payment_id,
            p.payment_date,
            p.date_of_transaction,
            p.application_status,
            p.match_status,
            p.payment_number,
            p.principal AS payment_principal,
            p.interest AS payment_interest

        FROM payments p
        INNER JOIN loans l
            ON p.loan_id = l.loan_id

        LEFT JOIN car_loans c
            ON l.loan_id = c.loan_id

        LEFT JOIN motor_loans m
            ON l.loan_id = m.loan_id

        LEFT JOIN secondary_ledger sl
            ON sl.last_payment_id = p.payment_id
           AND sl.loan_id = p.loan_id

        WHERE p.match_status = 'matched'
          AND p.application_status IN ('fully_applied', 'partially_applied')
          AND (
                (l.loan_type_id = 1 AND UPPER(COALESCE(c.gps_provider, '')) = 'GMS')
                OR
                (l.loan_type_id = 2 AND UPPER(COALESCE(m.gps_provider, '')) = 'GMS')
              )
    ";

    $params = [];

    if ($search !== '') {
        $sql .= "
            AND (
                l.reference_number LIKE :search
                OR CONCAT_WS(' ', l.first_name, l.middle_name, l.last_name) LIKE :search
            )
        ";
        $params[':search'] = '%' . $search . '%';
    }

    if ($vehicleType === 'car') {
        $sql .= " AND l.loan_type_id = 1 ";
    } elseif ($vehicleType === '2-wheels') {
        $sql .= " AND l.loan_type_id = 2 AND m.type = '2-WHEELS' ";
    } elseif ($vehicleType === '3-wheels') {
        $sql .= " AND l.loan_type_id = 2 AND m.type = '3-WHEELS' ";
    }

    if ($startDate !== '' && $endDate !== '') {
        $sql .= " AND l.pn_date BETWEEN :start_date AND :end_date ";
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;
    } elseif ($startDate !== '') {
        $sql .= " AND l.pn_date = :start_date ";
        $params[':start_date'] = $startDate;
    }

    $sql .= " ORDER BY p.payment_date DESC, l.reference_number ASC, sl.installment_no ASC ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array_map(function ($row) {
        $fullName = trim(
            ($row['first_name'] ?? '') . ' ' .
            ($row['middle_name'] ?? '') . ' ' .
            ($row['last_name'] ?? '')
        );

        $dateInstalled = '---';
        if (!empty($row['car_date_installed'])) {
            $dateInstalled = date('m/d/Y', strtotime($row['car_date_installed']));
        } elseif (!empty($row['motor_date_installed'])) {
            $dateInstalled = date('m/d/Y', strtotime($row['motor_date_installed']));
        }

        $loanTypeFilter = '';
        $wheelsFilter = '';

        if ((int)$row['loan_type_id'] === 1) {
            $loanTypeFilter = 'car';
        } elseif ((int)$row['loan_type_id'] === 2) {
            $loanTypeFilter = 'motor';

            if (($row['motor_type'] ?? '') === '2-WHEELS') {
                $wheelsFilter = '2-wheels';
            } elseif (($row['motor_type'] ?? '') === '3-WHEELS') {
                $wheelsFilter = '3-wheels';
            }
        }

        $status = 'Pending';
        if (($row['application_status'] ?? '') === 'fully_applied') {
            $status = 'Fully Paid';
        } elseif (($row['application_status'] ?? '') === 'partially_applied') {
            $status = 'Partially Paid';
        }

        $principal = 0.00;
        $interest = 0.00;
        $monthlyAmortization = 0.00;

        if ($row['ledger_principal'] !== null) {
            $principal = (float)$row['ledger_principal'];
        } elseif ($row['payment_principal'] !== null) {
            $principal = (float)$row['payment_principal'];
        }

        if ($row['ledger_interest'] !== null) {
            $interest = (float)$row['ledger_interest'];
        } elseif ($row['payment_interest'] !== null) {
            $interest = (float)$row['payment_interest'];
        }

        if ($row['secondary_monthly'] !== null && $row['secondary_monthly'] !== '') {
            $monthlyAmortization = (float)$row['secondary_monthly'];
        }

        return [
            'loan_id' => (int)$row['loan_id'],
            'payment_id' => (int)$row['payment_id'],
            'date_granted' => !empty($row['pn_date']) ? date('m/d/Y', strtotime($row['pn_date'])) : '---',
            'date_installed' => $dateInstalled,
            'account_name' => preg_replace('/\s+/', ' ', $fullName),
            'monthly_amortization' => number_format($monthlyAmortization, 2),
            'principal' => number_format($principal, 2),
            'interest' => number_format($interest, 2),
            'term' => (int)$row['term_months'] . ' mos',
            'reference_number' => $row['reference_number'] ?? '',
            'date_applied' => !empty($row['due_date']) ? date('m/d/Y', strtotime($row['due_date'])) : '---',
            'date_paid' => !empty($row['payment_date']) ? date('m/d/Y', strtotime($row['payment_date'])) : '---',
            'status' => $status,
            'loan_type_filter' => $loanTypeFilter,
            'wheels_filter' => $wheelsFilter,
        ];
    }, $rows);

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}