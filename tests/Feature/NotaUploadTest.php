<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Nota;
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

    public function test_usuario_pode_fazer_upload_de_imagem_valida(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('nota.jpg');

        $response = $this->actingAs($user)->post(route('notas.store'), [
            'arquivo' => $file,
        ]);

        $response->assertRedirect();
        Storage::disk('public')->assertExists('notas/' . $file->hashName());
        $this->assertDatabaseHas('notas', ['user_id' => $user->id, 'arquivo_tipo' => 'imagem']);
        Queue::assertPushed(ProcessarNotaJob::class);
    }

    public function test_usuario_pode_fazer_upload_de_pdf_valido(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('nota.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->post(route('notas.store'), [
            'arquivo' => $file,
        ]);

        $response->assertRedirect();
        Storage::disk('public')->assertExists('notas/' . $file->hashName());
        $this->assertDatabaseHas('notas', ['arquivo_tipo' => 'pdf']);
    }

    public function test_upload_rejeita_arquivo_muito_grande(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('grande.jpg', 11000); // 11MB

        $response = $this->actingAs($user)->post(route('notas.store'), [
            'arquivo' => $file,
        ]);

        $response->assertSessionHasErrors('arquivo');
    }

    public function test_upload_rejeita_tipo_invalido(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('virus.exe', 100);

        $response = $this->actingAs($user)->post(route('notas.store'), [
            'arquivo' => $file,
        ]);

        $response->assertSessionHasErrors('arquivo');
    }

    public function test_usuario_nao_autenticado_e_redirecionado(): void
    {
        $file = UploadedFile::fake()->image('nota.jpg');
        $response = $this->post(route('notas.store'), ['arquivo' => $file]);
        $response->assertRedirect(route('login'));
    }
}
