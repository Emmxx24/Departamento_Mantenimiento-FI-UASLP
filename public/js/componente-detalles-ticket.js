function getTicketUrl() {
    const container = document.getElementById('ticket-container');
    return container ? container.dataset.url : null;
}

// Función auxiliar que hace el fetch real a la BD
function realizarPeticion(campo, valor) {

    const url = getTicketUrl();
    if (!url) {
        console.error('No se encontró la URL del ticket');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ campo, valor })
    }).then(response => response.json()).then(data => {
        if (data.success) {

            const nombresAmigables = {
                'asunto': 'Asunto',
                'descripcion': 'Descripción',
                'piso': 'Piso',
                'id_edificio': 'Edificio',
                'id_aula': 'Lugar',
                'id_servicio': 'Servicio',
                'prioridad': 'Prioridad',
                'estado': 'Estado',
                'id_admin_atiende': 'Administrador'
            };
            const campoAmigable = nombresAmigables[campo] || campo;

            Swal.fire({
                title: 'Éxito',
                text: `${campoAmigable} actualizado correctamente.`,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });

            // ACTUALIZACIÓN DINÁMICA DEL DOM (Sin recargar)
            if (campo === 'estado') {
                const btnEstado = document.getElementById('dropdownEstadoBtn');
                if (btnEstado) {
                    // Actualizamos el texto del botón
                    btnEstado.textContent = valor;

                    // Limpiamos los colores anteriores
                    btnEstado.classList.remove('bg-primary', 'bg-warning', 'bg-info', 'bg-success', 'bg-secondary', 'text-white', 'text-dark');

                    // Diccionario de clases según el estado
                    const coloresEstado = {
                        'Pendiente': ['bg-primary', 'text-white'],
                        'Atendiendo': ['bg-warning', 'text-dark'],
                        'Terminado': ['bg-success', 'text-white'],
                        'Cerrado': ['bg-secondary', 'text-white']
                    };

                    // Aplicamos el nuevo color
                    if (coloresEstado[valor]) {
                        btnEstado.classList.add(...coloresEstado[valor]);
                    }

                    // Bloqueamos la interfaz si el ticket concluye
                    if (valor === 'Cerrado' || valor === 'Terminado') {
                        btnEstado.disabled = true;
                        const btnImportancia = document.getElementById('dropdownImportanciaBtn');
                        if (btnImportancia) btnImportancia.disabled = true;

                        // Bloqueamos también los selects de la columna izquierda
                        document.querySelectorAll('#ticket-container select').forEach(sel => sel.disabled = true);

                        // Ocultamos botones de edición
                        const btnEditAsunto = document.getElementById('btn-edit-asunto');
                        if (btnEditAsunto) btnEditAsunto.style.display = 'none';
                        const btnEditDescripcion = document.getElementById('btn-edit-descripcion');
                        if (btnEditDescripcion) btnEditDescripcion.style.display = 'none';

                        // Ocultamos botón de generar requisición o continuar
                        const btnGenerarReq = document.getElementById('btn-generar-requisicion');
                        if (btnGenerarReq) btnGenerarReq.style.display = 'none';
                        const btnContinuarReq = document.getElementById('btn-continuar-requisicion');
                        if (btnContinuarReq) btnContinuarReq.style.display = 'none';

                        // Bloqueamos el chat
                        const chatForm = document.getElementById('form-chat-ticket');
                        if (chatForm) {
                            const alertHtml = `
                            <div class="alert text-center fw-bold text-dark border-0 shadow-sm" style="background-color: #e9ecef; border-radius: 15px;">
                                <i class="bi bi-lock-fill me-2 text-secondary"></i> El ticket ha sido cerrado. El chat es de solo lectura.
                            </div>`;
                            chatForm.insertAdjacentHTML('beforebegin', alertHtml);
                            chatForm.style.display = 'none';
                        }
                    }
                }
            } else if (campo === 'prioridad') {
                const btnImportancia = document.getElementById('dropdownImportanciaBtn');
                if (btnImportancia) {
                    btnImportancia.textContent = valor;
                }
            } else if (campo === 'id_admin_atiende') {
                const adminLabel = document.getElementById('label-admin');
                if (adminLabel) {
                    // Quita las clases rojas y de alerta, y regresa a text-primary
                    adminLabel.className = 'form-label fw-bold mb-0 text-nowrap text-primary';
                }
            }
        } else {
            Swal.fire({ title: 'Error', text: data.message || 'No se pudo actualizar', icon: 'error' });
        }
    }).catch(() => {
        Swal.fire({ title: 'Error', text: 'Fallo de conexión al servidor', icon: 'error' });
    });
}

