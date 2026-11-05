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
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->boolean('sistema_inicializado')->default(0);
            $table->boolean('login_institucional')->default(0);
            $table->unsignedTinyInteger('max_superadmins')->default(2);
            $table->unsignedTinyInteger('max_admins')->default(5);
            $table->string('correo_contacto', 100)->nullable();
            $table->string('telefono_contacto', 50)->nullable();
            $table->string('clave_superadmin_origen', 20)->default('Superadmin'); //modificable
            $table->unsignedSmallInteger('minutos_expiracion_otp')->default(15);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
