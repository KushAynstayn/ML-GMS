<?php include('../includes/header.php'); ?>

<style>
    .tab-btn:focus, .tab-btn:active { outline: none !important; box-shadow: none !important; }
    button:focus { outline: none !important; }
    /* Custom scrollbar for the wide table */
    .overflow-x-auto::-webkit-scrollbar { 
    height: 8px; 
    }
    .overflow-x-auto::-webkit-scrollbar-track { 
        background: #f3f4f6; /* This is the hex code for gray-100 */
    }
    .overflow-x-auto::-webkit-scrollbar-thumb { 
        background: #e5e7eb; 
        border-radius: 4px; 
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<div class="flex overflow-hidden" style="height: calc(100vh - 64px);">
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto animate-content">
        <header class="mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Collection <span class="text-red-600">Report</span></h2>
                    <p class="text-gray-500 font-medium mt-2 mb-6">Access detailed borrower insights and track all collection data.</p>
                </div>
            </div>

            <div class="flex gap-8 border-b border-gray-200 mb-6">
                <button onclick="switchTab('car', this)" id="tabCar" class="tab-btn pb-2 font-semibold text-red-600 border-b-2 border-red-600 transition-all">Car Loan</button>
                <button onclick="switchTab('motor', this)" id="tabMotor" class="tab-btn pb-2 font-semibold text-gray-500 hover:text-red-600 transition-all">Motor Loan</button>
            </div>

            <div class="flex flex-nowrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                
                <div class="flex-1 min-w-[200px] relative">
                    <input type="text" id="searchInput" placeholder="Search account or reference..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-0 text-gray-600 placeholder-gray-400">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <div id="motorFilterContainer" class="hidden shrink-0">
                    <select id="wheelFilter" onchange="filterTable()" class="px-4 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-gray-500 font-bold text-xs uppercase cursor-pointer hover:border-gray-300">
                        <option value="all">All Wheels</option>
                        <option value="2-wheels">2-Wheels</option>
                        <option value="3-wheels">3-Wheels</option>
                    </select>
                </div>

                <div class="flex shrink-0 bg-gray-100 p-1 rounded-lg border border-gray-200">
                    <button onclick="setDateMode('single')" id="btnSingle" class="px-3 py-1.5 text-[10px] font-bold uppercase rounded-md transition-all bg-white text-red-600 shadow-sm">Single Date</button>
                    <button onclick="setDateMode('range')" id="btnRange" class="px-3 py-1.5 text-[10px] font-bold uppercase rounded-md transition-all text-gray-500 hover:text-gray-700">Select Range</button>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500 hover:border-gray-300 transition-colors bg-white">
                        <span id="dateLabel" class="text-[10px] font-bold uppercase">Date</span>
                        <input type="date" id="startDate" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer uppercase text-xs">
                    </div>
                    
                    <div id="toDateContainer" class="hidden flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500 hover:border-gray-300 transition-colors bg-white">
                        <span class="text-[10px] font-bold uppercase">To</span>
                        <input type="date" id="endDate" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer uppercase text-xs">
                    </div>
                </div>

                <div class="relative inline-block text-left shrink-0" id="downloadDropdown">
                    <button onclick="toggleDropdown()" class="flex items-center gap-2 bg-red-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-red-700 transition-all shadow-sm text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download Report
                    </button>
                    <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden">
                        <button onclick="downloadExcel()" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                            <span class="text-green-600 font-bold">EXCEL</span> 
                        </button>
                        <button onclick="downloadPDF()" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 border-t border-gray-100">
                            <span class="text-red-600 font-bold">PDF</span> 
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div id="noRecordFound" class="hidden mb-4 p-4 bg-red-50 border border-red-100 rounded-lg flex items-center gap-3 text-red-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium">No records found matching your selection.</span>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm overflow-x-auto border border-gray-100">
            <table id="collectionTable" class="w-full text-left border-collapse min-w-[1800px]">
                <thead class="bg-red-600">
                    <tr>
                        <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Release Date</th>
                        <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Installment Date</th>
                        <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white">Account Name</th>
                        <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Monthly Amort.</th>
                        <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Principal</th>
                        <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Interest</th>
                        <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Term</th>
                        <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Reference #</th>
                        <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Amort. Period</th>
                        <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Date Paid</th>
                        <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Device Installed</th>
                        <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100">
                    <tr class="hover:bg-pink-50 transition-colors" data-loan-type="car">
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">08/08/2025</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">31/07/2025</td>
                        <td class="px-4 py-4 text-sm text-gray-800 font-medium">Leah Faye Genson</td>
                        <td class="px-4 py-4 text-sm text-gray-700 text-center">24,940.67</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">12,551.77</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">12,388.89</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">48 months</td>
                        <td class="px-4 py-4 text-sm font-mono text-gray-500 uppercase text-center">MCRVMWQTW</td>
                        <td class="px-4 py-4 text-[12px] text-gray-500 leading-tight text-center">Dec 2025</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">09/01/2026</td>
                        <td class="px-4 py-4 text-sm text-center"><span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-[10px] font-bold uppercase">Yes</span></td>
                        <td class="px-4 py-4 text-sm text-center">
                            <span class="px-3 py-1 bg-green-500 text-white rounded-full text-[10px] font-bold uppercase shadow-sm">Fully Paid</span>
                        </td>
                    </tr>
                    <tr class="hover:bg-pink-50 transition-colors" data-loan-type="motor" data-wheels="2-wheels">
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">10/01/2026</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">01/01/2026</td>
                        <td class="px-4 py-4 text-sm text-gray-800 font-medium">Motorbike User (2W)</td>
                        <td class="px-4 py-4 text-sm text-gray-700 text-center">3,200.00</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">2,000.00</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">1,200.00</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">12 months</td>
                        <td class="px-4 py-4 text-sm font-mono text-gray-500 uppercase text-center">MOT999XXX</td>
                        <td class="px-4 py-4 text-[12px] text-gray-500 leading-tight text-center">Jan 2026</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">---</td>
                        <td class="px-4 py-4 text-sm text-center"><span class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-[10px] font-bold uppercase">No</span></td>
                        <td class="px-4 py-4 text-sm text-center">
                            <span class="px-3 py-1 bg-orange-500 text-white rounded-full text-[10px] font-bold uppercase shadow-sm">Repossessed</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
let dateMode = 'single';
let currentTab = 'car';

function switchTab(tabName, element) {
    currentTab = tabName;
    const motorFilter = document.getElementById('motorFilterContainer');

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('text-red-600', 'border-b-2', 'border-red-600');
        btn.classList.add('text-gray-500');
    });
    
    element.classList.add('text-red-600', 'border-b-2', 'border-red-600');
    element.classList.remove('text-gray-500');

    if (tabName === 'motor') {
        motorFilter.classList.remove('hidden');
    } else {
        motorFilter.classList.add('hidden');
    }

    filterTable();
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
        btnSingle.classList.add('bg-white', 'text-red-600', 'shadow-sm');
        btnRange.classList.remove('bg-white', 'text-red-600', 'shadow-sm');
        document.getElementById('endDate').value = ""; 
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
    const startDateVal = document.getElementById('startDate').value;
    const endDateVal = document.getElementById('endDate').value;
    const wheelVal = document.getElementById('wheelFilter').value;
    const rows = document.querySelectorAll('#tableBody tr');
    let hasMatch = false;

    rows.forEach(row => {
        const releaseDateStr = row.cells[0].textContent.trim();
        const nameText = row.cells[2].textContent.toUpperCase();
        const refText = row.cells[7].textContent.toUpperCase();
        const rowLoanType = row.getAttribute('data-loan-type');
        const rowWheels = row.getAttribute('data-wheels');

        const tabMatch = (rowLoanType === currentTab);
        let wheelMatch = (currentTab !== 'motor' || wheelVal === 'all' || rowWheels === wheelVal);

        const [day, month, year] = releaseDateStr.split('/');
        const rowDate = new Date(year, month - 1, day);
        rowDate.setHours(0, 0, 0, 0);

        const filterStart = startDateVal ? new Date(startDateVal) : null;
        const filterEnd = endDateVal ? new Date(endDateVal) : null;

        let dateMatch = true;
        if (dateMode === 'single' && filterStart) {
            dateMatch = rowDate.getTime() === new Date(startDateVal).setHours(0,0,0,0);
        } else if (dateMode === 'range' && filterStart && filterEnd) {
            dateMatch = rowDate >= new Date(startDateVal).setHours(0,0,0,0) && rowDate <= new Date(endDateVal).setHours(0,0,0,0);
        }

        const textMatch = nameText.includes(searchText) || refText.includes(searchText);

        if (tabMatch && wheelMatch && dateMatch && textMatch) {
            row.style.display = "";
            hasMatch = true;
        } else {
            row.style.display = "none";
        }
    });

    document.getElementById('noRecordFound').classList.toggle('hidden', hasMatch);
}

