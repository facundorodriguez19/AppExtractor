<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Nota;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        $qtd = $this->faker->randomFloat(3, 1, 10);
        $preco = $this->faker->randomFloat(2, 5, 100);

        return [
            'nota_id' => Nota::factory(),
            'nome' => $this->faker->word,
            'quantidade' => $qtd,
            'unidade' => 'UN',
            'preco_unitario' => $preco,
            'preco_total' => $qtd * $preco,
        ];
    }
}
