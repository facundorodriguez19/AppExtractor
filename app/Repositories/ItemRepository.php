<?php

namespace App\Repositories;

use App\Models\Item;

class ItemRepository
{
    public function createMany(int $notaId, array $itens): void
    {
        foreach ($itens as $item) {
            Item::create(array_merge($item, ['nota_id' => $notaId]));
        }
    }
}
