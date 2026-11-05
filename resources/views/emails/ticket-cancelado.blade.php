<x-mail::message :message="$message">
# Ticket Cancelado

El ticket **#{{ $ticket->id_ticket }}** ha sido cancelado por el solicitante.

<x-mail::panel>
**ID:** #{{ $ticket->id_ticket }}<br>
**Asunto:** {{ $ticket->asunto }}<br>
**Solicitante:** {{ $nombreSolicitante }}<br>
**Estado actual:** Cerrado<br>
**Fecha de cancelación:** {{ now()->format('d/m/Y H:i') }}
</x-mail::panel>

<x-mail::button :url="route('tickets.show', $ticket->id_ticket)" color="primary">
Ver detalle del ticket
</x-mail::button>

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>

