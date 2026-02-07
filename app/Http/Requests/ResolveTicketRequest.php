<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolveTicketRequest extends FormRequest
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
            'resolution' => ['required', 'string', 'min:20', 'max:5000'],
            'evidence' => ['nullable', 'array', 'max:5'],
            'evidence.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'resolution.required' => 'Solusi/penyelesaian harus diisi.',
            'resolution.min' => 'Solusi minimal 20 karakter agar informatif.',
            'resolution.max' => 'Solusi maksimal 5000 karakter.',
            'evidence.max' => 'Maksimal 5 file bukti.',
            'evidence.*.max' => 'Ukuran file maksimal 10MB.',
            'evidence.*.mimes' => 'Tipe file tidak didukung.',
        ];
    }
}
