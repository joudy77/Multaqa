<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * مدخلات الانسة: أي طالبة، مجال الصفحات اللي مجهزته الطالبة (من وين لوين)،
 * وكم سؤال تريد يقترحلها النظام.
 */
class SuggestSmartRecitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|integer|exists:students,id',
            'from_page' => 'required|integer|min:1',
            'to_page' => 'required|integer|min:1|gte:from_page',
            'count' => 'required|integer|min:1|max:20',
        ];
    }
}
