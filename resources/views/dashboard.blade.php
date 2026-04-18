<x-app-layout>
    <div class="space-y-6 sm:space-y-8">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bem-vindo!</h1>
            <p class="text-gray-500">Aqui está o resumo dos seus gastos e processamentos.</p>
        </div>

        <!-- Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center space-x-4">
                <div class="p-3 bg-blue-50 text-blue-500 rounded-xl">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium tracking-wide">Total Notas</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $stats['total_notas'] }}</h3>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center space-x-4">
                <div class="p-3 bg-green-50 text-green-500 rounded-xl">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium tracking-wide">Gasto Total</p>
                    <h3 class="text-2xl font-bold text-gray-900">R$ {{ number_format($stats['total_gasto'], 2, ',', '.') }}</h3>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center space-x-4">
                <div class="p-3 bg-indigo-50 text-indigo-500 rounded-xl">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium tracking-wide">Gasto este Mês</p>
                    <h3 class="text-2xl font-bold text-gray-900">R$ {{ number_format($stats['gasto_mes'], 2, ',', '.') }}</h3>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center space-x-4">
                <div class="p-3 bg-purple-50 text-purple-500 rounded-xl">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium tracking-wide">Cat. Predominante</p>
                    <h3 class="text-2xl font-bold text-gray-900 capitalize">{{ $stats['categoria_predominante'] }}</h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
            <!-- Gráficos -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100" x-data="chartsHandler()" x-init="init()">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Análise de Gastos</h3>
                <div class="space-y-8">
                    <div class="h-64">
                        <canvas id="categoryChart"></canvas>
                    </div>
                    <div class="h-64 pt-8 border-t border-gray-50">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Últimas Notas -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Últimas Notas</h3>
                    <a href="{{ route('notas.index') }}" class="text-primary-500 text-sm font-medium hover:underline">Ver todas</a>
                </div>
                <div class="space-y-4">
                    @forelse($ultimasNotas as $nota)
                        <a href="{{ route('notas.show', $nota) }}" class="flex items-center justify-between gap-3 p-3 sm:p-4 rounded-xl hover:bg-gray-50 transition border border-transparent hover:border-gray-100">
                            <div class="flex min-w-0 items-center space-x-3 sm:space-x-4">
                                <div class="bg-gray-100 p-2 rounded-lg flex-shrink-0">
                                    @if($nota->arquivo_tipo === 'pdf')
                                        <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    @else
                                        <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900 truncate max-w-[9.5rem] sm:max-w-xs">{{ $nota->empresa_emissora ?? 'Processando...' }}</p>
                                    <p class="text-xs text-gray-500">{{ $nota->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold text-gray-900">R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</p>
                                <span class="text-[10px] px-2 py-1 rounded-full font-bold uppercase
                                    @if($nota->status === 'processado') bg-green-100 text-green-700
                                    @elseif($nota->status === 'erro') bg-red-100 text-red-700
                                    @else bg-yellow-100 text-yellow-700 @endif">
                                    {{ $nota->status }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <p class="text-center text-gray-500 py-8">Nenhuma nota encontrada.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        function chartsHandler() {
            return {
                categoryChart: null,
                monthlyChart: null,
                init() {
                    fetch('/api/notas/estatisticas')
                        .then(res => res.json())
                        .then(data => {
                            this.renderCategoryChart(data.por_categoria);
                            this.renderMonthlyChart(data.por_mes);
                        });
                },
                renderCategoryChart(data) {
                    const canvas = document.getElementById('categoryChart');
                    if (!canvas) return;

                    Chart.getChart(canvas)?.destroy();
                    this.categoryChart?.destroy();

                    this.categoryChart = new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: data.map(item => item.categoria),
                            datasets: [{
                                label: 'Gasto por Categoria',
                                data: data.map(item => item.total),
                                backgroundColor: '#6366f1',
                                borderRadius: 8,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                title: { display: true, text: 'Distribuição por Categoria (R$)' }
                            }
                        }
                    });
                },
                renderMonthlyChart(data) {
                    const canvas = document.getElementById('monthlyChart');
                    if (!canvas) return;

                    Chart.getChart(canvas)?.destroy();
                    this.monthlyChart?.destroy();

                    this.monthlyChart = new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: data.map(item => item.mes),
                            datasets: [{
                                label: 'Gasto Mensal',
                                data: data.map(item => item.total),
                                borderColor: '#6366f1',
                                tension: 0.4,
                                fill: true,
                                backgroundColor: 'rgba(99, 102, 241, 0.1)'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                title: { display: true, text: 'Evolução de Gastos (6 meses)' }
                            }
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>