function updateTicket(campo, valor) {
    console.log(`Actualizando ticket: campo=${campo}, valor=${valor}`);
    // CONFIRMACIÓN AL CERRAR O TERMINAR (PUNTO 1)
    if (campo === 'estado' && (valor === 'Cerrado' || valor === 'Terminado')) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: valor === 'Cerrado' ? 'El ticket se cerrará sin dar solución.' : 'El ticket se marcará como terminado.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                realizarPeticion(campo, valor);
            }
        });
    } else {
        // Si es cualquier otro campo (lugar, edificio, etc) se guarda directo
        realizarPeticion(campo, valor);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    let edificio = document.getElementById('edificio');
    if (edificio) updateFloor(edificio);
});

function updateFloor(edificioElement) {
    const piso = document.getElementById('campo-piso');
    if (!piso) return;

    const selectEdificio = edificioElement.options ? edificioElement : document.getElementById('edificio');
    const pisosRaw = selectEdificio.options[selectEdificio.selectedIndex]?.getAttribute('data-pisos');
    const pisos = JSON.parse(pisosRaw);
    const pisoActual = piso.dataset.pisoActual;

    piso.innerHTML = '<option value="" selected disabled>Selecciona piso</option>';

    if (!pisos) return;

    for (let i = 0; i < pisos.length; i++) {
        let currentPiso = pisos[i];
        let texto = (currentPiso.piso === 0) ? "Planta baja" : "Piso " + currentPiso.piso;
        if (currentPiso.activo === 0) {
            texto += " (Desactivado)";
        }
        let option = document.createElement('option');
        option.value = currentPiso.piso;
        option.text = texto;
        if (pisoActual == currentPiso.piso) {
            option.selected = true;
        }
        option.setAttribute('data-aulas', JSON.stringify(currentPiso.aulas));
        piso.appendChild(option);
    }

    updateLugares()
}

function updateLugares() {
    const selectLugar = document.getElementById('campo-aula');
    const selectPiso = document.getElementById('campo-piso');
    if (!selectLugar || !selectPiso) return;

    const selectedPisoOption = selectPiso.options[selectPiso.selectedIndex];
    const lugaresRaw = selectedPisoOption?.getAttribute('data-aulas');

    const selectedLugarOption = selectLugar.options[selectLugar.selectedIndex];
    const lugarActual = (selectedLugarOption && selectedLugarOption.value) ?
        selectedLugarOption.value : (selectLugar.dataset.lugarActual || null);

    selectLugar.innerHTML = '<option value="" selected disabled>Selecciona lugar</option>';
    if (!lugaresRaw) return;

    const lugares = JSON.parse(lugaresRaw);
    if (!lugares) return;

    for (let i = 0; i < lugares.length; i++) {
        let currentAula = lugares[i];
        let texto = currentAula.nombre;
        if (currentAula.activo === 0) {
            texto += " (Desactivada)";
        }
        let option = document.createElement('option');
        option.value = currentAula.id_aula;
        option.text = texto;

        if (currentAula.id_aula == lugarActual) {
            option.selected = true;
        }
        option.setAttribute('data-id-aula', currentAula.id_aula);
        selectLugar.appendChild(option);
    }
}

function toggleEditField(campo, isTextarea = false) {
    const input = document.getElementById(`campo-${campo}`);
    if (!input) return;

    if (!input.disabled) {
        input.focus();
        return;
    }

    input.dataset.previo = input.value.trim();
    input.disabled = false;
    input.focus();
    if (!isTextarea) {
        input.select();
    }

    const finishEdit = () => {
        input.removeEventListener('blur', onBlur);
        input.removeEventListener('keydown', onKeyDown);

        input.disabled = true;
        const nuevoValor = input.value.trim();
        const valorAnterior = input.dataset.previo;

        if (nuevoValor !== valorAnterior) {
            input.dataset.previo = nuevoValor;
            updateTicket(campo, nuevoValor);
        }
    };

    const onBlur = () => {
        finishEdit();
    };

    const onKeyDown = (e) => {
        if (e.key === 'Enter' && !isTextarea) {
            e.preventDefault();
            input.blur();
        } else if (e.key === 'Escape') {
            input.value = input.dataset.previo;
            input.blur();
        }
    };

    input.addEventListener('blur', onBlur);
    input.addEventListener('keydown', onKeyDown);
}

window.toggleEditField = toggleEditField;
window.updateTicket = updateTicket;
window.updateFloor = updateFloor;
window.updateLugares = updateLugares;