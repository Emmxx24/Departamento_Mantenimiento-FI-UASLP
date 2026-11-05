<?php

namespace App\Imports;

use App\Http\Controllers\UsuarioController;
use App\Models\Configuracion;
use App\Models\User;
use App\Services\TicketMailService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsuariosSheetImport implements ToCollection, WithHeadingRow
{
    /**
     * @var array Lista de registros insertados exitosamente
     */
    protected array $insertados = [];

    /**
     * @var array Lista de registros omitidos o rechazados con su motivo detallado
     */
    protected array $omitidos = [];

    /**
     * @var int Total de filas con datos leídas
     */
    protected int $totalLeidos = 0;

    /**
     * Procesa la colección de filas de la primera hoja del Excel.
     */
    public function collection(Collection $rows)
    {
        $config = Configuracion::obtener();
        $esInstitucional = $config->isInstitucional();
        $maxSuperadmins = (int) $config->max_superadmins;
        $maxAdmins = (int) $config->max_admins;

        // Conteo inicial en tiempo real de cuentas activas
        $totalSuperadmins = User::where('rol', 'Superadministrador')->where('activo', 1)->count();
        $totalAdmins = User::where('rol', 'Administrador')->where('activo', 1)->count();

        // Control de duplicados intra-archivo
        $clavesVistas = [];
        $emailsVistos = [];

        foreach ($rows as $index => $row) {
            // En Excel: la fila 1 es encabezado, por lo que el índice 0 corresponde a la fila 2
            $numFila = $index + 2;

            // Normalizar encabezados a minúsculas y sin espacios
            $rowArray = $row instanceof Collection ? $row->toArray() : (array) $row;
            $normalizado = [];
            foreach ($rowArray as $k => $v) {
                $normalizado[strtolower(trim((string) $k))] = $v;
            }

            // Extracción de campos admitiendo variantes frecuentes
            $clave = trim((string) ($normalizado['clave'] ?? $normalizado['cve'] ?? ''));
            $nombre = trim((string) ($normalizado['nombre'] ?? $normalizado['nombres'] ?? ''));
            $apPat = trim((string) ($normalizado['ap_pat'] ?? $normalizado['apellido_paterno'] ?? $normalizado['primer_apellido'] ?? ''));
            
            $apMat = null;
            if (isset($normalizado['ap_mat']) || isset($normalizado['apellido_materno']) || isset($normalizado['segundo_apellido'])) {
                $rawMat = trim((string) ($normalizado['ap_mat'] ?? $normalizado['apellido_materno'] ?? $normalizado['segundo_apellido'] ?? ''));
                $apMat = $rawMat !== '' ? $rawMat : null;
            }

            $email = strtolower(trim((string) ($normalizado['email'] ?? $normalizado['correo'] ?? $normalizado['correo_institucional'] ?? '')));
            $rolRaw = trim((string) ($normalizado['rol'] ?? $normalizado['role'] ?? ''));

            // Si toda la fila está completamente vacía, ignorar silenciosamente sin alertar
            if ($clave === '' && $nombre === '' && $apPat === '' && $email === '' && $rolRaw === '') {
                continue;
            }

            $this->totalLeidos++;
            $identificadorNombre = ($nombre !== '' || $apPat !== '') ? trim("{$nombre} {$apPat}") : 'No especificado';
            $identificadorClave = $clave !== '' ? $clave : 'Sin clave';

            // 1. Validación de campos obligatorios
            if ($clave === '' || $nombre === '' || $apPat === '' || $email === '' || $rolRaw === '') {
                $faltantes = [];
                if ($clave === '') $faltantes[] = 'clave';
                if ($nombre === '') $faltantes[] = 'nombre';
                if ($apPat === '') $faltantes[] = 'ap_pat';
                if ($email === '') $faltantes[] = 'email';
                if ($rolRaw === '') $faltantes[] = 'rol';

                $this->omitidos[] = [
                    'fila'    => $numFila,
                    'clave'   => $identificadorClave,
                    'nombre'  => $identificadorNombre,
                    'motivo'  => 'Faltan campos obligatorios: ' . implode(', ', $faltantes) . '.',
                ];
                continue;
            }

            // 2. Longitud máxima estándar
            if (strlen($nombre) > 50 || strlen($apPat) > 50 || ($apMat && strlen($apMat) > 50)) {
                $this->omitidos[] = [
                    'fila'    => $numFila,
                    'clave'   => $clave,
                    'nombre'  => $identificadorNombre,
                    'motivo'  => 'El nombre o apellidos superan el límite máximo de 50 caracteres.',
                ];
                continue;
            }

            // 3. Validación de clave: Numérica UASLP (solo dígitos, hasta 20 caracteres)
            if (!ctype_digit($clave) || strlen($clave) > 20) {
                $this->omitidos[] = [
                    'fila'    => $numFila,
                    'clave'   => $clave,
                    'nombre'  => $identificadorNombre,
                    'motivo'  => 'La clave institucional debe ser numérica (solo dígitos, máximo 20 caracteres).',
                ];
                continue;
            }

            // 4. Protección del Superadministrador raíz original
            if ($config->isSuperadminRaiz($clave)) {
                $this->omitidos[] = [
                    'fila'    => $numFila,
                    'clave'   => $clave,
                    'nombre'  => $identificadorNombre,
                    'motivo'  => 'La clave corresponde al Superadministrador principal del sistema (cuenta protegida).',
                ];
                continue;
            }

            // 5. Normalización y validación del Rol
            $rolesValidos = [
                'superadministrador' => 'Superadministrador',
                'administrador'      => 'Administrador',
                'solicitante'        => 'Solicitante',
            ];
            $rolKey = strtolower($rolRaw);
            if (!isset($rolesValidos[$rolKey])) {
                $this->omitidos[] = [
                    'fila'    => $numFila,
                    'clave'   => $clave,
                    'nombre'  => $identificadorNombre,
                    'motivo'  => "El rol '{$rolRaw}' no es válido. Debe ser: Superadministrador, Administrador o Solicitante.",
                ];
                continue;
            }
            $rolCanonico = $rolesValidos[$rolKey];

            // 6. Validación de formato de correo electrónico
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
                $this->omitidos[] = [
                    'fila'    => $numFila,
                    'clave'   => $clave,
                    'nombre'  => $identificadorNombre,
                    'motivo'  => "El correo electrónico '{$email}' no tiene un formato válido.",
                ];
                continue;
            }

            // 7. Validación estricta del dominio institucional @uaslp.mx
            if (!UsuarioController::esCorreoInstitucionalValido($email)) {
                $this->omitidos[] = [
                    'fila'    => $numFila,
                    'clave'   => $clave,
                    'nombre'  => $identificadorNombre,
                    'motivo'  => "El correo '{$email}' debe pertenecer estrictamente al dominio institucional (@uaslp.mx).",
                ];
                continue;
            }

            // 8. Detección de duplicados dentro del mismo archivo Excel
            if (isset($clavesVistas[$clave])) {
                $this->omitidos[] = [
                    'fila'    => $numFila,
                    'clave'   => $clave,
                    'nombre'  => $identificadorNombre,
                    'motivo'  => "Clave duplicada dentro del archivo Excel (registrada previamente en la fila {$clavesVistas[$clave]}).",
                ];
                continue;
            }

            if (isset($emailsVistos[$email])) {
                $this->omitidos[] = [
                    'fila'    => $numFila,
                    'clave'   => $clave,
                    'nombre'  => $identificadorNombre,
                    'motivo'  => "Correo duplicado dentro del archivo Excel (registrado previamente en la fila {$emailsVistos[$email]}).",
                ];
                continue;
            }

            // 9. Unicidad contra la base de datos
            $existenteClave = User::where('clave', $clave)->first();
            if ($existenteClave) {
                $estado = $existenteClave->activo ? 'activo' : 'desactivado';
                $this->omitidos[] = [
                    'fila'    => $numFila,
                    'clave'   => $clave,
                    'nombre'  => $identificadorNombre,
                    'motivo'  => "La clave ya existe en el sistema ({$estado}, asociado a {$existenteClave->nombre_completo}).",
                ];
                continue;
            }

            $existenteEmail = User::where('email', $email)->first();
            if ($existenteEmail) {
                $estado = $existenteEmail->activo ? 'activo' : 'desactivado';
                $this->omitidos[] = [
                    'fila'    => $numFila,
                    'clave'   => $clave,
                    'nombre'  => $identificadorNombre,
                    'motivo'  => "El correo ya existe en el sistema ({$estado}, asociado a {$existenteEmail->nombre_completo}).",
                ];
                continue;
            }

            // 10. Verificación de cupos máximos en tiempo real
            if ($rolCanonico === 'Superadministrador') {
                if ($totalSuperadmins >= $maxSuperadmins) {
                    $this->omitidos[] = [
                        'fila'    => $numFila,
                        'clave'   => $clave,
                        'nombre'  => $identificadorNombre,
                        'motivo'  => "Cupo agotado para Superadministradores ({$totalSuperadmins} / {$maxSuperadmins} permitidos).",
                    ];
                    continue;
                }
            } elseif ($rolCanonico === 'Administrador') {
                if ($totalAdmins >= $maxAdmins) {
                    $this->omitidos[] = [
                        'fila'    => $numFila,
                        'clave'   => $clave,
                        'nombre'  => $identificadorNombre,
                        'motivo'  => "Cupo agotado para Administradores ({$totalAdmins} / {$maxAdmins} permitidos).",
                    ];
                    continue;
                }
            }

            // 11. Asignación de contraseña:
            // Modo institucional -> null (gestión LDAP central)
            // Modo local -> misma clave institucional cifrada como contraseña temporal
            $passwordHash = $esInstitucional ? null : Hash::make($clave);

            // 12. Creación e inserción del nuevo usuario
            $usuario = new User();
            $usuario->clave = $clave;
            $usuario->nombre = $nombre;
            $usuario->ap_pat = $apPat;
            $usuario->ap_mat = $apMat;
            $usuario->email = $email;
            $usuario->rol = $rolCanonico;
            $usuario->password = $passwordHash;
            $usuario->activo = 1;
            $usuario->save();

            // Notificar bienvenida al usuario registrado
            $passwordInicial = $esInstitucional ? null : $clave;
            TicketMailService::enviarBienvenidaUsuario($usuario, $passwordInicial, $esInstitucional);

            // Registrar índices para unicidad intra-archivo y conteos
            $clavesVistas[$clave] = $numFila;
            $emailsVistos[$email] = $numFila;

            if ($rolCanonico === 'Superadministrador') {
                $totalSuperadmins++;
            } elseif ($rolCanonico === 'Administrador') {
                $totalAdmins++;
            }

            $this->insertados[] = [
                'fila'   => $numFila,
                'clave'  => $clave,
                'nombre' => "{$nombre} {$apPat}" . ($apMat ? " {$apMat}" : ''),
                'email'  => $email,
                'rol'    => $rolCanonico,
            ];
        }
    }

    /**
     * Retorna el resumen consolidado de la importación.
     */
    public function getResumen(): array
    {
        return [
            'totalLeidos'   => $this->totalLeidos,
            'totalExito'    => count($this->insertados),
            'totalOmitidos' => count($this->omitidos),
            'insertados'    => $this->insertados,
            'omitidos'      => $this->omitidos,
        ];
    }
}
