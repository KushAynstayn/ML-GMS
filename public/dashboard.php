<?php include('../includes/header.php'); ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fcfcfc; }
    
    .stats-card {
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
        background: white;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(185, 28, 28, 0.08);
    }

    /* Red state for selected buttons */
    .btn-active-red {
        background-color: #c53030 !important;
        color: white !important;
        border-color: #c53030 !important;
        box-shadow: 0 4px 14px 0 rgba(197, 48, 48, 0.39);
    }

    /* Matching the image table header */
    .table-crimson thead {
        background-color: #c53030;
    }
</style>

<div class="flex overflow-hidden" style="height: calc(100vh - 64px);">
    
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 p-8 lg:p-10 overflow-y-auto">
        <header class="relative bg-white p-10 rounded-[2rem] border border-gray-100 shadow-sm mb-10 overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">Dashboard <span class="text-red-600">Overview</span></h2>
                <p class="text-gray-500 font-medium mt-2">Insights and performance overview</p>

                <div class="mt-8 space-y-4">
                    <div class="max-w-2xl relative">
                        <input type="text" id="searchInput" placeholder="Search account or reference..." 
                            class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-red-600/20 focus:bg-white transition-all text-sm font-medium">
                        <svg class="w-5 h-5 absolute left-4 top-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button onclick="setDateMode('single')" id="btnSingle" class="px-8 py-2.5 text-sm font-bold rounded-full border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition-all btn-active-red">Single Date</button>
                        <button onclick="setDateMode('range')" id="btnRange" class="px-8 py-2.5 text-sm font-bold rounded-full border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition-all">Select Range</button>
                        
                        <div class="flex items-center gap-2">
                            <div class="flex items-center px-6 py-2.5 bg-gray-50 border border-gray-200 rounded-full">
                                <span id="dateLabel" class="text-[10px] font-black text-gray-400 uppercase mr-3 tracking-widest">Date</span>
                                <input type="date" id="startDate" onchange="filterDashboard()" class="bg-transparent text-sm font-bold text-gray-700 focus:outline-none cursor-pointer">
                            </div>

                            <div id="toDateContainer" class="hidden flex items-center px-6 py-2.5 bg-gray-50 border border-gray-200 rounded-full">
                                <span class="text-[10px] font-black text-gray-400 uppercase mr-3 tracking-widest">To</span>
                                <input type="date" id="endDate" onchange="filterDashboard()" class="bg-transparent text-sm font-bold text-gray-700 focus:outline-none cursor-pointer">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="absolute right-10 top-1/2 -translate-y-1/2 opacity-20 lg:opacity-100 pointer-events-none">
                <img src="../assets/images/mlcircle.png" alt="MLhuillier Logo" class="w-48 h-auto object-contain">
            </div>
        </header>

        <div id="noRecordFound" class="hidden mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center gap-3 text-red-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold">No records found matching your selection.</span>
        </div>

        <div id="searchTableContainer" class="hidden bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mb-10">
            <table class="w-full text-left table-crimson" id="loansTable">
                <thead>
                    <tr>
                        <th class="px-8 py-5 text-xs font-bold text-white border-r border-red-500/30">Date Released</th>
                        <th class="px-8 py-5 text-xs font-bold text-white border-r border-red-500/30">Account Name</th>
                        <th class="px-8 py-5 text-xs font-bold text-white">Reference</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="text-gray-700">
                    <tr class="border-b border-gray-50 hover:bg-red-50/20 transition-colors">
                        <td class="px-8 py-5 font-bold text-sm text-gray-400">08/08/2025</td>
                        <td class="px-8 py-5 font-bold">Leah Faye Genson</td>
                        <td class="px-8 py-5">
                            <span class="px-4 py-1.5 border border-red-600 text-red-600 rounded-full text-xs font-bold">MCRVMWQTW</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <div class="stats-card p-8 rounded-[1.5rem] flex items-center gap-6">
                <div class="w-20 h-20 bg-red-900 rounded-3xl flex items-center justify-center shrink-0 shadow-lg shadow-red-900/20">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                </div>
                <div>
                    <h4 class="text-gray-900 text-3xl font-extrabold">$12,000</h4>
                    <p class="text-gray-500 font-medium">Sample Data</p>
                </div>
            </div>

            <div class="stats-card p-8 rounded-[1.5rem] flex items-center gap-6">
                <div class="w-20 h-20 bg-red-600 rounded-3xl flex items-center justify-center shrink-0 shadow-lg shadow-red-600/20">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                </div>
                <div>
                    <h4 class="text-gray-900 text-3xl font-extrabold">$482,000</h4>
                    <p class="text-gray-500 font-medium">Sample Data</p>
                </div>
            </div>

            <div class="stats-card p-8 rounded-[1.5rem] flex items-center gap-6">
                <div class="w-20 h-20 bg-rose-500 rounded-3xl flex items-center justify-center shrink-0 shadow-lg shadow-rose-500/20">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                </div>
                <div>
                    <h4 class="text-gray-900 text-3xl font-extrabold">$52,000</h4>
                    <p class="text-gray-500 font-medium">Sample Data</p>
                </div>
            </div>

            <div class="stats-card p-8 rounded-[1.5rem] flex items-center gap-6">
                <div class="w-20 h-20 bg-red-400 rounded-3xl flex items-center justify-center shrink-0 shadow-lg shadow-red-400/20">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </div>
                <div>
                    <h4 class="text-gray-900 text-3xl font-extrabold">$1,0000</h4>
                    <p class="text-gray-500 font-medium">Sample Data</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
