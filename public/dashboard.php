<?php 
// 1. Fix for "Headers already sent": Start output buffering immediately.
ob_start(); 
include('../includes/header.php'); 
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    /* Background and Wave Styles */
    body { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background-color: #5d0101; /* Solid base color */        color: #ffffff;
        min-height: 100vh;
        margin: 0;
        overflow-x: hidden;
    }

    #waveCanvas {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1; /* Keeps it behind all content */
    }

    .glass-panel {
        background: rgba(255, 0, 0, 0.02);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
    }

    .glass-table-container {
        background: rgba(75, 2, 2, 0.8); 
        backdrop-filter: blur(15px);
        border: 1px solid rgba(239, 68, 68, 0.1);
        border-radius: 1.5rem;
        overflow: hidden;
    }

    @keyframes contentFadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-content {
        animation: contentFadeIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    .header-content-inner {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .search-container {
        position: relative;
        z-index: 20; 
        transition: all 0.4s ease;
        width: 100%;
        max-width: 70%; 
    }

    .sidebar-active .search-container {
        max-width: 55%; 
    }

    @media (max-width: 1024px) {
        .search-container { max-width: 100%; }
    }

    .stats-card {
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 2.5rem;
    }
    
    .stats-card:hover {
        transform: translateY(-8px) scale(1.02);
        border-color: rgba(255, 255, 255, 0.3);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
    }

    .card-accent {
        position: absolute;
        top: 0; right: 0; width: 120px; height: 120px;
        background: radial-gradient(circle, var(--accent-color) 0%, transparent 70%);
        opacity: 0.15;
        filter: blur(20px);
    }

    .btn-active-red {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%) !important;
        color: white !important;
        border: none !important;
        box-shadow: 0 8px 15px rgba(239, 68, 68, 0.3);
    }

    .table-crimson-dark thead { 
        background: linear-gradient(90deg, #ef4444 0%, #991b1b 100%); 
    }

    .stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        font-weight: 800;
        color: rgba(255, 255, 255, 0.5);
    }

    .dark-input {
        background: rgba(0, 0, 0, 0.2) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
    }

    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #ef4444; }
</style>

<canvas id="waveCanvas"></canvas>

<div id="layoutContainer" class="flex overflow-hidden" style="min-height: calc(100vh - 64px); flex: 1;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main class="flex-1 p-8 lg:p-12 overflow-y-auto animate-content">
        
        <header class="relative bg-white/5 border border-white/10 p-10 rounded-[3rem] mb-8 overflow-hidden">
            <div class="header-content-inner">
                <div class="flex items-center gap-4 mb-2">
                    <span class="h-1 w-12 bg-red-500 rounded-full"></span>
                    <p class="text-red-500 font-bold tracking-widest text-sm uppercase">M LHUILLIER</p>
                </div>
                <h2 class="text-5xl font-black text-white tracking-tight">Dashboard</h2>
                
                <div class="search-container mt-10 space-y-6">
                    <div class="relative group">
                        <input type="text" id="searchInput" onkeyup="filterDashboard()" placeholder="Search by borrower name or reference ID..." 
                            class="dark-input font-normal w-full pl-16 pr-6 py-5 rounded-3xl focus:outline-none text-base border-white/10">
                        <svg class="w-7 h-7 absolute left-5 top-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <div class="flex flex-wrap items-center gap-6">
                        <div class="flex p-1.5 bg-black/30 rounded-full border border-white/5">
                            <button onclick="setDateMode('single')" id="btnSingle" class="px-8 py-3 text-xs font-black rounded-full transition-all btn-active-red">SINGLE</button>
                            <button onclick="setDateMode('range')" id="btnRange" class="px-8 py-3 text-xs font-black rounded-full text-gray-400 hover:text-white transition-all">RANGE</button>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="flex items-center px-6 py-3 dark-input rounded-2xl">
                                <span id="dateLabel" class="text-[10px] font-black text-red-500 uppercase mr-4 tracking-tighter">Start</span>
                                <input type="date" id="startDate" onchange="filterDashboard()" class="bg-transparent text-sm font-bold text-white focus:outline-none [color-scheme:dark]">
                            </div>
                            <div id="toDateContainer" class="hidden flex items-center px-6 py-3 dark-input rounded-2xl">
                                <span class="text-[10px] font-black text-red-500 uppercase mr-4 tracking-tighter">End</span>
                                <input type="date" id="endDate" onchange="filterDashboard()" class="bg-transparent text-sm font-bold text-white focus:outline-none [color-scheme:dark]">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="headerLogo" class="hidden lg:block absolute right-4 top-1/2 -translate-y-1/2 scale-100 opacity-100 pointer-events-none">
                    <img src="../assets/images/mlcircle.png" alt="Logo" 
                        class="w-64 h-64 object-contain drop-shadow-[0_0_40px_rgba(239,68,68,0.8)] brightness-110">
                </div>
            </div>  
        </header>

        <div id="searchTableContainer" class="hidden glass-table-container mb-12 transition-all duration-500">
            <div class="p-6 border-b border-white/5">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    Live Filter Results
                </h3>
            </div>
            <table class="w-full text-left table-crimson-dark" id="loansTable">
                <thead>
                    <tr>
                        <th class="px-8 py-5 text-xs font-black text-white uppercase tracking-widest">Date Released</th>
                        <th class="px-8 py-5 text-xs font-black text-white uppercase tracking-widest">Account Name</th>
                        <th class="px-8 py-5 text-xs font-black text-white uppercase tracking-widest">Reference</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="text-gray-300">
                    <tr class="border-b border-white/5 hover:bg-white/10 transition-colors">
                        <td class="px-8 py-5 font-medium text-sm">08/08/2025</td>
                        <td class="px-8 py-5 font-bold text-white">Leah Faye Genson</td>
                        <td class="px-8 py-5">
                            <span class="px-4 py-1.5 bg-red-500/10 border border-red-500/50 text-red-400 rounded-lg text-xs font-black tracking-widest">MCRVMWQTW</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="glass-panel p-8 lg:p-12 rounded-[3rem] border-none relative mb-12 overflow-hidden">
            <div class="absolute top-0 left-0 w-1/2 h-full bg-gradient-to-br from-red-600/10 to-transparent pointer-events-none"></div>
            <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-bl from-amber-600/10 to-transparent pointer-events-none"></div>
            
            <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-12 divide-y md:divide-y-0 md:divide-x divide-white/10">
                <div class="space-y-8">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-6 bg-red-500 rounded-full"></span>
                        <p class="stat-label" style="font-size: 1.1rem;">Overall Portfolio</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <p class="stat-label text-red-500 mb-1">Total Borrowers</p>
                            <h2 class="text-4xl lg:text-5xl font-black tracking-tighter text-white">1,248,392</h2>
                        </div>
                        <div>
                            <p class="stat-label text-red-500 mb-1">Total Loan Amount</p>
                            <h2 class="text-4xl lg:text-5xl font-black tracking-tighter text-white">₱4,290,150</h2>
                        </div>
                    </div>
                </div>

                <div class="md:pl-12 pt-8 md:pt-0 space-y-8">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-6 bg-amber-500 rounded-full"></span>
                        <p class="stat-label" style="font-size: 1.1rem;">GMS Commissions</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <p class="stat-label text-amber-500 mb-1">GMS Users</p>
                            <h2 class="text-4xl lg:text-5xl font-black tracking-tighter text-white">42,850</h2>
                        </div>
                        <div>
                            <p class="stat-label text-amber-500 mb-1">Commission Total</p>
                            <h2 class="text-4xl lg:text-5xl font-black tracking-tighter text-white">₱158,400</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <div class="stats-card p-8" style="--accent-color: #ef4444;">
                <div class="card-accent"></div>
                <div class="flex justify-between items-start mb-8">
                    <div class="p-4 bg-red-500/20 text-red-400 rounded-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1" stroke-width="2"/></svg></div>
                    <div class="text-right"><p class="stat-label">Borrower</p><p class="text-2xl font-black text-red-500">12,402</p></div>
                </div>
                <p class="stat-label">Total Car Loan</p>
                <h4 class="text-white text-4xl font-black mt-2 tracking-tighter">₱420,000</h4>
            </div>

            <div class="stats-card p-8" style="--accent-color: #3b82f6;">
                <div class="card-accent"></div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 bg-blue-500/20 text-blue-400 rounded-xl"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                    <p class="stat-label" style="font-size: 1.1rem;">Total Motor Loans</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 divide-x divide-white/10">
                    <div class="pr-4">
                        <p class="stat-label text-blue-400 mb-1">2-Wheels</p>
                        <p class="text-xs text-gray-400 mb-1 font-bold">52,105 Borrowers</p>
                        <h4 class="text-white text-xl font-black tracking-tight">₱115,000</h4>
                    </div>
                    <div class="pl-4">
                        <p class="stat-label text-blue-400 mb-1">3-Wheels</p>
                        <p class="text-xs text-gray-400 mb-1 font-bold">33,105 Borrowers</p>
                        <h4 class="text-white text-xl font-black tracking-tight">₱70,000</h4>
                    </div>
                </div>
            </div>

            <div class="stats-card p-8" style="--accent-color: #f59e0b;">
                <div class="card-accent"></div>
                <div class="flex justify-between items-start mb-8">
                    <div class="p-4 bg-amber-500/20 text-amber-400 rounded-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke-width="2"/></svg></div>
                    <div class="text-right"><p class="stat-label">Borrower</p><p class="text-2xl font-black text-amber-400">4,120</p></div>
                </div>
                <p class="stat-label">Total Home Loan</p>
                <h4 class="text-white text-4xl font-black mt-2 tracking-tighter">₱850,000</h4>
            </div>

            <div class="stats-card p-8" style="--accent-color: #10b981;">
                <div class="card-accent"></div>
                <div class="flex justify-between items-start mb-8">
                    <div class="p-4 bg-emerald-500/20 text-emerald-400 rounded-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" stroke-width="2"/></svg></div>
                    <div class="text-right"><p class="stat-label">Borrower</p><p class="text-2xl font-black text-emerald-400">214,500</p></div>
                </div>
                <p class="stat-label">Total Salary Loan</p>
                <h4 class="text-white text-4xl font-black mt-2 tracking-tighter">₱120,000</h4>
            </div>

            <div class="stats-card p-8" style="--accent-color: #8b5cf6;">
                <div class="card-accent"></div>
                <div class="flex justify-between items-start mb-8">
                    <div class="p-4 bg-purple-500/20 text-purple-400 rounded-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-width="2"/></svg></div>
                    <div class="text-right"><p class="stat-label">Borrower</p><p class="text-2xl font-black text-purple-400">31,040</p></div>
                </div>
                <p class="stat-label">Personal Property</p>
                <h4 class="text-white text-4xl font-black mt-2 tracking-tighter">₱245,000</h4>
            </div>

            <div class="stats-card p-8" style="--accent-color: #f43f5e;">
                <div class="card-accent"></div>
                <div class="flex justify-between items-start mb-8">
                    <div class="p-4 bg-rose-500/20 text-rose-400 rounded-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-width="2"/></svg></div>
                    <div class="text-right"><p class="stat-label">Borrower</p><p class="text-2xl font-black text-rose-400">1,050</p></div>
                </div>
                <p class="stat-label">Real Estate Loan</p>
                <h4 class="text-white text-4xl font-black mt-2 tracking-tighter">₱630,000</h4>
            </div>
        </div>

        <div class="glass-panel p-10 rounded-[3rem] mb-10">
            <h3 class="text-2xl font-black mb-10 flex items-center gap-3">
                <span class="w-3 h-8 bg-red-500 rounded-full"></span>
                Portfolio Growth Analysis
            </h3>
            <div style="height: 450px;">
                <canvas id="loanChart"></canvas>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/modals/change_password_modal.php'; ?>

<script>
// --- Wave Animation Logic ---
const canvas = document.getElementById('waveCanvas');
const ctxWave = canvas.getContext('2d');

function resize() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}
window.addEventListener('resize', resize);
resize();

