<?php include('../includes/header.php'); ?>

<style>
    .tab-btn:focus, .tab-btn:active { outline: none !important; box-shadow: none !important; }
    button:focus { outline: none !important; }
    /* Table Styling */
    .bg-ml-red { background-color: #D50000; }
    .text-ml-red { color: #D50000; }
    .subtotal-row { background-color: #f9fafb; font-weight: 700; }
    .grand-total-row { background-color: #f3f4f6; font-weight: 800; border-top: 2px solid #D50000; }
    .category-header { background-color: #fee2e2; color: #b91c1c; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<div class="flex h-screen overflow-hidden">
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto h-full">
        <header class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Running <span class="text-ml-red">Receivables</span></h2>
                    <p class="text-gray-500 font-medium mt-2">Summary of outstanding balances across regions and loan types.</p>
                </div>

                <div class="flex gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex shrink-0 bg-gray-100 p-1 rounded-lg border border-gray-200">
                        <button onclick="setDateMode('single')" id="btnSingle" class="px-3 py-1.5 text-[10px] font-bold uppercase rounded-md transition-all bg-white text-[#D50000] shadow-sm">Single Date</button>
                        <button onclick="setDateMode('range')" id="btnRange" class="px-3 py-1.5 text-[10px] font-bold uppercase rounded-md transition-all text-gray-500 hover:text-gray-700">Select Range</button>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500 hover:border-gray-300 transition-colors bg-white">
                            <span id="dateLabel" class="text-[10px] font-bold uppercase">Date</span>
                            <input type="date" id="startDate" onchange="updateDateDisplay()" class="focus:outline-none bg-transparent cursor-pointer uppercase text-xs">
                        </div>
                        
                        <div id="toDateContainer" class="hidden flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500 hover:border-gray-300 transition-colors bg-white">
                            <span class="text-[10px] font-bold uppercase">To</span>
                            <input type="date" id="endDate" onchange="updateDateDisplay()" class="focus:outline-none bg-transparent cursor-pointer uppercase text-xs">
                        </div>
                    </div>

                    <div class="relative inline-block text-left shrink-0" id="downloadDropdown">
                        <button onclick="toggleDropdown()" class="flex items-center gap-2 bg-[#D50000] text-white px-5 py-2 rounded-lg font-medium hover:bg-[#B70000] transition-all shadow-sm text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download
                        </button>
                        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden">
                            <button onclick="downloadExcel()" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"><span class="text-green-600 font-bold">EXCEL</span></button>
                            <button onclick="downloadPDF()" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 border-t border-gray-100"><span class="text-[#D50000] font-bold">PDF</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <hr class="border-gray-300 mb-4">
        <p id="asOfDateText" class="text-center text-lg font-extrabold text-gray-800 uppercase mb-6 tracking-wide">Select a date to view report</p>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <table id="receivableTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-ml-red text-white">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider">Loans</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center">LNCR</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center">VISMIN</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center">HEAD OFFICE</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center">TOTAL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <tr class="category-header"><td colspan="5" class="px-6 py-3 text-sm">ML LOANS</td></tr>
                    <?php 
                    $ml_loans = ['Auto Loan', 'Motorcycle Loan', '2-Wheels', '3-Wheels', 'Real-Estate Loan', 'Commercial Loan', 'Salary Loan', 'Truck Loan'];
                    foreach($ml_loans as $loan): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium"><?php echo $loan; ?></td>
                        <td class="px-6 py-4 text-sm text-center">0.00</td>
                        <td class="px-6 py-4 text-sm text-center">0.00</td>
                        <td class="px-6 py-4 text-sm text-center">0.00</td>
                        <td class="px-6 py-4 text-sm text-center font-semibold">0.00</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="subtotal-row border-t-2 border-gray-200">
                        <td class="px-6 py-4 text-sm uppercase">Subtotal (ML Loans)</td>
                        <td class="px-6 py-4 text-sm text-center">0.00</td>
                        <td class="px-6 py-4 text-sm text-center">0.00</td>
                        <td class="px-6 py-4 text-sm text-center">0.00</td>
                        <td class="px-6 py-4 text-sm text-center text-ml-red">0.00</td>
                    </tr>
                    <tr class="category-header"><td colspan="5" class="px-6 py-3 text-sm">OTHER LOANS</td></tr>
                    <?php 
                    $other_loans = ['Small Business Loan', "Pensioner's Loan"];
                    foreach($other_loans as $loan): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium"><?php echo $loan; ?></td>
                        <td class="px-6 py-4 text-sm text-center">0.00</td>
                        <td class="px-6 py-4 text-sm text-center">0.00</td>
                        <td class="px-6 py-4 text-sm text-center">0.00</td>
                        <td class="px-6 py-4 text-sm text-center font-semibold">0.00</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="subtotal-row border-t-2 border-gray-200">
                        <td class="px-6 py-4 text-sm uppercase">Subtotal (Other Loans)</td>
                        <td class="px-6 py-4 text-sm text-center">0.00</td>
                        <td class="px-6 py-4 text-sm text-center">0.00</td>
                        <td class="px-6 py-4 text-sm text-center">0.00</td>
                        <td class="px-6 py-4 text-sm text-center text-ml-red">0.00</td>
                    </tr>
                    <tr class="grand-total-row">
                        <td class="px-6 py-5 text-base uppercase">Grand Total</td>
                        <td class="px-6 py-5 text-sm text-center">0.00</td>
                        <td class="px-6 py-5 text-sm text-center">0.00</td>
                        <td class="px-6 py-5 text-sm text-center">0.00</td>
                        <td class="px-6 py-5 text-lg text-center text-ml-red">0.00</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
let dateMode = 'single';

function updateDateDisplay() {
    const startVal = document.getElementById('startDate').value;
    const endVal = document.getElementById('endDate').value;
    const display = document.getElementById('asOfDateText');

    if (!startVal) {
        display.innerText = "Select a date to view report";
        return;
    }

    const start = new Date(startVal);
    const monthName = start.toLocaleDateString('en-US', { month: 'long' });
    const year = start.getFullYear();

    if (dateMode === 'single') {
        const day = start.getDate();
        display.innerText = `AS OF ${monthName.toUpperCase()} ${day}, ${year}`;
    } else if (endVal) {
        const end = new Date(endVal);
        const startDay = start.getDate();
        const endDay = end.getDate();
        display.innerText = `AS OF ${monthName.toUpperCase()} ${startDay}-${endDay}, ${year}`;
    }
}

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
        document.getElementById('endDate').value = "";
    } else {
        toContainer.classList.remove('hidden');
        dateLabel.innerText = 'From';
        btnRange.classList.add('bg-white', 'text-[#D50000]', 'shadow-sm');
        btnSingle.classList.remove('bg-white', 'text-[#D50000]', 'shadow-sm');
    }
    updateDateDisplay();
}

function toggleDropdown() {
    document.getElementById('dropdownMenu').classList.toggle('hidden');
}

window.onclick = function(event) {
    if (!event.target.closest('#downloadDropdown')) {
        document.getElementById('dropdownMenu').classList.add('hidden');
    }
}

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'letter');
    doc.text("Running Receivables Report", 14, 15);
    doc.text(document.getElementById('asOfDateText').innerText, 14, 22);
    doc.autoTable({ html: '#receivableTable', startY: 28, theme: 'grid' });
    doc.save('Running_Receivables.pdf');
}

async function downloadExcel() {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Receivables');
    worksheet.getCell('A1').value = "Running Receivables Report";
    worksheet.getCell('A2').value = document.getElementById('asOfDateText').innerText;
    
    const table = document.getElementById('receivableTable');
    const rows = Array.from(table.querySelectorAll('tr'));
    
    rows.forEach((tr, rowIndex) => {
        const cells = Array.from(tr.querySelectorAll('th, td'));
        cells.forEach((cell, colIndex) => {
            const excelCell = worksheet.getRow(rowIndex + 4).getCell(colIndex + 1);
            excelCell.value = cell.innerText.trim();
        });
    });
    
    const buffer = await workbook.xlsx.writeBuffer();
    saveAs(new Blob([buffer]), 'Running_Receivables.xlsx');
}
</script>