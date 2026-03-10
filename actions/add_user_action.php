<?php

require_once __DIR__ . '/../includes/init.php';

    /* SECURITY: Only admins can create users */
    if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin'){
        header("Location: ../public/login.php");
        exit();
    }

    if(isset($_POST['submit'])){

        $idNumber = $_POST['idNum'];
        $fname    = $_POST['fname'];
        $mname    = $_POST['mname'];
        $lname    = $_POST['lname'];
        $email    = $_POST['email'];
        $user_type = $_POST['user_type'];

        $defaultPassword = "Mlinc12345!@";
        $hashed_pass = password_hash($defaultPassword, PASSWORD_DEFAULT);

        $mustChange = ($user_type === 'admin') ? 0 : 1;

        $select = $loanConn->prepare("SELECT * FROM users WHERE email = :email");
        $select->execute([':email' => $email]);

        if($select->rowCount() > 0){

            $_SESSION['error_message'] = "User already exists";

            header("Location: ../public/user_management.php");
            exit();

        }

        $insert = $loanConn->prepare("
            INSERT INTO users
            (id_number, first_name, middle_name, last_name, email, password, user_type, status, must_change_password, date_created)
            VALUES
            (:id, :fname, :mname, :lname, :email, :pass, :utype, 'active', :must_change, NOW())
        ");

        $result = $insert->execute([
            ':id' => $idNumber,
            ':fname' => $fname,
            ':mname' => $mname,
            ':lname' => $lname,
            ':email' => $email,
            ':pass' => $hashed_pass,
            ':utype' => $user_type,
            ':must_change' => $mustChange
        ]);

        if($result){

            $_SESSION['success_message'] = "User created successfully";

        }else{

            $_SESSION['error_message'] = "User creation failed";

        }

        header("Location: ../public/user_management.php");
        exit();

}