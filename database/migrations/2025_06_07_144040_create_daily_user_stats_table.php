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
        Schema::create('daily_user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('fecha');
            $table->integer('documentos_creados')->default(0);
            $table->integer('documentos_editados')->default(0);
            $table->integer('documentos_eliminados')->default(0);
            $table->integer('consultas_realizadas')->default(0);
            $table->integer('libros_creados')->default(0);
            $table->integer('libros_anillados_creados')->default(0);
            $table->integer('azs_creados')->default(0);
            $table->integer('tiempo_sesion_minutos')->default(0);
            $table->time('primera_actividad')->nullable();
            $table->time('ultima_actividad')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'fecha']);
            $table->index('fecha');
            $table->index(['user_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_user_stats');
    }
};
