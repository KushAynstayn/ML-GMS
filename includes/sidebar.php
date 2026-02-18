<?php
// Get the current filename (e.g., 'dashboard.php') to highlight the active link
$current_page = basename($_SERVER['PHP_SELF']);

$menu_items = [
    ['file' => 'dashboard.php', 'label' => 'Dashboard', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z'],
    ['file' => 'add_loan.php', 'label' => 'Add Loan', 'icon' => 'M12 4v16m8-8H4'],
    ['file' => 'all_loans.php', 'label' => 'All Loans', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ['file' => 'collection_report.php', 'label' => 'Collection Report', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ['file' => 'user_management.php', 'label' => 'User Management', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z']
];
?>

<aside id="sidebar" class="w-20 hover:w-64 transition-all duration-300 ease-in-out bg-white border-r border-gray-200 flex flex-col justify-between py-4 relative group shrink-0 shadow-md">
    <div>
        <div class="px-3 flex items-center h-12 border-b border-gray-200 mx-4 mb-4">
             <button id="logo-lock-btn" class="flex items-center justify-center min-w-[26px]">
                <img src="../assets/images/mlhuillier-red.png" alt="M Lhuillier" class="w-7 h-7 object-contain">
            </button>
            <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-150 sidebar-text font-bold text-xl ml-8" style="color: #d42929;">
                ML LOANS
            </span>
        </div>

        <nav class="space-y-2 px-4">
            <?php foreach ($menu_items as $item): 
                // Check if this item is the current page
                $isActive = ($current_page === $item['file']);
            ?>
                <a href="<?php echo $item['file']; ?>" 
                   class="flex items-center gap-4 px-3 py-3 rounded-lg transition <?php echo $isActive ? 'bg-red-600 text-white font-bold shadow-md' : 'text-gray-500 hover:bg-gray-100'; ?>">
                    <div class="flex items-center justify-center min-w-[24px]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $item['icon']; ?>"></path>
                        </svg>
                    </div>
                    <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-150 sidebar-text">
                        <?php echo $item['label']; ?>
                    </span>
                </a>
            <?php endforeach; ?>

            <div class="pt-4 mt-4 border-t border-gray-100 mx-2"></div>

            <a href="../actions/logout.php" class="flex items-center gap-4 px-3 py-3 text-gray-500 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                <div class="flex items-center justify-center min-w-[24px]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-150 sidebar-text">
                    Logout
                </span>
            </a>
        </nav>
    </div>
    
    <div class="px-4 mb-4">
        <div class="flex items-center gap-4 text-gray-700 font-semibold border-t pt-4">
            <div class="w-10 h-10 bg-gray-200 rounded-full flex-shrink-0 flex items-center justify-center border-2 border-white shadow-sm">
                <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path>
                </svg>
            </div>
            <div class="flex flex-col whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-150 sidebar-text overflow-hidden">
                <span class="text-sm font-bold text-gray-800">Juan Dela Cruz</span>
                <span class="text-xs text-gray-500 font-medium">Administrator</span>
            </div>
        </div>
    </div>
</aside>

<script>
    // 1. Sidebar Lock Logic (from your original code)
    const sidebar = document.getElementById('sidebar');
    const lockBtn = document.getElementById('logo-lock-btn');
    if(lockBtn) {
        lockBtn.addEventListener('click', (e) => {
            e.preventDefault();
            sidebar.classList.toggle('sidebar-locked');
        });
    }

    // 2. Content Loading Logic
    function loadContent(url, element) {
        const mainArea = document.querySelector('main');
        
        // Fetch the PHP file
        fetch(url)
            .then(response => response.text())
            .then(html => {
                // Update the right side content
                mainArea.innerHTML = html;

                // Update active button styles
                document.querySelectorAll('.menu-link').forEach(link => {
                    link.classList.remove('bg-red-600', 'text-white', 'font-bold', 'shadow-md');
                    link.classList.add('text-gray-500', 'hover:bg-gray-100');
                });
                element.classList.add('bg-red-600', 'text-white', 'font-bold', 'shadow-md');
                element.classList.remove('text-gray-500', 'hover:bg-gray-100');
            })
            .catch(err => console.warn('Something went wrong.', err));
    }
</script>

<style>
    /* 1. Base Sidebar Transition */
    #sidebar {
        transition: width 300ms ease-in-out !important;
    }

    /* 2. Base Text Transition (No delay by default) */
    .sidebar-text {
        transition: opacity 200ms ease-in-out !important;
    }

    /* 3. Add Delay ONLY when opening (Hover or Locked) */
    #sidebar:hover .sidebar-text,
    .sidebar-locked .sidebar-text {
        opacity: 1 !important;
        transition-delay: 150ms !important; /* Wait for sidebar to widen */
    }

    /* 4. Remove Delay when closing (Mouse leaves or Unlocked) */
    #sidebar:not(:hover):not(.sidebar-locked) .sidebar-text {
        transition-delay: 0ms !important; /* Hide immediately as sidebar shrinks */
        opacity: 0 !important;
    }

    /* 5. Sidebar Widths */
    .sidebar-locked { 
        width: 16rem !important; 
    }

    #sidebar:hover {
        width: 16rem;
    }

    .sidebar-locked #logo-lock-btn { 
        filter: drop-shadow(0 0 4px rgba(139, 0, 0, 0.4)); 
    }
</style>