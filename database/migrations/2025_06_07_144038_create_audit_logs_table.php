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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('usuario');
            $table->enum('accion', ['crear', 'editar', 'eliminar', 'consultar', 'login', 'logout']);
            $table->enum('modulo', ['documentos', 'usuarios', 'configuracion', 'reportes', 'sistema']);
            $table->text('descripcion');
            $table->text('detalles')->nullable();
            $table->string('tabla_afectada')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('navegador')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('accion');
            $table->index('modulo');
            $table->index('created_at');
            $table->index(['tabla_afectada', 'registro_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
