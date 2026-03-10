/**
 * Global function to initialize or re-initialize listeners
 * This is crucial because switchTab() replaces the HTML content
 */

// Ensure this is at the top of main.js
function initSearchableDropdowns() {
    const regionSelect = document.querySelector('#regionSelect');
    const branchSelect = document.querySelector('#branchSelect');

    if (regionSelect) {
        if (regionSelect.tomselect) regionSelect.tomselect.destroy();
        new TomSelect(regionSelect, {
            maxOptions: 100,
            create: false,
            placeholder: "Select Region...",
        });
    }

    if (branchSelect) {
        if (branchSelect.tomselect) branchSelect.tomselect.destroy();
        new TomSelect(branchSelect, {
            maxOptions: 500,
            create: false,
            placeholder: "Select Branch...",
        });
    }
}


// Initialize on page load
document.addEventListener('DOMContentLoaded', initSearchableDropdowns);

function initLoanCalculator() {
    const loanTypeSelect = document.getElementById('loanType');
    const classDiv = document.getElementById('classificationDiv');
    const classSelect = document.getElementById('classificationSelect');

    const motorDiv = document.getElementById('motorTypeDiv');
    const motorSelect = document.getElementById('motorTypeSelect');

    function toggleClassificationFields() {
        if (!loanTypeSelect) return;

        const val = loanTypeSelect.options[loanTypeSelect.selectedIndex]?.text.toLowerCase() || "";

        if (classDiv) classDiv.classList.add('hidden');
        if (motorDiv) motorDiv.classList.add('hidden');

        if (classSelect) classSelect.required = false;
        if (motorSelect) motorSelect.required = false;

        if (val.includes('car')) {
            if (classDiv) classDiv.classList.remove('hidden');
            if (classSelect) classSelect.required = true;
        }

        if (val.includes('motor')) {
            if (motorDiv) motorDiv.classList.remove('hidden');
            if (motorSelect) motorSelect.required = true;
        }
    }

    loanTypeSelect.addEventListener('change', toggleClassificationFields);
    toggleClassificationFields();
    const vehicleFields = document.getElementById('vehicleFields');

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
        const selectedOption = loanTypeSelect.options[loanTypeSelect.selectedIndex];
        if (!selectedOption) return; // Safety check

        const selectedText = selectedOption.text.trim().toUpperCase();
        const motorTypeDiv = document.getElementById('motorTypeDiv');
        const carLoanFlag = document.getElementById('is_car_loan_flag');
        const loanTypeTextFlag = document.getElementById('loan_type_text_flag');

        // 1. Safe assignment for Loan Type Text
        if (loanTypeTextFlag) {
            loanTypeTextFlag.value = selectedText;
        } else {
            console.error("loan_type_text_flag not found in DOM");
        }

        if (selectedText === 'CAR LOAN' || selectedText === 'MOTOR LOAN') {
            vehicleFields.classList.remove('hidden');
            
            // 2. Safe assignment for Car Loan Flag
            if (carLoanFlag) carLoanFlag.value = '1';

            if (selectedText === 'MOTOR LOAN') {
                if (motorTypeDiv) motorTypeDiv.classList.remove('hidden');
            } else {
                if (motorTypeDiv) motorTypeDiv.classList.add('hidden');
            }
        } else {
            vehicleFields.classList.add('hidden');
            if (carLoanFlag) carLoanFlag.value = '0';
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

    const tol = 1e-10;
    const maxIter = 100;
    let rate = guess;

    for (let i = 0; i < maxIter; i++) {

        let f = pv;
        let df = 0;

        for (let t = 1; t <= nper; t++) {
            f += pmt / Math.pow(1 + rate, t);
            df += -t * pmt / Math.pow(1 + rate, t + 1);
        }

        let newRate = rate - f / df;

        if (Math.abs(newRate - rate) < tol) {
            return newRate;
        }

        rate = newRate;
    }

    return rate;
}


const loanRates = {

        "PRE-OWNED": {
            factor: 1.50,
            aor: {12:18,24:36,36:54}
        },

        "PRENDA": {
            factor: 1.75,
            aor: {12:21,24:42,36:63}
        },

        "SURPLUS": {
            factor: 2.75,
            aor: {12:33,24:66,36:99}
        },

        "2-WHEELS": {
            factor: 1.75,
            aor: {12:21,24:42,36:63}
        },

        "3-WHEELS": {
            factor: 2.00,
            aor: {12:24,24:48,36:72}
        }

    };



function calculateLoan() {

    const loanAmtInp = document.getElementById('calcLoanAmount');
    const termInp = document.getElementById('calcTerm');
    const dateGrantedInp = document.getElementById('calcDateGranted');
    const dateInstalledInp = document.getElementById('dateInstalled'); // GMS trigger
    if (dateInstalledInp) {
        dateInstalledInp.addEventListener('change', calculateLoan);
        // Also trigger on 'input' for better compatibility with some datepickers
        dateInstalledInp.addEventListener('input', calculateLoan); 
    }

    if (!loanAmtInp || !termInp) return;

    const principal = parseFloat(loanAmtInp.value) || 0;
    const term = parseInt(termInp.value) || 0;
    const dateGranted = dateGrantedInp?.value || "";

    const resMaturity = document.getElementById('resMaturity');
    const resMonthly = document.getElementById('resMonthly');
    const resAOR = document.getElementById('resAOR');
    const resEIR = document.getElementById('resEIR');

    const hiddenIncentive = document.getElementById('hiddenIncentive');
    const hiddenNetProceeds = document.getElementById('hiddenNetProceeds');
    const hiddenMonthly = document.getElementById('hiddenMonthly');
    const hiddenSecondaryMonthly = document.getElementById('hiddenSecondaryMonthly');
    const hiddenEIR = document.getElementById('hiddenEIR');
    const hiddenMaturity = document.getElementById('hiddenMaturity');
    const hiddenAOR = document.getElementById('hiddenAOR');

    const classificationSelect = document.querySelector('[name="classification"]');
    const vehicleTypeSelect = document.querySelector('[name="vehicle_type"]');

    let selectedClass = "";

    if (classificationSelect) {
    classificationSelect.addEventListener("change", calculateLoan);
    }

    if (vehicleTypeSelect) {
        vehicleTypeSelect.addEventListener("change", calculateLoan);
    }

    if (classificationSelect && classificationSelect.value) {
        selectedClass = classificationSelect.value.toUpperCase();
    }

    if (vehicleTypeSelect && vehicleTypeSelect.value) {
        selectedClass = vehicleTypeSelect.value.toUpperCase();
    }

    if (!loanRates[selectedClass]) return;

    const monthlyFactor = loanRates[selectedClass].factor;
    const monthlyRate = monthlyFactor / 100;

    const aorDisplay = loanRates[selectedClass].aor[term] || 0;

    if (principal <= 0 || term <= 0) return;

    // ===============================
    // 1️⃣ MATURITY DATE
    // ===============================

    if (dateGranted && term > 0) {
        let d = new Date(dateGranted);
        d.setMonth(d.getMonth() + term);

        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const yyyy = d.getFullYear();

        if (resMaturity) resMaturity.value = `${mm}/${dd}/${yyyy}`;
        if (hiddenMaturity) hiddenMaturity.value = `${yyyy}-${mm}-${dd}`;
    } else {
        if (resMaturity) resMaturity.value = "";
    }

    // =====================================================
    // 2️⃣ PRIMARY LEDGER (AMORTIZED - REDUCING BALANCE)
    // =====================================================

   

    // Proper amortized monthly payment formula
    const primaryMonthlyPayment = Number(
    (
        (((principal * monthlyRate) * term) + principal) / term
    ).toFixed(2)
    );

    let primaryBalance = Number(principal.toFixed(2));
    const primarySchedule = [];

    for (let i = 1; i <= term; i++) {

        let interest = Number((primaryBalance * monthlyRate).toFixed(2));
        let principalPayment = Number(
            (primaryMonthlyPayment - interest).toFixed(2)
        );

        // Final month adjustment
        if (i === term || principalPayment >= primaryBalance) {
            principalPayment = primaryBalance;
            interest = Number((primaryMonthlyPayment - principalPayment).toFixed(2));
            if (interest < 0) interest = 0;
        }

        let endingBalance = Number(
            (primaryBalance - principalPayment).toFixed(2)
        );

        if (endingBalance < 0) endingBalance = 0;

        primarySchedule.push({
            payment_number: i,
            principal: principalPayment,
            interest: interest,
            total_payment: primaryMonthlyPayment,
            ending_balance: endingBalance,
            status: 'UNPAID',
            due_date: getDueDate(dateGranted, i)
        });

        primaryBalance = endingBalance;
    }

    // Display Primary Results
    if (resMonthly) resMonthly.value = primaryMonthlyPayment.toFixed(2);
    if (hiddenMonthly) hiddenMonthly.value = primaryMonthlyPayment.toFixed(2);

    if (resAOR) resAOR.value = aorDisplay + "%";
    if (hiddenAOR) hiddenAOR.value = aorDisplay;

    const hiddenMonthlyFactor = document.getElementById("hiddenMonthlyFactor");
    if(hiddenMonthlyFactor){
        hiddenMonthlyFactor.value = monthlyFactor;
    }

    const computedEIRPrimary = getEIRRate(term, -primaryMonthlyPayment, principal);
    const eirPercentagePrimary = computedEIRPrimary * 100;

    if (resEIR) resEIR.value = eirPercentagePrimary.toFixed(6) + "%";
    if (hiddenEIR) hiddenEIR.value = eirPercentagePrimary.toFixed(6);

    renderAmortizationTable(primarySchedule);

    // =====================================================
    // 3️⃣ SECONDARY LEDGER (ONLY IF GMS)
    // =====================================================

    const isGMS = dateInstalledInp && dateInstalledInp.value.trim() !== "";
    console.log("Is GMS active?", isGMS); // Debug line

    if (isGMS) {

        const incentive = Number((principal * 0.05).toFixed(2));
        const netLoan = Number((principal - incentive).toFixed(2));

        const secondaryMonthlyPayment = Number(
        (
            (((netLoan * monthlyRate) * term) + netLoan) / term
        ).toFixed(2)
        );
        console.log("Calculated Secondary Monthly:", secondaryMonthlyPayment);

        if (hiddenSecondaryMonthly) {
            hiddenSecondaryMonthly.value = secondaryMonthlyPayment.toFixed(2);
        }

        let secondaryBalance = netLoan;
        const secondarySchedule = [];

        for (let i = 1; i <= term; i++) {

            let interest = Number((secondaryBalance * monthlyRate).toFixed(2));

            let principalPayment = Number(
                (secondaryMonthlyPayment - interest).toFixed(2)
            );

            // Final month adjustment
            if (i === term || principalPayment >= secondaryBalance) {
                principalPayment = secondaryBalance;
                interest = Number((secondaryMonthlyPayment - principalPayment).toFixed(2));
                if (interest < 0) interest = 0;
            }

            let endingBalance = Number(
                (secondaryBalance - principalPayment).toFixed(2)
            );

            if (endingBalance < 0) endingBalance = 0;

            secondarySchedule.push({
                payment_number: i,
                principal: principalPayment,
                interest: interest,
                total_payment: secondaryMonthlyPayment,
                ending_balance: endingBalance,
                status: 'UNPAID',
                due_date: getDueDate(dateGranted, i)
            });

            secondaryBalance = endingBalance;
        }

        if (hiddenIncentive) hiddenIncentive.value = incentive.toFixed(2);
        if (hiddenNetProceeds) hiddenNetProceeds.value = netLoan.toFixed(2);
        if (hiddenSecondaryMonthly) hiddenSecondaryMonthly.value = secondaryMonthlyPayment.toFixed(2);

        // 🔹 Store secondarySchedule when saving record
        window.secondaryLedger = secondarySchedule;

    } else {

        if (hiddenIncentive) hiddenIncentive.value = "0.00";
        if (hiddenNetProceeds) hiddenNetProceeds.value = principal.toFixed(2);
        if (hiddenSecondaryMonthly) hiddenSecondaryMonthly.value = "";
    
        window.secondaryLedger = [];
    }

    // 🔹 Store primary ledger globally for save function
    window.primaryLedger = primarySchedule;
}

// Helper: calculate each payment's due date
function getDueDate(startDate, monthNumber) {
    const date = new Date(startDate);
    date.setMonth(date.getMonth() + monthNumber);
    const dd = String(date.getDate()).padStart(2, '0');
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const yyyy = date.getFullYear();
    return `${yyyy}-${mm}-${dd}`; // YYYY-MM-DD format for consistency
}

// Helper: populate amortization table
function renderAmortizationTable(schedule) {
    const tbody = document.getElementById('amortizationTableBody');
    if (!tbody) return;
    tbody.innerHTML = ''; // clear existing rows

    schedule.forEach(row => {
        const totalAmount = row.principal + row.interest;
        const tr = `
            <tr class="hover:bg-gray-50 border-b border-gray-200">
                <td class="p-2 border-r border-gray-300 text-center font-bold">${row.payment_number}</td>
                <td class="p-2 border-r border-gray-300 text-center">${formatDate(row.due_date)}</td>
                <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.principal)}</td>
                <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.interest)}</td>
                <td class="p-2 border-r border-gray-300 text-right font-medium">${formatCurrency(totalAmount)}</td>
                <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.ending_balance)}</td>
                <td class="p-2 text-center font-bold text-xs ${row.status === 'PAID' ? 'text-green-600' : 'text-red-500'}">
                    ${row.status}
                </td>
            </tr>`;
        tbody.insertAdjacentHTML('beforeend', tr);
    });
}

// Reuse your existing formatting functions
function formatCurrency(num) {
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(num);
}

function formatDate(dateStr) {
    const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
    return new Date(dateStr).toLocaleDateString('en-GB', options); // DD/MM/YYYY
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

// --- 1. Validation Modal (Keep this for missing fields) ---
function showErrorModal(fields) {
    const modal = document.getElementById('errorModal'); // This is from addloan_errormodal.php
    const list = document.getElementById('errorList');
    
    if (!modal || !list) return;

    list.innerHTML = ''; 
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

// --- 2. The Submission Event Listener (KEEP THIS) ---
document.addEventListener('submit', function (e) {
    if (e.target && e.target.id === 'loanForm') {
        e.preventDefault(); 
        e.stopPropagation();
        handleLoanSubmission(e.target);
    }
});

// --- 3. The Refined Submission Function ---
async function handleLoanSubmission(formElement) {
    // Show loading state first
    showStatusModal('success', 'Processing...', 'Please wait...', false);

    const formData = new FormData(formElement);

    try {
        const response = await fetch('../actions/save_loan.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json(); 
        console.log("Server Response:", result); 

        // Change 'result.success' to 'result.status === "success"'
        if (result.status === "success" || result.success === true) { 
            showStatusModal('success', 'Success!', result.message || 'Record saved successfully.', true);
        } else {
            showStatusModal('error', 'Save Failed', result.message || 'Error saving record.', false);
        }
    } catch (error) {
        console.error("Submission Error:", error);
        showStatusModal('error', 'Connection Error', 'Server unreachable.', false);
    }
}

// --- 4. The Updated Modal Controller ---
function showStatusModal(type, title, msg, shouldRefresh) {
    // 1. Target the correct ID
    const modal = document.getElementById('saveRecordModal'); 
    const container = document.getElementById('modalContainer');
    const iconDiv = document.getElementById('modalIcon');
    const closeBtn = document.getElementById('modalCloseBtn');
    
    // Check if modal exists
    if (!modal) {
        console.error("saveRecordModal not found in DOM.");
        return;
    }

    // 2. Set the text
    document.getElementById('statusTitle').innerText = title;
    document.getElementById('statusMsg').innerHTML = msg;

    // 3. Set the icon, colors, and handle the Timer/Button logic
    if (type === 'success') {
        iconDiv.className = "mx-auto mb-4 flex items-center justify-center w-20 h-20 rounded-full bg-green-100 text-green-600 pop-icon";
        iconDiv.innerHTML = '<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>';
        
        // --- HIDE BUTTON & START TIMER ---
        closeBtn.style.display = 'none'; 
        
        setTimeout(() => {
            if (shouldRefresh) {
                window.location.reload();
            } else {
                closeStatusModal();
            }
        }, 1000); // 2-second timer

    } else {
        iconDiv.className = "mx-auto mb-4 flex items-center justify-center w-20 h-20 rounded-full bg-red-100 text-red-600 pop-icon";
        iconDiv.innerHTML = '<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>';
        
        // Show button for errors so user can acknowledge
        closeBtn.style.display = 'block'; 
    }

    // 4. Show the modal
    modal.classList.replace('hidden', 'flex');
    setTimeout(() => container.classList.replace('scale-95', 'scale-100'), 10);

    // 5. Handle Manual Close (for errors or if timer is skipped)
    closeBtn.onclick = () => {
        if (shouldRefresh && type === 'success') {
            window.location.reload();
        } else {
            closeStatusModal();
        }
    };
}

function closeStatusModal() {
    const modal = document.getElementById('saveRecordModal');
    if (modal) {
        const container = document.getElementById('modalContainer');
        container.classList.replace('scale-100', 'scale-95');
        setTimeout(() => {
            modal.classList.replace('flex', 'hidden');
            // Reset button display for the next time the modal opens
            document.getElementById('modalCloseBtn').style.display = 'block';
        }, 150);
    }
}


document.addEventListener('DOMContentLoaded', function() {
    const refInput = document.getElementById('ref_no');
    const errorDisplay = document.getElementById('ref-error');

    if (refInput) {
        refInput.addEventListener('input', function() {
            // 1. Force Uppercase and limit to 11
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, ''); 
            
            const val = this.value;

            // 2. Clear errors if empty
            if (val.length === 0) {
                hideError();
                return;
            }

            // 3. Trapping: Check if less than 11
            if (val.length < 11) {
                showError("Reference must be exactly 11 characters.");
            } 
            // 4. Trapping: If exactly 11, check Database
            else if (val.length === 11) {
                fetch(`../api/check_reference.php?ref=${val}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.exists) {
                            showError("Reference number already exists in the database.");
                        } else {
                            hideError(); // Valid and Unique!
                        }
                    })
                    .catch(err => console.error("Error checking reference:", err));
            }
        });
    }

    function showError(msg) {
        errorDisplay.textContent = msg;
        errorDisplay.classList.remove('hidden');
        refInput.classList.add('border-red-500', 'ring-1', 'ring-red-500');
    }

    function hideError() {
        errorDisplay.classList.add('hidden');
        refInput.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
    }
});



