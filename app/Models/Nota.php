<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nota extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'empresa_emissora',
        'cnpj',
        'data_emissao',
        'valor_total',
        'categoria',
        'arquivo',
        'arquivo_tipo',
        'texto_ocr',
        'status',
        'erro_mensagem',
        'processado_em'
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'valor_total' => 'decimal:2',
        'processado_em' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
