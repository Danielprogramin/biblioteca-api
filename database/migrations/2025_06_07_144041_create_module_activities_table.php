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
        Schema::create('module_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('modulo', ['documentos', 'usuarios', 'configuracion', 'reportes', 'sistema']);
            $table->date('fecha');
            $table->integer('total_acciones')->default(0);
            $table->integer('acciones_crear')->default(0);
            $table->integer('acciones_editar')->default(0);
            $table->integer('acciones_eliminar')->default(0);
            $table->integer('acciones_consultar')->default(0);
            $table->integer('tiempo_en_modulo_minutos')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'modulo', 'fecha']);
            $table->index(['modulo', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_activities');
    }
};
