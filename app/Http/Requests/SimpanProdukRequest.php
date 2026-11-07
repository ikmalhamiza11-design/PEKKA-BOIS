<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimpanProdukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'harga' => preg_replace('/[\s.]|rp/i', '', (string) $this->input('harga')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'harga' => ['required', 'integer', 'min:500', 'max:100000000'],
            'stok' => ['required', 'integer', 'min:0', 'max:100000'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:max_width=6000,max_height=6000'],
            'aktif' => ['nullable', 'boolean'],
            'hapus_foto' => ['nullable', 'boolean'],
        ];
    }
}