let offset = 0;
function drawWaves() {
    ctxWave.fillStyle = '#220303'; // Base dark red
    ctxWave.fillRect(0, 0, canvas.width, canvas.height);
    
    ctxWave.beginPath();
    ctxWave.moveTo(0, canvas.height);
    
    // Draw 3 layers of waves
    for (let i = 0; i < 3; i++) {
        ctxWave.fillStyle = `rgba(200, 0, 0, ${0.1 + (i * 0.05)})`;
        ctxWave.beginPath();
        ctxWave.moveTo(0, canvas.height);
        for (let x = 0; x <= canvas.width; x++) {
            let y = Math.sin(x * 0.003 + offset + (i * 2)) * 50 + (canvas.height - (i * 100) - 100);
            ctxWave.lineTo(x, y);
        }
        ctxWave.lineTo(canvas.width, canvas.height);
        ctxWave.fill();
    }
    
    offset += 0.01;
    requestAnimationFrame(drawWaves);
}
drawWaves();
// --- End Wave Animation ---

// Existing Dashboard Logic
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('aside');
    const layout = document.getElementById('layoutContainer');
    if (sidebar && layout) {
        const checkSidebar = () => {
            const isOpen = sidebar.classList.contains('active') || sidebar.classList.contains('open') || sidebar.offsetWidth > 100;
            if (isOpen) layout.classList.add('sidebar-active');
            else layout.classList.remove('sidebar-active');
        };
        const observer = new MutationObserver(checkSidebar);
        observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
        checkSidebar(); 
    }
});

