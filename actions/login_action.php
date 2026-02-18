<?php
// 1. Initialize the system
require_once __DIR__ . '/../includes/init.php';

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $current_time = date('Y-m-d H:i:s');

    try {
        // 2. Fetch user by email only
        $stmt = $loanConn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // 3. Verify existence and check HASHED password
        if ($user && password_verify($pass, $user['password'])) {
            
            if ($user['status'] == 'Inactive') {
                $_SESSION['error_message'] = "End-User is Inactive. Please contact the administrator.";
                header('location: ../public/login.php');
                exit();
            }

            // 4. Update last online
            $update = $loanConn->prepare("UPDATE users SET last_online = ? WHERE email = ?");
            $update->execute([$current_time, $email]);

            // 5. Set Sessions
            $prefix = ($user['user_type'] == 'admin') ? 'admin' : 'user';
            $_SESSION[$prefix.'_name'] = $user['first_name'].' '.$user['last_name'];
            $_SESSION[$prefix.'_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];

            // 6. Check for default password
            if ($pass == "Mlinc1234") {
                $_SESSION['force_password_change'] = true;
            }

            header('location: ../public/dashboard.php');
            exit();

        } else {
            $_SESSION['error_message'] = "Incorrect Username or Password";
            header('location: ../public/login.php');
            exit();
        }

    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = "A system error occurred.";
        header('location: ../public/login.php');
        exit();
    }
}