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
        // SUPERADMINISTRADOR MAESTRO POR DEFECTO (ENTREGA EN EL SOBRE)
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

        // =========================================================================
        // USUARIOS DE PRUEBA (COMENTADOS PARA ENTREGA LIMPIA DEL SISTEMA)
        // Descomentar para pruebas locales si se requieren usuarios de relleno.
        // =========================================================================
        /*
        $usuarios = [
            [
                'clave'    => '355521',
                'nombre'   => 'Fernando',
                'ap_pat'   => 'Constantino',
                'ap_mat'   => 'Vieyra',
                'email'    => 'a355521@alumnos.uaslp.mx',
                'password' => Hash::make('externo'),
                'rol'      => 'Administrador',
                'activo'   => 1,
            ],
            [
                'clave'    => '353262',
                'nombre'   => 'Christian',
                'ap_pat'   => 'Montaño',
                'ap_mat'   => 'González',
                'email'    => 'a353262@alumnos.uaslp.mx',
                'password' => Hash::make('externo'),
                'rol'      => 'Administrador',
                'activo'   => 1,
            ],
            [
                'clave'    => '355231',
                'nombre'   => 'Leslie',
                'ap_pat'   => 'Moreno',
                'ap_mat'   => 'Ramos',
                'email'    => 'a355231@alumnos.uaslp.mx',
                'password' => Hash::make('externo'),
                'rol'      => 'Solicitante',
                'activo'   => 1,
            ],
            [
                'clave'    => '256316',
                'nombre'   => 'Antonio',
                'ap_pat'   => 'Galván',
                'ap_mat'   => 'Trejo',
                'email'    => 'a256316@alumnos.uaslp.mx',
                'password' => Hash::make('externo'),
                'rol'      => 'Solicitante',
                'activo'   => 1,
            ],
        ];

        foreach ($usuarios as $usuario) {
            User::firstOrCreate(['clave' => $usuario['clave']], $usuario);
        }
        */
    }
}
