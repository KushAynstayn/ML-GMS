<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Ensure vendor/autoload is here if using Composer
require_once __DIR__ . '/../classes/database.php';
require_once __DIR__ . '/../classes/MasterDataService.php';

use Cadc20239999\MlGms\MasterDataService;

$service = new MasterDataService();
echo json_encode($service->getRegions());