let dateMode = 'single';

function setDateMode(mode) {
    dateMode = mode;
    const toContainer = document.getElementById('toDateContainer');
    const dateLabel = document.getElementById('dateLabel');
    const btnSingle = document.getElementById('btnSingle');
    const btnRange = document.getElementById('btnRange');

    if (mode === 'single') {
        toContainer.classList.add('hidden');
        dateLabel.innerText = 'Date';
        btnSingle.classList.add('btn-active-red');
        btnRange.classList.remove('btn-active-red');
        document.getElementById('endDate').value = ""; 
    } else {
        toContainer.classList.remove('hidden');
        dateLabel.innerText = 'From';
        btnRange.classList.add('btn-active-red');
        btnSingle.classList.remove('btn-active-red');
    }
    filterDashboard(); 
}

function filterDashboard() {
    const searchText = document.getElementById('searchInput').value.toUpperCase();
    const startDateStr = document.getElementById('startDate').value;
    const endDateStr = document.getElementById('endDate').value;
    const tableContainer = document.getElementById('searchTableContainer');
    const rows = document.querySelectorAll('#tableBody tr');
    
    const isActive = searchText.length > 0 || startDateStr.length > 0 || endDateStr.length > 0;
    
    if (!isActive) {
        tableContainer.classList.add('hidden');
        document.getElementById('noRecordFound').classList.add('hidden');
        return;
    }

    let hasMatch = false;
    rows.forEach(row => {
        const releaseDateStr = row.cells[0].textContent.trim(); 
        const nameText = row.cells[1].textContent.toUpperCase();
        const refText = row.cells[2].textContent.toUpperCase();
        
        const rowDate = new Date(releaseDateStr);
        rowDate.setHours(0, 0, 0, 0);

        const filterStart = startDateStr ? new Date(startDateStr) : null;
        if(filterStart) filterStart.setHours(0,0,0,0);

        const filterEnd = endDateStr ? new Date(endDateStr) : null;
        if(filterEnd) filterEnd.setHours(0,0,0,0);

        let dateMatch = true;
        if (dateMode === 'single' && filterStart) {
            dateMatch = rowDate.getTime() === filterStart.getTime();
        } else if (dateMode === 'range' && filterStart && filterEnd) {
            dateMatch = rowDate >= filterStart && rowDate <= filterEnd;
        } else if (dateMode === 'range' && filterStart) {
            dateMatch = rowDate >= filterStart;
        }

        const textMatch = nameText.includes(searchText) || refText.includes(searchText);

        if (dateMatch && textMatch) {
            row.style.display = "";
            hasMatch = true;
        } else {
            row.style.display = "none";
        }
    });

    if (hasMatch) {
        tableContainer.classList.remove('hidden');
        document.getElementById('noRecordFound').classList.add('hidden');
    } else {
        tableContainer.classList.add('hidden');
        document.getElementById('noRecordFound').classList.remove('hidden');
    }
}

document.getElementById('searchInput').addEventListener('keyup', filterDashboard);
</script>

</body>
</html>