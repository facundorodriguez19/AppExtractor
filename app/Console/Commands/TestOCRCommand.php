<?php

namespace App\Console\Commands;

use App\Services\OCRService;
use Exception;
use Illuminate\Console\Command;

class TestOCRCommand extends Command
{
    protected $signature = 'ocr:testar {arquivo}';

    protected $description = 'Testa a integracao com OCR.space enviando um arquivo local';

    public function handle(OCRService $ocrService): int
    {
        $arquivo = $this->argument('arquivo');

        if (!file_exists($arquivo)) {
            $this->error("Erro: Arquivo nao encontrado em {$arquivo}");
            return Command::FAILURE;
        }

        $this->info("Iniciando teste OCR para o arquivo: {$arquivo}");
        $this->warn('Aguarde, isso pode levar alguns segundos...');

        $start = microtime(true);

        try {
            $extension = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
            $tipo = $extension === 'pdf' ? 'pdf' : 'imagem';

            $this->line('Tentando processar via OCRService...');

            $text = $ocrService->extrairTextoPorCaminhoAbsoluto($arquivo, $tipo);
            $time = round(microtime(true) - $start, 2);

            $this->info("\n=== TEXTO EXTRAIDO ===");
            $this->line($text);
            $this->info('======================');
            $this->success("Processamento concluido em {$time} segundos.");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("\n!!! FALHA NO TESTE !!!");
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
