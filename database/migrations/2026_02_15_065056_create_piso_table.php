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
        Schema::create('CatalogoPiso', function (Blueprint $table) {
            $table->id('id_piso');
            $table->integer('piso');
            $table->foreignId('id_edificio')->constrained('CatalogoEdificio', 'id_edificio')->onDelete('cascade');
            $table->boolean('activo')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CatalogoPiso');
    }
};
