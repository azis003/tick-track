<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetPendingRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:user,external'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Tipe pending harus dipilih.',
            'type.in' => 'Tipe pending tidak valid.',
            'reason.required' => 'Alasan pending harus diisi.',
            'reason.min' => 'Alasan pending minimal 10 karakter.',
            'reason.max' => 'Alasan pending maksimal 1000 karakter.',
        ];
    }
}
