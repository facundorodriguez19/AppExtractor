<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Nota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotaAutorizacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_nao_pode_ver_nota_de_outro_usuario(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $notaDeUser1 = Nota::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->get(route('notas.show', $notaDeUser1));

        $response->assertStatus(403);
    }

    public function test_usuario_nao_pode_editar_nota_de_outro_usuario(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $notaDeUser1 = Nota::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->get(route('notas.edit', $notaDeUser1));
        $response->assertStatus(403);

        $response = $this->actingAs($user2)->put(route('notas.update', $notaDeUser1), ['categoria' => 'saude']);
        $response->assertStatus(403);
    }

    public function test_usuario_nao_pode_deletar_nota_de_outro_usuario(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $notaDeUser1 = Nota::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->delete(route('notas.destroy', $notaDeUser1));

        $response->assertStatus(403);
        $this->assertDatabaseHas('notas', ['id' => $notaDeUser1->id]);
    }
}
