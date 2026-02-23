<?php include('../includes/header.php'); ?>

<div class="flex overflow-hidden" style="height: calc(100vh - 64px);">
    
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto animate-content">
        <header class="mb-8">
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Global Mobility Service <span class="text-red-600">Commissions</span></h2>
                    <p class="text-gray-500 font-medium mt-2">Track and manage GMS installment commissions and payment status.</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100 mt-8">
                <div class="flex-1 min-w-[200px] relative">
                    <input type="text" id="searchInput" placeholder="Search account, reference, or device..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500/20 text-sm">
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
                        <input type="date" id="startDate" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer uppercase text-sm">
                    </div>
                    
                    <div id="toDateContainer" class="hidden flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500 hover:border-gray-300 transition-colors">
                        <span class="text-xs font-bold uppercase">To</span>
                        <input type="date" id="endDate" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer uppercase text-sm">
                    </div>
                </div>

                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold text-sm transition shadow-sm flex items-center gap-2 ml-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Commission
                </button>
            </div>
        </header>

        <div id="noRecordFound" class="hidden mb-4 p-4 bg-red-50 border border-red-100 rounded-lg flex items-center gap-3 text-red-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium">No records found matching your selection.</span>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm overflow-x-auto border border-gray-100">
            <table class="w-full text-left border-collapse" id="commissionsTable">
                <thead class="bg-red-600 border-b border-red-700">
                    <tr class="whitespace-nowrap">
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-white">Installment Date</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-white">Ref #</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-white">Account Name</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-white">Vehicle</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-white">Device</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-white">Rate</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-white">Value</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-white">Status</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-white">Paid Amt</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wider text-white">Date Paid</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100">
                    <?php
                    // MOCK DATA ARRAY
                    $records = [
                        ['date' => '2026-02-23', 'ref' => 'REF-GMS001', 'name' => 'Juan Dela Cruz', 'vehicle' => 'Car (Toyota)', 'device' => 'GPS Tracker', 'rate' => '10%', 'val' => 1500, 'status' => 'fully paid', 'paid' => 1500, 'p_date' => '2026-02-23'],
                        ['date' => '2026-02-22', 'ref' => 'REF-GMS002', 'name' => 'Maria Clara', 'vehicle' => 'Motor (Honda)', 'device' => 'MCCS Unit', 'rate' => '10%', 'val' => 500, 'status' => 'partial', 'paid' => 250, 'p_date' => '2026-02-23'],
                        ['date' => '2026-02-20', 'ref' => 'REF-GMS003', 'name' => 'Ricardo Dalisay', 'vehicle' => 'Car (Ford)', 'device' => 'IOT Sensor', 'rate' => '5%', 'val' => 2500, 'status' => 'not paid', 'paid' => 0, 'p_date' => null],
                        ['date' => '2026-02-18', 'ref' => 'REF-GMS004', 'name' => 'Leonora Rivera', 'vehicle' => 'Motor (Yamaha)', 'device' => 'GPS Tracker', 'rate' => '10%', 'val' => 800, 'status' => 'fully paid', 'paid' => 800, 'p_date' => '2026-02-19'],
                        ['date' => '2026-02-15', 'ref' => 'REF-GMS005', 'name' => 'Crisostomo Ibarra', 'vehicle' => 'Car (Mitsubishi)', 'device' => 'MCCS Unit', 'rate' => '15%', 'val' => 3200, 'status' => 'partial', 'paid' => 1000, 'p_date' => '2026-02-16']
                    ];

                    foreach ($records as $row): 
                        $status = strtolower($row['status']);
                        $statusColor = match($status) {
                            'fully paid' => 'bg-green-100 text-green-700 border-green-200',
                            'partial'    => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'not paid'   => 'bg-red-100 text-red-700 border-red-200',
                            default      => 'bg-gray-100 text-gray-700',
                        };
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors group cursor-pointer border-b border-gray-50">
                            <td class="px-4 py-4 text-xs text-gray-600"><?php echo date("d/m/Y", strtotime($row['date'])); ?></td>
                            <td class="px-4 py-4 text-xs font-mono font-bold text-gray-700"><?php echo $row['ref']; ?></td>
                            <td class="px-4 py-4 text-xs font-semibold text-gray-800 uppercase"><?php echo $row['name']; ?></td>
                            <td class="px-4 py-4 text-xs text-gray-600"><?php echo $row['vehicle']; ?></td>
                            <td class="px-4 py-4 text-xs text-gray-600"><?php echo $row['device']; ?></td>
                            <td class="px-4 py-4 text-xs text-gray-600"><?php echo $row['rate']; ?></td>
                            <td class="px-4 py-4 text-xs font-bold text-gray-900">₱<?php echo number_format($row['val'], 2); ?></td>
                            <td class="px-4 py-4 text-[10px]">
                                <span class="px-2 py-1 rounded-full font-black uppercase border <?php echo $statusColor; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-xs font-semibold text-blue-600">₱<?php echo number_format($row['paid'], 2); ?></td>
                            <td class="px-4 py-4 text-xs text-gray-500"><?php echo $row['p_date'] ? date("d/m/Y", strtotime($row['p_date'])) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
    } else {
        toContainer.classList.remove('hidden');
        dateLabel.innerText = 'From';
        btnRange.classList.add('bg-white', 'text-red-600', 'shadow-sm');
        btnSingle.classList.remove('bg-white', 'text-red-600', 'shadow-sm');
    }
    filterTable(); 
}

function filterTable() {
    const searchText = document.getElementById('searchInput').value.toUpperCase();
    const startDateValue = document.getElementById('startDate').value;
    const endDateValue = document.getElementById('endDate').value;
    const rows = document.querySelectorAll('#tableBody tr');
    let hasMatch = false;

    rows.forEach(row => {
        const dateStr = row.cells[0].textContent.trim();
        const refText = row.cells[1].textContent.toUpperCase();
        const accountText = row.cells[2].textContent.toUpperCase();
        const deviceText = row.cells[4].textContent.toUpperCase();
        
        // Date parsing (DD/MM/YYYY)
        const [day, month, year] = dateStr.split('/');
        const rowDate = new Date(year, month - 1, day);
        rowDate.setHours(0, 0, 0, 0);

        const filterStart = startDateValue ? new Date(startDateValue) : null;
        if(filterStart) filterStart.setHours(0,0,0,0);

        const filterEnd = endDateValue ? new Date(endDateValue) : null;
        if(filterEnd) filterEnd.setHours(0,0,0,0);

        let dateMatch = true;
        if (dateMode === 'single' && filterStart) {
            dateMatch = rowDate.getTime() === filterStart.getTime();
        } else if (dateMode === 'range' && filterStart && filterEnd) {
            dateMatch = rowDate >= filterStart && rowDate <= filterEnd;
        }

        const textMatch = accountText.includes(searchText) || 
                        refText.includes(searchText) || 
                        deviceText.includes(searchText);

        if (dateMatch && textMatch) {
            row.style.display = "";
            hasMatch = true;
        } else {
            row.style.display = "none";
        }
    });
    document.getElementById('noRecordFound').classList.toggle('hidden', hasMatch);
}

document.getElementById('searchInput').addEventListener('keyup', filterTable);
</script>