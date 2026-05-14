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
<?php include '../includes/modals/add_user_modal.php'; ?>

<style>
    /* Custom Scrollbar */
    .overflow-x-auto::-webkit-scrollbar {
        height: 8px;
    }
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f3f4f6;
    }
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #e5e7eb;
        border-radius: 4px;
    }

    /* --- ANIMATIONS --- */
    @keyframes subtle-jump {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    .hover-jump:hover {
        animation: subtle-jump 0.6s infinite ease-in-out;
    }

    /* Bounce Effect for Modals */
    @keyframes bounceIn {
        0% { transform: scale(0.9); opacity: 0; }
        60% { transform: scale(1.05); opacity: 1; }
        100% { transform: scale(1); }
    }
    .animate-bounce-in {
        animation: bounceIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    /* Piano Row Effect */
    #tableBody td {
        text-transform: uppercase;
        font-size: 0.7rem;
        padding: 0.5rem 0.75rem;
        white-space: nowrap;
    }

    #tableBody tr {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        z-index: 1;
    }

    #tableBody tr:hover {
        background-color: #fef2f2 !important; /* Very light red */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        z-index: 10;
    }

    /* Table Container Deep Shadow */
    .table-container-shadow {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow-x: hidden;
    }

    @keyframes fadeInSlide {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .row-fade-in {
        animation: fadeInSlide 0.3s ease-out forwards;
    }
</style>

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

                <div class="flex shrink-0 items-center gap-3">
                    <?php include '../includes/date_picker.php'; ?>
                </div>
                
                <button 
                    onclick="openAddUserModal()" 
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold text-sm transition-colors shadow-sm hover-jump"
                >
                    + Add User
                </button>
            </div>
        </header>

        <div class="bg-white rounded-xl table-container-shadow border border-gray-100">
            <table class="w-full text-left border-collapse" id="userTable">
                <thead class="bg-[#D50000]">
                    <tr>
                        <th class="px-3 py-3 text-[10px] font-bold uppercase tracking-wider text-white whitespace-nowrap">ID Number</th>
                        <th class="px-3 py-3 text-[10px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Username</th>
                        <th class="px-3 py-3 text-[10px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Full Name</th>
                        <th class="px-3 py-3 text-[10px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Last Online</th>
                        <th class="px-3 py-3 text-[10px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Date Created</th>
                        <th class="px-3 py-3 text-[10px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Date Modified</th>
                        <th class="px-3 py-3 text-[10px] font-bold uppercase tracking-wider text-white whitespace-nowrap">User Type</th>
                        <th class="px-3 py-3 text-[10px] font-bold uppercase tracking-wider text-white whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100">
                    <tr id="noRecordFound" class="hidden">
                        <td colspan="8" class="px-4 py-12 text-center text-sm text-gray-400 italic">No records found.</td>
                    </tr>
                    <?php foreach ($users as $user): 
                        $fullName = $user['first_name'] . ' ' . $user['last_name'];
                        $typeClass = ($user['user_type'] === 'admin') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700';
                        $statusClass = ($user['status'] === 'active') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                    ?>
                        <tr onclick='openEditModal(<?php echo json_encode($user); ?>)' class="cursor-pointer border-b border-gray-100">
                            <td class="px-3 py-2 text-[11px] font-mono text-gray-500 whitespace-nowrap"><?php echo htmlspecialchars($user['id_number']); ?></td>
                            <td class="px-3 py-2 text-[11px] text-gray-600 whitespace-nowrap"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-3 py-2 text-[11px] font-semibold text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($fullName); ?></td>
                            <td class="px-3 py-2 text-[11px] text-gray-500 whitespace-nowrap"><?php echo $user['last_online'] ? date("m/d/Y H:i", strtotime($user['last_online'])) : 'NEVER'; ?></td>
                            <td class="px-3 py-2 text-[11px] text-gray-600 whitespace-nowrap"><?php echo date("m/d/Y", strtotime($user['date_created'])); ?></td>
                            <td class="px-3 py-2 text-[11px] text-gray-500 whitespace-nowrap"><?php echo $user['modified_date'] ? date("m/d/Y", strtotime($user['modified_date'])) : '---'; ?></td>
                            <td class="px-3 py-2 text-[11px] whitespace-nowrap"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?php echo $typeClass; ?>"><?php echo htmlspecialchars($user['user_type']); ?></span></td>
                            <td class="px-3 py-2 text-[11px] whitespace-nowrap"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?php echo $statusClass; ?>"><?php echo htmlspecialchars($user['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="editModal" onclick="closeEditModal()" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden animate-bounce-in" onclick="event.stopPropagation()">
        <div class="bg-gray-50 p-4 border-b border-gray-200">
            <h3 class="text-gray-800 font-bold text-lg">User <span class="text-[#D50000]">Details</span></h3>
        </div>
        <form action="../actions/update_user.php" method="POST" class="p-4 space-y-2">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5">ID Number</label>
                <input type="text" name="id_number" id="edit_id" readonly
                    class="w-full bg-gray-50 border border-gray-200 p-2 rounded-lg text-sm text-gray-500 cursor-not-allowed">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5">Username</label>
                <input type="text" name="email" id="edit_user" readonly
                    class="w-full bg-gray-50 border border-gray-200 p-2 rounded-lg text-sm text-gray-500 cursor-not-allowed">
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5">First Name</label>
                    <input type="text" name="first_name" id="edit_fname" required
                        class="w-full border border-gray-200 p-2 rounded-lg text-sm focus:ring-2 focus:ring-[#D50000]/10 outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5">Middle</label>
                    <input type="text" name="middle_name" id="edit_mname" placeholder="Optional"
                        class="w-full border border-gray-200 p-2 rounded-lg text-sm focus:ring-2 focus:ring-[#D50000]/10 outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5">Last Name</label>
                    <input type="text" name="last_name" id="edit_lname" required
                        class="w-full border border-gray-200 p-2 rounded-lg text-sm focus:ring-2 focus:ring-[#D50000]/10 outline-none"
                        oninput="generateEditUsername()">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5">User Type</label>
                    <select name="user_type" id="edit_type" class="w-full border border-gray-200 p-2 rounded-lg text-sm outline-none cursor-pointer">
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5">Status</label>
                    <select name="status" id="edit_status" class="w-full border border-gray-200 p-2 rounded-lg text-sm outline-none cursor-pointer">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-600 text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-[#D50000] text-white rounded-lg text-sm font-bold hover:bg-[#B70000] shadow-md transition-colors">Save Changes</button>
            </div>
        </form>
    </div>
</div>


<?php if (isset($_GET['update']) && $_GET['update'] === 'error'): ?>
<div id="errorModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden border-t-4 border-[#D50000] animate-bounce-in">
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

function openAddUserModal() {
    document.getElementById("addUserModal").classList.remove("hidden");
    document.getElementById("addUserModal").classList.add("flex");
}

function closeAddUserModal() {
    document.getElementById("addUserModal").classList.remove("flex");
    document.getElementById("addUserModal").classList.add("hidden");
}

function generateUsername() {
    const lastName = document.getElementById('addLastName').value.trim();
    const idNumber = document.getElementById('addIdNumber').value.trim();
    const usernameField = document.getElementById('addUsername');

    const last4 = lastName.substring(0, 4).toLowerCase();
    usernameField.value = (last4 && idNumber) ? last4 + idNumber : '';
}

function generateEditUsername() {
    const lastName = document.getElementById('edit_lname').value.trim();
    const idNumber = document.getElementById('edit_id').value.trim();
    const usernameField = document.getElementById('edit_user');

    const last4 = lastName.substring(0, 4).toLowerCase();
    usernameField.value = (last4 && idNumber) ? last4 + idNumber : '';
}


function openEditModal(user) {
    document.getElementById('edit_id').value = user.id_number;
    document.getElementById('edit_user').value = user.email;
    document.getElementById('edit_fname').value = user.first_name;
    document.getElementById('edit_mname').value = user.middle_name || '';
    document.getElementById('edit_lname').value = user.last_name;
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

function filterTable() {
    const searchText = document.getElementById('searchInput').value.toUpperCase();
    const startDateVal = document.getElementById('startDate').value;
    const endDateVal = document.getElementById('endDate').value;
    const rows = document.querySelectorAll('#tableBody tr:not(#noRecordFound)');
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
        if (filterStart && filterEnd) {
            dateMatch = rowDate >= filterStart && rowDate <= filterEnd;
        } else if (filterStart) {
            dateMatch = rowDate.getTime() === filterStart.getTime();
        }

        const textMatch = idText.includes(searchText) || 
                        userText.includes(searchText) || 
                        nameText.includes(searchText);

        if (dateMatch && textMatch) {
            // Only add animation if it was previously hidden to create a "fade-in" effect on search
            if (row.style.display === "none") {
                row.classList.remove('row-fade-in');
                void row.offsetWidth; // Trigger reflow to restart animation
                row.classList.add('row-fade-in');
            }
            row.style.display = "";
            hasMatch = true;
        } else {
            row.style.display = "none";
            row.classList.remove('row-fade-in');
        }
    });

    document.getElementById('noRecordFound').classList.toggle('hidden', hasMatch);
}

document.getElementById('searchInput').addEventListener('keyup', filterTable);
</script>