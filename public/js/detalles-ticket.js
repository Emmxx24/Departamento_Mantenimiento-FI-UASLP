// =====================================================================
//  Lógica de Detalles de Ticket: Chat en Tiempo Real (Smart Polling),
//  Adjuntos y Bóveda de Evidencias
// =====================================================================

document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.querySelector('.chat-messages');
    const chatForm = document.getElementById('form-chat-ticket');
    if (!chatContainer && !chatForm) return;

    // Configuración y constantes (idénticas a crear-ticket)
    const MAX_FILES = 5;
    const FILE_MAX_SIZE_MB = 10;
    const MAX_SIZE_BYTES = FILE_MAX_SIZE_MB * 1024 * 1024;
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
    const COLOR_PRIMARIO = '#004A98';
    const POLLING_INTERVAL_MS = 5000;

    // Elementos del DOM
    const textoInput = chatForm ? chatForm.querySelector('input[name="texto_mensaje"]') : null;
    const chatFileInput = document.getElementById('chat-attachment');
    const chipsContainer = document.getElementById('chips-archivos-chat');
    const submitBtn = document.getElementById('btn-enviar-chat');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Estado local
    let archivosChat = [];
    let isSubmitting = false;

    // -----------------------------------------------------------------
    //  Utilidades
    // -----------------------------------------------------------------
    const obtenerExtension = (nombre) => nombre.split('.').pop().toLowerCase();

    function formatearTamano(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function escapeHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function scrollAlFondo(suave = false) {
        if (!chatContainer) return;
        chatContainer.scrollTo({
            top: chatContainer.scrollHeight,
            behavior: suave ? 'smooth' : 'auto'
        });
    }

    // Scroll inicial al abrir el ticket
    scrollAlFondo(false);

    // -----------------------------------------------------------------
    //  Activación / Desactivación del botón de Enviar
    // -----------------------------------------------------------------
    function actualizarEstadoBotonEnviar() {
        if (!submitBtn) return;
        const texto = textoInput ? textoInput.value.trim() : '';
        const tieneArchivos = archivosChat.length > 0;
        const puedeEnviar = (texto.length > 0 || tieneArchivos) && !isSubmitting;

        submitBtn.disabled = !puedeEnviar;
        if (puedeEnviar) {
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            submitBtn.title = 'Enviar mensaje';
        } else {
            submitBtn.style.opacity = '0.45';
            submitBtn.style.cursor = 'not-allowed';
            submitBtn.title = 'Escribe un mensaje o adjunta archivos para enviar';
        }
    }

    if (textoInput) {
        textoInput.addEventListener('input', actualizarEstadoBotonEnviar);
        textoInput.addEventListener('keyup', actualizarEstadoBotonEnviar);
        textoInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                const texto = textoInput.value.trim();
                const tieneArchivos = archivosChat.length > 0;
                if (!texto && !tieneArchivos) {
                    e.preventDefault();
                }
            }
        });
    }

    // -----------------------------------------------------------------
    //  Gestión y Validación de Archivos Adjuntos (Igual a Crear Ticket)
    // -----------------------------------------------------------------
    function renderChips() {
        if (!chipsContainer) return;
        chipsContainer.innerHTML = '';

        archivosChat.forEach((file, index) => {
            const ext = obtenerExtension(file.name);
            let iconClass = 'bi-file-earmark-text text-secondary';
            if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) iconClass = 'bi-file-earmark-image text-info';
            else if (ext === 'pdf') iconClass = 'bi-file-earmark-pdf text-danger';
            else if (['doc', 'docx'].includes(ext)) iconClass = 'bi-file-earmark-word text-primary';
            else if (['xls', 'xlsx'].includes(ext)) iconClass = 'bi-file-earmark-excel text-success';

            const chip = document.createElement('div');
            chip.className = 'badge bg-white text-dark border border-secondary-subtle d-flex align-items-center gap-1 py-1 px-2 shadow-sm rounded-pill';
            chip.style.fontSize = '0.82rem';
            chip.innerHTML = `
                <i class="bi ${iconClass} me-1"></i>
                <span class="text-truncate" style="max-width: 140px;" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</span>
                <button type="button" class="btn-close ms-1 btn-eliminar-chip" style="font-size: 0.55rem; padding: 2px;" aria-label="Eliminar archivo"></button>
            `;

            chip.querySelector('.btn-eliminar-chip').addEventListener('click', (e) => {
                e.preventDefault();
                archivosChat.splice(index, 1);
                renderChips();
                actualizarEstadoBotonEnviar();
            });

            chipsContainer.appendChild(chip);
        });

        actualizarEstadoBotonEnviar();
    }

    if (chatFileInput) {
        chatFileInput.addEventListener('change', () => {
            const nuevos = Array.from(chatFileInput.files || []);
            chatFileInput.value = ''; // Permite volver a seleccionar el mismo archivo
            if (nuevos.length === 0) return;

            // 1. Validar límite máximo de archivos por mensaje
            if (archivosChat.length + nuevos.length > MAX_FILES) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Límite de archivos excedido',
                    html: `Solo puedes adjuntar hasta <b>${MAX_FILES}</b> archivos por mensaje.<br>
                           Actualmente tienes <b>${archivosChat.length}</b> e intentaste agregar <b>${nuevos.length}</b>.`,
                    confirmButtonColor: COLOR_PRIMARIO,
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // 2. Validar extensiones y pesos
            const rechazados = [];
            nuevos.forEach(file => {
                const ext = obtenerExtension(file.name);
                const esDuplicado = archivosChat.some(f => f.name === file.name && f.size === file.size);

                if (esDuplicado) {
                    return; // Ignorar silenciosamente si ya estaba seleccionado
                }

                if (!ALLOWED_EXTENSIONS.includes(ext)) {
                    rechazados.push(`${file.name} — formato .${ext} no permitido`);
                } else if (file.size > MAX_SIZE_BYTES) {
                    rechazados.push(`${file.name} — ${formatearTamano(file.size)}, supera ${FILE_MAX_SIZE_MB} MB`);
                } else {
                    archivosChat.push(file);
                }
            });

            // 3. Notificar rechazados
            if (rechazados.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivos no válidos',
                    html: `
                        <p class="mb-2">Los siguientes archivos no se pudieron agregar:</p>
                        <ul class="text-start small mb-2">${rechazados.map(r => `<li>${escapeHtml(r)}</li>`).join('')}</ul>
                        <p class="small text-muted mb-0">Permitidos: JPG, PNG, WEBP, PDF, Word, Excel y TXT (máx. ${FILE_MAX_SIZE_MB} MB c/u).</p>
                    `,
                    confirmButtonColor: COLOR_PRIMARIO,
                    confirmButtonText: 'Aceptar'
                });
            }

            renderChips();
        });
    }

    // -----------------------------------------------------------------
    //  Envío del Mensaje por AJAX
    // -----------------------------------------------------------------
    if (chatForm) {
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const texto = textoInput ? textoInput.value.trim() : '';
            const tieneArchivos = archivosChat.length > 0;

            if ((!texto && !tieneArchivos) || isSubmitting) return;

            isSubmitting = true;
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 0.85rem; height: 0.85rem;" role="status"></span>';
            }

            const formData = new FormData();
            formData.append('texto_mensaje', texto);
            archivosChat.forEach(file => formData.append('archivos[]', file));

            try {
                const response = await fetch(chatForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                if (response.status === 403) {
                    bloquearChatEnDOM();
                    throw new Error('El ticket concluyó, este chat es de sólo lectura.');
                }

                // Si la sesión expiró (419, 401 o redirección al login)
                if (response.status === 419 || response.status === 401 || response.redirected) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesión expirada',
                        text: 'Tu sesión ha expirado por inactividad. Serás redirigido para iniciar sesión nuevamente.',
                        confirmButtonColor: COLOR_PRIMARIO
                    }).then(() => {
                        window.location.href = response.url || window.location.origin;
                    });
                    return;
                }

                // Blindaje: verificar que la respuesta sea JSON antes de parsear
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('Ocurrió un error inesperado en el servidor. Por favor, recarga la página.');
                }

                const data = await response.json();

                if (!response.ok || !data.success) {
                    const mensajeError = data.errors
                        ? Object.values(data.errors).flat().join('<br>')
                        : (data.message || 'No se pudo enviar el mensaje.');
                    throw new Error(mensajeError);
                }

                // Éxito: Quitar placeholder vacío si existe
                chatContainer?.querySelector('.empty-chat-placeholder')?.remove();

                // Inyectar burbuja de mensaje y actualizar ID
                if (chatContainer && data.html) {
                    chatContainer.insertAdjacentHTML('beforeend', data.html);
                    if (data.mensaje?.id_mensaje) {
                        chatContainer.dataset.ultimoId = data.mensaje.id_mensaje;
                    }
                    scrollAlFondo(true);
                }

                // Inyectar en la Bóveda de Evidencias
                if (data.bovedaHTML) {
                    const bovedaList = document.querySelector('#lista-archivos-boveda');
                    if (bovedaList) {
                        document.getElementById('boveda-vacia')?.remove();
                        bovedaList.insertAdjacentHTML('beforeend', data.bovedaHTML);
                    }
                }

                // Resetear estado del formulario
                if (textoInput) textoInput.value = '';
                archivosChat = [];
                renderChips();

            } catch (error) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    html: error.message || 'Ocurrió un error al enviar el mensaje.',
                    confirmButtonColor: COLOR_PRIMARIO
                });
            } finally {
                isSubmitting = false;
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="bi bi-send-fill" style="font-size: 0.85rem; margin-left: 2px;"></i>';
                }
                actualizarEstadoBotonEnviar();
                if (textoInput && !chatContainer.dataset.isLocked) {
                    textoInput.focus();
                }
            }
        });
    }

    // Inicializar estado del botón
    actualizarEstadoBotonEnviar();

    // -----------------------------------------------------------------
    //  Smart Polling (Consulta periódica sin WebSockets)
    // -----------------------------------------------------------------
    let pollingTimer = null;
    let isPollingInFlight = false;

    function bloquearChatEnDOM() {
        if (!chatContainer) return;
        chatContainer.dataset.isLocked = '1';
        detenerPolling();

        const form = document.getElementById('form-chat-ticket');
        if (form) {
            const alertHtml = `
                <div class="alert alert-secondary text-center fw-bold text-dark border-0 shadow-sm rounded-4 mt-3">
                    <i class="bi bi-lock-fill me-2 text-secondary"></i> El ticket ha sido cerrado. El chat es de solo lectura.
                </div>`;
            form.insertAdjacentHTML('beforebegin', alertHtml);
            form.remove();
        }
    }

    async function consultarMensajesNuevos() {
        if (!chatContainer) return;
        if (document.hidden) return; // Pausado si la pestaña no es visible
        if (chatContainer.dataset.isLocked === '1') {
            detenerPolling();
            return;
        }
        if (isPollingInFlight) return; // Evitar consultas superpuestas

        const pollUrl = chatContainer.dataset.pollUrl;
        if (!pollUrl) return;

        const ultimoId = chatContainer.dataset.ultimoId || 0;
        isPollingInFlight = true;

        try {
            const response = await fetch(`${pollUrl}?ultimo_id=${encodeURIComponent(ultimoId)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok || response.redirected) return;

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) return;

            const data = await response.json();

            // Si el ticket fue cerrado remotamente
            if (data.isLocked) {
                bloquearChatEnDOM();
                return;
            }

            // Si llegaron mensajes nuevos
            if (data.nuevos && data.html) {
                // Verificar si el usuario estaba al final del chat antes de inyectar
                const isNearBottom = (chatContainer.scrollHeight - chatContainer.scrollTop - chatContainer.clientHeight) < 70;

                // Quitar placeholder de chat vacío
                chatContainer.querySelector('.empty-chat-placeholder')?.remove();

                // Inyectar nuevos globos
                chatContainer.insertAdjacentHTML('beforeend', data.html);
                chatContainer.dataset.ultimoId = data.ultimo_id;

                // Inyectar en la Bóveda de Evidencias
                if (data.bovedaHTML) {
                    const bovedaList = document.querySelector('#lista-archivos-boveda');
                    if (bovedaList) {
                        document.getElementById('boveda-vacia')?.remove();
                        bovedaList.insertAdjacentHTML('beforeend', data.bovedaHTML);
                    }
                }

                // Auto-scroll solo si el usuario ya estaba mirando abajo
                if (isNearBottom) {
                    scrollAlFondo(true);
                }
            }
        } catch (err) {
            console.debug('Error en polling de chat:', err);
        } finally {
            isPollingInFlight = false;
        }
    }

    function iniciarPolling() {
        if (pollingTimer) clearInterval(pollingTimer);
        if (!chatContainer || chatContainer.dataset.isLocked === '1') return;
        pollingTimer = setInterval(consultarMensajesNuevos, POLLING_INTERVAL_MS);
    }

    function detenerPolling() {
        if (pollingTimer) {
            clearInterval(pollingTimer);
            pollingTimer = null;
        }
    }

    // Detener/Reanudar con Page Visibility API (ahorro de batería y ancho de banda)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            detenerPolling();
        } else {
            consultarMensajesNuevos();
            iniciarPolling();
        }
    });

    // Iniciar si el chat no está bloqueado
    if (chatContainer && chatContainer.dataset.isLocked !== '1') {
        iniciarPolling();
    }
});