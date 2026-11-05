@props(['reportes'])

<div class="modal fade" id="modalHistorialReportes" tabindex="-1" aria-labelledby="modalHistorialReportesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            
            {{-- Encabezado Azul --}}
            <div class="modal-header text-white px-4 py-3" style="background-color: #004b93;">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0" id="modalHistorialReportesLabel" style="font-size: 1.15rem;">
                    <i class="bi bi-folder-fill text-warning"></i>
                    Reportes Generados
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Cuerpo del Modal con scroll --}}
            <div class="modal-body p-4 bg-light">
                <p class="text-secondary small mb-4">
                    Listado de todos los reportes generados y almacenados en el sistema.
                </p>

                @forelse($reportes as $reporte)
                    
                    {{-- Evaluar si tiene PDF guardado --}}
                    @if($reporte->ruta_pdf)
                    <div class="card border rounded-4 bg-white p-3 shadow-sm mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 44px; height: 44px; background-color: #ffeef0;">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">
                                        reporte_{{ $reporte->id_reporte }}.pdf
                                    </h6>
                                    <span class="small text-muted d-block mt-1">
                                        <i class="bi bi-arrow-return-right me-1"></i>
                                        Reporte #{{ $reporte->id_reporte }} - Documento PDF
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3 ps-2">
                                <span class="small text-muted text-nowrap">
                                    {{ \Carbon\Carbon::parse($reporte->fecha_rep)->format('d/m/Y H:i') }}
                                </span>
                                {{-- Asegúrate de que esta URL coincida con tu ruta web.php para descargar PDF --}}
                                <a href="{{ url('reportes/exportar-pdf?reporte_id=' . $reporte->id_reporte) }}" target="_blank" class="text-primary fs-5 p-1" title="Descargar PDF">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Evaluar si tiene Excel guardado --}}
                    @if($reporte->ruta_excel)
                    <div class="card border rounded-4 bg-white p-3 shadow-sm mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 44px; height: 44px; background-color: #e8f7ee;">
                                    <i class="bi bi-file-earmark-excel-fill text-success fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">
                                        reporte_{{ $reporte->id_reporte }}.xlsx
                                    </h6>
                                    <span class="small text-muted d-block mt-1">
                                        <i class="bi bi-arrow-return-right me-1"></i>
                                        Reporte #{{ $reporte->id_reporte }} - Hoja de Cálculo
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3 ps-2">
                                <span class="small text-muted text-nowrap">
                                    {{ \Carbon\Carbon::parse($reporte->fecha_rep)->format('d/m/Y H:i') }}
                                </span>
                                {{-- Asegúrate de que esta URL coincida con tu ruta web.php para descargar Excel --}}
                                <a href="{{ url('reportes/exportar-excel?reporte_id=' . $reporte->id_reporte) }}" target="_blank" class="text-success fs-5 p-1" title="Descargar Excel">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                @empty
                    {{-- Estado vacío si no se ha exportado ningún reporte aún --}}
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                        No hay documentos generados todavía.
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</div>