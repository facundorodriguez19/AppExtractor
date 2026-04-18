<?php

namespace Database\Factories;

use App\Models\Nota;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotaFactory extends Factory
{
    protected $model = Nota::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'empresa_emissora' => $this->faker->company,
            'cnpj' => $this->faker->numerify('##.###.###/0001-##'),
            'data_emissao' => $this->faker->date(),
            'valor_total' => $this->faker->randomFloat(2, 10, 1000),
            'categoria' => $this->faker->randomElement(['alimentacao', 'transporte', 'saude', 'tecnologia', 'educacao', 'outros']),
            'arquivo' => 'notas/fake_nota.jpg',
            'arquivo_tipo' => 'imagem',
            'status' => 'pendente',
        ];
    }

    public function pendente(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pendente',
        ]);
    }

    public function processada(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processado',
            'processado_em' => now(),
        ]);
    }

    public function comErro(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'erro',
            'erro_mensagem' => 'Falha no processamento OCR',
        ]);
    }
}
