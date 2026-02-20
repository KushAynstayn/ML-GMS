<?php
require_once __DIR__ . '/../classes/database.php';
require_once __DIR__ . '/../classes/MasterDataService.php';

// If you have a vendor/autoload or init file that handles the .env, ensure it's included 
// if your Database class relies on it.

use Cadc20239999\MlGms\MasterDataService;

header('Content-Type: application/json');

try {
    $service = new MasterDataService();
    // No longer passing $region_id as we want all branches
    $branches = $service->getBranches(); 
    echo json_encode($branches);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}