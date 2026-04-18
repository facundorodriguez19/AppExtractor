<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        $stats = [
            'total_notas' => Nota::where('user_id', $userId)->count(),
            'total_gasto' => Nota::where('user_id', $userId)->where('status', 'processado')->sum('valor_total'),
            'gasto_mes' => Nota::where('user_id', $userId)
                ->where('status', 'processado')
                ->whereMonth('data_emissao', now()->month)
                ->sum('valor_total'),
            'categoria_predominante' => Nota::where('user_id', $userId)
                ->select('categoria', DB::raw('count(*) as total'))
                ->groupBy('categoria')
                ->orderByDesc('total')
                ->first()?->categoria ?? 'N/A'
        ];

        $ultimasNotas = Nota::where('user_id', $userId)->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'ultimasNotas'));
    }

    public function estatisticas()
    {
        $userId = auth()->id();

        // Gastos por categoria
        $porCategoria = Nota::where('user_id', $userId)
            ->where('status', 'processado')
            ->select('categoria', DB::raw('sum(valor_total) as total'))
            ->groupBy('categoria')
            ->get();

        // Gastos por mês (últimos 6 meses)
        $porMes = Nota::where('user_id', $userId)
            ->where('status', 'processado')
            ->where('data_emissao', '>=', now()->subMonths(6))
            ->select(DB::raw("DATE_FORMAT(data_emissao, '%Y-%m') as mes"), DB::raw('sum(valor_total) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return response()->json([
            'por_categoria' => $porCategoria,
            'por_mes' => $porMes
        ]);
    }
}
