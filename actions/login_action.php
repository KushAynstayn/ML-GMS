<?php
include '../config/config.php';
session_start();

// Handle Login Submission
if(isset($_POST['submit'])){
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = md5($_POST['password']);
   $current_day_and_time = date('Y-m-d H:i:s');

   $select = "SELECT * FROM mldb.user_form WHERE email = '$email' && password = '$pass'";
   $result = mysqli_query($conn, $select);

   if(mysqli_num_rows($result) > 0){
      $row = mysqli_fetch_array($result);
      
      if($row['status'] == 'Inactive'){
          $_SESSION['error_message'] = "End-User is Inactive. Please contact the administrator.";
          header('location: ../public/login.php');
      } else {
          // Update last online
          mysqli_query($conn, "UPDATE mldb.user_form SET last_online = '$current_day_and_time' WHERE email = '$email'");
          
          // Set Sessions
          $session_prefix = ($row['user_type'] == 'admin') ? 'admin' : 'user';
          $_SESSION[$session_prefix.'_name'] = $row['first_name'].' '.$row['last_name'];
          $_SESSION[$session_prefix.'_email'] = $row['email'];
          $_SESSION['user_type'] = $row['user_type'];

          // Logic for default password
          if($pass == md5("Mlinc1234")){
              $_SESSION['force_password_change'] = true;
          }
          header('location: ../public/dashboard.php');
      }
   } else {
      $_SESSION['error_message'] = "Incorrect Username or Password";
      header('location: ../public/login.php');
   }
   exit();
}