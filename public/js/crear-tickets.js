// =====================================================================
//  Configuración
// =====================================================================
const MAX_FILES = 5;
const FILE_MAX_SIZE_MB = 10;
const MAX_SIZE_BYTES = FILE_MAX_SIZE_MB * 1024 * 1024;
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
const COLOR_PRIMARIO = '#0a3957';
const DRAFT_DEBOUNCE_MS = 2000;

// =====================================================================
//  Utilidades
// =====================================================================
const obtenerExtension = (nombre) => nombre.split('.').pop().toLowerCase();

function formatearTamano(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

// Evita inyectar HTML con datos del usuario (nombres de archivo, asunto, etc.)
function escapeHtml(valor) {
    return String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function resetSelect(select, placeholder) {
    select.innerHTML = `<option value="" selected disabled>${placeholder}</option>`;
}

async function postFormData(url, formData, signal) {
    const response = await fetch(url, {
        method: 'POST',
        body: formData,
        signal,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    });
    return response.json();
}

// =====================================================================
//  Inicialización
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('ticketForm');
    if (!form) return;

    // Rutas inyectadas desde Blade
    const URL_BORRADOR = form.dataset.urlBorrador;
    const URL_HOME = form.dataset.urlHome;

    const selectEdificio = document.getElementById('edificio');
    const selectPiso = document.getElementById('piso');
    const selectAula = document.getElementById('aula');
    const inputArchivos = document.getElementById('adjunto');
    const btnEnviar = document.getElementById('btn-enviar-ticket');
    const notificacionBorrador = document.getElementById('notificacion-borrador');

    // =================================================================
    //  Edificio → Piso → Lugar
    // =================================================================
    const pisosDelEdificio = () =>
        JSON.parse(selectEdificio.selectedOptions[0]?.dataset.pisos || '[]');

    function actualizarPisos() {
        resetSelect(selectPiso, 'Selecciona piso');
        resetSelect(selectAula, 'Selecciona lugar');

        pisosDelEdificio().forEach(({ piso }) => {
            const num = parseInt(piso);
            selectPiso.add(new Option(num === 0 ? 'Planta baja' : `Piso ${num}`, num));
        });
    }

    function actualizarLugares() {
        resetSelect(selectAula, 'Selecciona lugar');

        const piso = pisosDelEdificio().find(p => String(parseInt(p.piso)) === selectPiso.value);
        (piso?.aulas || []).forEach(aula => {
            selectAula.add(new Option(aula.nombre, aula.id_aula));
        });
    }

    selectEdificio.addEventListener('change', actualizarPisos);
    selectPiso.addEventListener('change', actualizarLugares);

    // Restaurar selección si se está editando un borrador
    const pisoBorrador = document.getElementById('piso_borrador')?.value;
    const lugarBorrador = document.getElementById('lugar_borrador')?.value;

    if (selectEdificio.value) {
        actualizarPisos();
        if (pisoBorrador) {
            selectPiso.value = pisoBorrador;
            actualizarLugares();
            if (lugarBorrador) selectAula.value = lugarBorrador;
        }
    }

    // =================================================================
    //  Archivos adjuntos
    // =================================================================
    let archivosSeleccionados = [];
    let urlsPreview = [];

    function abrirModalPreview(url, titulo) {
        document.getElementById('modalImagenTitulo').innerText = titulo;
        document.getElementById('modalImagenSrc').src = url;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalImagenPreview')).show();
    }

    function agregarArchivos(input) {
        const nuevos = Array.from(input.files || []);
        input.value = ''; // permite volver a elegir el mismo archivo
        if (nuevos.length === 0) return;

        // 1. Si se excede el límite total, se descarta toda la selección
        if (archivosSeleccionados.length + nuevos.length > MAX_FILES) {
            Swal.fire({
                icon: 'warning',
                title: 'Límite de archivos excedido',
                html: `Solo puedes adjuntar hasta <b>${MAX_FILES}</b> archivos por ticket.<br>
                       Ya tienes <b>${archivosSeleccionados.length}</b> e intentaste agregar <b>${nuevos.length}</b>.`,
                confirmButtonColor: COLOR_PRIMARIO,
                confirmButtonText: 'Entendido',
            });
            return;
        }

        // 2. Validar extensión y tamaño
        const rechazados = [];
        nuevos.forEach(file => {
            const ext = obtenerExtension(file.name);
            if (!ALLOWED_EXTENSIONS.includes(ext)) {
                rechazados.push(`${file.name} — formato .${ext} no permitido`);
            } else if (file.size > MAX_SIZE_BYTES) {
                rechazados.push(`${file.name} — ${formatearTamano(file.size)}, supera ${FILE_MAX_SIZE_MB} MB`);
            } else {
                archivosSeleccionados.push(file);
            }
        });

        // 3. Avisar de los rechazados
        if (rechazados.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Archivos no válidos',
                html: `<ul class="text-start small">${rechazados.map(r => `<li>${escapeHtml(r)}</li>`).join('')}</ul>
                       <small class="text-muted">Permitidos: JPG, PNG, WEBP, PDF, Word, Excel y TXT (máx. ${FILE_MAX_SIZE_MB} MB c/u).</small>`,
                confirmButtonColor: COLOR_PRIMARIO,
                confirmButtonText: 'Aceptar',
            });
        }

        renderArchivos();
    }

    function eliminarArchivo(index) {
        archivosSeleccionados.splice(index, 1);
        renderArchivos();
    }

    function renderArchivos() {
        const contenedor = document.getElementById('lista-archivos-seleccionados');
        const contador = document.getElementById('contador-archivos');
        const total = archivosSeleccionados.length;

        contador.innerText = `${total} / ${MAX_FILES} archivos`;
        contador.className = `badge border fw-bold px-3 py-2 ${total === MAX_FILES ? 'bg-warning text-dark' : 'bg-light text-secondary'}`;

        // Liberar URLs de previsualizaciones anteriores
        urlsPreview.forEach(URL.revokeObjectURL);
        urlsPreview = [];

        contenedor.innerHTML = '';
        contenedor.style.display = total === 0 ? 'none' : 'flex';

        archivosSeleccionados.forEach((file, index) => {
            const esImagen = IMAGE_EXTENSIONS.includes(obtenerExtension(file.name));
            const urlPreview = esImagen ? URL.createObjectURL(file) : null;
            if (urlPreview) urlsPreview.push(urlPreview);

            const preview = esImagen
                ? `<img src="${urlPreview}" class="card-img-top img-preview" style="height: 80px; object-fit: cover; cursor: pointer;" title="Clic para ampliar">`
                : `<div class="d-flex align-items-center justify-content-center text-secondary" style="height: 80px;"><i class="bi bi-file-earmark-text fs-1"></i></div>`;

            const col = document.createElement('div');
            col.className = 'col-6 col-sm-4 col-md-3 col-lg-2';
            col.innerHTML = `
                <div class="card h-100 shadow-sm position-relative overflow-hidden bg-light" style="border-radius: 10px;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-0 btn-eliminar"
                        style="width: 22px; height: 22px; z-index: 2; line-height: 1;" title="Eliminar archivo">&times;</button>
                    ${preview}
                    <div class="p-1 text-center border-top bg-white">
                        <span class="d-block fw-bold text-truncate text-dark" style="font-size: 0.72rem;" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</span>
                        <span class="text-muted" style="font-size: 0.68rem;">${formatearTamano(file.size)}</span>
                    </div>
                </div>`;

            col.querySelector('.btn-eliminar').addEventListener('click', () => eliminarArchivo(index));
            col.querySelector('.img-preview')?.addEventListener('click', () => abrirModalPreview(urlPreview, file.name));

            contenedor.appendChild(col);
        });
    }

    inputArchivos.addEventListener('change', () => agregarArchivos(inputArchivos));

    // =================================================================
    //  Autoguardado de borrador
    // =================================================================
    let debounceTimer = null;
    let notificacionTimer = null;
    let draftAbortController = null;
    let enviandoFormulario = false;

    function mostrarNotificacionBorrador() {
        notificacionBorrador.classList.remove('d-none');
        clearTimeout(notificacionTimer);
        notificacionTimer = setTimeout(() => notificacionBorrador.classList.add('d-none'), 3000);
    }

    function cancelarBorrador() {
        clearTimeout(debounceTimer);
        draftAbortController?.abort();
    }

    async function guardarBorrador() {
        if (!URL_BORRADOR || enviandoFormulario) return;

        cancelarBorrador();
        draftAbortController = new AbortController();

        const formData = new FormData(form);
        formData.delete('archivo_adjunto[]'); // los archivos no se guardan en borradores

        try {
            const data = await postFormData(URL_BORRADOR, formData, draftAbortController.signal);
            if (enviandoFormulario) return;

            if (data.success && data.id_ticket) {
                document.getElementById('id_ticket').value = data.id_ticket;
                mostrarNotificacionBorrador();
            }
        } catch (error) {
            if (error.name !== 'AbortError') console.error('Error al guardar el borrador:', error);
        }
    }

    form.querySelectorAll('input:not([type="file"]), select, textarea').forEach(el => {
        el.addEventListener('input', () => {
            if (enviandoFormulario) return;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(guardarBorrador, DRAFT_DEBOUNCE_MS);
        });
    });

    // =================================================================
    //  Envío final
    // =================================================================
    const textoOriginalBtn = btnEnviar.innerHTML;

    function setEnviando(enviando) {
        enviandoFormulario = enviando;
        btnEnviar.disabled = enviando;
        btnEnviar.innerHTML = enviando
            ? '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...'
            : textoOriginalBtn;
    }

    function mostrarTicketCreado(ticket, urlTicket) {
        const filas = [
            ['Ticket', `<b class="text-primary">#${escapeHtml(ticket.id)}</b>`],
            ['Asunto', `<b>${escapeHtml(ticket.asunto)}</b>`],
            ['Servicio', escapeHtml(ticket.servicio)],
            ['Edificio', escapeHtml(ticket.edificio)],
            ticket.piso ? ['Piso', escapeHtml(ticket.piso)] : null,
            ['Lugar', escapeHtml(ticket.lugar)],
            ['Estado', `<span class="badge bg-primary">${escapeHtml(ticket.estado)}</span>`],
            ['Fecha', escapeHtml(ticket.fecha)],
        ].filter(Boolean);

        const tabla = filas
            .map(([etiqueta, valor]) => `<tr><th class="text-muted fw-semibold">${etiqueta}</th><td class="text-end text-break">${valor}</td></tr>`)
            .join('');

        Swal.fire({
            icon: 'success',
            title: '¡Ticket registrado con éxito!',
            html: `<table class="table table-sm small text-start mb-0">${tabla}</table>`,
            confirmButtonColor: COLOR_PRIMARIO,
            confirmButtonText: '<i class="bi bi-box-arrow-up-right me-1"></i> Ver ticket',
            showCancelButton: true,
            cancelButtonColor: '#6c757d',
            cancelButtonText: 'Ir a inicio',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(result => {
            window.location.replace(result.isConfirmed ? urlTicket : URL_HOME);
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        cancelarBorrador();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        setEnviando(true);

        const formData = new FormData(form);
        formData.delete('archivo_adjunto[]');
        archivosSeleccionados.forEach(file => formData.append('archivo_adjunto[]', file));

        try {
            const data = await postFormData(form.action, formData);

            if (data.success && data.ticket) {
                mostrarTicketCreado(data.ticket, data.redirect);
                return;
            }

            setEnviando(false);

            if (data.duplicado) {
                Swal.fire({
                    icon: 'info',
                    title: 'Ya estamos atendiendo tu solicitud',
                    text: data.message,
                    confirmButtonColor: COLOR_PRIMARIO,
                    confirmButtonText: 'Entendido',
                }).then(() => {
                    window.location.href = data.redirect || URL_HOME;
                });
                return;
            }

            // Errores de validación (422) u otros errores controlados
            console.error('Error al guardar el ticket:', data);
            const errores = data.errors
                ? Object.values(data.errors).flat()
                : [data.message || 'Ocurrió un error al procesar la solicitud.'];

            Swal.fire({
                icon: 'error',
                title: 'Error al registrar',
                html: errores.map(escapeHtml).join('<br>'),
                confirmButtonColor: COLOR_PRIMARIO,
                confirmButtonText: 'Revisar datos',
            });
        } catch (error) {
            setEnviando(false);
            console.error('Error en fetch al enviar ticket:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo comunicar con el servidor. Intenta nuevamente.',
                confirmButtonColor: COLOR_PRIMARIO,
            });
        }
    });
});