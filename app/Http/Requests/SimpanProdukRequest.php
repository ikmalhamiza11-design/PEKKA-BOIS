<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Contracts\Validation\Validator;
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
            'stok_harian' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'pre_order' => ['nullable', 'boolean'],
            'pre_order_hari' => ['nullable', 'required_if_accepted:pre_order', 'integer', 'min:1', 'max:14'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:max_width=6000,max_height=6000'],
            'aktif' => ['nullable', 'boolean'],
            'hapus_foto' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'pre_order_hari.required_if_accepted' => 'Isi berapa hari pembuatan produk pre-order ini (1 sampai 14 hari).',
            'pre_order_hari.min' => 'Lama pre-order minimal 1 hari.',
            'pre_order_hari.max' => 'Lama pre-order maksimal 14 hari.',
            'stok_harian.min' => 'Stok harian minimal 1. Kosongkan kalau stok tidak perlu diisi ulang tiap hari.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->boolean('pre_order')) {
                    return;
                }

                $kategori = Category::find($this->input('category_id'));

                if ($kategori && ! $kategori->bolehPreOrder()) {
                    $validator->errors()->add('pre_order', 'Pre-order hanya untuk produk makanan (kategori Kuliner).');
                }

                if (filled($this->input('stok_harian'))) {
                    $validator->errors()->add('stok_harian', 'Produk pre-order tidak memakai stok harian. Pilih salah satu.');
                }
            },
        ];
    }
}
