<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormPostRequest extends FormRequest
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
            'email' => ['required', 'string', 'email:strict,dns,spoof', 'max:255'],
            'age' => ['required'],
            'gender' => ['required'],
            'tel' => ['nullable', 'string', 'regex:/^0[0-9]{9,10}$/u'],
            'q02' => ['required'],
            'q03' => ['required'],
            'q04' => ['required'],
            'q06' => ['required'],
            'q09' => ['required'],
            'q11' => ['required'],
            'q12' => ['required'],
        ];
    }
}
