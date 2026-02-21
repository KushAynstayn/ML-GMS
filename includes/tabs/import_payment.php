<div class="w-full">
    <div class="mb-6">
        <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Type of Loan</label>
        <select id="loanTypeSelect" class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:outline-none focus:ring-1 focus:ring-red-500 transition-all text-sm">
            <option value="">Select Type</option>
            <option value="Car Loan">Car Loan</option>
            <option value="Motor Loan">Motor Loan</option>
            <option value="Home Loan">Home Loan</option>
            <option value="Salary Loan">Salary Loan</option>
            <option value="Personal Property Loan">Personal Property Loan</option>
            <option value="Real Estate Loan">Real Estate Loan</option>
        </select>
    </div>

    <div class="bg-white p-12 rounded-xl shadow-sm border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-center">
        <div class="bg-yellow-100 p-4 rounded-lg mb-4">
            <svg class="w-10 h-10 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
            </svg>
        </div>
        
        <p id="uploadTitle" class="font-bold text-gray-700">Upload Payment file</p>
        <p id="fileNameDisplay" class="text-sm text-gray-400 mb-6">Click to browse your computer</p>
        
        <input 
            type="file" 
            id="fileInput" 
            accept=".pdf, .xls, .xlsx" 
            style="display: none;"
        >

        <div class="flex gap-2">
            <button 
                id="cancelBtn"
                onclick="resetFileInput()" 
                class="hidden bg-red-100 text-red-600 px-6 py-2 rounded-lg font-bold hover:bg-red-200 transition-colors"
            >
                Cancel
            </button>

            <button 
                id="selectBtn"
                onclick="document.getElementById('fileInput').click()" 
                class="bg-gray-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-gray-700 transition-colors"
            >
                Select File
            </button>
        </div>
    </div>

    <div class="mt-10 flex justify-end">
        <button id="uploadBtn" class="bg-red-600 text-white px-8 py-2 rounded-lg font-bold hover:bg-red-700 transition-colors text-sm uppercase tracking-wide">
            UPLOAD
        </button>
    </div>

    <div id="fileModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[110] p-4">
        <div class="bg-white rounded-xl w-full max-w-4xl max-h-[90vh] flex flex-col shadow-2xl">
            <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-xl">
                <h3 id="modalTitle" class="font-bold text-gray-700 text-lg">File Preview</h3>
                <button onclick="document.getElementById('fileModal').classList.replace('flex', 'hidden')" class="text-gray-400 hover:text-gray-700 text-3xl font-light">&times;</button>
            </div>
            <div id="modalBody" class="flex-1 overflow-auto p-4 bg-gray-200 custom-scrollbar"></div>
        </div>
    </div>
</div>