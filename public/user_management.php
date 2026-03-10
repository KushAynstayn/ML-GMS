<?php
require_once __DIR__ . '/../includes/init.php';

if ($_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/dashboard.php");
    exit();
}

try {
    // Sorted by date_created DESC (Newest to Oldest)
    $stmt = $loanConn->prepare("SELECT * FROM users ORDER BY date_created DESC");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $users = [];
}
?>

<?php include('../includes/header.php'); ?>

<div class="flex h-screen overflow-hidden">
    <?php include('../includes/sidebar.php'); ?>

    <main class="flex-1 bg-gray-50 p-8 overflow-y-auto animate-content h-full">
        <header class="mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">User <span class="text-[#D50000]">Management</span></h2>
            <p class="text-gray-500 font-medium mt-2 mb-8">View, edit, and track system users.</p>
            <div class="flex flex-wrap gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex-1 min-w-[200px] relative">
                    <input type="text" id="searchInput" placeholder="Search by name, email, or ID..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D50000]/20">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <div class="flex shrink-0 bg-gray-100 p-1 rounded-lg border border-gray-200">
                    <button onclick="setDateMode('single')" id="btnSingle" class="px-3 py-1.5 text-xs font-bold uppercase rounded-md transition-all bg-white text-[#D50000] shadow-sm">Single Date</button>
                    <button onclick="setDateMode('range')" id="btnRange" class="px-3 py-1.5 text-xs font-bold uppercase rounded-md transition-all text-gray-500 hover:text-gray-700">Select Range</button>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500 hover:border-gray-300 transition-colors">
                        <span id="dateLabel" class="text-xs font-bold uppercase">Date</span>
                        <input type="date" id="startDate" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer uppercase text-sm">
                    </div>
                    <div id="toDateContainer" class="hidden flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500 hover:border-gray-300 transition-colors">
                        <span class="text-xs font-bold uppercase">To</span>
                        <input type="date" id="endDate" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer uppercase text-sm">
                    </div>
                </div>
                
                <a href="../public/register_form.php" class="bg-[#D50000] hover:bg-[#B70000] text-white px-4 py-2 rounded-lg font-bold text-sm transition-colors shadow-sm">
                    + Add User
                </a>
            </div>
        </header>

        <div id="noRecordFound" class="hidden mb-4 p-4 bg-red-50 border border-red-100 rounded-lg flex items-center gap-3 text-[#D50000]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium">No records found matching your search.</span>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-x-auto border border-gray-100">
            <table class="w-full text-left" id="userTable">
                <thead class="bg-[#D50000]">
                    <tr>
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-white">ID Number</th>
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-white">Username</th>
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-white">Full Name</th>
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-white">Last Online</th>
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-white">Date Created</th>
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-white">Date Modified</th>
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-white">User Type</th>
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-white">Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100">
                    <?php foreach ($users as $user): 
                        $fullName = $user['first_name'] . ' ' . $user['last_name'];
                        $typeClass = ($user['user_type'] === 'admin') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700';
                        $statusClass = ($user['status'] === 'active') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                    ?>
                        <tr onclick='openEditModal(<?php echo json_encode($user); ?>)' class="hover:bg-red-50/50 transition-colors group cursor-pointer">
                            <td class="px-4 py-4 text-sm font-mono text-gray-500"><?php echo htmlspecialchars($user['id_number']); ?></td>
                            <td class="px-4 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-4 py-4 text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($fullName); ?></td>
                            <td class="px-4 py-4 text-sm text-gray-500"><?php echo $user['last_online'] ? date("m/d/Y H:i", strtotime($user['last_online'])) : 'Never'; ?></td>
                            <td class="px-4 py-4 text-sm text-gray-600"><?php echo date("m/d/Y", strtotime($user['date_created'])); ?></td>
                            <td class="px-4 py-4 text-sm text-gray-500"><?php echo $user['modified_date'] ? date("m/d/Y", strtotime($user['modified_date'])) : '---'; ?></td>
                            <td class="px-4 py-4 text-sm"><span class="px-2 py-1 rounded text-xs font-bold uppercase <?php echo $typeClass; ?>"><?php echo htmlspecialchars($user['user_type']); ?></span></td>
                            <td class="px-4 py-4 text-sm"><span class="px-2 py-1 rounded text-xs font-bold uppercase <?php echo $statusClass; ?>"><?php echo htmlspecialchars($user['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="bg-[#D50000] p-6 flex justify-between items-center">
            <h3 class="text-white font-bold text-xl">Edit User Details</h3>
            <button onclick="closeEditModal()" class="text-white/80 hover:text-white text-2xl">&times;</button>
        </div>
        <form action="../actions/update_user.php" method="POST" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">ID Number (Read Only)</label>
                <input type="text" name="id_number" id="edit_id" readonly class="w-full bg-gray-50 border border-gray-200 p-2 rounded-lg text-gray-500 cursor-not-allowed">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">First Name</label>
                    <input type="text" name="first_name" id="edit_fname" required class="w-full border border-gray-200 p-2 rounded-lg focus:ring-2 focus:ring-[#D50000]/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Last Name</label>
                    <input type="text" name="last_name" id="edit_lname" required class="w-full border border-gray-200 p-2 rounded-lg focus:ring-2 focus:ring-[#D50000]/20 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Username</label>
                <input type="text" name="email" id="edit_user" required class="w-full border border-gray-200 p-2 rounded-lg focus:ring-2 focus:ring-[#D50000]/20 outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">User Type</label>
                    <select name="user_type" id="edit_type" class="w-full border border-gray-200 p-2 rounded-lg outline-none cursor-pointer">
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                    <select name="status" id="edit_status" class="w-full border border-gray-200 p-2 rounded-lg outline-none cursor-pointer">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-600 font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-[#D50000] text-white rounded-lg font-bold hover:bg-[#B70000] shadow-md transition-colors">Save Changes</button>
            </div>
        </form>
    </div>
</div>


<?php if (isset($_GET['update']) && $_GET['update'] === 'error'): ?>
<div id="errorModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden border-t-4 border-[#D50000]">
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-red-100 text-[#D50000] rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Save Failed</h3>
            <p class="text-sm text-gray-600 mb-6 bg-gray-50 p-3 rounded-lg font-mono text-left break-words">
                <strong>Error Details:</strong><br>
                <?php echo htmlspecialchars($_GET['msg'] ?? 'Unknown database error occurred.'); ?>
            </p>
            <button onclick="this.parentElement.parentElement.parentElement.remove()" class="w-full py-2 bg-gray-800 text-white rounded-lg font-bold hover:bg-gray-900 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
<div id="successToast" class="fixed bottom-5 right-5 bg-green-600 text-white px-6 py-3 rounded-xl shadow-lg z-50 animate-bounce">
    ✅ Changes saved successfully!
    <script>setTimeout(() => document.getElementById('successToast').remove(), 3000);</script>
</div>
<?php endif; ?>

<script>
function openEditModal(user) {
    document.getElementById('edit_id').value = user.id_number;
    document.getElementById('edit_fname').value = user.first_name;
    document.getElementById('edit_lname').value = user.last_name;
    
    document.getElementById('edit_user').value = user.email; 
    
    document.getElementById('edit_type').value = user.user_type;
    document.getElementById('edit_status').value = user.status;
    
    const modal = document.getElementById('editModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}


function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

let dateMode = 'single';

function setDateMode(mode) {
    dateMode = mode;
    const toContainer = document.getElementById('toDateContainer');
    const dateLabel = document.getElementById('dateLabel');
    const btnSingle = document.getElementById('btnSingle');
    const btnRange = document.getElementById('btnRange');

    if (mode === 'single') {
        toContainer.classList.add('hidden');
        dateLabel.innerText = 'Date';
        btnSingle.classList.add('bg-white', 'text-[#D50000]', 'shadow-sm');
        btnRange.classList.remove('bg-white', 'text-[#D50000]', 'shadow-sm');
        document.getElementById('endDate').value = ""; 
    } else {
        toContainer.classList.remove('hidden');
        dateLabel.innerText = 'From';
        btnRange.classList.add('bg-white', 'text-[#D50000]', 'shadow-sm');
        btnSingle.classList.remove('bg-white', 'text-[#D50000]', 'shadow-sm');
    }
    filterTable(); 
}

function filterTable() {
    const searchText = document.getElementById('searchInput').value.toUpperCase();
    const startDateVal = document.getElementById('startDate').value;
    const endDateVal = document.getElementById('endDate').value;
    const rows = document.querySelectorAll('#tableBody tr');
    let hasMatch = false;

    rows.forEach(row => {
        const idText = row.cells[0].textContent.toUpperCase();
        const userText = row.cells[1].textContent.toUpperCase();
        const nameText = row.cells[2].textContent.toUpperCase();
        const dateCreatedStr = row.cells[4].textContent.trim(); 

        const [m, d, y] = dateCreatedStr.split('/');
        const rowDate = new Date(y, m - 1, d);
        rowDate.setHours(0, 0, 0, 0);

        const filterStart = startDateVal ? new Date(startDateVal) : null;
        if(filterStart) filterStart.setHours(0,0,0,0);

        const filterEnd = endDateVal ? new Date(endDateVal) : null;
        if(filterEnd) filterEnd.setHours(0,0,0,0);

        let dateMatch = true;
        if (dateMode === 'single' && filterStart) {
            dateMatch = rowDate.getTime() === filterStart.getTime();
        } else if (dateMode === 'range' && filterStart && filterEnd) {
            dateMatch = rowDate >= filterStart && rowDate <= filterEnd;
        }

        const textMatch = idText.includes(searchText) || 
                        userText.includes(searchText) || 
                        nameText.includes(searchText);

        if (dateMatch && textMatch) {
            row.style.display = "";
            hasMatch = true;
        } else {
            row.style.display = "none";
        }
    });

    document.getElementById('noRecordFound').classList.toggle('hidden', hasMatch);
}

document.getElementById('searchInput').addEventListener('keyup', filterTable);
</script>