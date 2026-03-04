async function viewAmortization(loanId, sourceTab) {
    console.log("Loan ID sent:", loanId);
    
    try {
        const response = await fetch(`../api/get_amortization.php?loan_id=${loanId}`);
        const responseData = await response.json();

        if (responseData.status !== "success") {
            alert(responseData.message || "Failed to load amortization.");
            return;
        }

        // Extract data from responseData.data (matches get_amortization.php)
        const { loan, primary_ledger, secondary_ledger } = responseData.data;

        // Borrower Info (Ensure these IDs exist in your HTML)
        document.getElementById('modalDispName').innerText = (loan.first_name + ' ' + loan.last_name);
        document.getElementById('modalDispRef').innerText = loan.reference_number;
        document.getElementById('modalDispContact').innerText = loan.contact_number || 'N/A';
        document.getElementById('modalDispPrincipal').innerText = formatCurrency(loan.principal_amount);
        document.getElementById('modalDispAmount').innerText = formatCurrency(loan.net_proceeds);
        document.getElementById('modalDispDate').innerText = formatDate(loan.pn_date);
        document.getElementById('modalDispTerm').innerText = loan.term_months || '0';
        document.getElementById('modalDispMaturity').innerText = formatDate(loan.pn_maturity_date);
        document.getElementById('modalDispRate').innerText = (loan.interest_rate || 0) + '%';
        document.getElementById('modalDispMonthly').innerText = formatCurrency(loan.monthly_amortization);
        const principalLabel = document.getElementById('principalLabel');
        const principalWrapper = document.getElementById('principalWrapper');

        const netLabel = document.getElementById('netLabel');
        const netWrapper = document.getElementById('netWrapper');

        if (sourceTab === 'primary') {

            // Show principal
            if (principalLabel) principalLabel.classList.remove('hidden');
            if (principalWrapper) principalWrapper.classList.remove('hidden');

            // Hide net proceeds
            if (netLabel) netLabel.classList.add('hidden');
            if (netWrapper) netWrapper.classList.add('hidden');

        } else if (sourceTab === 'secondary') {

            // Hide principal
            if (principalLabel) principalLabel.classList.add('hidden');
            if (principalWrapper) principalWrapper.classList.add('hidden');

            // Show net proceeds
            if (netLabel) netLabel.classList.remove('hidden');
            if (netWrapper) netWrapper.classList.remove('hidden');
        }

        // PRIMARY LEDGER
        const primaryBody = document.getElementById('primaryLedgerBody');
        const primaryTitle = document.getElementById('primaryLedgerTitle');

        const primaryWrapper = document.querySelector('#primaryLedgerTable').closest('.overflow-x-auto');
        // 🔥 CONTROL PRIMARY DISPLAY
        if (sourceTab === 'primary') {
            primaryWrapper.classList.remove('hidden');
            if (primaryTitle) primaryTitle.classList.remove('hidden');
        } else {
            primaryWrapper.classList.add('hidden');
            if (primaryTitle) primaryTitle.classList.add('hidden');
        }
        primaryBody.innerHTML = '';

        // Add starting row
        const primaryStartRow = `
            <tr class="bg-gray-50">
                <td colspan="5" class="p-2 border-r border-gray-300"></td>
                <td class="p-2 border-r border-gray-300 text-right font-bold bg-gray-100">
                    ${formatCurrency(loan.principal_amount)}
                </td>
                <td></td>
            </tr>`;
        primaryBody.insertAdjacentHTML('beforeend', primaryStartRow);

        primary_ledger.forEach(row => { // ✅ updated variable name
            const totalAmount = parseFloat(row.principal || 0) + parseFloat(row.interest || 0);
            const tr = `
                <tr class="hover:bg-gray-50 border-b border-gray-200">
                    <td class="p-2 border-r border-gray-300 text-center font-bold">${row.installment_no}</td>
                    <td class="p-2 border-r border-gray-300 text-center">${formatDate(row.due_date)}</td>
                    <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.principal)}</td>
                    <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.interest)}</td>
                    <td class="p-2 border-r border-gray-300 text-right font-medium">${formatCurrency(totalAmount)}</td>
                    <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.ending_balance)}</td>
                    <td class="p-2 text-center font-bold text-xs ${row.status === 'paid' ? 'text-green-600' : 'text-red-500'}">
                        ${row.status || 'UNPAID'}
                    </td>
                </tr>`;
            primaryBody.insertAdjacentHTML('beforeend', tr);
        });

        // SECONDARY LEDGER (GMS)
        const secondaryWrapper = document.getElementById('secondaryLedgerWrapper');
        const secondaryTitle = document.getElementById('secondaryLedgerTitle');
        const secondaryBody = document.getElementById('secondaryLedgerBody');

        if (secondary_ledger && secondary_ledger.length > 0 && sourceTab === 'secondary') {

        secondaryWrapper.classList.remove('hidden');
        if (secondaryTitle) secondaryTitle.classList.remove('hidden');
        secondaryBody.innerHTML = '';

        const secondaryStartRow = `
            <tr class="bg-gray-50">
                <td colspan="5" class="p-2 border-r border-gray-300"></td>
                <td class="p-2 border-r border-gray-300 text-right font-bold bg-gray-100">
                    ${formatCurrency(loan.net_proceeds)}
                </td>
                <td></td>
            </tr>`;
        secondaryBody.insertAdjacentHTML('beforeend', secondaryStartRow);

        secondary_ledger.forEach(row => {
            const totalAmount = parseFloat(row.principal || 0) + parseFloat(row.interest || 0);
            const tr = `
                <tr class="hover:bg-gray-50 border-b border-gray-200">
                    <td class="p-2 border-r border-gray-300 text-center font-bold">${row.installment_no}</td>
                    <td class="p-2 border-r border-gray-300 text-center">${formatDate(row.due_date)}</td>
                    <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.principal)}</td>
                    <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.interest)}</td>
                    <td class="p-2 border-r border-gray-300 text-right font-medium">${formatCurrency(totalAmount)}</td>
                    <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.ending_balance)}</td>
                    <td class="p-2 text-center font-bold text-xs ${row.status === 'paid' ? 'text-green-600' : 'text-red-500'}">
                        ${row.status || 'UNPAID'}
                    </td>
                </tr>`;
            secondaryBody.insertAdjacentHTML('beforeend', tr);
        });

    } else {

        secondaryWrapper.classList.add('hidden');
        if (secondaryTitle) secondaryTitle.classList.add('hidden');
    }

        // THE KEY LINE: This shows the modal
        document.getElementById('amortizationModal').classList.remove('hidden');

    } catch (error) {
        console.error('Final JS Error Trace:', error);
    }
}

