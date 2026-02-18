<?php include('../includes/header.php'); ?>

<div class="flex overflow-hidden" style="height: calc(100vh - 64px);">
    
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto">
        <header class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h2>
            
            <div class="flex flex-wrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex-1 min-w-[200px] relative">
                    <input type="text" id="searchInput" placeholder="Search account or reference..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500/20">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <div class="flex shrink-0 bg-gray-100 p-1 rounded-lg border border-gray-200">
                    <button onclick="setDateMode('single')" id="btnSingle" class="px-3 py-1.5 text-xs font-bold uppercase rounded-md transition-all bg-white text-red-600 shadow-sm">Single Date</button>
                    <button onclick="setDateMode('range')" id="btnRange" class="px-3 py-1.5 text-xs font-bold uppercase rounded-md transition-all text-gray-500 hover:text-gray-700">Select Range</button>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500 hover:border-gray-300 transition-colors">
                        <span id="dateLabel" class="text-xs font-bold uppercase">Date</span>
                        <input type="date" id="startDate" onchange="filterDashboard()" class="focus:outline-none bg-transparent cursor-pointer uppercase text-sm">
                    </div>
                    
                    <div id="toDateContainer" class="hidden flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500 hover:border-gray-300 transition-colors">
                        <span class="text-xs font-bold uppercase">To</span>
                        <input type="date" id="endDate" onchange="filterDashboard()" class="focus:outline-none bg-transparent cursor-pointer uppercase text-sm">
                    </div>
                </div>
            </div>
        </header>

        <div id="noRecordFound" class="hidden mb-6 p-4 bg-red-50 border border-red-100 rounded-lg flex items-center gap-3 text-red-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium">No records found matching your selection.</span>
        </div>

        <div id="searchTableContainer" class="hidden bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 mb-10 transition-all">
            <table class="w-full text-left" id="loansTable">
                <thead class="bg-red-600 border-b border-red-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-white">Date Released</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-white">Account Name</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-white">Reference Number</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100">
                    <tr class="hover:bg-pink-50 transition-colors group cursor-default">
                        <td class="px-6 py-4 text-sm text-gray-600">08/08/2025</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-700">Leah Faye Genson</td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-500">MCRVMWQTW</td>
                    </tr>
                    <tr class="hover:bg-pink-50 transition-colors group cursor-default">
                        <td class="px-6 py-4 text-sm text-gray-600">15/09/2025</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-700">Juan Dela Cruz</td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-500">XPQZRT123</td>
                    </tr>
                    <tr class="hover:bg-pink-50 transition-colors group cursor-default">
                        <td class="px-6 py-4 text-sm text-gray-600">20/10/2025</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-700">Maria Clara Reyes</td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-500">LKNMOP789</td>
                    </tr>
                </tbody>
            </table>
        </div>

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
        btnSingle.classList.add('bg-white', 'text-red-600', 'shadow-sm');
        btnRange.classList.remove('bg-white', 'text-red-600', 'shadow-sm');
        document.getElementById('endDate').value = ""; 
    } else {
        toContainer.classList.remove('hidden');
        dateLabel.innerText = 'From';
        btnRange.classList.add('bg-white', 'text-red-600', 'shadow-sm');
        btnSingle.classList.remove('bg-white', 'text-red-600', 'shadow-sm');
    }
    filterDashboard(); 
}

function filterDashboard() {
    const searchText = document.getElementById('searchInput').value.toUpperCase();
    const startDateStr = document.getElementById('startDate').value;
    const endDateStr = document.getElementById('endDate').value;
    const tableContainer = document.getElementById('searchTableContainer');
    const rows = document.querySelectorAll('#tableBody tr');
    
    // Check if any filter is active
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