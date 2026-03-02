<?php
// Fix: Ensure $type is available whether accessed via include OR fetch
if (!isset($type)) {
    $type = $_GET['type'] ?? 'car';
}
$isMotor = ($type === 'motor');
require_once '../vendor/autoload.php';
use Cadc20239999\MlGms\Database;

try {
    $db = (new Database())->connect('LOAN');

    // SECONDARY LEDGER LOGIC: 
    // 1. Filter by Loan Category
    // 2. ONLY show records where device_installed = 'yes'
    
    $sql = "SELECT loan_id, account_name, reference_number, pn_date";
    
    // Add wheel_type column only if it's a motor loan
    if ($type === 'motor') {
        $sql .= ", wheel_type";
    }
    
    $sql .= " FROM loans WHERE loan_category = :type ";
    $sql .= " AND device_installed = 'yes' "; // The "Secondary" specific logic
    $sql .= " ORDER BY loan_id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute(['type' => $type]);
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($loans) {
        foreach ($loans as $loan) {
            $loanId = $loan['loan_id'];
            $accountName = htmlspecialchars($loan['account_name']);
            $reference = htmlspecialchars($loan['reference_number']);
            $releaseDate = date("d/m/Y", strtotime($loan['pn_date']));
            $wheelType = $loan['wheel_type'] ?? 'N/A';
            ?>
            <tr onclick="viewAmortization('<?php echo $loanId; ?>')" class="hover:bg-pink-50 transition-colors group cursor-pointer">
                <td class="px-6 py-4 text-sm text-gray-600">
                    <?php echo $releaseDate; ?>
                </td>
                <td class="px-6 py-4 text-sm font-semibold text-gray-700">
                    <?php echo $accountName; ?>
                </td>
                <td class="px-6 py-4 text-sm font-mono text-gray-500">
                    <?php echo $reference; ?>
                </td>
                <?php if ($type === 'motor'): ?>
                    <td class="px-6 py-4 text-sm text-gray-600 font-bold italic">
                        <?php echo htmlspecialchars($wheelType); ?>
                    </td>
                <?php endif; ?>
            </tr>
            <?php
        }
    } else {
        $colspan = ($type === 'motor') ? 4 : 3;
        echo "<tr><td colspan='$colspan' class='px-6 py-12 text-center text-gray-400 text-sm italic'>No records found with devices installed.</td></tr>";
    }
} catch (Exception $e) {
    $colspan = ($type === 'motor') ? 4 : 3;
    echo "<tr><td colspan='$colspan' class='px-6 py-12 text-center text-red-500 text-sm font-medium'>Database Connection Error.</td></tr>";
}
?>