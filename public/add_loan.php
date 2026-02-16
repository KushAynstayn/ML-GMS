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

    <script>
    function switchTab(tabName, element) {
        const contentArea = document.getElementById('tab-content-area');
        
        // 1. Update UI: Remove active styles from all buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('text-red-600', 'border-b-2', 'border-red-600');
            btn.classList.add('text-gray-500');
        });

        // 2. Add active styles to clicked button
        element.classList.add('text-red-600', 'border-b-2', 'border-red-600');
        element.classList.remove('text-gray-500');

        // 3. Fetch the content from the folder structure
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
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    </style>
</body>
</html>