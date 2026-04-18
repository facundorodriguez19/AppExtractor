<x-app-layout>
    <div class="max-w-4xl mx-auto" x-data="editHandler({{ json_encode($nota->itens) }}, {{ $nota->valor_total ?? 0 }})">
        <form action="{{ route('notas.update', $nota) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Editar Nota Fiscal</h1>
                        <p class="text-gray-500">Corrija os dados extraídos pelo sistema.</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('notas.show', $nota) }}" class="px-6 py-2 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Voltar
                        </a>
                        <button type="submit" class="px-8 py-2 bg-primary-500 text-white rounded-xl font-bold hover:bg-primary-600 transition shadow-lg shadow-primary-200">
                            Salvar Alterações
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Informações Básicas -->
                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                        <h2 class="text-lg font-bold text-gray-900 border-b border-gray-50 pb-4">Dados do Emissor</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Empresa Emissora</label>
                                <input type="text" name="empresa_emissora" value="{{ $nota->empresa_emissora }}" 
                                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">CNPJ</label>
                                <input type="text" name="cnpj" x-mask="99.999.999/9999-99" value="{{ $nota->cnpj }}" 
                                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Data Emissão</label>
                                    <input type="date" name="data_emissao" value="{{ $nota->data_emissao?->format('Y-m-d') }}" 
                                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Categoria</label>
                                    <select name="category" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition capitalize">
                                        @foreach(['alimentacao', 'transporte', 'saude', 'tecnologia', 'educacao', 'outros'] as $cat)
                                            <option value="{{ $cat }}" {{ $nota->categoria === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Valor Total e Arquivo -->
                    <div class="space-y-8">
                        <div class="bg-primary-500 p-8 rounded-2xl shadow-lg relative overflow-hidden">
                            <div class="relative z-10">
                                <h3 class="text-primary-100 font-bold uppercase tracking-widest text-xs mb-2">Valor Total</h3>
                                <div class="flex items-center text-white">
                                    <span class="text-2xl mr-2">R$</span>
                                    <input type="number" step="0.01" name="valor_total" x-model="total" readonly
                                           class="bg-transparent border-none p-0 text-4xl font-black focus:ring-0 w-full">
                                </div>
                                <p class="text-primary-200 text-xs mt-4">* O valor total é recalculado com base nos itens.</p>
                            </div>
                            <!-- Background Decoration -->
                            <div class="absolute -bottom-10 -right-10 h-32 w-32 bg-primary-400 rounded-full opacity-20"></div>
                            <div class="absolute top-0 right-0 h-24 w-24 bg-primary-600 rounded-full -mt-12 -mr-12 opacity-30"></div>
                        </div>

                        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                             <h2 class="text-lg font-bold text-gray-900 border-b border-gray-50 pb-4 mb-6">Itens da Nota</h2>
                             <div class="space-y-4 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(item, index) in itens" :key="index">
                                    <div class="p-4 bg-gray-50 rounded-xl relative group">
                                        <button @click.prevent="removeItem(index)" class="absolute top-2 right-2 text-gray-300 hover:text-red-500 transition">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <div class="grid grid-cols-1 gap-4">
                                            <input type="text" :name="`itens[${index}][nome]`" x-model="item.nome" placeholder="Nome do item"
                                                   class="w-full text-sm font-bold bg-transparent border-b border-gray-200 focus:border-primary-500 transition pb-1">
                                            
                                            <div class="grid grid-cols-3 gap-4">
                                                <div>
                                                    <label class="text-[10px] uppercase font-bold text-gray-400">Qtd</label>
                                                    <input type="number" step="0.001" :name="`itens[${index}][quantidade]`" x-model="item.quantidade" @input="calculateItemTotal(index)"
                                                           class="w-full text-xs bg-white border border-gray-100 rounded-lg p-2 mt-1">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] uppercase font-bold text-gray-400">Unidade</label>
                                                    <input type="text" :name="`itens[${index}][unidade]`" x-model="item.unidade"
                                                           class="w-full text-xs bg-white border border-gray-100 rounded-lg p-2 mt-1">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] uppercase font-bold text-gray-400">Total Item</label>
                                                    <input type="number" step="0.01" :name="`itens[${index}][preco_total]`" x-model="item.preco_total" @input="updateGrandTotal()"
                                                           class="w-full text-xs bg-white border border-gray-100 rounded-lg p-2 mt-1 font-bold text-primary-600">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                             </div>
                             <button @click.prevent="addItem" class="mt-6 w-full py-3 border-2 border-dashed border-gray-200 rounded-xl text-sm font-bold text-gray-400 hover:border-primary-300 hover:text-primary-500 transition">
                                + Adicionar Item
                             </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Alpine.js Mask Plugin via CDN (Optional but useful for inputs) -->
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js" defer></script>

    <script>
        function editHandler(initialItens, initialTotal) {
            return {
                itens: initialItens || [],
                total: initialTotal,
                addItem() {
                    this.itens.push({ nome: '', quantidade: 1, unidade: 'UN', preco_total: 0 });
                },
                removeItem(index) {
                    this.itens.splice(index, 1);
                    this.updateGrandTotal();
                },
                calculateItemTotal(index) {
                    // Logic to calculate if we had unit price, but here we prioritize manual total or simpler calculation
                    // For now, just ensure total is updated
                    this.updateGrandTotal();
                },
                updateGrandTotal() {
                    const total = this.itens.reduce((acc, item) => acc + parseFloat(item.preco_total || 0), 0);
                    this.total = total.toFixed(2);
                }
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
    </style>
</x-app-layout>
