<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bibliotecas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento');
            $table->string('denominacion');
            $table->string('denominacion_numerica');
            $table->string('titulo');
            $table->string('autor');
            $table->string('editorial')->nullable();
            $table->string('tomo')->nullable();
            $table->year('año')->nullable();
            $table->string('pais')->nullable();
            $table->string('archivo');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bibliotecas');
    }
};
