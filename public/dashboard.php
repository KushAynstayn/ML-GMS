<?php include('../includes/header.php'); ?>

<div class="flex overflow-hidden" style="height: calc(100vh - 64px);">
    
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto">
        <header class="mb-8">
            <h2 class="text-3xl font-bold text-gray-400 mb-6">Dashboard</h2>
            
            <div class="flex flex-wrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm">
                <div class="flex-1 relative">
                    <input type="text" placeholder="Search" class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-ml-red/20">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500">
                    <span class="text-xs font-bold uppercase">From</span>
                    <input type="date" class="focus:outline-none bg-transparent">
                </div>
                <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500">
                    <span class="text-xs font-bold uppercase">To</span>
                    <input type="date" class="focus:outline-none bg-transparent">
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-gradient-to-br from-red-900 to-red-800 p-6 rounded-2xl shadow-lg text-white text-center">
                <div class="h-24 flex items-center justify-center mb-2">
                    <div class="w-full h-full opacity-50 bg-[url('https://www.svgrepo.com/show/532276/chart-line.svg')] bg-center bg-no-repeat bg-contain"></div>
                </div>
                <div class="text-3xl font-black">100</div>
                <div class="text-sm font-medium opacity-80 mt-1 uppercase tracking-wider">New Records</div>
            </div>

            <div class="bg-red-600 p-6 rounded-2xl shadow-lg text-white text-center">
                <div class="h-24 flex items-center justify-center mb-2 text-red-200">
                    <div class="w-full h-full opacity-50 bg-[url('https://www.svgrepo.com/show/532276/chart-line.svg')] bg-center bg-no-repeat bg-contain brightness-200"></div>
                </div>
                <div class="text-3xl font-black">100</div>
                <div class="text-sm font-medium opacity-80 mt-1 uppercase tracking-wider">Monthly Loans</div>
            </div>

            <div class="bg-red-700 p-6 rounded-2xl shadow-lg text-white text-center">
                <div class="h-24 flex items-center justify-center mb-2">
                    <div class="w-full h-full opacity-50 bg-[url('https://www.svgrepo.com/show/532276/chart-line.svg')] bg-center bg-no-repeat bg-contain brightness-200"></div>
                </div>
                <div class="text-3xl font-black">100</div>
                <div class="text-sm font-medium opacity-80 mt-1 uppercase tracking-wider">Analytics</div>
            </div>

            <div class="bg-red-500 p-6 rounded-2xl shadow-lg text-white text-center">
                <div class="h-24 flex items-center justify-center mb-2">
                    <div class="w-full h-full opacity-50 bg-[url('https://www.svgrepo.com/show/532276/chart-line.svg')] bg-center bg-no-repeat bg-contain brightness-200"></div>
                </div>
                <div class="text-3xl font-black">100</div>
                <div class="text-sm font-medium opacity-80 mt-1 uppercase tracking-wider">Analytics</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white p-8 rounded-2xl shadow-sm flex flex-col items-center justify-center min-h-[300px]">
                 <div class="w-48 h-48 rounded-full border-[30px] border-red-600 border-r-pink-300 border-b-yellow-700 relative"></div>
                 <p class="mt-4 text-gray-400 font-bold uppercase tracking-widest text-xs">Loan Distribution</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm flex flex-col items-center justify-center min-h-[300px]">
                 <div class="w-48 h-48 rounded-full border-[20px] border-red-600 relative flex items-center justify-center">
                    <div class="w-32 h-32 rounded-full border-[2px] border-red-600"></div>
                 </div>
                 <p class="mt-4 text-gray-400 font-bold uppercase tracking-widest text-xs">Target Completion</p>
            </div>
        </div>
    </main>
</div>

</body>
</html>