const ctx = document.getElementById('loanChart').getContext('2d');
const createGradient = (color) => {
    const g = ctx.createLinearGradient(0, 0, 0, 400);
    g.addColorStop(0, color + '44');
    g.addColorStop(1, color + '00');
    return g;
};

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['2022', '2023', '2024', '2025', '2026'],
        datasets: [
            { label: 'Car', data: [150, 220, 310, 420, 500], borderColor: '#ef4444', backgroundColor: createGradient('#ef4444'), fill: true, tension: 0.4 },
            { label: 'Motor', data: [80, 110, 145, 185, 230], borderColor: '#3b82f6', backgroundColor: createGradient('#3b82f6'), fill: true, tension: 0.4 },
            { label: 'Home', data: [500, 620, 740, 850, 980], borderColor: '#f59e0b', backgroundColor: createGradient('#f59e0b'), fill: true, tension: 0.4 },
            { label: 'Salary', data: [40, 65, 90, 120, 160], borderColor: '#10b981', backgroundColor: createGradient('#10b981'), fill: true, tension: 0.4 },
            { label: 'Property', data: [120, 160, 205, 245, 310], borderColor: '#8b5cf6', backgroundColor: createGradient('#8b5cf6'), fill: true, tension: 0.4 },
            { label: 'Real Estate', data: [350, 430, 510, 630, 780], borderColor: '#f43f5e', backgroundColor: createGradient('#f43f5e'), fill: true, tension: 0.4 }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8', font: { weight: 'bold' } } } },
        scales: {
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { weight: 'bold' } } },
            x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { weight: 'bold' } } }
        }
    }
});

