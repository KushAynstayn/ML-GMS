<?php include('../includes/header.php'); ?>

<?php
// 1. DATA ARRAY (Device key remains in data but won't be displayed)
$records = [
    ['date' => '2026-02-23', 'ref' => 'REF-GMS001', 'name' => 'JUAN DELA CRUZ', 'vehicle' => 'Car', 'rate' => '10%', 'val' => 1500, 'status' => 'FULLY PAID', 'paid' => 1500, 'p_date' => '2026-02-23'],
    ['date' => '2026-02-22', 'ref' => 'REF-GMS002', 'name' => 'MARIA CLARA', 'vehicle' => 'Motor 2-Wheels', 'rate' => '10%', 'val' => 500, 'status' => 'PARTIAL', 'paid' => 250, 'p_date' => '2026-02-23'],
    ['date' => '2026-02-20', 'ref' => 'REF-GMS003', 'name' => 'RICARDO DALISAY', 'vehicle' => 'Car', 'rate' => '5%', 'val' => 2500, 'status' => 'NOT PAID', 'paid' => 0, 'p_date' => null],
    ['date' => '2026-02-18', 'ref' => 'REF-GMS004', 'name' => 'LEONORA RIVERA', 'vehicle' => 'Motor 2-Wheels', 'rate' => '10%', 'val' => 800, 'status' => 'FULLY PAID', 'paid' => 800, 'p_date' => '2026-02-19'],
    ['date' => '2026-02-15', 'ref' => 'REF-GMS005', 'name' => 'CRISOSTOMO IBARRA', 'vehicle' => 'Motor 3-Wheels', 'rate' => '15%', 'val' => 3200, 'status' => 'PARTIAL', 'paid' => 1000, 'p_date' => '2026-02-16']
];

// 2. STATISTICS CALCULATIONS
$totalCarVal = 0; $carBorrowers = [];
$totalMotor2Val = 0; $motor2Borrowers = [];
$totalMotor3Val = 0; $motor3Borrowers = [];

foreach ($records as $r) {
    $v = $r['vehicle'];
    $name = $r['name'];
    if ($v === 'Car') {
        $totalCarVal += $r['val'];
        $carBorrowers[] = $name;
    } elseif ($v === 'Motor 2-Wheels') {
        $totalMotor2Val += $r['val'];
        $motor2Borrowers[] = $name;
    } elseif ($v === 'Motor 3-Wheels') {
        $totalMotor3Val += $r['val'];
        $motor3Borrowers[] = $name;
    }
}

$countCar = count(array_unique($carBorrowers));
$countMotor2 = count(array_unique($motor2Borrowers));
$countMotor3 = count(array_unique($motor3Borrowers));
?>

