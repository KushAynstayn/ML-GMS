<div id="changePasswordModal" class="change-password-modal" style="display: none;">
   <div class="change-password-modal-content">
      <div class="text-center mb-4">
         <h3 class="text-lg font-bold text-gray-800">Create a New Password</h3>
         <p class="text-xs text-gray-500 mt-1">You are using the default password.</p>
         <p class="text-xs text-gray-500 mt-0">Please change it to continue.</p>
      </div>

      <form action="../actions/change_password_action.php" method="post" onsubmit="return validatePasswordForm()" class="space-y-3">
         <div class="input-container relative border-b-2 border-gray-200 focus-within:border-red-600 transition-all">
            <input type="password" id="newPassword" name="new_password" required
                   class="block w-full px-0 py-1.5 text-sm bg-transparent text-gray-900 relative z-10 focus:outline-none peer">
            <label class="absolute left-0 top-1.5 text-gray-500 text-sm transition-all peer-focus:-top-3 peer-focus:text-[10px] peer-focus:text-red-600 peer-valid:-top-3 peer-valid:text-[10px]">
               New Password
            </label>
         </div>

         <div id="passwordRequirements" class="text-[10px] space-y-0.5 text-gray-600 bg-gray-50 border border-gray-200 rounded-lg p-2">
            <p class="font-semibold text-gray-700 mb-0.5">Password must contain:</p>
            <p id="reqLength" class="text-red-500">• At least 8 characters</p>
            <p id="reqUpper" class="text-red-500">• At least 1 uppercase letter</p>
            <p id="reqLower" class="text-red-500">• At least 1 lowercase letter</p>
            <p id="reqNumber" class="text-red-500">• At least 1 number</p>
            <p id="reqSpecial" class="text-red-500">• At least 1 special character</p>
         </div>

         <div class="input-container relative border-b-2 border-gray-200 focus-within:border-red-600 transition-all">
            <input type="password" id="confirmPassword" name="confirm_password" required
                   class="block w-full px-0 py-1.5 text-sm bg-transparent text-gray-900 relative z-10 focus:outline-none peer">
            <label class="absolute left-0 top-1.5 text-gray-500 text-sm transition-all peer-focus:-top-3 peer-focus:text-[10px] peer-focus:text-red-600 peer-valid:-top-3 peer-valid:text-[10px]">
               Confirm Password
            </label>

            <p id="passwordMatchMessage" class="text-[10px] mt-1"></p>
         </div>

         <div class="flex justify-center pt-2">
            <button type="submit" name="newPass"
                    class="bg-red-600 text-white px-6 py-2 rounded-full text-sm font-bold shadow-md hover:bg-red-700 transition">
               Change Password
            </button>
         </div>
      </form>
   </div>
</div>

<style>
    .change-password-modal {
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
    }

    .change-password-modal-content {
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 1rem;
        width: 100%;
        max-width: 380px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const password = document.getElementById("newPassword");
    const confirmPassword = document.getElementById("confirmPassword");
    const message = document.getElementById("passwordMatchMessage");

    const reqLength = document.getElementById("reqLength");
    const reqUpper = document.getElementById("reqUpper");
    const reqLower = document.getElementById("reqLower");
    const reqNumber = document.getElementById("reqNumber");
    const reqSpecial = document.getElementById("reqSpecial");

    function setRequirement(el, valid) {
        el.classList.remove("text-red-500", "text-green-500");
        el.classList.add(valid ? "text-green-500" : "text-red-500");
    }

    function validateStrength() {
        const value = password.value;

        const hasLength = value.length >= 8;
        const hasUpper = /[A-Z]/.test(value);
        const hasLower = /[a-z]/.test(value);
        const hasNumber = /[0-9]/.test(value);
        const hasSpecial = /[^A-Za-z0-9]/.test(value);

        setRequirement(reqLength, hasLength);
        setRequirement(reqUpper, hasUpper);
        setRequirement(reqLower, hasLower);
        setRequirement(reqNumber, hasNumber);
        setRequirement(reqSpecial, hasSpecial);

        return hasLength && hasUpper && hasLower && hasNumber && hasSpecial;
    }

    function checkPasswordMatch() {
        if (!confirmPassword.value.length) {
            message.textContent = "";
            return false;
        }

        if (password.value === confirmPassword.value) {
            message.textContent = "✔ Passwords match";
            message.style.color = "green";
            return true;
        } else {
            message.textContent = "✖ Passwords do not match";
            message.style.color = "red";
            return false;
        }
    }

    if (password) {
        password.addEventListener("keyup", function() {
            validateStrength();
            checkPasswordMatch();
        });
    }

    if (confirmPassword) {
        confirmPassword.addEventListener("keyup", checkPasswordMatch);
    }
});

function validatePasswordForm() {
    const password = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    const hasLength = password.length >= 8;
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[^A-Za-z0-9]/.test(password);

    if (!(hasLength && hasUpper && hasLower && hasNumber && hasSpecial)) {
        alert("Password must be at least 8 characters and include uppercase, lowercase, number, and special character.");
        return false;
    }

    if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return false;
    }

    return true;
}
</script>