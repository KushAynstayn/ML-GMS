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

<body class="h-screen overflow-hidden flex flex-col bg-gray-50">
    <div class="flex flex-1 overflow-hidden">
        <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto animate-content">
        <header class="mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                <?php echo $current_config['title']; ?> <span class="text-[#D50000]">Loans</span>
            </h2>
            <p class="text-gray-500 font-medium mt-2 mb-8">View and track the status of <?php echo strtolower($current_config['title']); ?> loan records.</p>

            <?php if ($hasTabs): ?>
            <div class="flex gap-8 border-b border-gray-200 mb-8">
                <button onclick="switchLedger('primary', this)" 
                        class="ledger-tab-btn pb-2 font-semibold text-[#D50000] border-b-2 border-[#D50000] transition-all">
                    Primary Ledger
                </button>
                <button onclick="switchLedger('secondary', this)" 
                        class="ledger-tab-btn pb-2 font-semibold text-gray-500 hover:text-[#D50000] transition-all">
                    Secondary Ledger
                </button>
            </div>
            <?php endif; ?>

            <div class="flex flex-wrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex-1 min-w-[200px] relative">
                    <input type="text" id="searchInput" placeholder="Search account or reference..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D50000]/20">
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

                <?php include('../includes/date_picker.php'); ?>

                <a href="add_loan.php" class="bg-[#D50000] text-white px-4 py-2 rounded-lg text-xs font-bold uppercase hover:bg-[#b00000] transition-all shadow-sm">
                    Add Loan
                </a>
            </div>
        </header>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <table class="w-full text-left" id="loansTable">
                <thead class="bg-[#D50000]">
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
                    <tr id="noResultsRow" class="hidden">
                        <td colspan="<?php echo ($isMotor ? '4' : '3'); ?>" class="px-6 py-12 text-center text-gray-400 text-sm italic">
                            No records found.
                        </td>
                    </tr>
                    <?php 
                        $type = $_GET['type'] ?? 'car';
                        $primaryFile = dirname(__DIR__) . '/includes/tabs/primary_ledger.php';
                        if (file_exists($primaryFile)) {
                            // Capture the output of the ledger file
                            ob_start();
                            include($primaryFile);
                            $output = ob_get_clean();

                            // Use Regex to find DD/MM/YYYY and flip to MM/DD/YYYY before displaying
                            echo preg_replace_callback('/(\d{2})\/(\d{2})\/(\d{4})/', function($matches) {
                                return $matches[2] . '/' . $matches[1] . '/' . $matches[3];
                            }, $output);

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
const dateMode = 'range';

function switchLedger(tabName, element) {
    const tableBody = document.getElementById('tableBody');
    const loanType = "<?php echo $type; ?>";

    document.querySelectorAll('.ledger-tab-btn').forEach(btn => {
        btn.classList.remove('text-[#D50000]', 'border-b-2', 'border-[#D50000]');
        btn.classList.add('text-gray-500');
    });

    element.classList.add('text-[#D50000]', 'border-b-2', 'border-[#D50000]');
    element.classList.remove('text-gray-500');

    tableBody.innerHTML =
        '<tr><td colspan="5" class="p-10 text-center text-gray-400 italic">Updating records...</td></tr>';

    fetch(`../includes/tabs/${tabName}_ledger.php?type=${loanType}`)
        .then(response => {
            if (!response.ok) {
                throw new Error("File not found");
            }
            return response.text();
        })
        .then(html => {
            // Apply the same date flip to the AJAX response
            const flippedHtml = html.replace(/(\d{2})\/(\d{2})\/(\d{4})/g, '$2/$1/$3');
            tableBody.innerHTML = flippedHtml;
            
            const isMotor = "<?php echo $isMotor; ?>";
            const colspan = isMotor ? '4' : '3';
            const noResultsHTML = `<tr id="noResultsRow" class="hidden"><td colspan="${colspan}" class="px-6 py-12 text-center text-gray-400 text-sm italic">No records found.</td></tr>`;
            tableBody.insertAdjacentHTML('afterbegin', noResultsHTML);
            filterTable();
        })
        .catch(error => {
            tableBody.innerHTML = '<tr><td colspan="5" class="p-10 text-center text-red-500 italic">Ledger file not found.</td></tr>';
        });
}

function filterTable() {
    const searchText = document.getElementById('searchInput').value.toUpperCase();
    const startDateValue = document.getElementById('startDate').value;
    const endDateValue = document.getElementById('endDate').value;
    const wheelValue = document.getElementById('wheelFilter')?.value.toUpperCase() || "";

    const rows = document.querySelectorAll('#tableBody tr');
    const noResultsRow = document.getElementById('noResultsRow');
    let visibleRows = 0;

    rows.forEach(row => {
        if(row.id === 'noResultsRow' || row.cells.length < 3) return; 

        const dateText = row.cells[0].textContent.trim();
        const nameText = row.cells[1].textContent.toUpperCase();
        const refText = row.cells[2].textContent.toUpperCase();
        
        let dateMatch = checkDateMatch(dateText, startDateValue, endDateValue);

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

    if (noResultsRow) {
        if (visibleRows === 0) {
            noResultsRow.classList.remove('hidden');
            noResultsRow.style.display = "table-row";
        } else {
            noResultsRow.classList.add('hidden');
            noResultsRow.style.display = "none";
        }
    }
}

document.getElementById('searchInput').addEventListener('keyup', filterTable);
</script>