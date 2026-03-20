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
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<div id="amortizationModal" class="hidden fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center py-6 px-4">
    <div class="bg-white w-full max-w-6xl rounded-xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200 flex flex-col">
        
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <button onclick="closeAmortization()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </button>

            <div class="relative inline-block text-left" id="amortizationDownloadDropdown">
                <button onclick="toggleAmortizationDropdown()" class="flex items-center gap-2 bg-red-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-red-700 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                    </svg>
                </button>

                <div id="amortizationDropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
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

        <div class="flex flex-col items-center justify-center px-6 py-4 border-b border-gray-100 gap-2">
            <img src="../assets/images/ml.png" id="mlLogo" alt="M Lhuillier" class="h-8">
            <h4 class="text-sm font-semibold text-gray-500 mt-1 uppercase" id="dynamicLedgerHeader">PRIMARY LEDGER</h4>
        </div>

        <div id="amortizationPrintArea" class="p-6 overflow-y-auto max-h-[70vh]">

            <div class="border border-gray-300 rounded-sm overflow-hidden text-black text-[13px] mb-4">
                
                <div class="grid grid-cols-12 border-b border-gray-300">
                    <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">Account Name :</div>
                    <div class="col-span-4 p-2 font-bold border-r border-gray-300" id="modalDispName">---</div>
                    
                    <div class="col-span-3 bg-gray-50 p-2 font-bold border-r border-gray-300">
                        <span id="principalLabel">Loan Amount (Principal) :</span>
                        <span id="netLabel" class="hidden">Amount (5%) :</span>
                    </div>
                    <div class="col-span-3 p-2 font-bold text-right">
                        <div id="principalWrapper"><span id="modalDispPrincipal">0.00</span></div>
                        <div id="netWrapper" class="hidden"><span id="modalDispAmount">0.00</span></div>
                    </div>
                </div>

                <div class="grid grid-cols-12 border-b border-gray-300">
                    <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">Contact Number</div>
                    <div class="col-span-4 p-2 border-r border-gray-300 font-bold" id="modalDispContact">---</div>
                    <div class="col-span-3 bg-gray-50 p-2 font-bold border-r border-gray-300">Term :</div>
                    <div class="col-span-3 p-2 text-right font-bold"><span id="modalDispTerm">0</span> <span class="text-gray-400 font-normal">months</span></div>
                </div>

                <div class="grid grid-cols-12 border-b border-gray-300">
                    <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">Reference Number :</div>
                    <div class="col-span-4 p-2 border-r border-gray-300 font-bold" id="modalDispRef">---</div>
                    <div class="col-span-3 bg-gray-50 p-2 font-bold border-r border-gray-300">Interest Rate (AOR)</div>
                    <div class="col-span-3 p-2 text-right font-bold" id="modalDispRate">0%</div>
                </div>

                <div class="grid grid-cols-12 border-b border-gray-300">
                    <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">Date Granted :</div>
                    <div class="col-span-4 p-2 border-r border-gray-300 font-bold" id="modalDispDate">---</div>
                    <div class="col-span-3 bg-gray-50 p-2 font-bold border-r border-gray-300">Monthly Amortization</div>
                    <div class="col-span-3 p-2 font-bold text-right text-base text-black" id="modalDispMonthly">0.00</div>
                </div>

                <div class="grid grid-cols-12 border-b border-gray-300">
                    <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">Maturity Date :</div>
                    <div class="col-span-4 p-2 border-r border-gray-300 font-bold" id="modalDispMaturity">---</div>
                    <div class="col-span-6 bg-gray-50 p-2"></div>
                </div>
            </div>

            <div class="overflow-x-auto mb-6">
                <table class="w-full text-[12px] border-collapse text-black" id="primaryLedgerTable">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-300 font-bold uppercase">
                            <th class="p-2 border-r border-gray-300 w-8 text-center">#</th>
                            <th class="p-2 border-r border-gray-300 text-center">Date</th>
                            <th class="p-2 border-r border-gray-300 text-center">Principal</th>
                            <th class="p-2 border-r border-gray-300 text-center">Interest</th>
                            <th class="p-2 border-r border-gray-300 text-center">Total Amount</th>
                            <th class="p-2 border-r border-gray-300 text-center">Principal Balance</th>
                            <th class="p-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="primaryLedgerBody" class="divide-y divide-gray-300"></tbody>
                </table>
            </div>

            <div class="overflow-x-auto hidden" id="secondaryLedgerWrapper">
                <table class="w-full text-[12px] border-collapse text-black" id="secondaryLedgerTable">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-300 font-bold uppercase">
                            <th class="p-2 border-r border-gray-300 w-8 text-center">#</th>
                            <th class="p-2 border-r border-gray-300 text-center">Date</th>
                            <th class="p-2 border-r border-gray-300 text-center">Principal</th>
                            <th class="p-2 border-r border-gray-300 text-center">Interest</th>
                            <th class="p-2 border-r border-gray-300 text-center">Total Amount</th>
                            <th class="p-2 border-r border-gray-300 text-center">Principal Balance</th>
                            <th class="p-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="secondaryLedgerBody" class="divide-y divide-gray-300"></tbody>
                </table>
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
    if (menu) menu.classList.toggle('hidden');
}