function formatCurrency(num) {
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(num);
}

function formatDate(dateStr) {
    const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
    return new Date(dateStr).toLocaleDateString('en-GB', options); // DD/MM/YYYY
}

/**
 * Export Data (Excel / PDF) – works for both Primary & Secondary Ledgers
 */
function exportData(format) {
    const pnNumber = document.getElementById('modalDispRef').innerText;

    if (format === 'excel') {
        const wb = XLSX.utils.book_new();
        const ledgers = [
            { id: 'primaryLedgerBody', title: 'PRIMARY_LEDGER' },
            { id: 'secondaryLedgerBody', title: 'SECONDARY_LEDGER', wrapper: 'secondaryLedgerWrapper' }
        ];

        ledgers.forEach(ledger => {
            const wrapper = ledger.wrapper ? document.getElementById(ledger.wrapper) : null;
            if (wrapper && wrapper.classList.contains('hidden')) return; // Skip hidden ledger

            const tbodyRows = document.querySelectorAll(`#${ledger.id} tr`);
            if (tbodyRows.length === 0) return;

            const rows = [
                [`${ledger.title} - ${pnNumber}`],
                ["#", "DATE", "PRINCIPAL", "INTEREST", "TOTAL AMOUNT", "PRINCIPAL BALANCE", "STATUS"]
            ];

            tbodyRows.forEach(tr => {
                const cols = tr.querySelectorAll('td');
                if (cols.length >= 6) {
                    rows.push([
                        cols[0]?.innerText || "",
                        cols[1]?.innerText || "",
                        parseFloat(cols[2]?.innerText.replace(/,/g, '')) || 0,
                        parseFloat(cols[3]?.innerText.replace(/,/g, '')) || 0,
                        parseFloat(cols[4]?.innerText.replace(/,/g, '')) || 0,
                        parseFloat(cols[5]?.innerText.replace(/,/g, '')) || 0,
                        cols[6]?.innerText || ""
                    ]);
                }
            });

            const ws = XLSX.utils.aoa_to_sheet(rows);
            XLSX.utils.book_append_sheet(wb, ws, ledger.title);
        });

        XLSX.writeFile(wb, `Amortization_${pnNumber}.xlsx`);

    } else if (format === 'pdf') {
        const element = document.getElementById('amortizationPrintArea');
        const opt = {
            margin: 0.5,
            filename: `Amortization_${pnNumber}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }

    document.getElementById('amortizationDropdownMenu').classList.add('hidden');
}