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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<div class="flex h-screen overflow-hidden">
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto animate-content h-full">
        <header class="mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Collection <span class="text-[#D50000]">Report</span></h2>
                    <p class="text-gray-500 font-medium mt-2 mb-6">Access detailed borrower insights and track all collection data.</p>
                </div>
            </div>

            <div class="flex gap-8 border-b border-gray-200 mb-6">
                <button onclick="switchTab('car', this)" id="tabCar" class="tab-btn pb-2 font-semibold text-[#D50000] border-b-2 border-[#D50000] transition-all">Car Loan</button>
                <button onclick="switchTab('motor', this)" id="tabMotor" class="tab-btn pb-2 font-semibold text-gray-500 hover:text-[#D50000] transition-all">Motor Loan</button>
            </div>

            <div class="flex flex-nowrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                
                <div class="flex-1 min-w-[200px] relative">
                    <input type="text" id="searchInput" placeholder="Search account or reference..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D50000]/20 text-gray-600 placeholder-gray-400">
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
                    <button onclick="setDateMode('single')" id="btnSingle" class="px-3 py-1.5 text-[10px] font-bold uppercase rounded-md transition-all bg-white text-[#D50000] shadow-sm">Single Date</button>
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
                    <button onclick="toggleDropdown()" class="flex items-center gap-2 bg-[#D50000] text-white px-5 py-2 rounded-lg font-medium hover:bg-[#B70000] transition-all shadow-sm text-sm">
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
                            <span class="text-[#D50000] font-bold">PDF</span> 
                        </button>
                    </div>
                </div>
            </div>
        </header>

<div class="bg-white rounded-xl shadow-sm overflow-x-auto border border-gray-100">
    <table id="collectionTable" class="w-full text-left border-collapse min-w-[2100px]">
        <thead class="bg-[#D50000]">
            <tr>
                <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Released Date</th>
                <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Installation Date</th>
                <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white">Account Name</th>
                <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Monthly Amortization</th>
                <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Principal</th>
                <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Interest</th>
                <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Loan Term</th>
                <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Loan Reference Number</th>
                <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Paid Amortization</th>
                <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Date Paid</th>
                <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wider text-white text-center">Status</th>
            </tr>
        </thead>
        <tbody id="tableBody" class="divide-y divide-gray-100">
            <tr id="noRecordFound" class="hidden">
                <td colspan="11" class="px-4 py-12 text-center text-sm text-gray-400 italic">No records found.</td>
            </tr>
            <tr class="hover:bg-red-50/50 transition-colors" data-loan-type="car">
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
                <td class="px-4 py-4 text-sm text-center">
                    <span class="px-3 py-1 bg-green-500 text-white rounded-full text-[10px] font-bold uppercase shadow-sm">Fully Paid</span>
                </td>
            </tr>
            <tr class="hover:bg-red-50/50 transition-colors" data-loan-type="motor" data-wheels="2-wheels" style="display:none">
                <td class="px-4 py-4 text-sm text-gray-600 text-center">10/01/2026</td>
                <td class="px-4 py-4 text-sm text-gray-600 text-center">01/01/2026</td>
                <td class="px-4 py-4 text-sm text-gray-800 font-medium">John Doe</td>
                <td class="px-4 py-4 text-sm text-gray-700 text-center">3,200.00</td>
                <td class="px-4 py-4 text-sm text-gray-600 text-center">2,000.00</td>
                <td class="px-4 py-4 text-sm text-gray-600 text-center">1,200.00</td>
                <td class="px-4 py-4 text-sm text-gray-600 text-center">12 months</td>
                <td class="px-4 py-4 text-sm font-mono text-gray-500 uppercase text-center">MTR2W001</td>
                <td class="px-4 py-4 text-[12px] text-gray-500 leading-tight text-center">Jan 2026</td>
                <td class="px-4 py-4 text-sm text-gray-600 text-center">---</td>
                <td class="px-4 py-4 text-sm text-center">
                    <span class="px-3 py-1 bg-orange-500 text-white rounded-full text-[10px] font-bold uppercase shadow-sm">Repossessed</span>
                </td>
            </tr>
            <tr class="hover:bg-red-50/50 transition-colors" data-loan-type="motor" data-wheels="3-wheels" style="display:none">
                <td class="px-4 py-4 text-sm text-gray-600 text-center">12/01/2026</td>
                <td class="px-4 py-4 text-sm text-gray-600 text-center">05/01/2026</td>
                <td class="px-4 py-4 text-sm text-gray-800 font-medium">Jane Smith</td>
                <td class="px-4 py-4 text-sm text-gray-700 text-center">4,500.00</td>
                <td class="px-4 py-4 text-sm text-gray-600 text-center">3,000.00</td>
                <td class="px-4 py-4 text-sm text-gray-600 text-center">1,500.00</td>
                <td class="px-4 py-4 text-sm text-gray-600 text-center">24 months</td>
                <td class="px-4 py-4 text-sm font-mono text-gray-500 uppercase text-center">MTR3W001</td>
                <td class="px-4 py-4 text-[12px] text-gray-500 leading-tight text-center">Jan 2026</td>
                <td class="px-4 py-4 text-sm text-gray-600 text-center">06/01/2026</td>
                <td class="px-4 py-4 text-sm text-center">
                    <span class="px-3 py-1 bg-[#D50000] text-white rounded-full text-[10px] font-bold uppercase shadow-sm">No Payment</span>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
let dateMode = 'single';
let currentTab = 'car';

function switchTab(tabName, element) {
    currentTab = tabName;
    const motorFilter = document.getElementById('motorFilterContainer');

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('text-[#D50000]', 'border-b-2', 'border-[#D50000]');
        btn.classList.add('text-gray-500');
    });
    
    element.classList.add('text-[#D50000]', 'border-b-2', 'border-[#D50000]');
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
        btnSingle.classList.add('bg-white', 'text-[#D50000]', 'shadow-sm');
        btnRange.classList.remove('bg-white', 'text-[#D50000]', 'shadow-sm');
        document.getElementById('endDate').value = ""; 
    } else {
        toContainer.classList.remove('hidden');
        dateLabel.innerText = 'From';
        btnRange.classList.add('bg-white', 'text-[#D50000]', 'shadow-sm');
        btnSingle.classList.remove('bg-white', 'text-[#D50000]', 'shadow-sm');
    }
    filterTable(); 
}

