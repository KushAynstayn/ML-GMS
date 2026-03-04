<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Cadc20239999\MlGms\Database;

if (!isset($type)) { $type = $_GET['type'] ?? 'car'; }

$type_map = ['car' => 1, 'motor' => 2, 'home' => 3, 'salary' => 4, 'personal' => 5, 'realestate' => 6];
$target_id = $type_map[$type] ?? 1;

try {
    $db = (new Database())->connect('LOAN');

    // INNER JOIN with sub-tables and filter by gps_provider = 'GMS'
    if ($type === 'car') {
        $sql = "SELECT l.loan_id, l.reference_number, l.pn_date, CONCAT(l.first_name, ' ', l.last_name) AS full_name 
                FROM loans l 
                INNER JOIN car_loans c ON l.loan_id = c.loan_id 
                WHERE l.loan_type_id = 1 AND c.gps_provider = 'GMS'";
    } else if ($type === 'motor') {
        $sql = "SELECT l.loan_id, l.reference_number, l.pn_date, CONCAT(l.first_name, ' ', l.last_name) AS full_name, m.type AS wheel_type 
                FROM loans l 
                INNER JOIN motor_loans m ON l.loan_id = m.loan_id 
                WHERE l.loan_type_id = 2 AND m.gps_provider = 'GMS'";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($loans) {
        foreach ($loans as $loan) {
            // Reusing the same render logic
            $date = date("d/m/Y", strtotime($loan['pn_date']));
            $wheelCell = ($type === 'motor') ? "<td class='px-6 py-4 text-sm font-bold'>".htmlspecialchars($loan['wheel_type'])."</td>" : "";
            echo "
            <tr onclick=\"viewAmortization('{$loan['loan_id']}', 'secondary')\" class='hover:bg-pink-50 transition-colors cursor-pointer'>
                <td class='px-6 py-4 text-sm text-gray-600'>$date</td>
                <td class='px-6 py-4 text-sm font-semibold text-gray-700'>".htmlspecialchars($loan['full_name'])."</td>
                <td class='px-6 py-4 text-sm font-mono text-gray-500'>".htmlspecialchars($loan['reference_number'])."</td>
                $wheelCell
            </tr>";
        }
    } else {
        $colspan = ($type === 'motor') ? 4 : 3;
        echo "<tr><td colspan='$colspan' class='px-6 py-12 text-center text-gray-400 text-sm italic'>No GMS records found.</td></tr>";
    }
} catch (Exception $e) {
    echo "<tr><td colspan='4'>Error loading secondary ledger.</td></tr>";
}