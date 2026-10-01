<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesImageField;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CornerImageStoreRequest;
use App\Http\Requests\Admin\CornerImageUpdateRequest;
use App\Models\CornerImage;

class CornerImageController extends Controller
{
    use ResolvesImageField;

    public function index()
    {
        $cornerImages = CornerImage::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.corner-images.index', compact('cornerImages'));
    }

    public function create()
    {
        $cornerImage = new CornerImage;

        return view('admin.corner-images.create', compact('cornerImage'));
    }

    public function store(CornerImageStoreRequest $request)
    {
        $data = $request->validated();
        $data['src'] = $this->resolveImage($request, 'src', null);
        unset($data['image']);

        CornerImage::create($data);

        return redirect()->route('admin.corner-images.index')
            ->with('sukses', 'Gambar pojok berhasil ditambahkan.');
    }

    public function edit(CornerImage $cornerImage)
    {
        return view('admin.corner-images.edit', compact('cornerImage'));
    }

    public function update(CornerImageUpdateRequest $request, CornerImage $cornerImage)
    {
        $data = $request->validated();
        $data['src'] = $this->resolveImage($request, 'src', $cornerImage->src);
        unset($data['image']);

        $cornerImage->update($data);

        return redirect()->route('admin.corner-images.index')
            ->with('sukses', 'Gambar pojok berhasil diperbarui.');
    }

    public function destroy(CornerImage $cornerImage)
    {
        $cornerImage->delete();

        return redirect()->route('admin.corner-images.index')
            ->with('sukses', 'Gambar pojok berhasil dihapus.');
    }
}
