<?php

use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\EdificioController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LugarController;
use App\Http\Controllers\PoaController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\RequisicionController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketMensajeController;
use App\Http\Controllers\PasswordOtpController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Ruta raíz del sistema (redirige según si tiene sesión o no)
Route::get('/', function () {
    return Auth::check() ? redirect()->route('home') : redirect()->route('login');
});

// Rutas públicas
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
});

// Rutas de recuperación de contraseña (Solo disponibles si el sistema está inicializado en Modo Local)
Route::middleware(['guest', 'modo.local'])->group(function () {
    Route::get('/password/recuperar', [PasswordOtpController::class, 'mostrarSolicitud'])->name('password.request');
    Route::post('/password/recuperar', [PasswordOtpController::class, 'enviarCodigo'])
        ->middleware('throttle:3,60')
        ->name('password.email');
    Route::get('/password/verificar', [PasswordOtpController::class, 'mostrarVerificacion'])->name('password.verificar');
    Route::post('/password/verificar', [PasswordOtpController::class, 'verificarYActualizar'])
        ->middleware('throttle:10,1')
        ->name('password.update');
});

// ===================================================================
// RUTAS AUTENTICADAS (cualquier rol)
// ===================================================================
// Logout accesible para cualquier usuario con sesión activa
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Rutas autenticadas que requieren sistema inicializado y modo local activo
Route::middleware(['role:Superadministrador,Administrador,Solicitante', 'sistema.inicializado', 'modo.local'])->group(function () {

    // Actualizar contraseña desde el perfil (Solo en Modo Local)
    Route::put('/perfil/password', [PasswordOtpController::class, 'actualizarDesdePerfil'])
        ->name('perfil.password.update');

    // Home (maneja ambos roles internamente)
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Ver tickets (maneja ambos roles internamente)
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');

    // ---- Tickets ----
    // Crear ticket (accesible para ambos roles)
    Route::get('/tickets/crear', [TicketController::class, 'create'])->name('tickets.crear');
    Route::post('/tickets/crear', [TicketController::class, 'store'])->name('tickets.store');
    Route::post('/tickets/borrador', [TicketController::class, 'guardarBorrador'])->name('tickets.borrador');

    // Borradores de tickets
    Route::get('/tickets/borradores', [TicketController::class, 'borradores'])->name('tickets.borradores');
    Route::delete('/tickets/borradores/{id}', [TicketController::class, 'eliminarBorrador'])->name('tickets.borrador.eliminar');

    // Detalle unificado de ticket
    Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
    Route::put('/tickets/{id}/cancelar', [TicketController::class, 'cancelar'])->name('tickets.cancelar');

    // Chat y archivos (compartido por ambos roles)
    Route::post('/tickets/{id}/mensaje', [TicketMensajeController::class, 'store'])->name('tickets.mensaje');
    Route::get('/tickets/{id}/mensajes/nuevos', [TicketMensajeController::class, 'obtenerMensajesNuevos'])->name('tickets.mensajes.nuevos');
    Route::get('/tickets/archivos/{id}', [ArchivoController::class, 'descargar'])->name('archivo.descargar');
    Route::get('/tickets/archivos/descargar/{id}', [ArchivoController::class, 'forzarDescarga'])->name('archivo.fdescargar');
});

