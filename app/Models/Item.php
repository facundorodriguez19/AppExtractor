<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    protected $table = 'itens';

    protected $fillable = [
        'nota_id',
        'nome',
        'quantidade',
        'unidade',
        'preco_unitario',
        'preco_total'
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
        'preco_unitario' => 'decimal:2',
        'preco_total' => 'decimal:2',
    ];

    public function nota(): BelongsTo
    {
        return $this->belongsTo(Nota::class);
    }
}
