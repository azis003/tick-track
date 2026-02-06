<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('tickets.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:5000',
            'category_id' => 'required|exists:categories,id',
            'user_priority_id' => 'required|exists:priorities,id',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
        ];

        // Helpdesk wajib memilih reporter
        if ($this->user()->hasRole('helpdesk')) {
            $rules['reporter_id'] = 'required|exists:users,id';
        } else {
            $rules['reporter_id'] = 'nullable';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul kendala wajib diisi.',
            'title.max' => 'Judul maksimal 200 karakter.',
            'description.required' => 'Deskripsi kendala wajib diisi.',
            'description.max' => 'Deskripsi maksimal 5000 karakter.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'user_priority_id.required' => 'Urgensi wajib dipilih.',
            'user_priority_id.exists' => 'Urgensi tidak valid.',
            'reporter_id.required' => 'Pelapor wajib dipilih.',
            'reporter_id.exists' => 'Pelapor tidak valid.',
            'attachments.max' => 'Maksimal 5 file lampiran.',
            'attachments.*.max' => 'Ukuran file maksimal 5MB.',
            'attachments.*.mimes' => 'Format file tidak didukung. Format yang didukung: JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX.',
        ];
    }
}
