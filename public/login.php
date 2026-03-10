<?php 
// Go up one level from /public/ to find the includes folder
require_once __DIR__ . '/../includes/init.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Login - ML LOANS</title>
   <script src="https://cdn.tailwindcss.com"></script>
   <link rel="icon" href="../assets/images/MLW logo.png" type="image/png">
   <link rel="stylesheet" href="../assets/css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen font-sans">

   <div class="bg-white p-10 rounded-[2.5rem] shadow-xl w-full max-w-[450px] mx-4">
      <form action="../actions/login_action.php" method="post">
         
         <div class="flex flex-col items-center mb-8">
            <div class="bg-red-600 p-3 rounded-2xl shadow-lg mb-4">
               <img src="../assets/images/mlhuillier-red.png" alt="logo" class="w-12 h-12 brightness-0 invert">
            </div>
            <h3 class="text-2xl font-extrabold text-gray-800 tracking-tight uppercase">ML Loans</h3>
         </div>
         
         <div class="space-y-4">
            <input type="text" 
                  name="email" 
                  placeholder="USERNAME" 
                  class="w-full px-6 py-4 border border-gray-400 rounded-full text-center focus:outline-none focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition-all placeholder:text-gray-400 uppercase" 
                  required>
            
            <input type="password" 
                  name="password" 
                  placeholder="PASSWORD" 
                  class="w-full px-6 py-4 border border-gray-400 rounded-full text-center focus:outline-none focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition-all placeholder:text-gray-400" 
                  required>
         </div>
         
         <div class="mt-10">
            <button type="submit" 
                  name="submit" 
                  class="w-32 block mx-auto bg-black text-white font-bold py-3 rounded-full hover:bg-gray-800 transition-all shadow-md active:scale-95 text-sm tracking-widest">
               LOGIN
            </button>
         </div>
         
         <div class="text-center mt-6 flex flex-col gap-2">
            <a href="landing.php" class="text-xs font-semibold text-gray-400 underline decoration-1 underline-offset-4 hover:text-gray-800 transition-colors">
               Back to home
            </a>
         </div>
      </form>
   </div>

   <script>
      const sessionSuccess = "<?php echo $_SESSION['success_message'] ?? ''; ?>";
      const sessionError = "<?php echo $_SESSION['error_message'] ?? ''; ?>";
   </script>
   <script src="../assets/js/login.js"></script>
</body>
</html>
<?php 
unset($_SESSION['success_message']); 
unset($_SESSION['error_message']); 
?>