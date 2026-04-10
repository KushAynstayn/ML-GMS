<?php include('../includes/header.php'); ?>

<style>
    .tab-btn:focus, .tab-btn:active { outline: none !important; box-shadow: none !important; }
    button:focus { outline: none !important; }
    .overflow-x-auto::-webkit-scrollbar {
        height: 8px;
    }
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #ffffff;
    }
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #ffffff;
        border-radius: 4px;
    }

    /* --- ANIMATIONS FROM ALL LOANS --- */
    @keyframes subtle-jump {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    .hover-jump:hover {
        animation: subtle-jump 0.6s infinite ease-in-out;
    }

    /* Piano Row Effect */
    #tableBody tr {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        z-index: 1;
    }

    #tableBody tr:hover {
        transform: scale(1.015);
        background-color: #fef2f2 !important; /* Very light red */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        z-index: 10;
    }

    /* Table Container Deep Shadow */
    .table-container-shadow {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* Search/Fade Swap Effect */
    @keyframes fadeInSlide {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .row-fade-in {
        animation: fadeInSlide 0.3s ease-out forwards;
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
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        Collection <span class="text-[#D50000]">Report</span>
                    </h2>
                    <p class="text-gray-500 font-medium mt-2 mb-6">
                        Access detailed borrower insights and track all collection data.
                    </p>
                </div>
            </div>

            <div class="flex flex-nowrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">

                <div class="flex-1 min-w-[200px] relative">
                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Search account or reference..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D50000]/20 text-gray-600 placeholder-gray-400"
                    >
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <div class="shrink-0">
                    <select
                        id="vehicleTypeFilter"
                        class="px-4 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-gray-500 font-bold text-xs uppercase cursor-pointer hover:border-gray-300"
                    >
                        <option value="all">All Vehicle Type</option>
                        <option value="car">Car Loan</option>
                        <option value="2-wheels">Motor 2-Wheels</option>
                        <option value="3-wheels">Motor 3-Wheels</option>
                    </select>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <?php include '../includes/date_picker.php'; ?>
                </div>

                <div class="relative inline-block text-left shrink-0" id="downloadDropdown">
                    <button onclick="toggleDropdown()" class="flex items-center gap-2 bg-[#D50000] text-white px-5 py-2 rounded-lg font-medium hover:bg-[#B70000] transition-all shadow-sm text-sm hover-jump">
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

        <div class="bg-white rounded-xl table-container-shadow overflow-x-auto border border-gray-100">
            <table id="collectionTable" class="w-full text-left border-collapse min-w-[1400px]">
                <thead class="bg-[#D50000]">
                    <tr>
                        <th class="px-2 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-white">Date Granted</th>
                        <th class="px-2 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-white">Date Installed</th>
                        <th class="px-3 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-white">Account Name</th>
                        <th class="px-2 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-white">Monthly Amortization</th>
                        <th class="px-2 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-white">Principal</th>
                        <th class="px-2 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-white">Interest</th>
                        <th class="px-2 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-white">Term</th>
                        <th class="px-2 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-white">Reference Number</th>
                        <th class="px-2 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-white">Date Applied</th>
                        <th class="px-2 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-white">Date Paid</th>
                        <th class="px-2 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-white">Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100">
                    <tr id="loadingRow">
                        <td colspan="11" class="px-4 py-12 text-center text-sm text-gray-400 italic">
                            Loading collection report...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
let collectionRows = [];

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function formatStatusBadge(status) {
    const normalized = (status || '').toLowerCase();

    if (normalized === 'fully paid') {
        return `<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase">Fully Paid</span>`;
    }

    if (normalized === 'partially paid') {
        return `<span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-bold uppercase">Partially Paid</span>`;
    }

    return `<span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-[10px] font-bold uppercase">Pending</span>`;
}

function renderTableRows(rows) {
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    if (!rows.length) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="11" class="px-4 py-12 text-center text-sm text-gray-400 italic">
                    No records found.
                </td>
            </tr>`;
        return;
    }

    rows.forEach(row => {
        const tr = document.createElement('tr');
        // Combined fade-in and specific border styles. 
        // Note: The "Piano Row" effect is targeted via CSS #tableBody tr
        tr.className = 'row-fade-in border-b border-gray-100'; 
        
        tr.onclick = () => {
            if (typeof viewAmortization === 'function') {
                viewAmortization(row.loan_id, 'secondary');
            } else {
                console.error("viewAmortization function not found. Check if amortization.js is loaded.");
            }
        };

        tr.innerHTML = `
            <td class="px-2 py-4 text-xs text-gray-600 text-center">${escapeHtml(row.date_granted)}</td>
            <td class="px-2 py-4 text-xs text-gray-600 text-center">${escapeHtml(row.date_installed)}</td>
            <td class="px-3 py-4 text-xs text-gray-800 font-medium text-center">${escapeHtml(row.account_name)}</td>
            <td class="px-2 py-4 text-xs text-gray-700 text-center">${escapeHtml(row.monthly_amortization)}</td>
            <td class="px-2 py-4 text-xs text-gray-600 text-center">${escapeHtml(row.principal)}</td>
            <td class="px-2 py-4 text-xs text-gray-600 text-center">${escapeHtml(row.interest)}</td>
            <td class="px-2 py-4 text-xs text-gray-600 text-center">${escapeHtml(row.term)}</td>
            <td class="px-2 py-4 text-xs font-mono text-gray-500 uppercase text-center">${escapeHtml(row.reference_number)}</td>
            <td class="px-2 py-4 text-xs text-gray-600 text-center">${escapeHtml(row.date_applied)}</td>
            <td class="px-2 py-4 text-xs text-gray-600 text-center">${escapeHtml(row.date_paid)}</td>
            <td class="px-2 py-4 text-xs text-center">${formatStatusBadge(row.status)}</td>
        `;

        tableBody.appendChild(tr);
    });
}

async function fetchCollectionReport() {
    const loadingRow = document.getElementById('loadingRow');
    const tableBody = document.getElementById('tableBody');

    const search = document.getElementById('searchInput').value.trim();
    const vehicleType = document.getElementById('vehicleTypeFilter').value;
    const startDate = document.getElementById('startDate')?.value || '';
    const endDate = document.getElementById('endDate')?.value || '';

    tableBody.innerHTML = `
        <tr id="loadingRow">
            <td colspan="11" class="px-4 py-12 text-center text-sm text-gray-400 italic">
                Loading collection report...
            </td>
        </tr>
    `;

    try {
        const params = new URLSearchParams({
            search,
            vehicle_type: vehicleType,
            start_date: startDate,
            end_date: endDate
        });

        const response = await fetch(`../api/get_collection_report.php?${params.toString()}`);
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Failed to fetch collection report.');
        }

        collectionRows = result.data || [];
        renderTableRows(collectionRows);
    } catch (error) {
        console.error(error);
        tableBody.innerHTML = `
            <tr>
                <td colspan="11" class="px-4 py-12 text-center text-sm text-red-500 italic">
                    Failed to load collection report.
                </td>
            </tr>
        `;
    }
}

function toggleDropdown() {
    document.getElementById('dropdownMenu').classList.toggle('hidden');
}

window.onclick = function(event) {
    if (!event.target.closest('#downloadDropdown')) {
        const menu = document.getElementById('dropdownMenu');
        if (menu) menu.classList.add('hidden');
    }
};

document.getElementById('searchInput').addEventListener('input', fetchCollectionReport);
document.getElementById('vehicleTypeFilter').addEventListener('change', fetchCollectionReport);

if (document.getElementById('startDate')) {
    document.getElementById('startDate').addEventListener('change', fetchCollectionReport);
}

if (document.getElementById('endDate')) {
    document.getElementById('endDate').addEventListener('change', fetchCollectionReport);
}

window.addEventListener('load', fetchCollectionReport);

// --- Download Functions ---

function getReportDateString() {
    const endDateVal = document.getElementById('endDate')?.value;
    let targetDate = endDateVal ? new Date(endDateVal) : new Date();
    
    return `As of ${targetDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;
}

function getVisibleTableRowsForExport() {
    const headerRow = Array.from(document.querySelectorAll('#collectionTable thead th')).map(th => th.innerText.trim());
    const dataRows = Array.from(document.querySelectorAll('#collectionTable tbody tr'))
        .filter(row => !row.id || (row.id !== 'noRecordFound' && row.id !== 'loadingRow'))
        .map(row => Array.from(row.querySelectorAll('td')).map(td => td.innerText.trim()));

    return { headerRow, dataRows };
}

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'letter');
    const img = new Image();
    img.src = '../assets/images/ml.png';

    const dateStr = getReportDateString();

    const filterSelect = document.getElementById('vehicleTypeFilter');
    const filterText = filterSelect.options[filterSelect.selectedIndex].text.toUpperCase();

    const { headerRow, dataRows } = getVisibleTableRowsForExport();

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
        doc.text(filterText, 14, 40);

        doc.autoTable({
            head: [headerRow],
            body: dataRows,
            startY: 45,
            theme: 'grid',
            styles: {
                fontSize: 6,
                halign: 'center',
                textColor: [0, 0, 0],
                lineColor: [200, 200, 200]
            },
            headStyles: {
                fillColor: [213, 0, 0],
                textColor: [255, 255, 255]
            },
            margin: { left: 14, right: 14 }
        });

        doc.save(`Collection_Report_${filterText.replace(/\s+/g, '_')}.pdf`);
    };
}

