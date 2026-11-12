<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ConfiguracionController extends Controller
{
    /**
     * Muestra el panel de configuración del sistema.
     */
    public function index()
    {
        $config = Configuracion::obtener();
        $totalSuperadmins = User::where('rol', 'Superadministrador')->where('activo', 1)->count();
        $totalAdmins = User::where('rol', 'Administrador')->where('activo', 1)->count();

        return view('paginas.panel-maestro', compact('config', 'totalSuperadmins', 'totalAdmins'));
    }

    /**
     * Actualiza la configuración del sistema previa confirmación de contraseña.
     */
    public function update(Request $request)
    {
        $this->validar($request);

        DB::beginTransaction();

        try {
            $config = Configuracion::firstOrFail();

            $config->login_institucional    = (bool) $request->login_institucional;
            $config->minutos_expiracion_otp = (int) $request->minutos_expiracion_otp;
            $config->max_superadmins        = (int) $request->max_superadmins;
            $config->max_admins             = (int) $request->max_admins;
            $config->correo_contacto        = trim($request->correo_contacto);
            $config->telefono_contacto      = trim($request->telefono_contacto);
            $config->sistema_inicializado   = true;
            $config->save();

            DB::commit();

            return redirect()->route('panel.maestro')
                ->with('success', 'Configuración del sistema guardada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar configuración del sistema: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'Ocurrió un error inesperado al guardar la configuración. Inténtalo de nuevo.']);
        }
    }

    /**
     * Valida los datos del formulario, incluyendo confirmación de contraseña y coherencia de límites.
     */
    private function validar(Request $request): void
    {
        $request->validate([
            'password_confirmacion'  => 'required|string',
            'login_institucional'    => 'required|boolean',
            'minutos_expiracion_otp' => 'required|integer|min:1|max:120',
            'max_superadmins'        => 'required|integer|min:1|max:100',
            'max_admins'             => 'required|integer|min:1|max:100',
            'correo_contacto'        => 'required|email|max:100',
            'telefono_contacto'      => 'required|string|max:50',
        ], [
            'password_confirmacion.required'  => 'Ingresa tu contraseña de Superadministrador para autorizar los cambios.',
            'login_institucional.required'    => 'Selecciona el modo de autenticación.',
            'minutos_expiracion_otp.required' => 'El tiempo de vigencia del código OTP es obligatorio.',
            'minutos_expiracion_otp.integer'  => 'El tiempo de vigencia del código OTP debe ser un número entero.',
            'minutos_expiracion_otp.min'      => 'El tiempo de vigencia del código OTP debe ser de al menos 1 minuto.',
            'minutos_expiracion_otp.max'      => 'El tiempo de vigencia del código OTP no puede exceder los 120 minutos.',
            'max_superadmins.required'        => 'El límite de Superadministradores es obligatorio.',
            'max_superadmins.min'             => 'El límite de Superadministradores debe ser al menos 1.',
            'max_admins.required'             => 'El límite de Administradores es obligatorio.',
            'max_admins.min'                  => 'El límite de Administradores debe ser al menos 1.',
            'correo_contacto.required'        => 'El correo electrónico de contacto es obligatorio.',
            'correo_contacto.email'           => 'Ingresa un correo electrónico de contacto válido.',
            'telefono_contacto.required'      => 'El teléfono de contacto es obligatorio.',
        ]);

        // Verificación de contraseña de confirmación
        if (!Hash::check($request->password_confirmacion, Auth::user()->password)) {
            throw ValidationException::withMessages([
                'password_confirmacion' => 'Contraseña incorrecta. Ningún cambio fue aplicado por seguridad.',
            ]);
        }

        // Validación de coherencia de límites respecto a usuarios activos actuales
        $totalSuperadmins = User::where('rol', 'Superadministrador')->where('activo', 1)->count();
        if ($request->max_superadmins < $totalSuperadmins) {
            throw ValidationException::withMessages([
                'max_superadmins' => "No es posible reducir el límite a {$request->max_superadmins}. Actualmente hay {$totalSuperadmins} Superadministradores activos.",
            ]);
        }

        $totalAdmins = User::where('rol', 'Administrador')->where('activo', 1)->count();
        if ($request->max_admins < $totalAdmins) {
            throw ValidationException::withMessages([
                'max_admins' => "No es posible reducir el límite a {$request->max_admins}. Actualmente hay {$totalAdmins} Administradores activos.",
            ]);
        }
    }
}
