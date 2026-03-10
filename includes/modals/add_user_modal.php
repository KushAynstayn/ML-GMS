<div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-2xl">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Add New User</h3>

            <button onclick="closeAddUserModal()" class="text-gray-500 hover:text-black text-xl">&times;</button>
        </div>

        <form action="../actions/add_user_action.php" method="POST" class="space-y-4">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="md:col-span-2">
                <label class="text-xs font-bold text-gray-500 uppercase ml-4">Account Type</label>

                <select name="user_type" class="w-full px-6 py-3 border border-gray-300 rounded-full">
                <option value="user">User</option>
                <option value="admin">Admin</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <input type="text" name="idNum" required placeholder="ID Number"
                class="w-full px-6 py-3 border border-gray-300 rounded-full">
                </div>

                <input type="text" name="fname" required placeholder="First Name"
                class="w-full px-6 py-3 border border-gray-300 rounded-full">

                <input type="text" name="mname" placeholder="Middle Name"
                class="w-full px-6 py-3 border border-gray-300 rounded-full">

                <input type="text" name="lname" placeholder="Last Name"
                class="w-full px-6 py-3 border border-gray-300 rounded-full">

                <input type="text" name="email" required placeholder="Username / Email"
                class="w-full px-6 py-3 border border-gray-300 rounded-full">

            </div>

            <div class="bg-yellow-50 border border-yellow-300 p-4 rounded-xl text-sm text-yellow-700 text-center">
                Default password will be assigned automatically:<br>
                <b>Mlinc12345!@</b><br><br>
                User will be required to change it after first login.
            </div>

            <div class="flex justify-center pt-4 gap-2">

                <button type="submit"
                    name="submit"
                    class="px-4 py-2 rounded-lg bg-red-600 text-white">

                    Create User

                </button>

            </div>

        </form>

    </div>
</div>