<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SemanticSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => 'required|string|min:2|max:200',
            'top_k' => 'nullable|integer|min:1|max:20',
        ];
    }
}