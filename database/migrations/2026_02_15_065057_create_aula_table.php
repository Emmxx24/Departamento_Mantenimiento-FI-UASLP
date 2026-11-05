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
        Schema::create('CatalogoAula', function (Blueprint $table) {
            $table->id('id_aula');
            $table->string('nombre', 100);
            $table->foreignId('id_piso')->constrained('CatalogoPiso', 'id_piso')->onDelete('cascade');
            $table->foreignId('id_lugar')->constrained('CatalogoLugar', 'id_lugar');
            $table->boolean('activo')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CatalogoAula');
    }
};
