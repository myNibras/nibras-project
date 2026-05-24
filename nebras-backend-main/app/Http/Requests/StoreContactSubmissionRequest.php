<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactSubmissionRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:50'],
            'email'     => ['required', 'string', 'email', 'max:50'],
            'phone'     => ['nullable', 'string', 'max:30', 'regex:/^[\d\s\-+()]+$/'],
            'country'   => ['nullable', 'string', 'max:100'],
            'subject'   => ['required', 'string', 'max:100'],
            'message'   => ['required', 'string', 'max:250'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required.',
            'full_name.max'       => 'Full name may not exceed 50 characters.',
            'email.required'     => 'Email is required.',
            'email.email'        => 'Please provide a valid email address.',
            'email.max'          => 'Email may not exceed 50 characters.',
            'phone.regex'        => 'Please provide a valid phone number.',
            'subject.required'   => 'Subject is required.',
            'subject.max'        => 'Subject may not exceed 100 characters.',
            'message.required'   => 'Message is required.',
            'message.max'        => 'Message may not exceed 250 characters.',
        ];
    }
}
