<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_id')->constrained('notas')->onDelete('cascade');
            $table->string('nome');
            $table->decimal('quantidade', 10, 3)->default(1);
            $table->string('unidade', 10)->nullable();
            $table->decimal('preco_unitario', 10, 2)->nullable();
            $table->decimal('preco_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens');
    }
};
