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
        Schema::create('Ticket', function (Blueprint $table) {
            $table->id('id_ticket');
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_cierre')->nullable();
            $table->enum('estado', ['Pendiente', 'Atendiendo', 'Terminado', 'Cerrado'])->default('Pendiente');
            $table->text('descripcion')->nullable();
            $table->string('asunto', 100)->nullable();
            $table->enum('prioridad', ['Alta', 'Media', 'Baja'])->nullable();
            $table->boolean('es_borrador')->default(1); // si es borrador es 1, si no es 0
            $table->foreignId('id_solicitante')->constrained('users');
            $table->foreignId('id_admin_atiende')->nullable()->constrained('users')->nullable();
            $table->foreignId('id_edificio')->nullable()->constrained('CatalogoEdificio', 'id_edificio');
            $table->foreignId('id_aula')->nullable()->constrained('CatalogoAula', 'id_aula');
            $table->foreignId('id_servicio')->nullable()->constrained('CatalogoServicio', 'id_servicio');
            $table->integer('piso')->nullable();
            // $table->foreignId('id_requisicion')->nullable()->constrained('Requisicion', 'id_requisicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Ticket');
    }
};
