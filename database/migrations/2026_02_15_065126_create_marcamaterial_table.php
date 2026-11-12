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
        Schema::create('MarcaMaterial', function (Blueprint $table) {
            $table->foreignId('id_marca')->constrained('Marca', 'id_marca');
            $table->foreignId('id_material')->constrained('Material', 'id_material')->cascadeOnDelete();
            $table->primary(['id_marca', 'id_material']); //pk compuesta
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('MarcaMaterial');
    }
};
