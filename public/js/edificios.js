//Sweet alert de confirmación al querer eliminar un edificio.
//Diferencia entre eliminar (sin tickets) y desactivar (con tickets).
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-eliminar-edificio');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;

    const tieneTickets = form.getAttribute('data-tiene-tickets') === '1';
    const nombre = form.getAttribute('data-nombre') || 'este edificio';

    if (tieneTickets) {
        Swal.fire({
            title: '¿Desactivar Edificio?',
            text: `"${nombre}" tiene tickets asociados en el sistema. Se desactivará para preservar el historial, pero podrás reactivarlo después.`,
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
            title: '¿Eliminar Edificio Permanentemente?',
            text: `Se eliminará permanentemente "${nombre}" con todos sus pisos y aulas. Esta acción no se puede deshacer.`,
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

//Sweet alert de confirmación al querer reactivar un edificio.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-reactivar-edificio');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;

    Swal.fire({
        title: '¿Reactivar Edificio?',
        text: 'Se reactivará el edificio junto con todos sus pisos y aulas.',
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
