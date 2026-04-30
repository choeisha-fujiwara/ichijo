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
            'article_id' => ['required', 'integer', 'exists:articles,id'],
            'reservation_slot_id' => ['required', 'integer', 'exists:reservation_slots,id'],
            'reservation_datetime' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'first_name_kana' => ['required', 'string', 'max:255'],
            'last_name_kana' => ['required', 'string', 'max:255'],
            'postal_code_1' => ['required', 'digits:3'],
            'postal_code_2' => ['required', 'digits:4'],
            'address_prefectures' => ['required', 'string', 'max:255'],
            'address_municipalities' => ['required', 'string', 'max:255'],
            'address_detail' => ['required', 'string', 'max:255'],
            'address_building' => ['nullable', 'string', 'max:255'],
            'phone-1' => ['required', 'digits_between:2,5'],
            'phone-2' => ['required', 'digits_between:1,5'],
            'phone-3' => ['required', 'digits_between:3,5'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'confirmed'],
            'email_confirmation' => ['required', 'string', 'email:rfc', 'max:255'],
            'memo' => ['nullable', 'string', 'max:1000'],
            'privacy_policy' => ['accepted'],
        ];
    }
}
