<?php
// This replaces the vendor/autoload, .env, and Database initialization
require_once __DIR__ . '/../includes/init.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ML Loans | M Lhuillier</title>
    <link rel="icon" type="image/png" href="../assets/images/mlcircle.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="bg-white font-sans min-h-screen flex flex-col overflow-x-hidden">
    <?php include('../includes/header.php'); ?>

    <main class="flex-grow flex items-center px-10 md:px-24">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            
            <div class="space-y-6 -mt-12 md:-mt-24 relative z-10">
                <h1 class="text-6xl md:text-6xl font-black text-ml-red tracking-tight leading-none">
                    ML LOANS
                </h1>
                <p class="text-gray-600 text-lg leading-relaxed max-w-md">
                    M Lhuillier offers flexible loan options, including vehicle, home, salary, and personal property loans, designed to provide quick and accessible financing for different financial needs.
                </p>
                
                <a href="../public/login.php">
                    <button class="btn-slide px-8 py-2 font-bold rounded-xl shadow-[0_4px_15px_rgba(0,0,0,0.1)] hover:shadow-xl transition-shadow duration-300 mt-4">
                        LOGIN
                    </button>
                </a>
            </div>

            <div class="flex justify-center md:justify-end">
                <img src="../assets/images/landingillus.png" alt="Loan Illustration" 
                     class="animate-float-heartbeat w-[120%] max-w-none md:w-[140%] -mt-20 md:-mt-40 lg:-mt-30 transform transition duration-500 relative z-20">      
            </div>
                                
        </div>
    </main>

    <div class="wave-container">
        <img src="../assets/images/lg.png" alt="Wave Background" class="w-full object-cover">
        <div class="absolute bottom-4 w-full text-center text-white text-sm opacity-80">
            © <?php echo date("Y"); ?> All Rights Reserved | M Lhuillier
        </div>
    </div>

</body>
</html>