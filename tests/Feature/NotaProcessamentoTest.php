<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Nota;
use App\Jobs\ProcessarNotaJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotaProcessamentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_nota_criada_com_status_pendente(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('nota.jpg');

        $this->actingAs($user)->post(route('notas.store'), ['arquivo' => $file]);

        $this->assertDatabaseHas('notas', ['status' => 'pendente']);
    }

    public function test_job_e_despachado_apos_upload(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('nota.jpg');

        $this->actingAs($user)->post(route('notas.store'), ['arquivo' => $file]);

        Queue::assertPushed(ProcessarNotaJob::class);
    }

    public function test_polling_retorna_status_correto(): void
    {
        $user = User::factory()->create();
        $nota = Nota::factory()->create(['user_id' => $user->id, 'status' => 'processando']);

        $response = $this->actingAs($user)->get("/api/notas/{$nota->id}/status");

        $response->assertJson([
            'status' => 'processando',
            'processado' => false
        ]);
    }

    public function test_nota_atualiza_status_para_processado(): void
    {
        $user = User::factory()->create();
        $nota = Nota::factory()->pendente()->create(['user_id' => $user->id]);

        // Simula atualização direta para testar o polling posterior
        $nota->update(['status' => 'processado']);

        $response = $this->actingAs($user)->get("/api/notas/{$nota->id}/status");
        $response->assertJson(['status' => 'processado', 'processado' => true]);
    }
}
