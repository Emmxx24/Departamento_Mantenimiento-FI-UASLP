<x-mail::message :message="$message">
# Restablecimiento de Contraseña

Hola **{{ $nombre }}**,

Has solicitado restablecer la contraseña de tu cuenta en el **Sistema del Departamento de Mantenimiento de la Facultad de Ingeniería (UASLP)**.

Utiliza el siguiente código de verificación de 6 dígitos para continuar:

<x-mail::panel>
<div style="text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #004A98; padding: 10px 0;">
{{ $codigo }}
</div>
</x-mail::panel>

> **Importante:** Este código es válido durante los próximos **{{ $minutos }} minutos**. Por tu seguridad, no compartas este código con ninguna otra persona.

Si tú no realizaste esta solicitud, puedes ignorar este correo de forma segura; tu contraseña actual permanecerá sin cambios y tu cuenta sigue protegida.

Atentamente,<br>
**Departamento de Mantenimiento**<br>
Facultad de Ingeniería — UASLP
</x-mail::message>
