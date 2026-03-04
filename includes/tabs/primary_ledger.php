<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Cadc20239999\MlGms\Database;

// Ensure $type is available from the URL or the include context
$type = $_GET['type'] ?? $type ?? 'car';

// Map your slugs to the loan_type_id in your DB (Car=1, Motor=2)
$type_map = ['car' => 1, 'motor' => 2, 'home' => 3, 'salary' => 4, 'personal' => 5, 'realestate' => 6];
$target_id = $type_map[$type] ?? 1;


try {
    $db = (new Database())->connect('LOAN');

    // SELECT personal info and JOIN motor_loans if type is motor to get 'type' (3-WHEELS, etc)
    $sql = "SELECT l.loan_id, l.reference_number, l.pn_date, 
                   CONCAT(l.first_name, ' ', l.last_name) AS full_name";
    
    if ($type === 'motor') {
        $sql .= ", m.type AS wheel_type ";
        $sql .= " FROM loans l LEFT JOIN motor_loans m ON l.loan_id = m.loan_id ";
    } else {
        $sql .= " FROM loans l ";
    }
    
    $sql .= " WHERE l.loan_type_id = :type_id ORDER BY l.loan_id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute(['type_id' => $target_id]);
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($loans) {
        foreach ($loans as $loan) {
            echo renderLoanRow($loan, $type);
        }
    } else {
        $colspan = ($type === 'motor') ? 4 : 3;
        echo "<tr><td colspan='$colspan' class='px-6 py-12 text-center text-gray-400 text-sm italic'>No records found.</td></tr>";
    }
} catch (Exception $e) {
    echo "<tr><td colspan='4' class='text-red-500'>Error: " . $e->getMessage() . "</td></tr>";
}

// Helper function to keep code clean
function renderLoanRow($loan, $type) {
    $date = date("d/m/Y", strtotime($loan['pn_date']));
    $wheelCell = ($type === 'motor') ? "<td class='px-6 py-4 text-sm font-bold'>".htmlspecialchars($loan['wheel_type'])."</td>" : "";
    
    return "
    <tr onclick=\"viewAmortization('{$loan['loan_id']}', 'primary')\" class='hover:bg-pink-50 transition-colors cursor-pointer'>
        <td class='px-6 py-4 text-sm text-gray-600'>$date</td>
        <td class='px-6 py-4 text-sm font-semibold text-gray-700'>".htmlspecialchars($loan['full_name'])."</td>
        <td class='px-6 py-4 text-sm font-mono text-gray-500'>".htmlspecialchars($loan['reference_number'])."</td>
        $wheelCell
    </tr>";
}