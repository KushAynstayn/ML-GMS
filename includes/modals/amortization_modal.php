<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
<div id="amortizationModal" class="hidden fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center py-6 px-4">
    <div class="bg-white w-full max-w-5xl rounded-xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200 flex flex-col">
        
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col gap-2">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-2">
                    <button onclick="closeAmortization()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </button>
                    <h3 class="text-xl font-bold text-gray-700">Monthly Amortization</h3>
                </div>
                
                <div class="relative inline-block text-left shrink-0" id="amortizationDownloadDropdown">
                    <button onclick="toggleAmortizationDropdown()" class="flex items-center gap-2 bg-red-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-red-700 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
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

            <div class="flex justify-center">
                <img src="../assets/images/ml.png" alt="M Lhuillier" class="h-8">
            </div>
        </div>

        <div class="p-6 overflow-y-auto max-h-[70vh]">
    <div class="border border-gray-300 rounded-sm overflow-hidden text-black">
        
        <div class="text-[13px]">
            <div class="grid grid-cols-12 border-b border-gray-300">
                <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">Account Name :</div>
                <div class="col-span-10 p-2 font-bold" id="modalDispName">Leah Faye Genson</div>
            </div>

            <div class="grid grid-cols-12 border-b border-gray-300">
                <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">Contact Number</div>
                <div class="col-span-10 p-2 font-bold">9514791978</div>
            </div>

            <div class="grid grid-cols-12 border-b border-gray-300">
                <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">PN Number :</div>
                <div class="col-span-4 p-2 border-r border-gray-300 font-mono" id="modalDispRef">MCRVMWQTW</div>
                <div class="col-span-3 bg-gray-50 p-2 font-bold border-r border-gray-300">Loan Amount :</div>
                <div class="col-span-3 p-2 font-bold text-right">224,276.00</div>
            </div>

            <div class="grid grid-cols-12 border-b border-gray-300">
                <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">PN Date :</div>
                <div class="col-span-4 p-2 border-r border-gray-300">February 3, 2024</div>
                <div class="col-span-3 bg-gray-50 p-2 font-bold border-r border-gray-300">Term :</div>
                <div class="col-span-3 p-2 text-right font-bold">10 <span class="text-gray-400 font-normal">months</span></div>
            </div>

            <div class="grid grid-cols-12 border-b border-gray-300">
                <div class="col-span-2 bg-gray-50 p-2 font-bold border-r border-gray-300">PN Maturity :</div>
                <div class="col-span-4 p-2 border-r border-gray-300">December 3, 2024</div>
                <div class="col-span-3 bg-gray-50 p-2 font-bold border-r border-gray-300">Interest Rate (AOR)</div>
                <div class="col-span-3 p-2 text-right font-bold">96 %</div>
            </div>

            <div class="grid grid-cols-12 bg-gray-50 items-center border-b border-gray-300">
                <div class="col-span-6 border-r border-gray-300 h-full"></div> 
                <div class="col-span-3 p-2 font-bold border-r border-gray-300 tracking-wider">Monthly Amortization</div>
                <div class="col-span-3 p-2 font-bold text-right text-base text-black">33,189.65</div>
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
                <tbody class="divide-y divide-gray-300">
                    <tr>
                        <td colspan="5" class="border-r border-gray-300 bg-white"></td>
                        <td class="p-2 border-r border-gray-300 text-right font-bold bg-gray-100">224,276.00</td>
                        <td class="bg-white"></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border-r border-gray-300 text-center font-bold">1</td>
                        <td class="p-2 border-r border-gray-300 text-center">03/03/2024</td>
                        <td class="p-2 border-r border-gray-300 text-right">15,247.57</td>
                        <td class="p-2 border-r border-gray-300 text-right">17,942.08</td>
                        <td class="p-2 border-r border-gray-300 text-right font-medium">33,189.65</td>
                        <td class="p-2 border-r border-gray-300 text-right">209,028.43</td>
                        <td class="p-2 text-center font-bold text-black">PAID</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border-r border-gray-300 text-center font-bold">2</td>
                        <td class="p-2 border-r border-gray-300 text-center">04/03/2024</td>
                        <td class="p-2 border-r border-gray-300 text-right">16,467.38</td>
                        <td class="p-2 border-r border-gray-300 text-right">16,722.27</td>
                        <td class="p-2 border-r border-gray-300 text-right font-medium">33,189.65</td>
                        <td class="p-2 border-r border-gray-300 text-right">192,561.05</td>
                        <td class="p-2 text-center font-bold text-black">PAID</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border-r border-gray-300 text-center font-bold">3</td>
                        <td class="p-2 border-r border-gray-300 text-center">05/03/2024</td>
                        <td class="p-2 border-r border-gray-300 text-right">17,784.77</td>
                        <td class="p-2 border-r border-gray-300 text-right">15,404.88</td>
                        <td class="p-2 border-r border-gray-300 text-right font-medium">33,189.65</td>
                        <td class="p-2 border-r border-gray-300 text-right">174,776.28</td>
                        <td class="p-2 text-center font-bold text-black">UNPAID</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border-r border-gray-300 text-center font-bold">4</td>
                        <td class="p-2 border-r border-gray-300 text-center">06/03/2024</td>
                        <td class="p-2 border-r border-gray-300 text-right">19,207.55</td>
                        <td class="p-2 border-r border-gray-300 text-right">13,982.10</td>
                        <td class="p-2 border-r border-gray-300 text-right font-medium">33,189.65</td>
                        <td class="p-2 border-r border-gray-300 text-right">155,568.73</td>
                        <td class="p-2 text-center font-bold text-black">UNPAID</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border-r border-gray-300 text-center font-bold">5</td>
                        <td class="p-2 border-r border-gray-300 text-center">07/03/2024</td>
                        <td class="p-2 border-r border-gray-300 text-right">20,744.15</td>
                        <td class="p-2 border-r border-gray-300 text-right">12,445.50</td>
                        <td class="p-2 border-r border-gray-300 text-right font-medium">33,189.65</td>
                        <td class="p-2 border-r border-gray-300 text-right">134,824.58</td>
                        <td class="p-2 text-center font-bold text-black">UNPAID</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border-r border-gray-300 text-center font-bold">6</td>
                        <td class="p-2 border-r border-gray-300 text-center">08/03/2024</td>
                        <td class="p-2 border-r border-gray-300 text-right">22,403.69</td>
                        <td class="p-2 border-r border-gray-300 text-right">10,785.96</td>
                        <td class="p-2 border-r border-gray-300 text-right font-medium">33,189.65</td>
                        <td class="p-2 border-r border-gray-300 text-right">112,420.89</td>
                        <td class="p-2 text-center font-bold text-black">UNPAID</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border-r border-gray-300 text-center font-bold">7</td>
                        <td class="p-2 border-r border-gray-300 text-center">09/03/2024</td>
                        <td class="p-2 border-r border-gray-300 text-right">24,195.98</td>
                        <td class="p-2 border-r border-gray-300 text-right">8,993.67</td>
                        <td class="p-2 border-r border-gray-300 text-right font-medium">33,189.65</td>
                        <td class="p-2 border-r border-gray-300 text-right">88,224.91</td>
                        <td class="p-2 text-center font-bold text-black">UNPAID</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border-r border-gray-300 text-center font-bold">8</td>
                        <td class="p-2 border-r border-gray-300 text-center">10/03/2024</td>
                        <td class="p-2 border-r border-gray-300 text-right">26,131.66</td>
                        <td class="p-2 border-r border-gray-300 text-right">7,057.99</td>
                        <td class="p-2 border-r border-gray-300 text-right font-medium">33,189.65</td>
                        <td class="p-2 border-r border-gray-300 text-right">62,093.25</td>
                        <td class="p-2 text-center font-bold text-black">UNPAID</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border-r border-gray-300 text-center font-bold">9</td>
                        <td class="p-2 border-r border-gray-300 text-center">11/03/2024</td>
                        <td class="p-2 border-r border-gray-300 text-right">28,222.19</td>
                        <td class="p-2 border-r border-gray-300 text-right">4,967.46</td>
                        <td class="p-2 border-r border-gray-300 text-right font-medium">33,189.65</td>
                        <td class="p-2 border-r border-gray-300 text-right">33,871.06</td>
                        <td class="p-2 text-center font-bold text-black">UNPAID</td>
                    </tr>
                    <tr class="hover:bg-gray-50 bg-red-50">
                        <td class="p-2 border-r border-gray-300 text-center font-bold">10</td>
                        <td class="p-2 border-r border-gray-300 text-center">12/03/2024</td>
                        <td class="p-2 border-r border-gray-300 text-right">30,479.97</td>
                        <td class="p-2 border-r border-gray-300 text-right">2,709.68</td>
                        <td class="p-2 border-r border-gray-300 text-right font-medium">33,189.65</td>
                        <td class="p-2 border-r border-gray-300 text-right font-bold">0.00</td>
                        <td class="p-2 text-center font-bold text-black">UNPAID</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-2 gap-x-12 text-[13px] text-black">
        <div>
            <p class="mb-4">Prepared by:</p>
            <div class="border-b border-black w-48 mb-1 pb-1">
                <input type="text" class="font-bold bg-transparent border-none outline-none w-full p-0" value="Reynaldo Lapiña Jr.">
            </div>
            <p class="text-xs uppercase">LOANS EVALUATOR</p>
        </div>
        <div>
            <p class="mb-4">Checked by:</p>
            <div class="border-b border-black w-48 mb-1 pb-1">
                <input type="text" class="font-bold bg-transparent border-none outline-none w-full p-0" value="Jacob N. Lomotos">
            </div>
            <p class="text-xs">ML Lending Head</p>
        </div>
        <div class="mt-8">
            <p class="mb-4">Conforme:</p>
            <div class="border-b border-black w-48 mb-1 pb-1">
                <input type="text" class="font-bold bg-transparent border-none outline-none w-full p-0" placeholder="">
            </div>
            <p class="text-xs">Debtor/Creditor</p>
        </div>
        <div class="mt-8 self-end">
            <div class="border-b border-black w-48 mb-1 pb-1">
                <input type="text" class="font-bold bg-transparent border-none outline-none w-full p-0" placeholder="">
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
    if (typeof XLSX === 'undefined') {
        alert("Excel library (xlsx-js-style) is required for styling. Please check your script tag.");
        return;
    }

    if (format === 'excel') {
        const wb = XLSX.utils.book_new();
        const accountName = document.getElementById('modalDispName').innerText;
        const pnNumber = document.getElementById('modalDispRef').innerText;
        const table = document.getElementById('amortizationTable');
        const tbodyRows = table.querySelectorAll('tbody tr');

        // --- STYLE DEFINITIONS ---
        const boldStyle = { font: { bold: true } };
        
        const borderStyle = {
            border: {
                top: { style: "thin" }, bottom: { style: "thin" },
                left: { style: "thin" }, right: { style: "thin" }
            }
        };

        const headerStyle = { 
            font: { bold: true }, 
            alignment: { horizontal: "center", vertical: "center" },
            ...borderStyle
        };

        const labelStyle = { font: { bold: true }, ...borderStyle };

        // --- ROW CONSTRUCTION ---
        const rows = [
            // Top Info Table
            [{ v: "Account Name :", s: labelStyle }, { v: accountName, s: borderStyle }, { v: "", s: borderStyle }, { v: "", s: borderStyle }, { v: "", s: borderStyle }, { v: "", s: borderStyle }],
            [{ v: "Contact Number", s: labelStyle }, { v: "9514791978", s: borderStyle }, { v: "", s: borderStyle }, { v: "", s: borderStyle }, { v: "", s: borderStyle }, { v: "", s: borderStyle }],
            [{ v: "PN Number :", s: labelStyle }, { v: pnNumber, s: borderStyle }, { v: "", s: borderStyle }, { v: "Loan Amount :", s: labelStyle }, { v: 224276.00, s: { font: { bold: true }, ...borderStyle }, t: 'n', z: '#,##0.00' }, { v: "", s: borderStyle }],
            [{ v: "PN Date :", s: labelStyle }, { v: "February 3, 2024", s: { alignment: { horizontal: "center" }, ...borderStyle } }, { v: "", s: borderStyle }, { v: "Term :", s: labelStyle }, { v: 10, s: { alignment: { horizontal: "right" }, font: { bold: true }, ...borderStyle } }, { v: "months", s: borderStyle }],
            [{ v: "PN Maturity :", s: labelStyle }, { v: "December 3, 2024", s: { alignment: { horizontal: "center" }, ...borderStyle } }, { v: "", s: borderStyle }, { v: "Interest Rate (AOR)", s: labelStyle }, { v: "96 %", s: { alignment: { horizontal: "right" }, font: { bold: true }, ...borderStyle } }, { v: "", s: borderStyle }],
            [{ v: "", s: borderStyle }, { v: "", s: borderStyle }, { v: "", s: borderStyle }, { v: "Monthly Amortization", s: labelStyle }, { v: "", s: borderStyle }, { v: 33189.65, s: { font: { bold: true }, alignment: { horizontal: "right" }, ...borderStyle }, t: 'n', z: '#,##0.00' }],
            
            // Spacer row keeping the vertical line connected
            [{v: "", s: {border: {left: {style: "thin"}}}}, "", "", "", "", {v: "", s: {border: {right: {style: "thin"}}}}],

            // APPLICATION Title Row
            [{ v: "APPLICATION", s: headerStyle }, {v: "", s: borderStyle}, {v: "", s: borderStyle}, {v: "", s: borderStyle}, {v: "", s: borderStyle}, {v: "", s: borderStyle}],
            
            // Column Headers
            [
                { v: "#", s: headerStyle }, 
                { v: "DATE", s: headerStyle }, 
                { v: "PRINCIPAL", s: headerStyle }, 
                { v: "INTEREST", s: headerStyle }, 
                { v: "TOTAL AMOUNT", s: headerStyle }, 
                { v: "PRINCIPAL BALANCE", s: headerStyle }
            ],
            
            // Initial Balance Row
            [
                { v: "", s: borderStyle }, 
                { v: "", s: borderStyle }, 
                { v: "", s: borderStyle }, 
                { v: "", s: borderStyle }, 
                { v: "", s: borderStyle }, 
                { v: 224276.00, s: { font: { bold: true }, ...borderStyle }, t: 'n', z: '#,##0.00' }
            ]
        ];

        // --- ADD TABLE DATA ---
        tbodyRows.forEach((tr) => {
            const cols = tr.querySelectorAll('td');
            if(cols.length > 5) {
                const principal = parseFloat(cols[2].innerText.replace(/,/g, '')) || 0;
                const interest = parseFloat(cols[3].innerText.replace(/,/g, '')) || 0;
                const total = parseFloat(cols[4].innerText.replace(/,/g, '')) || 0;
                const balance = parseFloat(cols[5].innerText.replace(/,/g, '')) || 0;

                rows.push([
                    { v: cols[0].innerText, s: borderStyle }, 
                    { v: cols[1].innerText, s: borderStyle }, 
                    { v: principal, s: borderStyle, t: 'n', z: '#,##0.00' },
                    { v: interest, s: borderStyle, t: 'n', z: '#,##0.00' }, 
                    { v: total, s: borderStyle, t: 'n', z: '#,##0.00' }, 
                    { v: balance, s: borderStyle, t: 'n', z: '#,##0.00' }
                ]);
            }
        });

        // --- FOOTER / SIGNATURES (FIXED: Uses Input values) ---
        const signatureInputs = document.querySelectorAll('#amortizationModal input[type="text"]');
        const preparedBy = signatureInputs[0]?.value || "";
        const checkedBy = signatureInputs[1]?.value || "";
        const debtor1 = signatureInputs[2]?.value || "";
        const debtor2 = signatureInputs[3]?.value || "";

        rows.push([], []);
        rows.push([{ v: "Prepared by:", s: boldStyle }, "", "", { v: "Checked by:", s: boldStyle }]);
        rows.push([], []); 
        rows.push([{ v: preparedBy, s: { font: { bold: true } } }, "", "", { v: checkedBy, s: { font: { bold: true } } }]);
        rows.push([{ v: "LOANS EVALUATOR", s: { font: { size: 9 } } }, "", "", { v: "ML Lending Head", s: { font: { size: 9 } } }]);
        rows.push([], []); 
        rows.push([{ v: "Conforme:", s: boldStyle }]);
        
        // Displays input text or stays blank if input is empty
        rows.push([
            { v: debtor1.toUpperCase(), s: { font: { bold: true, underline: true } } }, 
            "", "", 
            { v: debtor2.toUpperCase(), s: { font: { bold: true, underline: true } } }
        ]);
        rows.push([{ v: "Debtor/Creditor", s: { font: { size: 9 } } }, "", "", { v: "Debtor/Creditor", s: { font: { size: 9 } } }]);

        const ws = XLSX.utils.aoa_to_sheet(rows);

        // --- MERGES ---
        ws['!merges'] = [
            { s: { r: 0, c: 1 }, e: { r: 0, c: 5 } }, 
            { s: { r: 1, c: 1 }, e: { r: 1, c: 5 } }, 
            { s: { r: 7, c: 0 }, e: { r: 7, c: 5 } }, // APPLICATION title span
            { s: { r: 5, c: 3 }, e: { r: 5, c: 4 } }
        ];
        
        ws['!cols'] = [
            { wch: 10 }, { wch: 18 }, { wch: 15 }, { wch: 15 }, { wch: 15 }, { wch: 22 }
        ];

        XLSX.utils.book_append_sheet(wb, ws, "Amortization");
        XLSX.writeFile(wb, `Amortization_${pnNumber}.xlsx`);
    }
    document.getElementById('amortizationDropdownMenu').classList.add('hidden');
}
</script>