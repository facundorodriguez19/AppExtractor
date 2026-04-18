<?php

namespace Tests\Unit;

use App\Exceptions\AIProcessingException;
use App\Services\AIService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AIServiceTest extends TestCase
{
    public function test_estruturar_nota_retorna_array_correto(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'empresa_emissora' => 'Teste Ltda',
                                        'cnpj' => '12.345.678/0001-95',
                                        'data_emissao' => '2024-01-15',
                                        'valor_total' => 150.75,
                                        'categoria' => 'alimentacao',
                                        'itens' => [
                                            [
                                                'nome' => 'Pizza',
                                                'quantidade' => 1,
                                                'unidade' => 'UN',
                                                'preco_unitario' => 150.75,
                                                'preco_total' => 150.75,
                                            ],
                                        ],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new AIService();
        $result = $service->estruturarNota('texto bruto ocr');

        $this->assertEquals('Teste Ltda', $result['empresa_emissora']);
        $this->assertEquals(150.75, $result['valor_total']);
        $this->assertEquals('alimentacao', $result['categoria']);
    }

    public function test_estruturar_nota_lanca_excecao_com_json_invalido(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'nao e um json']]]],
                ],
            ], 200),
        ]);

        $this->expectException(AIProcessingException::class);

        $service = new AIService();
        $service->estruturarNota('texto bruto ocr');
    }

    public function test_valores_sao_normalizados_corretamente(): void
    {
        $method = new \ReflectionMethod(AIService::class, 'normalizarDados');
        $method->setAccessible(true);

        $service = new AIService();
        $data = [
            'valor_total' => '150.75',
            'cnpj' => '12345678000195',
            'categoria' => 'INVALIDA',
        ];

        $normalized = $method->invoke($service, $data);

        $this->assertIsFloat($normalized['valor_total']);
        $this->assertEquals(150.75, $normalized['valor_total']);
        $this->assertEquals('12.345.678/0001-95', $normalized['cnpj']);
        $this->assertEquals('outros', $normalized['categoria']);
    }

    public function test_recibo_brasileiro_com_data_valores_e_itens_em_formato_local(): void
    {
        $method = new \ReflectionMethod(AIService::class, 'normalizarDados');
        $method->setAccessible(true);

        $service = new AIService();
        $data = [
            'empresa_emissora' => ' BRASIL FOOD DISTRIBUIDORA LTDA ',
            'cnpj' => '45123456000189',
            'data_emissao' => '18/04/2026',
            'valor_total' => 'R$ 121,46',
            'categoria' => 'Alimentação',
            'itens' => [
                [
                    'nome' => 'Arroz Integral 5kg',
                    'quantidade' => '2',
                    'unidade' => 'UN',
                    'preco_unitario' => 'R$ 28,50',
                    'preco_total' => 'R$ 57,00',
                ],
                [
                    'nome' => 'Passagem Ônibus',
                    'quantidade' => '1',
                    'unidade' => 'UN',
                    'preco_unitario' => 'R$ 5,50',
                    'preco_total' => 'R$ 5,50',
                ],
            ],
        ];

        $normalized = $method->invoke($service, $data);

        $this->assertEquals('BRASIL FOOD DISTRIBUIDORA LTDA', $normalized['empresa_emissora']);
        $this->assertEquals('45.123.456/0001-89', $normalized['cnpj']);
        $this->assertEquals('2026-04-18', $normalized['data_emissao']);
        $this->assertSame(121.46, $normalized['valor_total']);
        $this->assertEquals('alimentacao', $normalized['categoria']);
        $this->assertCount(2, $normalized['itens']);
        $this->assertSame(28.50, $normalized['itens'][0]['preco_unitario']);
        $this->assertSame(57.00, $normalized['itens'][0]['preco_total']);
        $this->assertSame(5.50, $normalized['itens'][1]['preco_total']);
    }
}