let currentMode = 'single';
function setDateMode(mode) {
    currentMode = mode;
    const btnSingle = document.getElementById('btnSingle');
    const btnRange = document.getElementById('btnRange');
    const toDateContainer = document.getElementById('toDateContainer');

    if (mode === 'single') {
        btnSingle.className = "px-8 py-3 text-xs font-black rounded-full transition-all btn-active-red";
        btnRange.className = "px-8 py-3 text-xs font-black rounded-full text-gray-400 hover:text-white transition-all";
        toDateContainer.classList.add('hidden');
        document.getElementById('dateLabel').innerText = 'Start';
    } else {
        btnRange.className = "px-8 py-3 text-xs font-black rounded-full transition-all btn-active-red";
        btnSingle.className = "px-8 py-3 text-xs font-black rounded-full text-gray-400 hover:text-white transition-all";
        toDateContainer.classList.remove('hidden');
        document.getElementById('dateLabel').innerText = 'From';
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
        const dateCell = row.cells[0].innerText;
        const nameCell = row.cells[1].innerText.toLowerCase();
        const refCell = row.cells[2].innerText.toLowerCase();
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

document.addEventListener("DOMContentLoaded", function () {
    const forceChange = <?php echo isset($_SESSION['force_password_change']) ? 'true' : 'false'; ?>;
    const modal = document.getElementById("changePasswordModal");

    if (forceChange && modal) {
        modal.style.display = "flex";
    }

    // Optional: block ESC so user cannot bypass password change
    document.addEventListener("keydown", function(e) {
        if (forceChange && e.key === "Escape") {
            e.preventDefault();
        }
    });
});

</script>