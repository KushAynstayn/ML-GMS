<aside id="sidebar" 
class="w-20 hover:w-64 transition-all duration-300 ease-in-out bg-white border-r border-gray-200 flex flex-col justify-between py-4 relative group shrink-0">    
    <div>
        <div class="px-3 flex items-center h-12 border-b border-gray-200 mx-4 mb-4">
            <button id="logo-lock-btn" class="flex items-center justify-center min-w-[26px] focus:outline-none hover:scale-110 transition-transform active:scale-95">
                <img src="../assets/images/mlhuillier-red.png" alt="M Lhuillier" class="w-7 h-7 object-contain">
            </button>
            
            <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 sidebar-text font-bold text-xl ml-8" 
                style="font-family: 'Ultra', serif; font-weight: 900; color: #d42929; text-transform: uppercase;">
                ML LOANS
            </span>
        </div>

        <nav class="space-y-2 px-4">
            <a href="../public/dashboard" class="flex items-center gap-4 px-3 py-3 bg-ml-red text-white rounded-lg font-bold shadow-md">
                <div class="flex items-center justify-center min-w-[24px]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path></svg>
                </div>
                <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 sidebar-text">Dashboard</span>
            </a>

            <a href="../public/add_loan/add_loan.php" class="flex items-center gap-4 px-3 py-3 rounded-lg transition <?php echo ($current_page == 'add_loan.php') ? 'bg-ml-red text-white font-bold shadow-md' : 'text-gray-500 hover:bg-gray-100'; ?>">
                <div class="flex items-center justify-center min-w-[24px]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 sidebar-text">Add Loan</span>
            </a>

            <a href="#" class="flex items-center gap-4 px-3 py-3 text-gray-500 hover:bg-gray-100 rounded-lg transition">
                <div class="flex items-center justify-center min-w-[24px]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 sidebar-text">All Loans</span>
            </a>

            <a href="#" class="flex items-center gap-4 px-3 py-3 text-gray-500 hover:bg-gray-100 rounded-lg transition">
                <div class="flex items-center justify-center min-w-[24px]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 sidebar-text">Collection Report</span>
            </a>

            <a href="../index.php" class="flex items-center gap-4 px-3 py-3 text-gray-500 hover:bg-red-50 hover:text-red-600 rounded-lg transition mt-4 border-t pt-6">
                <div class="flex items-center justify-center min-w-[24px]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </div>
                <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 sidebar-text">Logout</span>
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
            <div class="flex flex-col whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 sidebar-text overflow-hidden">
                <span class="text-sm font-bold text-gray-800">Juan Dela Cruz</span>
                <span class="text-xs text-gray-500 font-medium">Administrator</span>
            </div>
        </div>
    </div>
</aside>

<style>
    /* Logic for Sidebar Locking */
    .sidebar-locked {
        width: 16rem !important; /* w-64 */
    }
    .sidebar-locked .sidebar-text {
        opacity: 1 !important;
    }
    /* Optional: Add a subtle glow or ring to the logo when locked */
    .sidebar-locked #logo-lock-btn {
        filter: drop-shadow(0 0 4px rgba(139, 0, 0, 0.4));
    }
</style>

<script>
    const sidebar = document.getElementById('sidebar');
    const lockBtn = document.getElementById('logo-lock-btn');

    lockBtn.addEventListener('click', (e) => {
        // Prevent any link navigation if the button is inside a container with listeners
        e.preventDefault();
        sidebar.classList.toggle('sidebar-locked');
    });
</script>