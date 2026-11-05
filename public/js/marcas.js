// Sweet alert de confirmación al querer eliminar/desactivar una marca.
// Si tiene materiales asociados, solo se desactiva. Si no, se elimina permanentemente.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-eliminar-marca');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;

    const tieneMateriales = form.getAttribute('data-tiene-materiales') === '1';
    const nombre = form.getAttribute('data-nombre') || 'esta marca';

    if (tieneMateriales) {
        Swal.fire({
            title: '¿Desactivar Marca?',
            text: `"${nombre}" tiene materiales asociados en el inventario. Se desactivará para preservar el historial, pero podrás reactivarla en cualquier momento.`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    } else {
        Swal.fire({
            title: '¿Eliminar Marca Permanentemente?',
            text: `"${nombre}" no tiene ningún material asociado. Se eliminará permanentemente de la base de datos. Esta acción no se puede deshacer.`,
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
    }
});

// Sweet alert de confirmación al querer reactivar una marca.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-reactivar-marca');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;

    const nombre = form.getAttribute('data-nombre') || 'esta marca';

    Swal.fire({
        title: '¿Reactivar Marca?',
        text: `"${nombre}" volverá a estar disponible para el catálogo e inventario.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, reactivar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
