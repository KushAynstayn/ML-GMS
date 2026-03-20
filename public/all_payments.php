<?php 
require_once '../vendor/autoload.php';
use Cadc20239999\MlGms\Database;
include('../includes/header.php'); 

// Get the type from URL, default to car
$type = $_GET['type'] ?? 'car'; 

// Payment-specific configuration
$payment_configs = [
    'car'         => ['id' => 1, 'title' => 'Car'],
    'motor'       => ['id' => 2, 'title' => 'Motor'],
    'home'        => ['id' => 3, 'title' => 'Home'],
    'salary'      => ['id' => 4, 'title' => 'Salary'],
    'personal'    => ['id' => 5, 'title' => 'Personal Property'],
    'realestate'  => ['id' => 6, 'title' => 'Real Estate']
];

$current_config = $payment_configs[$type] ?? $payment_configs['car'];
$type_id = $current_config['id']; 

$processImportUrl = '../actions/process_import.php';

?>


<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<style>
    /* Consistent styling for focus states */
    .tab-btn:focus, .tab-btn:active { outline: none !important; box-shadow: none !important; }
    button:focus { outline: none !important; }
    .cursor-pointer-row { cursor: pointer; transition: background-color 0.2s; }
</style>

<body class="h-screen overflow-hidden flex flex-col bg-gray-50">
    <div class="flex flex-1 overflow-hidden">
        <?php include('../includes/sidebar.php'); ?>

        <main class="flex-1 bg-gray-50 p-8 overflow-y-auto animate-content">
            <header class="mb-6 flex flex-col md:flex-row justify-between items-start">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        <?php echo $current_config['title']; ?> <span class="text-[#D50000]">Payments</span>
                    </h2>
                    <p class="text-gray-500 font-medium mt-2 mb-8">View and track the status of <?php echo strtolower($current_config['title']); ?> payment records.</p>
                </div>

                <div class="flex gap-4">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 min-w-[140px] relative">
                        <p class="text-[10px] font-bold text-gray-400 uppercase leading-tight">Total <?php echo $current_config['title']; ?><br>Borrowers</p>
                        <p class="text-xl font-black text-gray-900 mt-2" id="summaryTotalCount">0</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 min-w-[160px]">
                        <p class="text-[10px] font-bold text-gray-400 uppercase leading-tight">Total Amount<br>Collected</p>
                        <p class="text-xl font-black text-[#D50000] mt-2">₱<span id="summaryTotalAmount">0.00</span></p>
                    </div>
                </div>
            </header>

            <div class="flex flex-wrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="flex-1 min-w-[200px] relative">
                    <input type="text" id="searchInput" placeholder="Search account or reference..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D50000]/20 text-sm">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <div class="relative">
                    <select onchange="window.location.replace('?type=' + this.value)" class="appearance-none bg-white border border-gray-200 text-gray-700 py-2 px-4 pr-8 rounded-lg text-sm focus:outline-none cursor-pointer font-medium">
                        <?php foreach ($payment_configs as $key => $config): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($type === $key) ? 'selected' : ''; ?>>
                                <?php echo $config['title']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                    </div>
                </div>

                <?php include('../includes/date_picker.php'); ?>
                
                <button onclick="openPaymentModal()" class="bg-[#D50000] text-white px-4 py-2 rounded-lg text-xs font-bold uppercase hover:bg-[#b00000] transition-all shadow-sm">
                    Add Payment
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="w-full text-left" id="paymentsTable">
                        <thead class="bg-[#D50000]">
                            <tr>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Date Paid</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Account Name</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Payment Reference Number</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Monthly Amortization</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Paid Amount</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Date Applied</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Status</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Due Next Month</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="divide-y divide-gray-100">
                            <tr id="noResultsRow" class="hidden">
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400 text-xs italic">No records found.</td>
                            </tr>
                            
                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer-row" 
                                onclick="showBreakdown(this)"
                                data-amort="64147.00" data-prev-paid="64147.00" data-prev-unpaid="0" data-partial="0" data-vat="0" data-penalty="0" data-interest-diff="0" data-advance="0">
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">2026-01-02</td>
                                <td class="px-4 py-3 text-[12px] font-bold text-gray-900 whitespace-nowrap">Juan Dela Cruz</td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">REF-JAN-001</td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">64,147.00</td>
                                <td class="px-4 py-3 text-[12px] font-bold text-gray-900 whitespace-nowrap">64,147.00</td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">January</td>
                                <td class="px-4 py-3">
                                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase whitespace-nowrap">Paid</span>
                                </td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">64,147.00 (Feb)</td>
                            </tr>

                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer-row"
                                onclick="showBreakdown(this)"
                                data-amort="25000.00" data-prev-paid="20000.00" data-prev-unpaid="5000.00" data-partial="5000.00" data-vat="600.00" data-penalty="500.00" data-interest-diff="0" data-advance="0">
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">2026-01-02</td>
                                <td class="px-4 py-3 text-[12px] font-bold text-gray-900 whitespace-nowrap">Maria Clara</td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">REF-JAN-002</td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">25,000.00</td>
                                <td class="px-4 py-3 text-[12px] font-bold text-gray-900 whitespace-nowrap">20,000.00</td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">January</td>
                                <td class="px-4 py-3">
                                    <span class="bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase whitespace-nowrap">Partial</span>
                                </td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">31,100.00 (Feb)</td>
                            </tr>

                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer-row"
                                onclick="showBreakdown(this)"
                                data-amort="65000.00" data-prev-paid="130000.00" data-prev-unpaid="0" data-partial="0" data-vat="0" data-penalty="0" data-interest-diff="0" data-advance="65000.00">
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">2026-01-02</td>
                                <td class="px-4 py-3 text-[12px] font-bold text-gray-900 whitespace-nowrap">Santiago Enrile</td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">REF-JAN-003</td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">65,000.00</td>
                                <td class="px-4 py-3 text-[12px] font-bold text-gray-900 whitespace-nowrap">130,000.00</td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">Jan-Feb</td>
                                <td class="px-4 py-3">
                                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase whitespace-nowrap">Paid</span>
                                </td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">0.00 (Feb)</td>
                            </tr>

                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer-row"
                                onclick="showBreakdown(this)"
                                data-amort="10000.00" data-prev-paid="0" data-prev-unpaid="10000.00" data-partial="0" data-vat="1200.00" data-penalty="1000.00" data-interest-diff="0" data-advance="0">
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">2026-01-02</td>
                                <td class="px-4 py-3 text-[12px] font-bold text-gray-900 whitespace-nowrap">Mockup Borrower 4</td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">REF-JAN-004</td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">10,000.00</td>
                                <td class="px-4 py-3 text-[12px] font-bold text-gray-900 whitespace-nowrap">0.00</td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">--</td>
                                <td class="px-4 py-3">
                                    <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase whitespace-nowrap">Unpaid</span>
                                </td>
                                <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap">22,200.00 (Feb)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="breakdownModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[150] p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-pop">
            <div class="p-5 border-b flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Payment <span class="text-[#D50000]">Breakdown</span></h3>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between text-sm py-2 border-b border-gray-50">
                    <span class="text-gray-500 font-medium">Monthly Amortization</span>
                    <span class="font-bold text-gray-900" id="bdAmort">₱0.00</span>
                </div>
                <div class="flex justify-between text-sm py-2 border-b border-gray-50">
                    <span class="text-gray-500 font-medium">Amount Paid</span>
                    <span class="font-bold text-gray-900" id="bdPaid">₱0.00</span>
                </div>
                <div class="flex justify-between text-sm py-2 border-b border-gray-50">
                    <span class="text-gray-500 font-medium">Unpaid/Balance</span>
                    <span class="font-bold text-gray-900" id="bdUnpaid">₱0.00</span>
                </div>
                <div class="flex justify-between text-sm py-2 border-b border-gray-50">                 
                    <span class="text-gray-500 font-medium">Advance Payment</span>
                    <span class="font-bold text-gray-900" id="bdAdvance">₱0.00</span>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl space-y-2">
                    <div class="flex justify-between text-[11px] uppercase tracking-wider font-bold text-gray-400">
                        <span>Additional Charges</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">VAT</span>
                        <span class="font-medium text-gray-800" id="bdVat">₱0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Penalty</span>
                        <span class="font-medium text-gray-800" id="bdPenalty">₱0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Interest Difference</span>
                        <span class="font-medium text-gray-800" id="bdInterest">₱0.00</span>
                    </div>
                </div>
                <div class="pt-4 border-t-2 border-dashed border-gray-100 mt-4">
                    <div class="flex justify-between items-end">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase">Total Due Next Month</span>
                            <p id="nextMonthLabel" class="text-[10px] font-bold text-[#D50000] uppercase tracking-wider">For February</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xl font-black text-[#D50000]" id="bdTotalDue">₱0.00</span>
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-2 italic">*Includes next month's amort + unpaid balance + penalties.</p>
                </div>
            </div>
            <div class="p-4 bg-gray-50 text-center">
                <button onclick="closeBreakdownModal()" class="w-full bg-gray-800 text-white py-2 rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-gray-900 transition-all">Close Details</button>
            </div>
        </div>
    </div>

    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[110] p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden animate-pop">
            <div class="p-6 border-b flex justify-between items-center bg-gray-50">
                <h3 class="text-xl font-bold text-gray-800">Import <span class="text-[#D50000]">Payments</span></h3>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600 transition-colors text-3xl font-light focus:outline-none">&times;</button>
            </div>
            <div class="p-8">
                <div class="bg-white p-10 rounded-xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-center">
                    <div class="bg-yellow-100 p-4 rounded-lg mb-4">
                        <svg class="w-10 h-10 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                        </svg>
                    </div>
                    <p id="uploadTitle" class="font-bold text-gray-700">Upload Payment File</p>
                    <p id="fileNameDisplay" class="text-sm text-gray-400 mb-6">Click to browse your computer</p>
                    <input type="file" id="fileInput" name="payment_file" accept=".xls,.xlsx" class="hidden">

                    <input type="hidden" id="importType" value="payment">
                    <input type="hidden" id="loanTypeId" value="<?php echo (int)$type_id; ?>">

                    <div class="flex gap-2">
                        <button id="cancelBtn" onclick="resetFileInput()" class="hidden bg-red-100 text-red-600 px-6 py-2 rounded-lg font-bold hover:bg-red-200 transition-colors">Cancel</button>
                        <button id="selectBtn" onclick="document.getElementById('fileInput').click()" class="bg-gray-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-gray-700 transition-colors">Select File</button>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button id="uploadBtn" onclick="handleUpload()" class="bg-[#D50000] text-white px-8 py-3 rounded-lg font-bold hover:bg-red-700 transition-colors text-sm uppercase tracking-wide shadow-lg">UPLOAD</button>
                </div>
            </div>
        </div>
    </div>

    <div id="statusAlert" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-[200] p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden animate-pop">
            <div id="alertContent" class="p-8 text-center">
                <div id="alertIconContainer" class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4"></div>
                <h3 id="alertTitle" class="text-xl font-bold mb-2"></h3>
                <p id="alertMessage" class="text-gray-500 text-sm mb-6"></p>
                <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                    <div id="alertTimerBar" class="h-full transition-all duration-100 ease-linear"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="fileModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[120] p-4">
        <div class="bg-white rounded-xl w-full max-w-7xl max-h-[90vh] flex flex-col shadow-2xl">
            <div class="p-5 border-b flex justify-between items-center bg-gray-50 rounded-t-xl">
                <h3 id="modalTitle" class="font-bold text-gray-700 text-lg">File Preview</h3>
                <button onclick="document.getElementById('fileModal').classList.replace('flex', 'hidden')" class="text-gray-400 hover:text-gray-700 transition-colors text-3xl font-light">&times;</button>
            </div>
            <div id="modalBody" class="flex-1 overflow-auto bg-gray-100 visible-scrollbar rounded-b-xl border border-gray-100"></div>
        </div>
    </div>

    <script>
    // Execute filter immediately to calculate totals and hide rows correctly
    document.addEventListener("DOMContentLoaded", filterTable);

    // Breakdown Modal Functions
    function showBreakdown(row) {
        const data = row.dataset;
        const format = (val) => "₱" + parseFloat(val).toLocaleString('en-US', {minimumFractionDigits: 2});
        
        // Populate Modal Fields
        document.getElementById('bdAmort').innerText = format(data.amort);
        document.getElementById('bdPaid').innerText = format(data.prevPaid);
        document.getElementById('bdUnpaid').innerText = format(data.prevUnpaid);
        document.getElementById('bdVat').innerText = format(data.vat);
        document.getElementById('bdPenalty').innerText = format(data.penalty);
        document.getElementById('bdInterest').innerText = format(data.interestDiff);
        document.getElementById('bdAdvance').innerText = format(data.advance);

        // Logic for Total Due Next Month
        let totalDue = (parseFloat(data.prevUnpaid) + parseFloat(data.amort) + parseFloat(data.vat) + parseFloat(data.penalty) + parseFloat(data.interestDiff)) - parseFloat(data.advance);
        if(totalDue < 0) totalDue = 0;

        document.getElementById('bdTotalDue').innerText = format(totalDue);
        
        document.getElementById('breakdownModal').classList.replace('hidden', 'flex');
    }

    function closeBreakdownModal() {
        document.getElementById('breakdownModal').classList.replace('flex', 'hidden');
    }

    function filterTable() {
        const searchText = document.getElementById('searchInput').value.toUpperCase();
        const startVal = document.getElementById('startDate').value;
        const endVal = document.getElementById('endDate').value;
        const rows = document.querySelectorAll('#tableBody tr');
        let visibleRows = 0;
        let totalAmount = 0;

        rows.forEach(row => {
            if(row.id === 'noResultsRow' || row.cells.length < 8) return;

            const nameText = row.cells[1].textContent.toUpperCase();
            const refText = row.cells[2].textContent.toUpperCase();
            const matchesSearch = nameText.includes(searchText) || refText.includes(searchText);

            const rowDateStr = row.cells[0].textContent.trim(); 
            
            let matchesDate = true;
            if (startVal && endVal) {
                const rowDate = new Date(rowDateStr).setHours(0,0,0,0);
                const start = new Date(startVal).setHours(0,0,0,0);
                const end = new Date(endVal).setHours(0,0,0,0);
                matchesDate = rowDate >= start && rowDate <= end;
            } else if (startVal) {
                const rowDate = new Date(rowDateStr).setHours(0,0,0,0);
                const start = new Date(startVal).setHours(0,0,0,0);
                matchesDate = rowDate === start;
            }

            if (matchesSearch && matchesDate) {
                row.style.display = ""; 
                visibleRows++;
                const amtString = row.cells[4].textContent.replace(/,/g, '').trim();
                const amt = parseFloat(amtString);
                if(!isNaN(amt)) totalAmount += amt;
            } else { 
                row.style.display = "none"; 
            }
        });

        document.getElementById('summaryTotalCount').innerText = visibleRows;
        document.getElementById('summaryTotalAmount').innerText = totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('noResultsRow').style.display = visibleRows === 0 ? "table-row" : "none";
    }

    document.getElementById('searchInput').addEventListener('keyup', filterTable);
    function openPaymentModal() { document.getElementById('paymentModal').classList.replace('hidden', 'flex'); }
    function closePaymentModal() {
        const modal = document.getElementById('paymentModal');
        modal.classList.replace('flex', 'hidden');
        resetFileInput();
    }

    document.getElementById('fileInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            document.getElementById('uploadTitle').innerText = "File Selected";
            document.getElementById('fileNameDisplay').innerHTML = `Selected: <span class="text-[#D50000] font-bold cursor-pointer hover:underline">${file.name}</span>`;
            document.getElementById('fileNameDisplay').querySelector('span').onclick = () => openFilePreview(file);
            document.getElementById('selectBtn').innerText = "View File";
            document.getElementById('selectBtn').onclick = (ev) => { ev.preventDefault(); openFilePreview(file); };
            document.getElementById('cancelBtn').classList.remove('hidden');
        }
    });

    function resetFileInput() {
        document.getElementById('fileInput').value = "";
        document.getElementById('uploadTitle').innerText = "Upload Payment File";
        document.getElementById('fileNameDisplay').innerText = "Click to browse your computer";
        const selectBtn = document.getElementById('selectBtn');
        selectBtn.innerText = "Select File";
        selectBtn.onclick = () => document.getElementById('fileInput').click();
        document.getElementById('cancelBtn').classList.add('hidden');
    }

    function handleUpload() {
        const fileInput = document.getElementById('fileInput');

        if (!fileInput.files.length) {
            showStatusAlert('error', 'No File', 'Please select a file first.');
            return;
        }

        const formData = new FormData();
        formData.append('import_type', 'payment');
        formData.append('loan_type_id', document.getElementById('loanTypeId').value);
        formData.append('payment_file', fileInput.files[0]);

        const uploadBtn = document.getElementById('uploadBtn');
        uploadBtn.disabled = true;
        uploadBtn.textContent = 'Uploading...';

        fetch('<?php echo $processImportUrl; ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(text => {
            let data;
            try { data = JSON.parse(text); }
            catch { data = {status:'error', message:text}; }

            if (data.status === 'success') {
                showStatusAlert('success', 'Success', 'Payment import completed.');
                setTimeout(() => location.reload(), 1500);
            } else {
                showStatusAlert('error', 'Error', data.message);
            }
        })
        .catch(() => {
            showStatusAlert('error', 'Error', 'Upload failed.');
        })
        .finally(() => {
            uploadBtn.disabled = false;
            uploadBtn.textContent = 'UPLOAD';
        });
    }

    function showStatusAlert(type, title, message) {
        const modal = document.getElementById('statusAlert');
        document.getElementById('alertTitle').innerText = title;
        document.getElementById('alertMessage').innerText = message;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 2000);
    }

    function openFilePreview(file) {
        const modal = document.getElementById('fileModal');
        const modalBody = document.getElementById('modalBody');
        document.getElementById('modalTitle').innerText = file.name;
        modal.classList.replace('hidden', 'flex');
        const reader = new FileReader();
        reader.onload = (e) => {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, {type: 'array'});
            const worksheet = workbook.Sheets[workbook.SheetNames[0]];
            modalBody.innerHTML = `<div class="p-4">${XLSX.utils.sheet_to_html(worksheet, { id: "excelTablePreview" })}</div>`;
        };
        reader.readAsArrayBuffer(file);
    }

    function openPaymentModal() { document.getElementById('paymentModal').classList.replace('hidden','flex'); }
    function closePaymentModal() { document.getElementById('paymentModal').classList.replace('flex','hidden'); }
    </script>

    <style>
        @keyframes pop { 0% { transform: scale(0.95); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        .animate-pop { animation: pop 0.2s ease-out forwards; }
        .visible-scrollbar::-webkit-scrollbar { width: 14px; height: 14px; }
        .visible-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .visible-scrollbar::-webkit-scrollbar-thumb { background: #a3a3a3; border-radius: 4px; border: 2px solid #f1f1f1; }
        #excelTablePreview { width: max-content; min-width: 100%; border-collapse: collapse; font-size: 0.8rem; background: white; }
        #excelTablePreview th, #excelTablePreview td { border-bottom: 1px solid #f3f4f6; padding: 0.75rem 1.5rem; text-align: left; white-space: nowrap; }
        #excelTablePreview th { font-weight: 700; background: white; color: #1f2937; padding-top: 1rem; padding-bottom: 1rem; }
    </style>
</body>
</html>