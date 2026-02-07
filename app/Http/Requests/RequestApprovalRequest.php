<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestApprovalRequest extends FormRequest
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
            'request_type' => ['required', 'in:purchase,vendor,other'],
            'request_reason' => ['required', 'string', 'min:20', 'max:2000'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'request_type.required' => 'Tipe approval harus dipilih.',
            'request_type.in' => 'Tipe approval tidak valid.',
            'request_reason.required' => 'Alasan permintaan approval harus diisi.',
            'request_reason.min' => 'Alasan minimal 20 karakter.',
            'request_reason.max' => 'Alasan maksimal 2000 karakter.',
            'estimated_cost.numeric' => 'Estimasi biaya harus berupa angka.',
            'estimated_cost.min' => 'Estimasi biaya tidak boleh negatif.',
        ];
    }
}
