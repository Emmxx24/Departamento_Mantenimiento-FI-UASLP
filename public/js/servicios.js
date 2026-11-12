// Sweet alert de confirmación al querer eliminar/desactivar un servicio.
// Si tiene tickets asociados, solo se desactiva. Si no, se elimina permanentemente.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-eliminar-servicio');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;

    const tieneTickets = form.getAttribute('data-tiene-tickets') === '1';
    const nombre = form.getAttribute('data-nombre') || 'este servicio';

    if (tieneTickets) {
        Swal.fire({
            title: '¿Desactivar Servicio?',
            text: `"${nombre}" tiene tickets asociados en el sistema. Se desactivará para preservar el historial, pero podrás reactivarlo en cualquier momento.`,
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
            title: '¿Eliminar Servicio Permanentemente?',
            text: `"${nombre}" no tiene ningún ticket asociado. Se eliminará permanentemente de la base de datos. Esta acción no se puede deshacer.`,
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

// Sweet alert de confirmación al querer reactivar un servicio.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-reactivar-servicio');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;

    Swal.fire({
        title: '¿Reactivar Servicio?',
        text: 'El servicio volverá a estar disponible para crear nuevos tickets.',
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