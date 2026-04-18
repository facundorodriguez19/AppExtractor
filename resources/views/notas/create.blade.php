<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-8">
            <div class="mb-6 sm:mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Nova Nota Fiscal</h1>
                <p class="text-gray-500">Envie uma imagem ou PDF para processamento automático.</p>
            </div>

            <form action="{{ route('notas.store') }}" method="POST" enctype="multipart/form-data" 
                  x-data="notaUploadForm()"
                  @submit="loading = true">
                @csrf

                <div class="space-y-6">
                    <!-- Dropzone -->
                    <div 
                        @dragover.prevent="dragging = true" 
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="selectDroppedFile($event)"
                        :class="dragging ? 'border-primary-500 bg-primary-50' : 'border-gray-300 bg-gray-50'"
                        class="relative border-2 border-dashed rounded-2xl p-6 sm:p-12 text-center transition-all duration-200 group"
                    >
                        <input type="file" name="arquivo" id="arquivo" class="hidden" accept=".jpg,.jpeg,.png,.pdf"
                               x-ref="fileInput"
                               @change="selectFile($event.target.files[0])">
                        
                        <label for="arquivo" class="cursor-pointer">
                            <div x-show="!preview" class="space-y-4">
                                <div class="mx-auto w-16 h-16 bg-white rounded-xl shadow-sm flex items-center justify-center text-primary-500 border border-gray-100 group-hover:scale-110 transition-transform">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-base sm:text-lg font-semibold text-gray-700">Clique ou arraste seu arquivo</p>
                                    <p class="text-sm text-gray-500">JPG, PNG ou PDF até 10MB</p>
                                </div>
                            </div>

                            <div x-show="preview" x-cloak class="space-y-4">
                                <template x-if="preview && preview.type.startsWith('image/')">
                                    <img :src="preview.url" class="mx-auto h-48 rounded-lg shadow-md object-cover">
                                </template>
                                <template x-if="preview && preview.type === 'application/pdf'">
                                    <div class="mx-auto w-32 h-40 bg-red-50 rounded-lg flex flex-col items-center justify-center border border-red-100">
                                        <svg class="h-16 w-16 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 012-2h4.586A1 1 0 0111.293 2.707l3 3a1 1 0 01.293.707V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                                        </svg>
                                        <span class="text-xs font-bold text-red-700 mt-2">PDF</span>
                                    </div>
                                </template>
                                <p class="text-sm font-medium text-gray-700 underline break-all" x-text="file?.name"></p>
                                <button type="button" @click.prevent="clearFile()" class="text-xs text-red-500 hover:underline">Remover</button>
                            </div>
                        </label>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 sm:gap-4">
                        <a href="{{ route('notas.index') }}" class="px-6 py-2 text-center text-sm font-medium text-gray-600 hover:text-gray-900">Cancelar</a>
                        <button type="submit" 
                                :disabled="!file || loading"
                                :class="(!file || loading) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-primary-600 active:scale-95 shadow-lg shadow-primary-200'"
                                class="w-full sm:w-auto justify-center px-8 py-3 bg-primary-500 text-white rounded-xl font-bold transition flex items-center space-x-2">
                            <template x-if="loading">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="loading ? 'Processando...' : 'Processar Nota'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function notaUploadForm() {
            return {
                dragging: false,
                preview: null,
                file: null,
                loading: false,
                selectDroppedFile(event) {
                    this.dragging = false;
                    const droppedFile = event.dataTransfer.files[0];
                    if (!droppedFile) return;

                    const transfer = new DataTransfer();
                    transfer.items.add(droppedFile);
                    this.$refs.fileInput.files = transfer.files;
                    this.selectFile(droppedFile);
                },
                selectFile(file) {
                    if (!file) return;
                    this.file = file;

                    if (!file.type.startsWith('image/')) {
                        this.preview = { url: null, type: file.type };
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (event) => {
                        this.preview = {
                            url: event.target.result,
                            type: file.type
                        };
                    };
                    reader.readAsDataURL(file);
                },
                clearFile() {
                    this.preview = null;
                    this.file = null;
                    this.$refs.fileInput.value = '';
                }
            }
        }
    </script>
</x-app-layout>
