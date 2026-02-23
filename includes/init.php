<?php
/**
 * System Initialization File
 * This file handles sessions, autoloading, and database connections.
 */

// 🔒 Prevent "headers already sent" issues
if (!ob_get_level()) {
    ob_start();
}

// 1. Start Session - keeps users logged in across pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Load Composer Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// 3. Load Environment Variables (.env)
try {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    die("Error: Could not find or load the .env file in the root directory.");
}

// 4. Import the Database Class
use Cadc20239999\MlGms\Database;

// 5. Initialize Connections
$db = new Database();
$loanConn   = $db->connect('LOAN');
$masterConn = $db->connect('MASTER');