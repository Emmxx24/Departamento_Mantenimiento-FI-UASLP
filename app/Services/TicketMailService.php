<?php

namespace App\Services;

use App\Mail\CuentaEstadoActualizada;
use App\Mail\NuevoMensajeChat;
use App\Mail\PasswordActualizada;
use App\Mail\RolActualizado;
use App\Mail\TicketActualizado;
use App\Mail\TicketAsignado;
use App\Mail\TicketCancelado;
use App\Mail\TicketCreado;
use App\Mail\UsuarioBienvenida;
use App\Models\Aula;
use App\Models\Edificio;
use App\Models\Mensaje;
use App\Models\Servicio;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function Illuminate\Support\defer;

/**
 * Los correos se envían DESPUÉS de mandar la respuesta al navegador (defer),
 * así el usuario no espera al servidor SMTP y no se necesita un queue worker.
 * Nota: en producción con PHP-FPM la respuesta se libera de inmediato; en XAMPP (mod_php)
 * el navegador puede seguir esperando hasta que termine el envío.
 */
class TicketMailService
{
    /**
     * Notifica a los administradores y al solicitante cuando se crea un nuevo ticket.
     * Filtra únicamente destinatarios activos.
     */
    public static function enviarTicketCreado(Ticket $ticket): void
    {
        self::enviarDespues('Ticket Creado', ['ticket_id' => $ticket->id_ticket], function () use ($ticket) {
            $solicitante = $ticket->solicitante ?? User::find($ticket->id_solicitante);

            // Notificar a administradores y superadministradores activos
            $admins = User::whereIn('rol', ['Administrador', 'Superadministrador'])
                ->where('activo', 1)
                ->get();

            foreach ($admins as $admin) {
                if ($admin->email) {
                    Mail::to($admin->email)->send(new TicketCreado($ticket));
                }
            }

            // Notificar al solicitante si su cuenta está activa
            if ($solicitante && $solicitante->email && $solicitante->activo) {
                Mail::to($solicitante->email)->send(new TicketCreado($ticket));
            }
        });
    }

    /**
     * Notifica a la contraparte (solicitante o admin asignado/superadmins) cuando hay un nuevo mensaje en el chat.
     * Filtra únicamente destinatarios activos.
     */
    public static function enviarNuevoMensaje(Ticket $ticket, Mensaje $mensaje): void
    {
        $contextoLog = ['ticket_id' => $ticket->id_ticket, 'mensaje_id' => $mensaje->id_mensaje];

        self::enviarDespues('Nuevo Mensaje', $contextoLog, function () use ($ticket, $mensaje) {
            $remitenteId = $mensaje->remitente ?? Auth::id();
            $remitente = User::find($remitenteId);
            $solicitante = $ticket->solicitante ?? User::find($ticket->id_solicitante);
            $adminAsignado = $ticket->id_admin_atiende ? ($ticket->adminAtiende ?? User::find($ticket->id_admin_atiende)) : null;

            $destinatarios = collect();

            // Si quien escribe es el Solicitante
            if ($remitenteId == $ticket->id_solicitante) {
                if ($adminAsignado && $adminAsignado->email && $adminAsignado->activo) {
                    $destinatarios->push($adminAsignado);
                } else {
                    // Si aún no hay admin asignado, notificar a los Superadministradores activos
                    $superadmins = User::where('rol', 'Superadministrador')
                        ->where('activo', 1)
                        ->get();
                    foreach ($superadmins as $sa) {
                        $destinatarios->push($sa);
                    }
                }
            } else {
                // Si quien escribe es un Administrador o Superadministrador:
                // Siempre notificar al Solicitante si está activo
                if ($solicitante && $solicitante->email && $solicitante->activo) {
                    $destinatarios->push($solicitante);
                }

                // Si quien escribe es Superadministrador y el ticket está asignado a otro Administrador activo
                if ($remitente && $remitente->rol === 'Superadministrador' && $adminAsignado && $adminAsignado->activo) {
                    if ($adminAsignado->id != $remitenteId && $adminAsignado->email) {
                        $destinatarios->push($adminAsignado);
                    }
                }
            }

            // Excluir al propio remitente, usuarios inactivos, emails vacíos y remover duplicados
            $destinatariosUnicos = $destinatarios
                ->filter(fn ($u) => !empty($u->email) && $u->activo && $u->id != $remitenteId)
                ->unique('id');

            foreach ($destinatariosUnicos as $destinatario) {
                Mail::to($destinatario->email)->send(new NuevoMensajeChat($ticket, $mensaje));
            }
        });
    }

