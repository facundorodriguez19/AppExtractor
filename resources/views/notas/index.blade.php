<x-app-layout>
    <div class="space-y-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Minhas Notas Fiscais</h1>
                <p class="text-gray-500">Gerencie e visualize todos os seus documentos.</p>
            </div>
            <div class="flex items-center space-x-3 w-full md:w-auto">
                <a href="{{ route('notas.exportar.csv', request()->all()) }}" class="flex-1 md:flex-none px-6 py-2 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition text-center">
                    Exportar CSV
                </a>
                <a href="{{ route('notas.create') }}" class="flex-1 md:flex-none px-6 py-2 bg-primary-500 text-white rounded-xl text-sm font-bold hover:bg-primary-600 transition shadow-lg shadow-primary-200 text-center">
                    + Nova Nota
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <form action="{{ route('notas.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Categoria</label>
                    <select name="categoria" class="w-full px-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                        <option value="">Todas</option>
                        @foreach(['alimentacao', 'transporte', 'saude', 'tecnologia', 'educacao', 'outros'] as $cat)
                            <option value="{{ $cat }}" {{ request('categoria') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Busca</label>
                    <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Empresa ou CNPJ..."
                           class="w-full px-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Período</label>
                    <div class="flex items-center space-x-2">
                        <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="flex-1 px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-xs">
                        <span class="text-gray-300">-</span>
                        <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="flex-1 px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-xs">
                    </div>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 py-2 bg-gray-900 text-white rounded-xl text-sm font-bold hover:bg-gray-800 transition">Filtrar</button>
                    <a href="{{ route('notas.index') }}" class="px-4 py-2 bg-gray-100 text-gray-500 rounded-xl text-sm hover:bg-gray-200 transition">Limpar</a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Empresa / Emissão</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Categoria</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Valor</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($notas as $nota)
                            <tr class="hover:bg-gray-50 transition group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-10 w-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-500 flex-shrink-0 group-hover:bg-primary-50 group-hover:text-primary-500 transition">
                                            @if($nota->arquivo_tipo === 'pdf')
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                            @else
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 truncate w-48">{{ $nota->empresa_emissora ?? 'Pendente...' }}</p>
                                            <p class="text-xs text-gray-500">{{ $nota->data_emissao?->format('d/m/Y') ?? $nota->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-bold uppercase
                                        @if($nota->categoria === 'alimentacao') bg-orange-100 text-orange-700
                                        @elseif($nota->categoria === 'transporte') bg-blue-100 text-blue-700
                                        @elseif($nota->categoria === 'saude') bg-green-100 text-green-700
                                        @else bg-gray-100 text-gray-700 @endif">
                                        {{ $nota->categoria ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <p class="text-sm font-bold text-gray-900">R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $nota->itens()->count() }} itens</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase
                                        @if($nota->status === 'processado') bg-green-100 text-green-700
                                        @elseif($nota->status === 'erro') bg-red-100 text-red-700
                                        @elseif($nota->status === 'processando') bg-blue-100 text-blue-700
                                        @else bg-yellow-100 text-yellow-700 @endif">
                                        {{ $nota->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('notas.show', $nota) }}" class="p-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-primary-500 hover:text-white transition">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                        <a href="{{ route('notas.edit', $nota) }}" class="p-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-blue-500 hover:text-white transition">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <p class="mb-4">Nenhuma nota encontrada.</p>
                                    <a href="{{ route('notas.create') }}" class="text-primary-500 font-bold hover:underline">Enviar sua primeira nota</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($notas->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $notas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
