<?php 
// 1. Fix for "Headers already sent": Start output buffering immediately.
ob_start(); 
require_once('../includes/init.php');

// Map the connection from init.php to the $conn variable used in your queries
$conn = $loanConn;


// --- THE SEARCH BRIDGE (AJAX Handler) ---
if (isset($_GET['ajax_search'])) {
    // Clear buffers to prevent any HTML/whitespace from breaking the JSON output
    while (ob_get_level()) { ob_end_clean(); } 
    
    $search = $_GET['ajax_search'] ?? '';
    $term = "%$search%";
    
    try {
        // Updated Query using exact column names from your database screenshot
        $query = "SELECT first_name, last_name, reference_number, date_created 
                  FROM loans 
                  WHERE reference_number LIKE :s 
                  OR first_name LIKE :s 
                  OR last_name LIKE :s 
                  OR CONCAT(first_name, ' ', last_name) LIKE :s
                  LIMIT 10";
                  
        $stmt = $conn->prepare($query);
        $stmt->execute(['s' => $term]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($results);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
    exit; 
}

include('../includes/header.php'); 

// --- BACKEND DATA FETCHING ---

// 1. Overall Portfolio Totals
$portfolio_query = "SELECT COUNT(loan_id) as total_borrowers, SUM(principal_amount) as total_amount FROM loans WHERE status = 'active'";
$portfolio_stmt = $conn->prepare($portfolio_query);
$portfolio_stmt->execute();
$portfolio = $portfolio_stmt->fetch(PDO::FETCH_ASSOC);

// 2. GMS Commissions (Fixed to join car_loans and motor_loans)
$gms_query = "
    SELECT 
        COUNT(DISTINCT l.loan_id) as total_gms_borrowers, 
        SUM(l.dealer_incentive) as total_comm 
    FROM loans l
    WHERE l.loan_id IN (
        SELECT loan_id FROM car_loans WHERE gps_provider = 'GMS'
        UNION
        SELECT loan_id FROM motor_loans WHERE gps_provider = 'GMS'
    )";

$gms_stmt = $conn->prepare($gms_query);
$gms_stmt->execute();
$gms = $gms_stmt->fetch(PDO::FETCH_ASSOC);

// 3. New Loans List (Latest 3) - Uses your new first_name/last_name structure
$new_loans_query = "SELECT first_name, last_name, principal_amount FROM loans ORDER BY date_created DESC LIMIT 3";
$new_loans_stmt = $conn->prepare($new_loans_query);
$new_loans_stmt->execute();
$new_loans_res = $new_loans_stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. New Payments List (Latest 3)
$new_payments_query = "SELECT borrower_name, amount FROM payments ORDER BY payment_date DESC LIMIT 3";
$new_payments_stmt = $conn->prepare($new_payments_query);
$new_payments_stmt->execute();
$new_payments_res = $new_payments_stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Categorized Stats for the 6 Cards
$type_stats_query = "SELECT lt.loan_type_name, 
                    COUNT(l.loan_id) as b_count, 
                    SUM(l.principal_amount) as total_val 
                    FROM loan_types lt 
                    LEFT JOIN loans l ON lt.loan_type_id = l.loan_type_id
                    GROUP BY lt.loan_type_id";
$type_stats_stmt = $conn->prepare($type_stats_query);
$type_stats_stmt->execute();
$loan_data = [];
while($row = $type_stats_stmt->fetch(PDO::FETCH_ASSOC)) {
    $loan_data[$row['loan_type_name']] = $row;
}

// 6. Motor Specific Breakdown (2-Wheels vs 3-Wheels)
$motor_breakdown_query = "SELECT ml.type, COUNT(*) as count, SUM(l.principal_amount) as amt 
                         FROM motor_loans ml 
                         JOIN loans l ON ml.loan_id = l.loan_id 
                         GROUP BY ml.type";
$motor_stmt = $conn->prepare($motor_breakdown_query);
$motor_stmt->execute();
$motors = ['2-WHEELS' => ['count' => 0, 'amt' => 0], '3-WHEELS' => ['count' => 0, 'amt' => 0]];
while($m = $motor_stmt->fetch(PDO::FETCH_ASSOC)) { 
    $motors[$m['type']] = ['count' => $m['count'], 'amt' => $m['amt']]; 
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    body { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background-color: #5d0101; 
        color: #ffffff;
        min-height: 100vh;
        margin: 0;
        overflow-x: hidden;
    }

    #waveCanvas {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        z-index: -1;
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

    .animate-content {
        animation: contentFadeIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    @keyframes contentFadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .search-container {
        position: relative;
        z-index: 20; 
        transition: all 0.4s ease;
        width: 100%;
        max-width: 70%; 
    }

    .sidebar-active .search-container { max-width: 55%; }

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
    }

    .card-accent {
        position: absolute;
        top: 0; right: 0; width: 120px; height: 120px;
        background: radial-gradient(circle, var(--accent-color) 0%, transparent 70%);
        opacity: 0.15;
        filter: blur(20px);
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
        text-transform: uppercase;
    }

    .mockup-container {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 2rem;
        padding: 1.5rem;
    }

    .mockup-item {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        padding: 0.75rem 0;
    }
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
                            class="dark-input font-normal w-full pl-16 pr-6 py-5 rounded-3xl focus:outline-none text-base border-white/10" autocomplete="off">
                        <svg class="w-7 h-7 absolute left-5 top-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <div id="headerLogo" class="hidden lg:block absolute right-4 top-1/2 -translate-y-1/2 scale-100 opacity-100 pointer-events-none">
                    <img src="../assets/images/mlcircle.png" alt="Logo" class="w-64 h-64 object-contain drop-shadow-[0_0_40px_rgba(239,68,68,0.8)] brightness-110">
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
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gradient-to-r from-red-600 to-red-900">
                        <th class="px-8 py-5 text-xs font-black text-white uppercase tracking-widest">Date Released</th>
                        <th class="px-8 py-5 text-xs font-black text-white uppercase tracking-widest">Account Name</th>
                        <th class="px-8 py-5 text-xs font-black text-white uppercase tracking-widest">Reference</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="text-gray-300">
                    </tbody>
            </table>
            <div id="noRecordFound" class="hidden px-8 py-12 text-center text-gray-400 italic">
                No records found.
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="mockup-container glass-panel relative">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="stat-label text-red-500" style="font-size: 0.9rem;">New Loans</h3>
                    <div class="flex items-center justify-end gap-2">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
                        <span class="text-[10px] font-black text-red-500 tracking-widest">LIVE RECORD</span>
                    </div>
                </div>
                <div class="space-y-1">
                    <?php foreach($new_loans_res as $nl): ?>
                        <div class="mockup-item flex justify-between text-sm">
                            <span><?php echo htmlspecialchars($nl['first_name'] . ' ' . $nl['last_name']); ?></span>
                            <span class="font-bold">₱<?php echo number_format($nl['principal_amount'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-right mt-4">
                    <a href="all_loans.php" class="text-[10px] font-black text-red-400 hover:text-white transition-colors tracking-widest">SHOW MORE →</a>
                </div>
            </div>

            <div class="mockup-container glass-panel relative">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="stat-label text-amber-500" style="font-size: 0.9rem;">New Payments</h3>
                    <div class="flex items-center justify-end gap-2">
                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                        <span class="text-[10px] font-black text-amber-500 tracking-widest">LIVE RECORD</span>
                    </div>
                </div>
                <div class="space-y-1">
                    <?php foreach($new_payments_res as $np): ?>
                        <div class="mockup-item flex justify-between text-sm">
                            <span class="uppercase"><?php echo htmlspecialchars($np['borrower_name']); ?></span>
                            <span class="font-bold">₱<?php echo number_format($np['amount'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-right mt-4">
                    <a href="all_payments.php" class="text-[10px] font-black text-amber-400 hover:text-white transition-colors tracking-widest">SHOW MORE →</a>
                </div>
            </div>
        </div>

        <div class="glass-panel p-8 lg:p-12 rounded-[3rem] border-none relative mb-12 overflow-hidden">
            <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-12 divide-y md:divide-y-0 md:divide-x divide-white/10">
                <div class="space-y-8">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-6 bg-red-500 rounded-full"></span>
                        <p class="stat-label" style="font-size: 1.1rem;">Overall Portfolio</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <p class="stat-label text-red-500 mb-1">Total Borrowers</p>
                            <h2 class="text-4xl lg:text-5xl font-black tracking-tighter text-white"><?php echo number_format($portfolio['total_borrowers']); ?></h2>
                        </div>
                        <div>
                            <p class="stat-label text-red-500 mb-1">Total Loan Amounts</p>
                            <h2 class="text-4xl lg:text-5xl font-black tracking-tighter text-white">₱<?php echo number_format($portfolio['total_amount'], 2); ?></h2>
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
                            <p class="stat-label text-amber-500 mb-1">GMS Borrowers</p>
                            <h2 class="text-4xl lg:text-5xl font-black tracking-tighter text-white">
                                <?php echo number_format($gms['total_gms_borrowers']); ?>
                            </h2>
                        </div>
                        <div>
                            <p class="stat-label text-amber-500 mb-1">Commission Total</p>
                            <h2 class="text-4xl lg:text-5xl font-black tracking-tighter text-white">₱<?php echo number_format($gms['total_comm'], 2); ?></h2>
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
                    <div class="text-right"><p class="stat-label">Borrower</p><p class="text-2xl font-black text-red-500"><?php echo number_format($loan_data['Car Loan']['b_count'] ?? 0); ?></p></div>
                </div>
                <p class="stat-label">Total Car Loan</p>
                <h4 class="text-white text-4xl font-black mt-2 tracking-tighter">₱<?php echo number_format($loan_data['Car Loan']['total_val'] ?? 0, 2); ?></h4>
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
                        <p class="text-xs text-gray-400 mb-1 font-bold"><?php echo number_format($motors['2-WHEELS']['count']); ?> Borrowers</p>
                        <h4 class="text-white text-xl font-black tracking-tight">₱<?php echo number_format($motors['2-WHEELS']['amt'], 2); ?></h4>
                    </div>
                    <div class="pl-4">
                        <p class="stat-label text-blue-400 mb-1">3-Wheels</p>
                        <p class="text-xs text-gray-400 mb-1 font-bold"><?php echo number_format($motors['3-WHEELS']['count']); ?> Borrowers</p>
                        <h4 class="text-white text-xl font-black tracking-tight">₱<?php echo number_format($motors['3-WHEELS']['amt'], 2); ?></h4>
                    </div>
                </div>
            </div>

            <div class="stats-card p-8" style="--accent-color: #f43f5e;">
                <div class="card-accent"></div>
                <div class="flex justify-between items-start mb-8">
                    <div class="p-4 bg-rose-500/20 text-rose-400 rounded-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-width="2"/></svg></div>
                    <div class="text-right"><p class="stat-label">Borrower</p><p class="text-2xl font-black text-rose-400"><?php echo number_format($loan_data['REAL-ESTATE LOAN']['b_count'] ?? 0); ?></p></div>
                </div>
                <p class="stat-label">Real Estate Loan</p>
                <h4 class="text-white text-4xl font-black mt-2 tracking-tighter">₱<?php echo number_format($loan_data['REAL-ESTATE LOAN']['total_val'] ?? 0, 2); ?></h4>
            </div>

            <div class="stats-card p-8" style="--accent-color: #f59e0b;">
                <div class="card-accent"></div>
                <div class="flex justify-between items-start mb-8">
                    <div class="p-4 bg-amber-500/20 text-amber-400 rounded-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke-width="2"/></svg></div>
                    <div class="text-right"><p class="stat-label">Borrower</p><p class="text-2xl font-black text-amber-400"><?php echo number_format($loan_data['COMMERCIAL LOAN']['b_count'] ?? 0); ?></p></div>
                </div>
                <p class="stat-label">Commercial Loan</p>
                <h4 class="text-white text-4xl font-black mt-2 tracking-tighter">₱<?php echo number_format($loan_data['COMMERCIAL LOAN']['total_val'] ?? 0, 2); ?></h4>
            </div>

            <div class="stats-card p-8" style="--accent-color: #10b981;">
                <div class="card-accent"></div>
                <div class="flex justify-between items-start mb-8">
                    <div class="p-4 bg-emerald-500/20 text-emerald-400 rounded-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" stroke-width="2"/></svg></div>
                    <div class="text-right"><p class="stat-label">Borrower</p><p class="text-2xl font-black text-emerald-400"><?php echo number_format($loan_data['SALARY LOAN']['b_count'] ?? 0); ?></p></div>
                </div>
                <p class="stat-label">Total Salary Loan</p>
                <h4 class="text-white text-4xl font-black mt-2 tracking-tighter">₱<?php echo number_format($loan_data['SALARY LOAN']['total_val'] ?? 0, 2); ?></h4>
            </div>

            <div class="stats-card p-8" style="--accent-color: #8b5cf6;">
                <div class="card-accent"></div>
                <div class="flex justify-between items-start mb-8">
                    <div class="p-4 bg-purple-500/20 text-purple-400 rounded-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-width="2"/></svg></div>
                    <div class="text-right"><p class="stat-label">Borrower</p><p class="text-2xl font-black text-purple-400"><?php echo number_format($loan_data['TRUCK LOAN']['b_count'] ?? 0); ?></p></div>
                </div>
                <p class="stat-label">Truck Loan</p>
                <h4 class="text-white text-4xl font-black mt-2 tracking-tighter">₱<?php echo number_format($loan_data['TRUCK LOAN']['total_val'] ?? 0, 2); ?></h4>
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

<?php if (isset($_SESSION['force_password_change']) && $_SESSION['force_password_change'] === true): ?>
<style>
    /* 1. Override the inline style="display: none;" on the modal */
    #changePasswordModal {
        display: flex !important; 
    }
    
    /* 2. Prevent the user from scrolling the dashboard in the background */
    body {
        overflow: hidden; 
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        /* 3. Prevent the user from hitting the 'Escape' key to close the modal */
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                event.preventDefault();
            }
        });
    });
</script>
<?php endif; ?>

<script>
// --- Wave Animation (Kept from original) ---
const canvas = document.getElementById('waveCanvas');
const ctxWave = canvas.getContext('2d');
function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
window.addEventListener('resize', resize);
resize();
let offset = 0;
function drawWaves() {
    ctxWave.fillStyle = '#220303'; ctxWave.fillRect(0, 0, canvas.width, canvas.height);
    for (let i = 0; i < 3; i++) {
        ctxWave.fillStyle = `rgba(200, 0, 0, ${0.1 + (i * 0.05)})`;
        ctxWave.beginPath(); ctxWave.moveTo(0, canvas.height);
        for (let x = 0; x <= canvas.width; x++) {
            let y = Math.sin(x * 0.003 + offset + (i * 2)) * 50 + (canvas.height - (i * 100) - 100);
            ctxWave.lineTo(x, y);
        }
        ctxWave.lineTo(canvas.width, canvas.height); ctxWave.fill();
    }
    offset += 0.01; requestAnimationFrame(drawWaves);
}
drawWaves();

// --- Chart.js Configuration ---
const ctx = document.getElementById('loanChart').getContext('2d');
const createGradient = (color) => {
    const g = ctx.createLinearGradient(0, 0, 0, 400);
    g.addColorStop(0, color + '44'); g.addColorStop(1, color + '00');
    return g;
};

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['2022', '2023', '2024', '2025', '2026'],
        datasets: [
            { label: 'Car', data: [150, 220, 310, 420, 500], borderColor: '#ef4444', backgroundColor: createGradient('#ef4444'), fill: true, tension: 0.4 },
            { label: 'Motor', data: [80, 110, 145, 185, 230], borderColor: '#3b82f6', backgroundColor: createGradient('#3b82f6'), fill: true, tension: 0.4 },
            { label: 'Real Estate', data: [350, 430, 510, 630, 780], borderColor: '#f43f5e', backgroundColor: createGradient('#f43f5e'), fill: true, tension: 0.4 }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8', font: { weight: 'bold' } } } },
        scales: {
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { weight: 'bold' } } },
            x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { weight: 'bold' } } }
        }
    }
});

