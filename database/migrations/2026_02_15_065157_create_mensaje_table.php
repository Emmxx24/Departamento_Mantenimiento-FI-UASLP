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
        Schema::create('Mensaje', function (Blueprint $table) {
            $table->id('id_mensaje');
            $table->string('texto');
            $table->dateTime('fecha_men');
            $table->foreignId('remitente')->constrained('users');
            $table->foreignId('id_ticket')->constrained('Ticket', 'id_ticket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Mensaje');
    }
};
