<x-mail::message :message="$message">
# Actualización en tu ticket

Se ha realizado una actualización en el ticket **#{{ $ticket->id_ticket }}**.

<x-mail::panel>
**Asunto:** {{ $ticket->asunto }}<br>
**Campo modificado:** {{ $campo }}<br>
**Nuevo valor:** {{ $valor }}
</x-mail::panel>

<x-mail::button :url="route('tickets.show', $ticket->id_ticket)" color="primary">
Ver detalle del ticket
</x-mail::button>

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>
