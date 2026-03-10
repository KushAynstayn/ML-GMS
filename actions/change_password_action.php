<?php
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "All password fields are required.";
        header("Location: ../public/dashboard.php");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header("Location: ../public/dashboard.php");
        exit();
    }

    $hasLength  = strlen($new_password) >= 8;
    $hasUpper   = preg_match('/[A-Z]/', $new_password);
    $hasLower   = preg_match('/[a-z]/', $new_password);
    $hasNumber  = preg_match('/[0-9]/', $new_password);
    $hasSpecial = preg_match('/[^A-Za-z0-9]/', $new_password);

    if (!($hasLength && $hasUpper && $hasLower && $hasNumber && $hasSpecial)) {
        $_SESSION['error_message'] = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
        header("Location: ../public/dashboard.php");
        exit();
    }

    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    $email = $_SESSION['admin_email'] ?? $_SESSION['user_email'] ?? null;

    if (!$email) {
        $_SESSION['error_message'] = "Session expired. Please log in again.";
        header("Location: ../public/login.php");
        exit();
    }

    $stmt = $loanConn->prepare("
        UPDATE users
        SET password = ?, must_change_password = 0
        WHERE email = ?
    ");

    $stmt->execute([$hashed, $email]);

    unset($_SESSION['force_password_change']);
    $_SESSION['success_message'] = "Password updated successfully.";

    header("Location: ../public/dashboard.php");
    exit();
}