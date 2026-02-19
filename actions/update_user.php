<?php
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['user_type'] === 'admin') {
    $id_number = $_POST['id_number'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email']; 
    $user_type = $_POST['user_type'];
    $status = $_POST['status'];
    
    $admin_name = $_SESSION['full_name'] ?? $_SESSION['admin_name'] ?? 'Admin';

    try {
        $sql = "UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                user_type = ?, 
                status = ?, 
                modify_by = ?, 
                modified_date = NOW() 
                WHERE id_number = ?";
        
        $stmt = $loanConn->prepare($sql);
        $stmt->execute([$first_name, $last_name, $email, $user_type, $status, $admin_name, $id_number]);
        
        header("Location: ../public/user_management.php?update=success");
        exit();
    } catch (PDOException $e) {
        // This captures the exact SQL error (e.g., Duplicate entry, Column not found)
        $error_msg = urlencode($e->getMessage());
        header("Location: ../public/user_management.php?update=error&msg=" . $error_msg);
        exit();
    }
}