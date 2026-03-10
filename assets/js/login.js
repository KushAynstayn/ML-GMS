document.addEventListener("DOMContentLoaded", function() {
    // Handle Success Messages
    if (sessionSuccess) {
        Swal.fire({
            title: 'Success!',
            text: sessionSuccess,
            icon: 'success',
            confirmButtonColor: '#d33'
        });
    }

    // Handle Error Messages
    if (sessionError) {
        Swal.fire({
            title: 'Error!',
            text: sessionError,
            icon: 'error',
            confirmButtonColor: '#d33'
        });
    }
});