<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\NotaProcessorService;
use App\Services\OCRService;
use App\Services\AIService;

class CNPJValidacaoTest extends TestCase
{
    protected NotaProcessorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock das dependências que não queremos testar aqui
        $ocr = $this->createMock(OCRService::class);
        $ai = $this->createMock(AIService::class);
        $this->service = new NotaProcessorService($ocr, $ai);
    }

    public function test_cnpj_valido_retorna_true(): void
    {
        // CNPJs reais válidos (gerados por geradores de teste)
        $this->assertTrue($this->service->validarCNPJ('12.345.678/0001-95'));
        $this->assertTrue($this->service->validarCNPJ('45.997.418/0001-53'));
        $this->assertTrue($this->service->validarCNPJ('04.252.011/0001-10'));
    }

    public function test_cnpj_invalido_retorna_false(): void
    {
        $this->assertFalse($this->service->validarCNPJ('12.345.678/0001-00')); // Dígitos errados
        $this->assertFalse($this->service->validarCNPJ('11.111.111/1111-11')); // Números repetidos
        $this->assertFalse($this->service->validarCNPJ('123')); // Muito curto
    }

    public function test_cnpj_com_formatacao_diferente(): void
    {
        // Deve funcionar apenas com números ou com pontos/traço
        $this->assertTrue($this->service->validarCNPJ('12345678000195'));
        $this->assertTrue($this->service->validarCNPJ(' 12.345.678/0001-95 '));
    }
}
