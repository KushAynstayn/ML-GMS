<?php
require_once __DIR__ . '/../includes/init.php';

if (isset($_POST['submit'])) {

    $email = trim($_POST['email']);
    $pass  = $_POST['password'];
    $current_time = date('Y-m-d H:i:s');

    try {

        $stmt = $loanConn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password'])) {

            if ($user['status'] === 'inactive') {
                $_SESSION['error_message'] = "User account is inactive. Please contact the administrator.";
                header('Location: ../public/login.php');
                exit();
            }

            $update = $loanConn->prepare("UPDATE users SET last_online = ? WHERE email = ?");
            $update->execute([$current_time, $email]);

            $prefix = ($user['user_type'] === 'admin') ? 'admin' : 'user';

            $_SESSION[$prefix . '_name']  = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION[$prefix . '_email'] = $user['email'];
            $_SESSION['user_type']        = $user['user_type'];
            $_SESSION['user_id']          = $user['id'];

            if ((int)$user['must_change_password'] === 1) {
                $_SESSION['force_password_change'] = true;
            } else {
                // Ensure the flag is completely removed if they are already good to go
                unset($_SESSION['force_password_change']);
            }

            header('Location: ../public/dashboard.php');
            exit();

        } else {
            $_SESSION['error_message'] = "Incorrect Username or Password";
            header('Location: ../public/login.php');
            exit();
        }

    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = "A system error occurred.";
        header('Location: ../public/login.php');
        exit();
    }
}
?>