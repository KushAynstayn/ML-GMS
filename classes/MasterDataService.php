<?php

namespace Cadc20239999\MlGms;

use Cadc20239999\MlGms\Database; // Explicitly import the namespaced class
use PDO;

class MasterDataService {
    private $db;

    public function __construct() {
        // Your database.php requires a parameter ('LOAN' or 'MASTER')
        $this->db = (new Database())->connect('LOAN'); 
    }

    public function getLoanTypes() {
        $stmt = $this->db->query("SELECT * FROM loan_types WHERE status = 'active'");
        return $stmt->fetchAll();
    }

    public function getRegions() { // Connect to Master database for regions 
        $masterDb = (new Database())->connect('MASTER'); 
        $stmt = $masterDb->query("SELECT id, region_description FROM 
        region_masterfile"); 
        return $stmt->fetchAll(); 
    }

    public function getBranches() {
        $masterDb = (new Database())->connect('MASTER');

        // We fetch all branches without filtering by region
        $stmt = $masterDb->prepare("
            SELECT branch_id, branch_name 
            FROM branch_profile 
            ORDER BY branch_name ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll();
    }
}