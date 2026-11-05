<footer class="mt-auto">
    <div class="fondoFooter" style="height:10px !important"></div>
    <div class="piePagina text-white">
        <div class="container">
            <div id="circulos" class="row py-4">
                <div class="col-12 col-md-9">
                    <div id="identidad">
                        <span class="fw-bold">FACULTAD DE INGENIERÍA</span><br />
                        <span>Departamento de Mantenimiento</span><br />
                        <span>Av. Manuel Nava No. 8, Zona Universitaria, CP 78290</span><br />
                        <span>San Luis Potosí, S.L.P. México</span><br />
                        @if (!empty($config?->telefono_contacto))
                            <span>Tel. {{ $config->telefono_contacto }}</span><br />
                        @endif
                        @if (!empty($config?->correo_contacto))
                            <span>Contacto: {{ $config->correo_contacto }}</span><br />
                        @endif
                        <span class="small">© {{ date('Y') }} Todos los derechos reservados</span><br />
                    </div>
                </div>
                <div class="col-12 col-md-3 mt-4 mt-md-0">
                    <h5 class="text-white">Enlaces Rápidos</h5>
                    <div class="d-flex flex-column">
                        <a href="#" class="text-white text-decoration-none mb-2">Inicio</a>
                        <a href="#" class="text-white text-decoration-none mb-2">Manual de Usuario</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
