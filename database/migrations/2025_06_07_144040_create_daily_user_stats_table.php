<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('tipo_documento', ['libros', 'libros_anillados', 'azs']);
            $table->integer('documentos_procesados')->default(0);
            $table->date('fecha');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['fecha', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_user_stats');
    }
};