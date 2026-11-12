<x-mail::message :message="$message">
# Nuevo ticket creado

Se ha creado un nuevo ticket en el Sistema de Mantenimiento.

<x-mail::panel>
**ID:** {{ $ticket->id_ticket }}<br>
**Asunto:** {{ $ticket->asunto }}<br>
**Piso:** {{ $ticket->piso }}
</x-mail::panel>

**Descripción:**
{{ $ticket->descripcion }}

<x-mail::button :url="route('tickets.show', $ticket->id_ticket)" color="primary">
Ver ticket
</x-mail::button>

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>