<style>
    /* This ensures the modal wrapper always fills the screen and centers the content */
    #amortizationModal {
        position: fixed !important;
        inset: 0 !important;
        width: 100% !important;
        height: 100% !important;
        display: none; /* Controlled by the 'hidden' class */
        align-items: center !important;
        justify-content: center !important;
    }

    /* When 'hidden' is removed by your script, force flex display */
    #amortizationModal:not(.hidden) {
        display: flex !important;
    }

    /* Prevents the modal from jumping if the table is very long */
    #amortizationModal > div {
        margin: auto !important;
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div id="amortizationModal" class="hidden fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center py-6 px-4">
    <div class="bg-white w-full max-w-5xl rounded-xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200 flex flex-col">
        
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col gap-2">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-2">
                    <button onclick="closeAmortization()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </button>
                </div>

                <div class="relative inline-block text-left shrink-0" id="amortizationDownloadDropdown">
                    <button onclick="toggleAmortizationDropdown()" class="flex items-center gap-2 bg-red-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-red-700 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                        </svg>
                    </button>

                    <div id="amortizationDropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-[60] overflow-hidden">
                        <div class="py-1">
                            <button onclick="exportData('excel')" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 transition-colors">
                                <span class="text-green-600 font-bold">EXCEL</span>
                            </button>
                            <button onclick="exportData('pdf')" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 border-t border-gray-100 transition-colors">
                                <span class="text-red-600 font-bold">PDF</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-center justify-center">
                <img src="../assets/images/ml.png" id="mlLogo" alt="M Lhuillier" class="h-8">
                <h4 class="text-sm font-semibold text-gray-500 mt-1">SECOND LEDGER</h4>
            </div>

        <div id="amortizationPrintArea" class="p-6 overflow-y-auto max-h-[70vh]">
            <div class="border border-gray-300 rounded-sm overflow-hidden text-black">
                
                <div class="text-[13px]">
                    <div class="grid grid-cols-12 border-b border-gray-300">
                        <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">Account Name :</div>
                        <div class="col-span-10 p-2 font-bold" id="modalDispName">---</div>
                    </div>

                    

                    <div class="grid grid-cols-12 border-b border-gray-300">
                        <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">Contact Number</div>
                        <div class="col-span-4 p-2 border-r border-gray font-bold" id="modalDispContact">---</div>
                        <div class="col-span-3 bg-gray-50 p-2 font-bold border-r border-gray-300">Loan Amount (Principal) :</div>
                        <div class="col-span-3 p-2 font-bold text-right" id="modalDispPrincipal">0.00</div>
                    </div>

                    <div class="grid grid-cols-12 border-b border-gray-300">
                        <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">Reference Number :</div>
                        <div class="col-span-4 p-2 border-r border-gray-300 font-bold" id="modalDispRef">---</div>
                        <div class="col-span-3 bg-gray-50 p-2 font-bold border-r border-gray-300">Amount (5% off) :</div>
                        <div class="col-span-3 p-2 font-bold text-right" id="modalDispAmount">0.00</div>
                    </div>

                    <div class="grid grid-cols-12 border-b border-gray-300">
                        <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">Date Granted :</div>
                        <div class="col-span-4 p-2 border-r border-gray-300 font-bold" id="modalDispDate">---</div>
                        <div class="col-span-3 bg-gray-50 p-2 font-bold border-r border-gray-300">Term :</div>
                        <div class="col-span-3 p-2 text-right font-bold"><span id="modalDispTerm">0</span> <span class="text-gray-400 font-normal">months</span></div>
                    </div>

                    <div class="grid grid-cols-12 border-b border-gray-300">
                        <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">Maturity Date :</div>
                        <div class="col-span-4 p-2 border-r border-gray-300 font-bold" id="modalDispMaturity">---</div>
                        <div class="col-span-3 bg-gray-50 p-2 font-bold border-r border-gray-300">Interest Rate (AOR)</div>
                        <div class="col-span-3 p-2 text-right font-bold" id="modalDispRate">0%</div>
                    </div>

                    <div class="grid grid-cols-12 bg-gray-50 items-center border-b border-gray-300">
                        <div class="col-span-6 border-r border-gray-300 h-full"></div> 
                        <div class="col-span-3 p-2 font-bold border-r border-gray-300 tracking-wider">Monthly Amortization</div>
                        <div class="col-span-3 p-2 font-bold text-right text-base text-black" id="modalDispMonthly">0.00</div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-[12px] border-collapse text-black" id="amortizationTable">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th colspan="4" class="p-2 text-center border-r border-gray-300 font-bold uppercase tracking-wider">Application</th>
                                <th colspan="3" class="bg-white"></th>
                            </tr>
                            <tr class="bg-gray-50 border-b border-gray-300 font-bold uppercase">
                                <th class="p-2 border-r border-gray-300 text-center w-8">#</th>
                                <th class="p-2 border-r border-gray-300 text-center">Date</th>
                                <th class="p-2 border-r border-gray-300 text-center">Principal</th>
                                <th class="p-2 border-r border-gray-300 text-center">Interest</th>
                                <th class="p-2 border-r border-gray-300 text-center">Total Amount</th>
                                <th class="p-2 border-r border-gray-300 text-center">Principal Balance</th>
                                <th class="p-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="amortizationTableBody" class="divide-y divide-gray-300">
                            </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-x-12 text-[13px] text-black">
                <div>
                    <p class="mb-4">Prepared by:</p>
                    <div class="border-b border-black w-48 mb-1 pb-1">
                        <input type="text" id="inputPreparedBy" class="font-bold bg-transparent border-none outline-none w-full p-0" value="Reynaldo Lapiña Jr.">
                    </div>
                    <p class="text-xs uppercase">LOANS EVALUATOR</p>
                </div>
                <div>
                    <p class="mb-4">Checked by:</p>
                    <div class="border-b border-black w-48 mb-1 pb-1">
                        <input type="text" id="inputCheckedBy" class="font-bold bg-transparent border-none outline-none w-full p-0" value="Jacob N. Lomotos">
                    </div>
                    <p class="text-xs">ML Lending Head</p>
                </div>
                <div class="mt-8">
                    <p class="mb-4">Conforme:</p>
                    <div class="border-b border-black w-48 mb-1 pb-1">
                        <input type="text" id="inputConforme1" class="font-bold bg-transparent border-none outline-none w-full p-0" placeholder="Signer Name">
                    </div>
                    <p class="text-xs">Debtor/Creditor</p>
                </div>
                <div class="mt-8 self-end">
                    <div class="border-b border-black w-48 mb-1 pb-1">
                        <input type="text" id="inputConforme2" class="font-bold bg-transparent border-none outline-none w-full p-0" placeholder="Signer Name">
                    </div>
                    <p class="text-xs">Debtor/Creditor</p>
                </div>
            </div>
        </div>

        <div class="p-3 bg-gray-50 border-t border-gray-100"></div>
    </div>
