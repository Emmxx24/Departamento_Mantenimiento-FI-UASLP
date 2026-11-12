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
        Schema::create('Archivo', function (Blueprint $table) {
            $table->id('id_archivo');
            $table->string('nombre_archivo');
            $table->string('ruta');
            $table->string('extension', 10);
            $table->foreignId('id_ticket')->nullable()->constrained('Ticket', 'id_ticket');
            $table->foreignId('id_mensaje')->nullable()->constrained('Mensaje', 'id_mensaje');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Archivo');
    }
};
