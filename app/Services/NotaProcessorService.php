<?php

namespace App\Services;

use App\Models\Nota;
use App\Models\Item;
use App\Exceptions\OCRException;
use App\Exceptions\AIProcessingException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class NotaProcessorService
{
    public function __construct(
        protected OCRService $ocrService,
        protected AIService $aiService
    ) {}

    public function processar(Nota $nota): void
    {
        $transactionStarted = false;

        try {
            $nota->update(['status' => 'processando']);

            // 1. OCR
            $textoOcr = $this->ocrService->extrairTexto($nota->arquivo, $nota->arquivo_tipo);
            $nota->update(['texto_ocr' => $textoOcr]);

            // 2. IA
            $dadosEstruturados = $this->aiService->estruturarNota($textoOcr);

            // 3. Validação Real de CNPJ
            if ($dadosEstruturados['cnpj'] && !$this->validarCNPJ($dadosEstruturados['cnpj'])) {
                Log::warning("CNPJ inválido detectado para nota {$nota->id}: {$dadosEstruturados['cnpj']}");
                // Opcionalmente invalidar, mas aqui vamos apenas logar e prosseguir conforme spec
            }

            // 4. Persistência
            DB::beginTransaction();
            $transactionStarted = true;

            $nota->update([
                'empresa_emissora' => $dadosEstruturados['empresa_emissora'],
                'cnpj' => $dadosEstruturados['cnpj'],
                'data_emissao' => $dadosEstruturados['data_emissao'],
                'valor_total' => $dadosEstruturados['valor_total'],
                'categoria' => $dadosEstruturados['categoria'],
                'status' => 'processado',
                'processado_em' => now(),
            ]);

            // Limpa itens anteriores se houver (reprocessamento)
            $nota->itens()->delete();

            foreach ($dadosEstruturados['itens'] as $itemData) {
                Item::create([
                    'nota_id' => $nota->id,
                    'nome' => $itemData['nome'] ?? 'Item sem nome',
                    'quantidade' => $itemData['quantidade'] ?? 1,
                    'unidade' => $itemData['unidade'] ?? null,
                    'preco_unitario' => $itemData['preco_unitario'] ?? null,
                    'preco_total' => $itemData['preco_total'] ?? ($itemData['valor_total'] ?? 0),
                ]);
            }

            DB::commit();
            $transactionStarted = false;
        } catch (Exception $e) {
            if ($transactionStarted) {
                DB::rollBack();
            }

            Log::error("Erro no processamento da nota {$nota->id}: " . $e->getMessage());
            
            $nota->update([
                'status' => 'erro',
                'erro_mensagem' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function validarCNPJ(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^\d]/', '', $cnpj);

        if (strlen($cnpj) != 14) return false;
        if (preg_match('/(\d)\1{13}/', $cnpj)) return false;

        for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $m = ($t - 7), $i = 0; $i < $t; $i++) {
                $d += $cnpj[$i] * $m;
                $m = ($m == 2 ? 9 : --$m);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cnpj[$i] != $d) return false;
        }

        return true;
    }
}
