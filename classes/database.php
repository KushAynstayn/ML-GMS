<?php

namespace Cadc20239999\MlGms;

use PDO;
use PDOException;

class Database
{
    private $conn;

    /**
     * Connect to a specific database defined in .env
     * * @param string $db_type Either 'LOAN' or 'MASTER'
     * @return PDO|null
     */
    public function connect($db_type = 'LOAN')
{
    $this->conn = null;

    // ✅ Ensure .env is loaded
    if (empty($_ENV)) {
        require_once __DIR__ . '/../vendor/autoload.php';
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    }

        $host = $_ENV["{$db_type}_DB_HOST"] ?? $_SERVER["{$db_type}_DB_HOST"] ?? getenv("{$db_type}_DB_HOST");
        $db_name = $_ENV["{$db_type}_DB_NAME"] ?? $_SERVER["{$db_type}_DB_NAME"] ?? getenv("{$db_type}_DB_NAME");
        $username = $_ENV["{$db_type}_DB_USER"] ?? $_SERVER["{$db_type}_DB_USER"] ?? getenv("{$db_type}_DB_USER");
        $password = $_ENV["{$db_type}_DB_PASS"] ?? $_SERVER["{$db_type}_DB_PASS"] ?? getenv("{$db_type}_DB_PASS");

        if (!$host || !$db_name) {
            die("Error: Database configuration for '{$db_type}' not found in .env file.");
        }

        try {
            // Setting up the DSN (Data Source Name)
            $dsn = "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4";
            
            $this->conn = new PDO($dsn, $username, $password);

            // Set Error Mode to Exception so we can catch mistakes early
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set Default Fetch Mode to Associative Array (cleaner to use)
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $exception) {
            echo "Connection error for {$db_name}: " . $exception->getMessage();
        }

        return $this->conn;
    }
}