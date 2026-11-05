<x-mail::message :message="$message">
# Nuevo mensaje en tu ticket

Se envió un nuevo mensaje en el ticket **#{{ $ticket->id_ticket }} — {{ $ticket->asunto }}**.

<x-mail::panel>
{{ $mensaje->texto ? $mensaje->texto : '📎 [Archivo(s) adjunto(s)]' }}
</x-mail::panel>

<x-mail::button :url="route('tickets.show', $ticket->id_ticket)" color="primary">
Ver conversación
</x-mail::button>

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>