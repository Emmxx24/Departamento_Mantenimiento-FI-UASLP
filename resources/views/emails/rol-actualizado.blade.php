<x-mail::message :message="$message">
# Cambio de Rol en tu Cuenta

Hola **{{ $usuario->nombre }}**,

Te informamos que se ha modificado el rol de acceso de tu cuenta en el **Sistema del Departamento de Mantenimiento**.

<x-mail::panel>
**Clave institucional:** {{ $usuario->clave }}<br>
**Nuevo rol asignado:** **{{ $nuevoRol }}**
</x-mail::panel>

Tus nuevos permisos ya se encuentran activos a partir de este momento.

<x-mail::button :url="route('login')" color="primary">
Ingresar al Sistema
</x-mail::button>

Atentamente,<br>
**Departamento de Mantenimiento**<br>
Facultad de Ingeniería — UASLP
</x-mail::message>