function filterTable() {
    const searchText = document.getElementById('searchInput').value.toUpperCase();
    const startDateVal = document.getElementById('startDate').value;
    const endDateVal = document.getElementById('endDate').value;
    const wheelVal = document.getElementById('wheelFilter').value;
    const rows = document.querySelectorAll('#tableBody tr:not(#noRecordFound)');
    let hasMatch = false;

    rows.forEach(row => {
        const releaseDateStr = row.cells[0].textContent.trim();
        const nameText = row.cells[2].textContent.toUpperCase();
        const refText = row.cells[7].textContent.toUpperCase(); // Index adjusted from 8 to 7 due to Unit removal
        const rowLoanType = row.getAttribute('data-loan-type');
        const rowWheels = row.getAttribute('data-wheels');

        const tabMatch = (rowLoanType === currentTab);
        let wheelMatch = (currentTab !== 'motor' || wheelVal === 'all' || rowWheels === wheelVal);

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

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    
    const doc = new jsPDF('p', 'mm', 'letter');
    
    const img = new Image();
    img.src = '../assets/images/ml.png';

    const now = new Date();
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    const dateStr = `As of ${lastDay.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;

    // Determine Display Title
    let displayTab = currentTab.toUpperCase();
    if (displayTab === "CAR") {
        displayTab = "CAR LOAN";
    } else if (displayTab === "MOTOR") {
        const wheelFilter = document.getElementById('wheelFilter').value;
        if (wheelFilter === '2-wheels') {
            displayTab = "MOTOR LOAN (2-WHEELS)";
        } else if (wheelFilter === '3-wheels') {
            displayTab = "MOTOR LOAN (3-WHEELS)";
        } else {
            displayTab = "MOTOR LOAN";
        }
    }

    img.onload = function() {
        const pageWidth = doc.internal.pageSize.getWidth();
        const centerX = pageWidth / 2;

        doc.addImage(img, 'PNG', centerX - 17.5, 10, 35, 7);
        
        doc.setFontSize(10);
        doc.setFont("helvetica", "bold");
        doc.setTextColor(100, 100, 100);
        doc.text("LOANS DEPARTMENT", centerX, 22, { align: "center" });
        doc.text("COLLECTION REPORT", centerX, 27, { align: "center" });
        
        doc.setFont("helvetica", "normal");
        doc.text(dateStr, centerX, 32, { align: "center" });
        
        doc.setFontSize(10);
        doc.text(displayTab, 14, 40);

        doc.autoTable({
            html: '#collectionTable',
            startY: 45,
            theme: 'grid',
            styles: { 
                fontSize: 6, 
                halign: 'center', 
                textColor: [0, 0, 0], 
                lineColor: [200, 200, 200] 
            },
            headStyles: { 
                fillColor: [213, 0, 0], // #D50000
                textColor: [255, 255, 255] 
            },
            margin: { left: 14, right: 14 }
        });
        
        doc.save(`Collection_Report_${currentTab}.pdf`);
    };
}

async function downloadExcel() {
    try {
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Collections');

        // 1. Add Logo centered at column F
        const response = await fetch('../assets/images/ml.png');
        const buffer = await response.arrayBuffer();
        const logoId = workbook.addImage({ buffer: buffer, extension: 'png' });

        // We set col: 5 (Column F). 
        // The 'offset' properties move the image slightly to the right 
        // within the cell to achieve a true centered look.
        worksheet.addImage(logoId, {
            tl: { col: 5, row: 0 },
            ext: { width: 120, height: 40 },
            editAs: 'oneCell' 
        });

        // 2. Title Rows (No merge, placed in Column F/Index 6)
        worksheet.getCell('F3').value = "LOANS DEPARTMENT";
        worksheet.getCell('F3').font = { bold: true, size: 14 };
        worksheet.getCell('F3').alignment = { horizontal: 'center' };

        worksheet.getCell('F4').value = "COLLECTION REPORT";
        worksheet.getCell('F4').font = { bold: true, size: 12 };
        worksheet.getCell('F4').alignment = { horizontal: 'center' };

        // 3. Loan Type Info
        let loanTypeDisplay = currentTab === 'motor' 
            ? "MOTOR LOAN (" + document.getElementById('wheelFilter').value.toUpperCase() + ")" 
            : "CAR LOAN";
        worksheet.getCell('A5').value = loanTypeDisplay;
        worksheet.getCell('F5').font = { bold: true };
        worksheet.getCell('F5').alignment = { horizontal: 'center' };

        // 4. Data Extraction - Get ONLY visible rows from table
        const table = document.getElementById('collectionTable');
        const allRows = Array.from(table.querySelectorAll('tr'));
        const visibleRows = allRows.filter(tr => tr.style.display !== 'none' && tr.id !== 'noRecordFound');

        visibleRows.forEach((tr, rowIndex) => {
            const cells = Array.from(tr.querySelectorAll('th, td'));
            const excelRow = worksheet.getRow(rowIndex + 6);

            // Column A: "#"
            const hashCell = excelRow.getCell(1);
            hashCell.value = (rowIndex === 0) ? "#" : rowIndex;
            hashCell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
            
            // Apply Red Header styling to "#"
            if (rowIndex === 0) {
                hashCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFD50000' } };
                hashCell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
            }
            hashCell.alignment = { horizontal: 'center', vertical: 'middle' };

            // Fill Columns B through N
            cells.forEach((cell, colIndex) => {
                const excelCell = excelRow.getCell(colIndex + 2);
                excelCell.value = cell.innerText.trim();    
                excelCell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };

                if (rowIndex === 0) {
                    excelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFD50000' } };
                    excelCell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
                }
                excelCell.alignment = { horizontal: 'center', vertical: 'middle' };
            });
        });

        worksheet.columns.forEach(col => col.width = 18);

        const excelBuffer = await workbook.xlsx.writeBuffer();
        saveAs(new Blob([excelBuffer]), `Collection_Report_${currentTab}.xlsx`);
    } catch (error) {
        console.error("Error generating Excel:", error);
        alert("Failed to download Excel. Please check the console for details.");
    }
}
</script>