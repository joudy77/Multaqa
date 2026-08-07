<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** الأنسة تنشئ جلسة سبر ذكي لطالبة معيّنة على مجال صفحات محدد */
class CreateSmartRecitationSessionRequest extends FormRequest
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
