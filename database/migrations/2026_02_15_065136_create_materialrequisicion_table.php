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
        Schema::create('MaterialRequisicion', function (Blueprint $table) {
            $table->id('id_item'); // PK propia (ya no compuesta)
            $table->foreignId('id_requisicion')->constrained('Requisicion', 'id_requisicion')->onDelete('cascade');
            // id_material es nullable: null = material libre (no está en el inventario)
            $table->unsignedBigInteger('id_material')->nullable();
            $table->foreign('id_material')->references('id_material')->on('Material')->nullOnDelete();
            // Campos para material libre (solo se usan cuando id_material es null)
            $table->string('nombre_libre', 150)->nullable();
            $table->text('especificaciones')->nullable();
            // Unidad: nullable string (no enum, para mayor flexibilidad)
            $table->string('unidad', 20)->nullable();
            // Marca como texto plano, NO es FK a la tabla Marca (solo para el formato de la requisición)
            $table->string('marca_texto', 100)->nullable();
            // Campos económicos
            $table->integer('cantidad')->default(1);
            $table->decimal('unitario', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2)->default(0); // calculado: cantidad * unitario

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('MaterialRequisicion');
    }
};
