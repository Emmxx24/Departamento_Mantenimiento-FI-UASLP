document.addEventListener('DOMContentLoaded', function () {
    const accordion = document.getElementById('accordionPisos');
    const inputCantidad = document.getElementById('cantidadPisos');
    const btnSumar = document.getElementById('btnSumarPiso');
    const btnRestar = document.getElementById('btnRestarPiso');
    const lugares = window.lugaresDisponibles || [];
    const pisosIniciales = window.pisosIniciales || [];

    /**
     * Genera el nombre legible de un piso a partir de su número.
     */
    function nombrePiso(num) {
        return num === 0 ? 'Planta baja' : 'Piso ' + num;
    }

    /**
     * Genera el HTML del select de tipos de lugar (solo lugares activos).
     */
    function selectLugar(indicePiso, indiceAula, valorSeleccionado) {
        let options = '<option value="" disabled ' + (!valorSeleccionado ? 'selected' : '') + '>Tipo de Lugar</option>';
        lugares.forEach(function (lugar) {
            const selected = (valorSeleccionado && valorSeleccionado == lugar.id_lugar) ? 'selected' : '';
            options += '<option value="' + lugar.id_lugar + '" ' + selected + '>' + lugar.nombre + '</option>';
        });

        return '<select class="form-select form-select-sm rounded-3 select-lugar-aula" ' +
            'name="pisos[' + indicePiso + '][aulas][' + indiceAula + '][id_lugar]" required>' +
            options + '</select>';
    }

    /**
     * Genera el HTML de una fila de aula activa.
     */
    function filaAula(indicePiso, indiceAula, aula) {
        const nombre = aula ? (aula.nombre || '') : '';
        const idLugar = aula ? (aula.id_lugar || '') : '';
        const idAula = aula && aula.id_aula ? aula.id_aula : '';
        const tieneTickets = aula && (aula.tiene_tickets === 1 || aula.tiene_tickets === '1') ? '1' : '0';
        const lugarActivo = aula && typeof aula.lugar_activo !== 'undefined' ? aula.lugar_activo : 1;

        let hiddenId = '';
        if (idAula) {
            hiddenId = '<input type="hidden" name="pisos[' + indicePiso + '][aulas][' + indiceAula + '][id_aula]" value="' + idAula + '">';
        }

        return '<div class="row g-2 align-items-center mb-2 fila-aula" ' +
            'data-id-aula="' + idAula + '" ' +
            'data-nombre="' + nombre + '" ' +
            'data-id-lugar="' + idLugar + '" ' +
            'data-lugar-activo="' + lugarActivo + '" ' +
            'data-tiene-tickets="' + tieneTickets + '">' +
            hiddenId +
            '<div class="col">' +
            '<input type="text" class="form-control form-control-sm rounded-3 input-nombre-aula" ' +
            'name="pisos[' + indicePiso + '][aulas][' + indiceAula + '][nombre]" ' +
            'value="' + nombre + '" placeholder="Nombre del aula" required>' +
            '</div>' +
            '<div class="col-auto" style="min-width: 150px;">' +
            selectLugar(indicePiso, indiceAula, idLugar) +
            '</div>' +
            '<div class="col-auto d-flex gap-1">' +
            '<button type="button" class="btn btn-outline-warning btn-sm btn-desactivar-aula" title="Desactivar / Clausurar temporalmente">' +
            '<i class="bi bi-pause-circle"></i>' +
            '</button>' +
            '<button type="button" class="btn btn-outline-danger btn-sm btn-eliminar-aula" title="Eliminar aula">' +
            '<i class="bi bi-trash3"></i>' +
            '</button>' +
            '</div>' +
            '</div>';
    }

    /**
     * Genera el HTML de un ítem en la lista de aulas inactivas de un piso.
     */
    function itemAulaInactiva(indicePiso, aula) {
        const idAula = aula.id_aula || '';
        const nombre = aula.nombre || 'Aula';
        const idLugar = aula.id_lugar || '';
        const lugarNombre = aula.lugar_nombre || 'Sin tipo';
        const lugarActivo = (typeof aula.lugar_activo !== 'undefined' && parseInt(aula.lugar_activo) === 1) ? 1 : 0;
        const tieneTickets = (aula.tiene_tickets === 1 || aula.tiene_tickets === '1') ? 1 : 0;

        let badgeLugar = lugarActivo === 0
            ? '<span class="badge bg-danger ms-1 small"><i class="bi bi-exclamation-octagon me-1"></i>Tipo de lugar desactivado</span>'
            : '';

        let botonEliminar = tieneTickets === 1
            ? '<span class="badge bg-secondary rounded-pill py-2 px-3 small text-white" title="No se puede eliminar permanentemente porque tiene tickets asociados"><i class="bi bi-lock-fill me-1"></i>En tickets</span>'
            : '<button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-definitivo-aula rounded-pill" ' +
            'data-id="' + idAula + '" data-nombre="' + nombre + '" data-piso="' + indicePiso + '" title="Eliminar permanentemente">' +
            '<i class="bi bi-trash3 me-1"></i>Eliminar</button>';

        return '<li class="list-group-item d-flex justify-content-between align-items-center bg-light text-muted border rounded-3 mb-2 shadow-sm item-aula-inactiva" id="aulaInactiva-' + idAula + '">' +
            (idAula ? '<input type="hidden" name="pisos[' + indicePiso + '][aulas_inactivas][]" value="' + idAula + '" class="input-aula-inactiva-id">' : '') +
            '<div>' +
            '<i class="bi bi-door-closed text-secondary me-2"></i><strong>' + nombre + '</strong> ' +
            '<small class="text-secondary ms-1">(' + lugarNombre + ')</small>' +
            badgeLugar +
            '</div>' +
            '<div class="d-flex gap-2 align-items-center">' +
            '<button type="button" class="btn btn-sm btn-success text-white btn-reactivar-aula fw-bold rounded-pill shadow-sm" ' +
            'data-id="' + idAula + '" data-nombre="' + nombre + '" data-lugar="' + idLugar + '" ' +
            'data-lugar-nombre="' + lugarNombre + '" data-lugar-activo="' + lugarActivo + '" ' +
            'data-tiene-tickets="' + tieneTickets + '" data-piso="' + indicePiso + '">' +
            '<i class="bi bi-arrow-counterclockwise me-1"></i> Reactivar' +
            '</button>' +
            botonEliminar +
            '</div>' +
            '</li>';
    }

    /**
     * Reindexa los names de las aulas activas de un piso para mantener índices consecutivos.
     */
    function reindexarAulas(contenedorAulas, indicePiso) {
        const filas = contenedorAulas.querySelectorAll('.fila-aula');
        filas.forEach(function (fila, idx) {
            const inputs = fila.querySelectorAll('input[name], select[name]');
            inputs.forEach(function (input) {
                const name = input.getAttribute('name');
                const nuevoName = name.replace(
                    /pisos\[\d+\]\[aulas\]\[\d+\]/,
                    'pisos[' + indicePiso + '][aulas][' + idx + ']'
                );
                input.setAttribute('name', nuevoName);
            });
        });
    }

    /**
     * Genera el bloque accordion completo de un piso.
     */
    function bloquePiso(indicePiso, dataPiso) {
        const nombre = nombrePiso(indicePiso);
        const collapseId = 'collapsePiso' + indicePiso;
        const headerId = 'headingPiso' + indicePiso;

        const esPisoActivo = (dataPiso && typeof dataPiso.activo !== 'undefined') ? (parseInt(dataPiso.activo) === 1) : true;
        const activeAulas = Array.isArray(dataPiso) ? dataPiso : (dataPiso && dataPiso.aulas ? dataPiso.aulas : []);
        const inactiveAulas = (dataPiso && dataPiso.aulasInactivas) ? dataPiso.aulasInactivas : [];

        // Generar HTML de aulas inactivas
        let itemsInactivasHtml = '';
        inactiveAulas.forEach(function (aula) {
            itemsInactivasHtml += itemAulaInactiva(indicePiso, aula);
        });

        const tieneInactivas = inactiveAulas.length > 0;

        // Generar HTML de aulas activas
        let aulasHtml = '';
        activeAulas.forEach(function (aula, idx) {
            aulasHtml += filaAula(indicePiso, idx, aula);
        });

        return '<div class="accordion-item border rounded-3 mb-3 shadow-sm ' + (!esPisoActivo ? 'border-warning bg-light' : '') + '" data-piso="' + indicePiso + '">' +
            '<input type="hidden" name="pisos[' + indicePiso + '][activo]" value="' + (esPisoActivo ? '1' : '0') + '" class="input-piso-activo">' +
            '<h2 class="accordion-header" id="' + headerId + '">' +
            '<button class="accordion-button collapsed fw-bold d-flex align-items-center" type="button" ' +
            'data-bs-toggle="collapse" data-bs-target="#' + collapseId + '" ' +
            'aria-expanded="false" aria-controls="' + collapseId + '">' +
            '<i class="bi bi-layers me-2 text-primary"></i> ' +
            '<span class="me-2">' + nombre + '</span>' +
            '<span class="badge ' + (esPisoActivo ? 'bg-secondary' : 'bg-warning text-dark') + ' me-2 badge-count-aulas">' + activeAulas.length + ' aulas</span>' +
            '<span class="badge bg-warning text-dark badge-piso-mantenimiento ' + (esPisoActivo ? 'd-none' : '') + '"><i class="bi bi-tools me-1"></i>Desactivado</span>' +
            '</button>' +
            '</h2>' +
            '<div id="' + collapseId + '" class="accordion-collapse collapse" ' +
            'aria-labelledby="' + headerId + '" data-bs-parent="#accordionPisos">' +
            '<div class="accordion-body">' +

            // Panel de estado del piso
            '<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2 panel-estado-piso">' +
            '<div>' +
            '<span class="estado-piso-texto fw-bold ' + (esPisoActivo ? 'text-success' : 'text-warning') + '">' +
            '<i class="bi ' + (esPisoActivo ? 'bi-check-circle-fill' : 'bi-tools') + ' me-1"></i>' +
            (esPisoActivo ? 'Piso activo' : 'Piso desactivado') +
            '</span>' +
            '<small class="text-muted d-block">' +
            (esPisoActivo ? 'Disponible para tickets y asignación de aulas.' : 'Las aulas de este piso no estarán disponibles para crear tickets.') +
            '</small>' +
            '</div>' +
            '<button type="button" class="btn btn-sm ' + (esPisoActivo ? 'btn-outline-warning text-dark' : 'btn-outline-success') + ' btn-toggle-piso-activo rounded-pill fw-bold px-3 shadow-sm" data-piso="' + indicePiso + '">' +
            '<i class="bi ' + (esPisoActivo ? 'bi-tools' : 'bi-arrow-counterclockwise') + ' me-1"></i>' +
            (esPisoActivo ? 'Desactivar piso' : 'Activar piso') +
            '</button>' +
            '</div>' +

            // Contenedor de aulas activas
            '<div class="contenedor-aulas">' +
            aulasHtml +
            '</div>' +

            '<button type="button" class="btn btn-outline-primary btn-sm mt-2 rounded-pill px-3 btn-agregar-aula" data-piso="' + indicePiso + '">' +
            '<i class="bi bi-plus-circle me-1"></i> Agregar aula' +
            '</button>' +

            // Sección de aulas desactivadas / clausuradas
            '<div class="mt-4 contenedor-aulas-inactivas ' + (tieneInactivas ? '' : 'd-none') + '">' +
            '<h6 class="text-muted fw-bold mb-2">' +
            '<i class="bi bi-archive me-1"></i> Aulas desactivadas' +
            '</h6>' +
            '<ul class="list-group mb-2 shadow-sm lista-aulas-inactivas">' +
            itemsInactivasHtml +
            '</ul>' +
            '</div>' +

            '</div>' +
            '</div>' +
            '</div>';
    }

    /**
     * Actualiza los badges de cantidad de aulas y estado en cada piso.
     */
    function actualizarBadges() {
        const items = accordion.querySelectorAll('.accordion-item');
        items.forEach(function (item) {
            const cantAulas = item.querySelectorAll('.fila-aula').length;
            const badge = item.querySelector('.badge-count-aulas');
            if (badge) {
                badge.textContent = cantAulas + (cantAulas === 1 ? ' aula' : ' aulas');
            }

            const inputActivo = item.querySelector('.input-piso-activo');
            const esActivo = inputActivo ? (parseInt(inputActivo.value) === 1) : true;
            const badgeMantenimiento = item.querySelector('.badge-piso-mantenimiento');
            if (badgeMantenimiento) {
                if (esActivo) {
                    badgeMantenimiento.classList.add('d-none');
                } else {
                    badgeMantenimiento.classList.remove('d-none');
                }
            }
        });
    }

    /**
     * Renderiza todos los pisos según la cantidad indicada.
     */
    function renderizarPisos(cantidad, pisosData) {
        accordion.innerHTML = '';

        for (let i = 0; i < cantidad; i++) {
            const dataPiso = (pisosData && pisosData[i]) ? pisosData[i] : { piso: i, activo: 1, aulas: [], aulasInactivas: [] };
            accordion.insertAdjacentHTML('beforeend', bloquePiso(i, dataPiso));
        }

        actualizarBadges();
    }

    /**
     * Agrega un piso nuevo al final.
     */
    function agregarPiso() {
        const cantidad = parseInt(inputCantidad.value);
        if (cantidad >= 20) return;

        const nuevaCantidad = cantidad + 1;
        inputCantidad.value = nuevaCantidad;

        // Si existía información previa en pisosIniciales para este piso, recuperarla
        const dataPisoPrevio = (pisosIniciales && pisosIniciales[cantidad]) ? pisosIniciales[cantidad] : { piso: cantidad, activo: 1, aulas: [], aulasInactivas: [] };
        accordion.insertAdjacentHTML('beforeend', bloquePiso(cantidad, dataPisoPrevio));
        actualizarBadges();
    }

    /**
     * Elimina el último piso.
     */
    function quitarPiso() {
        const cantidad = parseInt(inputCantidad.value);
        if (cantidad <= 1) return;

        const ultimoItem = accordion.querySelector('.accordion-item:last-child');
        if (ultimoItem) {
            const cantAulasActivas = ultimoItem.querySelectorAll('.fila-aula').length;
            const cantAulasInactivas = ultimoItem.querySelectorAll('.item-aula-inactiva').length;
            const totalAulas = cantAulasActivas + cantAulasInactivas;

            if (totalAulas > 0) {
                Swal.fire({
                    title: '¿Eliminar ' + nombrePiso(cantidad - 1) + '?',
                    text: 'Este piso tiene ' + totalAulas + (totalAulas === 1 ? ' aula registrada.' : ' aulas registradas.') + ' Si tiene tickets asociados se desactivará, de lo contrario se eliminará.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, quitar piso',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        ultimoItem.remove();
                        inputCantidad.value = cantidad - 1;
                        actualizarBadges();
                    }
                });
            } else {
                ultimoItem.remove();
                inputCantidad.value = cantidad - 1;
                actualizarBadges();
            }
        }
    }

    // --- Event listeners de botones de control ---

    btnSumar.addEventListener('click', agregarPiso);
    btnRestar.addEventListener('click', quitarPiso);

    // Delegación de eventos del acordeón de pisos
    accordion.addEventListener('click', function (e) {

        // 1. Alternar mantenimiento del piso
        const btnTogglePiso = e.target.closest('.btn-toggle-piso-activo');
        if (btnTogglePiso) {
            const item = btnTogglePiso.closest('.accordion-item');
            const indicePiso = parseInt(btnTogglePiso.getAttribute('data-piso'));
            const inputActivo = item.querySelector('.input-piso-activo');
            const estadoActual = parseInt(inputActivo.value);

            if (estadoActual === 1) {
                Swal.fire({
                    title: '¿Desactivar ' + nombrePiso(indicePiso) + '?',
                    text: 'El piso quedará desactivado y sus aulas no estarán disponibles para levantar nuevos tickets.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        inputActivo.value = '0';
                        item.classList.add('border-warning', 'bg-light');

                        const textoEstado = item.querySelector('.estado-piso-texto');
                        textoEstado.className = 'estado-piso-texto fw-bold text-warning';
                        textoEstado.innerHTML = '<i class="bi bi-tools me-1"></i>Piso desactivado';

                        btnTogglePiso.className = 'btn btn-sm btn-outline-success btn-toggle-piso-activo rounded-pill fw-bold px-3 shadow-sm';
                        btnTogglePiso.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i>Activar piso (poner en servicio)';

                        actualizarBadges();
                    }
                });
            } else {
                Swal.fire({
                    title: '¿Activar ' + nombrePiso(indicePiso) + '?',
                    text: 'El piso volverá a estar activo.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, activar piso',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        inputActivo.value = '1';
                        item.classList.remove('border-warning', 'bg-light');

                        const textoEstado = item.querySelector('.estado-piso-texto');
                        textoEstado.className = 'estado-piso-texto fw-bold text-success';
                        textoEstado.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Piso activo';

                        btnTogglePiso.className = 'btn btn-sm btn-outline-warning text-dark btn-toggle-piso-activo rounded-pill fw-bold px-3 shadow-sm';
                        btnTogglePiso.innerHTML = '<i class="bi bi-tools me-1"></i>Desactivar piso';

                        actualizarBadges();
                    }
                });
            }
            return;
        }

        // 2. Agregar aula
        const btnAgregar = e.target.closest('.btn-agregar-aula');
        if (btnAgregar) {
            const indicePiso = parseInt(btnAgregar.getAttribute('data-piso'));
            const item = btnAgregar.closest('.accordion-item');
            const contenedor = item.querySelector('.contenedor-aulas');
            const indiceAula = contenedor.querySelectorAll('.fila-aula').length;

            contenedor.insertAdjacentHTML('beforeend', filaAula(indicePiso, indiceAula, null));
            actualizarBadges();
            return;
        }

        // 3. Desactivar / Clausurar aula manualmente
        const btnDesactivar = e.target.closest('.btn-desactivar-aula');
        if (btnDesactivar) {
            const fila = btnDesactivar.closest('.fila-aula');
            const item = btnDesactivar.closest('.accordion-item');
            const indicePiso = parseInt(item.getAttribute('data-piso'));
            const inputNombre = fila.querySelector('.input-nombre-aula');
            const nombre = inputNombre ? inputNombre.value.trim() : 'esta aula';
            const idAula = fila.getAttribute('data-id-aula');
            const selectLugarEl = fila.querySelector('.select-lugar-aula');
            const idLugar = selectLugarEl ? selectLugarEl.value : fila.getAttribute('data-id-lugar');
            const lugarNombre = selectLugarEl && selectLugarEl.selectedIndex >= 0 ? selectLugarEl.options[selectLugarEl.selectedIndex].text : '';
            const tieneTickets = fila.getAttribute('data-tiene-tickets');

            if (!idAula) {
                // Aula que aún no se ha guardado en la base de datos
                Swal.fire({
                    title: '¿Descartar aula no guardada?',
                    text: 'Esta aula aún no ha sido guardada. Si deseas que no esté activa, puedes eliminarla.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, quitar aula',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        fila.remove();
                        const contenedor = item.querySelector('.contenedor-aulas');
                        reindexarAulas(contenedor, indicePiso);
                        actualizarBadges();
                    }
                });
                return;
            }

            Swal.fire({
                title: '¿Desactivar / Clausurar aula?',
                text: 'El aula "' + nombre + '" se moverá a las aulas desactivadas de este piso y no se le podrán levantar tickets.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    const aulaInactivaObj = {
                        id_aula: idAula,
                        nombre: nombre,
                        id_lugar: idLugar,
                        lugar_nombre: lugarNombre,
                        lugar_activo: 1,
                        tiene_tickets: tieneTickets
                    };

                    const contenedorInactivas = item.querySelector('.contenedor-aulas-inactivas');
                    const listaInactivas = item.querySelector('.lista-aulas-inactivas');

                    listaInactivas.insertAdjacentHTML('beforeend', itemAulaInactiva(indicePiso, aulaInactivaObj));
                    contenedorInactivas.classList.remove('d-none');

                    fila.remove();
                    const contenedor = item.querySelector('.contenedor-aulas');
                    reindexarAulas(contenedor, indicePiso);
                    actualizarBadges();
                }
            });
            return;
        }

        // 4. Eliminar aula (distingue entre desactivar si tiene tickets o eliminar permanente)
        const btnEliminar = e.target.closest('.btn-eliminar-aula');
        if (btnEliminar) {
            const fila = btnEliminar.closest('.fila-aula');
            const item = btnEliminar.closest('.accordion-item');
            const contenedor = item.querySelector('.contenedor-aulas');
            const indicePiso = parseInt(item.getAttribute('data-piso'));
            const inputNombre = fila.querySelector('.input-nombre-aula');
            const nombre = inputNombre ? inputNombre.value.trim() : 'esta aula';
            const idAula = fila.getAttribute('data-id-aula');
            const tieneTickets = fila.getAttribute('data-tiene-tickets') === '1';

            if (tieneTickets) {
                // Tiene tickets registrados en la BD => solo se puede desactivar
                Swal.fire({
                    title: '¿Desactivar aula?',
                    text: 'El aula "' + nombre + '" tiene tickets asociados en el sistema. Se desactivará para preservar el historial.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        const selectLugarEl = fila.querySelector('.select-lugar-aula');
                        const idLugar = selectLugarEl ? selectLugarEl.value : fila.getAttribute('data-id-lugar');
                        const lugarNombre = selectLugarEl && selectLugarEl.selectedIndex >= 0 ? selectLugarEl.options[selectLugarEl.selectedIndex].text : '';

                        const aulaInactivaObj = {
                            id_aula: idAula,
                            nombre: nombre,
                            id_lugar: idLugar,
                            lugar_nombre: lugarNombre,
                            lugar_activo: 1,
                            tiene_tickets: 1
                        };

                        const contenedorInactivas = item.querySelector('.contenedor-aulas-inactivas');
                        const listaInactivas = item.querySelector('.lista-aulas-inactivas');

                        listaInactivas.insertAdjacentHTML('beforeend', itemAulaInactiva(indicePiso, aulaInactivaObj));
                        contenedorInactivas.classList.remove('d-none');

                        fila.remove();
                        reindexarAulas(contenedor, indicePiso);
                        actualizarBadges();
                    }
                });
            } else {
                // No tiene tickets asociados => se elimina permanentemente
                Swal.fire({
                    title: '¿Eliminar aula permanentemente?',
                    text: 'El aula "' + nombre + '" no tiene tickets asociados. Se eliminará de forma definitiva.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        fila.remove();
                        reindexarAulas(contenedor, indicePiso);
                        actualizarBadges();
                    }
                });
            }
            return;
        }

        // 5. Reactivar aula desde la lista de desactivadas
        const btnReactivar = e.target.closest('.btn-reactivar-aula');
        if (btnReactivar) {
            const indicePiso = parseInt(btnReactivar.getAttribute('data-piso'));
            const idAula = btnReactivar.getAttribute('data-id');
            const nombre = btnReactivar.getAttribute('data-nombre');
            let idLugar = btnReactivar.getAttribute('data-lugar');
            const lugarActivo = parseInt(btnReactivar.getAttribute('data-lugar-activo'));
            const tieneTickets = parseInt(btnReactivar.getAttribute('data-tiene-tickets'));

            const item = btnReactivar.closest('.accordion-item');
            const contenedor = item.querySelector('.contenedor-aulas');
            const indiceAula = contenedor.querySelectorAll('.fila-aula').length;

            // Función interna para mover a activas
            const ejecutarReactivacion = function (lugarSeleccionado) {
                const aulaData = {
                    id_aula: idAula,
                    nombre: nombre,
                    id_lugar: lugarSeleccionado,
                    tiene_tickets: tieneTickets,
                    lugar_activo: 1
                };

                contenedor.insertAdjacentHTML('beforeend', filaAula(indicePiso, indiceAula, aulaData));

                // Remover el ítem de la lista de inactivas
                const li = document.getElementById('aulaInactiva-' + idAula);
                if (li) {
                    const ul = li.closest('ul');
                    li.remove();
                    if (ul && ul.children.length === 0) {
                        const contenedorInactivas = ul.closest('.contenedor-aulas-inactivas');
                        if (contenedorInactivas) {
                            contenedorInactivas.classList.add('d-none');
                        }
                    }
                }

                actualizarBadges();
            };

            // Si el lugar al que pertenecía está inactivo, exigir elegir un lugar activo
            if (lugarActivo === 0) {
                let optionsHtml = '';
                lugares.forEach(function (l) {
                    optionsHtml += '<option value="' + l.id_lugar + '">' + l.nombre + '</option>';
                });

                Swal.fire({
                    title: 'Tipo de lugar inactivo',
                    html: '<p class="text-muted small">El tipo de lugar asignado previamente a <strong>' + nombre + '</strong> se encuentra desactivado. Elige uno activo para reactivarla:</p>' +
                        '<select id="swalSelectLugar" class="form-select form-select-sm mt-2">' + optionsHtml + '</select>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Reactivar con este tipo',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        const selectEl = document.getElementById('swalSelectLugar');
                        if (!selectEl || !selectEl.value) {
                            Swal.showValidationMessage('Debes seleccionar un tipo de lugar.');
                        }
                        return selectEl.value;
                    }
                }).then(function (result) {
                    if (result.isConfirmed && result.value) {
                        ejecutarReactivacion(result.value);
                    }
                });
                return;
            }

            // Si el lugar está activo, reactivación normal
            Swal.fire({
                title: '¿Reactivar aula?',
                text: 'El aula "' + nombre + '" volverá a estar activa en este piso.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, reactivar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    ejecutarReactivacion(idLugar);
                }
            });
            return;
        }

        // 6. Eliminar definitivo de un aula que estaba desactivada y NO tiene tickets
        const btnEliminarDefinitivo = e.target.closest('.btn-eliminar-definitivo-aula');
        if (btnEliminarDefinitivo) {
            const nombre = btnEliminarDefinitivo.getAttribute('data-nombre');
            const idAula = btnEliminarDefinitivo.getAttribute('data-id');

            Swal.fire({
                title: '¿Eliminar permanentemente?',
                text: 'El aula "' + nombre + '" no tiene tickets asociados. Se eliminará por completo de la base de datos al guardar los cambios.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    const li = document.getElementById('aulaInactiva-' + idAula);
                    if (li) {
                        const ul = li.closest('ul');
                        li.remove();
                        if (ul && ul.children.length === 0) {
                            const contenedorInactivas = ul.closest('.contenedor-aulas-inactivas');
                            if (contenedorInactivas) {
                                contenedorInactivas.classList.add('d-none');
                            }
                        }
                    }
                }
            });
            return;
        }

    });

    // --- Inicialización ---

    if (pisosIniciales.length > 0) {
        // Modo edición: cargar pisos existentes con activas e inactivas
        renderizarPisos(pisosIniciales.length, pisosIniciales);

        // Abrir el primer piso
        const primerCollapse = accordion.querySelector('.accordion-collapse');
        if (primerCollapse) {
            primerCollapse.classList.add('show');
            const primerBtn = accordion.querySelector('.accordion-button');
            if (primerBtn) primerBtn.classList.remove('collapsed');
        }
    } else {
        // Modo creación: renderizar con la cantidad inicial
        const cantidadInicial = parseInt(inputCantidad.value) || 1;
        renderizarPisos(cantidadInicial, []);

        // Abrir el primer piso
        const primerCollapse = accordion.querySelector('.accordion-collapse');
        if (primerCollapse) {
            primerCollapse.classList.add('show');
            const primerBtn = accordion.querySelector('.accordion-button');
            if (primerBtn) primerBtn.classList.remove('collapsed');
        }
    }

    // Validación antes de enviar el formulario
    document.getElementById('formEdificio').addEventListener('submit', function (e) {
        const cantidadPisos = parseInt(inputCantidad.value);

        if (cantidadPisos < 1) {
            e.preventDefault();
            Swal.fire({
                title: 'Error',
                text: 'El edificio debe tener al menos 1 piso.',
                icon: 'error',
                confirmButtonColor: '#004A98'
            });
            return;
        }

        // Verificar que cada piso ACTIVO tenga al menos un aula activa
        const items = accordion.querySelectorAll('.accordion-item');
        let pisosActivosSinAulas = [];
        let aulasDuplicadasPorPiso = [];

        items.forEach(function (item) {
            const numPiso = parseInt(item.getAttribute('data-piso'));
            const inputActivo = item.querySelector('.input-piso-activo');
            const esActivo = inputActivo ? (parseInt(inputActivo.value) === 1) : true;
            const filasAulas = item.querySelectorAll('.fila-aula');

            // Solo los pisos activos requieren al menos un aula activa
            if (esActivo && filasAulas.length === 0) {
                pisosActivosSinAulas.push(nombrePiso(numPiso));
            }

            // Comprobar nombres repetidos dentro de ESTE piso (unicidad por piso)
            let nombresAulasPiso = {};
            filasAulas.forEach(function (fila) {
                const inputNombre = fila.querySelector('.input-nombre-aula');
                if (inputNombre) {
                    const nombreNorm = inputNombre.value.trim().toLowerCase();
                    if (nombreNorm.length > 0) {
                        if (nombresAulasPiso[nombreNorm]) {
                            const yaAgregado = aulasDuplicadasPorPiso.some(function (d) {
                                return d.numPiso === numPiso && d.nombreNorm === nombreNorm;
                            });
                            if (!yaAgregado) {
                                aulasDuplicadasPorPiso.push({
                                    numPiso: numPiso,
                                    nombreNorm: nombreNorm,
                                    piso: nombrePiso(numPiso),
                                    nombre: inputNombre.value.trim()
                                });
                            }
                        } else {
                            nombresAulasPiso[nombreNorm] = true;
                        }
                    }
                }
            });
        });

        if (pisosActivosSinAulas.length > 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Pisos activos sin aulas',
                html: 'Los siguientes pisos están activos pero no tienen aulas registradas:<br><br><strong>' + pisosActivosSinAulas.join('<br>') + '</strong><br><br>Cada piso en servicio debe tener al menos un aula. Si el piso está desactivado, desactívalo con el botón correspondiente.',
                icon: 'warning',
                confirmButtonColor: '#004A98'
            });
            return;
        }

        if (aulasDuplicadasPorPiso.length > 0) {
            e.preventDefault();
            let listaHtml = '<div class="text-start p-3 bg-light border rounded-3 mb-2" style="max-height: 180px; overflow-y: auto; font-size: 0.9rem;">';
            aulasDuplicadasPorPiso.forEach(function (dup) {
                const nombreEscapado = dup.nombre.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                listaHtml += '<div class="py-1 border-bottom d-flex align-items-center justify-content-between">' +
                    '<span><strong>"' + nombreEscapado + '"</strong></span>' +
                    '<span class="badge bg-danger ms-2">' + dup.piso + '</span>' +
                    '</div>';
            });
            listaHtml += '</div>';

            Swal.fire({
                title: 'Aulas duplicadas en el mismo piso',
                html: '<p class="text-muted small mb-3">No puedes registrar dos aulas con el mismo nombre dentro del mismo piso:</p>' + listaHtml,
                icon: 'warning',
                confirmButtonColor: '#004A98',
                confirmButtonText: 'Entendido'
            });
            return;
        }
    });
});
