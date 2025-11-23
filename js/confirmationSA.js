const themedSwal = Swal.mixin({
    background: '#111827',
    color: '#e5e7eb',
    confirmButtonColor: '#22c55e', // accent
    cancelButtonColor: '#6b7280',
    iconColor: '#fbbf24',
    focusConfirm: false,
});

// Helper to fire themed alerts elsewhere
function fireThemed(options) {
    return themedSwal.fire(options);
}

function confirmDelete(event) {
    event.preventDefault(); // Prevent form submission
    const form = event.target;

    themedSwal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}

function confirmCancel(event) {
    event.preventDefault(); // Prevent form submission
    const form = event.target;

    themedSwal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        reverseButtons: false,  /* Prevents double-clicking */
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
