<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'number' => 'required|string|max:10|unique:users,number',
            'password' => 'required|string|min:8',
            'last_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'home_address' => 'required|string|max:255',
            'goal' => 'required|integer', 
            'college' => 'required|string|max:255',
            'path' => 'required|in:زاد,أترجة',
            'start_page' => 'required|integer|min:1',
            'end_page' => ['required', 'integer', 'min:' . ($this->start_page + (20 * $this->goal)-1),'max:'.min (($this->start_page + (20 * $this->goal)+2),604)], 
            'days_of_memorization' => 'required|in:SundayTuesdayThursday,SaturdayMondayWednesday',
            //
        ];
    }
}
