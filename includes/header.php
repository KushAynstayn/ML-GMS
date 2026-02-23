<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ML Loans | M Lhuillier</title>
    <link rel="icon" type="image/png" href="../assets/images/mlcircle.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .text-ml-red { color: #d10000; }
        .bg-ml-red { background-color: #d10000; }
        .wave-container {
            position: fixed; bottom: 0; left: 0;
            width: 100%; z-index: -1; line-height: 0;
        }
        @keyframes contentFadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-content {
            animation: contentFadeIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
    </style> </head>
<body class="bg-white font-sans h-screen flex flex-col overflow-hidden">
    <nav class="py-4 px-5 flex justify-between items-center relative z-50 bg-white shadow-md">
        <div class="flex items-center gap-2">
            <img src="../assets/images/ml.png" alt="M Lhuillier" class="h-6 md:ml-2">
        </div>
        <div>
            <img src="../assets/images/mlhuillier-red.png" alt="Icon" class="h-6 md:mr-2">
        </div>
    </nav>