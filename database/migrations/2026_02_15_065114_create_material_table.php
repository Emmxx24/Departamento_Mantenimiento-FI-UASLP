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
        Schema::create('Material', function (Blueprint $table) {
            $table->id('id_material');
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->integer('cantidad');
            $table->foreignId('id_unidad')->constrained('CatalogoUnidad', 'id_unidad');
            $table->string('imagen')->nullable();
            $table->boolean('activo')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Material');
    }
};
