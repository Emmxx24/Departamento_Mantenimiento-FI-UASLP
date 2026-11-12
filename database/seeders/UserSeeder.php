<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================================
        // SUPERADMINISTRADOR MAESTRO POR DEFECTO, CAMBIAR AL QUE SE SOLICITE A LA HORA DE LA ENTREGA
        // TAMBIEN EN CONFIGURACIONSEEDER
        // =========================================================================
        User::firstOrCreate(
            ['clave' => 'Superadmin'],
            [
                'nombre'   => 'Superadmin',
                'ap_pat'   => '1',
                'email'    => 'a323352@alumnos.uaslp.mx',
                'password' => Hash::make('externo'),
                'rol'      => 'Superadministrador',
                'activo'   => 1,
            ]
        );
    }
}