// ===================================================================
// RUTAS SOLO ADMINISTRADOR
// ===================================================================
Route::middleware(['role:Superadministrador,Administrador', 'sistema.inicializado', 'modo.local'])->group(function () {

    // Actualizar estado o importancia del ticket vía AJAX
    Route::put('/admin/tickets/{id}/detalles', [AdminTicketController::class, 'updateDetalles'])->name('admin.tickets.detalles');

    // ---- Inventario ----
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario');
    Route::get('/inventario/crear', [InventarioController::class, 'create'])->name('inventario.create');
    Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store');
    Route::get('/inventario/{id}/editar', [InventarioController::class, 'edit'])->name('inventario.edit');
    Route::put('/inventario/{id}', [InventarioController::class, 'update'])->name('inventario.update');
    Route::delete('/inventario/{id}', [InventarioController::class, 'destroy'])->name('inventario.destroy');
    Route::put('/inventario/{id}/reactivar', [InventarioController::class, 'reactivar'])->name('inventario.reactivar');
    Route::get('/inventario/{material}/imagen', [InventarioController::class, 'mostrarImagen'])->name('inventario.mostrarImagen');

    // API: pisos y aulas para otros módulos (tickets, etc.)
    Route::get('/api/edificios/{id}/pisos', [EdificioController::class, 'getPisos'])->name('api.edificios.pisos');
    Route::get('/api/pisos/{id}/aulas', [EdificioController::class, 'getAulas'])->name('api.pisos.aulas');

    // ---- Requisiciones ----
    Route::get('/admin/tickets/{ticket_id}/requisicion/crear', [RequisicionController::class, 'create'])->name('requisicion.create');
    Route::post('/admin/requisiciones/borrador', [RequisicionController::class, 'guardarBorrador'])->name('requisicion.borrador');
    Route::post('/admin/requisiciones', [RequisicionController::class, 'store'])->name('requisicion.store');
    Route::get('/admin/requisiciones/{id}', [RequisicionController::class, 'show'])->name('requisicion.show');
    Route::get('/admin/requisiciones/{id}/editar', [RequisicionController::class, 'edit'])->name('requisicion.edit');
    Route::put('/admin/requisiciones/{id}', [RequisicionController::class, 'update'])->name('requisicion.update');
    Route::post('/admin/requisiciones/{id}/generar-poa', [RequisicionController::class, 'generarPoaManual'])->name('requisicion.generarPoa');
    Route::delete('/admin/requisiciones/{id}', [RequisicionController::class, 'destroy'])->name('requisicion.destroy');
    Route::get('/admin/requisiciones/ticket/{id}', [RequisicionController::class, 'requisiciones'])->name('requisicion.admin');

    // AJAX: obtener datos de un material del inventario (para el modal)
    Route::get('/admin/materiales/{id}/datos', [RequisicionController::class, 'getMaterialData'])->name('material.datos');

    // Exportaciones (pendiente instalación de dependencias)
    Route::get('/admin/requisiciones/{id}/pdf', [RequisicionController::class, 'exportPdf'])->name('requisicion.pdf');
    Route::get('/admin/requisiciones/{id}/excel', [RequisicionController::class, 'exportExcel'])->name('requisicion.excel');

    // AJAX: materiales dentro de una requisición
    Route::post('/admin/requisiciones/{id}/material', [RequisicionController::class, 'addMaterial'])->name('requisicion.addMaterial');
    Route::delete('/admin/requisiciones/{id}/material/{item_id}', [RequisicionController::class, 'removeMaterial'])->name('requisicion.removeMaterial');

    // ---- POA ----
    Route::get('/admin/poa/{id}', [PoaController::class, 'show'])->name('poa.show');
    Route::get('/admin/poa/{id}/editar', [PoaController::class, 'edit'])->name('poa.edit');
    Route::put('/admin/poa/{id}', [PoaController::class, 'update'])->name('poa.update');
    Route::get('/admin/poa/{id}/pdf', [PoaController::class, 'exportPdf'])->name('poa.pdf');
});

