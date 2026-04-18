<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('empresa_emissora')->nullable();
            $table->string('cnpj', 18)->nullable();
            $table->date('data_emissao')->nullable();
            $table->decimal('valor_total', 10, 2)->nullable();
            $table->enum('categoria', ['alimentacao', 'transporte', 'saude', 'tecnologia', 'educacao', 'outros'])->nullable();
            $table->string('arquivo');
            $table->enum('arquivo_tipo', ['imagem', 'pdf']);
            $table->longText('texto_ocr')->nullable();
            $table->enum('status', ['pendente', 'processando', 'processado', 'erro'])->default('pendente');
            $table->text('erro_mensagem')->nullable();
            $table->timestamp('processado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
