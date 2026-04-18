<?php

namespace App\Jobs;

use App\Models\Nota;
use App\Services\NotaProcessorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessarNotaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(public Nota $nota)
    {
    }

    public function backoff(): array
    {
        return [60, 120, 300];
    }

    public function handle(NotaProcessorService $service): void
    {
        $service->processar($this->nota);
    }

    public function failed(Throwable $exception): void
    {
        $this->nota->update([
            'status' => 'erro',
            'erro_mensagem' => $exception->getMessage()
        ]);
    }
}
