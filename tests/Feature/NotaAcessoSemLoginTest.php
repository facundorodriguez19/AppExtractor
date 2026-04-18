<?php

namespace Tests\Feature;

use App\Models\Nota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotaAcessoSemLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_pode_ver_nota_sem_login(): void
    {
        $nota = Nota::factory()->create();

        $response = $this->get(route('notas.show', $nota));

        $response->assertStatus(200);
    }

    public function test_visitante_pode_editar_nota_sem_login(): void
    {
        $nota = Nota::factory()->create();

        $response = $this->get(route('notas.edit', $nota));
        $response->assertStatus(200);

        $response = $this->put(route('notas.update', $nota), [
            'categoria' => 'saude',
            'valor_total' => 25.50,
        ]);

        $response->assertRedirect(route('notas.show', $nota));
        $this->assertDatabaseHas('notas', [
            'id' => $nota->id,
            'categoria' => 'saude',
            'valor_total' => 25.50,
        ]);
    }

    public function test_visitante_pode_deletar_nota_sem_login(): void
    {
        $nota = Nota::factory()->create();

        $response = $this->delete(route('notas.destroy', $nota));

        $response->assertRedirect(route('notas.index'));
        $this->assertDatabaseMissing('notas', ['id' => $nota->id]);
    }
}
