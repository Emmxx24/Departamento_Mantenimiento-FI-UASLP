/**
 * Prevención Universal de Doble Submit
 *
 * Bloquea el reenvío múltiple de formularios, deshabilita el botón de acción
 * insertando un spinner Bootstrap dinámico y desactiva botones de cancelar/cerrar
 * en formularios estándar y modales durante el procesamiento en vuelo.
 */
(function () {
    'use strict';

    function obtenerTextoSpinner(btn) {
        if (btn.dataset.loadingText) {
            return btn.dataset.loadingText;
        }

        const texto = (btn.textContent || '').trim().toLowerCase();

        if (texto.includes('iniciar') || texto.includes('login') || texto.includes('acceder')) {
            return 'Iniciando sesión...';
        }
        if (texto.includes('importar')) {
            return 'Importando...';
        }
        if (texto.includes('eliminar')) {
            return 'Eliminando...';
        }
        if (texto.includes('desactivar')) {
            return 'Desactivando...';
        }
        if (texto.includes('reactivar')) {
            return 'Reactivando...';
        }
        if (texto.includes('enviar')) {
            return 'Enviando...';
        }
        if (texto.includes('actualizar')) {
            return 'Actualizando...';
        }
        if (texto.includes('restablecer') || texto.includes('cambiar')) {
            return 'Guardando...';
        }

        return 'Guardando...';
    }

    function bloquearFormulario(form, submitter) {
        if (!form) return;

        // 1. Marcar el formulario como en proceso de envío
        form.dataset.submitting = 'true';

        // 2. Identificar el botón de submit activo o principal
        let btn = submitter;
        if (!btn || (btn.type !== 'submit' && btn.getAttribute('type') !== 'submit')) {
            const formId = form.getAttribute('id');
            const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            if (submitButtons.length > 0) {
                btn = submitButtons[0];
            } else if (formId) {
                btn = document.querySelector(`button[type="submit"][form="${formId}"], input[type="submit"][form="${formId}"]`);
            }
        }

        // 3. Aplicar spinner y deshabilitar botón de submit
        if (btn) {
            // Guardar ancho actual para evitar saltos bruscos de diseño
            if (btn.offsetWidth > 0 && !btn.style.minWidth) {
                btn.style.minWidth = `${btn.offsetWidth}px`;
            }

            // Guardar HTML original si no se ha respaldado
            if (!btn.dataset.originalHtml) {
                btn.dataset.originalHtml = btn.innerHTML;
            }

            // Si el botón no contiene ya un spinner
            if (!btn.querySelector('.spinner-border')) {
                const label = obtenerTextoSpinner(btn);
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${label}`;
            }

            // Deshabilitar botón
            btn.disabled = true;
            btn.classList.add('disabled');
        }

        // 4. Deshabilitar botones de cancelar / cerrar / dismiss en modales
        const modal = form.closest('.modal');
        if (modal) {
            const dismissButtons = modal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close, .btn-outline-secondary, .btn-outline-danger, #btnCancelarImport');
            dismissButtons.forEach(b => {
                if (b.tagName === 'BUTTON' || b.tagName === 'INPUT') {
                    b.disabled = true;
                }
                b.classList.add('disabled');
                b.setAttribute('aria-disabled', 'true');
                b.style.pointerEvents = 'none';
            });

            // Evitar que el modal se cierre con clic exterior o tecla Escape mientras se envía
            modal.setAttribute('data-bs-backdrop', 'static');
            modal.setAttribute('data-bs-keyboard', 'false');
        }

        // 5. Deshabilitar botones de cancelar dentro del propio formulario
        const formCancelButtons = form.querySelectorAll('[data-bs-dismiss="modal"], .btn-outline-secondary, .btn-outline-danger, #btnCancelarImport');
        formCancelButtons.forEach(b => {
            if (b.tagName === 'BUTTON' || b.tagName === 'INPUT') {
                b.disabled = true;
            }
            b.classList.add('disabled');
            b.setAttribute('aria-disabled', 'true');
            b.style.pointerEvents = 'none';
        });
    }

    // Listener global de evento submit
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        // Omitir si el formulario tiene opt-out explícito
        if (form.hasAttribute('data-no-submit-lock') || form.classList.contains('no-submit-lock')) {
            return;
        }

        // Si ya fue prevenido por validaciones previas de JavaScript
        if (e.defaultPrevented) {
            return;
        }

        // Si la validación nativa HTML5 falla
        if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
            return;
        }

        // Prevenir doble envío si ya está procesándose
        if (form.dataset.submitting === 'true') {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
        }

        bloquearFormulario(form, e.submitter);
    }, false);

    // Restaurar estado si la página se recupera de bfcache (atrás / adelante)
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form[data-submitting="true"]').forEach(form => {
            delete form.dataset.submitting;
            const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('disabled');
                if (btn.dataset.originalHtml) {
                    btn.innerHTML = btn.dataset.originalHtml;
                }
            });

            const cancelButtons = form.querySelectorAll('.disabled[aria-disabled="true"]');
            cancelButtons.forEach(b => {
                b.disabled = false;
                b.classList.remove('disabled');
                b.removeAttribute('aria-disabled');
                b.style.pointerEvents = '';
            });

            const modal = form.closest('.modal');
            if (modal) {
                modal.querySelectorAll('.disabled[aria-disabled="true"]').forEach(b => {
                    b.disabled = false;
                    b.classList.remove('disabled');
                    b.removeAttribute('aria-disabled');
                    b.style.pointerEvents = '';
                });
            }
        });
    });

    // Exponer helper global por si algún flujo requiere bloqueo manual
    window.lockFormSubmit = bloquearFormulario;
})();

