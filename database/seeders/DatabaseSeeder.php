<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ConfiguracionSeeder::class,
            UserSeeder::class,
            CatalogosSeeder::class,
            // TicketSeeder::class,    // Comentado para entrega limpia del sistema
        ]);
    }
}