window.addEventListener('click', function(event) {
    const dropdown = document.getElementById('amortizationDownloadDropdown');
    const menu = document.getElementById('amortizationDropdownMenu');
    if (dropdown && !dropdown.contains(event.target)) {
        if (menu) menu.classList.add('hidden');
    }
});

function closeAmortization() {
    const modal = document.getElementById('amortizationModal');
    if (modal) modal.classList.add('hidden');
}

/**
 * AUTOMATIC HEADER SWITCHER
 * This observes your secondaryLedgerWrapper. 
 * If your existing tab-switch script removes the 'hidden' class from it,
 * the header will automatically change to "SECONDARY LEDGER (GMS)".
 */
const observer = new MutationObserver(() => {
    const secondaryWrapper = document.getElementById('secondaryLedgerWrapper');
    const isSecondary = !secondaryWrapper.classList.contains('hidden');
    updateAmortizationHeader(isSecondary);
});

const targetNode = document.getElementById('secondaryLedgerWrapper');
if (targetNode) {
    observer.observe(targetNode, { attributes: true, attributeFilter: ['class'] });
}

function updateAmortizationHeader(isSecondary) {
    const header = document.getElementById('dynamicLedgerHeader');
    const pLabel = document.getElementById('principalLabel');
    const nLabel = document.getElementById('netLabel');
    const pWrap = document.getElementById('principalWrapper');
    const nWrap = document.getElementById('netWrapper');

    if (isSecondary) {
        header.innerText = "SECONDARY LEDGER (GMS)";
        if(pLabel) pLabel.classList.add('hidden');
        if(pWrap) pWrap.classList.add('hidden');
        if(nLabel) nLabel.classList.remove('hidden');
        if(nWrap) nWrap.classList.remove('hidden');
    } else {
        header.innerText = "PRIMARY LEDGER";
        if(pLabel) pLabel.classList.remove('hidden');
        if(pWrap) pWrap.classList.remove('hidden');
        if(nLabel) nLabel.classList.add('hidden');
        if(nWrap) nWrap.classList.add('hidden');
    }
}

/**
 * REVISED DATE FORMATTER
 * This logic captures both tab switches and initial data load.
 */
function forceDateFormat() {
    // 1. Fix Summary Headers (Date Granted / Maturity)
    ['modalDispDate', 'modalDispMaturity'].forEach(id => {
        const el = document.getElementById(id);
        if (el && el.innerText.includes('/')) {
            const currentText = el.innerText.trim();
            const formatted = reformatDateString(currentText);
            // Only update if it's actually different to prevent flickering
            if (currentText !== formatted) {
                el.innerText = formatted;
            }
        }
    });

    // 2. Fix Table Body Rows
    ['primaryLedgerBody', 'secondaryLedgerBody'].forEach(bodyId => {
        const body = document.getElementById(bodyId);
        if (body) {
            Array.from(body.rows).forEach(row => {
                const dateCell = row.cells[1];
                if (dateCell && dateCell.innerText.includes('/')) {
                    const currentVal = dateCell.innerText.trim();
                    const newVal = reformatDateString(currentVal);
                    if (currentVal !== newVal) {
                        dateCell.innerText = newVal;
                    }
                }
            });
        }
    });
}

function reformatDateString(str) {
    // Expected format: DD/MM/YYYY
    const parts = str.trim().split('/');
    if (parts.length === 3) {
        const d = parts[0];
        const m = parts[1];
        const y = parts[2];
        
        // Logical check: if first part > 12, it is definitely a Day.
        // We swap them to MM/DD/YYYY
        if (parseInt(d) > 12) {
            return `${m}/${d}/${y}`;
        }
        
        // If your system always outputs DD/MM/YYYY even for early days (e.g., 01/05), 
        // we assume the first is always Day.
        return `${m}/${d}/${y}`;
    }
    return str;
}

// Global Observer to handle tab switching and data injections
const globalObserver = new MutationObserver(() => {
    globalObserver.disconnect();
    forceDateFormat();
    startObserving();
});

function startObserving() {
    const config = { childList: true, subtree: true, characterData: true };
    
    // Watch the containers that get updated during tab switches
    const watchList = [
        'primaryLedgerBody', 
        'secondaryLedgerBody', 
        'modalDispDate', 
        'modalDispMaturity'
    ];

    watchList.forEach(id => {
        const target = document.getElementById(id);
        if (target) globalObserver.observe(target, config);
    });
}

// Initial Run
startObserving();
</script>