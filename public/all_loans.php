<?php 
require_once '../vendor/autoload.php';
use Cadc20239999\MlGms\Database;
include('../includes/header.php'); 


$type = $_GET['type'] ?? 'car'; 

$loan_configs = [
    'car'         => ['id' => 1, 'title' => 'Car', 'has_tabs' => true],
    'motor'       => ['id' => 2, 'title' => 'Motor', 'has_tabs' => true, 'show_wheels' => true],
    'home'        => ['id' => 3, 'title' => 'Home', 'has_tabs' => false],
    'salary'      => ['id' => 4, 'title' => 'Salary', 'has_tabs' => false],
    'personal'    => ['id' => 5, 'title' => 'Personal Property', 'has_tabs' => false],
    'realestate'  => ['id' => 6, 'title' => 'Real Estate', 'has_tabs' => false]
];

$current_config = $loan_configs[$type] ?? $loan_configs['car'];
$type_id = $current_config['id']; // Use this for SQL queries
$isMotor = ($type === 'motor');
$hasTabs = $current_config['has_tabs'];
?>

<div class="flex overflow-hidden" style="height: calc(100vh - 64px);">
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 p-8 lg:p-10 overflow-y-auto animate-content">
        <header class="mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                <?php echo $current_config['title']; ?> <span class="text-red-600">Loans</span>
            </h2>
            <p class="text-gray-500 font-medium mt-2 mb-8">View and track the status of <?php echo strtolower($current_config['title']); ?> loan records.</p>

            <?php if ($hasTabs): ?>
            <div class="flex gap-8 border-b border-gray-200 mb-8">
                <button onclick="switchLedger('primary', this)" 
                        class="ledger-tab-btn pb-2 font-semibold text-red-600 border-b-2 border-red-600 transition-all">
                    Primary Ledger
                </button>
                <button onclick="switchLedger('secondary', this)" 
                        class="ledger-tab-btn pb-2 font-semibold text-gray-500 hover:text-red-600 transition-all">
                    Secondary Ledger
                </button>
            </div>
            <?php endif; ?>

            <div class="flex flex-wrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex-1 min-w-[200px] relative">
                    <input type="text" id="searchInput" placeholder="Search account or reference..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500/20">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <?php if ($isMotor): ?>
                <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500">
                    <select id="wheelFilter" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer text-xs font-bold uppercase">
                        <option value="">All Wheels</option>
                        <option value="2-WHEELS">2-Wheels</option>
                        <option value="3-WHEELS">3-Wheels</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="flex shrink-0 bg-gray-100 p-1 rounded-lg border border-gray-200">
                    <button onclick="setDateMode('single')" id="btnSingle" class="px-3 py-1.5 text-xs font-bold uppercase rounded-md transition-all bg-white text-red-600 shadow-sm">Single Date</button>
                    <button onclick="setDateMode('range')" id="btnRange" class="px-3 py-1.5 text-xs font-bold uppercase rounded-md transition-all text-gray-500 hover:text-gray-700">Select Range</button>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500">
                        <span id="dateLabel" class="text-xs font-bold uppercase">Date</span>
                        <input type="date" id="startDate" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer text-sm">
                    </div>
                    
                    <div id="toDateContainer" class="hidden flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500">
                        <span class="text-xs font-bold uppercase">To</span>
                        <input type="date" id="endDate" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer text-sm">
                    </div>
                </div>
            </div>
        </header>

        <div id="noRecordFound" class="hidden mb-4 p-4 bg-red-50 border border-red-100 rounded-lg flex items-center gap-3 text-red-700">
            <span class="font-medium">No records found matching your selection.</span>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <table class="w-full text-left" id="loansTable">
                <thead class="bg-red-600">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-white">Date Released</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-white">Account Name</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-white">Reference Number</th>
                        <?php if ($isMotor): ?>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-white">Wheel Type</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100">
                    <?php 
                        // Define the type for the included file
                        $type = $_GET['type'] ?? 'car';

                        // Default to Primary Ledger for all pages
                        $primaryFile = dirname(__DIR__) . '/includes/tabs/primary_ledger.php';
                        if (file_exists($primaryFile)) {
                            include($primaryFile);
                        } else {
                            echo "<tr><td colspan='5' class='p-10 text-center text-gray-400 italic'>No records found.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include('../includes/modals/amortization_modal.php'); ?>
<script src="../assets/js/amortization.js"></script>

<script>
// Date and Filtering logic remains the same...
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

function switchLedger(tabName, element) {
    const tableBody = document.getElementById('tableBody');
    const loanType = "<?php echo $type; ?>";

    document.querySelectorAll('.ledger-tab-btn').forEach(btn => {
        btn.classList.remove('text-red-600', 'border-b-2', 'border-red-600');
        btn.classList.add('text-gray-500');
    });

    element.classList.add('text-red-600', 'border-b-2', 'border-red-600');
    element.classList.remove('text-gray-500');

    tableBody.innerHTML =
        '<tr><td colspan="5" class="p-10 text-center text-gray-400 italic">Updating records...</td></tr>';

    // ✅ Correct relative path (public → ../includes)
    fetch(`../includes/tabs/${tabName}_ledger.php?type=${loanType}`)
        .then(response => {
            if (!response.ok) {
                throw new Error("File not found");
            }
            return response.text();
        })
        .then(html => {
            tableBody.innerHTML = html;
            filterTable();
        })
        .catch(error => {
            tableBody.innerHTML =
                '<tr><td colspan="5" class="p-10 text-center text-red-500 italic">Ledger file not found.</td></tr>';
            console.error(error);
        });
}

function filterTable() {
    const searchText = document.getElementById('searchInput').value.toUpperCase();
    const startDateValue = document.getElementById('startDate').value;
    const endDateValue = document.getElementById('endDate').value;
    const wheelValue = document.getElementById('wheelFilter')?.value.toUpperCase() || "";

    const rows = document.querySelectorAll('#tableBody tr');
    let visibleRows = 0;

    rows.forEach(row => {
        if(row.cells.length < 3) return; 

        const dateText = row.cells[0].textContent.trim();
        const nameText = row.cells[1].textContent.toUpperCase();
        const refText = row.cells[2].textContent.toUpperCase();
        
        let dateMatch = true;
        if (startDateValue) {
            const [d, m, y] = dateText.split('/');
            const rowDate = new Date(y, m - 1, d).setHours(0,0,0,0);
            const start = new Date(startDateValue).setHours(0,0,0,0);
            
            if (dateMode === 'single') {
                dateMatch = rowDate === start;
            } else if (endDateValue) {
                const end = new Date(endDateValue).setHours(0,0,0,0);
                dateMatch = rowDate >= start && rowDate <= end;
            }
        }

        let wheelMatch = true;
        if (wheelValue !== "" && row.cells[3]) {
            wheelMatch = row.cells[3].textContent.toUpperCase().includes(wheelValue);
        }

        const textMatch = nameText.includes(searchText) || refText.includes(searchText);
        
        if (textMatch && wheelMatch && dateMatch) {
            row.style.display = "";
            visibleRows++;
        } else {
            row.style.display = "none";
        }
    });

}

document.getElementById('searchInput').addEventListener('keyup', filterTable);
</script>