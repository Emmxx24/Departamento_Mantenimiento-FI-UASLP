<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{
    public const MAX_ARCHIVOS = 5;
    public const MAX_KB_POR_ARCHIVO = 10240; // 10 MB
    public const EXTENSIONES_PERMITIDAS = 'jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt';

    /**
     * Reglas de validación estandarizadas para subida de archivos adjuntos.
     */
    public static function reglas(string $campo = 'archivos'): array
    {
        return [
            $campo       => 'nullable|array|max:' . self::MAX_ARCHIVOS,
            "{$campo}.*" => 'file|mimes:' . self::EXTENSIONES_PERMITIDAS . '|max:' . self::MAX_KB_POR_ARCHIVO,
        ];
    }

    /**
     * Mensajes descriptivos para validaciones de archivos.
     */
    public static function mensajes(string $campo = 'archivos'): array
    {
        return [
            "{$campo}.max"     => 'Solo puedes adjuntar hasta ' . self::MAX_ARCHIVOS . ' archivos en total.',
            "{$campo}.*.file"  => 'Uno de los archivos adjuntos no es válido.',
            "{$campo}.*.mimes" => 'Tipo de archivo no permitido. Solo se aceptan: JPG, PNG, WEBP, PDF, Word, Excel o TXT.',
            "{$campo}.*.max"   => 'Cada archivo no puede superar los 10 MB de tamaño.',
        ];
    }

    /**
     * Sirve un archivo adjunto asegurando control de acceso (anti-IDOR).
     */
    public function descargar($id)
    {
        $archivo = Archivo::findOrFail($id);
        $ticket = $archivo->ticketAsociado();

        if (! $ticket) {
            abort(404, 'Ticket asociado no encontrado.');
        }

        // Validación de permisos unificada con el modelo Ticket (devuelve 404)
        abort_unless($ticket->puedeVer(Auth::user()), 404);

        $diskName = Storage::disk('public')->exists($archivo->ruta) ? 'public' : (Storage::exists($archivo->ruta) ? config('filesystems.default') : null);

        if (! $diskName) {
            abort(404, 'El archivo solicitado no se encuentra en el almacenamiento.');
        }

        return Storage::disk($diskName)->response($archivo->ruta, $archivo->nombre_archivo);
    }

    /**
     * Redirige exclusivamente la descarga de un archivo. A diferencia de la funcion descargar()
     * que lo abre en el navegador de ser posible.
     */
    public function forzarDescarga($id)
    {
        $archivo = Archivo::findOrFail($id);
        $ticket = $archivo->ticketAsociado();

        if (! $ticket) {
            abort(404, 'Ticket asociado no encontrado.');
        }

        // Validación de permisos unificada con el modelo Ticket (devuelve 404)
        abort_unless($ticket->puedeVer(Auth::user()), 404);

        $diskName = Storage::disk('public')->exists($archivo->ruta) ? 'public' : (Storage::exists($archivo->ruta) ? config('filesystems.default') : null);

        if (! $diskName) {
            abort(404, 'El archivo solicitado no se encuentra en el almacenamiento.');
        }

        return Storage::disk($diskName)->download($archivo->ruta, $archivo->nombre_archivo);
    }

    /**
     * Almacena físicamente una lista de archivos y registra sus metadatos en BD.
     * Garantiza el límite máximo de 5 archivos en backend.
     *
     * @param  array<UploadedFile>  $archivos
     * @return array<Archivo>
     */
    public static function guardarArchivos(array $archivos, ?int $idTicket = null, ?int $idMensaje = null, string $carpeta = 'tickets'): array
    {
        // Seguridad estricta: asegurar que nunca se guarden más de 5 archivos
        $archivosValidos = array_slice($archivos, 0, self::MAX_ARCHIVOS);
        $guardados = [];

        foreach ($archivosValidos as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $path = $file->store($carpeta);

                $guardados[] = Archivo::create([
                    'nombre_archivo' => $file->getClientOriginalName(),
                    'ruta'           => $path,
                    'extension'      => $file->getClientOriginalExtension(),
                    'id_ticket'      => $idTicket,
                    'id_mensaje'     => $idMensaje,
                ]);
            }
        }

        return $guardados;
    }
}