    /**
     * Notifica al solicitante cuando se actualiza un campo relevante (estado, prioridad, etc.)
     * Si se asigna un administrador, también notifica al administrador asignado.
     */
    public static function enviarTicketActualizado(Ticket $ticket, string $campo, mixed $valor): void
    {
        $contextoLog = ['ticket_id' => $ticket->id_ticket, 'campo' => $campo, 'valor' => $valor];

        self::enviarDespues('Ticket Actualizado', $contextoLog, function () use ($ticket, $campo, $valor) {
            $solicitante = $ticket->solicitante ?? User::find($ticket->id_solicitante);

            if ($solicitante && $solicitante->email && $solicitante->activo) {
                [$campoAmigable, $valorAmigable] = self::traducirCampoYValor($campo, $valor);
                Mail::to($solicitante->email)->send(new TicketActualizado($ticket, $campoAmigable, $valorAmigable));
            }

            // Notificación al administrador recién asignado
            if ($campo === 'id_admin_atiende' && $valor) {
                $adminAsignado = User::find($valor);
                if ($adminAsignado && $adminAsignado->email && $adminAsignado->activo) {
                    Mail::to($adminAsignado->email)->send(new TicketAsignado($ticket));
                }
            }
        });
    }

    /**
     * Notifica la cancelación de un ticket al solicitante y al personal de atención (admin asignado o superadmins).
     */
    public static function enviarTicketCancelado(Ticket $ticket): void
    {
        self::enviarDespues('Ticket Cancelado', ['ticket_id' => $ticket->id_ticket], function () use ($ticket) {
            $solicitante = $ticket->solicitante ?? User::find($ticket->id_solicitante);
            $adminAsignado = $ticket->id_admin_atiende ? ($ticket->adminAtiende ?? User::find($ticket->id_admin_atiende)) : null;

            $destinatarios = collect();

            // Administrador asignado activo o Superadministradores activos
            if ($adminAsignado && $adminAsignado->email && $adminAsignado->activo) {
                $destinatarios->push($adminAsignado);
            } else {
                $superadmins = User::where('rol', 'Superadministrador')
                    ->where('activo', 1)
                    ->get();
                foreach ($superadmins as $sa) {
                    if ($sa->email) {
                        $destinatarios->send($sa);
                    }
                }
            }

            // Solicitante si está activo
            if ($solicitante && $solicitante->email && $solicitante->activo) {
                $destinatarios->push($solicitante);
            }

            $destinatariosUnicos = $destinatarios
                ->filter(fn ($u) => !empty($u->email) && $u->activo)
                ->unique('id');

            foreach ($destinatariosUnicos as $destinatario) {
                Mail::to($destinatario->email)->send(new TicketCancelado($ticket));
            }
        });
    }

    /**
     * Envía correo de bienvenida al usuario recién creado (manual o por Excel).
     */
    public static function enviarBienvenidaUsuario(User $usuario, ?string $passwordInicial = null, bool $esInstitucional = false): void
    {
        if (empty($usuario->email) || !$usuario->activo) {
            return;
        }

        self::enviarDespues('Bienvenida Usuario', ['usuario_id' => $usuario->id, 'email' => $usuario->email], function () use ($usuario, $passwordInicial, $esInstitucional) {
            Mail::to($usuario->email)->send(new UsuarioBienvenida($usuario, $passwordInicial, $esInstitucional));
        });
    }

