<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_emissora' => 'nullable|string|max:255',
            'cnpj' => 'nullable|regex:/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/',
            'data_emissao' => 'nullable|date',
            'valor_total' => 'nullable|numeric|min:0',
            'categoria' => 'required|in:alimentacao,transporte,saude,tecnologia,educacao,outros',
            'itens' => 'nullable|array',
            'itens.*.nome' => 'required|string',
            'itens.*.quantidade' => 'required|numeric|min:0',
            'itens.*.preco_total' => 'required|numeric|min:0',
        ];
    }
}
