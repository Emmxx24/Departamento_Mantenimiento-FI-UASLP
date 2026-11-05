// Sweet alert de confirmación al querer eliminar o desactivar un lugar.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-eliminar-lugar');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;
    
    const tieneAulas = btn.getAttribute('data-tiene-aulas') === '1' || btn.getAttribute('data-tiene-aulas') === 'true';
    const nombre = btn.getAttribute('data-nombre') || 'este lugar';
    
    if (tieneAulas) {
        Swal.fire({
            title: '¿Desactivar Lugar?',
            text: `"${nombre}" está asignado a una o más aulas. Se desactivará para proteger los registros existentes, pero podrás reactivarlo cuando lo necesites.`,
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
            title: '¿Eliminar Lugar Permanentemente?',
            text: `"${nombre}" no está asociado a ninguna aula. Se eliminará permanentemente de la base de datos.`,
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

// Confirmación para reactivar un lugar
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-reactivar-lugar');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;

    Swal.fire({
        title: '¿Reactivar Lugar?',
        text: 'El lugar volverá a estar disponible para nuevas aulas.',
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