    /**
     * Notifica al usuario que su contraseña ha sido actualizada e incluye su nueva contraseña.
     */
    public static function enviarPasswordActualizada(User $usuario, string $nuevaPassword): void
    {
        if (empty($usuario->email) || !$usuario->activo) {
            return;
        }

        self::enviarDespues('Password Actualizada', ['usuario_id' => $usuario->id], function () use ($usuario, $nuevaPassword) {
            Mail::to($usuario->email)->send(new PasswordActualizada($usuario, $nuevaPassword));
        });
    }

    /**
     * Notifica al usuario sobre la asignación de un nuevo rol en el sistema.
     */
    public static function enviarRolActualizado(User $usuario, string $nuevoRol): void
    {
        if (empty($usuario->email) || !$usuario->activo) {
            return;
        }

        self::enviarDespues('Rol Actualizado', ['usuario_id' => $usuario->id, 'nuevo_rol' => $nuevoRol], function () use ($usuario, $nuevoRol) {
            Mail::to($usuario->email)->send(new RolActualizado($usuario, $nuevoRol));
        });
    }

    /**
     * Notifica al usuario sobre la activación o suspensión de su cuenta.
     */
    public static function enviarCuentaEstado(User $usuario, bool $activo): void
    {
        if (empty($usuario->email)) {
            return;
        }

        self::enviarDespues('Estado Cuenta', ['usuario_id' => $usuario->id, 'activo' => $activo], function () use ($usuario, $activo) {
            Mail::to($usuario->email)->send(new CuentaEstadoActualizada($usuario, $activo));
        });
    }

    /**
     * Ejecuta el envío después de responder al navegador y registra cualquier error en el log
     * (un fallo de correo nunca debe romper la petición del usuario).
     */
    private static function enviarDespues(string $tipo, array $contextoLog, \Closure $envio): void
    {
        defer(function () use ($tipo, $contextoLog, $envio) {
            try {
                $envio();
            } catch (\Throwable $e) {
                Log::error("Error al enviar correo de {$tipo}: ".$e->getMessage(), $contextoLog);
            }
        });
    }

    /**
     * Traduce los nombres técnicos de campos y los IDs de catálogos a nombres legibles para el usuario.
     */
    public static function traducirCampoYValor(string $campo, mixed $valor): array
    {
        $nombresCampos = [
            'asunto'           => 'Asunto',
            'descripcion'      => 'Descripción',
            'piso'             => 'Piso',
            'id_edificio'      => 'Edificio',
            'id_aula'          => 'Aula / Lugar',
            'id_servicio'      => 'Servicio',
            'prioridad'        => 'Prioridad',
            'estado'           => 'Estado',
            'id_admin_atiende' => 'Administrador Asignado',
        ];

        $campoAmigable = $nombresCampos[$campo] ?? ucfirst($campo);
        $valorAmigable = $valor;

        switch ($campo) {
            case 'id_edificio':
                $edificio = Edificio::find($valor);
                $valorAmigable = $edificio ? $edificio->nombre : "Edificio #{$valor}";
                break;

            case 'id_aula':
                $aula = Aula::find($valor);
                $valorAmigable = $aula ? $aula->nombre : "Aula #{$valor}";
                break;

            case 'id_servicio':
                $servicio = Servicio::find($valor);
                $valorAmigable = $servicio ? $servicio->nombre : "Servicio #{$valor}";
                break;

            case 'id_admin_atiende':
                $admin = User::find($valor);
                $valorAmigable = $admin ? trim("{$admin->nombre} {$admin->ap_pat} {$admin->ap_mat}") : "Administrador #{$valor}";
                break;

            case 'piso':
                $valorAmigable = ((string) $valor === '0') ? 'Planta Baja' : "Piso {$valor}";
                break;
        }

        return [$campoAmigable, $valorAmigable];
    }
}
