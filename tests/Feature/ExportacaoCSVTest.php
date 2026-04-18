<?php

namespace Tests\Feature;

use App\Models\Nota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportacaoCSVTest extends TestCase
{
    use RefreshDatabase;

    public function test_exportacao_csv_retorna_arquivo_correto(): void
    {
        Nota::factory()->count(3)->create();

        $response = $this->get(route('notas.exportar.csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="notas_export.csv"');
        
        $content = $response->streamedContent();
        $firstLine = strtok($content, "\n");

        $this->assertSame(
            ['ID', 'Empresa', 'CNPJ', 'Data', 'Valor Total', 'Categoria', 'Qtd Itens', 'Criado Em'],
            str_getcsv($firstLine)
        );
    }

    public function test_exportacao_respeita_filtro_de_categoria(): void
    {
        Nota::factory()->create(['categoria' => 'alimentacao', 'empresa_emissora' => 'Restaurante']);
        Nota::factory()->create(['categoria' => 'transporte', 'empresa_emissora' => 'Uber']);

        $response = $this->get(route('notas.exportar.csv', ['categoria' => 'alimentacao']));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Restaurante', $content);
        $this->assertStringNotContainsString('Uber', $content);
    }
}
