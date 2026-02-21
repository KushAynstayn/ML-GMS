<?php include('../includes/header.php'); ?>

<div class="flex overflow-hidden" style="height: calc(100vh - 64px);">
    
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto">
        <header class="mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">All <span class="text-red-600">Loans</span></h2>
            <p class="text-gray-500 font-medium mt-2 mb-8">View and track the status of all existing loan records.</p>


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
                        <input type="date" id="startDate" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer uppercase text-sm">
                    </div>
                    
                    <div id="toDateContainer" class="hidden flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500 hover:border-gray-300 transition-colors">
                        <span class="text-xs font-bold uppercase">To</span>
                        <input type="date" id="endDate" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer uppercase text-sm">
                    </div>
                </div>
            </div>
        </header>

        <div id="noRecordFound" class="hidden mb-4 p-4 bg-red-50 border border-red-100 rounded-lg flex items-center gap-3 text-red-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium">No records found matching your selection.</span>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <table class="w-full text-left" id="loansTable">
                <thead class="bg-red-600 border-b border-red-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-white">Date Released</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-white">Account Name</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-white">Reference Number</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100">
                    <?php
                    require_once '../vendor/autoload.php';

                    use Cadc20239999\MlGms\Database;

                    try {
                        $db = (new Database())->connect('LOAN');

                        $stmt = $db->prepare("
                            SELECT id, account_name, reference_number, pn_date 
                            FROM loans 
                            ORDER BY pn_date DESC
                        ");
                        $stmt->execute();
                        $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if ($loans) {
                            foreach ($loans as $loan) {

                                $loanId = $loan['id'];
                                $accountName = htmlspecialchars($loan['account_name']);
                                $reference = htmlspecialchars($loan['reference_number']);
                                $releaseDate = date("d/m/Y", strtotime($loan['pn_date']));
                    ?>
                            <tr onclick="openAmortization('<?php echo $loanId; ?>')"
                                class="hover:bg-pink-50 transition-colors group cursor-pointer">

                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo $releaseDate; ?>
                                </td>

                                <td class="px-6 py-4 text-sm font-semibold text-gray-700">
                                    <?php echo $accountName; ?>
                                </td>

                                <td class="px-6 py-4 text-sm font-mono text-gray-500">
                                    <?php echo $reference; ?>
                                </td>

                            </tr>
                    <?php
                            }
                        } else {
                    ?>
                            <tr>
                                <td colspan="3" class="px-6 py-6 text-center text-gray-400 text-sm">
                                    No loan records found.
                                </td>
                            </tr>
                    <?php
                        }
                    } catch (Exception $e) {
                        echo "<tr><td colspan='3' class='px-6 py-6 text-center text-red-500 text-sm'>No Records Found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include('../includes/modals/amortization_modal.php'); ?>

<script>
// Open Modal and Set Data
function openAmortization(loanId) {
    const modal = document.getElementById('amortizationModal');

    // You can fetch loan details using AJAX here using loanId

    document.getElementById('modalDispName').innerText = '';
    document.getElementById('modalDispRef').innerText = '';

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    // TODO: AJAX call to fetch amortization schedule using loanId
}

// Close Modal (Already handled inside amortization_modal.php, but kept here for safety)
function closeAmortization() {
    const modal = document.getElementById('amortizationModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Keep existing search/filter logic
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
        const releaseDateStr = row.cells[0].textContent.trim();
        const nameText = row.cells[1].textContent.toUpperCase();
        const refText = row.cells[2].textContent.toUpperCase();
        
        // Simple date parsing for DD/MM/YYYY
        const [day, month, year] = releaseDateStr.split('/');
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

        const textMatch = nameText.includes(searchText) || refText.includes(searchText);
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