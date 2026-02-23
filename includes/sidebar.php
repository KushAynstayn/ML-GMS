<?php
require_once __DIR__ . '/../includes/init.php';
$current_page = basename($_SERVER['PHP_SELF']);

$u_type = $_SESSION['user_type'] ?? 'user';
$prefix = ($u_type == 'admin') ? 'admin' : 'user';
$display_name = $_SESSION[$prefix.'_name'] ?? 'Unknown User';
$display_role = ($u_type == 'admin') ? 'Administrator' : 'Standard User';

$menu_items = [
    ['file' => 'dashboard.php', 'label' => 'Dashboard', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z', 'admin_only' => false],
    ['file' => 'add_loan.php', 'label' => 'Add Loan', 'icon' => 'M12 4v16m8-8H4', 'admin_only' => false],
    ['file' => 'all_loans.php', 'label' => 'All Loans', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'admin_only' => false],
    ['file' => 'gms_commission.php', 'label' => 'GMS Commissions', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'admin_only' => false],
    ['file' => 'collection_report.php', 'label' => 'Collection Report', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'admin_only' => false],
    ['file' => 'user_management.php', 'label' => 'User Management', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'admin_only' => true]
];
?>

<aside id="sidebar" class="z-20 w-16 hover:w-64 transition-all duration-300 ease-in-out bg-white border-r border-gray-100 flex flex-col justify-between py-6 relative group shrink-0 shadow-[4px_0_15px_rgba(0,0,0,0.05)]">
    <div>
        <div class="px-5 flex items-center h-10 mb-2 relative">
            <div class="flex items-center min-w-[32px]">
                <img src="../assets/images/mlhuillier-red.png" alt="Logo" class="w-6 h-6 object-contain grayscale-[0.2]">
                <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400&display=swap" rel="stylesheet">
                <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 sidebar-text ml-4 text-[#8b1a1a] font-['Cinzel'] text-lg tracking-wider">
                    ML LOANS
                </span>
            </div>
            <button id="logo-lock-btn" class="absolute right-2 opacity-0 group-hover:opacity-100 transition-opacity p-1.5 hover:bg-gray-50 rounded-md text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>

        <div class="mx-4 mb-4 border-b border-gray-50"></div>

        <nav class="px-3 space-y-1">
            <?php foreach ($menu_items as $item): 
                if ($item['admin_only'] && $u_type !== 'admin') continue;
                $isActive = ($current_page === $item['file']);
            ?>
                <a href="<?php echo $item['file']; ?>" 
                class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-all duration-200 group/item <?php echo $isActive ? 'bg-slate-50 text-red-600 font-medium' : 'text-slate-500 hover:bg-gray-50 hover:text-slate-900'; ?>">
                    <div class="flex items-center justify-center min-w-[20px]">
                        <svg class="w-5 h-5 <?php echo $isActive ? 'text-red-600' : 'text-slate-400 group-hover/item:text-slate-600'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="<?php echo $item['icon']; ?>"></path>
                        </svg>
                    </div>
                    <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity sidebar-text text-sm">
                        <?php echo $item['label']; ?>
                    </span>
                    <?php if($isActive): ?>
                        <div class="absolute left-0 w-1 h-6 bg-red-600 rounded-r-full"></div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>

            <div class="my-4 border-t border-gray-50 mx-2"></div>

            <a href="../actions/logout.php" class="flex items-center gap-4 px-3 py-2 text-slate-400 hover:bg-red-50 hover:text-red-600 rounded-lg transition-all group/logout">
                <div class="flex items-center justify-center min-w-[20px]">
                    <svg class="w-5 h-5 transition-colors group-hover/logout:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity sidebar-text text-sm font-normal">Logout</span>
            </a>
        </nav>
    </div>

    <div class="px-5">
        <div class="flex items-center gap-3 py-3 border-t border-gray-50 transition-all duration-300">
            <div class="w-8 h-8 rounded-full bg-slate-100 flex-shrink-0 flex items-center justify-center text-slate-500">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path>
                </svg>
            </div>
            <div class="flex flex-col whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity sidebar-text overflow-hidden">
                <span class="text-[13px] font-semibold text-slate-700 leading-tight">
                    <?php echo htmlspecialchars($display_name); ?>
                </span>
                <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">
                    <?php echo $display_role; ?>
                </span>
            </div>
        </div>
    </div>
</aside>

<style>
    #sidebar { transition: width 350ms cubic-bezier(0.4, 0, 0.2, 1) !important; }
    .sidebar-text { transition: opacity 250ms ease !important; }

    #sidebar:hover .sidebar-text,
    .sidebar-locked .sidebar-text {
        opacity: 1 !important;
    }

    .sidebar-locked #logo-lock-btn {
        opacity: 1 !important;
        color: #dc2626;
        background-color: #fef2f2;
    }

    #sidebar:not(:hover):not(.sidebar-locked) .sidebar-text {
        opacity: 0 !important;
        pointer-events: none;
    }

    .sidebar-locked { width: 16rem !important; }
    #sidebar:hover { width: 16rem; }
</style>

<script>
    const sidebar = document.getElementById('sidebar');
    const lockBtn = document.getElementById('logo-lock-btn');
    if(lockBtn) {
        lockBtn.addEventListener('click', (e) => {
            e.preventDefault();
            sidebar.classList.toggle('sidebar-locked');
        });
    }
</script>