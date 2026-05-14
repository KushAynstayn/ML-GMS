<div id="addUserModal" onclick="closeAddUserModal()" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div onclick="event.stopPropagation()" class="bg-white rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden animate-bounce-in">
        <div class="bg-gray-50 p-4 border-b border-gray-200">
            <h3 class="text-gray-800 font-bold text-lg">Add New <span class="text-[#D50000]">User</span></h3>
        </div>

        <form action="../actions/add_user_action.php" method="POST" class="p-4 space-y-3">
            <div>
                <select name="user_type" class="w-full border border-gray-200 p-2 rounded-lg text-sm outline-none cursor-pointer focus:ring-2 focus:ring-[#D50000]/10 transition-all">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div>
                <input type="text" name="idNum" id="addIdNumber" required placeholder="Enter ID Number"
                    class="w-full border border-gray-200 p-2 rounded-lg text-sm focus:ring-2 focus:ring-[#D50000]/10 outline-none transition-all"
                    oninput="generateUsername()">
            </div>

            <div class="grid grid-cols-3 gap-2">
                <input type="text" name="fname" required placeholder="First Name"
                    class="w-full border border-gray-200 p-2 rounded-lg text-sm focus:ring-2 focus:ring-[#D50000]/10 outline-none transition-all">
                <input type="text" name="mname" placeholder="M.I (Optional)"
                    class="w-full border border-gray-200 p-2 rounded-lg text-sm focus:ring-2 focus:ring-[#D50000]/10 outline-none transition-all">
                <input type="text" name="lname" id="addLastName" required placeholder="Last Name"
                    class="w-full border border-gray-200 p-2 rounded-lg text-sm focus:ring-2 focus:ring-[#D50000]/10 outline-none transition-all"
                    oninput="generateUsername()">
            </div>

            <div>
                <input type="text" name="email" id="addUsername" required placeholder="Username" readonly
                    class="w-full border border-gray-200 p-2 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed focus:ring-2 focus:ring-[#D50000]/10 outline-none transition-all">
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-center">
                <p class="text-[11px] text-amber-800 font-medium">Default password will be assigned automatically:</p>
                <p class="text-sm font-bold text-amber-900 mt-0.5">Mlinc12345!@</p>
                <p class="text-[10px] text-amber-700 mt-1 italic">User will be required to change it after first login.</p>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="closeAddUserModal()"
                    class="flex-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-600 text-sm font-bold hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" name="submit"
                    class="flex-1 px-4 py-2 bg-[#D50000] text-white rounded-lg text-sm font-bold hover:bg-[#B70000] shadow-md transition-all active:scale-95">
                    Create User

                </button>

            </div>

        </form>

    </div>
</div>