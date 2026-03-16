/**
 * Global function to initialize or re-initialize listeners
 * This is crucial because switchTab() replaces the HTML content
 */

// Ensure this is at the top of main.js
function initSearchableDropdowns() {
    const regionSelect = document.querySelector('#regionSelect');

    if (regionSelect) {
        if (regionSelect.tomselect) regionSelect.tomselect.destroy();
        new TomSelect(regionSelect, {
            maxOptions: 100,
            create: false,
            placeholder: "Select Region...",
        });
    }

}


// Initialize on page load
document.addEventListener('DOMContentLoaded', initSearchableDropdowns);
document.addEventListener('DOMContentLoaded', initReferenceNumberValidation);

function initLoanCalculator() {
    const loanTypeSelect = document.getElementById('loanType');
    const classDiv = document.getElementById('classificationDiv');
    const classSelect = document.getElementById('classificationSelect');

    const motorDiv = document.getElementById('motorTypeDiv');
    const motorSelect = document.getElementById('motorTypeSelect');

    const vehicleFields = document.getElementById('vehicleFields');

    const loanAmtInp = document.getElementById('calcLoanAmount');
    const termInp = document.getElementById('calcTerm');
    const dateGrantedInp = document.getElementById('calcDateGranted');
    const aorInp = document.getElementById('resAOR');
    const dateInstalledInp = document.getElementById('dateInstalled');

    const verifyCheckbox = document.getElementById('verifyCheckbox');
    const submitBtn = document.getElementById('submitBtn');

    // Stop if form not in DOM yet
    if (!loanTypeSelect || !vehicleFields) return;

    // =============================
    // 1. TOGGLE CLASSIFICATION / MOTOR TYPE FIELDS
    // =============================
    function toggleClassificationFields() {
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

    // =============================
    // 2. TOGGLE VEHICLE SECTION
    // =============================
    function toggleVehicleSection() {
        const selectedOption = loanTypeSelect.options[loanTypeSelect.selectedIndex];
        if (!selectedOption) return;

        const selectedText = selectedOption.text.trim().toUpperCase();
        const motorTypeDiv = document.getElementById('motorTypeDiv');
        const carLoanFlag = document.getElementById('is_car_loan_flag');
        const loanTypeTextFlag = document.getElementById('loan_type_text_flag');

        if (loanTypeTextFlag) {
            loanTypeTextFlag.value = selectedText;
        } else {
            console.error("loan_type_text_flag not found in DOM");
        }

        if (selectedText === 'CAR LOAN' || selectedText === 'MOTOR LOAN') {
            vehicleFields.classList.remove('hidden');

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

    // =============================
    // 3. LOAN TYPE CHANGE HANDLER
    // =============================
    loanTypeSelect.onchange = function () {
        toggleClassificationFields();
        toggleVehicleSection();
        calculateLoan();
    };

    // =============================
    // 4. CALCULATION LISTENERS
    // Equivalent of your requested:
    // document.addEventListener('input'...)
    // document.addEventListener('change'...)
    // but attached safely to the actual form elements
    // =============================
    [loanAmtInp, termInp, dateGrantedInp, aorInp].forEach(el => {
        if (!el) return;

        el.oninput = function () {
            calculateLoan();
        };

        el.onchange = function () {
            calculateLoan();
        };
    });

    if (dateInstalledInp) {
        dateInstalledInp.oninput = function () {
            calculateLoan();
        };

        dateInstalledInp.onchange = function () {
            calculateLoan();
        };
    }

    if (classSelect) {
        classSelect.onchange = function () {
            calculateLoan();
        };
    }

    if (motorSelect) {
        motorSelect.onchange = function () {
            calculateLoan();
        };
    }

    // =============================
    // 5. VERIFICATION CHECKBOX LOGIC
    // =============================
    if (verifyCheckbox && submitBtn) {
        verifyCheckbox.onchange = function () {
            if (this.checked) {
                const form = document.getElementById('loanForm');
                const requiredFields = form.querySelectorAll('[required]');
                let missingFields = [];

                requiredFields.forEach(field => {
                    if (!field.value.trim() && field.offsetParent !== null) {
                        const label = field.closest('div')?.querySelector('label');
                        const labelName = label ? label.innerText.replace(':', '').trim() : field.name;
                        missingFields.push(labelName);

                        field.classList.add('border-red-500', 'bg-red-50');
                    } else {
                        field.classList.remove('border-red-500', 'bg-red-50');
                    }
                });

                if (missingFields.length > 0) {
                    this.checked = false;
                    showErrorModal(missingFields);

                    submitBtn.disabled = true;
                    submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed', 'opacity-70');
                    submitBtn.classList.remove('bg-red-600', 'hover:bg-red-700', 'cursor-pointer');
                } else {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed', 'opacity-70');
                    submitBtn.classList.add('bg-red-600', 'hover:bg-red-700', 'cursor-pointer');
                }
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed', 'opacity-70');
                submitBtn.classList.remove('bg-red-600', 'hover:bg-red-700', 'cursor-pointer');
            }
        };
    }

    // =============================
    // 6. FORCE INITIAL STATE
    // =============================
    toggleClassificationFields();
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



function calculateLoan() {
    const loanAmtInp = document.getElementById('calcLoanAmount');
    const termInp = document.getElementById('calcTerm');
    const dateGrantedInp = document.getElementById('calcDateGranted');
    const dateInstalledInp = document.getElementById('dateInstalled');
    const aorInp = document.getElementById('resAOR');

    if (!loanAmtInp || !termInp || !aorInp) return;

    const principal = parseFloat((loanAmtInp.value || '').replace(/,/g, '')) || 0;
    const term = parseInt(termInp.value) || 0;
    const dateGranted = dateGrantedInp?.value || "";
    const aor = parseFloat((aorInp.value || '').replace('%', '').replace(/,/g, '')) || 0;

    const resMaturity = document.getElementById('resMaturity');
    const resMonthly = document.getElementById('resMonthly');
    const resEIR = document.getElementById('resEIR');
    const resEY = document.getElementById('resEY');

    const hiddenIncentive = document.getElementById('hiddenIncentive');
    const hiddenNetProceeds = document.getElementById('hiddenNetProceeds');
    const hiddenMonthly = document.getElementById('hiddenMonthly');
    const hiddenSecondaryMonthly = document.getElementById('hiddenSecondaryMonthly');
    const hiddenEIR = document.getElementById('hiddenEIR');
    const hiddenEY = document.getElementById('hiddenEY');
    const hiddenMaturity = document.getElementById('hiddenMaturity');
    const hiddenAOR = document.getElementById('hiddenAOR');
    const hiddenMonthlyFactor = document.getElementById('hiddenMonthlyFactor');

    if (principal <= 0 || term <= 0 || aor <= 0) {
        if (resMonthly) resMonthly.value = "";
        if (resEIR) resEIR.value = "";
        if (resEY) resEY.value = "";
        if (hiddenMonthly) hiddenMonthly.value = "";
        if (hiddenEIR) hiddenEIR.value = "";
        if (hiddenEY) hiddenEY.value = "";
        if (hiddenMonthlyFactor) hiddenMonthlyFactor.value = "";
        return;
    }

    // Monthly factor based on new formula:
    // (AOR / term) * 100 => display percent
    // decimal equivalent = (AOR / term) / 100
    const monthlyFactorPercent = aor / term;
    const monthlyRate = monthlyFactorPercent / 100;
    

    if (hiddenMonthlyFactor) {
        hiddenMonthlyFactor.value = monthlyFactorPercent.toFixed(6);
    }

    if (hiddenAOR) {
        hiddenAOR.value = aor.toFixed(6);
    }

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
        if (hiddenMaturity) hiddenMaturity.value = "";
    }

    // ===============================
    // 2️⃣ PRIMARY LEDGER
    // ===============================
    const primaryMonthlyPayment = Math.ceil(
        (principal * ((aor * 0.01) + 1)) / term
    );

    // EY = RATE(term, -monthly amortization, principal) * 12
    const primaryMonthlyEY = getEIRRate(term, -primaryMonthlyPayment, principal);
    const annualEY = primaryMonthlyEY * 12; // store annual EY
    const scheduleMonthlyEY = annualEY / 12; // monthly rate used in schedule

    let primaryBalance = Number(principal.toFixed(2));
    const primarySchedule = [];

    for (let i = 1; i <= term; i++) {
        let interest = Number((primaryBalance * scheduleMonthlyEY).toFixed(2));
        let principalPayment = Number((primaryMonthlyPayment - interest).toFixed(2));

        if (i === term || principalPayment >= primaryBalance) {
            principalPayment = primaryBalance;
            interest = Number((primaryMonthlyPayment - principalPayment).toFixed(2));
            if (interest < 0) interest = 0;
        }

        let endingBalance = Number((primaryBalance - principalPayment).toFixed(2));
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

    if (resMonthly) resMonthly.value = primaryMonthlyPayment.toFixed(2);
    if (hiddenMonthly) hiddenMonthly.value = primaryMonthlyPayment.toFixed(2);

    if (resEY) resEY.value = (annualEY * 100).toFixed(6) + "%";
    if (hiddenEY) hiddenEY.value = (annualEY * 100).toFixed(6);

    renderAmortizationTable(primarySchedule);

    // ===============================
    // 3️⃣ SECONDARY LEDGER (IF GMS)
    // ===============================
    const isGMS = dateInstalledInp && dateInstalledInp.value.trim() !== "";

    if (isGMS) {
        const incentive = Number((principal * 0.05).toFixed(2));
        const netLoan = Number((principal - incentive).toFixed(2));

        const secondaryMonthlyPayment = Number(
            (
                (((netLoan * monthlyRate) * term) + netLoan) / term
            ).toFixed(2)
        );

        let secondaryBalance = netLoan;
        const secondarySchedule = [];

        // EIR for secondary
        const secondaryMonthlyEIR = getEIRRate(term, -secondaryMonthlyPayment, netLoan);
        const annualEIR = secondaryMonthlyEIR * 12;

        for (let i = 1; i <= term; i++) {
            let interest = Number((secondaryBalance * secondaryMonthlyEIR).toFixed(2));
            let principalPayment = Number((secondaryMonthlyPayment - interest).toFixed(2));

            if (i === term || principalPayment >= secondaryBalance) {
                principalPayment = secondaryBalance;
                interest = Number((secondaryMonthlyPayment - principalPayment).toFixed(2));
                if (interest < 0) interest = 0;
            }

            let endingBalance = Number((secondaryBalance - principalPayment).toFixed(2));
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
        if (resEIR) resEIR.value = (annualEIR * 100).toFixed(6) + "%";
        if (hiddenEIR) hiddenEIR.value = (annualEIR * 100).toFixed(6);

        window.secondaryLedger = secondarySchedule;
    } else {
        if (hiddenIncentive) hiddenIncentive.value = "0.00";
        if (hiddenNetProceeds) hiddenNetProceeds.value = principal.toFixed(2);
        if (hiddenSecondaryMonthly) hiddenSecondaryMonthly.value = "";
        if (resEIR) resEIR.value = "";
        if (hiddenEIR) hiddenEIR.value = "";

        window.secondaryLedger = [];
    }

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


function initReferenceNumberValidation() {
    const refInput = document.getElementById('ref_no');
    const errorDisplay = document.getElementById('ref-error');

    if (!refInput || !errorDisplay) return;

    // Prevent duplicate binding if tab is reopened
    if (refInput.dataset.refInit === 'true') return;
    refInput.dataset.refInit = 'true';

    refInput.addEventListener('input', function () {
        // Force uppercase and remove invalid chars
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

        const val = this.value;

        if (val.length === 0) {
            hideError();
            return;
        }

        if (val.length < 11) {
            showError("Reference must be exactly 11 characters.");
        } else if (val.length === 11) {
            fetch(`../api/check_reference.php?ref=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        showError("Reference number already exists in the database.");
                    } else {
                        hideError();
                    }
                })
                .catch(err => {
                    console.error("Error checking reference:", err);
                });
        }
    });

    function showError(msg) {
        errorDisplay.textContent = msg;
        errorDisplay.classList.remove('hidden');
        refInput.classList.add('border-red-500', 'ring-1', 'ring-red-500');
    }

    function hideError() {
        errorDisplay.textContent = '';
        errorDisplay.classList.add('hidden');
        refInput.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
    }
}



