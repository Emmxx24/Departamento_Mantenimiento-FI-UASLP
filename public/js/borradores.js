//Sweet alert de confirmación al querer eliminar un borrador.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-eliminar-borrador');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;

    Swal.fire({
        title: '¿Eliminar borrador?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});