<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'arquivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ];
    }
}
