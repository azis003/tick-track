<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TriageTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('tickets.triage');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'final_priority_id' => 'required|exists:priorities,id',
            'notes' => 'nullable|string|max:1000',
            'action' => 'required|in:assign,self_handle',
            'technician_id' => 'required_if:action,assign|nullable|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'final_priority_id.required' => 'Prioritas final wajib dipilih.',
            'final_priority_id.exists' => 'Prioritas tidak valid.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
            'action.required' => 'Tindakan harus dipilih.',
            'action.in' => 'Tindakan tidak valid.',
            'technician_id.required_if' => 'Teknisi wajib dipilih jika menugaskan.',
            'technician_id.exists' => 'Teknisi tidak valid.',
        ];
    }
}
