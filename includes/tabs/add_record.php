<div class="bg-white p-8 rounded-xl shadow-sm w-full">
    <div class="grid grid-cols-2 gap-x-8 gap-y-4">
        <div class="col-span-2">
            <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Type of Loan</label>
            <select id="loanType" class="w-full border border-gray-200 rounded-lg p-2 bg-gray-50 focus:outline-none focus:ring-1 focus:ring-red-500 transition-all text-sm">
                <option value="">Select Type</option>
                <option value="Car Loan">Car Loan</option>
                <option value="Motor Loan">Motor Loan</option>
                <option value="Home Loan">Home Loan</option>
                <option value="Salary Loan">Salary Loan</option>
                <option value="Personal Property Loan">Personal Property Loan</option>
                <option value="Real Estate Loan">Real Estate Loan</option>
            </select>
        </div>

        <div>
            <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Reference Number</label>
            <input type="text" class="w-full border border-gray-200 rounded-lg p-1.5 bg-gray-50 focus:outline-none text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Account Name</label>
            <input type="text" class="w-full border border-gray-200 rounded-lg p-1.5 bg-gray-50 focus:outline-none text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Contact Number</label>
            <input type="text" class="w-full border border-gray-200 rounded-lg p-1.5 bg-gray-50 focus:outline-none text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">PN Date / Date Granted</label>
            <input type="date" class="w-full border border-gray-200 rounded-lg p-1.5 bg-gray-50 focus:outline-none text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Loan Amount</label>
            <input type="text" class="w-full border border-gray-200 rounded-lg p-1.5 bg-gray-50 focus:outline-none text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Term</label>
            <input type="text" class="w-full border border-gray-200 rounded-lg p-1.5 bg-gray-50 focus:outline-none text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Amortization</label>
            <input type="text" class="w-full border border-gray-200 rounded-lg p-1.5 bg-gray-50 focus:outline-none text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Branch</label>
            <select class="w-full border border-gray-200 rounded-lg p-1.5 bg-gray-50 focus:outline-none text-sm">
                <option>Select Branch</option>
            </select>
        </div>
        <div class="col-span-2">
            <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Region</label>
            <select class="w-full border border-gray-200 rounded-lg p-1.5 bg-gray-50 focus:outline-none text-sm">
                <option>Select Region</option>
            </select>
        </div>

        <div id="vehicleFields" class="col-span-2 grid grid-cols-2 gap-x-8 gap-y-4 hidden">
            <div class="col-span-2 py-2">
                <div class="border-t border-gray-100"></div>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Classification</label>
                <select class="w-full border border-gray-200 rounded-lg p-1.5 bg-gray-50 focus:outline-none text-sm">
                    <option>Owned</option>
                    <option>Pre-Owned</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Date Installed</label>
                <input type="date" class="w-full border border-gray-200 rounded-lg p-1.5 bg-gray-50 focus:outline-none text-sm">
            </div>
            <div class="col-span-2">
                <label class="text-xs font-semibold text-gray-500 mb-2 block uppercase tracking-wider">Device Installed</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="device" value="YES" class="accent-red-600"> <span class="text-gray-700">YES</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="device" value="NO" class="accent-red-600"> <span class="text-gray-700">NO</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-10 flex justify-end">
        <button class="bg-red-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-red-700 transition-colors text-sm">
            CREATE LOAN
        </button>
    </div>
</div>

<script>
    const loanTypeSelect = document.getElementById('loanType');
    const vehicleFields = document.getElementById('vehicleFields');

    loanTypeSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        if (selectedValue === 'Car Loan' || selectedValue === 'Motor Loan') {
            vehicleFields.classList.remove('hidden');
        } else {
            vehicleFields.classList.add('hidden');
        }
    });
</script>