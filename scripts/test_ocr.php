<?php

/**
 * Script de teste independente para OCR.space
 * Uso: php scripts/test_ocr.php patch/to/image.jpg
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// 1. Carrega .env
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Exception $e) {
    die("Erro ao carregar .env: " . $e->getMessage() . "\n");
}

$apiKey = $_ENV['OCR_SPACE_API_KEY'] ?? null;
if (!$apiKey) {
    die("Erro: OCR_SPACE_API_KEY não definida no .env\n");
}

// 2. Valida argumento
if ($argc < 2) {
    die("Uso: php scripts/test_ocr.php <caminho_do_arquivo>\n");
}

$filePath = $argv[1];
if (!file_exists($filePath)) {
    die("Erro: Arquivo não encontrado: {$filePath}\n");
}

echo "--- Iniciando Teste OCR.space ---\n";
echo "Arquivo: {$filePath}\n";
echo "Aguarde...\n\n";

$start = microtime(true);

try {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mediaType = match($extension) {
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        default => 'image/jpeg'
    };

    $fileContent = file_get_contents($filePath);
    $base64 = base64_encode($fileContent);
    $base64Image = "data:{$mediaType};base64,{$base64}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.ocr.space/parse/image');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, true);
    
    $postFields = [
        'base64Image' => $base64Image,
        'language' => 'por',
        'isTable' => 'true',
        'OCREngine' => '2',
        'detectOrientation' => 'true',
        'scale' => 'true'
    ];

    if ($extension === 'pdf') {
        $postFields['isCreateSearchablePDF'] = 'false';
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: {$apiKey}"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("Erro Curl: " . $curlError);
    }

    if ($httpCode !== 200) {
        throw new Exception("Erro HTTP: " . $httpCode . " Response: " . $response);
    }

    $result = json_decode($response, true);
    
    if (isset($result['IsErroredOnProcessing']) && $result['IsErroredOnProcessing'] === true) {
        $msg = $result['ErrorMessage'][0] ?? 'Erro desconhecido';
        throw new Exception("Erro Processamento: " . $msg);
    }

    $text = $result['ParsedResults'][0]['ParsedText'] ?? 'Nenhum texto extraído.';
    $end = microtime(true);
    $time = round($end - $start, 2);

    echo "=== TEXTO EXTRAÍDO ===\n";
    echo $text . "\n";
    echo "======================\n";
    echo "Tempo de processamento: {$time} segundos\n";

} catch (Exception $e) {
    echo "\n!!! FALHA NO TESTE !!!\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
