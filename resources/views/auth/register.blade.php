<x-app-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-2xl rounded-2xl border border-gray-100">
            <h2 class="text-3xl font-black text-center text-gray-900 mb-8">Criar Conta</h2>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nome</label>
                        <input type="text" name="name" required autofocus class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">E-mail</label>
                        <input type="email" name="email" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Senha</label>
                        <input type="password" name="password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Confirmar Senha</label>
                        <input type="password" name="password_confirmation" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 transition">
                    </div>
                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:underline">Já tem uma conta?</a>
                    </div>
                    <button type="submit" class="w-full py-4 bg-primary-500 text-white rounded-xl font-bold text-lg hover:bg-primary-600 transition shadow-lg shadow-primary-200">
                        Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
