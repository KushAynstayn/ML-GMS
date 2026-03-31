<?php
require_once __DIR__ . '/../includes/init.php';

use Cadc20239999\MlGms\Database;

header('Content-Type: application/json');

try {
    $database = new Database();
    $masterDb = $database->connect('MASTER');

    $mainZoneCode = trim($_GET['main_zone_code'] ?? '');
    $zoneCode = trim($_GET['zone_code'] ?? '');

    $result = [
        'main_zones' => [],
        'zones' => [],
        'regions' => []
    ];

    $stmt = $masterDb->query("
        SELECT main_zone_code, main_zone_description
        FROM main_zone_masterfile
        WHERE main_zone_code IN ('LNCR', 'VISMIN', 'HO', 'JEW')
        ORDER BY FIELD(main_zone_code, 'LNCR', 'VISMIN', 'HO', 'JEW')
    ");
    $result['main_zones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($mainZoneCode !== '') {
        $stmt = $masterDb->prepare("
            SELECT zone_code, zone_description
            FROM zone_masterfile
            WHERE main_zone_code = ?
            ORDER BY zone_code
        ");
        $stmt->execute([$mainZoneCode]);
    } else {
        $stmt = $masterDb->query("
            SELECT zone_code, zone_description
            FROM zone_masterfile
            WHERE main_zone_code IN ('LNCR', 'VISMIN', 'HO', 'JEW')
            ORDER BY zone_code
        ");
    }
    $result['zones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "
        SELECT
            r.region_code,
            r.region_description
        FROM region_masterfile r
        INNER JOIN zone_masterfile z ON z.zone_code = r.zone_code
        WHERE z.main_zone_code IN ('LNCR', 'VISMIN', 'HO', 'JEW')
    ";

    $params = [];

    if ($mainZoneCode !== '') {
        $sql .= " AND z.main_zone_code = ? ";
        $params[] = $mainZoneCode;
    }

    if ($zoneCode !== '') {
        $sql .= " AND r.zone_code = ? ";
        $params[] = $zoneCode;
    }

    $sql .= " ORDER BY r.region_description ";

    $stmt = $masterDb->prepare($sql);
    $stmt->execute($params);
    $result['regions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}