<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OCRService;
use Exception;

class TestOCRCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocr:testar {arquivo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a integração com OCR.space enviando um arquivo local';

    /**
     * Execute the console command.
     */
    public function handle(OCRService $ocrService)
    {
        $arquivo = $this->argument('arquivo');

        if (!file_exists($arquivo)) {
            $this->error("Erro: Arquivo não encontrado em {$arquivo}");
            return Command::FAILURE;
        }

        $this->info("Iniciando teste OCR para o arquivo: {$arquivo}");
        $this->warn("Aguarde, isso pode levar alguns segundos...");

        $start = microtime(true);

        try {
            // Como o OCRService usa Storage::path('public/' . $caminho),
            // aqui vamos fazer um pequeno hack para aceitar caminhos absolutos no teste
            // ou podemos simplesmente copiar o arquivo para o storage temporariamente.
            
            $extension = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
            $tipo = ($extension === 'pdf') ? 'pdf' : 'imagem';

            // Simula o comportamento do service mas permitindo arquivo externo
            // Para total diagnóstico, vamos usar o Service real mas ele espera caminhos no storage.
            // Vou instanciar um Service customizado ou apenas logar o texto.
            
            // Para ser fiel ao diagnóstico de "produção", vamos usar o service.
            // Mas o service faz Storage::path('public/'). Então o arquivo deve estar lá.
            
            $this->line("Tentando processar via OCRService...");
            
            // Vamos apenas ler o arquivo e usar a lógica interna do Service diretamente aqui
            // para evitar problemas de caminho no storage durante o teste CLI livre.
            
            $text = $ocrService->extrairTextoPorCaminhoAbsoluto($arquivo, $tipo);

            $end = microtime(true);
            $time = round($end - $start, 2);

            $this->info("\n=== TEXTO EXTRAÍDO ===");
            $this->line($text);
            $this->info("======================");
            $this->success("Processamento concluído em {$time} segundos.");

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error("\n!!! FALHA NO TESTE !!!");
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