</div>

<script>
function toggleAmortizationDropdown() {
    const menu = document.getElementById('amortizationDropdownMenu');
    menu.classList.toggle('hidden');
}

window.addEventListener('click', function(event) {
    const dropdown = document.getElementById('amortizationDownloadDropdown');
    const menu = document.getElementById('amortizationDropdownMenu');
    if (dropdown && !dropdown.contains(event.target)) {
        if (menu) menu.classList.add('hidden');
    }
});

function closeAmortization() {
    document.getElementById('amortizationModal').classList.add('hidden');
}

function exportData(format) {
    const pnNumber = document.getElementById('modalDispRef').innerText;

    if (format === 'excel') {
        const wb = XLSX.utils.book_new();
        
        // --- 1. Data Collection ---
        const accountName = document.getElementById('modalDispName').innerText;
        const contactNum = document.getElementById('modalDispContact').innerText;
        const loanAmt = document.getElementById('modalDispAmount').innerText;
        const term = document.getElementById('modalDispTerm').innerText;
        const rate = document.getElementById('modalDispRate').innerText;
        const dateGrant = document.getElementById('modalDispDate').innerText;
        const maturity = document.getElementById('modalDispMaturity').innerText;
        const monthly = document.getElementById('modalDispMonthly').innerText;

        // --- 2. Styling Definitions ---
        const s_Bold = { font: { bold: true, name: 'Arial', sz: 10 } };
        const s_Header = { font: { bold: true }, alignment: { horizontal: "center" }, fill: { fgColor: { rgb: "F3F4F6" } } };
        const s_Number = { numFmt: '#,##0.00', alignment: { horizontal: "right" } };
        const s_Center = { alignment: { horizontal: "center" } };

        // --- 3. Build the Matrix based on your reference ---
        const rows = [
            /* Row 1 */ [{ v: "Account Name :", s: s_Bold }, { v: accountName, s: s_Bold }, "", "", "", ""],
            /* Row 2 */ [{ v: "Contact Number", s: s_Bold }, { v: contactNum, s: s_Bold }, "", "", "", ""],
            /* Row 3 */ [{ v: "Reference Number :", s: s_Bold }, { v: pnNumber }, { v: "Loan Amount(5% off) :", s: s_Bold }, { v: parseFloat(loanAmt.replace(/,/g,'')), s: s_Bold, t:'n', z:'#,##0.00' }, "", ""],
            /* Row 4 */ [{ v: "Date Granted :", s: s_Bold }, { v: dateGrant }, { v: "Term :", s: s_Bold }, { v: term + " months", s: s_Bold }, "", ""],
            /* Row 5 */ [{ v: "Maturity Date :", s: s_Bold }, { v: maturity }, { v: "Interest Rate (AOR)", s: s_Bold }, { v: rate, s: s_Bold }, "", ""],
            /* Row 6 */ ["", "", { v: "Monthly Amortization", s: s_Bold }, { v: parseFloat(monthly.replace(/,/g,'')), s: s_Bold, t:'n', z:'#,##0.00' }, "", ""],
            /* Row 7 */ ["", "", { v: "APPLICATION", s: s_Header }, "", "", ""], // Title Row
            /* Row 8 */ [
                { v: "#", s: s_Header }, 
                { v: "DATE", s: s_Header }, 
                { v: "PRINCIPAL", s: s_Header }, 
                { v: "INTEREST", s: s_Header }, 
                { v: "TOTAL AMOUNT", s: s_Header }, 
                { v: "PRINCIPAL BALANCE", s: s_Header }, 
                { v: "STATUS", s: s_Header }
            ],
            // Row 9: The initial balance row (Row before records start)
            ["", "", "", "", "", { v: parseFloat(loanAmt.replace(/,/g,'')), s: s_Bold, t:'n', z:'#,##0.00' }, ""]
        ];

        // --- 4. Append Table Records ---
        const tbodyRows = document.querySelectorAll('#amortizationTableBody tr');
        tbodyRows.forEach((tr) => {
            const cols = tr.querySelectorAll('td');
            if (cols.length >= 6) {
                rows.push([
                    { v: cols[0]?.innerText || "", s: s_Center }, // #
                    { v: cols[1]?.innerText || "", s: s_Center }, // Date
                    { v: parseFloat(cols[2]?.innerText.replace(/,/g, '')) || 0, s: s_Number }, // Principal
                    { v: parseFloat(cols[3]?.innerText.replace(/,/g, '')) || 0, s: s_Number }, // Interest
                    { v: parseFloat(cols[4]?.innerText.replace(/,/g, '')) || 0, s: s_Number }, // Total
                    { v: parseFloat(cols[5]?.innerText.replace(/,/g, '')) || 0, s: s_Number }, // Balance
                    { v: cols[6]?.innerText || "", s: s_Center }  // Status
                ]);
            }
        });

        const ws = XLSX.utils.aoa_to_sheet(rows);

        // Merge cells for the "APPLICATION" header to span across Principal/Interest
        ws['!merges'] = [
            { s: { r: 6, c: 2 }, e: { r: 6, c: 3 } } // Merges C7:D7
        ];

        // Column Widths
        ws['!cols'] = [{ wch: 5 }, { wch: 15 }, { wch: 15 }, { wch: 15 }, { wch: 18 }, { wch: 20 }, { wch: 12 }];

        XLSX.utils.book_append_sheet(wb, ws, "Amortization");
        XLSX.writeFile(wb, `Amortization_${pnNumber}.xlsx`);

    } else if (format === 'pdf') {
        // ... (Keep your existing PDF logic)
        const element = document.getElementById('amortizationPrintArea');
        const opt = {
            margin: 0.5,
            filename: `Amortization_${pnNumber}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }
    document.getElementById('amortizationDropdownMenu').classList.add('hidden');
}
</script>