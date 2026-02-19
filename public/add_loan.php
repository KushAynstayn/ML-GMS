<?php include('../includes/header.php'); ?>

<body class="h-screen overflow-hidden flex flex-col bg-gray-50">

    <div class="flex flex-1 overflow-hidden">
        
        <?php include('../includes/sidebar.php'); ?>

        <main class="flex-1 p-8 overflow-y-auto custom-scrollbar">
            <header class="mb-6">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Add Loan</h2>
                
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

    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>

    <script>
    // Set PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

    // --- 0. ANTI-DRAG LOGIC ---
    window.addEventListener("dragover", function(e) {
        e.preventDefault();
    }, false);

    window.addEventListener("drop", function(e) {
        e.preventDefault();
    }, false);

    // 1. Switch Tab Function
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
            })
            .catch(err => {
                contentArea.innerHTML = `<p class="text-red-500">Error loading tab: ${err.message}</p>`;
            });
    }

    // 2. The Global Listener for the File Input
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'fileInput') {
            const file = e.target.files[0];
            if (file) {
                const fileNameDisplay = document.querySelector('.text-sm.text-gray-400.mb-6');
                const titleDisplay = document.querySelector('.font-bold.text-gray-700');
                const selectBtn = document.getElementById('selectBtn');
                const cancelBtn = document.getElementById('cancelBtn');

                if (titleDisplay) titleDisplay.innerText = "File Selected";
                if (fileNameDisplay) {
                    fileNameDisplay.innerHTML = `Selected: <span class="text-red-600 font-bold">${file.name}</span>`;
                }

                if (selectBtn) {
                    selectBtn.innerText = "View File";
                    selectBtn.classList.remove('bg-blue-600'); 
                    selectBtn.classList.add('bg-gray-600');    
                    
                    selectBtn.onclick = function(event) {
                        event.preventDefault();
                        openFileModal(file);
                    };
                }

                if (cancelBtn) {
                    cancelBtn.classList.remove('hidden');
                }
            }
        }
    });

    // 3. Reset the Selection
    function resetFileInput() {
        const fileInput = document.getElementById('fileInput');
        const selectBtn = document.getElementById('selectBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const fileNameDisplay = document.querySelector('.text-sm.text-gray-400.mb-6');
        const titleDisplay = document.querySelector('.font-bold.text-gray-700');

        if (fileInput) fileInput.value = "";

        if (titleDisplay) {
            const isPaymentTab = document.querySelector('button[onclick*="import_payment"]').classList.contains('text-red-600');
            titleDisplay.innerText = isPaymentTab ? "Upload Payment file" : "Upload Loan Releases file";
        }
        
        if (fileNameDisplay) fileNameDisplay.innerText = "Click to browse your computer";

        if (selectBtn) {
            selectBtn.innerText = "Select File";
            selectBtn.classList.remove('bg-blue-600');
            selectBtn.classList.add('bg-gray-600');
            selectBtn.onclick = function() {
                document.getElementById('fileInput').click();
            };
        }

        if (cancelBtn) {
            cancelBtn.classList.add('hidden');
        }
    }
    
    // 4. Modal Preview Function (Updated for Canvas PDF Rendering)
    function openFileModal(file) {
        const modal = document.getElementById('fileModal');
        const modalBody = document.getElementById('modalBody');
        const modalTitle = document.getElementById('modalTitle');
        
        modalTitle.innerText = file.name;
        modalBody.innerHTML = '<div class="text-center p-10"><p class="text-gray-500">Loading preview...</p></div>'; 
        modal.classList.replace('hidden', 'flex');

        // --- Handle PDF ---
        if (file.type === "application/pdf") {
            modalBody.innerHTML = '<div id="pdf-viewer" class="flex flex-col items-center gap-4 py-4"></div>';
            const reader = new FileReader();
            
            reader.onload = function() {
                const typedarray = new Uint8Array(this.result);
                
                pdfjsLib.getDocument(typedarray).promise.then(pdf => {
                    const viewer = document.getElementById('pdf-viewer');
                    viewer.innerHTML = ''; // Clear loading text
                    
                    // Render all pages
                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        pdf.getPage(pageNum).then(page => {
                            const scale = 1.5;
                            const viewport = page.getViewport({ scale });
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            canvas.className = "shadow-lg mb-4 max-w-full h-auto";
                            
                            viewer.appendChild(canvas);

                            const renderContext = {
                                canvasContext: context,
                                viewport: viewport
                            };
                            page.render(renderContext);
                        });
                    }
                });
            };
            reader.readAsArrayBuffer(file);
        } 
        // --- Handle Excel ---
        else if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {type: 'array'});
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];
                const htmlTable = XLSX.utils.sheet_to_html(worksheet, { 
                    editable: false,
                    header: '' 
                });

                modalBody.innerHTML = `
                    <div class="overflow-x-auto p-2 bg-white">
                        <div class="excel-preview-container">
                            ${htmlTable}
                        </div>
                    </div>`;
                
                const table = modalBody.querySelector('table');
                if(table) {
                    table.className = "min-w-full divide-y divide-gray-200 text-sm border-collapse border border-gray-200";
                    table.querySelectorAll('td').forEach(td => {
                        td.className = "px-4 py-2 border border-gray-200 whitespace-nowrap text-gray-600";
                    });
                }
            };
            reader.readAsArrayBuffer(file);
        } else {
            modalBody.innerHTML = `
                <div class="text-center p-10">
                    <p class="text-gray-600 mb-2 font-semibold italic text-lg">No preview available for this file type.</p>
                    <p class="text-sm text-gray-400">File: ${file.name}</p>
                </div>`;
        }
    }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        .excel-preview-container table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        .excel-preview-container tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .excel-preview-container tr:hover {
            background-color: #f3f4f6;
        }
        /* Custom PDF Viewer Background */
        #modalBody { background-color: #525659; } 
    </style>
</body>
</html>