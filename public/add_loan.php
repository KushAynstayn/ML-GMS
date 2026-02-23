<?php 
require_once __DIR__ . '/../includes/init.php';
include('../includes/header.php'); 
include_once '../includes/modals/addloan_errormodal.php'; 
include_once '../includes/modals/status_modal.php';
?>

<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.default.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<body class="h-screen overflow-hidden flex flex-col bg-gray-50">
    

    <div class="flex flex-1 overflow-hidden">
        <?php include('../includes/sidebar.php'); ?>

        <main class="flex-1 p-8 overflow-y-auto custom-scrollbar">
            <header class="mb-6">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Add <span class="text-red-600">Loan</span></h2>
    <p class="text-gray-500 font-medium mt-2 mb-8">Fill out the details below to register a new loan application.</p>
                <div class="flex gap-8 border-b border-gray-200">
                    <button onclick="switchTab('add_record', this)" class="tab-btn pb-2 font-semibold text-red-600 border-b-2 border-red-600 transition-all">Add new record</button>
                    <button onclick="switchTab('import_file', this)" class="tab-btn pb-2 font-semibold text-gray-500 hover:text-red-600 transition-all">Import file</button>
                    <button onclick="switchTab('import_payment', this)" class="tab-btn pb-2 font-semibold text-gray-500 hover:text-red-600 transition-all">Import payment</button>
                </div>
            </header>

            <div id="tab-content-area" class="mt-6">
                <?php include('../includes/tabs/add_record.php'); ?>
            </div>
        </main>
    </div>

    <div id="fileModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[110] p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 id="modalTitle" class="text-lg font-bold text-gray-800 truncate">File Preview</h3>
                <button onclick="document.getElementById('fileModal').classList.replace('flex', 'hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div id="modalBody" class="p-6 overflow-auto custom-scrollbar bg-gray-50">
                </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>

    <script src="../assets/js/main.js"></script>

    <script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    let autoCloseTimer;

    // --- Tab Management ---
    function switchTab(tabName, element) {
    const contentArea = document.getElementById('tab-content-area');    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('text-red-600', 'border-b-2', 'border-red-600');
        btn.classList.add('text-gray-500');
    });
    
    element.classList.add('text-red-600', 'border-b-2', 'border-red-600');
    element.classList.remove('text-gray-500');
    
    fetch(`../includes/tabs/${tabName}.php`)
        .then(response => {
            if (!response.ok) throw new Error('File not found');
            return response.text();
        })
        .then(html => {
            contentArea.innerHTML = html;
            
            if (tabName === 'add_record') {
                setTimeout(() => {
                    // 1. Re-initialize dropdowns and calculators
                    if (typeof initLoanCalculator === 'function') initLoanCalculator();
                    if (typeof initSearchableDropdowns === 'function') initSearchableDropdowns();

                    // 2. CRITICAL FIX: Re-bind the Submit event to the new form
                    const form = document.getElementById('loanForm');
                    if (form) {
                        // Ensure we don't have duplicate listeners
                        form.removeEventListener('submit', handleFormSubmit); 
                        form.addEventListener('submit', handleFormSubmit);
                    }
                }, 50);
            }
        })
        .catch(err => {
            contentArea.innerHTML = `<p class="text-red-500">Error loading tab: ${err.message}</p>`;
        });
    }

    // --- File Selection UI Logic ---
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'fileInput') {
            const file = e.target.files[0];
            if (file) {
                const fileNameDisplay = document.getElementById('fileNameDisplay');
                const titleDisplay = document.getElementById('uploadTitle');
                const selectBtn = document.getElementById('selectBtn');
                const cancelBtn = document.getElementById('cancelBtn');

                if (titleDisplay) titleDisplay.innerText = "File Selected";
                if (fileNameDisplay) {
                    fileNameDisplay.innerHTML = `Selected: <span class="text-red-600 font-bold">${file.name}</span>`;
                }

                if (selectBtn) {
                    selectBtn.innerText = "View File";
                    selectBtn.className = "bg-gray-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-gray-700 transition-colors"; 
                    selectBtn.onclick = function(event) {
                        event.preventDefault();
                        openFileModal(file);
                    };
                }
                if (cancelBtn) cancelBtn.classList.remove('hidden');
            }
        }
    });

    function resetFileInput() {
        const fileInput = document.getElementById('fileInput');
        if(!fileInput) return;
        fileInput.value = "";
        document.getElementById('uploadTitle').innerText = "Upload Loan Releases file";
        document.getElementById('fileNameDisplay').innerText = "Click to browse your computer";
        const selectBtn = document.getElementById('selectBtn');
        if (selectBtn) {
            selectBtn.innerText = "Select File";
            selectBtn.className = "bg-gray-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-gray-700 transition-colors";
            selectBtn.onclick = () => { document.getElementById('fileInput').click(); };
        }
        const cancelBtn = document.getElementById('cancelBtn');
        if (cancelBtn) cancelBtn.classList.add('hidden');
    }

    // --- Scanning Logic ---
    document.addEventListener('click', async function(e) {
        if (e.target && e.target.id === 'uploadBtn') {
            const fileInput = document.getElementById('fileInput');
            const loanType = document.getElementById('loanTypeSelect').value;
            if (!loanType || !fileInput.files.length) {
                showStatusModal('error', 'Missing Information', 'Please select a loan type and a file.', false);
                return;
            }
            const file = fileInput.files[0];
            file.type === "application/pdf" ? scanPDF(file, loanType) : scanExcel(file, loanType);
        }
    });

    async function scanPDF(file, searchTerm) {
        const reader = new FileReader();
        reader.onload = async function() {
            const typedarray = new Uint8Array(this.result);
            try {
                const pdf = await pdfjsLib.getDocument(typedarray).promise;
                let found = false;
                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const textContent = await page.getTextContent();
                    const pageText = textContent.items.map(item => item.str).join(" ");
                    if (pageText.toLowerCase().includes(searchTerm.toLowerCase())) { found = true; break; }
                }
                handleScanResult(found, searchTerm);
            } catch (err) { showStatusModal('error', 'Scan Error', 'Unable to read PDF content.', false); }
        };
        reader.readAsArrayBuffer(file);
    }

    function scanExcel(file, searchTerm) {
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const workbook = XLSX.read(new Uint8Array(e.target.result), {type: 'array'});
                let found = false;
                workbook.SheetNames.forEach(name => {
                    const sheet = XLSX.utils.sheet_to_json(workbook.Sheets[name], {header: 1});
                    if (sheet.flat().some(cell => cell && cell.toString().toLowerCase().includes(searchTerm.toLowerCase()))) found = true;
                });
                handleScanResult(found, searchTerm);
            } catch (err) { showStatusModal('error', 'Scan Error', 'Unable to read Excel content.', false); }
        };
        reader.readAsArrayBuffer(file);
    }

    function handleScanResult(found, searchTerm) {
        if (found) {
            showStatusModal('success', 'Success', 'File Uploaded Successfully', true);
            resetFileInput();
        } else {
            showStatusModal('error', 'Mismatch Detected', `Please make sure the selected loan type matches the file you uploaded.`, false);
        }
    }


    function openFileModal(file) {
        const modal = document.getElementById('fileModal');
        const modalBody = document.getElementById('modalBody');
        document.getElementById('modalTitle').innerText = file.name;
        modal.classList.replace('hidden', 'flex');

        if (file.type === "application/pdf") {
            modalBody.innerHTML = '<div id="pdf-viewer" class="flex flex-col items-center"></div>';
            const reader = new FileReader();
            reader.onload = function() {
                const typedarray = new Uint8Array(this.result);
                pdfjsLib.getDocument(typedarray).promise.then(pdf => {
                    const viewer = document.getElementById('pdf-viewer');
                    for (let i = 1; i <= pdf.numPages; i++) {
                        pdf.getPage(i).then(page => {
                            const canvas = document.createElement('canvas');
                            viewer.appendChild(canvas);
                            const viewport = page.getViewport({ scale: 1.5 });
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport });
                        });
                    }
                });
            };
            reader.readAsArrayBuffer(file);
        } else {
            const reader = new FileReader();
            reader.onload = (e) => {
                const workbook = XLSX.read(new Uint8Array(e.target.result), {type: 'array'});
                const sheet = workbook.Sheets[workbook.SheetNames[0]];
                
                // Convert to HTML and wrap in a clean container
                const htmlTable = XLSX.utils.sheet_to_html(sheet);
                modalBody.innerHTML = `
                    <div class="excel-preview-wrapper bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            ${htmlTable}
                        </div>
                    </div>`;
            };
            reader.readAsArrayBuffer(file);
        }
    }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        .pop-icon { animation: icon-pop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
        @keyframes icon-pop { 0% { transform: scale(0.5); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        
        /* Fixed Excel Preview Styles */
        .excel-preview-wrapper table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white;
            font-size: 0.875rem;
            color: #374151;
        }
        /* Header styling (First row) */
        .excel-preview-wrapper tr:first-child {
            background-color: #f8fafc;
            font-weight: 700;
            color: #1f2937;
            border-bottom: 2px solid #e2e8f0;
        }
        .excel-preview-wrapper td { 
            border: 1px solid #e5e7eb; 
            padding: 12px 16px; 
            white-space: nowrap;
        }
        /* Zebra stripes */
        .excel-preview-wrapper tr:nth-child(even) {
            background-color: #fcfcfd;
        }
        /* Hover effect */
        .excel-preview-wrapper tr:hover {
            background-color: #f3f4f6;
        }
    </style>
    
</body>
</html>