<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Nota;
use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Criar usuário admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@teste.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Criar 20 notas processadas com itens variados
        $categorias = ['alimentacao', 'transporte', 'saude', 'tecnologia', 'educacao', 'outros'];

        for ($i = 0; $i < 20; $i++) {
            $nota = Nota::factory()->processada()->create([
                'user_id' => $admin->id,
                'categoria' => $categorias[array_rand($categorias)],
                'data_emissao' => now()->subDays(rand(0, 180)),
            ]);

            // Adiciona de 1 a 5 itens por nota
            Item::factory()->count(rand(1, 5))->create([
                'nota_id' => $nota->id
            ]);

            // Atualiza o valor_total da nota com a soma dos itens
            $nota->update([
                'valor_total' => $nota->itens()->sum('preco_total')
            ]);
        }
    }
}
