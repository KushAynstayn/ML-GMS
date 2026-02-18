<?php include('../includes/header.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<div class="flex overflow-hidden" style="height: calc(100vh - 64px);">
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto">
        <header class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Collection Report</h2>

            <div class="flex flex-nowrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                
                <div class="flex-1 min-w-[200px] relative">
                    <input type="text" id="searchInput" placeholder="Search accounts, references..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500/20">
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

                <div class="relative inline-block text-left shrink-0" id="downloadDropdown">
                    <button onclick="toggleDropdown()" class="flex items-center gap-2 bg-red-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-red-700 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download Report
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"></path></svg>
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
            <table id="collectionTable" class="w-full text-left border-collapse min-w-[1600px]">
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
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100">
                    <tr class="hover:bg-pink-50 transition-colors">
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">08/08/2025</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">31/07/2025</td>
                        <td class="px-4 py-4 text-sm text-gray-800">Leah Faye Genson</td>
                        <td class="px-4 py-4 text-sm text-gray-700 text-center">24,940.67</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">12,551.77</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">12,388.89</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">48 months</td>
                        <td class="px-4 py-4 text-sm font-mono text-gray-500 uppercase text-center">MCRVMWQTW</td>
                        <td class="px-4 py-4 text-[12px] text-gray-500 leading-tight text-center">Dec 2025 - Jan 2026</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">09/01/2026</td>
                        <td class="px-4 py-4 text-sm text-center"><span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold uppercase">Yes</span></td>
                    </tr>
                    <tr class="hover:bg-pink-50 transition-colors">
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">15/09/2025</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">30/08/2025</td>
                        <td class="px-4 py-4 text-sm text-gray-800">Juan Dela Cruz</td>
                        <td class="px-4 py-4 text-sm text-gray-700 text-center">15,200.00</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">8,500.00</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">6,700.00</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">24 months</td>
                        <td class="px-4 py-4 text-sm font-mono text-gray-500 uppercase text-center">XPQZRT123</td>
                        <td class="px-4 py-4 text-[12px] text-gray-500 leading-tight text-center">Jan 2026 - Feb 2026</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">15/01/2026</td>
                        <td class="px-4 py-4 text-sm text-center"><span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold uppercase">Yes</span></td>
                    </tr>
                    <tr class="hover:bg-pink-50 transition-colors">
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">20/10/2025</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">30/09/2025</td>
                        <td class="px-4 py-4 text-sm text-gray-800">Maria Clara Reyes</td>
                        <td class="px-4 py-4 text-sm text-gray-700 text-center">10,500.50</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">6,200.25</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">4,300.25</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">36 months</td>
                        <td class="px-4 py-4 text-sm font-mono text-gray-500 uppercase text-center">LKNMOP789</td>
                        <td class="px-4 py-4 text-[12px] text-gray-500 leading-tight text-center">Feb 2026 - Mar 2026</td>
                        <td class="px-4 py-4 text-sm text-gray-600 text-center">20/01/2026</td>
                        <td class="px-4 py-4 text-sm text-center"><span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold uppercase">No</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
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
    const rows = document.querySelectorAll('#tableBody tr');
    let hasMatch = false;

    rows.forEach(row => {
        const releaseDateStr = row.cells[0].textContent.trim(); // Now DD/MM/YYYY
        const nameText = row.cells[2].textContent.toUpperCase();
        const refText = row.cells[7].textContent.toUpperCase();
        
        // Correctly parse DD/MM/YYYY
        const [day, month, year] = releaseDateStr.split('/');
        const rowDate = new Date(year, month - 1, day);
        rowDate.setHours(0, 0, 0, 0);

        const filterStart = startDateVal ? new Date(startDateVal) : null;
        if(filterStart) filterStart.setHours(0,0,0,0);

        const filterEnd = endDateVal ? new Date(endDateVal) : null;
        if(filterEnd) filterEnd.setHours(0,0,0,0);

        let dateMatch = true;
        if (dateMode === 'single' && filterStart) {
            dateMatch = rowDate.getTime() === filterStart.getTime();
        } else if (dateMode === 'range' && filterStart && filterEnd) {
            dateMatch = rowDate >= filterStart && rowDate <= filterEnd;
        } else if (dateMode === 'range' && filterStart) {
            dateMatch = rowDate >= filterStart;
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

function toggleDropdown() {
    document.getElementById('dropdownMenu').classList.toggle('hidden');
}

window.onclick = function(event) {
    if (!event.target.closest('#downloadDropdown')) {
        document.getElementById('dropdownMenu').classList.add('hidden');
    }
}

function downloadExcel() {
    const table = document.getElementById('collectionTable');
    const worksheet = XLSX.utils.table_to_sheet(table);
    const colWidths = [
        { wch: 15 }, { wch: 18 }, { wch: 30 }, { wch: 18 }, { wch: 15 },
        { wch: 15 }, { wch: 12 }, { wch: 18 }, { wch: 20 }, { wch: 15 }, { wch: 15 }
    ];
    worksheet['!cols'] = colWidths;

    for (let cell in worksheet) {
        if (cell[0] === '!') continue; 
        const row = parseInt(cell.replace(/[^\d]/g, ''));
        if (row === 1) {
            worksheet[cell].s = {
                fill: { fgColor: { rgb: "C6E0B4" } }, 
                font: { bold: true, sz: 11 },
                alignment: { horizontal: "center", vertical: "center" },
                border: { top: { style: "thin" }, bottom: { style: "thin" }, left: { style: "thin" }, right: { style: "thin" } }
            };
        } else if (!cell.startsWith('C')) {
            worksheet[cell].s = { alignment: { horizontal: "center", vertical: "center" } };
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
        doc.addImage(img, 'PNG', 135, 10, 25, 10);
        doc.setFontSize(14);
        doc.setFont("helvetica", "bold");
        doc.text("LOANS DEPARTMENT", 148.5, 25, { align: "center" });
        doc.setFontSize(12);
        doc.text("COLLECTION REPORT", 148.5, 32, { align: "center" });
        doc.setFontSize(10);
        doc.setFont("helvetica", "normal");
        doc.text("As of January 31, 2026", 148.5, 38, { align: "center" });

        doc.autoTable({
            html: '#collectionTable',
            startY: 45,
            theme: 'grid',
            headStyles: { fillColor: [198, 224, 180], textColor: [0, 0, 0], halign: 'center', fontSize: 8 },
            bodyStyles: { fontSize: 8 },
            columnStyles: {
                2: { fontStyle: 'bold' },
                0: { halign: 'center' }, 1: { halign: 'center' }, 3: { halign: 'center' },
                4: { halign: 'center' }, 5: { halign: 'center' }, 6: { halign: 'center' },
                7: { halign: 'center' }, 8: { halign: 'center' }, 9: { halign: 'center' }, 10: { halign: 'center' }
            }
        });
        doc.save("Collection-Report.pdf");
    };
}
</script>