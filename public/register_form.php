<?php
// 1. Initialize the system
require_once __DIR__ . '/../includes/init.php';

if(isset($_POST['submit'])){
   $idNumber = $_POST['idNum'];
   $fname    = $_POST['fname'];
   $mname    = $_POST['mname'];
   $lname    = $_POST['lname'];
   $email    = $_POST['email'];
   $pass     = $_POST['password'];
   $cpass    = $_POST['cpassword'];
   $user_type = $_POST['user_type'];

   // Check if user already exists
   $select = $loanConn->prepare("SELECT * FROM users WHERE email = :email");
   $select->execute([':email' => $email]);
   
   if($select->rowCount() > 0){
      $error[] = 'User already exists!';
   } else {
      if($pass != $cpass){
         $error[] = 'Passwords do not match!';
      } else {
         $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
         $insert = $loanConn->prepare("INSERT INTO users (id_number, first_name, middle_name, last_name, email, password, user_type, status) 
                                       VALUES (:id, :fname, :mname, :lname, :email, :pass, :utype, 'Active')");
         
         $result = $insert->execute([
            ':id'    => $idNumber,
            ':fname' => $fname,
            ':mname' => $mname,
            ':lname' => $lname,
            ':email' => $email,
            ':pass'  => $hashed_pass,
            ':utype' => $user_type
         ]);

         if($result) {
            header('location: ../public/login.php');
            exit();
         } else {
            $error[] = 'Registration failed.';
         }
      }
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register - ML LOANS</title>
   <link rel="icon" type="image/png" href="../assets/images/mlcircle.png">
   <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-6">

   <div class="bg-white p-8 rounded-[2rem] shadow-2xl w-full max-w-2xl">
      <div class="flex flex-col items-center mb-6">
         <img src="../assets/images/mlhuillier-red.png" alt="logo" class="w-16 mb-2">
         <h3 class="text-3xl font-black text-gray-800 uppercase tracking-tight">User Registration</h3>
      </div>

      <?php if(isset($error)): ?>
         <?php foreach($error as $msg): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded italic text-sm">
               <?php echo $msg; ?>
            </div>
         <?php endforeach; ?>
      <?php endif; ?>

      <form action="" method="post" class="space-y-4">
         
         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
               <label class="text-xs font-bold text-gray-500 uppercase ml-4">Account Type</label>
               <select name="user_type" class="w-full px-6 py-3 border border-gray-300 rounded-full focus:ring-2 focus:ring-red-500 outline-none appearance-none bg-white transition-all">
                  <option value="user">User</option>
                  <option value="admin">Admin</option>
               </select>
            </div>

            <div class="md:col-span-2">
               <input type="text" name="idNum" required placeholder="ID Number (e.g. 00000001)" 
                      class="w-full px-6 py-3 border border-gray-300 rounded-full focus:ring-2 focus:ring-red-500 outline-none transition-all">
            </div>

            <input type="text" name="fname" required placeholder="First Name" 
                   class="w-full px-6 py-3 border border-gray-300 rounded-full focus:ring-2 focus:ring-red-500 outline-none transition-all">
            
            <input type="text" name="mname" placeholder="Middle Name (Optional)" 
                   class="w-full px-6 py-3 border border-gray-300 rounded-full focus:ring-2 focus:ring-red-500 outline-none transition-all">

            <input type="text" name="lname" placeholder="Last Name" 
                   class="w-full px-6 py-3 border border-gray-300 rounded-full focus:ring-2 focus:ring-red-500 outline-none transition-all">

            <input type="text" name="email" required placeholder="Username / Email" 
                   class="w-full px-6 py-3 border border-gray-300 rounded-full focus:ring-2 focus:ring-red-500 outline-none transition-all">
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <input id="psw" type="password" name="password" required placeholder="Password" 
                   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                   class="w-full px-6 py-3 border border-gray-300 rounded-full focus:ring-2 focus:ring-red-500 outline-none transition-all">
            
            <input type="password" name="cpassword" required placeholder="Confirm Password" 
                   class="w-full px-6 py-3 border border-gray-300 rounded-full focus:ring-2 focus:ring-red-500 outline-none transition-all">
         </div>

         <div id="message" class="hidden bg-gray-50 p-4 rounded-2xl text-xs space-y-1 border border-gray-200">
            <p class="font-bold text-gray-700 mb-1">Password requirements:</p>
            <p id="letter" class="text-red-500 flex items-center">● A lowercase letter</p>
            <p id="capital" class="text-red-500 flex items-center">● A capital letter</p>
            <p id="number" class="text-red-500 flex items-center">● A number</p>
            <p id="length" class="text-red-500 flex items-center">● Minimum 8 characters</p>
         </div>

         <div class="flex flex-col items-center pt-4">
            <button type="submit" name="submit" 
                    class="bg-red-600 text-white font-bold px-12 py-3 rounded-full hover:bg-red-700 transition-all shadow-lg active:scale-95 uppercase tracking-widest text-sm">
               Register 
            </button>
            <a href="login.php" class="mt-4 text-sm text-gray-500 hover:text-red-600 transition-colors">Already have an account? Login</a>
         </div>
      </form>
   </div>

   <script>
      const myInput = document.getElementById("psw");
      const message = document.getElementById("message");
      const letter = document.getElementById("letter");
      const capital = document.getElementById("capital");
      const number = document.getElementById("number");
      const length = document.getElementById("length");

      myInput.onfocus = () => message.classList.remove("hidden");
      myInput.onblur = () => message.classList.add("hidden");

      myInput.onkeyup = function() {
         const lowerCase = /[a-z]/g;
         const upperCase = /[A-Z]/g;
         const numbers = /[0-9]/g;

         const validate = (condition, element) => {
            if(condition) {
               element.classList.replace("text-red-500", "text-green-500");
               element.innerHTML = "✓ " + element.innerText.split(' ').slice(1).join(' ');
            } else {
               element.classList.replace("text-green-500", "text-red-500");
               element.innerHTML = "● " + element.innerText.split(' ').slice(1).join(' ');
            }
         };

         validate(myInput.value.match(lowerCase), letter);
         validate(myInput.value.match(upperCase), capital);
         validate(myInput.value.match(numbers), number);
         validate(myInput.value.length >= 8, length);
      }
   </script>
</body>
</html>