<?php
require_once __DIR__ . '/../../classes/database.php';
require_once __DIR__ . '/../../classes/MasterDataService.php';

use Cadc20239999\MlGms\MasterDataService;

$masterData = new MasterDataService();
$loanTypes = $masterData->getLoanTypes();
$regions = $masterData->getRegions();
$branches = $masterData->getBranches();
?>

<form id="loanForm" method="POST" class="bg-white p-8 rounded-xl shadow-sm w-full max-w-full mx-auto">
    <div class="mb-8">
        <h3 class="text-sm font-bold text-gray-700 mb-4 border-b pb-2">BASIC INFORMATION</h3>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-x-8 gap-y-4">
            <div class="col-span-2 md:col-span-6">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Type of Loan</label>
                <select id="loanType" name="loan_type_id" required class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:outline-none focus:ring-1 focus:ring-red-500 transition-all text-sm text-gray-700">
                    <option value="" class="text-gray-500">Select Type</option>
                    <?php foreach ($loanTypes as $type): ?>
                        <option value="<?= $type['loan_type_id'] ?>"><?= $type['loan_type_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="classificationDiv" class="col-span-2 md:col-span-6 hidden">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Classification</label>
                <select name="classification" class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:outline-none focus:ring-1 focus:ring-red-500 transition-all text-sm text-gray-700">
                    <option value="" class="text-gray-500">Select Classification</option>
                    <option value="Prenda">Prenda</option>
                    <option value="Pre-owned">Pre-owned</option>
                    <option value="Surplus">Surplus</option>
                </select>
            </div>

            <div id="motorTypeDiv" class="col-span-2 md:col-span-6 hidden">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Motorcycle Type</label>
                <select name="vehicle_type" class="w-full border border-gray-200 rounded-lg p-2 bg-white text-sm focus:outline-none text-gray-700">
                    <option value="" class="text-gray-500">Select Type</option>
                    <option value="2-WHEELS">2-WHEELS</option>
                    <option value="3-WHEELS">3-WHEELS</option>
                </select>
            </div>
            <div class="col-span-2 md:col-span-6">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Reference Number</label>
                <input type="text" 
                    id="ref_no" 
                    name="ref_no" 
                    required 
                    maxlength="11" 
                    autocomplete="off"
                    class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm transition-all">
                <p id="ref-error" class="text-red-500 text-[10px] mt-1 hidden font-bold italic"></p>
            </div>

            <div class="col-span-2 md:col-span-2">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">First Name</label>
                <input type="text" name="first_name" required class="uppercase w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
            <div class="col-span-2 md:col-span-2">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Middle Name</label>
                <input type="text" name="middle_name" class="uppercase w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
            <div class="col-span-2 md:col-span-2">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Last Name</label>
                <input type="text" name="last_name" required class="uppercase w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>

            <div class="col-span-2 md:col-span-2">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Contact Number</label>
                <input type="text" name="contact" class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>

            <div class="col-span-2 md:col-span-2">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Region</label>
                <select id="regionSelect" name="region_id" required class="w-full border border-gray-200 rounded-lg p-0 bg-white focus:outline-none text-sm text-gray-700">
                    <option value="" class="text-gray-500">Select Region</option>
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= $region['id'] ?>"><?= $region['region_description'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h3 class="text-sm font-bold text-gray-700 mb-4 border-b pb-2">LOAN COMPUTATION</h3>
        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Loan Amount (Principal)</label>
                <input type="text" name="principal" id="calcLoanAmount" placeholder="0.00" required class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Term (Months)</label>
                <input type="text" name="term" id="calcTerm" placeholder="e.g. 36" required class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Date Granted</label>
                <input type="date" name="date_granted" id="calcDateGranted" required class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm text-gray-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Maturity Date</label>
                <input type="text" id="resMaturity" readonly class="w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-gray-600 text-sm font-mono cursor-not-allowed">
                <input type="hidden" name="maturity_date" id="hiddenMaturity">
            </div>

            <div class="mt-2 hidden">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Dealer's Incentives (5%)</label>
                <input type="text" id="resIncentive" readonly class="w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-sm font-mono">
                <input type="hidden" name="incentive" id="hiddenIncentive">
            </div>
            <div class="mt-2 hidden">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Loan Amount with 5% off</label>
                <input type="text" id="resNetLoan" readonly class="w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-sm font-mono">
                <input type="hidden" name="net_proceeds" id="hiddenNetProceeds">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Interest Rate (AOR)</label>
                <input type="text" id="resAOR" readonly class="w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-sm font-mono">
                <input type="hidden" name="aor" id="hiddenAOR">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Monthly Amortization</label>
                <input type="text" id="resMonthly" readonly class="w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-red-600 font-bold text-sm font-mono">
                <input type="hidden" name="monthly_amortization" id="hiddenMonthly">
            </div>

            <input type="hidden" name="monthly_factor" id="hiddenMonthlyFactor">
            <input type="hidden" id="hiddenSecondaryMonthly" name="secondary_monthly">
            
            <div class="col-span-2 md:col-span-1 hidden">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">EIR</label>
                <input type="text" id="resEIR" readonly class="w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-sm font-mono">
                <input type="hidden" name="eir" id="hiddenEIR">
            </div>
        </div>
    </div>

    <input type="hidden" name="is_car_loan" id="is_car_loan_flag" value="0">
    <input type="hidden" name="loan_type_text" id="loan_type_text_flag" value="">

    <div id="vehicleFields" class="hidden mb-8">
        <h3 class="text-sm font-bold text-gray-700 mb-4 border-b pb-2">VEHICLE DETAILS</h3>
        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <div id="dateInstalledDiv">
                <label class="text-xs font-semibold text-gray-500 mb-1 block uppercase tracking-wider">Date Installed (If Device Installed)</label>
                <input type="date" id="dateInstalled" name="date_installed" class="w-full border border-gray-200 rounded-lg p-1.5 bg-white focus:outline-none text-sm text-gray-500">
            </div>
        </div>
    </div>

    <div class="mt-6 mb-4 p-4 bg-gray-50 rounded-lg border border-gray-100">
        <label class="flex items-start gap-3 cursor-pointer">
            <div class="flex items-center h-5">
                <input id="verifyCheckbox" type="checkbox" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500 cursor-pointer">
            </div>
            <div class="text-sm">
                <span class="font-medium text-gray-700">Verification & Accuracy</span>
                <p class="text-gray-500 text-xs">I hereby certify that all information entered has been verified and is true and correct. I understand that this will generate a permanent loan record and amortization schedule.</p>
            </div>
        </label>
    </div>

    <div class="flex justify-end">
        <button type="submit" id="submitBtn" disabled class="bg-gray-500 text-white px-8 py-2.5 rounded-lg font-bold transition-all text-sm cursor-not-allowed opacity-70">
            SAVE LOAN
        </button>
    </div>
</form>

<script>
document.getElementById('loanType').addEventListener('change', function() {
    const val = this.options[this.selectedIndex].text.toLowerCase();
    const classDiv = document.getElementById('classificationDiv');
    const motorDiv = document.getElementById('motorTypeDiv');
    const vehicleFields = document.getElementById('vehicleFields');

    classDiv.classList.add('hidden');
    motorDiv.classList.add('hidden');
    vehicleFields.classList.add('hidden');

    if (val.includes('car')) {
        classDiv.classList.remove('hidden');
    } else if (val.includes('motor')) {
        motorDiv.classList.remove('hidden');
        vehicleFields.classList.remove('hidden');
    }
});
</script>

<style>
.ts-wrapper.single .ts-control {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.375rem 0.5rem;
    font-size: 0.875rem;
    min-height: 38px;
    box-shadow: none;
}
.ts-wrapper.single.focus .ts-control {
    border-color: #ef4444;
    box-shadow: 0 0 0 1px #ef4444;
}
.ts-dropdown {
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    font-size: 0.875rem;
}
</style>