<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AIService;
use App\Exceptions\AIProcessingException;
use Illuminate\Support\Facades\Http;

class AIServiceTest extends TestCase
{
    public function test_estruturar_nota_retorna_array_correto(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'empresa_emissora' => 'Teste Ltda',
                                'cnpj' => '12.345.678/0001-95',
                                'data_emissao' => '2024-01-15',
                                'valor_total' => 150.75,
                                'categoria' => 'alimentacao',
                                'itens' => [
                                    ['nome' => 'Pizza', 'quantidade' => 1, 'unidade' => 'UN', 'preco_unitario' => 150.75, 'preco_total' => 150.75]
                                ]
                            ])
                        ]
                    ]
                ]
            ], 200)
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
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'não é um json']]
                ]
            ], 200)
        ]);

        $this->expectException(AIProcessingException::class);

        $service = new AIService();
        $service->estruturarNota('texto brute ocr');
    }

    public function test_valores_sao_normalizados_corretamente(): void
    {
        // Testa se "150.75" (string) vira 150.75 (float)
        // Isso é feito internamente no Service via normalizarDados
        
        $method = new \ReflectionMethod(AIService::class, 'normalizarDados');
        $method->setAccessible(true);
        
        $service = new AIService();
        $data = [
            'valor_total' => '150.75',
            'cnpj' => '12345678000195',
            'categoria' => 'INVALIDA'
        ];
        
        $normalized = $method->invoke($service, $data);
        
        $this->assertIsFloat($normalized['valor_total']);
        $this->assertEquals(150.75, $normalized['valor_total']);
        $this->assertEquals('12.345.678/0001-95', $normalized['cnpj']);
        $this->assertEquals('outros', $normalized['categoria']); // Default para invalida
    }
}
