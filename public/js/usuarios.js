/**
 * Lógica del Módulo de Gestión de Usuarios
 * DataTables, filtros rápidos, modal de cambio de rol con preservación de etiquetas,
 * confirmaciones SweetAlert2 e importación masiva desde Excel.
 */
$(document).ready(function () {
    const config = window.USUARIOS_CONFIG;
    if (!config) return;

    const baseUrl = config.baseUrl;
    const metricas = config.metricas;

    // 1. Manejador del envío del formulario de importación
    const formImportarExcel = document.getElementById('formImportarExcel');
    if (formImportarExcel) {
        formImportarExcel.addEventListener('submit', function () {
            const btnSubmit = document.getElementById('btnSubmitImport');
            const btnCancelar = document.getElementById('btnCancelarImport');
            const loadingState = document.getElementById('importLoadingState');

            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Importando...';
            }
            if (btnCancelar) {
                btnCancelar.disabled = true;
            }
            if (loadingState) {
                loadingState.classList.remove('d-none');
            }
        });
    }

    // 2. Reporte detallado de importación masiva Excel
    if (config.importResumen) {
        const resumen = config.importResumen;
        const totalLeidos = resumen.totalLeidos;
        const totalExito = resumen.totalExito;
        const totalOmitidos = resumen.totalOmitidos;
        const omitidos = resumen.omitidos;

        let tituloAlerta = 'Resumen de Importación';
        let iconoAlerta = 'info';

        if (totalExito > 0 && totalOmitidos === 0) {
            tituloAlerta = '¡Importación Exitosa!';
            iconoAlerta = 'success';
        } else if (totalExito > 0 && totalOmitidos > 0) {
            tituloAlerta = 'Importación Parcial';
            iconoAlerta = 'warning';
        } else if (totalExito === 0 && totalOmitidos > 0) {
            tituloAlerta = 'Importación No Realizada';
            iconoAlerta = 'error';
        }

        let htmlContent = `
            <div class="text-start">
                <div class="row g-2 mb-3 text-center">
                    <div class="col-4">
                        <div class="p-2 border rounded-3 bg-light">
                            <span class="d-block text-muted small">Leídos</span>
                            <strong class="fs-5">${totalLeidos}</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded-3 bg-success-subtle text-success-emphasis">
                            <span class="d-block small">Importados</span>
                            <strong class="fs-5">${totalExito}</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded-3 bg-danger-subtle text-danger-emphasis">
                            <span class="d-block small">Omitidos</span>
                            <strong class="fs-5">${totalOmitidos}</strong>
                        </div>
                    </div>
                </div>
        `;

        if (totalOmitidos > 0) {
            htmlContent += `
                <p class="small text-muted mb-2 fw-semibold">
                    <i class="bi bi-exclamation-circle text-danger me-1"></i>Detalle de filas no importadas y motivo del rechazo:
                </p>
                <div style="max-height: 260px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px;">
                    <table class="table table-sm table-striped table-hover mb-0" style="font-size: 0.82rem;">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="text-center" style="width: 55px;">Fila</th>
                                <th>Clave / Usuario</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            omitidos.forEach(item => {
                htmlContent += `
                    <tr>
                        <td class="text-center fw-bold text-danger">${item.fila}</td>
                        <td>
                            <strong>${item.clave}</strong>
                            <div class="text-muted" style="font-size: 0.75rem;">${item.nombre}</div>
                        </td>
                        <td class="text-danger small">${item.motivo}</td>
                    </tr>
                `;
            });

            htmlContent += `
                        </tbody>
                    </table>
                </div>
            `;
        } else if (totalExito > 0) {
            htmlContent += `
                <div class="alert alert-success border-0 small mb-0">
                    <i class="bi bi-check-circle-fill me-1"></i> Todos los registros válidos del archivo Excel fueron dados de alta exitosamente en el sistema.
                </div>
            `;
        } else {
            htmlContent += `
                <div class="alert alert-warning border-0 small mb-0">
                    <i class="bi bi-info-circle me-1"></i> El archivo procesado no contiene registros válidos para importar.
                </div>
            `;
        }

        htmlContent += `</div>`;

        Swal.fire({
            title: tituloAlerta,
            html: htmlContent,
            icon: iconoAlerta,
            width: totalOmitidos > 0 ? '700px' : '520px',
            confirmButtonColor: '#004A98',
            confirmButtonText: 'Entendido'
        });
    }

    // 3. Alertas de errores de validación ($errors->any()) y fallos de sesión (session('failure'))
    if (config.validationError) {
        Swal.fire({
            title: 'Operación no permitida',
            text: config.validationError,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Entendido'
        });
    } else if (config.sessionFailure) {
        Swal.fire({
            title: 'Atención',
            text: config.sessionFailure,
            icon: 'warning',
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'Entendido'
        });
    }

    // 4. Inicialización de DataTable en español nativo
    const tabla = $('#tablaUsuarios').DataTable({
        language: {
            processing: "Procesando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ usuarios",
            info: "Mostrando _START_ a _END_ de _TOTAL_ usuarios",
            infoEmpty: "Mostrando 0 usuarios",
            infoFiltered: "(filtrado de _MAX_ usuarios en total)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron usuarios coincidentes",
            emptyTable: "No hay usuarios registrados en el sistema",
            paginate: {
                first: "Primero",
                previous: "Anterior",
                next: "Siguiente",
                last: "Último"
            }
        },
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        columnDefs: [
            { orderable: false, targets: 5 }
        ]
    });

    // 5. Filtros rápidos mediante extensión nativa de DataTables
    let filtroActivo = 'todos';

    $.fn.dataTable.ext.search.push(function (settings, data) {
        if (settings.sTableId && settings.sTableId !== 'tablaUsuarios') {
            return true;
        }
        if (filtroActivo === 'todos') {
            return true;
        }
        const rol = (data[3] ? data[3] : '').trim();
        const estado = (data[4] ? data[4] : '').trim();

        if (filtroActivo === 'Inactivo') {
            return estado.indexOf('Inactivo') !== -1;
        }
        return rol === filtroActivo;
    });

    $('.btn-filtro-tabla').on('click', function () {
        $('.btn-filtro-tabla').removeClass('active btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('active btn-primary');

        filtroActivo = $(this).data('filtro');
        tabla.draw();
    });

    // 6. Manejo del modal de cambio de rol (con preservación de data-label-original)
    const modalCambiarRol = document.getElementById('modalCambiarRol');
    if (modalCambiarRol) {
        modalCambiarRol.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            const id = btn.getAttribute('data-id');
            const nombre = btn.getAttribute('data-nombre');
            const rolActual = btn.getAttribute('data-rol');
            const usuarioActivo = btn.getAttribute('data-activo') === '1';
            const esUltimoSuperadmin = btn.getAttribute('data-es-ultimo-superadmin') === '1';

            const totalSuperadmins = metricas.totalSuperadmins;
            const totalAdmins = metricas.totalAdmins;
            const maxSuperadmins = metricas.maxSuperadmins;
            const maxAdmins = metricas.maxAdmins;

            const form = document.getElementById('formCambiarRol');
            form.action = `${baseUrl}/${id}/cambiar-rol`;

            document.getElementById('modalNombreUsuario').textContent = nombre;
            const selectRol = document.getElementById('selectRol');
            selectRol.value = rolActual;

            const avisoSuperadmin = document.getElementById('avisoUltimoSuperadmin');
            const avisoLimite = document.getElementById('avisoLimiteRol');
            const textoAvisoLimite = document.getElementById('textoAvisoLimite');
            const btnGuardar = document.getElementById('btnGuardarRol');

            if (avisoLimite) {
                avisoLimite.classList.add('d-none');
            }

            const optSuperadmin = document.getElementById('optSuperadmin');
            const optAdmin = document.getElementById('optAdmin');

            // Solo restringir por cupo activo si el usuario editado está activo
            const cupoLlenoSuperadmin = (usuarioActivo && rolActual !== 'Superadministrador' && totalSuperadmins >= maxSuperadmins);
            const cupoLlenoAdmin = (usuarioActivo && rolActual !== 'Administrador' && totalAdmins >= maxAdmins);

            if (optSuperadmin) {
                const labelBaseSuperadmin = optSuperadmin.getAttribute('data-label-original');
                optSuperadmin.disabled = cupoLlenoSuperadmin;
                optSuperadmin.textContent = cupoLlenoSuperadmin
                    ? `${labelBaseSuperadmin} [Cupo lleno: ${maxSuperadmins}/${maxSuperadmins}]`
                    : labelBaseSuperadmin;
            }

            if (optAdmin) {
                const labelBaseAdmin = optAdmin.getAttribute('data-label-original');
                optAdmin.disabled = cupoLlenoAdmin;
                optAdmin.textContent = cupoLlenoAdmin
                    ? `${labelBaseAdmin} [Cupo lleno: ${maxAdmins}/${maxAdmins}]`
                    : labelBaseAdmin;
            }

            if (esUltimoSuperadmin) {
                avisoSuperadmin.classList.remove('d-none');
                selectRol.disabled = true;
                btnGuardar.disabled = true;
            } else {
                avisoSuperadmin.classList.add('d-none');
                selectRol.disabled = false;
                btnGuardar.disabled = false;
            }

            selectRol.onchange = function () {
                const seleccion = selectRol.value;
                if (seleccion === 'Superadministrador' && cupoLlenoSuperadmin) {
                    avisoLimite.classList.remove('d-none');
                    textoAvisoLimite.textContent = `Ya existen ${maxSuperadmins} Superadministradores activos. No es posible asignar más.`;
                    btnGuardar.disabled = true;
                } else if (seleccion === 'Administrador' && cupoLlenoAdmin) {
                    avisoLimite.classList.remove('d-none');
                    textoAvisoLimite.textContent = `Ya existen ${maxAdmins} Administradores activos. No es posible asignar más.`;
                    btnGuardar.disabled = true;
                } else {
                    avisoLimite.classList.add('d-none');
                    btnGuardar.disabled = esUltimoSuperadmin;
                }
            };
        });
    }

    // 7. SweetAlert para Desactivar / Reactivar
    $(document).on('click', '.btn-toggle-usuario', function () {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        const activo = $(this).data('activo') == 1;
        const esUltimoSuperadmin = $(this).data('es-ultimo-superadmin') == 1;

        if (activo && esUltimoSuperadmin) {
            Swal.fire({
                title: 'Operación no permitida',
                text: 'No es posible desactivar al único Superadministrador activo del sistema.',
                icon: 'warning',
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Si va a reactivar, verificar cupos antes
        if (!activo) {
            const rol = $(this).data('rol');
            const totalSuperadmins = metricas.totalSuperadmins;
            const totalAdmins = metricas.totalAdmins;
            const maxSuperadmins = metricas.maxSuperadmins;
            const maxAdmins = metricas.maxAdmins;

            if (rol === 'Superadministrador' && totalSuperadmins >= maxSuperadmins) {
                Swal.fire({
                    title: 'Cupo máximo alcanzado',
                    text: `No es posible reactivar a "${nombre}" como Superadministrador porque ya se alcanzó el límite máximo de ${maxSuperadmins}.`,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            if (rol === 'Administrador' && totalAdmins >= maxAdmins) {
                Swal.fire({
                    title: 'Cupo máximo alcanzado',
                    text: `No es posible reactivar a "${nombre}" como Administrador porque ya se alcanzó el límite máximo de ${maxAdmins}.`,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
        }

        const titulo = activo ? '¿Desactivar usuario?' : '¿Reactivar usuario?';
        const texto = activo
            ? `La cuenta de "${nombre}" será dada de baja y no podrá acceder al sistema.`
            : `La cuenta de "${nombre}" volverá a estar activa y podrá acceder normalmente.`;
        const confirmText = activo ? 'Sí, desactivar' : 'Sí, reactivar';
        const confirmColor = activo ? '#f59e0b' : '#198754';

        Swal.fire({
            title: titulo,
            text: texto,
            icon: activo ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('formToggleActivo');
                form.action = `${baseUrl}/${id}/toggle-activo`;
                form.submit();
            }
        });
    });

    // 8. SweetAlert para Eliminar
    $(document).on('click', '.btn-eliminar-usuario', function () {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        const esUltimoSuperadmin = $(this).data('es-ultimo-superadmin') == 1;

        if (esUltimoSuperadmin) {
            Swal.fire({
                title: 'Operación no permitida',
                text: 'No es posible eliminar al único Superadministrador activo del sistema.',
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        Swal.fire({
            title: '¿Eliminar usuario permanentemente?',
            text: `¿Estás seguro de eliminar a "${nombre}"? Esta acción eliminará permanentemente al usuario de la base de datos si no cuenta con historial asociado.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('formEliminar');
                form.action = `${baseUrl}/${id}`;
                form.submit();
            }
        });
    });
});
