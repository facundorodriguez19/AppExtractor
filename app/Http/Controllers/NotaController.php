<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Http\Requests\UploadNotaRequest;
use App\Http\Requests\UpdateNotaRequest;
use App\Jobs\ProcessarNotaJob;
use App\Repositories\NotaRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotaController extends Controller
{
    public function __construct(protected NotaRepository $notaRepository) {}

    public function index(Request $request)
    {
        $notas = $this->notaRepository->paginate(15, $request->all());
        return view('notas.index', compact('notas'));
    }

    public function create()
    {
        return view('notas.create');
    }

    public function store(UploadNotaRequest $request)
    {
        $path = $request->file('arquivo')->store('notas', 'public');
        $type = $request->file('arquivo')->getClientOriginalExtension() === 'pdf' ? 'pdf' : 'imagem';

        $nota = Nota::create([
            'user_id' => null,
            'arquivo' => $path,
            'arquivo_tipo' => $type,
            'status' => 'pendente'
        ]);

        ProcessarNotaJob::dispatch($nota);

        return redirect()->route('notas.show', $nota)->with('success', 'Nota enviada para processamento!');
    }

    public function show(Nota $nota)
    {
        return view('notas.show', compact('nota'));
    }

    public function edit(Nota $nota)
    {
        return view('notas.edit', compact('nota'));
    }

    public function update(UpdateNotaRequest $request, Nota $nota)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $nota) {
            $nota->update(collect($validated)->only([
                'empresa_emissora',
                'cnpj',
                'data_emissao',
                'valor_total',
                'categoria',
            ])->all());
            
            if (array_key_exists('itens', $validated)) {
                $nota->itens()->delete();
                foreach ($validated['itens'] ?? [] as $item) {
                    $nota->itens()->create($item);
                }
            }
        });

        return redirect()->route('notas.show', $nota)->with('success', 'Nota atualizada com sucesso!');
    }

    public function destroy(Nota $nota)
    {
        Storage::disk('public')->delete($nota->arquivo);
        $nota->delete();

        return redirect()->route('notas.index')->with('success', 'Nota excluída!');
    }

    public function status(Nota $nota)
    {
        return response()->json([
            'status' => $nota->status,
            'processado' => $nota->status === 'processado',
            'erro' => $nota->status === 'erro'
        ]);
    }

    public function exportarCSV(Request $request)
    {
        $notas = Nota::query()
            ->when($request->categoria, fn($q) => $q->where('categoria', $request->categoria))
            ->when($request->data_inicio, fn($q) => $q->whereDate('data_emissao', '>=', $request->data_inicio))
            ->when($request->data_fim, fn($q) => $q->whereDate('data_emissao', '<=', $request->data_fim))
            ->get();

        $response = new StreamedResponse(function () use ($notas) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Empresa', 'CNPJ', 'Data', 'Valor Total', 'Categoria', 'Qtd Itens', 'Criado Em']);

            foreach ($notas as $nota) {
                fputcsv($handle, [
                    $nota->id,
                    $nota->empresa_emissora,
                    $nota->cnpj,
                    $nota->data_emissao?->format('d/m/Y'),
                    $nota->valor_total,
                    $nota->categoria,
                    $nota->itens()->count(),
                    $nota->created_at->format('d/m/Y H:i')
                ]);
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="notas_export.csv"');

        return $response;
    }
}
