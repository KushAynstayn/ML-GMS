<div id="changePasswordModal" class="change-password-modal" style="display: none;">
   <div class="change-password-modal-content">
      <div class="text-center mb-6">
         <h3 class="text-xl font-bold text-gray-800">Create a New Password</h3>
         <h6 class="text-xs italic text-red-500">(Press ESC to CLOSE)</h6>
      </div>

      <form action="../actions/change_password_action.php" method="post" class="space-y-6">
         <div class="input-container relative border-b-2 border-gray-200 focus-within:border-red-600 transition-all">
            <input type="password" name="new_password" required 
                   class="block w-full px-0 py-2 bg-transparent focus:outline-none peer">
            <label class="absolute left-0 top-2 text-gray-500 transition-all peer-focus:-top-4 peer-focus:text-xs peer-focus:text-red-600 peer-valid:-top-4 peer-valid:text-xs">
                New Password
            </label>
         </div>

         <div class="input-container relative border-b-2 border-gray-200 focus-within:border-red-600 transition-all">
            <input type="password" name="confirm_password" required 
                   class="block w-full px-0 py-2 bg-transparent focus:outline-none peer">
            <label class="absolute left-0 top-2 text-gray-500 transition-all peer-focus:-top-4 peer-focus:text-xs peer-focus:text-red-600 peer-valid:-top-4 peer-valid:text-xs">
                Confirm Password
            </label>
         </div>

         <div class="flex justify-center pt-4">
            <button type="submit" name="newPass" 
                    class="bg-red-600 text-white px-8 py-2 rounded-full font-bold shadow-md hover:bg-red-700 transition">
                Change Password
            </button>
         </div>
      </form>
   </div>
</div>

<style>
    /* Modal Background Overlay */
    .change-password-modal {
        position: fixed;
        z-index: 50;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Modal Content Box */
    .change-password-modal-content {
        background-color: #fff;
        padding: 2.5rem;
        border-radius: 1.5rem;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
</style>