// ===================================================================
// RUTAS SOLO SUPERADMINISTRADOR
// ===================================================================
Route::middleware(['role:Superadministrador'])->group(function () {

    // ---- Configuración Maestra del Sistema (Accesible para inicializar) ----
    Route::get('/panel-maestro', [ConfiguracionController::class, 'index'])->name('panel.maestro');
    Route::put('/panel-maestro', [ConfiguracionController::class, 'update'])->name('panel.maestro.update');

    // ---- Módulos de Superadministrador que requieren el sistema inicializado y modo local ----
    Route::middleware(['sistema.inicializado', 'modo.local'])->group(function () {
        // ---- Servicios ----
        Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios');
        Route::get('/servicios/crear', [ServicioController::class, 'create'])->name('servicios.create');
        Route::post('/servicios', [ServicioController::class, 'store'])->name('servicios.store');
        Route::get('/servicios/{id}/editar', [ServicioController::class, 'edit'])->name('servicios.edit');
        Route::put('/servicios/{id}', [ServicioController::class, 'update'])->name('servicios.update');
        Route::delete('/servicios/{id}', [ServicioController::class, 'destroy'])->name('servicios.destroy');
        Route::put('/servicios/{id}/reactivar', [ServicioController::class, 'reactivar'])->name('servicios.reactivar');

        // ---- Edificios ----
        Route::get('/edificios', [EdificioController::class, 'index'])->name('edificios');
        Route::get('/edificios/crear', [EdificioController::class, 'create'])->name('edificios.create');
        Route::post('/edificios', [EdificioController::class, 'store'])->name('edificios.store');
        Route::get('/edificios/{id}/editar', [EdificioController::class, 'edit'])->name('edificios.edit');
        Route::put('/edificios/{id}', [EdificioController::class, 'update'])->name('edificios.update');
        Route::delete('/edificios/{id}', [EdificioController::class, 'destroy'])->name('edificios.destroy');
        Route::put('/edificios/{id}/reactivar', [EdificioController::class, 'reactivar'])->name('edificios.reactivar');

        // ---- Lugares ----
        Route::get('/lugares', [LugarController::class, 'index'])->name('lugares');
        Route::get('/lugares/crear', [LugarController::class, 'create'])->name('lugares.create');
        Route::post('/lugares', [LugarController::class, 'store'])->name('lugares.store');
        Route::get('/lugares/{id}/editar', [LugarController::class, 'edit'])->name('lugares.edit');
        Route::put('/lugares/{id}', [LugarController::class, 'update'])->name('lugares.update');
        Route::delete('/lugares/{id}', [LugarController::class, 'destroy'])->name('lugares.destroy');
        Route::put('/lugares/{id}/reactivar', [LugarController::class, 'reactivar'])->name('lugares.reactivar');

        // ---- Reportes ----
        Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes');
        Route::post('/reportes/generar', [ReportesController::class, 'generar'])->name('reportes.generar');
        Route::post('/reportes/configuracion', [ReportesController::class, 'configuracion'])->name('reportes.configuracion');
        Route::get('/reportes/exportar-pdf', [ReportesController::class, 'pdf'])->name('reportes.pdf');
        Route::get('/reportes/exportar-excel', [ReportesController::class, 'excel'])->name('reportes.excel');

        // ---- Marcas ----
        Route::get('/marcas', [MarcaController::class, 'index'])->name('marcas');
        Route::get('/marcas/crear', [MarcaController::class, 'create'])->name('marcas.create');
        Route::post('/marcas', [MarcaController::class, 'store'])->name('marcas.store');
        Route::get('/marcas/{id}/editar', [MarcaController::class, 'edit'])->name('marcas.edit');
        Route::put('/marcas/{id}', [MarcaController::class, 'update'])->name('marcas.update');
        Route::delete('/marcas/{id}', [MarcaController::class, 'destroy'])->name('marcas.destroy');
        Route::put('/marcas/{id}/reactivar', [MarcaController::class, 'reactivar'])->name('marcas.reactivar');

        // ---- Unidades de Medida ----
        Route::get('/unidades', [UnidadController::class, 'index'])->name('unidades');
        Route::get('/unidades/crear', [UnidadController::class, 'create'])->name('unidades.create');
        Route::post('/unidades', [UnidadController::class, 'store'])->name('unidades.store');
        Route::get('/unidades/{id}/editar', [UnidadController::class, 'edit'])->name('unidades.edit');
        Route::put('/unidades/{id}', [UnidadController::class, 'update'])->name('unidades.update');
        Route::delete('/unidades/{id}', [UnidadController::class, 'destroy'])->name('unidades.destroy');
        Route::put('/unidades/{id}/reactivar', [UnidadController::class, 'reactivar'])->name('unidades.reactivar');

        // ---- Usuarios ----
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios');
        Route::get('/usuarios/plantilla', [UsuarioController::class, 'descargarPlantilla'])->name('usuarios.plantilla');
        Route::post('/usuarios/importar-excel', [UsuarioController::class, 'importarExcel'])->name('usuarios.importarExcel');
        Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::put('/usuarios/{id}/cambiar-rol', [UsuarioController::class, 'cambiarRol'])->name('usuarios.cambiarRol');
        Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
        Route::post('/usuarios/{id}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.toggleActivo');
    });

});

// Ruta para redireccion cuando haya un error 404
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
