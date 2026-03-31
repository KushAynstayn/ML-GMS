<?php
require_once __DIR__ . '/../includes/init.php';
include('../includes/header.php');
?>

<style>
    .tab-btn:focus, .tab-btn:active { outline: none !important; box-shadow: none !important; }
    button:focus { outline: none !important; }
    .bg-ml-red { background-color: #D50000; }
    .text-ml-red { color: #D50000; }
    .subtotal-row { background-color: #f9fafb; font-weight: 700; }
    .grand-total-row { background-color: #f3f4f6; font-weight: 800; border-top: 2px solid #D50000; }
    .category-header { background-color: #fee2e2; color: #b91c1c; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }

    .filter-card,
    .summary-card {
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
    }

    .summary-card .label {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .summary-card .value {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
    }

    .view-btn.active {
        background-color: #D50000;
        color: #fff;
    }

    .view-btn {
        border: 1px solid #e5e7eb;
    }

    .loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 20;
        border-radius: 16px;
    }

    .spinner {
        width: 36px;
        height: 36px;
        border: 4px solid #fecaca;
        border-top-color: #D50000;
        border-radius: 9999px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<div class="flex h-screen overflow-hidden">
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto h-full">
        <header class="mb-6">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        Running <span class="text-ml-red">Receivables</span>
                    </h2>
                    <p class="text-gray-500 font-medium mt-2">
                        Summary of outstanding balances across main zones, zones, regions, and loan types.
                    </p>
                </div>

                <div class="relative inline-block text-left shrink-0" id="downloadDropdown">
                    <button onclick="toggleDropdown()" class="flex items-center gap-2 bg-[#D50000] text-white px-5 py-2.5 rounded-lg font-medium hover:bg-[#B70000] transition-all shadow-sm text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download
                    </button>

                    <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden">
                        <button onclick="downloadExcel()" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                            <span class="text-green-600 font-bold">EXCEL</span>
                        </button>
                        <button onclick="downloadPDF()" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 border-t border-gray-100">
                            <span class="text-[#D50000] font-bold">PDF</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="filter-card p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-2">From</label>
                    <input type="date" id="startDate" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-2">To</label>
                    <input type="date" id="endDate" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-2">Main Zone</label>
                    <select id="mainZoneFilter" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm">
                        <option value="">All Main Zones</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-2">Zone</label>
                    <select id="zoneFilter" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm">
                        <option value="">All Zones</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-2">Region</label>
                    <select id="regionFilter" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm">
                        <option value="">All Regions</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button id="applyFiltersBtn" class="w-full bg-[#D50000] text-white rounded-lg px-4 py-2.5 text-sm font-semibold hover:bg-[#B70000] transition">
                        Apply
                    </button>
                    <button id="resetFiltersBtn" class="w-full bg-white border border-gray-200 text-gray-700 rounded-lg px-4 py-2.5 text-sm font-semibold hover:bg-gray-50 transition">
                        Reset
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 mt-4">
                <p id="asOfDateText" class="text-sm font-extrabold text-gray-800 uppercase tracking-wide">
                    SELECT DATE RANGE TO VIEW REPORT
                </p>

                <div class="inline-flex rounded-lg overflow-hidden border border-gray-200">
                    <button class="view-btn active px-4 py-2 text-sm font-semibold" data-view="summary">Summary View</button>
                    <button class="view-btn px-4 py-2 text-sm font-semibold" data-view="details">Detailed View</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
            <div class="summary-card p-5">
                <div class="label">LNCR Outstanding</div>
                <div id="cardLncr" class="value mt-2">0.00</div>
            </div>
            <div class="summary-card p-5">
                <div class="label">VISMIN Outstanding</div>
                <div id="cardVismin" class="value mt-2">0.00</div>
            </div>
            <div class="summary-card p-5">
                <div class="label">Head Office Outstanding</div>
                <div id="cardHeadOffice" class="value mt-2">0.00</div>
            </div>
            <div class="summary-card p-5">
                <div class="label">Jewelry Outstanding</div>
                <div id="cardJewelry" class="value mt-2">0.00</div>
            </div>
            <div class="summary-card p-5">
                <div class="label">Grand Total</div>
                <div id="cardGrandTotal" class="value mt-2 text-ml-red">0.00</div>
            </div>
        </div>

        <div class="relative">
            <div id="loadingOverlay" class="loading-overlay">
                <div class="spinner"></div>
            </div>

            <div id="summarySection" class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <table id="receivableTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-ml-red text-white">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider">Loans</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center">LNCR</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center">VISMIN</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center">HEAD OFFICE</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center">JEWELRY</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody id="summaryTableBody" class="divide-y divide-gray-100 text-gray-700"></tbody>
                </table>
            </div>

            <div id="detailsSection" class="hidden bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 mt-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-extrabold text-gray-900">Running Receivables Details</h3>
                    <p class="text-sm text-gray-500 mt-1">Borrower-level outstanding balances based on selected filters.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-ml-red text-white">
                                <th class="px-4 py-3 text-xs font-bold uppercase">Ref No.</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase">Borrower</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase">Loan Type</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase">Main Zone</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase">Zone</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase">Region</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase text-right">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody id="detailsTableBody" class="divide-y divide-gray-100 text-gray-700"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
const SUMMARY_STRUCTURE = {
    ml_loans: [
        { key: 'auto_car_loan', label: 'Auto/Car Loan' },
        { key: 'prenda', label: ' - Prenda' },
        { key: 'pre_owned', label: ' - Pre-owned' },
        { key: 'surplus', label: ' - Surplus' },
        { key: 'motorcycle_loan', label: 'Motorcycle Loan' },
        { key: 'two_wheels', label: ' - 2-Wheels' },
        { key: 'three_wheels', label: ' - 3-Wheels' },
        { key: 'real_estate_loan', label: 'Real-Estate Loan' },
        { key: 'commercial_loan', label: 'Commercial Loan' },
        { key: 'salary_loan', label: 'Salary Loan' },
        { key: 'truck_loan', label: 'Truck Loan' }
    ],
    other_loans: [
        { key: 'small_business_loan', label: 'Small Business Loan' },
        { key: 'pensioners_loan', label: "Pensioner's Loan" }
    ]
};

document.addEventListener('DOMContentLoaded', async function () {
    bindEvents();
    updateDateDisplay();
    await loadFilterOptions();
    await loadReport();
});

function bindEvents() {
    document.getElementById('startDate').addEventListener('change', updateDateDisplay);
    document.getElementById('endDate').addEventListener('change', updateDateDisplay);
    document.getElementById('mainZoneFilter').addEventListener('change', handleMainZoneChange);
    document.getElementById('zoneFilter').addEventListener('change', handleZoneChange);
    document.getElementById('applyFiltersBtn').addEventListener('click', loadReport);
    document.getElementById('resetFiltersBtn').addEventListener('click', resetFilters);

    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.view-btn').forEach(x => x.classList.remove('active'));
            this.classList.add('active');

            const view = this.dataset.view;
            document.getElementById('summarySection').classList.toggle('hidden', view !== 'summary');
            document.getElementById('detailsSection').classList.toggle('hidden', view !== 'details');
        });
    });
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

function updateDateDisplay() {
    const startVal = document.getElementById('startDate').value;
    const endVal = document.getElementById('endDate').value;
    const display = document.getElementById('asOfDateText');

    if (!startVal && !endVal) {
        display.innerText = 'SELECT DATE RANGE TO VIEW REPORT';
        return;
    }

    if (startVal && !endVal) {
        const start = new Date(startVal);
        display.innerText = `AS OF ${formatLongDate(start)}`;
        return;
    }

    const start = new Date(startVal);
    const end = new Date(endVal);

    const startMonth = start.toLocaleDateString('en-US', { month: 'long' }).toUpperCase();
    const endMonth = end.toLocaleDateString('en-US', { month: 'long' }).toUpperCase();
    const startDay = start.getDate();
    const endDay = end.getDate();
    const startYear = start.getFullYear();
    const endYear = end.getFullYear();

    if (startMonth === endMonth && startYear === endYear) {
        display.innerText = `AS OF ${startMonth} ${startDay}-${endDay}, ${startYear}`;
    } else {
        display.innerText = `AS OF ${startMonth} ${startDay}, ${startYear} - ${endMonth} ${endDay}, ${endYear}`;
    }
}

function formatLongDate(date) {
    const month = date.toLocaleDateString('en-US', { month: 'long' }).toUpperCase();
    return `${month} ${date.getDate()}, ${date.getFullYear()}`;
}

function formatAmount(value) {
    const num = Number(value || 0);
    return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

async function loadFilterOptions() {
    const response = await fetch('../api/get_running_receivables_filters.php');
    const data = await response.json();

    populateSelect('mainZoneFilter', data.main_zones || [], 'main_zone_code', 'main_zone_description', 'All Main Zones');
    populateSelect('zoneFilter', data.zones || [], 'zone_code', 'zone_description', 'All Zones');
    populateSelect('regionFilter', data.regions || [], 'region_code', 'region_description', 'All Regions');
}

function populateSelect(selectId, items, valueKey, textKey, defaultLabel) {
    const select = document.getElementById(selectId);
    select.innerHTML = `<option value="">${defaultLabel}</option>`;

    items.forEach(item => {
        const option = document.createElement('option');
        option.value = item[valueKey];
        option.textContent = `${item[valueKey]} - ${item[textKey]}`;
        select.appendChild(option);
    });
}

async function handleMainZoneChange() {
    const mainZone = document.getElementById('mainZoneFilter').value;
    const response = await fetch(`../api/get_running_receivables_filters.php?main_zone_code=${encodeURIComponent(mainZone)}`);
    const data = await response.json();

    populateSelect('zoneFilter', data.zones || [], 'zone_code', 'zone_description', 'All Zones');
    populateSelect('regionFilter', data.regions || [], 'region_code', 'region_description', 'All Regions');
}

async function handleZoneChange() {
    const mainZone = document.getElementById('mainZoneFilter').value;
    const zone = document.getElementById('zoneFilter').value;
    const response = await fetch(`../api/get_running_receivables_filters.php?main_zone_code=${encodeURIComponent(mainZone)}&zone_code=${encodeURIComponent(zone)}`);
    const data = await response.json();

    populateSelect('regionFilter', data.regions || [], 'region_code', 'region_description', 'All Regions');
}

function resetFilters() {
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';
    document.getElementById('mainZoneFilter').value = '';
    document.getElementById('zoneFilter').innerHTML = '<option value="">All Zones</option>';
    document.getElementById('regionFilter').innerHTML = '<option value="">All Regions</option>';
    updateDateDisplay();
    loadFilterOptions().then(loadReport);
}

function getFilters() {
    return {
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value,
        main_zone_code: document.getElementById('mainZoneFilter').value,
        zone_code: document.getElementById('zoneFilter').value,
        region_code: document.getElementById('regionFilter').value
    };
}

async function loadReport() {
    showLoading(true);

    try {
        const params = new URLSearchParams(getFilters());
        const response = await fetch(`../api/get_running_receivables_report.php?${params.toString()}`);
        const data = await response.json();

        if (data.error) {
            throw new Error(data.message || 'Failed to load report.');
        }

        renderSummaryCards(data.cards || {});
        renderSummaryTable(data.summary || {});
        renderDetailsTable(data.details || []);
    } catch (error) {
        console.error('Failed to load report:', error);
        alert(error.message || 'Failed to load running receivables report.');
    } finally {
        showLoading(false);
    }
}

function showLoading(show) {
    document.getElementById('loadingOverlay').style.display = show ? 'flex' : 'none';
}

function renderSummaryCards(cards) {
    document.getElementById('cardLncr').innerText = formatAmount(cards.lncr_total || 0);
    document.getElementById('cardVismin').innerText = formatAmount(cards.vismin_total || 0);
    document.getElementById('cardHeadOffice').innerText = formatAmount(cards.head_office_total || 0);
    document.getElementById('cardJewelry').innerText = formatAmount(cards.jewelry_total || 0);
    document.getElementById('cardGrandTotal').innerText = formatAmount(cards.grand_total || 0);
}

function createSummaryRow(label, values, rowClass = '') {
    const lncr = Number(values.lncr || 0);
    const vismin = Number(values.vismin || 0);
    const headOffice = Number(values.head_office || 0);
    const jewelry = Number(values.jewelry || 0);
    const total = lncr + vismin + headOffice + jewelry;

    return `
        <tr class="${rowClass}">
            <td class="px-6 py-4 text-sm font-medium">${label}</td>
            <td class="px-6 py-4 text-sm text-center">${formatAmount(lncr)}</td>
            <td class="px-6 py-4 text-sm text-center">${formatAmount(vismin)}</td>
            <td class="px-6 py-4 text-sm text-center">${formatAmount(headOffice)}</td>
            <td class="px-6 py-4 text-sm text-center">${formatAmount(jewelry)}</td>
            <td class="px-6 py-4 text-sm text-center font-semibold">${formatAmount(total)}</td>
        </tr>
    `;
}

function renderSummaryTable(summary) {
    let html = '';

    html += `<tr class="category-header"><td colspan="6" class="px-6 py-3 text-sm">ML LOANS</td></tr>`;

    let mlSubtotal = { lncr: 0, vismin: 0, head_office: 0, jewelry: 0 };
    SUMMARY_STRUCTURE.ml_loans.forEach(item => {
        const row = summary[item.key] || {};
        html += createSummaryRow(item.label, row, 'hover:bg-gray-50 transition-colors');
        mlSubtotal.lncr += Number(row.lncr || 0);
        mlSubtotal.vismin += Number(row.vismin || 0);
        mlSubtotal.head_office += Number(row.head_office || 0);
        mlSubtotal.jewelry += Number(row.jewelry || 0);
    });

    html += createSummaryRow('Subtotal (ML Loans)', mlSubtotal, 'subtotal-row border-t-2 border-gray-200');

    html += `<tr class="category-header"><td colspan="6" class="px-6 py-3 text-sm">OTHER LOANS</td></tr>`;

    let otherSubtotal = { lncr: 0, vismin: 0, head_office: 0, jewelry: 0 };
    SUMMARY_STRUCTURE.other_loans.forEach(item => {
        const row = summary[item.key] || {};
        html += createSummaryRow(item.label, row, 'hover:bg-gray-50 transition-colors');
        otherSubtotal.lncr += Number(row.lncr || 0);
        otherSubtotal.vismin += Number(row.vismin || 0);
        otherSubtotal.head_office += Number(row.head_office || 0);
        otherSubtotal.jewelry += Number(row.jewelry || 0);
    });

    html += createSummaryRow('Subtotal (Other Loans)', otherSubtotal, 'subtotal-row border-t-2 border-gray-200');

    const grandTotal = {
        lncr: mlSubtotal.lncr + otherSubtotal.lncr,
        vismin: mlSubtotal.vismin + otherSubtotal.vismin,
        head_office: mlSubtotal.head_office + otherSubtotal.head_office,
        jewelry: mlSubtotal.jewelry + otherSubtotal.jewelry
    };

    html += `
        <tr class="grand-total-row">
            <td class="px-6 py-5 text-base uppercase">Grand Total</td>
            <td class="px-6 py-5 text-sm text-center">${formatAmount(grandTotal.lncr)}</td>
            <td class="px-6 py-5 text-sm text-center">${formatAmount(grandTotal.vismin)}</td>
            <td class="px-6 py-5 text-sm text-center">${formatAmount(grandTotal.head_office)}</td>
            <td class="px-6 py-5 text-sm text-center">${formatAmount(grandTotal.jewelry)}</td>
            <td class="px-6 py-5 text-lg text-center text-ml-red">${formatAmount(grandTotal.lncr + grandTotal.vismin + grandTotal.head_office + grandTotal.jewelry)}</td>
        </tr>
    `;

    document.getElementById('summaryTableBody').innerHTML = html;
}

function renderDetailsTable(rows) {
    const tbody = document.getElementById('detailsTableBody');

    if (!rows.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-6 text-center text-sm text-gray-500">
                    No running receivables found for the selected filters.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = rows.map(row => `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm">${escapeHtml(row.reference_number || '')}</td>
            <td class="px-4 py-3 text-sm">${escapeHtml(row.borrower_name || '')}</td>
            <td class="px-4 py-3 text-sm">${escapeHtml(row.loan_type_label || '')}</td>
            <td class="px-4 py-3 text-sm">${escapeHtml(row.main_zone_display || '')}</td>
            <td class="px-4 py-3 text-sm">${escapeHtml(row.zone_display || '')}</td>
            <td class="px-4 py-3 text-sm">${escapeHtml(row.region_display || '')}</td>
            <td class="px-4 py-3 text-sm text-right font-semibold">${formatAmount(row.outstanding_balance || 0)}</td>
        </tr>
    `).join('');
}

function escapeHtml(str) {
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'letter');
    doc.setFontSize(14);
    doc.text("Running Receivables Report", 14, 15);
    doc.setFontSize(10);
    doc.text(document.getElementById('asOfDateText').innerText, 14, 22);
    doc.autoTable({
        html: '#receivableTable',
        startY: 28,
        theme: 'grid',
        styles: { fontSize: 8, halign: 'center' },
        headStyles: { fillColor: [213, 0, 0] }
    });
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
            worksheet.getRow(rowIndex + 4).getCell(colIndex + 1).value = cell.innerText.trim();
        });
    });

    const buffer = await workbook.xlsx.writeBuffer();
    saveAs(new Blob([buffer]), 'Running_Receivables.xlsx');
}
</script>