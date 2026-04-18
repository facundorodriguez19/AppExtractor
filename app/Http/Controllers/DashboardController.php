<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_notas' => Nota::count(),
            'total_gasto' => Nota::where('status', 'processado')->sum('valor_total'),
            'gasto_mes' => Nota::query()
                ->where('status', 'processado')
                ->whereYear('data_emissao', now()->year)
                ->whereMonth('data_emissao', now()->month)
                ->sum('valor_total'),
            'categoria_predominante' => Nota::query()
                ->select('categoria', DB::raw('count(*) as total'))
                ->groupBy('categoria')
                ->orderByDesc('total')
                ->first()?->categoria ?? 'N/A'
        ];

        $ultimasNotas = Nota::latest()->take(5)->get();

        return view('dashboard', compact('stats', 'ultimasNotas'));
    }

    public function estatisticas()
    {
        $dateExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', data_emissao)"
            : "DATE_FORMAT(data_emissao, '%Y-%m')";

        // Gastos por categoria
        $porCategoria = Nota::query()
            ->where('status', 'processado')
            ->select('categoria', DB::raw('sum(valor_total) as total'))
            ->groupBy('categoria')
            ->get();

        // Gastos por mês (últimos 6 meses)
        $porMes = Nota::query()
            ->where('status', 'processado')
            ->where('data_emissao', '>=', now()->subMonths(6))
            ->select(DB::raw("{$dateExpression} as mes"), DB::raw('sum(valor_total) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return response()->json([
            'por_categoria' => $porCategoria,
            'por_mes' => $porMes
        ]);
    }
}
