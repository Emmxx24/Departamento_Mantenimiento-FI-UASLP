/**
 * Sweet alert para confirmar la creación de una requisición.
 * Solo se muestra si es la PRIMERA requisición del ticket (total === 0).
 * Informa que una vez creada la primera requisición, ya no se podrán modificar los datos principales del ticket.
 */
document.addEventListener('click', (e) => {
    const btn = e.target.closest('#btn-crear-requisicion');
    if (!btn) return;

    const total = parseInt(btn.dataset.total ?? '0', 10);

    // Si ya existe al menos una requisición, navegar directamente sin alert
    if (total > 0) {
        return;
    }

    e.preventDefault();
    const url = btn.getAttribute('href');

    Swal.fire({
        title: '¿Crear primera requisición?',
        text: 'Al crear una requisición ya no podrás modificar los datos del ticket. ¿Estás de acuerdo?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#004A98',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
});

/**
 * Sweet alert para eliminar requisición.
 * Si tiene POA vinculado, advierte explícitamente que también se borrará el POA.
 */
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-eliminar-requisicion');
    if (!btn) return;

    e.preventDefault();

    const form = btn.closest('form');
    if (!form) return;

    const tienePoa = btn.dataset.tienePoa === '1';
    const idPoa = btn.dataset.idPoa;

    let texto = 'Esta acción no se puede deshacer.';
    if (tienePoa) {
        texto = 'Esta requisición tiene un POA asociado. Si la eliminas, también se eliminará el POA vinculado de forma permanente.';
    }

    Swal.fire({
        title: '¿Eliminar requisición?',
        text: texto,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: tienePoa ? 'Sí, eliminar requisición y POA' : 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});