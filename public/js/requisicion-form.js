// ===================================================================
// REQUISICION.JS
// Las URLs y datos de configuración se leen desde los atributos
// data-* del formulario principal (#formRequisicion), no hardcodeados.
// ===================================================================

// Estado en memoria
let idRequisicion = '';
let contadorItems = 0;

// Referencias al formulario y su config (se inicializan en DOMContentLoaded)
let CFG = {};

// ===================================================================
// INICIALIZACIÓN
// ===================================================================
document.addEventListener('DOMContentLoaded', function () {

    const formReq = document.getElementById('formRequisicion');
    if (!formReq) return;

    // Leer configuración desde data-* del formulario (pasado desde Blade via route())
    CFG = {
        urlBorrador: formReq.dataset.urlBorrador,
        urlRequisiciones: formReq.dataset.urlRequisiciones || `${formReq.dataset.urlMaterialBase?.replace(/\/materiales$/, '/requisiciones')}`,
        urlMaterialBase: formReq.dataset.urlMaterialBase,
        idTicket: formReq.dataset.idTicket,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content ?? '',
    };

    idRequisicion = formReq.dataset.idRequisicion || '';
    if (idRequisicion) {
        CFG.urlAddMaterial = `${CFG.urlRequisiciones}/${idRequisicion}/material`;
        CFG.urlRemoveMaterial = `${CFG.urlRequisiciones}/${idRequisicion}/material`;
    }

    // Contar items ya cargados (del borrador)
    contadorItems = document.querySelectorAll('#cuerpoPartidas tr:not(.fila-vacia)').length;

    // ---- Autoguardado del borrador ----
    const notif = document.getElementById('notificacionBorrador');
    let debounceTimer;

    const mostrarNotif = () => {
        notif.classList.remove('d-none');
        setTimeout(() => notif.classList.add('d-none'), 3000);
    };

    window.saveDraft = async () => {
        
        const data = {
            id_requisicion: idRequisicion,
            id_admin: document.getElementById('id_admin').value,
            id_ticket: CFG.idTicket,
            proveedor: document.getElementById('inputProveedor')?.value ?? '',
            cotizacion: document.getElementById('selectCotizacion')?.value || null,
            subtotal: parseMoney(document.getElementById('displaySubtotal')?.innerText),
            total: parseMoney(document.getElementById('displayTotal')?.innerText),
        };

        try {
            const res = await fetchJSON(CFG.urlBorrador, 'POST', data);
            if (res.success && res.id_requisicion) {
                idRequisicion = res.id_requisicion;
                document.getElementById('id_requisicion').value = idRequisicion;
                // Actualizar URLs con el id recién creado
                CFG.urlAddMaterial = `${CFG.urlRequisiciones}/${idRequisicion}/material`;
                CFG.urlRemoveMaterial = `${CFG.urlRequisiciones}/${idRequisicion}/material`;
                mostrarNotif();
            }
        } catch (e) {
            console.error('Error al guardar borrador de requisición:', e);
        }
    };

    // Auto-crear borrador si es nuevo
    if (!idRequisicion) {
        window.saveDraft();
    }

    // Forzar recálculo inicial para sincronizar totales
    recalcularTotalesDisplay();

    // Escuchar cambios en inputs y selects del formulario principal para autoguardar
    formReq.querySelectorAll('input:not([type="file"]), select, textarea').forEach(el => {
        const handleTrigger = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(window.saveDraft, 2000);
        };
        el.addEventListener('input', handleTrigger);
        el.addEventListener('change', handleTrigger);
    });

    // ---- Toggle inventario / libre ----
    document.querySelectorAll('input[name="tipoMaterial"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const esInventario = this.value === 'inventario';
            document.getElementById('seccionInventario').classList.toggle('d-none', !esInventario);
            document.getElementById('seccionLibre').classList.toggle('d-none', esInventario);
            limpiarModal();
            actualizarEstadoPrecioUnitario(esInventario);
        });
    });

    // Inicializar estado de precio unitario (bloqueado por defecto para inventario)
    actualizarEstadoPrecioUnitario(true);

    // ---- Carga de datos al seleccionar material del inventario ----
    document.getElementById('selectMaterial')?.addEventListener('change', async function () {
        const idMat = this.value;
        if (!idMat) return;

        try {
            const datos = await fetchJSON(`${CFG.urlMaterialBase}/${idMat}/datos`, 'GET');
            document.getElementById('autoEspecificaciones').value = datos.descripcion ?? '';
            document.getElementById('autoUnidad').value = datos.unidad ?? '';

            const selectMarca = document.getElementById('selectMarcaInventario');
            if (datos.marcas && datos.marcas.length > 0) {
                selectMarca.innerHTML = '<option value="" selected disabled>Selecciona una marca</option>';
                datos.marcas.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.nombre;
                    opt.textContent = m.nombre;
                    selectMarca.appendChild(opt);
                });
            } else {
                selectMarca.innerHTML = '<option value="" selected disabled>Sin marcas</option>';
            }

            document.getElementById('datosAutoMaterial').classList.remove('d-none');
        } catch (e) {
            console.error('Error al cargar datos del material:', e);
        }
    });

    // ---- Cálculo automático del subtotal en el modal ----
    document.getElementById('modalCantidad')?.addEventListener('input', recalcularSubtotalModal);
    document.getElementById('modalUnitario')?.addEventListener('input', recalcularSubtotalModal);

    // ---- Confirmar agregar material ----
    document.getElementById('btnConfirmarMaterial')?.addEventListener('click', agregarMaterial);

    // ---- Eliminar material (delegación de eventos) ----
    document.getElementById('cuerpoPartidas')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-eliminar-item');
        if (!btn) return;
        eliminarMaterial(btn.dataset.id);
    });

    // ---- Resetear modal al cerrarse ----
    document.getElementById('modalAgregarMaterial')?.addEventListener('hidden.bs.modal', () => {
        limpiarModal();
        resetearToggleModal();
    });

    // ---- Submit final del formulario ----
    formReq.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!idRequisicion) {
            await window.saveDraft();
        }
        if (!idRequisicion) {
            alert('No se pudo crear la requisición. Intenta guardar el borrador primero.');
            return;
        }

        if (contadorItems === 0) {
            Swal.fire('Requisición vacía', 'Debes agregar al menos un material antes de guardar la requisición.', 'warning');
            return;
        }

        const btnFinal = document.getElementById('btnGuardarFinal');
        btnFinal.disabled = true;
        btnFinal.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

        const formData = new FormData(formReq);
        formData.set('id_requisicion', idRequisicion);

        try {
            const res = await fetch(formReq.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CFG.csrfToken,
                },
            });

            if (res.redirected) {
                window.location.href = res.url;
            } else if (res.ok) {
                const data = await res.json().catch(() => null);
                if (data?.redirect) window.location.href = data.redirect;
            } else {
                const errData = await res.json().catch(() => null);
                btnFinal.disabled = false;
                btnFinal.innerHTML = 'Guardar requisición';
                Swal.fire('Error', errData?.message || 'No se pudo guardar la requisición.', 'error');
            }
        } catch (e) {
            console.error(e);
            btnFinal.disabled = false;
            btnFinal.innerHTML = 'Guardar requisición';
            Swal.fire('Error', 'Error de conexión.', 'error');
        }
    });
});

