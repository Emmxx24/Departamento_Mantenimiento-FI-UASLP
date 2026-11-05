// SweetAlert2 de confirmación al eliminar/desactivar un material.
// Si tiene requisiciones asociadas, se desactiva para mantener integridad referencial.
// Si no tiene registros asociados, se elimina permanentemente.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-eliminar-material');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;

    const tieneRequisiciones = form.getAttribute('data-tiene-requisiciones') === '1';
    const nombre = form.getAttribute('data-nombre') || 'este material';

    if (tieneRequisiciones) {
        Swal.fire({
            title: '¿Desactivar material?',
            text: `"${nombre}" tiene requisiciones asociadas en tickets. Se desactivará para preservar la integridad de los datos, pero podrás reactivarlo en cualquier momento.`,
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
            title: '¿Eliminar material permanentemente?',
            text: `"${nombre}" no tiene requisiciones asociadas en ningún ticket. Se eliminará permanentemente del inventario. Esta acción no se puede deshacer.`,
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

// SweetAlert2 de confirmación al reactivar un material.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-reactivar-material');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;

    const nombre = form.getAttribute('data-nombre') || 'el material';

    Swal.fire({
        title: '¿Reactivar material?',
        text: `"${nombre}" volverá a estar disponible en el inventario activo.`,
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
