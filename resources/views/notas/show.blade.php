<x-app-layout>
    <div x-data="notaStatusHandler({{ $nota->id }}, '{{ $nota->status }}')" x-init="init()">
        <!-- Status Banner -->
        <div x-show="status === 'pendente' || status === 'processando'" class="mb-8 p-6 bg-indigo-50 border border-indigo-100 rounded-2xl flex flex-col items-center justify-center space-y-4">
            <svg class="animate-spin h-10 w-10 text-primary-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <div class="text-center">
                <h3 class="text-lg font-bold text-indigo-900">Processando sua nota...</h3>
                <p class="text-indigo-600">Estamos extraindo e estruturando os dados usando IA. Por favor, aguarde.</p>
            </div>
        </div>

        <div x-show="status === 'erro'" class="mb-8 p-6 bg-red-50 border border-red-100 rounded-2xl">
            <div class="flex items-start">
                <div class="p-2 bg-red-100 rounded-lg text-red-600 mr-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-red-900">Ops! Algo deu errado.</h3>
                    <p class="text-red-700 mb-4">{{ $nota->erro_mensagem }}</p>
                    <a href="{{ route('notas.edit', $nota) }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">
                        Editar Manualmente
                    </a>
                </div>
            </div>
        </div>

        <!-- Render Content if Processed or Error (to allow manual correction) -->
        <div x-show="status === 'processado' || status === 'erro'" class="space-y-8">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Detalhes da Nota</h1>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('notas.edit', $nota) }}" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Editar
                    </a>
                    <form action="{{ route('notas.destroy', $nota) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta nota?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                            Excluir
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Card 1: Informações Gerais -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-hidden relative">
                        <div class="absolute top-0 right-0 mt-4 mr-4">
                            @php
                                $colors = [
                                    'alimentacao' => 'bg-orange-100 text-orange-700',
                                    'transporte'  => 'bg-blue-100 text-blue-700',
                                    'saude'       => 'bg-green-100 text-green-700',
                                    'tecnologia'  => 'bg-purple-100 text-purple-700',
                                    'educacao'    => 'bg-indigo-100 text-indigo-700',
                                    'outros'      => 'bg-gray-100 text-gray-700'
                                ];
                                $catColor = $colors[$nota->categoria] ?? $colors['outros'];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $catColor }}">
                                {{ $nota->categoria }}
                            </span>
                        </div>

                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-6">Emissor</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Nome / Razão Social</p>
                                <p class="text-lg font-bold text-gray-900">{{ $nota->empresa_emissora ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">CNPJ</p>
                                <p class="text-gray-900">{{ $nota->cnpj ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Data de Emissão</p>
                                <p class="text-gray-900">{{ $nota->data_emissao?->format('d/m/Y') ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-6">Arquivo Original</h3>
                        <div class="rounded-xl overflow-hidden border border-gray-100 bg-gray-50">
                            @if($nota->arquivo_tipo === 'pdf')
                                <div class="flex flex-col items-center justify-center py-12">
                                    <svg class="h-16 w-16 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A1 1 0 0111.293 2.707l3 3a1 1 0 01.293.707V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                                    </svg>
                                    <a href="{{ asset('storage/' . $nota->arquivo) }}" target="_blank" class="mt-4 text-primary-500 font-bold hover:underline">Ver PDF</a>
                                </div>
                            @else
                                <img src="{{ asset('storage/' . $nota->arquivo) }}" class="w-full h-auto cursor-pointer" @click="window.open($el.src)">
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Card 2: Itens e Total -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Qtd</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Unitário</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($nota->itens as $item)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $item->nome }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 text-center">{{ number_format($item->quantidade, 3, ',', '.') }} {{ $item->unidade }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 text-right">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 font-bold text-right">R$ {{ number_format($item->preco_total, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">Nenhum item detectado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <div class="p-8 bg-primary-50 flex justify-between items-center">
                            <span class="text-primary-900 font-bold text-lg">Total da Nota</span>
                            <span class="text-3xl font-black text-primary-600">R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="bg-white rounded-2xl shadow-sm border border-gray-100">
                        <button @click="open = !open" class="w-full flex justify-between items-center p-6 text-sm font-bold text-gray-500 uppercase">
                            Visualizar Texto Bruto (OCR)
                            <svg class="h-5 w-5 transform transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak class="p-6 border-t border-gray-50 bg-gray-900 rounded-b-2xl">
                            <pre class="text-green-400 font-mono text-xs overflow-x-auto whitespace-pre-wrap">{{ $nota->texto_ocr }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function notaStatusHandler(id, currentStatus) {
            return {
                status: currentStatus,
                init() {
                    if (this.status === 'pendente' || this.status === 'processando') {
                        this.pollStatus();
                    }
                },
                pollStatus() {
                    const interval = setInterval(async () => {
                        try {
                            const res = await fetch(`/api/notas/${id}/status`);
                            const data = await res.json();
                            
                            this.status = data.status;

                            if (data.processado || data.erro) {
                                clearInterval(interval);
                                window.location.reload();
                            }
                        } catch (e) {
                            console.error('Polling error:', e);
                        }
                    }, 3000);
                }
            }
        }
    </script>
</x-app-layout>
