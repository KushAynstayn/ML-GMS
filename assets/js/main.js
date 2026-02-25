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

document.addEventListener('DOMContentLoaded', initSearchableDropdowns);

document.addEventListener('DOMContentLoaded', initSearchableDropdowns);

// Initialize on page load
document.addEventListener('DOMContentLoaded', initSearchableDropdowns);

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
    const loanTypeSelect = document.getElementById('loanType');

    if (!loanAmtInp || !termInp) return;

    const loanAmount = parseFloat(loanAmtInp.value) || 0;
    const term = parseInt(termInp.value) || 0;
    const dateGranted = dateGrantedInp.value;

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
        if (resMaturity) resMaturity.value = "";
    }

    // 2. Calculations (Reducing Balance Fix)
    if (loanAmount > 0 && term > 0) {
        const incentive = loanAmount * 0.05;           // 5% incentive
        const netLoan = loanAmount - incentive;        // Loan after incentive
        const flatRate = 0.02;                         // 2% monthly FLAT
        const monthlyInterest = netLoan * flatRate;    // Constant monthly interest

        // Generate reducing balance schedule
        let balance = netLoan;
        const monthlyPayment = (netLoan + monthlyInterest * term) / term; // Initial monthly
        const amortizationSchedule = [];

        for (let i = 1; i <= term; i++) {
            let principal = monthlyPayment - monthlyInterest;

            // Last month adjustment
            if (i === term) {
                principal = balance;
            }

            balance -= principal;

            amortizationSchedule.push({
                payment_number: i,
                principal: principal,
                interest: monthlyInterest,
                ending_balance: balance >= 0 ? balance : 0,
                status: 'UNPAID',
                due_date: getDueDate(dateGranted, i)
            });
        }

        // Monthly payment (first month standard, last month may differ)
        const monthly = monthlyPayment;

        // AOR and EIR calculations
        const aorDisplay = flatRate * term * 100;
        const computedEIR = getEIRRate(term, -monthly, netLoan); // Your Excel-equivalent function
        const eirPercentage = computedEIR * 100;

        // Update result fields
        resIncentive.value = incentive.toFixed(2);
        resNetLoan.value = netLoan.toFixed(2);
        resAOR.value = aorDisplay.toFixed(2) + "%";
        resMonthly.value = monthly.toFixed(2);
        resEIR.value = eirPercentage.toFixed(6) + "%";

        document.getElementById('hiddenIncentive').value = incentive.toFixed(2);
        document.getElementById('hiddenNetProceeds').value = netLoan.toFixed(2);
        document.getElementById('hiddenAOR').value = (aorDisplay / 100).toFixed(4);
        document.getElementById('hiddenMonthly').value = monthly.toFixed(2);
        document.getElementById('hiddenEIR').value = eirPercentage.toFixed(6);

        // Optional: render amortization table immediately (if modal exists)
        renderAmortizationTable(amortizationSchedule);
    }
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
    document.getElementById('statusMsg').innerText = msg;

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



