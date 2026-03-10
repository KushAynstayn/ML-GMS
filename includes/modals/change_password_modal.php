<div id="changePasswordModal" class="change-password-modal" style="display: none;">
   <div class="change-password-modal-content">
      <div class="text-center mb-6">
         <h3 class="text-3xl font-bold text-gray-800">Create a New Password</h3>
         <h6 class="text-base text-gray-500 mt-2">You are using the default password. Please change it to continue.</h6>
      </div>

      <form action="../actions/change_password_action.php" method="post" onsubmit="return validatePasswordForm()" class="space-y-6">
         <div class="input-container relative border-b-2 border-gray-200 focus-within:border-red-600 transition-all">
            <input type="password" id="newPassword" name="new_password" required
                   class="block w-full px-0 py-2 bg-transparent text-gray-900 relative z-10 focus:outline-none peer">
            <label class="absolute left-0 top-2 text-gray-500 transition-all peer-focus:-top-4 peer-focus:text-xs peer-focus:text-red-600 peer-valid:-top-4 peer-valid:text-xs">
               New Password
            </label>
         </div>

         <div id="passwordRequirements" class="text-xs space-y-1 text-gray-600 bg-gray-50 border border-gray-200 rounded-xl p-3">
            <p class="font-semibold text-gray-700 mb-1">Password must contain:</p>
            <p id="reqLength" class="text-red-500">• At least 8 characters</p>
            <p id="reqUpper" class="text-red-500">• At least 1 uppercase letter</p>
            <p id="reqLower" class="text-red-500">• At least 1 lowercase letter</p>
            <p id="reqNumber" class="text-red-500">• At least 1 number</p>
            <p id="reqSpecial" class="text-red-500">• At least 1 special character</p>
         </div>

         <div class="input-container relative border-b-2 border-gray-200 focus-within:border-red-600 transition-all">
            <input type="password" id="confirmPassword" name="confirm_password" required
                   class="block w-full px-0 py-2 bg-transparent text-gray-900 relative z-10 focus:outline-none peer">
            <label class="absolute left-0 top-2 text-gray-500 transition-all peer-focus:-top-4 peer-focus:text-xs peer-focus:text-red-600 peer-valid:-top-4 peer-valid:text-xs">
               Confirm Password
            </label>

            <p id="passwordMatchMessage" class="text-xs mt-2"></p>
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
        padding: 2.5rem;
        border-radius: 1.5rem;
        width: 100%;
        max-width: 460px;
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