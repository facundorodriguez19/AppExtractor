<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\OCRException;

class OCRService
{
    /**
     * Extrai o texto de um arquivo usando a API do OCR.space.
     *
     * @param string $caminho Caminho relativo do arquivo no storage public.
     * @param string $tipo Tipo do arquivo (imagem ou pdf).
     * @return string Texto extraído.
     * @throws OCRException
     */
    public function extrairTexto(string $caminho, string $tipo): string
    {
        try {
            $caminhoCompleto = Storage::disk('public')->path($caminho);
            
            if (!file_exists($caminhoCompleto)) {
                throw new Exception("Arquivo não encontrado em: {$caminhoCompleto}");
            }

            $fileExtension = pathinfo($caminhoCompleto, PATHINFO_EXTENSION);
            $mediaType = $this->getMediaType($fileExtension);
            
            $fileContent = file_get_contents($caminhoCompleto);
            $base64 = base64_encode($fileContent);
            $base64Image = "data:{$mediaType};base64,{$base64}";

            $apiKey = config('services.ocr_space.key');
            if (!$apiKey || $apiKey === 'YOUR_FREE_OCR_SPACE_KEY') {
                throw new OCRException('Chave da API OCR.space nao configurada.');
            }

            $data = [
                'base64Image' => $base64Image,
                'language' => 'por',
                'isTable' => 'true',
                'OCREngine' => '2',
                'detectOrientation' => 'true',
                'scale' => 'true',
            ];

            if ($tipo === 'pdf') {
                $data['isCreateSearchablePDF'] = 'false';
            }

            $response = Http::withHeaders([
                'apikey' => $apiKey
            ])->timeout(30)
              ->asMultipart()
              ->post('https://api.ocr.space/parse/image', $data);

            if ($response->failed()) {
                throw new OCRException("Erro na requisição ao OCR.space: HTTP {$response->status()}");
            }

            $result = $response->json();

            if (isset($result['IsErroredOnProcessing']) && $result['IsErroredOnProcessing'] === true) {
                $errorMessage = $result['ErrorMessage'] ?? 'Erro desconhecido no processamento do OCR';
                $msg = is_array($errorMessage) ? ($errorMessage[0] ?? 'Erro desconhecido no processamento do OCR') : $errorMessage;
                throw new OCRException("Erro no processamento do OCR: {$msg}");
            }

            if (empty($result['ParsedResults'])) {
                throw new OCRException("A API do OCR não retornou resultados (ParsedResults vazio).");
            }

            return $result['ParsedResults'][0]['ParsedText'] ?? '';

        } catch (Exception $e) {
            Log::error('Erro no OCRService: ' . $e->getMessage(), [
                'caminho' => $caminho,
                'tipo' => $tipo
            ]);
            throw new OCRException($e->getMessage());
        }
    }

    /**
     * Versão do extrator que aceita caminho absoluto (usado em scripts de teste).
     */
    public function extrairTextoPorCaminhoAbsoluto(string $caminhoCompleto, string $tipo): string
    {
        try {
            if (!file_exists($caminhoCompleto)) {
                throw new Exception("Arquivo não encontrado em: {$caminhoCompleto}");
            }

            $fileExtension = pathinfo($caminhoCompleto, PATHINFO_EXTENSION);
            $mediaType = $this->getMediaType($fileExtension);
            
            $fileContent = file_get_contents($caminhoCompleto);
            $base64 = base64_encode($fileContent);
            $base64Image = "data:{$mediaType};base64,{$base64}";

            $apiKey = config('services.ocr_space.key');
            if (!$apiKey || $apiKey === 'YOUR_FREE_OCR_SPACE_KEY') {
                throw new OCRException('Chave da API OCR.space nao configurada.');
            }

            $data = [
                'base64Image' => $base64Image,
                'language' => 'por',
                'isTable' => 'true',
                'OCREngine' => '2',
                'detectOrientation' => 'true',
                'scale' => 'true',
            ];

            if ($tipo === 'pdf') {
                $data['isCreateSearchablePDF'] = 'false';
            }

            $response = Http::withHeaders(['apikey' => $apiKey])
                ->timeout(30)
                ->asMultipart()
                ->post('https://api.ocr.space/parse/image', $data);

            if ($response->failed()) throw new OCRException("Erro HTTP {$response->status()}");

            $result = $response->json();
            if (isset($result['IsErroredOnProcessing']) && $result['IsErroredOnProcessing'] === true) {
                $errorMessage = $result['ErrorMessage'] ?? 'Erro OCR';
                throw new OCRException(is_array($errorMessage) ? ($errorMessage[0] ?? 'Erro OCR') : $errorMessage);
            }

            return $result['ParsedResults'][0]['ParsedText'] ?? '';
        } catch (Exception $e) {
            Log::error('Erro OCRService (Absoluto): ' . $e->getMessage());
            throw new OCRException($e->getMessage());
        }
    }

    /**
     * Detecta o Media Type com base na extensão.
     */
    protected function getMediaType(string $extension): string
    {
        return match (strtolower($extension)) {
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/jpeg',
        };
    }
}