async function downloadExcel() {
    try {
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Collections');

        const response = await fetch('../assets/images/ml.png');
        const buffer = await response.arrayBuffer();
        const logoId = workbook.addImage({ buffer: buffer, extension: 'png' });

        worksheet.addImage(logoId, {
            tl: { col: 5, row: 0 },
            ext: { width: 120, height: 40 },
            editAs: 'oneCell'
        });

        worksheet.getCell('F3').value = "LOANS DEPARTMENT";
        worksheet.getCell('F3').font = { bold: true, size: 14 };
        worksheet.getCell('F3').alignment = { horizontal: 'center' };

        worksheet.getCell('F4').value = "COLLECTION REPORT";
        worksheet.getCell('F4').font = { bold: true, size: 12 };
        worksheet.getCell('F4').alignment = { horizontal: 'center' };
        
        // Date String updated to "As of..." and made BOLD
        worksheet.getCell('F5').value = getReportDateString();
        worksheet.getCell('F5').font = { bold: true, size: 10 };
        worksheet.getCell('F5').alignment = { horizontal: 'center' };

        const filterSelect = document.getElementById('vehicleTypeFilter');
        const filterText = filterSelect.options[filterSelect.selectedIndex].text.toUpperCase();

        // Moved "ALL VEHICLE TYPE" down to Row 6
        worksheet.getCell('A6').value = filterText;
        worksheet.getCell('A6').font = { bold: true };

        const { headerRow, dataRows } = getVisibleTableRowsForExport();

        const allRows = [headerRow, ...dataRows];

        allRows.forEach((rowData, rowIndex) => {
            // Pushing the table down to start at Row 7
            const excelRow = worksheet.getRow(rowIndex + 7);

            rowData.forEach((value, colIndex) => {
                const cell = excelRow.getCell(colIndex + 1);
                cell.value = value;
                cell.border = {
                    top: { style: 'thin' },
                    left: { style: 'thin' },
                    bottom: { style: 'thin' },
                    right: { style: 'thin' }
                };
                cell.alignment = { horizontal: 'center', vertical: 'middle' };

                if (rowIndex === 0) {
                    cell.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: { argb: 'FFD50000' }
                    };
                    cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
                }
            });
        });

        worksheet.columns = [
            { width: 16 },
            { width: 16 },
            { width: 28 },
            { width: 18 },
            { width: 16 },
            { width: 16 },
            { width: 12 },
            { width: 20 },
            { width: 16 },
            { width: 16 },
            { width: 18 }
        ];

        const excelBuffer = await workbook.xlsx.writeBuffer();
        saveAs(new Blob([excelBuffer]), `Collection_Report_${filterText.replace(/\s+/g, '_')}.xlsx`);
    } catch (error) {
        console.error("Error generating Excel:", error);
    }
}
</script>

<?php include('../includes/modals/amortization_modal.php'); ?>
<script src="../assets/js/amortization.js"></script>

<script>
// This function bridges the report to your existing amortization logic
function viewSecondaryLedger(loanId) {
    if (typeof openAmortizationModal === 'function') {
        openAmortizationModal(loanId);
    } else {
        console.error("openAmortizationModal function not found in amortization.js");
    }
}
</script>