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

    /* Jumping Animation */
    @keyframes subtle-jump {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    .hover-jump:hover {
        animation: subtle-jump 0.6s infinite ease-in-out;
    }

    /* Piano Row Effect for both tables */
    #summaryTableBody tr, #detailsTableBody tr {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        z-index: 1;
    }

    #summaryTableBody tr:hover, #detailsTableBody tr:hover {
        transform: scale(1.015);
        background-color: #fef2f2 !important; /* Light red piano effect */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        z-index: 10;
    }

    /* Glowing Power Effect */
    .summary-card {
        animation: powerGlow 3s infinite ease-in-out;
        border: 1px solid rgba(199, 18, 18, 0.2);
    }

    @keyframes powerGlow {
        0%, 100% { 
            box-shadow: 0 0 5px rgba(213, 0, 0, 0.1); 
            border-color: rgba(213, 0, 0, 0.1);
        }
        50% { 
            box-shadow: 0 0 20px rgba(207, 143, 143, 0.4), inset 0 0 10px rgba(213, 0, 0, 0.05); 
            border-color: rgba(255, 255, 255, 0.6);
        }
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
        border-color: #D50000;
    }

    .view-btn {
        border: 1px solid #e5e7eb;
        background: #fff;
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

    /* Modal Animation */
    @keyframes modal-pop {
        0% { transform: scale(0.95); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-modal-pop {
        animation: modal-pop 0.2s ease-out forwards;
    }

    /* Custom Dropdown Styling */
    .custom-dropdown-container {
        position: relative;
    }

    .custom-dropdown-content {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 200; 
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-top: 4px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        max-height: 210px; 
        overflow-y: auto;
    }

    /* Upward Dropdown Specific Styling */
    .drop-up-content {
        top: auto !important;
        bottom: 100% !important;
        margin-top: 0 !important;
        margin-bottom: 4px !important;
    }

    .option-item {
        padding: 10px 12px;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .option-item:hover {
        background-color: #f8fafc;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<div class="flex h-screen overflow-hidden">
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto h-full">
        <header class="mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                Running <span class="text-ml-red">Receivables</span>
            </h2>
            <p class="text-gray-500 font-medium mt-2">
                Summary of outstanding balances across main zones, zones, regions, and loan types.
            </p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-8">
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

        <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
            <div class="text-left">
                <div id="dateRangeContainer">
                    <p id="asOfDateText" class="text-sm font-extrabold text-gray-800 uppercase tracking-tight">
                        SELECT DATE RANGE TO VIEW REPORT
                    </p>
                </div>
                <div id="detailedInfoContainer" class="hidden">
                    <h3 class="text-sm font-extrabold text-gray-900 uppercase tracking-tight">Running Receivables Details</h3>
                    <p class="text-sm text-gray-500 mt-1 font-medium italic">Borrower-level outstanding balances based on selected filters.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="inline-flex rounded-lg overflow-hidden border border-gray-200 bg-white shadow-sm">
                    <button class="view-btn active px-4 py-2.5 text-xs font-bold uppercase tracking-wider" data-view="summary">Summary View</button>
                    <button class="view-btn px-4 py-2.5 text-xs font-bold uppercase tracking-wider" data-view="details">Detailed View</button>
                </div>

                <button onclick="toggleFilterModal(true)" class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-5 py-2.5 rounded-lg font-bold shadow-sm text-xs uppercase tracking-wider hover:bg-gray-50 transition-all hover-jump">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filters
                </button>

                <div class="relative inline-block text-left" id="downloadDropdown">
                    <button onclick="toggleDropdown()" class="flex items-center gap-2 bg-[#D50000] text-white px-5 py-2.5 rounded-lg font-bold shadow-sm text-xs uppercase tracking-wider hover:bg-[#B70000] transition-all hover-jump">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download
                    </button>
                    <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden">
                        <button onclick="downloadExcel()" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50"><span class="text-green-600 font-bold">EXCEL</span></button>
                        <button onclick="downloadPDF()" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 border-t border-gray-100"><span class="text-[#D50000] font-bold">PDF</span></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative">
            <div id="loadingOverlay" class="loading-overlay"><div class="spinner"></div></div>

            <div id="summarySection" class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <table id="receivableTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-ml-red text-white">
                            <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider">Loans</th>
                            <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-center">LNCR</th>
                            <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-center">VISMIN</th>
                            <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-center">HEAD OFFICE</th>
                            <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-center">JEWELRY</th>
                            <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-center">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody id="summaryTableBody" class="divide-y divide-gray-100 text-gray-700"></tbody>
                </table>
            </div>

            <div id="detailsSection" class="hidden bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
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

<div id="filterModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[150] p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-sm max-w-sm overflow-visible animate-modal-pop">
        <div class="p-5 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Filter <span class="text-[#D50000]">Report</span></h3>
            <button onclick="toggleFilterModal(false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">From</label>
                    <input type="date" id="startDate" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">To</label>
                    <input type="date" id="endDate" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none transition-all">
                </div>
            </div>

            <div class="custom-dropdown-container">
                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Main Zone</label>
                <input type="hidden" id="mainZoneFilter" value="">
                <button type="button" onclick="toggleCustomDropdown('mainZoneContent')" class="w-full flex justify-between items-center border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white hover:border-gray-300 focus:ring-2 focus:ring-red-500 outline-none" id="mainZoneDisplay">
                    <span class="truncate">All Main Zones</span>
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
                <div id="mainZoneContent" class="custom-dropdown-content hidden"></div>
            </div>

            <div class="custom-dropdown-container">
                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Zone</label>
                <input type="hidden" id="zoneFilter" value="">
                <button type="button" onclick="toggleCustomDropdown('zoneContent')" class="w-full flex justify-between items-center border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white hover:border-gray-300 focus:ring-2 focus:ring-red-500 outline-none" id="zoneDisplay">
                    <span class="truncate">All Zones</span>
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
                <div id="zoneContent" class="custom-dropdown-content hidden"></div>
            </div>

            <div class="custom-dropdown-container">
                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Region</label>
                <input type="hidden" id="regionFilter" value="">
                <button type="button" onclick="toggleCustomDropdown('regionContent')" class="w-full flex justify-between items-center border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white hover:border-gray-300 focus:ring-2 focus:ring-red-500 outline-none" id="regionDisplay">
                    <span class="truncate">All Regions</span>
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
                <div id="regionContent" class="custom-dropdown-content drop-up-content hidden"></div>
            </div>
        </div>

        <div class="p-5 bg-gray-50 border-t flex gap-3">
            <button id="applyFiltersBtn" class="flex-1 bg-[#D50000] text-white py-2.5 rounded-xl font-bold text-sm hover:bg-[#B70000] transition-all shadow-md active:scale-95">Apply Filters</button>
            <button id="resetFiltersBtn" class="flex-1 bg-white border border-gray-200 text-gray-600 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-100 transition-all active:scale-95">Reset</button>
        </div>
    </div>
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
    
    document.getElementById('applyFiltersBtn').addEventListener('click', function() {
        loadReport();
        toggleFilterModal(false);
    });

    document.getElementById('resetFiltersBtn').addEventListener('click', resetFilters);

    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.view-btn').forEach(x => x.classList.remove('active'));
            this.classList.add('active');

            const view = this.dataset.view;
            document.getElementById('summarySection').classList.toggle('hidden', view !== 'summary');
            document.getElementById('detailsSection').classList.toggle('hidden', view !== 'details');
            const dateContainer = document.getElementById('dateRangeContainer');
            const detailedContainer = document.getElementById('detailedInfoContainer');
            if (view === 'details') { dateContainer.classList.add('hidden'); detailedContainer.classList.remove('hidden'); }
            else { dateContainer.classList.remove('hidden'); detailedContainer.classList.add('hidden'); }
        });
    });

    window.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-dropdown-container')) {
            document.querySelectorAll('.custom-dropdown-content').forEach(el => el.classList.add('hidden'));
        }
    });
}

