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
        Schema::create('Requisicion', function (Blueprint $table) {
            $table->id('id_requisicion');
            $table->foreignId('id_admin')->constrained('users');
            $table->string('proveedor', 100)->nullable();   // Proveedor (se captura en el formulario)
            $table->dateTime('fecha_req')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('iva', 4, 2)->default(0.16);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('cotizacion')->nullable()->default(null);
            $table->boolean('es_borrador')->default(1);      // 1 = borrador, 0 = finalizada
            $table->foreignId('id_ticket')->constrained('Ticket', 'id_ticket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Requisicion');
    }
};
