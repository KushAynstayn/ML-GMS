document.addEventListener("DOMContentLoaded", function() {
    // 1. Handle Success Messages
    if (sessionSuccess) {
        Swal.fire({
            title: 'Success!',
            text: sessionSuccess,
            icon: 'success',
            confirmButtonColor: '#d33'
        });
    }

    // 2. Handle Error Messages
    if (sessionError) {
        Swal.fire({
            title: 'Error!',
            text: sessionError,
            icon: 'error',
            confirmButtonColor: '#d33'
        });
    }

    // 3. Handle Forced Password Change
    if (forceChange) {
        Swal.fire({
            title: "Change Password",
            text: "Your password is still set to default. Please update it.",
            icon: "warning",
            confirmButtonText: "OK",
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById("changePasswordModal").style.display = "block";
            }
        });
    }

    // 4. Modal Close Logic (ESC Key)
    document.addEventListener("keydown", function(event) {
        if (event.key === "Escape") {
            const modal = document.getElementById("changePasswordModal");
            if(modal) modal.style.display = "none";
        }
    });
});