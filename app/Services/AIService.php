<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exceptions\AIProcessingException;

class AIService
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', env('OPENAI_API_KEY'));
        $this->model = config('services.openai.model', env('OPENAI_MODEL', 'gpt-4o'));
    }

    public function estruturarNota(string $textoOcr): array
    {
        $prompt = "Você é um especialista em análise de documentos fiscais brasileiros.
Analise o texto extraído por OCR de uma nota fiscal e retorne EXCLUSIVAMENTE
um objeto JSON válido, sem nenhum texto adicional, sem blocos markdown,
sem explicações antes ou depois.

O JSON deve ter EXATAMENTE esta estrutura:
{
  \"empresa_emissora\": \"nome completo da empresa emissora\",
  \"cnpj\": \"XX.XXX.XXX/XXXX-XX\",
  \"data_emissao\": \"YYYY-MM-DD\",
  \"valor_total\": 0.00,
  \"itens\": [
    {
      \"nome\": \"descrição do produto ou serviço\",
      \"quantidade\": 1,
      \"unidade\": \"UN\",
      \"preco_unitario\": 0.00,
      \"preco_total\": 0.00
    }
  ],
  \"categoria\": \"alimentacao\"
}

Regras obrigatórias:
- valor_total DEVE ser número decimal, NUNCA string (correto: 45.90, errado: '45.90')
- preco_unitario e preco_total DEVEM ser números decimais
- data_emissao DEVE estar no formato YYYY-MM-DD (ex: 2024-01-15)
- cnpj DEVE estar no formato XX.XXX.XXX/XXXX-XX
- categoria DEVE ser exatamente uma destas opções:
  alimentacao, transporte, saude, tecnologia, educacao, outros
- Se não encontrar um campo, use null (não use string vazia)
- itens DEVE ser array, mesmo que vazio []
- quantidade e preco_unitario podem ser null se não encontrados

Texto da nota fiscal extraído por OCR:
{$textoOcr}";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0,
                'max_tokens' => 1500,
                'response_format' => ['type' => 'json_object']
            ]);

            $json = $response->json();

            if (!isset($json['choices'][0]['message']['content'])) {
                $errorMsg = $json['error']['message'] ?? 'Resposta inesperada da API';
                throw new AIProcessingException('Erro OpenAI: ' . $errorMsg);
            }

            $content = $json['choices'][0]['message']['content'];
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Tenta extrair JSON de markdown se necessário (fallback solicitado)
                if (preg_match('/```json(.*?)```/s', $content, $matches)) {
                    $data = json_decode(trim($matches[1]), true);
                }
            }

            if (!$data) {
                throw new AIProcessingException('Falha ao decodificar JSON da IA');
            }

            return $this->normalizarDados($data);
        } catch (Exception $e) {
            Log::error('Erro AIService: ' . $e->getMessage());
            throw new AIProcessingException('Falha no processamento da IA: ' . $e->getMessage());
        }
    }

    protected function normalizarDados(array $data): array
    {
        return [
            'empresa_emissora' => $data['empresa_emissora'] ?? null,
            'cnpj' => $this->formatarCNPJ($data['cnpj'] ?? null),
            'data_emissao' => $this->validarData($data['data_emissao'] ?? null),
            'valor_total' => (float) ($data['valor_total'] ?? 0),
            'itens' => $data['itens'] ?? [],
            'categoria' => $this->validarCategoria($data['categoria'] ?? 'outros'),
        ];
    }

    protected function formatarCNPJ(?string $cnpj): ?string
    {
        if (!$cnpj) return null;
        $cnpj = preg_replace('/\D/', '', $cnpj);
        if (strlen($cnpj) !== 14) return null;
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    protected function validarData(?string $data): ?string
    {
        if (!$data) return null;
        $d = \DateTime::createFromFormat('Y-m-d', $data);
        return $d && $d->format('Y-m-d') === $data ? $data : null;
    }

    protected function validarCategoria(string $categoria): string
    {
        $validas = ['alimentacao', 'transporte', 'saude', 'tecnologia', 'educacao', 'outros'];
        return in_array($categoria, $validas) ? $categoria : 'outros';
    }
}