// --- Dashboard Filtering Logic ---
function filterDashboard() {
    const searchInput = document.getElementById('searchInput');
    const tableContainer = document.getElementById('searchTableContainer');
    const tableBody = document.getElementById('tableBody');
    const noRecordDiv = document.getElementById('noRecordFound');

    if (!searchInput || !tableContainer || !tableBody) return;

    const value = searchInput.value.trim();

    if (value.length < 2) {
        tableContainer.classList.add('hidden');
        return;
    }

    tableContainer.classList.remove('hidden');

    fetch(`dashboard.php?ajax_search=${encodeURIComponent(value)}&v=${Date.now()}`)
        .then(response => response.json())
        .then(data => {
            tableBody.innerHTML = ''; 

            if (data.length > 0) {
                noRecordDiv.classList.add('hidden');
                data.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.className = "border-b border-white/5 hover:bg-white/5 transition-colors";
                    tr.innerHTML = `
                        <td class="px-8 py-4 text-sm">${row.date_created || 'N/A'}</td>
                        <td class="px-8 py-4 text-sm font-bold text-white">
                            ${row.first_name} ${row.last_name}
                        </td>
                        <td class="px-8 py-4 text-sm text-red-400 font-mono">${row.reference_number}</td>
                    `;
                    tableBody.appendChild(tr);
                });
            } else {
                noRecordDiv.classList.remove('hidden');
            }
        })
        .catch(err => console.error('AJAX Error:', err));
}
</script>