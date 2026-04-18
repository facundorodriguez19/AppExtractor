<?php

namespace Tests\Feature;

use App\Jobs\ProcessarNotaJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotaUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Queue::fake();
    }

    public function test_visitante_pode_fazer_upload_de_imagem_valida(): void
    {
        $file = UploadedFile::fake()->create('nota.jpg', 100, 'image/jpeg');

        $response = $this->post(route('notas.store'), [
            'arquivo' => $file,
        ]);

        $response->assertRedirect();
        Storage::disk('public')->assertExists('notas/' . $file->hashName());
        $this->assertDatabaseHas('notas', ['user_id' => null, 'arquivo_tipo' => 'imagem']);
        Queue::assertPushed(ProcessarNotaJob::class);
    }

    public function test_visitante_pode_fazer_upload_de_pdf_valido(): void
    {
        $file = UploadedFile::fake()->create('nota.pdf', 500, 'application/pdf');

        $response = $this->post(route('notas.store'), [
            'arquivo' => $file,
        ]);

        $response->assertRedirect();
        Storage::disk('public')->assertExists('notas/' . $file->hashName());
        $this->assertDatabaseHas('notas', ['arquivo_tipo' => 'pdf']);
    }

    public function test_upload_rejeita_arquivo_muito_grande(): void
    {
        $file = UploadedFile::fake()->create('grande.jpg', 11000); // 11MB

        $response = $this->post(route('notas.store'), [
            'arquivo' => $file,
        ]);

        $response->assertSessionHasErrors('arquivo');
    }

    public function test_upload_rejeita_tipo_invalido(): void
    {
        $file = UploadedFile::fake()->create('virus.exe', 100);

        $response = $this->post(route('notas.store'), [
            'arquivo' => $file,
        ]);

        $response->assertSessionHasErrors('arquivo');
    }

    public function test_visitante_pode_enviar_sem_login(): void
    {
        $file = UploadedFile::fake()->create('nota.jpg', 100, 'image/jpeg');
        $response = $this->post(route('notas.store'), ['arquivo' => $file]);
        $response->assertRedirect();
        $this->assertDatabaseHas('notas', ['user_id' => null]);
    }
}
