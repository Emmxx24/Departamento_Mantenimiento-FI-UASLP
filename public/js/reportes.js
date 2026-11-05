

let reporteIdActual = null;

function mostrarModal(mensaje, tipo = 'success') {
    const modalId = tipo === 'success' ? 'modalSuccess' : 'modalError';
    const modalElement = document.getElementById(modalId);

    if (!modalElement) return;

    const message = document.getElementById(modalId + 'Message');
    if (message) message.innerText = mensaje;

    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}
//  GENERAR REPORTE
document.getElementById('formReporteManual').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    // limpiar vacíos
    for (let [key, value] of formData.entries()) {
        if (!value) formData.delete(key);
    }

    fetch(window.routes.generar, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: formData
})
    .then(async res => {
    if (!res.ok) {
        const text = await res.text();
        console.error("Respuesta del servidor:", text);
        throw new Error("Error en servidor");
    }
    return res.json();
})
    .then(data => {

        if(data.success){

            reporteIdActual = data.reporte_id;

            const seccionExportar = document.getElementById('seccionExportar');
            if (seccionExportar) {
                seccionExportar.classList.remove('d-none');
            }

            document.getElementById('btnExportPDF').disabled = false;
            document.getElementById('btnExportExcel').disabled = false;

            document.getElementById('resultadosReporte').classList.remove('d-none');

            mostrarModal("Reporte generado correctamente", "success");
        }
    })
    .catch(err => {
        console.error(err);

        alert("Error al generar reporte");
    });
});


// PDF
document.getElementById('btnExportPDF').addEventListener('click', function() {
    if(reporteIdActual){
         window.location.href = window.routes.pdf + "?reporte_id=" + reporteIdActual;
    }
});


//  EXCEL
document.getElementById('btnExportExcel').addEventListener('click', function() {
    if(reporteIdActual){
        window.location.href = window.routes.excel + "?reporte_id=" + reporteIdActual;
    }
});

