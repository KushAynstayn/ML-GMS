/**
 * Global function to initialize or re-initialize listeners
 * This is crucial because switchTab() replaces the HTML content
 */
function initLoanCalculator() {
    const loanTypeSelect = document.getElementById('loanType');
    const vehicleFields = document.getElementById('vehicleFields');
    const deviceRadios = document.querySelectorAll('.device-radio');
    const dateInstalledDiv = document.getElementById('dateInstalledDiv');

    const loanAmtInp = document.getElementById('calcLoanAmount');
    const termInp = document.getElementById('calcTerm');
    const dateGrantedInp = document.getElementById('calcDateGranted');
    
    // ✅ Additional elements for Verification
    const verifyCheckbox = document.getElementById('verifyCheckbox');
    const submitBtn = document.getElementById('submitBtn');

    // Stop if form not in DOM yet
    if (!loanTypeSelect || !vehicleFields) return;

    // =============================
    // 1. TOGGLE VEHICLE SECTION
    // =============================
    function toggleVehicleSection() {
        const selectedText = loanTypeSelect.options[loanTypeSelect.selectedIndex].text.trim();
        
        // We also want to send a flag to PHP so it knows whether to insert into car_loans
        let isCarLoanInput = document.getElementById('is_car_loan_flag');
        if (!isCarLoanInput) {
            isCarLoanInput = document.createElement('input');
            isCarLoanInput.type = 'hidden';
            isCarLoanInput.name = 'is_car_loan';
            isCarLoanInput.id = 'is_car_loan_flag';
            loanTypeSelect.form.appendChild(isCarLoanInput);
        }

        if (selectedText === 'Car Loan' || selectedText === 'Motor Loan') {
            vehicleFields.classList.remove('hidden');
            isCarLoanInput.value = '1';
        } else {
            vehicleFields.classList.add('hidden');
            isCarLoanInput.value = '0';

            if (dateInstalledDiv) dateInstalledDiv.classList.add('hidden');

            const noRadio = document.querySelector('input[name="device_installed"][value="NO"]');
            if (noRadio) noRadio.checked = true;
        }
    }

    // Load branches immediately
    loadAllBranches();

    loanTypeSelect.removeEventListener('change', toggleVehicleSection);
    loanTypeSelect.addEventListener('change', function () {
        toggleVehicleSection();
        calculateLoan();
    });

    // =============================
    // 2. DEVICE RADIO TOGGLE
    // =============================
    deviceRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'YES') {
                dateInstalledDiv.classList.remove('hidden');
            } else {
                dateInstalledDiv.classList.add('hidden');
            }
        });
    });

    // =============================
    // 3. CALCULATION LISTENERS
    // =============================
    [loanAmtInp, termInp, dateGrantedInp].forEach(el => {
        if (!el) return;

        el.removeEventListener('input', calculateLoan);
        el.addEventListener('input', calculateLoan);
    });

    // =============================
    // 4. VERIFICATION CHECKBOX LOGIC WITH ERROR TRAPPING
    // =============================
    if (verifyCheckbox && submitBtn) {
        verifyCheckbox.addEventListener('change', function() {
            if (this.checked) {
                const form = document.getElementById('loanForm');
                const requiredFields = form.querySelectorAll('[required]');
                let missingFields = [];

                requiredFields.forEach(field => {
                    // Only validate fields that are currently VISIBLE to the user
                    if (!field.value.trim() && field.offsetParent !== null) {
                        const label = field.closest('div').querySelector('label');
                        const labelName = label ? label.innerText.replace(':', '').trim() : field.name;
                        missingFields.push(labelName);
                        
                        field.classList.add('border-red-500', 'bg-red-50');
                    } else {
                        field.classList.remove('border-red-500', 'bg-red-50');
                    }
                });

                if (missingFields.length > 0) {
                    this.checked = false; 
                    showErrorModal(missingFields); // Calls your new includes/modals/error_modal.php
                    
                    submitBtn.disabled = true;
                    submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed', 'opacity-70');
                    submitBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                } else {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed', 'opacity-70');
                    submitBtn.classList.add('bg-red-600', 'hover:bg-red-700', 'cursor-pointer');
                }
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed', 'opacity-70');
                submitBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
            }
        });
    }

    // =============================
    // 5. FORCE INITIAL STATE
    // =============================
    toggleVehicleSection();
    calculateLoan(); 
}

// Keep the global math functions outside of init so calculateLoan is always available
function getEIRRate(nper, pmt, pv, guess = 0.01) {
    let rate = guess;
    const tolerance = 0.0000001;
    const maxIterations = 100;
    for (let i = 0; i < maxIterations; i++) {
        let powNMinus1 = Math.pow(1 + rate, -nper);
        let f = pv + pmt * (1 - powNMinus1) / rate;
        let df = pmt * (nper * powNMinus1 / (rate * (1 + rate)) - (1 - powNMinus1) / Math.pow(rate, 2));
        let newRate = rate - (f / df);
        if (Math.abs(newRate - rate) < tolerance) return newRate;
        rate = newRate;
    }
    return rate;
}

