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

        // Fetching credentials from $_ENV based on the prefix provided
        $host = $_ENV["{$db_type}_DB_HOST"] ?? null;
        $db_name = $_ENV["{$db_type}_DB_NAME"] ?? null;
        $username = $_ENV["{$db_type}_DB_USER"] ?? null;
        $password = $_ENV["{$db_type}_DB_PASS"] ?? null;

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