document.getElementById('searchInput').addEventListener('keyup', filterTable);

function toggleDropdown() {
    document.getElementById('dropdownMenu').classList.toggle('hidden');
}

window.onclick = function(event) {
    if (!event.target.closest('#downloadDropdown')) {
        document.getElementById('dropdownMenu').classList.add('hidden');
    }
}

window.onload = function() {
    filterTable();
};

// --- Updated Download Functions ---

function downloadExcel() {
    const table = document.getElementById('collectionTable');
    const worksheet = XLSX.utils.table_to_sheet(table);
    // Adjusted for 12 columns
    const colWidths = [
        { wch: 14 }, { wch: 16 }, { wch: 28 }, { wch: 15 }, { wch: 12 },
        { wch: 12 }, { wch: 10 }, { wch: 18 }, { wch: 18 }, { wch: 14 }, 
        { wch: 14 }, { wch: 16 }
    ];
    worksheet['!cols'] = colWidths;

    for (let cell in worksheet) {
        if (cell[0] === '!') continue; 
        const row = parseInt(cell.replace(/[^\d]/g, ''));
        if (row === 1) {
            worksheet[cell].s = {
                fill: { fgColor: { rgb: "DC2626" } }, 
                font: { bold: true, sz: 10, color: { rgb: "FFFFFF" } },
                alignment: { horizontal: "center", vertical: "center" }
            };
        }
    }
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Collections");
    XLSX.writeFile(workbook, "Collection-Report.xlsx");
}

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');
    const img = new Image();
    img.src = '../assets/images/ml.png';
    
    img.onload = function() {
        doc.addImage(img, 'PNG', 131, 10, 35, 7); 
        doc.setFontSize(14);
        doc.setFont("helvetica", "bold");
        doc.text("LOANS DEPARTMENT", 148.5, 25, { align: "center" });
        doc.text("COLLECTION REPORT", 148.5, 32, { align: "center" });
        
        doc.autoTable({
            html: '#collectionTable',
            startY: 40,
            theme: 'grid',
            styles: { fontSize: 7, halign: 'center' },
            headStyles: { fillColor: [220, 38, 38], textColor: [255, 255, 255] },
            columnStyles: {
                2: { halign: 'left', fontStyle: 'bold' }, // Name
                11: { fontStyle: 'bold' } // Status
            }
        });
        doc.save("Collection-Report.pdf");
    };
}
</script>