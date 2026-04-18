<?php

namespace Tests\Feature;

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
        Queue::fake();

        $file = UploadedFile::fake()->create('nota.jpg', 100, 'image/jpeg');

        $this->post(route('notas.store'), ['arquivo' => $file]);

        $this->assertDatabaseHas('notas', ['status' => 'pendente']);
    }

    public function test_job_e_despachado_apos_upload(): void
    {
        Queue::fake();
        $file = UploadedFile::fake()->create('nota.jpg', 100, 'image/jpeg');

        $this->post(route('notas.store'), ['arquivo' => $file]);

        Queue::assertPushed(ProcessarNotaJob::class);
    }

    public function test_polling_retorna_status_correto(): void
    {
        $nota = Nota::factory()->create(['status' => 'processando']);

        $response = $this->get("/api/notas/{$nota->id}/status");

        $response->assertJson([
            'status' => 'processando',
            'processado' => false
        ]);
    }

    public function test_nota_atualiza_status_para_processado(): void
    {
        $nota = Nota::factory()->pendente()->create();

        // Simula atualização direta para testar o polling posterior
        $nota->update(['status' => 'processado']);

        $response = $this->get("/api/notas/{$nota->id}/status");
        $response->assertJson(['status' => 'processado', 'processado' => true]);
    }
}
