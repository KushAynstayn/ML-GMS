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
        if (sourceTab === 'secondary') {
            document.getElementById('modalDispMonthly').innerText = formatCurrency(loan.secondary_monthly || 0);
        } else {
            document.getElementById('modalDispMonthly').innerText = formatCurrency(loan.monthly_amortization || 0);
        }
        
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
 * Export Data (Excel / PDF) - Updated for Dynamic Wrapper logic
 */
function exportData(format) {
    const pnNumber = document.getElementById('modalDispRef')?.innerText || "";
    
    // IDs from your updated PHP
    const fullLoanAmount = document.getElementById('modalDispPrincipal')?.innerText || "0.00";
    const discountedAmount = document.getElementById('modalDispAmount')?.innerText || "0.00";

    const borrowerInfo = {
        accountName: document.getElementById('modalDispName')?.innerText || "---",
        contactNumber: document.getElementById('modalDispContact')?.innerText || "---",
        refNumber: pnNumber,
        dateGranted: document.getElementById('modalDispDate')?.innerText || "---",
        maturityDate: document.getElementById('modalDispMaturity')?.innerText || "---",
        term: document.getElementById('modalDispTerm')?.innerText || "0",
        interestRate: document.getElementById('modalDispRate')?.innerText || "0%",
        monthlyAmort: document.getElementById('modalDispMonthly')?.innerText || "0.00"
    };

    const signatures = {
        preparedBy: document.getElementById('inputPreparedBy')?.value || "Reynaldo Lapiña Jr.",
        checkedBy: document.getElementById('inputCheckedBy')?.value || "Jacob N. Lomotos",
        conforme1: document.getElementById('inputConforme1')?.value || "",
        conforme2: document.getElementById('inputConforme2')?.value || ""
    };

    document.getElementById('amortizationDropdownMenu')?.classList.add('hidden');

    if (format === 'pdf') {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4'); 
        const img = new Image();
        img.src = '../assets/images/ml.png'; 
        
        img.onload = function() {
            const renderLayout = (tableId, forcedTitle, amountLabel, amountValue) => {
                doc.addImage(img, 'PNG', 87.5, 10, 35, 7);
                doc.setFontSize(9);
                doc.setFont("helvetica", "bold");
                doc.setTextColor(100, 100, 100);
                doc.text(forcedTitle, 105, 22, { align: "center" });

                doc.autoTable({
                    startY: 28,
                    body: [
                        [{content: 'Account Name:', styles: {fontStyle: 'bold'}}, borrowerInfo.accountName, {content: amountLabel, styles: {fontStyle: 'bold'}}, amountValue],
                        [{content: 'Contact Number:', styles: {fontStyle: 'bold'}}, borrowerInfo.contactNumber, {content: 'Term:', styles: {fontStyle: 'bold'}}, borrowerInfo.term + " months"],
                        [{content: 'Reference Number:', styles: {fontStyle: 'bold'}}, borrowerInfo.refNumber, {content: 'Interest Rate (AOR):', styles: {fontStyle: 'bold'}}, borrowerInfo.interestRate],
                        [{content: 'Date Granted:', styles: {fontStyle: 'bold'}}, borrowerInfo.dateGranted, {content: 'Monthly Amortization:', styles: {fontStyle: 'bold'}}, borrowerInfo.monthlyAmort],
                        [{content: 'Maturity Date:', styles: {fontStyle: 'bold'}}, borrowerInfo.maturityDate, {content: '', colSpan: 2}]
                    ],
                    theme: 'grid',
                    styles: { fontSize: 7.5, cellPadding: 2, textColor: [0, 0, 0], lineColor: [200, 200, 200] },
                    columnStyles: { 0: { cellWidth: 35 }, 2: { cellWidth: 35 } }
                });

                doc.autoTable({
                    html: tableId,
                    startY: doc.lastAutoTable.finalY, 
                    theme: 'grid',
                    styles: { fontSize: 7, halign: 'center', lineColor: [200, 200, 200] },
                    headStyles: { fillColor: [220, 38, 38], textColor: [255, 255, 255] }
                });

                // Signatures Section
                let currentY = doc.lastAutoTable.finalY + 15;
                doc.setFontSize(8);
                doc.setTextColor(0, 0, 0);
                doc.setFont("helvetica", "normal");
                doc.text("Prepared by:", 20, currentY);
                doc.text("Checked by:", 130, currentY);
                doc.setFont("helvetica", "bold");
                doc.text(signatures.preparedBy, 20, currentY + 7);
                doc.text(signatures.checkedBy, 130, currentY + 7);
                doc.line(20, currentY + 8, 80, currentY + 8);
                doc.line(130, currentY + 8, 190, currentY + 8);
                doc.setFont("helvetica", "normal");
                doc.text("LOANS EVALUATOR", 20, currentY + 12);
                doc.text("ML Lending Head", 130, currentY + 12);

                currentY += 25; 
                doc.text("Conforme:", 20, currentY);
                doc.setFont("helvetica", "bold");
                doc.text(signatures.conforme1 || " ", 20, currentY + 7); 
                doc.text(signatures.conforme2 || " ", 130, currentY + 7);
                doc.line(20, currentY + 8, 80, currentY + 8);
                doc.line(130, currentY + 8, 190, currentY + 8);
                doc.setFontSize(6);
                doc.setFont("helvetica", "normal");
                doc.text("Debtor/Creditor", 20, currentY + 12);
                doc.text("Debtor/Creditor", 130, currentY + 12);
            };

            // NEW VISIBILITY LOGIC
            const secondaryWrapper = document.getElementById('secondaryLedgerWrapper');
            const primaryTable = document.getElementById('primaryLedgerTable');
            
            // If secondary is the ONLY thing visible (Primary is hidden or secondary has taken over)
            const isSecondaryVisible = secondaryWrapper && !secondaryWrapper.classList.contains('hidden');
            const isPrimaryHidden = primaryTable && primaryTable.closest('div').classList.contains('hidden');

            if (isSecondaryVisible && isPrimaryHidden) {
                // Single page: GMS only
                renderLayout('#secondaryLedgerTable', 'SECONDARY LEDGER (GMS)', 'Amount (5%):', discountedAmount);
            } else {
                // Page 1: Primary
                renderLayout('#primaryLedgerTable', 'PRIMARY LEDGER', 'Loan Amount:', fullLoanAmount);

                // Page 2: Secondary (if it exists/is enabled for this account)
                if (isSecondaryVisible) {
                    doc.addPage();
                    renderLayout('#secondaryLedgerTable', 'SECONDARY LEDGER (GMS)', 'Amount (5%):', discountedAmount);
                }
            }

            doc.save(`Amortization_${pnNumber}.pdf`);
        };
    }
    else if (format === 'excel') {
        const workbook = new ExcelJS.Workbook();
        
        const generateSheet = async (sheetName, title, amountLabel, amountValue, tableId) => {
            const worksheet = workbook.addWorksheet(sheetName);
            
            // Set Columns Width
            worksheet.columns = [
                { width: 20 }, { width: 25 }, { width: 12 }, 
                { width: 12 }, { width: 12 }, { width: 25 }, { width: 20 }
            ];

            // 1. Logo Handling (Rows 1 & 2 Center Column C)
            const response = await fetch('../assets/images/ml.png');
            const buffer = await response.arrayBuffer();
            const logoId = workbook.addImage({ buffer: buffer, extension: 'png' });
            worksheet.addImage(logoId, {
                tl: { col: 2.2, row: 0.2 },
                ext: { width: 120, height: 30 }
            });

            // 2. Ledger Name (Row 3 Column C)
            worksheet.getCell('C3').value = title;
            worksheet.getCell('C3').font = { bold: true, size: 12 };
            worksheet.getCell('C3').alignment = { horizontal: 'center' };

            // 3. Info Section (Rows 4-8) with Full Borders
            const borderStyle = {
                top: { style: 'thin' },
                left: { style: 'thin' },
                bottom: { style: 'thin' },
                right: { style: 'thin' }
            };

            // Left Side Data
            const leftData = [
                ['Account Name', borrowerInfo.accountName],
                ['Contact Number', borrowerInfo.contactNumber],
                ['Reference Number', borrowerInfo.refNumber],
                ['Date Granted', borrowerInfo.dateGranted],
                ['Maturity Date', borrowerInfo.maturityDate]
            ];

            // Right Side Data
            const rightData = [
                [amountLabel, amountValue],
                ['Term', borrowerInfo.term + " months"],
                ['Interest Rate', borrowerInfo.interestRate],
                ['Monthly Amortization', borrowerInfo.monthlyAmort],
                ['', ''] // Empty for symmetry
            ];

            for (let i = 0; i < 5; i++) {
                const rowNum = i + 4;
                const row = worksheet.getRow(rowNum);

                // Set Values
                row.getCell(1).value = leftData[i][0];
                row.getCell(2).value = leftData[i][1];
                row.getCell(6).value = rightData[i][0];
                row.getCell(7).value = rightData[i][1];

                // Styling and Borders for all columns A through G
                for (let col = 1; col <= 7; col++) {
                    const cell = row.getCell(col);
                    cell.border = borderStyle;
                    if (col === 1 || col === 6) cell.font = { bold: true };
                }
            }

            // 4. Table Header (Row 9 Column A to G)
            const headers = ['#', 'Date', 'Principal', 'Interest', 'Total Amount', 'Principal Balance', 'Status'];
            const headerRow = worksheet.getRow(9);
            headers.forEach((h, i) => {
                const cell = headerRow.getCell(i + 1);
                cell.value = h;
                cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFDC2626' } };
                cell.alignment = { horizontal: 'center' };
                cell.border = borderStyle;
            });

            // 5. Table Data (Starts Row 10)
            const table = document.querySelector(tableId);
            const rows = table.querySelectorAll('tbody tr');
            let lastRowIdx = 10;

            rows.forEach((tr, rowIndex) => {
                const currentExcelRowIdx = rowIndex + 10;
                const excelRow = worksheet.getRow(currentExcelRowIdx);
                const cells = tr.querySelectorAll('td');
                
                cells.forEach((td, cellIndex) => {
                    const cell = excelRow.getCell(cellIndex + 1);
                    let val = td.innerText.trim();

                    // SPECIAL LOGIC: Row 10 (First Data Row) shift Column B value to Column F
                    if (rowIndex === 0 && cellIndex === 1 && val !== "") {
                        // If it's the first row and column B (index 1), move to F (index 5)
                        excelRow.getCell(6).value = val;
                        excelRow.getCell(6).alignment = { horizontal: 'center' };
                    } else if (rowIndex === 0 && cellIndex === 1) {
                        // Do nothing for B cell itself
                    } else {
                        cell.value = val;
                    }
                    
                    cell.border = borderStyle;
                    cell.alignment = { horizontal: 'center' };
                });
                
                // Ensure the last column (G) has a border even if empty
                excelRow.getCell(7).border = borderStyle;
                lastRowIdx = currentExcelRowIdx;
            });

            // 6. Signatures Section (Below the table)
            let sigRow = lastRowIdx + 3;
            
            // Labels
            worksheet.getCell(`A${sigRow}`).value = "Prepared by:";
            worksheet.getCell(`F${sigRow}`).value = "Checked by:";
            
            // Names
            sigRow++;
            worksheet.getCell(`A${sigRow}`).value = signatures.preparedBy;
            worksheet.getCell(`F${sigRow}`).value = signatures.checkedBy;
            worksheet.getCell(`A${sigRow}`).font = { bold: true };
            worksheet.getCell(`F${sigRow}`).font = { bold: true };
            
            // Underlines (Bottom Border)
            worksheet.getCell(`A${sigRow}`).border = { bottom: { style: 'thin' } };
            worksheet.getCell(`F${sigRow}`).border = { bottom: { style: 'thin' } };

            // Designation
            sigRow++;
            worksheet.getCell(`A${sigRow}`).value = "LOANS EVALUATOR";
            worksheet.getCell(`F${sigRow}`).value = "ML Lending Head";
            worksheet.getCell(`A${sigRow}`).font = { size: 9 };
            worksheet.getCell(`F${sigRow}`).font = { size: 9 };

            // Conforme Section
            sigRow += 3;
            worksheet.getCell(`A${sigRow}`).value = "Conforme:";
            
            sigRow++;
            worksheet.getCell(`A${sigRow}`).value = signatures.conforme1 || "";
            worksheet.getCell(`F${sigRow}`).value = signatures.conforme2 || "";
            worksheet.getCell(`A${sigRow}`).font = { bold: true };
            worksheet.getCell(`F${sigRow}`).font = { bold: true };
            worksheet.getCell(`A${sigRow}`).border = { bottom: { style: 'thin' } };
            worksheet.getCell(`F${sigRow}`).border = { bottom: { style: 'thin' } };

            sigRow++;
            worksheet.getCell(`A${sigRow}`).value = "Debtor/Creditor";
            worksheet.getCell(`F${sigRow}`).value = "Debtor/Creditor";
            worksheet.getCell(`A${sigRow}`).font = { size: 8 };
            worksheet.getCell(`F${sigRow}`).font = { size: 8 };
        };

        const secondaryWrapper = document.getElementById('secondaryLedgerWrapper');
        const primaryTable = document.getElementById('primaryLedgerTable');
        const isSecondaryVisible = secondaryWrapper && !secondaryWrapper.classList.contains('hidden');
        const isPrimaryHidden = primaryTable && primaryTable.closest('div').classList.contains('hidden');

        (async () => {
            if (isSecondaryVisible && isPrimaryHidden) {
                await generateSheet('Secondary Ledger', 'SECONDARY LEDGER (GMS)', 'Amount (5%)', discountedAmount, '#secondaryLedgerTable');
            } else {
                await generateSheet('Primary Ledger', 'PRIMARY LEDGER', 'Loan Amount', fullLoanAmount, '#primaryLedgerTable');
                if (isSecondaryVisible) {
                    await generateSheet('Secondary Ledger', 'SECONDARY LEDGER (GMS)', 'Amount (5%)', discountedAmount, '#secondaryLedgerTable');
                }
            }
            
            const buffer = await workbook.xlsx.writeBuffer();
            saveAs(new Blob([buffer]), `Amortization_${pnNumber}.xlsx`);
        })();
    }
}