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
        Schema::create('TicketReporte', function (Blueprint $table) {
            $table->foreignId('id_ticket')->constrained('Ticket', 'id_ticket');
            $table->foreignId('id_reporte')->constrained('Reporte', 'id_reporte');
            $table->primary(['id_ticket', 'id_reporte']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('TicketReporte');
    }
};
