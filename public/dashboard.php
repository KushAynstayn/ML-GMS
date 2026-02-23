<?php include('../includes/header.php'); ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    body { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background: linear-gradient(135deg, #b91c1c 0%, #111827 100%); 
        color: #ffffff;
        min-height: 100vh;
        margin: 0;
    }

    /* --- New Smooth Transition Animation --- */
    @keyframes contentFadeIn {
        from { 
            opacity: 0; 
            transform: translateY(15px); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0); 
        }
    }

    .animate-content {
        animation: contentFadeIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }
    
    .glass-panel {
        background: rgba(255, 255, 255, 0.07);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
    }

    .stats-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.15);
        border-color: #ef4444;
    }

    .btn-active-red {
        background: #ef4444 !important;
        color: white !important;
        border: none !important;
        font-weight: 800 !important;
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
    }

    .glass-table-container {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 1.5rem;
        overflow: hidden;
    }

    .table-crimson-dark thead {
        background: #ef4444;
    }

    .stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 700;
        color: #f3f4f6;
    }

    .dark-input {
        background: rgba(255, 255, 255, 0.12) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
    }

    .icon-box {
        padding: 12px;
        border-radius: 14px;
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
    }
</style>

<div class="flex overflow-hidden" style="min-height: calc(100vh - 64px); flex: 1;">
    <?php include('../includes/sidebar.php'); ?>
    <main class="flex-1 p-8 lg:p-10 overflow-y-auto animate-content">
        <header class="relative glass-panel p-10 rounded-[2.5rem] mb-10 overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-4xl font-extrabold text-white tracking-tight">Dashboard Overview</h2>
                <p class="text-gray-200 font-medium mt-2">Insights and performance overview</p>

                <div class="mt-8 space-y-6">
                    <div class="max-w-2xl relative">
                        <input type="text" id="searchInput" onkeyup="filterDashboard()" placeholder="Search account or reference..." 
                            class="dark-input w-full pl-14 pr-4 py-4 rounded-2xl focus:outline-none focus:ring-2 focus:ring-red-500 transition-all text-sm">
                        <svg class="w-6 h-6 absolute left-4 top-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <button onclick="setDateMode('single')" id="btnSingle" class="px-8 py-3 text-xs font-bold rounded-full border border-white/20 text-white hover:bg-white/10 transition-all btn-active-red">SINGLE DATE</button>
                        <button onclick="setDateMode('range')" id="btnRange" class="px-8 py-3 text-xs font-bold rounded-full border border-white/20 text-white hover:bg-white/10 transition-all">SELECT RANGE</button>
                        
                        <div class="flex items-center gap-3">
                            <div class="flex items-center px-6 py-3 dark-input rounded-full">
                                <span id="dateLabel" class="text-[10px] font-black text-red-400 uppercase mr-3 tracking-widest">Date</span>
                                <input type="date" id="startDate" onchange="filterDashboard()" class="bg-transparent text-sm font-bold text-white focus:outline-none cursor-pointer [color-scheme:dark]">
                            </div>

                            <div id="toDateContainer" class="hidden flex items-center px-6 py-3 dark-input rounded-full">
                                <span class="text-[10px] font-black text-red-400 uppercase mr-3 tracking-widest">To</span>
                                <input type="date" id="endDate" onchange="filterDashboard()" class="bg-transparent text-sm font-bold text-white focus:outline-none cursor-pointer [color-scheme:dark]">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hidden lg:block absolute right-10 top-1/2 -translate-y-1/2">
                    <img src="../assets/images/mlcircle.png" alt="Logo" class="w-48 h-48 object-contain opacity-80">
                </div>
            </div>  
        </header>

        <div id="searchTableContainer" class="hidden glass-table-container mb-10 transition-all duration-500">
            <table class="w-full text-left table-crimson-dark" id="loansTable">
                <thead>
                    <tr>
                        <th class="px-8 py-5 text-xs font-bold text-white uppercase tracking-wider">Date Released</th>
                        <th class="px-8 py-5 text-xs font-bold text-white uppercase tracking-wider">Account Name</th>
                        <th class="px-8 py-5 text-xs font-bold text-white uppercase tracking-wider">Reference</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="text-gray-100">
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="px-8 py-5 font-medium text-sm text-gray-400">08/08/2025</td>
                        <td class="px-8 py-5 font-bold text-white">Leah Faye Genson</td>
                        <td class="px-8 py-5">
                            <span class="px-5 py-1.5 border border-red-500 text-red-400 rounded-full text-xs font-bold">MCRVMWQTW</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="noRecordFound" class="hidden mb-10 p-6 glass-panel rounded-2xl flex items-center gap-4 text-red-400 border-red-500/30">
            <div class="p-2 bg-red-500/20 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <span class="font-bold text-lg">No records found matching your selection.</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="stats-card p-8 rounded-[2.5rem]">
                <div class="flex items-center justify-between mb-6">
                    <div class="icon-box">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-[10px] font-bold py-1 px-3 bg-green-500 text-white rounded-lg">UP 12%</span>
                </div>
                <p class="stat-label">Total Volume</p>
                <h4 class="text-white text-3xl font-extrabold mt-2">$12,000</h4>
            </div>

            <div class="stats-card p-8 rounded-[2.5rem]">
                <div class="flex items-center justify-between mb-6">
                    <div class="icon-box" style="color: #60a5fa; background: rgba(96, 165, 250, 0.2);">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
                <p class="stat-label">Active Users</p>
                <h4 class="text-white text-3xl font-extrabold mt-2">482,000</h4>
            </div>

            <div class="stats-card p-8 rounded-[2.5rem]">
                <div class="flex items-center justify-between mb-6">
                    <div class="icon-box" style="color: #fbbf24; background: rgba(251, 191, 36, 0.2);">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <p class="stat-label">Pending Trans</p>
                <h4 class="text-white text-3xl font-extrabold mt-2">$52,000</h4>
            </div>

            <div class="stats-card p-8 rounded-[2.5rem]">
                <div class="flex items-center justify-between mb-6">
                    <div class="icon-box">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <p class="stat-label">Released Today</p>
                <h4 class="text-white text-3xl font-extrabold mt-2">$10,000</h4>
            </div>
        </div>
    </main>
