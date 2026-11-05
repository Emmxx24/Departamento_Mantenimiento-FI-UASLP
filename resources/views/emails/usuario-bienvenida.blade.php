<x-mail::message :message="$message">
# ¡Bienvenido al Sistema de Mantenimiento!

Hola **{{ $usuario->nombre }} {{ $usuario->ap_pat }}**,

Tu cuenta ha sido creada exitosamente en el **Sistema del Departamento de Mantenimiento de la Facultad de Ingeniería (UASLP)** con el rol de **{{ $usuario->rol }}**.

<x-mail::panel>
**Clave institucional:** {{ $usuario->clave }}<br>
**Correo registrado:** {{ $usuario->email }}<br>
**Rol asignado:** {{ $usuario->rol }}
@if (!$esInstitucional && $passwordInicial)
<br><br>
**Tu contraseña de acceso temporal es:**<br>
<div style="font-size: 18px; font-weight: bold; color: #004A98; padding: 6px 0;">
{{ $passwordInicial }}
</div>
*Por tu seguridad, te recomendamos cambiarla al ingresar por primera vez desde el menú de tu perfil.*
@elseif ($esInstitucional)
<br><br>
*Puedes ingresar utilizando tus credenciales institucionales de la UASLP.*
@endif
</x-mail::panel>

<x-mail::button :url="route('login')" color="primary">
Iniciar Sesión
</x-mail::button>

Atentamente,<br>
**Departamento de Mantenimiento**<br>
Facultad de Ingeniería — UASLP
</x-mail::message>

