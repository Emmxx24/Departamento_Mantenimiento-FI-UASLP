<x-mail::message :message="$message">
# Nuevo ticket asignado a tu cargo

Se te ha asignado la atención del ticket **#{{ $ticket->id_ticket }}**.

<x-mail::panel>
**ID:** #{{ $ticket->id_ticket }}<br>
**Asunto:** {{ $ticket->asunto }}<br>
**Piso:** {{ $ticket->piso }}<br>
**Prioridad:** {{ $ticket->prioridad ?? 'No asignada' }}<br>
**Estado:** {{ $ticket->estado }}
</x-mail::panel>

**Descripción:**
{{ $ticket->descripcion }}

<x-mail::button :url="route('tickets.show', $ticket->id_ticket)" color="primary">
Atender ticket
</x-mail::button>

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>

