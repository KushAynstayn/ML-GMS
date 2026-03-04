/**
 * Call this function when clicking a borrower name in your "All Loans" table
 * Example: <a href="javascript:void(0)" onclick="viewAmortization(123)">Borrower Name</a>
 */
async function viewAmortization(loanId) {
    try {
        const response = await fetch(`../api/get_amortization.php?loan_id=${loanId}`);
        const result = await response.json();

        console.log("DATABASE DATA:", result.data.loan);

        if (result.status === 'success') {
            const { loan, primary_schedule, secondary_schedule } = result.data;

            // Borrower Info
            document.getElementById('modalDispName').innerText = loan.account_name;
            document.getElementById('modalDispRef').innerText = loan.reference_number;
            document.getElementById('modalDispContact').innerText = loan.contact_number;
            document.getElementById('modalDispPrincipal').innerText = formatCurrency(loan.principal_amount);
            document.getElementById('modalDispAmount').innerText = formatCurrency(loan.net_proceeds);
            document.getElementById('modalDispDate').innerText = formatDate(loan.pn_date);
            document.getElementById('modalDispTerm').innerText = loan.term_months || '0';
            document.getElementById('modalDispMaturity').innerText = formatDate(loan.pn_maturity_date);
            document.getElementById('modalDispRate').innerText = (loan.interest_rate || 0) + '%';
            document.getElementById('modalDispMonthly').innerText = formatCurrency(loan.monthly_amortization);

            // PRIMARY LEDGER
            const primaryBody = document.getElementById('primaryLedgerBody');
            primaryBody.innerHTML = '';
            primary_schedule.forEach(row => {
                const totalAmount = parseFloat(row.principal) + parseFloat(row.interest);
                const tr = `
                    <tr class="hover:bg-gray-50 border-b border-gray-200">
                        <td class="p-2 border-r border-gray-300 text-center font-bold">${row.payment_number}</td>
                        <td class="p-2 border-r border-gray-300 text-center">${formatDate(row.due_date)}</td>
                        <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.principal)}</td>
                        <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.interest)}</td>
                        <td class="p-2 border-r border-gray-300 text-right font-medium">${formatCurrency(totalAmount)}</td>
                        <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.ending_balance)}</td>
                        <td class="p-2 text-center font-bold text-xs ${row.status === 'PAID' ? 'text-green-600' : 'text-red-500'}">
                            ${row.status || 'UNPAID'}
                        </td>
                    </tr>`;
                primaryBody.insertAdjacentHTML('beforeend', tr);
            });

            // SECONDARY LEDGER (GMS)
            const secondaryWrapper = document.getElementById('secondaryLedgerWrapper');
            const secondaryTitle = document.getElementById('secondaryLedgerTitle');
            const secondaryBody = document.getElementById('secondaryLedgerBody');

            if (loan.is_gms && secondary_schedule.length > 0) {
                secondaryWrapper.classList.remove('hidden');
                secondaryTitle.classList.remove('hidden');
                secondaryBody.innerHTML = '';

                secondary_schedule.forEach(row => {
                    const totalAmount = parseFloat(row.principal) + parseFloat(row.interest);
                    const tr = `
                        <tr class="hover:bg-gray-50 border-b border-gray-200">
                            <td class="p-2 border-r border-gray-300 text-center font-bold">${row.payment_number}</td>
                            <td class="p-2 border-r border-gray-300 text-center">${formatDate(row.due_date)}</td>
                            <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.principal)}</td>
                            <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.interest)}</td>
                            <td class="p-2 border-r border-gray-300 text-right font-medium">${formatCurrency(totalAmount)}</td>
                            <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.ending_balance)}</td>
                            <td class="p-2 text-center font-bold text-xs ${row.status === 'PAID' ? 'text-green-600' : 'text-red-500'}">
                                ${row.status || 'UNPAID'}
                            </td>
                        </tr>`;
                    secondaryBody.insertAdjacentHTML('beforeend', tr);
                });
            } else {
                secondaryWrapper.classList.add('hidden');
                secondaryTitle.classList.add('hidden');
            }

            document.getElementById('amortizationModal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
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