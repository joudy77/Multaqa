<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecitationSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // لاحقًا تحطي هون شرط صلاحية الأستاذة إذا احتجتِ
    }

    public function rules(): array
    {
        return [
            // 'student_id' => 'required|exists:students,id',
            // 'teacher_id'=>'required|exists:teachers,id',
            'from_page' => 'required|integer|min:1|max:604',
            'to_page' => 'required|integer|min:1|max:604|gte:from_page',
            'scheduled_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'to_page.gte' => 'صفحة النهاية يجب أن تكون أكبر أو تساوي صفحة البداية',
        ];
    }
}