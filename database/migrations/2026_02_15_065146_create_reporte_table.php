<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Reporte', function (Blueprint $table) {
            $table->id('id_reporte');
            $table->datetime('fecha_rep');
            $table->datetime('fecha_ini')->nullable();
            $table->datetime('fecha_fin')->nullable();
            $table->foreignId('id_edificio')->nullable();
            $table->foreignId('id_servicio')->nullable();
            $table->foreignId('id_lugar')->nullable();
            $table->foreignId('id_admin')->nullable()->constrained('users', 'id');
            $table->string('prioridad')->nullable();
            $table->string('estado')->nullable();
            $table->string('ruta_pdf')->nullable();
            $table->string('ruta_excel')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Reporte');
    }
};
