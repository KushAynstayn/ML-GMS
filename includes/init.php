<?php
/**
 * System Initialization File
 * This file handles sessions, autoloading, and database connections.
 */

// 1. Start Session - keeps users logged in across pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Load Composer Autoloader
// We use __DIR__ to get the current folder, then /../ to go up to the root
require_once __DIR__ . '/../vendor/autoload.php';

// 3. Load Environment Variables (.env)
try {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    die("Error: Could not find or load the .env file in the root directory.");
}

// 4. Import the Database Class
// This must match the namespace you defined in composer.json
use Cadc20239999\MlGms\Database;

// 5. Initialize Connections
$db = new Database();

// Establish connection to the main system database (ml_loans_db)
$loanConn = $db->connect('LOAN');

// Establish connection to the reference database (masterdata)
$masterConn = $db->connect('MASTER');

/**
 * Now, $loanConn and $masterConn are available to any page 
 * that includes this init.php file.
 */