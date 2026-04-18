<?php

namespace App\Services;

use App\Exceptions\AIProcessingException;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $apiKey;
    protected string $model;
    protected string $thinkingLevel;
    protected string $apiBaseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
        $this->model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-3.1-pro-preview'));
        $this->thinkingLevel = config('services.gemini.thinking_level', env('GEMINI_THINKING_LEVEL', 'medium'));
    }

    public function estruturarNota(string $textoOcr): array
    {
        if (!$this->apiKey || $this->apiKey === 'YOUR_GEMINI_API_KEY') {
            throw new AIProcessingException('Chave da API Gemini nao configurada.');
        }

        $prompt = <<<PROMPT
Voce e um especialista em documentos fiscais brasileiros. Analise o texto OCR de nota fiscal, cupom fiscal, recibo de venda, comprovante de pagamento ou documento comercial e retorne somente um objeto JSON valido.

Estrutura obrigatoria:
{
  "empresa_emissora": "nome completo da empresa emissora",
  "cnpj": "XX.XXX.XXX/XXXX-XX",
  "data_emissao": "YYYY-MM-DD",
  "valor_total": 0.00,
  "itens": [
    {
      "nome": "descricao do produto ou servico",
      "quantidade": 1,
      "unidade": "UN",
      "preco_unitario": 0.00,
      "preco_total": 0.00
    }
  ],
  "categoria": "alimentacao"
}

Regras obrigatorias:
- Nao escreva explicacoes, markdown ou texto fora do JSON.
- Use o VALOR TOTAL final pago no documento, nao subtotal. Se houver desconto/imposto, considere o total final.
- Converta datas brasileiras como 18/04/2026 para YYYY-MM-DD.
- Converta valores brasileiros como R$ 1.234,56 para numero JSON 1234.56.
- Extraia todas as linhas de produtos/servicos da tabela de itens. Ignore codigo do item, numero do recibo, serie, telefone, observacoes e rodape.
- "quantidade", "preco_unitario", "preco_total" e "valor_total" devem ser numeros ou null, nunca strings.
- "cnpj" deve ficar mascarado como XX.XXX.XXX/XXXX-XX quando existir.
- "categoria" deve ser uma destas opcoes: alimentacao, transporte, saude, tecnologia, educacao, outros.
- Escolha a categoria predominante pelos itens e valores. Em recibos de mercado/alimentos use alimentacao; passagens, taxi, app ou onibus use transporte.
- Se um campo nao existir, use null. "itens" deve ser array, mesmo vazio.

Exemplo de recibo esperado:
- Empresa: BRASIL FOOD DISTRIBUIDORA LTDA
- CNPJ: 45.123.456/0001-89
- Data: 18/04/2026
- Itens: Arroz Integral 5kg, Feijao Carioca 1kg, Oleo de Soja 900ml, Pao Frances (duzia), Passagem Onibus
- Total: R$ 121,46
- Categoria predominante: alimentacao

Texto OCR:
{$textoOcr}
PROMPT;

        try {
            $response = Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(90)->post($this->apiUrl(), [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0,
                    'maxOutputTokens' => 4096,
                    'responseMimeType' => 'application/json',
                    'responseJsonSchema' => $this->notaSchema(),
                    'thinkingConfig' => [
                        'thinkingLevel' => $this->thinkingLevel,
                    ],
                ],
            ]);

            $json = $response->json() ?? [];

            if ($response->failed()) {
                $errorMsg = $json['error']['message'] ?? "HTTP {$response->status()}";
                throw new AIProcessingException('Erro Gemini: ' . $errorMsg);
            }

            $content = $this->extractTextFromGeminiResponse($json);

            if ($content === '') {
                $errorMsg = $json['error']['message'] ?? 'Resposta inesperada da API';
                throw new AIProcessingException('Erro Gemini: ' . $errorMsg);
            }

            $data = $this->decodeJsonContent($content);

            if (!$data) {
                throw new AIProcessingException('Falha ao decodificar JSON da IA');
            }

            return $this->normalizarDados($data);
        } catch (Exception $e) {
            Log::error('Erro AIService: ' . $e->getMessage());
            throw new AIProcessingException('Falha no processamento da IA: ' . $e->getMessage());
        }
    }

    protected function apiUrl(): string
    {
        $model = trim($this->model, '/');

        return "{$this->apiBaseUrl}/{$model}:generateContent";
    }

    protected function extractTextFromGeminiResponse(array $json): string
    {
        $parts = $json['candidates'][0]['content']['parts'] ?? [];
        $texts = [];

        foreach ($parts as $part) {
            if (isset($part['text'])) {
                $texts[] = $part['text'];
            }
        }

        return trim(implode('', $texts));
    }

    protected function decodeJsonContent(string $content): ?array
    {
        $data = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }

        if (preg_match('/```(?:json)?\s*(.*?)```/is', $content, $matches)) {
            $data = json_decode(trim($matches[1]), true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }

        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $data = json_decode($matches[0], true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }

        return null;
    }

    protected function notaSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'empresa_emissora' => ['type' => ['string', 'null']],
                'cnpj' => ['type' => ['string', 'null']],
                'data_emissao' => [
                    'type' => ['string', 'null'],
                    'format' => 'date',
                ],
                'valor_total' => ['type' => ['number', 'null']],
                'itens' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'nome' => ['type' => ['string', 'null']],
                            'quantidade' => ['type' => ['number', 'null']],
                            'unidade' => ['type' => ['string', 'null']],
                            'preco_unitario' => ['type' => ['number', 'null']],
                            'preco_total' => ['type' => ['number', 'null']],
                        ],
                        'required' => ['nome', 'quantidade', 'unidade', 'preco_unitario', 'preco_total'],
                        'additionalProperties' => false,
                    ],
                ],
                'categoria' => [
                    'type' => 'string',
                    'enum' => ['alimentacao', 'transporte', 'saude', 'tecnologia', 'educacao', 'outros'],
                ],
            ],
            'required' => ['empresa_emissora', 'cnpj', 'data_emissao', 'valor_total', 'itens', 'categoria'],
            'additionalProperties' => false,
        ];
    }

    protected function normalizarDados(array $data): array
    {
        $itens = [];

        foreach (($data['itens'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }

            $itemNormalizado = $this->normalizarItem($item);

            if ($itemNormalizado['nome'] || $itemNormalizado['preco_total'] !== null) {
                $itens[] = $itemNormalizado;
            }
        }

        return [
            'empresa_emissora' => $this->normalizarTexto($data['empresa_emissora'] ?? null),
            'cnpj' => $this->formatarCNPJ($data['cnpj'] ?? null),
            'data_emissao' => $this->validarData($data['data_emissao'] ?? null),
            'valor_total' => $this->normalizarNumero($data['valor_total'] ?? null) ?? 0.0,
            'itens' => $itens,
            'categoria' => $this->validarCategoria($data['categoria'] ?? 'outros'),
        ];
    }

    protected function normalizarItem(array $item): array
    {
        return [
            'nome' => $this->normalizarTexto($item['nome'] ?? $item['descricao'] ?? null),
            'quantidade' => $this->normalizarNumero($item['quantidade'] ?? null),
            'unidade' => $this->normalizarTexto($item['unidade'] ?? 'UN') ?? 'UN',
            'preco_unitario' => $this->normalizarNumero($item['preco_unitario'] ?? null),
            'preco_total' => $this->normalizarNumero($item['preco_total'] ?? $item['valor_total'] ?? null),
        ];
    }

    protected function normalizarTexto(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : preg_replace('/\s+/', ' ', $texto);
    }

    protected function normalizarNumero(mixed $valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_int($valor) || is_float($valor)) {
            return round((float) $valor, 2);
        }

        $texto = trim((string) $valor);

        if ($texto === '') {
            return null;
        }

        $negativo = str_contains($texto, '-') || preg_match('/^\(.*\)$/', $texto);
        $texto = preg_replace('/[^\d,.]/', '', $texto);

        if ($texto === '') {
            return null;
        }

        $ultimaVirgula = strrpos($texto, ',');
        $ultimoPonto = strrpos($texto, '.');

        if ($ultimaVirgula !== false && $ultimoPonto !== false) {
            if ($ultimaVirgula > $ultimoPonto) {
                $texto = str_replace('.', '', $texto);
                $texto = str_replace(',', '.', $texto);
            } else {
                $texto = str_replace(',', '', $texto);
            }
        } elseif ($ultimaVirgula !== false) {
            $texto = str_replace('.', '', $texto);
            $texto = str_replace(',', '.', $texto);
        }

        if (!is_numeric($texto)) {
            return null;
        }

        $numero = round((float) $texto, 2);

        return $negativo ? -$numero : $numero;
    }

    protected function formatarCNPJ(mixed $cnpj): ?string
    {
        if (!$cnpj) {
            return null;
        }

        $cnpj = preg_replace('/\D/', '', (string) $cnpj);

        if (strlen($cnpj) !== 14) {
            return null;
        }

        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    protected function validarData(mixed $data): ?string
    {
        if (!$data) {
            return null;
        }

        $data = trim((string) $data);
        $data = preg_replace('/\s+\d{1,2}:\d{2}(:\d{2})?.*$/', '', $data);
        $formatos = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'Y/m/d'];

        foreach ($formatos as $formato) {
            $date = \DateTime::createFromFormat('!' . $formato, $data);

            if ($date && $date->format($formato) === $data) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    protected function validarCategoria(string $categoria): string
    {
        $categoria = $this->normalizarCategoriaTexto($categoria);
        $validas = ['alimentacao', 'transporte', 'saude', 'tecnologia', 'educacao', 'outros'];

        if (in_array($categoria, $validas, true)) {
            return $categoria;
        }

        $mapa = [
            'alimento' => 'alimentacao',
            'alimentos' => 'alimentacao',
            'alimenticia' => 'alimentacao',
            'mercado' => 'alimentacao',
            'supermercado' => 'alimentacao',
            'restaurante' => 'alimentacao',
            'comida' => 'alimentacao',
            'onibus' => 'transporte',
            'passagem' => 'transporte',
            'taxi' => 'transporte',
            'uber' => 'transporte',
            'combustivel' => 'transporte',
            'farmacia' => 'saude',
            'medico' => 'saude',
            'hospital' => 'saude',
            'software' => 'tecnologia',
            'hardware' => 'tecnologia',
            'computador' => 'tecnologia',
            'curso' => 'educacao',
            'livro' => 'educacao',
            'escola' => 'educacao',
        ];

        return $mapa[$categoria] ?? 'outros';
    }

    protected function normalizarCategoriaTexto(string $categoria): string
    {
        $categoria = trim($categoria);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $categoria);

        if ($ascii !== false) {
            $categoria = $ascii;
        }

        $categoria = strtolower($categoria);

        return preg_replace('/[^a-z]/', '', $categoria) ?: 'outros';
    }
}
