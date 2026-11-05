<x-mail::message :message="$message">
# Contraseña Actualizada

Hola **{{ $usuario->nombre }}**,

Te confirmamos que la contraseña de tu cuenta en el **Sistema del Departamento de Mantenimiento** ha sido actualizada exitosamente.

<x-mail::panel>
**Tu nueva contraseña es:**<br>
<div style="font-size: 18px; font-weight: bold; color: #004A98; padding: 6px 0;">
{{ $nuevaPassword }}
</div>
</x-mail::panel>

> **Importante:** Si tú no realizaste este cambio, por favor ponte en contacto inmediatamente con el administrador del sistema para proteger tu cuenta.

<x-mail::button :url="route('login')" color="primary">
Ir al Sistema
</x-mail::button>

Atentamente,<br>
**Departamento de Mantenimiento**<br>
Facultad de Ingeniería — UASLP
</x-mail::message>