// ===================================================================
// FUNCIONES: MATERIALES
// ===================================================================

async function agregarMaterial() {
    const errorDiv = document.getElementById('modalError');
    errorDiv.classList.add('d-none');

    const tipo = document.querySelector('input[name="tipoMaterial"]:checked')?.value;
    const cantidad = parseInt(document.getElementById('modalCantidad').value) || 0;
    const unitario = tipo === 'inventario' ? 0 : (parseFloat(document.getElementById('modalUnitario').value) || 0);

    if (cantidad < 1) return mostrarErrorModal('La cantidad debe ser al menos 1.');

    // Materiales libres requieren precio unitario mayor a 0
    if (tipo === 'libre' && unitario <= 0) {
        return mostrarErrorModal('El precio unitario es obligatorio para materiales que no están en el inventario.');
    }

    if (unitario < 0) return mostrarErrorModal('El precio unitario no puede ser negativo.');

    if (!idRequisicion) {
        Swal.fire('Atención', 'Primero guarda un borrador antes de agregar materiales.', 'warning');
        return;
    }

    if (contadorItems >= 16) {
        return mostrarErrorModal('Ya alcanzaste el máximo de 16 partidas en esta requisición.');
    }

    let payload = { tipo, cantidad, unitario };

    if (tipo === 'inventario') {
        const idMat = document.getElementById('selectMaterial').value;
        if (!idMat) return mostrarErrorModal('Selecciona un material del inventario.');

        const selectMarca = document.getElementById('selectMarcaInventario');
        if (selectMarca.options.length > 1 && !selectMarca.value) {
            return mostrarErrorModal('Selecciona una marca para el material.');
        }

        payload.id_material = idMat;
        payload.marca_texto = selectMarca.value || '';
    } else {
        const nombre = document.getElementById('libreNombre').value.trim();
        const unidad = document.getElementById('libreUnidad').value;
        if (!nombre) return mostrarErrorModal('El nombre del material es obligatorio.');
        if (!unidad) return mostrarErrorModal('Selecciona una unidad.');
        payload.nombre_libre = nombre;
        payload.especificaciones = document.getElementById('libreEspecificaciones').value.trim();
        payload.unidad = unidad;
        payload.marca_texto = document.getElementById('libreMarca').value.trim();
    }

    const btn = document.getElementById('btnConfirmarMaterial');
    const spinner = document.getElementById('btnConfirmarSpinner');
    btn.disabled = true;
    spinner.classList.remove('d-none');

    try {
        const urlAdd = CFG.urlAddMaterial || `${CFG.urlRequisiciones}/${idRequisicion}/material`;
        const res = await fetchJSON(urlAdd, 'POST', payload);
        if (res.success) {
            insertarFilaTabla(res.item);
            recalcularTotalesDisplay();
            window.saveDraft();
            bootstrap.Modal.getInstance(document.getElementById('modalAgregarMaterial'))?.hide();
        } else {
            mostrarErrorModal(res.message || 'No se pudo agregar el material.');
        }
    } catch (e) {
        mostrarErrorModal(e.message || 'Error de conexión al agregar material.');
        console.error(e);
    } finally {
        btn.disabled = false;
        spinner.classList.add('d-none');
    }
}

