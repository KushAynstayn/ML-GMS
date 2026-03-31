<?php
require_once '../vendor/autoload.php';

use Cadc20239999\MlGms\PaymentService;

include('../includes/header.php');

// Get the type from URL, default to car
$type = $_GET['type'] ?? 'car';

// Payment-specific configuration
$payment_configs = [
    'car'        => ['id' => 1, 'title' => 'Car'],
    'motor'      => ['id' => 2, 'title' => 'Motor'],
    'home'       => ['id' => 3, 'title' => 'Home'],
    'salary'     => ['id' => 4, 'title' => 'Salary'],
    'personal'   => ['id' => 5, 'title' => 'Personal Property'],
    'realestate' => ['id' => 6, 'title' => 'Real Estate']
];

$current_config = $payment_configs[$type] ?? $payment_configs['car'];
$type_id = $current_config['id'];

$processImportUrl = '../actions/process_import.php';

$paymentService = new PaymentService();
$paymentRows = $paymentService->getPaymentSummariesByLoanType((int)$type_id);

// --- NEW SORTING LOGIC: Newest to Oldest ---
if (!empty($paymentRows)) {
    usort($paymentRows, function($a, $b) {
        $dateA = !empty($a['date_paid']) ? strtotime($a['date_paid']) : 0;
        $dateB = !empty($b['date_paid']) ? strtotime($b['date_paid']) : 0;
        return $dateB <=> $dateA; // Descending order
    });
}
?>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<style>
    /* --- NEW ANIMATIONS & EFFECTS --- */

    /* 1. Add Payment Jump Effect */
    @keyframes jump {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    .btn-jump:hover {
        animation: jump 0.5s ease-in-out infinite;
    }
    .btn-jump:active {
        animation: none;
    }

    /* 2. Piano-like Zoom & Light Red Hover */
    .piano-row {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 1;
    }
    .piano-row:hover {
        transform: scale(1.0);
        background-color: #fef2f2 !important; /* Light red/rose background */
        z-index: 10;
        box-shadow: 0 4px 12px rgba(213, 0, 0, 0.1);
    }

    /* 3. Fade/Swap effect for search results */
    .row-fade {
        animation: fadeIn 0.4s ease-out forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Existing Styles */
    .tab-btn:focus, .tab-btn:active { outline: none !important; box-shadow: none !important; }
    button:focus { outline: none !important; }
    .cursor-pointer-row { cursor: pointer; }
    
    @keyframes modalPop {
        0% { transform: scale(0.9); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    @keyframes iconBounce {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    .animate-modal-pop { animation: modalPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    .animate-icon-bounce { animation: iconBounce 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.1s both; }

    .visible-scrollbar::-webkit-scrollbar { width: 14px; height: 14px; }
    .visible-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .visible-scrollbar::-webkit-scrollbar-thumb { background: #a3a3a3; border-radius: 4px; border: 2px solid #f1f1f1; }

    #excelTablePreview { width: max-content; min-width: 100%; border-collapse: collapse; font-size: 0.8rem; background: white; }
    #excelTablePreview th, #excelTablePreview td { border-bottom: 1px solid #f3f4f6; padding: 0.75rem 1.5rem; text-align: left; white-space: nowrap; }
    #excelTablePreview th { font-weight: 700; background: white; color: #1f2937; padding-top: 1rem; padding-bottom: 1rem; }
</style>

<body class="h-screen overflow-hidden flex flex-col bg-gray-50">
    <div class="flex flex-1 overflow-hidden">
        <?php include('../includes/sidebar.php'); ?>

        <main class="flex-1 bg-gray-50 p-8 overflow-y-auto animate-content">
            <header class="mb-6 flex flex-col md:flex-row justify-between items-start">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        <?php echo htmlspecialchars($current_config['title']); ?> <span class="text-[#D50000]">Payments</span>
                    </h2>
                    <p class="text-gray-500 font-medium mt-2 mb-8">
                        View and track the status of <?php echo strtolower(htmlspecialchars($current_config['title'])); ?> payment records.
                    </p>
                </div>

                <div class="flex gap-4">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 min-w-[140px] relative">
                        <p class="text-[10px] font-bold text-gray-400 uppercase leading-tight">
                            Total <?php echo htmlspecialchars($current_config['title']); ?><br>Borrowers
                        </p>
                        <p class="text-xl font-black text-gray-900 mt-2" id="summaryTotalCount">0</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 min-w-[160px]">
                        <p class="text-[10px] font-bold text-gray-400 uppercase leading-tight">
                            Total Amount<br>Collected
                        </p>
                        <p class="text-xl font-black text-[#D50000] mt-2">
                            ₱<span id="summaryTotalAmount">0.00</span>
                        </p>
                    </div>
                </div>
            </header>

            <div class="flex flex-wrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="flex-1 min-w-[200px] relative">
                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Search account or reference..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D50000]/20 text-sm"
                    >
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <div class="relative">
                    <select
                        onchange="window.location.replace('?type=' + this.value)"
                        class="appearance-none bg-white border border-gray-200 text-gray-700 py-2 px-4 pr-8 rounded-lg text-sm focus:outline-none cursor-pointer font-medium"
                    >
                        <?php foreach ($payment_configs as $key => $config): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($type === $key) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($config['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"></path>
                        </svg>
                    </div>
                </div>

                <?php include('../includes/date_picker.php'); ?>

                <button
                    onclick="openPaymentModal()"
                    class="bg-[#D50000] text-white px-4 py-2 rounded-lg text-xs font-bold uppercase hover:bg-[#b00000] transition-all shadow-md btn-jump"
                >
                    Add Payment
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="w-full" id="paymentsTable">
                        <thead class="bg-[#D50000]">
                            <tr>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Date Paid</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Account Name</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Payment Reference Number</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Paid Amount</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Date Applied</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Status</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Due Next Month</th>
                            </tr>
                        </thead>

                        <tbody id="tableBody" class="divide-y divide-gray-100">
                            <?php if (!empty($paymentRows)): ?>
                                <?php foreach ($paymentRows as $row): ?>
                                    <?php
                                    $statusLabel = trim((string)($row['status_label'] ?? 'Unpaid'));
                                    $statusLower = strtolower($statusLabel);

                                    $statusClass = 'bg-red-100 text-red-700';
                                    if ($statusLower === 'paid') {
                                        $statusClass = 'bg-green-100 text-green-700';
                                    } elseif ($statusLower === 'partial') {
                                        $statusClass = 'bg-orange-100 text-orange-700';
                                    }

                                    $datePaid = !empty($row['date_paid']) ? date('m/d/Y', strtotime($row['date_paid'])) : '--';
                                    $accountName = $row['account_name'] ?? '--';
                                    $paymentReferenceNo = $row['payment_reference_no'] ?? '--';
                                    $paidAmount = (float)($row['paid_amount'] ?? 0);
                                    $dateApplied = $row['date_applied'] ?? '--';
                                    $monthlyAmortization = (float)($row['monthly_amortization'] ?? 0);
                                    $unpaidBalance = (float)($row['total_partial_balance'] ?? 0);
                                    $vat = (float)($row['total_vat'] ?? 0);
                                    $penalty = (float)($row['total_penalty'] ?? 0);
                                    $interestDiff = (float)($row['total_interest_diff'] ?? 0);
                                    $advancePayment = (float)($row['advance_payment'] ?? 0);
                                    $nextDue = (float)($row['next_due'] ?? 0);
                                    $nextDueMonth = trim((string)($row['next_due_month'] ?? ''));
                                    $nextMonthLabel = $nextDueMonth !== '' ? 'For ' . $nextDueMonth : 'For Next Month';
                                    ?>
                                    <tr
                                        class="hover:bg-gray-50 transition-colors cursor-pointer-row piano-row"
                                        onclick="showBreakdown(this)"
                                        data-amort="<?php echo htmlspecialchars(number_format($monthlyAmortization, 2, '.', '')); ?>"
                                        data-prev-paid="<?php echo htmlspecialchars(number_format($paidAmount, 2, '.', '')); ?>"
                                        data-prev-unpaid="<?php echo htmlspecialchars(number_format($unpaidBalance, 2, '.', '')); ?>"
                                        data-vat="<?php echo htmlspecialchars(number_format($vat, 2, '.', '')); ?>"
                                        data-penalty="<?php echo htmlspecialchars(number_format($penalty, 2, '.', '')); ?>"
                                        data-interest-diff="<?php echo htmlspecialchars(number_format($interestDiff, 2, '.', '')); ?>"
                                        data-advance="<?php echo htmlspecialchars(number_format($advancePayment, 2, '.', '')); ?>"
                                        data-next-due="<?php echo htmlspecialchars(number_format($nextDue, 2, '.', '')); ?>"
                                        data-next-month-label="<?php echo htmlspecialchars($nextMonthLabel); ?>"
                                    >
                                        <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap text-center">
                                            <?php echo htmlspecialchars($datePaid); ?>
                                        </td>
                                        <td class="px-4 py-3 text-[12px] font-bold text-gray-900 whitespace-nowrap text-center">
                                            <?php echo htmlspecialchars($accountName); ?>
                                        </td>
                                        <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap text-center">
                                            <?php echo htmlspecialchars($paymentReferenceNo); ?>
                                        </td>
                                        <td class="px-4 py-3 text-[12px] font-bold text-gray-900 whitespace-nowrap text-center">
                                            <?php echo number_format($paidAmount, 2); ?>
                                        </td>
                                        <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap text-center">
                                            <?php echo htmlspecialchars(strtoupper($dateApplied)); ?>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="<?php echo $statusClass; ?> px-2 py-0.5 rounded-full text-[10px] font-bold uppercase whitespace-nowrap">
                                                <?php echo htmlspecialchars($statusLabel); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-[12px] text-gray-600 whitespace-nowrap text-center">
                                            <?php echo number_format($nextDue, 2); ?><?php echo $nextDueMonth !== '' ? ' (' . htmlspecialchars(strtoupper($nextDueMonth)) . ')' : ''; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <tr id="noResultsRow" class="<?php echo !empty($paymentRows) ? 'hidden' : ''; ?>">
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400 text-xs italic">
                                    No records found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="breakdownModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[150] p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-modal-pop">
            <div class="p-5 border-b flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Payment <span class="text-[#D50000]">Breakdown</span></h3>
            </div>

            <div class="p-6 space-y-3">
                <div class="flex justify-between text-sm py-2 border-b border-gray-50">
                    <span class="text-gray-500 font-medium">Amount Paid</span>
                    <span class="font-bold text-gray-900" id="bdPaid">₱0.00</span>
                </div>

                <div class="flex justify-between text-sm py-2 border-b border-gray-50">
                    <span class="text-gray-500 font-medium">Monthly Amortization</span>
                    <span class="font-bold text-gray-900" id="bdAmort">₱0.00</span>
                </div>

                <div class="flex justify-between text-sm py-2 border-b border-gray-50">
                    <span class="text-gray-500 font-medium">Unpaid/Balance</span>
                    <span class="font-bold text-gray-900" id="bdUnpaid">₱0.00</span>
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

                <hr class="border-gray-100">

                <div class="flex justify-between text-sm py-2 border-b border-gray-50">
                    <span class="text-gray-500 font-medium">Advance Payment</span>
                    <span class="font-bold text-gray-900" id="bdAdvance">₱0.00</span>
                </div>

                <div class="pt-4 border-t-2 border-dashed border-gray-100 mt-4">
                    <div class="flex justify-between items-end">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase">Total Due Next Month</span>
                            <p id="nextMonthLabel" class="text-[10px] font-bold text-[#D50000] uppercase tracking-wider">For Next Month</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xl font-black text-[#D50000]" id="bdTotalDue">₱0.00</span>
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-2 italic">
                        *Includes next month's amort + unpaid balance + penalties.
                    </p>
                </div>
            </div>

            <div class="p-4 bg-gray-50 text-center">
                <button
                    onclick="closeBreakdownModal()"
                    class="w-full bg-gray-800 text-white py-2 rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-gray-900 transition-all"
                >
                    Close Details
                </button>
            </div>
        </div>
    </div>

    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[110] p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden animate-modal-pop">
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
                    <button id="uploadBtn" onclick="handleUpload()" class="bg-[#D50000] text-white px-8 py-3 rounded-lg font-bold hover:bg-red-700 transition-colors text-sm uppercase tracking-wide shadow-lg">
                        UPLOAD
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="statusAlert" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[200] p-6">
        <div class="bg-white rounded-2xl shadow-2xl max-w-[320px] w-full overflow-hidden animate-modal-pop border border-gray-100">
            <div id="alertContent" class="p-8 text-center">
                <div id="alertIconContainer" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5 animate-icon-bounce">
                </div>
                <h3 id="alertTitle" class="text-xl font-bold text-gray-800 mb-1"></h3>
                <p id="alertMessage" class="text-sm text-gray-500 font-normal mb-6 leading-relaxed"></p>
                
                <button id="alertOkBtn" onclick="document.getElementById('statusAlert').classList.replace('flex', 'hidden')" 
                    class="w-full bg-[#1e293b] text-white py-3 rounded-xl text-sm font-semibold uppercase tracking-wider hover:bg-gray-900 transition-all shadow-sm">
                    OK
                </button>
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
        document.addEventListener("DOMContentLoaded", filterTable);

        function formatPeso(val) {
            const num = parseFloat(val || 0);
            return "₱" + num.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function showBreakdown(row) {
            const data = row.dataset;

            const amort = parseFloat(data.amort || 0);
            const paid = parseFloat(data.prevPaid || 0);
            const unpaid = parseFloat(data.prevUnpaid || 0);
            const vat = parseFloat(data.vat || 0);
            const penalty = parseFloat(data.penalty || 0);
            const interestDiff = parseFloat(data.interestDiff || 0);
            const advance = parseFloat(data.advance || 0);
            const nextDue = parseFloat(data.nextDue || 0);

            document.getElementById('bdAmort').innerText = formatPeso(amort);
            document.getElementById('bdPaid').innerText = formatPeso(paid);
            document.getElementById('bdUnpaid').innerText = formatPeso(unpaid);
            document.getElementById('bdVat').innerText = formatPeso(vat);
            document.getElementById('bdPenalty').innerText = formatPeso(penalty);
            document.getElementById('bdInterest').innerText = formatPeso(interestDiff);
            document.getElementById('bdAdvance').innerText = formatPeso(advance);
            document.getElementById('bdTotalDue').innerText = formatPeso(nextDue);
            document.getElementById('nextMonthLabel').innerText = data.nextMonthLabel || 'For Next Month';

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
                if (row.id === 'noResultsRow' || row.cells.length < 7) return;

                const nameText = row.cells[1].textContent.toUpperCase();
                const refText = row.cells[2].textContent.toUpperCase();
                const matchesSearch = nameText.includes(searchText) || refText.includes(searchText);

                const rowDateStr = row.cells[0].textContent.trim();

                let matchesDate = true;

                if (rowDateStr !== '--' && startVal && endVal) {
                    const [m, d, y] = rowDateStr.split('/');
                    const rowDate = new Date(`${y}-${m}-${d}`).setHours(0, 0, 0, 0);
                    const start = new Date(startVal).setHours(0, 0, 0, 0);
                    const end = new Date(endVal).setHours(0, 0, 0, 0);
                    matchesDate = rowDate >= start && rowDate <= end;
                } else if (rowDateStr !== '--' && startVal) {
                    const [m, d, y] = rowDateStr.split('/');
                    const rowDate = new Date(`${y}-${m}-${d}`).setHours(0, 0, 0, 0);
                    const start = new Date(startVal).setHours(0, 0, 0, 0);
                    matchesDate = rowDate === start;
                }

                if (matchesSearch && matchesDate) {
                    // APPLY FADE EFFECT IF IT WAS HIDDEN
                    if (row.style.display === "none") {
                        row.classList.add('row-fade');
                    }
                    row.style.display = "";
                    visibleRows++;

                    const amtString = row.cells[3].textContent.replace(/,/g, '').trim();
                    const amt = parseFloat(amtString);

                    if (!isNaN(amt)) {
                        totalAmount += amt;
                    }
                } else {
                    row.style.display = "none";
                    row.classList.remove('row-fade');
                }
            });

            document.getElementById('summaryTotalCount').innerText = visibleRows;
            document.getElementById('summaryTotalAmount').innerText = totalAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            const noResultsRow = document.getElementById('noResultsRow');
            if (noResultsRow) {
                noResultsRow.style.display = visibleRows === 0 ? "table-row" : "none";
            }
        }

        document.getElementById('searchInput').addEventListener('keyup', filterTable);

        function openPaymentModal() {
            document.getElementById('paymentModal').classList.replace('hidden', 'flex');
        }

        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            modal.classList.replace('flex', 'hidden');
            resetFileInput();
        }

        document.getElementById('fileInput').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('uploadTitle').innerText = "File Selected";
                document.getElementById('fileNameDisplay').innerHTML =
                    `Selected: <span class="text-[#D50000] font-bold cursor-pointer hover:underline">${file.name}</span>`;
                document.getElementById('fileNameDisplay').querySelector('span').onclick = () => openFilePreview(file);
                document.getElementById('selectBtn').innerText = "View File";
                document.getElementById('selectBtn').onclick = (ev) => {
                    ev.preventDefault();
                    openFilePreview(file);
                };
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
                showStatusAlert('error', 'Selection Required', 'Please select a file first.');
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
                try {
                    data = JSON.parse(text);
                } catch {
                    data = { status: 'error', message: text };
                }

                if (data.status === 'success') {
                    showStatusAlert('success', 'Success!', 'Records imported successfully.');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showStatusAlert('error', 'Upload Failed', data.message || 'There was an error processing your file.');
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
            const iconContainer = document.getElementById('alertIconContainer');
            const okBtn = document.getElementById('alertOkBtn');
            
            // Remove animation classes to re-trigger them
            modal.querySelector('.bg-white').classList.remove('animate-modal-pop');
            iconContainer.classList.remove('animate-icon-bounce');

            document.getElementById('alertTitle').innerText = title;
            document.getElementById('alertMessage').innerText = message;

            if (type === 'success') {
                iconContainer.className = "w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5 bg-green-50 animate-icon-bounce";
                iconContainer.innerHTML = `<svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>`;
                okBtn.style.display = "none";
            } else {
                iconContainer.className = "w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5 bg-red-50 animate-icon-bounce";
                iconContainer.innerHTML = `<svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>`;
                okBtn.style.display = "block";
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Void offset to force reflow and restart animation
            void modal.offsetWidth; 
            modal.querySelector('.bg-white').classList.add('animate-modal-pop');
        }

        function openFilePreview(file) {
            const modal = document.getElementById('fileModal');
            const modalBody = document.getElementById('modalBody');

            document.getElementById('modalTitle').innerText = file.name;
            modal.classList.replace('hidden', 'flex');

            const reader = new FileReader();
            reader.onload = (e) => {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const worksheet = workbook.Sheets[workbook.SheetNames[0]];
                modalBody.innerHTML = `<div class="p-4">${XLSX.utils.sheet_to_html(worksheet, { id: "excelTablePreview" })}</div>`;
            };
            reader.readAsArrayBuffer(file);
        }
    </script>
</body>