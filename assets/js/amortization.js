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
            const { loan, schedule } = result.data;

            // FIX: Use the specific column names from your database screenshot
            document.getElementById('modalDispName').innerText = loan.account_name;
            document.getElementById('modalDispRef').innerText = loan.reference_number;
            document.getElementById('modalDispContact').innerText = loan.contact_number;
            
            // This displays 224,276.00 instead of 236,080.00
            document.getElementById('modalDispAmount').innerText = formatCurrency(loan.net_proceeds);
            
            document.getElementById('modalDispDate').innerText = formatDate(loan.pn_date);
            
            // FIX: Matches 'term_months' and 'pn_maturity_date' from your SQL table
            document.getElementById('modalDispTerm').innerText = loan.term_months || '0';
            document.getElementById('modalDispMaturity').innerText = formatDate(loan.pn_maturity_date);
            
            document.getElementById('modalDispRate').innerText = (loan.interest_rate || 0) + '%';
            document.getElementById('modalDispMonthly').innerText = formatCurrency(loan.monthly_amortization);
            
            const tbody = document.getElementById('amortizationTableBody');
            tbody.innerHTML = ''; 

            // FIX: Beginning Balance Row starts with Net Proceeds (224,276)
            const startRow = `
                <tr class="bg-gray-50">
                    <td colspan="5" class="p-2 border-r border-gray-300"></td>
                    <td class="p-2 border-r border-gray-300 text-right font-bold bg-gray-100">
                        ${formatCurrency(loan.net_proceeds)}
                    </td>
                    <td></td>
                </tr>`;
            tbody.insertAdjacentHTML('beforeend', startRow);

            schedule.forEach(row => {
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
                tbody.insertAdjacentHTML('beforeend', tr);
            });

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