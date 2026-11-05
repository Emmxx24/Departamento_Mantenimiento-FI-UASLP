<?php

namespace App\Services;

class CorreoService
{
    public static function enviar($destino, $asunto, $contenido)
    {
        $endpoint = 'https://servicios.ing.uaslp.mx/gtm/api_correos/sendMail.php';

        $data = [
            'key_api' => 'S3RT?8B4674#$33FG($35H45',
            'tipo_mensaje' => 'mantenimiento',
            'destino' => $destino,
            'asunto' => $asunto,
            'contenido' => $contenido,
        ];

        $payload = json_encode($data);

        $ch = curl_init($endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: '.strlen($payload),
        ]);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        \Log::info('Intentando enviar correo', [
            'destino' => $destino,
            'asunto' => $asunto,
            'contenido' => $contenido,
            'response' => $response,
            'error' => curl_error($ch),
        ]);

        curl_close($ch);

        return $response;

    }
}