async function eliminarMaterial(idItem) {
    const confirmado = await Swal.fire({
        title: '¿Eliminar material?',
        text: 'Se quitará el material de la requisición.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    });

    if (!confirmado.isConfirmed) return;

    try {
        const urlRemove = CFG.urlRemoveMaterial || `${CFG.urlRequisiciones}/${idRequisicion}/material`;
        const res = await fetchJSON(`${urlRemove}/${idItem}`, 'DELETE');
        if (res.success) {
            const fila = document.querySelector(`#cuerpoPartidas tr[data-id="${idItem}"]`);
            if (fila) {
                fila.remove();
                contadorItems--;
                renumerarFilas();
                recalcularTotalesDisplay();
                window.saveDraft();
            }
        }
    } catch (e) {
        Swal.fire('Error', 'No se pudo eliminar la partida.', 'error');
        console.error(e);
    }
}

// ===================================================================
// FUNCIONES: DOM
// ===================================================================

/**
 * Inserta una nueva fila de material, reemplazando la primera fila vacía.
 */
function insertarFilaTabla(item) {
    const tbody = document.getElementById('cuerpoPartidas');
    const filasVacias = tbody.querySelectorAll('.fila-vacia');
    if (filasVacias.length === 0) return;

    contadorItems++;
    const partida = tbody.querySelectorAll('tr:not(.fila-vacia)').length + 1;
    const primerVacio = filasVacias[0];

    const fila = document.createElement('tr');
    fila.dataset.id = item.id_item;
    fila.innerHTML = `
        <td class="text-center text-muted">${partida}</td>
        <td class="text-center">${item.cantidad}</td>
        <td class="text-center">${escapeHtml(item.unidad ?? '')}</td>
        <td>${escapeHtml(item.nombre)}</td>
        <td class="text-center text-muted fst-italic" style="font-size:0.75rem;">—</td>
        <td>${escapeHtml(item.especificaciones ?? '')}</td>
        <td class="text-center">${escapeHtml(item.marca_texto ?? '')}</td>
        <td class="text-center text-muted fst-italic" style="font-size:0.75rem;">—</td>
        <td class="text-center">$${formatMoney(item.unitario)}</td>
        <td class="text-center" data-subtotal="${item.subtotal}">$${formatMoney(item.subtotal)}</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger py-0 px-1 btn-eliminar-item"
                    data-id="${item.id_item}" title="Eliminar">
                <i class="bi bi-x-lg"></i>
            </button>
        </td>
    `;

    tbody.insertBefore(fila, primerVacio);
    primerVacio.remove();
}

/**
 * Re-numera las filas después de eliminar una partida.
 */
function renumerarFilas() {
    document.querySelectorAll('#cuerpoPartidas tr:not(.fila-vacia)').forEach((fila, i) => {
        const firstTd = fila.querySelector('td');
        if (firstTd) firstTd.textContent = i + 1;
    });

    document.querySelectorAll('#cuerpoPartidas .fila-vacia').forEach((fila, i) => {
        const firstTd = fila.querySelector('td');
        if (firstTd) firstTd.textContent = contadorItems + i + 1;
    });
}

/**
 * Recalcula el subtotal en el modal al cambiar cantidad o precio unitario.
 * También limita el precio unitario a 2 decimales.
 */
function recalcularSubtotalModal() {
    const unitarioInput = document.getElementById('modalUnitario');

    // Limitar a 2 decimales sin bloquear al usuario mientras escribe
    const val = unitarioInput.value;
    if (val.includes('.')) {
        const parts = val.split('.');
        if (parts[1].length > 2) {
            unitarioInput.value = parts[0] + '.' + parts[1].substring(0, 2);
        }
    }

    const cant = parseFloat(document.getElementById('modalCantidad').value) || 0;
    const unit = parseFloat(unitarioInput.value) || 0;
    document.getElementById('modalSubtotal').value = '$' + formatMoney(cant * unit);
}

/**
 * Recalcula Subtotal / IVA / Total en el pie del documento
 * usando el atributo data-subtotal de cada celda (no índice hardcodeado).
 */
function recalcularTotalesDisplay() {
    let subtotal = 0;
    document.querySelectorAll('#cuerpoPartidas tr:not(.fila-vacia) [data-subtotal]').forEach(celda => {
        subtotal += parseFloat(celda.dataset.subtotal) || 0;
    });

    const iva = subtotal * 0.16;
    const total = subtotal + iva;

    document.getElementById('displaySubtotal').innerText = '$' + formatMoney(subtotal);
    document.getElementById('displayIva').innerText = '$' + formatMoney(iva);
    document.getElementById('displayTotal').innerText = '$' + formatMoney(total);
}

function limpiarModal() {
    document.getElementById('selectMaterial').value = '';
    document.getElementById('autoEspecificaciones').value = '';
    document.getElementById('autoUnidad').value = '';
    document.getElementById('selectMarcaInventario').innerHTML = '<option value="" disabled selected>Sin marcas</option>';
    document.getElementById('datosAutoMaterial').classList.add('d-none');
    document.getElementById('libreNombre').value = '';
    document.getElementById('libreUnidad').value = '';
    document.getElementById('libreMarca').value = '';
    document.getElementById('libreEspecificaciones').value = '';
    document.getElementById('modalCantidad').value = '1';
    document.getElementById('modalUnitario').value = '0.00';
    document.getElementById('modalSubtotal').value = '$0.00';
    document.getElementById('modalError').classList.add('d-none');
}

function resetearToggleModal() {
    document.getElementById('tipoInventario').checked = true;
    document.getElementById('seccionInventario').classList.remove('d-none');
    document.getElementById('seccionLibre').classList.add('d-none');
    actualizarEstadoPrecioUnitario(true);
}

function actualizarEstadoPrecioUnitario(esInventario) {
    const inputUnitario = document.getElementById('modalUnitario');
    const notaLibre = document.getElementById('notaUnitarioLibre');
    if (!inputUnitario) return;

    if (esInventario) {
        inputUnitario.value = '0.00';
        inputUnitario.readOnly = true;
        inputUnitario.disabled = true;
        inputUnitario.classList.add('bg-secondary-subtle');
        if (notaLibre) notaLibre.textContent = '';
        const modalSubtotal = document.getElementById('modalSubtotal');
        if (modalSubtotal) modalSubtotal.value = '$0.00';
    } else {
        inputUnitario.disabled = false;
        inputUnitario.readOnly = false;
        inputUnitario.classList.remove('bg-secondary-subtle');
        if (notaLibre) notaLibre.textContent = '';
    }
}

function mostrarErrorModal(msg) {
    const el = document.getElementById('modalError');
    el.textContent = msg;
    el.classList.remove('d-none');
}

// ===================================================================
// UTILS
// ===================================================================

/**
 * Wrapper fetch con CSRF, JSON y manejo de errores HTTP.
 */
async function fetchJSON(url, method = 'GET', body = null) {
    const opts = {
        method,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CFG.csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
    };
    if (body && method !== 'GET') opts.body = JSON.stringify(body);

    const res = await fetch(url, opts);
    if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error(err.message || `HTTP ${res.status}`);
    }
    return res.json();
}

/**
 * Formatea un número como moneda MX con 2 decimales.
 */
function formatMoney(n) {
    return parseFloat(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**
 * Parsea un string de moneda (ej: "$1,234.56") a número.
 */
function parseMoney(str) {
    return parseFloat((str || '0').replace(/[$,]/g, '')) || 0;
}

/**
 * Escapa caracteres HTML para evitar XSS al insertar texto en el DOM.
 */
function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}