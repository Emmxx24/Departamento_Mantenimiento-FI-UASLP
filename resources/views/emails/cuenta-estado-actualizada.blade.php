<x-mail::message :message="$message">
# Estado de Cuenta Actualizado

Hola **{{ $usuario->nombre }}**,

Te notificamos que el estado de tu cuenta en el **Sistema del Departamento de Mantenimiento de la Facultad de Ingeniería** ha sido modificado.

<x-mail::panel>
**Clave institucional:** {{ $usuario->clave }}<br>
**Estado de la cuenta:** **{{ $activo ? 'Activa / Habilitada' : 'Desactivada / Suspendida' }}**
</x-mail::panel>

@if ($activo)
Tu cuenta ha sido reactivada. Ya puedes acceder nuevamente al sistema con tus credenciales habituales.

<x-mail::button :url="route('login')" color="primary">
Acceder al Sistema
</x-mail::button>
@else
Tu acceso al sistema ha sido revocado temporalmente. Si consideras que se trata de un error, comunícate con el administrador del departamento de mantenimiento.
@endif

Atentamente,<br>
**Departamento de Mantenimiento**<br>
Facultad de Ingeniería — UASLP
</x-mail::message>

