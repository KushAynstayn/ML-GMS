<div id="addUserModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate-bounce-in">
        <div class="bg-gray-50 p-6 flex justify-between items-center border-b border-gray-200">
            <h3 class="text-gray-800 font-bold text-xl">Add New <span class="text-[#D50000]">User</span></h3>
            <button onclick="closeAddUserModal()" class="text-gray-400 hover:text-gray-600 text-2xl transition-colors">&times;</button>
        </div>
        
        <form action="../actions/add_user_action.php" method="POST" class="p-6 space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <select name="user_type" class="w-full border border-gray-200 p-2.5 rounded-lg outline-none cursor-pointer focus:ring-2 focus:ring-[#D50000]/10 transition-all">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="col-span-2">
                    <input type="text" name="idNum" required placeholder="Enter ID Number" 
                        class="w-full border border-gray-200 p-2.5 rounded-lg focus:ring-2 focus:ring-[#D50000]/10 outline-none transition-all">
                </div>

                <div>
                    <input type="text" name="fname" required placeholder="First Name" 
                        class="w-full border border-gray-200 p-2.5 rounded-lg focus:ring-2 focus:ring-[#D50000]/10 outline-none transition-all">
                </div>
                <div>
                    <input type="text" name="mname" placeholder="Middle Name (Optional)" 
                        class="w-full border border-gray-200 p-2.5 rounded-lg focus:ring-2 focus:ring-[#D50000]/10 outline-none transition-all">
                </div>
                
                <div class="col-span-1">
                    <input type="text" name="lname" required placeholder="Last Name" 
                        class="w-full border border-gray-200 p-2.5 rounded-lg focus:ring-2 focus:ring-[#D50000]/10 outline-none transition-all">
                </div>

                <div class="col-span-1">
                    <input type="text" name="email" required placeholder="username@example.com" 
                        class="w-full border border-gray-200 p-2.5 rounded-lg focus:ring-2 focus:ring-[#D50000]/10 outline-none transition-all">
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
                <p class="text-xs text-amber-800 font-medium">Default password will be assigned automatically:</p>
                <p class="text-sm font-bold text-amber-900 mt-1">Mlinc12345!@</p>
                <p class="text-[10px] text-amber-700 mt-2 italic">User will be required to change it after first login.</p>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeAddUserModal()" 
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" name="submit"
                    class="flex-1 px-4 py-3 bg-[#D50000] text-white rounded-xl font-bold hover:bg-[#B70000] shadow-md transition-all active:scale-95">
                    Create User

                </button>

            </div>

        </form>

    </div>
</div>