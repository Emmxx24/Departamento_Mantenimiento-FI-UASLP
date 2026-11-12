// Sweet alert de confirmación al querer eliminar/desactivar una unidad de medida.
// Si tiene materiales o requisiciones asociadas, se desactiva. Si no, se elimina permanentemente.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-eliminar-unidad');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;

    const tieneMateriales = form.getAttribute('data-tiene-materiales') === '1';
    const nombre = form.getAttribute('data-nombre') || 'esta unidad';
    const abreviatura = form.getAttribute('data-abreviatura') || '';
    const labelCompleto = abreviatura ? `"${nombre} (${abreviatura})"` : `"${nombre}"`;

    if (tieneMateriales) {
        Swal.fire({
            title: '¿Desactivar Unidad de Medida?',
            text: `${labelCompleto} tiene materiales en inventario o registros de requisición asociados. Se desactivará para preservar el historial, pero podrás reactivarla en cualquier momento.`,
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
            title: '¿Eliminar Unidad Permanentemente?',
            text: `${labelCompleto} no tiene materiales ni requisiciones asociadas. Se eliminará permanentemente de la base de datos. Esta acción no se puede deshacer.`,
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

// Sweet alert de confirmación al querer reactivar una unidad.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-reactivar-unidad');
    if (!btn) return;

    const form = btn.closest('form');
    if (!form) return;

    Swal.fire({
        title: '¿Reactivar Unidad de Medida?',
        text: 'La unidad volverá a estar disponible para el inventario y formatos de requisición.',
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
