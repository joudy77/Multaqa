<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecitationErrorsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'errors' => 'required|array|min:1',
            'errors.*.word_id' => 'required|integer',
            'errors.*.surah_number' => 'required|integer',
            'errors.*.ayah_number' => 'required|integer',
            'errors.*.error_type' => 'required|in:red,blue,yellow,green',
        ];
    }
}