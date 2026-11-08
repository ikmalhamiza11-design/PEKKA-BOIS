<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
use App\Http\Requests\SimpanProdukRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\FotoProduk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function index(Request $request): View
    {
        $toko = $request->user()->shop;

        return view('penjual.produk.index', [
            'toko' => $toko,
            'produk' => $toko->products()->with('category')->latest('id')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('penjual.produk.form', [
            'produk' => null,
            'kategori' => Category::orderBy('urutan')->get(),
        ]);
    }

    public function store(SimpanProdukRequest $request): RedirectResponse
    {
        $data = $this->data($request);

        if ($request->hasFile('foto')) {
            $data['foto'] = FotoProduk::simpan($request->file('foto'));
        }

        $request->user()->shop->products()->create($data);

        return redirect()->route('penjual.produk.index')->with('pesan', 'Produk berhasil ditambahkan.');
    }

    public function edit(Request $request, int $id): View
    {
        return view('penjual.produk.form', [
            'produk' => $this->milik($request, $id),
            'kategori' => Category::orderBy('urutan')->get(),
        ]);
    }

    public function update(SimpanProdukRequest $request, int $id): RedirectResponse
    {
        $produk = $this->milik($request, $id);
        $fotoLama = $produk->foto;
        $data = $this->data($request);

        if ($request->hasFile('foto')) {
            $data['foto'] = FotoProduk::simpan($request->file('foto'));
        } elseif ($request->boolean('hapus_foto')) {
            $data['foto'] = null;
        }

        $produk->update($data);

        if (array_key_exists('foto', $data)) {
            FotoProduk::hapus($fotoLama);
        }

        return redirect()->route('penjual.produk.index')->with('pesan', 'Perubahan produk disimpan.');
    }

    public function status(Request $request, int $id): RedirectResponse
    {
        $produk = $this->milik($request, $id);
        $produk->update(['aktif' => ! $produk->aktif]);

        return redirect()->back(fallback: route('penjual.produk.index'))
            ->with('pesan', $produk->aktif ? 'Produk diaktifkan.' : 'Produk dinonaktifkan.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $produk = $this->milik($request, $id);
        $foto = $produk->foto;

        $produk->delete();
        FotoProduk::hapus($foto);

        return redirect()->route('penjual.produk.index')->with('pesan', 'Produk dihapus.');
    }

    protected function milik(Request $request, int $id): Product
    {
        return $request->user()->shop->products()->findOrFail($id);
    }

    protected function data(SimpanProdukRequest $request): array
    {
        $preOrder = $request->boolean('pre_order');

        return [
            ...$request->safe()->only(['nama', 'category_id', 'deskripsi', 'harga', 'stok']),
            'stok_harian' => $preOrder ? null : ($request->filled('stok_harian') ? (int) $request->input('stok_harian') : null),
            'pre_order' => $preOrder,
            'pre_order_hari' => $preOrder ? (int) $request->input('pre_order_hari') : null,
            'aktif' => $request->boolean('aktif'),
        ];
    }
}
