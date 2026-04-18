<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Nota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportacaoCSVTest extends TestCase
{
    use RefreshDatabase;

    public function test_exportacao_csv_retorna_arquivo_correto(): void
    {
        $user = User::factory()->create();
        Nota::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('notas.exportar.csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="notas_export.csv"');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('ID,Empresa,CNPJ,Data,Valor Total,Categoria,Qtd Itens,Criado Em', $content);
    }

    public function test_exportacao_respeita_filtro_de_categoria(): void
    {
        $user = User::factory()->create();
        Nota::factory()->create(['user_id' => $user->id, 'categoria' => 'alimentacao', 'empresa_emissora' => 'Restaurante']);
        Nota::factory()->create(['user_id' => $user->id, 'categoria' => 'transporte', 'empresa_emissora' => 'Uber']);

        $response = $this->actingAs($user)->get(route('notas.exportar.csv', ['categoria' => 'alimentacao']));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Restaurante', $content);
        $this->assertStringNotContainsString('Uber', $content);
    }
}
