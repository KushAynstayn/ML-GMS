/**
 * Call this function when clicking a borrower name in your "All Loans" table
 * Example: <a href="javascript:void(0)" onclick="viewAmortization(123)">Borrower Name</a>
 */
async function viewAmortization(loanId) {
    try {
        const response = await fetch(`../api/get_amortization.php?loan_id=${loanId}`);
        const result = await response.json();

        if (result.status === 'success') {
            const { loan, schedule } = result.data;

            // 1. Populate Header Information
            document.getElementById('modalDispName').innerText = loan.account_name;
            document.getElementById('modalDispRef').innerText = loan.reference_number;
            
            // Map the rest of your header fields
            document.querySelector('#amortizationModal [id="modalDispRef"] + div + div').innerText = 
                new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(loan.principal_amount);
            
            // Update PN Date, Term, Maturity, AOR, and Monthly Amort
            // (Target them using specific IDs you should add to your HTML)

            // 2. Populate Table Body
            const tbody = document.querySelector('#amortizationTable tbody');
            tbody.innerHTML = ''; // Clear hardcoded dummy data

            // Re-add the starting balance row
            const startRow = `
                <tr>
                    <td colspan="5" class="border-r border-gray-300 bg-white"></td>
                    <td class="p-2 border-r border-gray-300 text-right font-bold bg-gray-100">
                        ${new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(loan.principal_amount)}
                    </td>
                    <td class="bg-white"></td>
                </tr>`;
            tbody.insertAdjacentHTML('beforeend', startRow);

            // Loop through schedule
            schedule.forEach(row => {
                const tr = `
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border-r border-gray-300 text-center font-bold">${row.payment_number}</td>
                        <td class="p-2 border-r border-gray-300 text-center">${formatDate(row.due_date)}</td>
                        <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.principal)}</td>
                        <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.interest)}</td>
                        <td class="p-2 border-r border-gray-300 text-right font-medium">${formatCurrency(parseFloat(row.principal) + parseFloat(row.interest))}</td>
                        <td class="p-2 border-r border-gray-300 text-right">${formatCurrency(row.ending_balance)}</td>
                        <td class="p-2 text-center font-bold text-black">${row.status || 'UNPAID'}</td>
                    </tr>`;
                tbody.insertAdjacentHTML('beforeend', tr);
            });

            // 3. Show Modal
            document.getElementById('amortizationModal').classList.remove('hidden');
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error fetching amortization:', error);
    }
}

function formatCurrency(num) {
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(num);
}

function formatDate(dateStr) {
    const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
    return new Date(dateStr).toLocaleDateString('en-GB', options); // DD/MM/YYYY
}