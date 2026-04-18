<?php

namespace App\Repositories;

use App\Models\Nota;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NotaRepository
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Nota::query()->latest();

        if (isset($filters['categoria'])) {
            $query->where('categoria', $filters['categoria']);
        }

        if (isset($filters['data_inicio'])) {
            $query->whereDate('data_emissao', '>=', $filters['data_inicio']);
        }

        if (isset($filters['data_fim'])) {
            $query->whereDate('data_emissao', '<=', $filters['data_fim']);
        }

        if (isset($filters['busca'])) {
            $query->where(function($q) use ($filters) {
                $q->where('empresa_emissora', 'like', '%' . $filters['busca'] . '%')
                  ->orWhere('cnpj', 'like', '%' . $filters['busca'] . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): Nota
    {
        return Nota::findOrFail($id);
    }
}