<body class="h-screen overflow-hidden flex flex-col bg-gray-50">
    <div class="flex flex-1 overflow-hidden">
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto animate-content">
        <header class="mb-8">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-center">
                <div class="xl:col-span-7">
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight whitespace-nowrap">
                        Global Mobility Service <span class="text-[#D50000]">Commissions</span>
                    </h2>
                    <p class="text-gray-500 font-medium mt-1 text-sm">Track and manage GMS installment commissions and payment status.</p>
                </div>

                <div class="xl:col-span-5 grid grid-cols-3 gap-3">
                    <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-center h-24">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-[9px] font-bold text-gray-400 uppercase leading-tight">Total Car<br>Loan</p>
                            <span class="bg-gray-100 text-gray-600 text-[8px] px-1.5 py-0.5 rounded-full font-bold"><?php echo $countCar; ?> Pax</span>
                        </div>
                        <p class="text-xl font-black text-gray-900">₱<?php echo number_format($totalCarVal, 0); ?></p>
                    </div>

                    <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-center h-24">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-[9px] font-bold text-gray-400 uppercase leading-tight">Motor<br>(2-Wheels)</p>
                            <span class="bg-red-50 text-[#D50000] text-[8px] px-1.5 py-0.5 rounded-full font-bold"><?php echo $countMotor2; ?> Pax</span>
                        </div>
                        <p class="text-xl font-black text-[#D50000]">₱<?php echo number_format($totalMotor2Val, 0); ?></p>
                    </div>

                    <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-center h-24">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-[9px] font-bold text-gray-400 uppercase leading-tight">Motor<br>(3-Wheels)</p>
                            <span class="bg-gray-100 text-gray-600 text-[8px] px-1.5 py-0.5 rounded-full font-bold"><?php echo $countMotor3; ?> Pax</span>
                        </div>
                        <p class="text-xl font-black text-gray-900">₱<?php echo number_format($totalMotor3Val, 0); ?></p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100 mt-8">
                <div class="flex-1 min-w-[200px] relative">
                    <input type="text" id="searchInput" placeholder="Search account..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D50000]/20 text-sm">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>

                <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500 hover:border-gray-300 bg-white">
                    <span class="text-xs font-bold uppercase whitespace-nowrap">Filter:</span>
                    <select id="vehicleFilter" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer text-sm font-semibold text-gray-700">
                        <option value="ALL">All Vehicles</option>
                        <option value="CAR">Car</option>
                        <option value="MOTOR 2-WHEELS">Motor 2-Wheels</option>
                        <option value="MOTOR 3-WHEELS">Motor 3-Wheels</option>
                    </select>
                </div>

                <div class="flex shrink-0 bg-gray-100 p-1 rounded-lg border border-gray-200">
                    <button onclick="setDateMode('single')" id="btnSingle" class="px-3 py-1.5 text-xs font-bold uppercase rounded-md transition-all bg-white text-[#D50000] shadow-sm">Single Date</button>
                    <button onclick="setDateMode('range')" id="btnRange" class="px-3 py-1.5 text-xs font-bold uppercase rounded-md transition-all text-gray-500">Select Range</button>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500">
                        <span id="dateLabel" class="text-xs font-bold uppercase">Date</span>
                        <input type="date" id="startDate" onchange="filterTable()" class="focus:outline-none bg-transparent text-sm">
                    </div>
                    <div id="toDateContainer" class="hidden flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500">
                        <span class="text-xs font-bold uppercase">To</span>
                        <input type="date" id="endDate" onchange="filterTable()" class="focus:outline-none bg-transparent text-sm">
                    </div>
                </div>
            </div>
        </header>
        <div class="bg-white rounded-xl shadow-sm overflow-x-auto border border-gray-100">
            <table class="w-full text-left border-collapse" id="commissionsTable">
                <thead class="bg-[#D50000] text-white">
                    <tr class="whitespace-nowrap">
                        <th class="px-4 py-4 text-[10px] font-bold uppercase">Installment Date</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase">Ref #</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase">Account Name</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase">Vehicle</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase">Rate</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase">Value</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase">Status</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase">Paid Amt</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase">Date Paid</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100">
                    <tr id="noRecordFound" class="hidden">
                        <td colspan="9" class="px-4 py-12 text-center text-gray-400 italic">No records found.</td>
                    </tr>
                    <?php foreach ($records as $row): 
                        $status = strtoupper($row['status']);
                        $statusColor = match($status) {
                            'FULLY PAID' => 'bg-green-100 text-green-700 border-green-200',
                            'PARTIAL'    => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'NOT PAID'   => 'bg-red-100 text-[#D50000] border-red-200',
                            default      => 'bg-gray-100 text-gray-700',
                        };
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 text-xs"><?php echo date("d/m/Y", strtotime($row['date'])); ?></td>
                            <td class="px-4 py-4 text-xs font-mono font-bold"><?php echo $row['ref']; ?></td>
                            <td class="px-4 py-4 text-xs font-semibold uppercase"><?php echo $row['name']; ?></td>
                            <td class="px-4 py-4 text-xs text-gray-600"><?php echo $row['vehicle']; ?></td>
                            <td class="px-4 py-4 text-xs text-gray-600"><?php echo $row['rate']; ?></td>
                            <td class="px-4 py-4 text-xs font-bold">₱<?php echo number_format($row['val'], 2); ?></td>
                            <td class="px-4 py-4 text-[10px]">
                                <span class="px-2 py-1 rounded-full font-black border <?php echo $statusColor; ?>">
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
</body>

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
        btnSingle.classList.add('bg-white', 'text-[#D50000]', 'shadow-sm');
        btnRange.classList.remove('bg-white', 'text-[#D50000]', 'shadow-sm');
    } else {
        toContainer.classList.remove('hidden');
        dateLabel.innerText = 'From';
        btnRange.classList.add('bg-white', 'text-[#D50000]', 'shadow-sm');
        btnSingle.classList.remove('bg-white', 'text-[#D50000]', 'shadow-sm');
    }
    filterTable(); 
}

function filterTable() {
    const searchText = document.getElementById('searchInput').value.toUpperCase();
    const vehicleType = document.getElementById('vehicleFilter').value.toUpperCase();
    const startDateValue = document.getElementById('startDate').value;
    const endDateValue = document.getElementById('endDate').value;
    const rows = document.querySelectorAll('#tableBody tr:not(#noRecordFound)');
    let hasMatch = false;

    rows.forEach(row => {
        const dateStr = row.cells[0].textContent.trim();
        const accountText = row.cells[2].textContent.toUpperCase();
        const vehicleText = row.cells[3].textContent.toUpperCase().trim(); // Still at index 3
        
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

        const textMatch = accountText.includes(searchText);

        let vehicleMatch = false;
        if (vehicleType === 'ALL') {
            vehicleMatch = true;
        } else {
            vehicleMatch = (vehicleText === vehicleType);
        }

        if (dateMatch && textMatch && vehicleMatch) {
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