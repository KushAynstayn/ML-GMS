<div class="bg-white p-8 rounded-xl shadow-sm w-full max-w-full mx-auto">
    <div class="mb-8">
        <h3 class="text-sm font-bold text-gray-700 mb-4 border-b pb-2">BASIC INFORMATION</h3>
        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <div class="col-span-2">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Type of Loan</label>
                <select id="loanType" class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:outline-none focus:ring-1 focus:ring-red-500 transition-all text-sm">
                    <option value="">Select Type</option>
                    <option value="Car Loan">Car Loan</option>
                    <option value="Motor Loan">Motor Loan</option>
                    <option value="Home Loan">Home Loan</option>
                    <option value="Salary Loan">Salary Loan</option>
                    <option value="Personal Property Loan">Personal Property Loan</option>
                    <option value="Real Estate Loan">Real Estate Loan</option>
                </select>
            </div>
            <div class="col-span-2">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Reference Number</label>
                <input type="text" class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">First Name</label>
                <input type="text" class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Middle Name</label>
                <input type="text" class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Last Name</label>
                <input type="text" class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Contact Number</label>
                <input type="text" class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Region</label>
                <select class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
                    <option>Select Region</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Branch</label>
                <select class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
                    <option>Select Branch</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h3 class="text-sm font-bold text-gray-700 mb-4 border-b pb-2">LOAN COMPUTATION</h3>
        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Loan Amount (Principal)</label>
                <input type="text" id="calcLoanAmount" placeholder="0.00" class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Term (Months)</label>
                <input type="text" id="calcTerm" placeholder="e.g. 36" class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Date Granted</label>
                <input type="date" id="calcDateGranted" class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Maturity Date</label>
                <input type="text" id="resMaturity" readonly class="w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-gray-600 text-sm font-mono cursor-not-allowed">
            </div>

            <div class="mt-2">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Dealer's Incentives (5%)</label>
                <input type="text" id="resIncentive" readonly class="w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-sm font-mono">
            </div>
            <div class="mt-2">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Loan Amount with 5% off</label>
                <input type="text" id="resNetLoan" readonly class="w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-sm font-mono">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Interest Rate (AOR)</label>
                <input type="text" id="resAOR" readonly class="w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-sm font-mono">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Monthly Amortization</label>
                <input type="text" id="resMonthly" readonly class="w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-red-600 font-bold text-sm font-mono">
            </div>
            <div class="col-span-2 md:col-span-1">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">EIR</label>
                <input type="text" id="resEIR" readonly class="w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-sm font-mono">
            </div>
        </div>
    </div>

    <div id="vehicleFields" class="hidden mb-8">
        <h3 class="text-sm font-bold text-gray-700 mb-4 border-b pb-2">VEHICLE DETAILS</h3>
        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Classification</label>
                <select class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
                    <option>Owned</option>
                    <option>Pre-Owned</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-2 block uppercase tracking-wider">Device Installed</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="device" value="YES" class="device-radio accent-red-600"> <span class="text-gray-700">YES</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="device" value="NO" class="device-radio accent-red-600" checked> <span class="text-gray-700">NO</span>
                    </label>
                </div>
            </div>
            <div id="dateInstalledDiv" class="hidden">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Date Installed</label>
                <input type="date" class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
        </div>
    </div>

    <div class="mt-10 flex justify-end">
        <button class="bg-red-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-red-700 transition-colors text-sm">
            CREATE LOAN
        </button>
    </div>
</div>

<script src="../assets/js/loan-calculator.js"></script>