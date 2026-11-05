<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        Configuracion::create([
            'sistema_inicializado' => false,
            'login_institucional'  => false,
            'max_superadmins'      => 3,
            'max_admins'           => 10,
            'correo_contacto'          => 'servicios.internet@uaslp.mx',
            'telefono_contacto'        => '(444) 826-2300 ext. 1234',
            'clave_superadmin_origen'  => 'Superadmin',
            'minutos_expiracion_otp'   => 15,
        ]);
    }
}
