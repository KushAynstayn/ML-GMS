<div class="w-full">
    <div class="mb-6">
        <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Type of Loan</label>
        <select class="w-full border border-gray-200 rounded-lg p-2 bg-gray-50 focus:outline-none focus:ring-1 focus:ring-red-500 transition-all text-sm">
            <option value="">Select Type</option>
            <option value="Car Loan">Car Loan</option>
            <option value="Motor Loan">Motor Loan</option>
            <option value="Home Loan">Home Loan</option>
            <option value="Salary Loan">Salary Loan</option>
            <option value="Personal Property Loan">Personal Property Loan</option>
            <option value="Real Estate Loan">Real Estate Loan</option>
        </select>
    </div>

    <div class="bg-white p-12 rounded-xl shadow-sm border-2 border-dashed border-gray-200 flex flex-col items-center justify-center">
        <div class="bg-yellow-100 p-4 rounded-lg mb-4">
            <svg class="w-10 h-10 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
            </svg>
        </div>
        
        <p class="font-bold text-gray-700">Upload Payment file</p>
        <p class="text-sm text-gray-400 mb-6">Drag & drop your file here or click to browse</p>
        
        <button class="bg-gray-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-gray-700 transition-colors">
            Select File
        </button>
    </div>

    <div class="mt-6 flex justify-end">
        <button class="bg-red-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-red-700 transition-colors">
            Upload Payment
        </button>
    </div>
</div>
