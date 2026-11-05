<?php

namespace App\Http\Controllers;

use App\Mail\CodigoVerificacionPassword;
use App\Models\Configuracion;
use App\Models\User;
use App\Services\TicketMailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PasswordOtpController extends Controller
{
    /**
     * Muestra la vista para solicitar el código de recuperación de contraseña.
     */
    public function mostrarSolicitud()
    {
        return view('paginas.recuperar-password');
    }

    /**
     * Genera y envía el código OTP de 6 dígitos al correo institucional del usuario.
     */
    public function enviarCodigo(Request $request)
    {
        $request->validate([
            'identificador' => 'required|string',
        ], [
            'identificador.required' => 'Ingresa tu clave o correo institucional.',
        ]);

        $termino = trim($request->identificador);

        $usuario = User::where('clave', $termino)
            ->orWhere('email', strtolower($termino))
            ->first();

        $config = Configuracion::obtener();
        $minutos = $config->minutos_expiracion_otp;

        if ($usuario && $usuario->activo) {
            $codigo = sprintf('%06d', random_int(0, 999999));

            DB::beginTransaction();
            try {
                DB::table('password_reset_codes')
                    ->where('email', $usuario->email)
                    ->delete();

                DB::table('password_reset_codes')->insert([
                    'email'      => $usuario->email,
                    'codigo'     => Hash::make($codigo),
                    'expires_at' => Carbon::now()->addMinutes($minutos),
                    'created_at' => Carbon::now(),
                ]);

                DB::commit();

                Mail::to($usuario->email)->send(new CodigoVerificacionPassword($codigo, $usuario->nombre, $minutos));
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al generar o enviar código OTP de recuperación: ' . $e->getMessage(), [
                    'email' => $usuario->email,
                    'trace' => $e->getTraceAsString(),
                ]);

                return back()
                    ->withInput()
                    ->withErrors(['identificador' => 'No fue posible procesar la solicitud en este momento. Inténtalo de nuevo más tarde.']);
            }
        }

        return redirect()->route('password.verificar')
            ->with('email_enviado', ($usuario && $usuario->activo) ? $usuario->email : null)
            ->with('success', 'Si los datos corresponden a una cuenta activa registrada, te hemos enviado un código de verificación a tu correo institucional.');
    }

    /**
     * Muestra la vista para ingresar el código OTP y definir la nueva contraseña.
     */
    public function mostrarVerificacion()
    {
        return view('paginas.verificar-password');
    }

    /**
     * Valida el código OTP y actualiza la contraseña del usuario.
     */
    public function verificarYActualizar(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|max:100',
            'codigo'   => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required'      => 'El correo institucional es obligatorio.',
            'email.email'         => 'Ingresa un formato de correo electrónico válido.',
            'codigo.required'     => 'El código de verificación es obligatorio.',
            'codigo.size'         => 'El código de verificación debe ser de 6 dígitos.',
            'password.required'   => 'Ingresa tu nueva contraseña.',
            'password.min'        => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'  => 'La confirmación de la contraseña no coincide.',
        ]);

        $email = strtolower(trim($request->email));

        $usuario = User::where('email', $email)->first();

        if (!$usuario || !$usuario->activo) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'La cuenta no existe o se encuentra inactiva.']);
        }

        $registroOtp = DB::table('password_reset_codes')
            ->where('email', $email)
            ->where('expires_at', '>', Carbon::now())
            ->latest('created_at')
            ->first();

        if (!$registroOtp || !Hash::check(trim($request->codigo), $registroOtp->codigo)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['codigo' => 'El código de verificación es incorrecto o ya ha expirado.']);
        }

        DB::beginTransaction();
        try {
            $usuario->password = Hash::make($request->password);
            $usuario->save();

            DB::table('password_reset_codes')
                ->where('email', $email)
                ->delete();

            DB::commit();

            TicketMailService::enviarPasswordActualizada($usuario, $request->password);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al restablecer contraseña con OTP: ' . $e->getMessage(), [
                'email' => $email,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['general' => 'Ocurrió un error inesperado al actualizar la contraseña.']);
        }

        return redirect()->route('login')
            ->with('success', '¡Tu contraseña ha sido restablecida exitosamente! Inicia sesión con tus nuevas credenciales.');
    }

    /**
     * Permite a un usuario con sesión activa actualizar su propia contraseña desde el menú de usuario.
     */
    public function actualizarDesdePerfil(Request $request)
    {
        $request->validate([
            'password_actual' => 'required|string',
            'nuevo_password'  => 'required|string|min:8|confirmed',
        ], [
            'password_actual.required' => 'Ingresa tu contraseña actual.',
            'nuevo_password.required'  => 'Ingresa la nueva contraseña.',
            'nuevo_password.min'       => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'nuevo_password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $usuario = Auth::user();

        if (!Hash::check($request->password_actual, $usuario->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual ingresada no es correcta.']);
        }

        DB::beginTransaction();
        try {
            $usuario->password = Hash::make($request->nuevo_password);
            $usuario->save();

            DB::commit();

            TicketMailService::enviarPasswordActualizada($usuario, $request->nuevo_password);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar contraseña desde perfil: ' . $e->getMessage(), [
                'user_id' => $usuario->id,
                'trace'   => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['general' => 'Ocurrió un error inesperado al actualizar tu contraseña.']);
        }

        return back()
            ->with('password_success', '¡Tu contraseña ha sido actualizada exitosamente!');
    }
}
