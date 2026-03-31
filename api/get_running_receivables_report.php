<?php
require_once __DIR__ . '/../includes/init.php';

use Cadc20239999\MlGms\Database;

header('Content-Type: application/json');

function normalizeLoanType(string $loanTypeName, ?string $vehicleType = null, ?string $classification = null): string
{
    $loanTypeName = strtoupper(trim($loanTypeName));
    $vehicleType = strtoupper(trim((string)$vehicleType));
    $classification = strtoupper(trim((string)$classification));

    if ($loanTypeName === 'CAR LOAN' || str_contains($loanTypeName, 'CAR')) {
        if (str_contains($classification, 'PRENDA')) return 'prenda';
        if (str_contains($classification, 'PRE') || str_contains($classification, 'OWNED')) return 'pre_owned';
        if (str_contains($classification, 'SURPLUS')) return 'surplus';
        return 'auto_car_loan';
    }

    if ($loanTypeName === 'MOTOR LOAN' || str_contains($loanTypeName, 'MOTOR')) {
        if ($vehicleType === '2-WHEELS') return 'two_wheels';
        if ($vehicleType === '3-WHEELS') return 'three_wheels';
        return 'motorcycle_loan';
    }

    if (str_contains($loanTypeName, 'REAL')) return 'real_estate_loan';
    if (str_contains($loanTypeName, 'COMMERCIAL')) return 'commercial_loan';
    if (str_contains($loanTypeName, 'SALARY')) return 'salary_loan';
    if (str_contains($loanTypeName, 'TRUCK')) return 'truck_loan';
    if (str_contains($loanTypeName, 'SMALL BUSINESS')) return 'small_business_loan';
    if (str_contains($loanTypeName, 'PENSION')) return 'pensioners_loan';

    return 'unknown';
}

try {
    $database = new Database();
    $loanDb = $database->connect('LOAN');
    $masterDb = $database->connect('MASTER');

    $startDate = trim($_GET['start_date'] ?? '');
    $endDate = trim($_GET['end_date'] ?? '');
    $mainZoneCode = trim($_GET['main_zone_code'] ?? '');
    $zoneCode = trim($_GET['zone_code'] ?? '');
    $regionCode = trim($_GET['region_code'] ?? '');

    $masterSql = "
        SELECT
            r.region_code,
            r.region_description,
            r.zone_code,
            z.zone_description,
            z.main_zone_code,
            mz.main_zone_description
        FROM region_masterfile r
        INNER JOIN zone_masterfile z ON z.zone_code = r.zone_code
        INNER JOIN main_zone_masterfile mz ON mz.main_zone_code = z.main_zone_code
        WHERE z.main_zone_code IN ('LNCR', 'VISMIN', 'HO', 'JEW')
    ";

    $masterStmt = $masterDb->query($masterSql);
    $regionMap = [];
    foreach ($masterStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $regionMap[$row['region_code']] = $row;
    }

    $sql = "
        SELECT
            l.loan_id,
            l.reference_number,
            CONCAT_WS(' ', l.first_name, l.middle_name, l.last_name) AS borrower_name,
            l.region_code,
            lt.loan_type_name,
            cl.classification AS car_classification,
            ml.type AS motor_type,
            SUM(pl.remaining_due) AS outstanding_balance
        FROM loans l
        INNER JOIN loan_types lt ON lt.loan_type_id = l.loan_type_id
        INNER JOIN primary_ledger pl ON pl.loan_id = l.loan_id
        LEFT JOIN car_loans cl ON cl.loan_id = l.loan_id
        LEFT JOIN motor_loans ml ON ml.loan_id = l.loan_id
        WHERE l.status = 'active'
          AND pl.status IN ('unpaid', 'partial')
          AND pl.remaining_due > 0
    ";

    $params = [];

    if ($startDate !== '') {
        $sql .= " AND pl.due_date >= ? ";
        $params[] = $startDate;
    }

    if ($endDate !== '') {
        $sql .= " AND pl.due_date <= ? ";
        $params[] = $endDate;
    }

    $sql .= "
        GROUP BY
            l.loan_id,
            l.reference_number,
            borrower_name,
            l.region_code,
            lt.loan_type_name,
            cl.classification,
            ml.type
        ORDER BY borrower_name
    ";

    $stmt = $loanDb->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $summary = [];
    $details = [];
    $cards = [
        'lncr_total' => 0,
        'vismin_total' => 0,
        'head_office_total' => 0,
        'jewelry_total' => 0,
        'grand_total' => 0
    ];

    foreach ($rows as $row) {
        $region = $regionMap[$row['region_code']] ?? null;

        if (!$region) {
            continue;
        }

        if ($mainZoneCode !== '' && $region['main_zone_code'] !== $mainZoneCode) {
            continue;
        }

        if ($zoneCode !== '' && $region['zone_code'] !== $zoneCode) {
            continue;
        }

        if ($regionCode !== '' && $region['region_code'] !== $regionCode) {
            continue;
        }

        $mainZoneBucket = match ($region['main_zone_code']) {
            'LNCR' => 'lncr',
            'VISMIN' => 'vismin',
            'HO' => 'head_office',
            'JEW' => 'jewelry',
            default => null
        };

        if ($mainZoneBucket === null) {
            continue;
        }

        $loanKey = normalizeLoanType(
            $row['loan_type_name'] ?? '',
            $row['motor_type'] ?? '',
            $row['car_classification'] ?? ''
        );

        if ($loanKey === 'unknown') {
            continue;
        }

        if (!isset($summary[$loanKey])) {
            $summary[$loanKey] = [
                'lncr' => 0,
                'vismin' => 0,
                'head_office' => 0,
                'jewelry' => 0
            ];
        }

        $amount = (float)$row['outstanding_balance'];
        $summary[$loanKey][$mainZoneBucket] += $amount;

        if ($mainZoneBucket === 'lncr') {
            $cards['lncr_total'] += $amount;
        } elseif ($mainZoneBucket === 'vismin') {
            $cards['vismin_total'] += $amount;
        } elseif ($mainZoneBucket === 'head_office') {
            $cards['head_office_total'] += $amount;
        } elseif ($mainZoneBucket === 'jewelry') {
            $cards['jewelry_total'] += $amount;
        }

        $cards['grand_total'] += $amount;

        $details[] = [
            'reference_number' => $row['reference_number'],
            'borrower_name' => $row['borrower_name'],
            'loan_type_label' => $row['loan_type_name'],
            'main_zone_display' => $region['main_zone_code'] . ' - ' . $region['main_zone_description'],
            'zone_display' => $region['zone_code'] . ' - ' . $region['zone_description'],
            'region_display' => $region['region_code'] . ' - ' . $region['region_description'],
            'outstanding_balance' => $amount
        ];
    }

    echo json_encode([
        'error' => false,
        'cards' => $cards,
        'summary' => $summary,
        'details' => $details
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}