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
        Schema::create('POA', function (Blueprint $table) {
            $table->id('id_poa');
            $table->dateTime('fecha_poa')->nullable();
            $table->string('area', 50)->nullable();
            $table->string('subarea', 50)->nullable();
            $table->text('descripcion')->nullable();
            $table->text('observaciones')->nullable(); // NUEVO
            $table->decimal('monto', 8, 2)->nullable();
            $table->boolean('es_borrador')->default(1);
            $table->foreignId('id_requisicion')->constrained('Requisicion','id_requisicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('POA');
    }
};