function toggleCustomDropdown(id) {
    const content = document.getElementById(id);
    const isHidden = content.classList.contains('hidden');
    document.querySelectorAll('.custom-dropdown-content').forEach(el => el.classList.add('hidden'));
    if (isHidden) content.classList.remove('hidden');
}

function selectCustomOption(hiddenId, displayId, value, text, dropdownId, callback) {
    document.getElementById(hiddenId).value = value;
    document.getElementById(displayId).querySelector('span').innerText = text;
    document.getElementById(dropdownId).classList.add('hidden');
    if (callback) callback();
}

function toggleFilterModal(show) {
    const modal = document.getElementById('filterModal');
    if (show) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    } else {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function toggleDropdown() { document.getElementById('dropdownMenu').classList.toggle('hidden'); }

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
    populateCustomDropdown('mainZoneContent', 'mainZoneFilter', 'mainZoneDisplay', data.main_zones || [], 'main_zone_code', 'main_zone_description', 'All Main Zones', handleMainZoneChange);
    populateCustomDropdown('zoneContent', 'zoneFilter', 'zoneDisplay', data.zones || [], 'zone_code', 'zone_description', 'All Zones', handleZoneChange);
    populateCustomDropdown('regionContent', 'regionFilter', 'regionDisplay', data.regions || [], 'region_code', 'region_description', 'All Regions');
}

