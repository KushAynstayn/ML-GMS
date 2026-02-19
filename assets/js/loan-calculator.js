document.addEventListener('DOMContentLoaded', function() {
    const loanTypeSelect = document.getElementById('loanType');
    const vehicleFields = document.getElementById('vehicleFields');
    const deviceRadios = document.querySelectorAll('.device-radio');
    const dateInstalledDiv = document.getElementById('dateInstalledDiv');

    const loanAmtInp = document.getElementById('calcLoanAmount');
    const termInp = document.getElementById('calcTerm');
    const dateGrantedInp = document.getElementById('calcDateGranted');

    const resMaturity = document.getElementById('resMaturity');
    const resIncentive = document.getElementById('resIncentive');
    const resNetLoan = document.getElementById('resNetLoan');
    const resAOR = document.getElementById('resAOR');
    const resMonthly = document.getElementById('resMonthly');
    const resEIR = document.getElementById('resEIR');

    // Toggle Vehicle Section
    loanTypeSelect.addEventListener('change', function() {
        if (this.value === 'Car Loan' || this.value === 'Motor Loan') {
            vehicleFields.classList.remove('hidden');
        } else {
            vehicleFields.classList.add('hidden');
        }
    });

    // Toggle Date Installed
    deviceRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'YES') {
                dateInstalledDiv.classList.remove('hidden');
            } else {
                dateInstalledDiv.classList.add('hidden');
            }
        });
    });

    // Helper function for EIR (Newton-Raphson) - Replicating Excel's RATE()
    function getEIRRate(nper, pmt, pv, guess = 0.01) {
        let rate = guess;
        const tolerance = 0.0000001;
        const maxIterations = 100;

        for (let i = 0; i < maxIterations; i++) {
            let powNMinus1 = Math.pow(1 + rate, -nper);
            
            // Function f(rate)
            let f = pv + pmt * (1 - powNMinus1) / rate;
            // Derivative f'(rate)
            let df = pmt * (nper * powNMinus1 / (rate * (1 + rate)) - (1 - powNMinus1) / Math.pow(rate, 2));

            let newRate = rate - (f / df);

            if (Math.abs(newRate - rate) < tolerance) {
                return newRate;
            }
            rate = newRate;
        }
        return rate;
    }

    function calculateLoan() {
        const loanAmount = parseFloat(loanAmtInp.value) || 0;
        const term = parseInt(termInp.value) || 0;
        const dateGranted = dateGrantedInp.value;

        // 1. Calculate Maturity Date
        if (dateGranted && term > 0) {
            let d = new Date(dateGranted);
            d.setMonth(d.getMonth() + term);
            
            const dd = String(d.getDate()).padStart(2, '0');
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const yyyy = d.getFullYear();
            
            resMaturity.value = `${dd}/${mm}/${yyyy}`;
        } else {
            resMaturity.value = "";
        }

        // 2. Calculations
        if (loanAmount > 0 && term > 0) {
            const incentive = loanAmount * 0.05;
            const netLoan = loanAmount - incentive;
            
            // AOR is fixed at 2% monthly in your logic
            const aorValue = 0.02; 
            const aorDisplay = (term * aorValue) * 100;
            
            // Monthly Amortization Calculation
            const monthly = (((netLoan * aorValue) * term) + netLoan) / term;

            // Get Monthly EIR (This matches Excel's =RATE function)
            const monthlyRate = getEIRRate(term, -monthly, netLoan);
            
            // Convert to percentage for display
            const eirPercentage = monthlyRate * 100;

            resIncentive.value = incentive.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            resNetLoan.value = netLoan.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            resAOR.value = aorDisplay.toFixed(2) + "%";
            resMonthly.value = monthly.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // Output the monthly rate to 6 decimal places as requested
            resEIR.value = eirPercentage.toFixed(6) + "%";
            
        } else {
            resIncentive.value = "";
            resNetLoan.value = "";
            resAOR.value = "";
            resMonthly.value = "";
            resEIR.value = "";
        }
    }

    [loanAmtInp, termInp, dateGrantedInp].forEach(el => {
        el.addEventListener('input', calculateLoan);
    });
});