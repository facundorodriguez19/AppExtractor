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
        $user = User::factory()->create([
            'name' => 'Usuário Teste',
            'email' => 'teste@exemplo.com',
            'password' => Hash::make('password'),
        ]);

        // Criar algumas notas de exemplo
        $nota = Nota::create([
            'user_id' => $user->id,
            'empresa_emissora' => 'Supermercado Modelo',
            'cnpj' => '12.345.678/0001-90',
            'data_emissao' => now()->subDays(2),
            'valor_total' => 150.50,
            'categoria' => 'alimentacao',
            'arquivo' => 'notas/exemplo.jpg',
            'arquivo_tipo' => 'imagem',
            'status' => 'processado',
            'processado_em' => now(),
        ]);

        Item::create([
            'nota_id' => $nota->id,
            'nome' => 'Arroz 5kg',
            'quantidade' => 1,
            'unidade' => 'UN',
            'preco_unitario' => 25.90,
            'preco_total' => 25.90,
        ]);

        Item::create([
            'nota_id' => $nota->id,
            'nome' => 'Feijão Carioca',
            'quantidade' => 2,
            'unidade' => 'KG',
            'preco_unitario' => 8.50,
            'preco_total' => 17.00,
        ]);
    }
}