function populateCustomDropdown(containerId, hiddenId, displayId, items, valueKey, textKey, defaultLabel, callback) {
    const container = document.getElementById(containerId);
    let html = `<div class="option-item text-gray-500 italic" onclick="selectCustomOption('${hiddenId}', '${displayId}', '', '${defaultLabel}', '${containerId}', ${callback ? callback.name : 'null'})">${defaultLabel}</div>`;
    items.forEach(item => { const text = `${item[valueKey]} - ${item[textKey]}`; html += `<div class="option-item" onclick="selectCustomOption('${hiddenId}', '${displayId}', '${item[valueKey]}', '${text}', '${containerId}', ${callback ? callback.name : 'null'})">${text}</div>`; });
    container.innerHTML = html;
}

async function handleMainZoneChange() {
    const mainZone = document.getElementById('mainZoneFilter').value;
    const response = await fetch(`../api/get_running_receivables_filters.php?main_zone_code=${encodeURIComponent(mainZone)}`);
    const data = await response.json();
    populateCustomDropdown('zoneContent', 'zoneFilter', 'zoneDisplay', data.zones || [], 'zone_code', 'zone_description', 'All Zones', handleZoneChange);
    populateCustomDropdown('regionContent', 'regionFilter', 'regionDisplay', data.regions || [], 'region_code', 'region_description', 'All Regions');
    document.getElementById('zoneDisplay').querySelector('span').innerText = 'All Zones'; document.getElementById('zoneFilter').value = '';
    document.getElementById('regionDisplay').querySelector('span').innerText = 'All Regions'; document.getElementById('regionFilter').value = '';
}

async function handleZoneChange() {
    const mainZone = document.getElementById('mainZoneFilter').value;
    const zone = document.getElementById('zoneFilter').value;
    const response = await fetch(`../api/get_running_receivables_filters.php?main_zone_code=${encodeURIComponent(mainZone)}&zone_code=${encodeURIComponent(zone)}`);
    const data = await response.json();
    populateCustomDropdown('regionContent', 'regionFilter', 'regionDisplay', data.regions || [], 'region_code', 'region_description', 'All Regions');
    document.getElementById('regionDisplay').querySelector('span').innerText = 'All Regions'; document.getElementById('regionFilter').value = '';
}

function resetFilters() {
    document.getElementById('startDate').value = ''; document.getElementById('endDate').value = '';
    document.getElementById('mainZoneFilter').value = ''; document.getElementById('mainZoneDisplay').querySelector('span').innerText = 'All Main Zones';
    document.getElementById('zoneFilter').value = ''; document.getElementById('zoneDisplay').querySelector('span').innerText = 'All Zones';
    document.getElementById('zoneContent').innerHTML = '<div class="option-item text-gray-500 italic">All Zones</div>';
    document.getElementById('regionFilter').value = ''; document.getElementById('regionDisplay').querySelector('span').innerText = 'All Regions';
    document.getElementById('regionContent').innerHTML = '<div class="option-item text-gray-500 italic">All Regions</div>';
    updateDateDisplay(); loadFilterOptions().then(loadReport);
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
            <td class="px-4 py-3 text-sm font-medium">${label}</td>
            <td class="px-4 py-3 text-sm text-center">${formatAmount(lncr)}</td>
            <td class="px-4 py-3 text-sm text-center">${formatAmount(vismin)}</td>
            <td class="px-4 py-3 text-sm text-center">${formatAmount(headOffice)}</td>
            <td class="px-4 py-3 text-sm text-center">${formatAmount(jewelry)}</td>
            <td class="px-4 py-3 text-sm text-center font-semibold">${formatAmount(total)}</td>
        </tr>
    `;
}

function renderSummaryTable(summary) {
    let html = '';
    html += `<tr class="category-header"><td colspan="6" class="px-4 py-3 text-sm">ML LOANS</td></tr>`;
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

    if (!rows || rows.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                    No running receivables found for the selected filters.
                </td>
            </tr>`;
        return;
    }

    // These keys match the $details array in your PHP file exactly
    tbody.innerHTML = rows.map(row => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 text-sm font-medium text-gray-900">${escapeHtml(row.reference_number)}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(row.borrower_name)}</td>
            <td class="px-4 py-3 text-sm text-gray-600">${escapeHtml(row.loan_type_label)}</td>
            <td class="px-4 py-3 text-sm text-gray-600">${escapeHtml(row.main_zone_display)}</td>
            <td class="px-4 py-3 text-sm text-gray-600">${escapeHtml(row.zone_display)}</td>
            <td class="px-4 py-3 text-sm text-gray-600">${escapeHtml(row.region_display)}</td>
            <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">${formatAmount(row.outstanding_balance)}</td>
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