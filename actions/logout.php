<?php
// 1. Initialize the system (This starts the session and loads necessary classes)
require_once __DIR__ . '/../includes/init.php';

// 2. Clear all session variables
$_SESSION = array();

// 3. Destroy the actual session on the server
session_destroy();

// 4. Redirect the user back to the landing page
header("Location: ../public/landing.php");
exit();
?>