</div>

<script>
let currentMode = 'single';

function setDateMode(mode) {
    currentMode = mode;
    const btnSingle = document.getElementById('btnSingle');
    const btnRange = document.getElementById('btnRange');
    const toDateContainer = document.getElementById('toDateContainer');
    const dateLabel = document.getElementById('dateLabel');

    if (mode === 'single') {
        btnSingle.classList.add('btn-active-red');
        btnRange.classList.remove('btn-active-red');
        toDateContainer.classList.add('hidden');
        dateLabel.innerText = 'Date';
    } else {
        btnRange.classList.add('btn-active-red');
        btnSingle.classList.remove('btn-active-red');
        toDateContainer.classList.remove('hidden');
        dateLabel.innerText = 'From';
    }
    filterDashboard();
}

function filterDashboard() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const tableContainer = document.getElementById('searchTableContainer');
    const noRecordFound = document.getElementById('noRecordFound');
    const tableRows = document.querySelectorAll('#tableBody tr');

    let visibleCount = 0;
    const isSearching = searchInput.length > 0 || startDate !== '';

    tableRows.forEach(row => {
        const dateCell = row.cells[0].innerText; // MM/DD/YYYY format from your static example
        const nameCell = row.cells[1].innerText.toLowerCase();
        const refCell = row.cells[2].innerText.toLowerCase();
        
        // Simple date parsing for comparison
        const rowDate = new Date(dateCell);
        const start = startDate ? new Date(startDate) : null;
        const end = endDate ? new Date(endDate) : null;

        let dateMatch = true;
        if (currentMode === 'single' && start) {
            dateMatch = rowDate.toDateString() === start.toDateString();
        } else if (currentMode === 'range' && start && end) {
            dateMatch = rowDate >= start && rowDate <= end;
        }

        const textMatch = nameCell.includes(searchInput) || refCell.includes(searchInput);

        if (dateMatch && textMatch) {
            row.style.display = "";
            visibleCount++;
        } else {
            row.style.display = "none";
        }
    });

    // Toggle visibility logic
    if (isSearching) {
        if (visibleCount > 0) {
            tableContainer.classList.remove('hidden');
            noRecordFound.classList.add('hidden');
        } else {
            tableContainer.classList.add('hidden');
            noRecordFound.classList.remove('hidden');
        }
    } else {
        tableContainer.classList.add('hidden');
        noRecordFound.classList.add('hidden');
    }
}
</script>