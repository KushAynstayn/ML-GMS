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
    <style>
        /* Keep the page white but allow the canvas to sit behind content */
        body {
            background-color: #ffffff;
            margin: 0;
            overflow-x: hidden;
        }

        #waveCanvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1; /* Behind everything */
            pointer-events: none;
        }

        /* Ensure text remains readable over the red waves at the bottom */
        .relative-z {
            position: relative;
            z-index: 10;
        }
    </style>
</head>
<body class="font-sans min-h-screen flex flex-col">

    <canvas id="waveCanvas"></canvas>

    <?php include('../includes/header.php'); ?>

    <main class="flex-grow flex items-center px-10 md:px-24 relative-z">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            
            <div class="space-y-6 -mt-12 md:-mt-24">
                <h1 class="text-6xl md:text-6xl font-black text-red-600 tracking-tight leading-none">
                    ML LOANS
                </h1>
                <p class="text-gray-600 text-lg leading-relaxed max-w-md">
                    M Lhuillier offers flexible loan options, including vehicle, home, salary, and personal property loans, designed to provide quick and accessible financing for different financial needs.
                </p>
                
                <a href="../public/login.php">
                    <button class="bg-red-600 text-white px-8 py-3 font-bold rounded-xl shadow-[0_4px_15px_rgba(239,68,68,0.3)] hover:bg-red-700 transition-all duration-300 mt-4">
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

    <footer class="relative-z pb-6">
        <div class="w-full text-center text-gray-300 text-sm">
            © <?php echo date("Y"); ?> All Rights Reserved | M Lhuillier
        </div>
    </footer>

    <script>
        const canvas = document.getElementById('waveCanvas');
        const ctxWave = canvas.getContext('2d');

        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resize);
        resize();

        let offset = 0;

        // Distinct red colors for depth
        const waveColors = [
            'rgba(239, 68, 68, 0.15)', // Red 500
            'rgba(185, 28, 28, 0.25)', // Red 700
            'rgba(127, 29, 29, 0.40)'  // Red 900
        ];

        function drawWaves() {
            ctxWave.clearRect(0, 0, canvas.width, canvas.height);
            
            for (let i = 0; i < 3; i++) {
                ctxWave.fillStyle = waveColors[i];
                ctxWave.beginPath();
                ctxWave.moveTo(0, canvas.height);
                
                for (let x = 0; x <= canvas.width; x++) {
                    /**
                     * ADJUSTMENT MADE HERE:
                     * Changed base offset from (canvas.height - 150) to (canvas.height - 20)
                     * Reduced layer spacing from (i * 80) to (i * 30)
                     * This keeps the waves at the very bottom of the viewport.
                     */
                    let y = Math.sin(x * 0.003 + offset + (i * 2)) * 40 + (canvas.height - (i * 30) - 80);
                    ctxWave.lineTo(x, y);
                }
                
                ctxWave.lineTo(canvas.width, canvas.height);
                ctxWave.fill();
            }
            
            offset += 0.008; 
            requestAnimationFrame(drawWaves);
        }
        
        drawWaves();
    </script>
</body>
</html>