function calculateLoan() {
    const loanAmtInp = document.getElementById('calcLoanAmount');
    const termInp = document.getElementById('calcTerm');
    const dateGrantedInp = document.getElementById('calcDateGranted');
    const loanTypeSelect = document.getElementById('loanType');

    if (!loanAmtInp || !termInp) return;

    const loanAmount = parseFloat(loanAmtInp.value) || 0;
    const term = parseInt(termInp.value) || 0;
    const dateGranted = dateGrantedInp.value;

    // Update Hidden Car Loan Flag
    let carLoanFlag = document.getElementById('is_car_loan');
    if(!carLoanFlag) {
        carLoanFlag = document.createElement('input');
        carLoanFlag.type = 'hidden';
        carLoanFlag.id = 'is_car_loan';
        carLoanFlag.name = 'is_car_loan';
        const form = document.getElementById('loanForm');
        if (form) form.appendChild(carLoanFlag);
    }
    const selectedText = loanTypeSelect.options[loanTypeSelect.selectedIndex].text.trim();
    carLoanFlag.value = (selectedText === 'Car Loan') ? '1' : '0';

    const resMaturity = document.getElementById('resMaturity');
    const resIncentive = document.getElementById('resIncentive');
    const resNetLoan = document.getElementById('resNetLoan');
    const resAOR = document.getElementById('resAOR');
    const resMonthly = document.getElementById('resMonthly');
    const resEIR = document.getElementById('resEIR');

    // 1. Calculate Maturity Date
    if (dateGranted && term > 0) {
        let d = new Date(dateGranted);
        d.setMonth(d.getMonth() + term);
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const yyyy = d.getFullYear();
        resMaturity.value = `${mm}/${dd}/${yyyy}`;
        document.getElementById('hiddenMaturity').value = `${yyyy}-${mm}-${dd}`;
    } else {
        if(resMaturity) resMaturity.value = "";
    }

    // 2. Calculations
    if (loanAmount > 0 && term > 0) {
        const incentive = loanAmount * 0.05;
        const netLoan = loanAmount - incentive;
        const aorValue = 0.02; 
        const aorDisplay = (term * aorValue) * 100;
        const monthly = (((netLoan * aorValue) * term) + netLoan) / term;
        const monthlyRate = getEIRRate(term, -monthly, netLoan);
        const eirPercentage = monthlyRate * 100;

        resIncentive.value = incentive.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        resNetLoan.value = netLoan.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        resAOR.value = aorDisplay.toFixed(2) + "%";
        resMonthly.value = monthly.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        resEIR.value = eirPercentage.toFixed(6) + "%";

        document.getElementById('hiddenIncentive').value = incentive.toFixed(2);
        document.getElementById('hiddenNetProceeds').value = netLoan.toFixed(2);
        document.getElementById('hiddenAOR').value = (aorDisplay / 100).toFixed(4);
        document.getElementById('hiddenMonthly').value = monthly.toFixed(2);
        document.getElementById('hiddenEIR').value = eirPercentage.toFixed(6);
    }
}

// Initialize on first load
document.addEventListener('DOMContentLoaded', initLoanCalculator);

// Handle Region/Branch changes (delegated listener to handle dynamic tab loading)
//document.addEventListener('change', function(e) {
//    if (e.target && e.target.name === 'region_id') {
//        const regionId = e.target.value;
//        const branchSelect = document.querySelector('[name="branch_id"]');
//        if (!branchSelect) return;

//        branchSelect.innerHTML = '<option>Loading...</option>';
//        fetch(`api/get_branches.php?region_id=${regionId}`)
//            .then(res => res.json())
//            .then(data => {
//                branchSelect.innerHTML = '<option value="">Select Branch</option>';
//                data.forEach(branch => {
//                    let opt = document.createElement('option');
//                    opt.value = branch.branch_id;
//                    opt.textContent = branch.branch_name;
//                    branchSelect.appendChild(opt);
//                });
//            });
//    }
//});

function loadAllBranches() {
    const branchSelect = document.getElementById('branchSelect');
    if (!branchSelect) return;

    // Optional: Only fetch if the dropdown is empty (prevents redundant calls)
    if (branchSelect.options.length > 1) return; 

    branchSelect.innerHTML = '<option>Loading branches...</option>';

    fetch('api/get_branches.php')
        .then(res => res.json())
        .then(data => {
            branchSelect.innerHTML = '<option value="">Select Branch</option>';
            data.forEach(branch => {
                let opt = document.createElement('option');
                opt.value = branch.branch_id;
                opt.textContent = branch.branch_name;
                branchSelect.appendChild(opt);
            });
        })
        .catch(err => {
            console.error("Error loading branches:", err);
            branchSelect.innerHTML = '<option value="">Error loading data</option>';
        });
}

function showErrorModal(fields) {
    const modal = document.getElementById('errorModal');
    const list = document.getElementById('errorList');
    
    list.innerHTML = ''; // Clear previous errors
    
    fields.forEach(field => {
        const li = document.createElement('li');
        li.className = "py-0.5";
        li.textContent = field;
        list.appendChild(li);
    });
    
    modal.classList.remove('hidden');
}

function closeErrorModal() {
    document.getElementById('errorModal').classList.add('hidden');
}


document.addEventListener('submit', function(e) {
    if (e.target && e.target.id === 'loanForm') {
        e.preventDefault();
        
        const statusModal = document.getElementById('statusModal');
        const modalLoading = document.getElementById('modalLoading');
        const modalSuccess = document.getElementById('modalSuccess');
        const submitBtn = document.getElementById('submitBtn');

        // 1. Show Loading State
        statusModal.classList.remove('hidden');
        modalLoading.classList.remove('hidden');
        modalSuccess.classList.add('hidden');
        
        // Disable button to prevent double-clicks
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

        const formData = new FormData(e.target);

        // 2. Post to PHP API
        fetch('../api/save_loan.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // 3. Switch to Success State
                modalLoading.classList.add('hidden');
                modalSuccess.classList.remove('hidden');
            } else {
                // Handle database/server errors
                statusModal.classList.add('hidden');
                alert("Database Error: " + data.message);
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        })
        .catch(err => {
            statusModal.classList.add('hidden');
            console.error("System Error:", err);
            alert("A network error occurred. Please try again.");
            submitBtn.disabled = false;
        });
    }
});