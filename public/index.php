<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ML Loans | M Lhuillier</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .text-ml-red { color: #d10000; }
        .bg-ml-red { background-color: #d10000; }
        
        .wave-container {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: -1;
            line-height: 0;
        }

        /* --- New Animation Logic --- */
        @keyframes float-heartbeat {
            0% {
                transform: translateY(0px) scale(1);
            }
            25% {
                transform: translateY(-10px) scale(1.02); /* Slight lift and pulse */
            }
            50% {
                transform: translateY(0px) scale(1);
            }
            75% {
                transform: translateY(-5px) scale(1.01); /* Smaller secondary pulse */
            }
            100% {
                transform: translateY(0px) scale(1);
            }
        }

        .animate-float-heartbeat {
            animation: float-heartbeat 4s ease-in-out infinite;
        }
        /* --------------------------- */

        .btn-slide {
            position: relative;
            display: inline-block;
            overflow: hidden;
            background: white;
            color: #d10000;
            border: 2px solid #d10000;
            transition: color 0.4s ease;
            z-index: 1;
        }

        .btn-slide::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: #d10000;
            transition: left 0.4s ease;
            z-index: -1;
        }

        .btn-slide:hover::before {
            left: 0;
        }

        .btn-slide:hover {
            color: white;
        }
    </style>
</head>
<body class="bg-white font-sans min-h-screen flex flex-col overflow-x-hidden">
    <?php include('../includes/header.php'); ?>

    <main class="flex-grow flex items-center px-10 md:px-24">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            
            <div class="space-y-6 -mt-12 md:-mt-24 relative z-10">
                <h1 class="text-6xl md:text-8xl font-black text-ml-red tracking-tight leading-none">
                    ML LOANS
                </h1>
                <p class="text-gray-600 text-lg leading-relaxed max-w-md">
                    M Lhuillier offers flexible loan options, including vehicle, home, salary, and personal property loans, designed to provide quick and accessible financing for different financial needs.
                </p>
                
                <button class="btn-slide px-14 py-3 font-bold rounded-xl shadow-[0_4px_15px_rgba(0,0,0,0.1)] hover:shadow-xl transition-shadow duration-300">
                    LOGIN
                </button>
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