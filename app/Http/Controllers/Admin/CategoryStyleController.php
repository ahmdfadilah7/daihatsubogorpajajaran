<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryStyleStoreRequest;
use App\Http\Requests\Admin\CategoryStyleUpdateRequest;
use App\Models\Car;
use App\Models\CategoryStyle;

class CategoryStyleController extends Controller
{
    public function index()
    {
        $categoryStyles = CategoryStyle::orderBy('category')->get();

        return view('admin.category-styles.index', compact('categoryStyles'));
    }

    public function create()
    {
        $categoryStyle = new CategoryStyle;

        return view('admin.category-styles.create', compact('categoryStyle'));
    }

    public function store(CategoryStyleStoreRequest $request)
    {
        CategoryStyle::create($request->validated());

        return redirect()->route('admin.category-styles.index')
            ->with('sukses', 'Gaya kategori berhasil ditambahkan.');
    }

    public function edit(CategoryStyle $categoryStyle)
    {
        return view('admin.category-styles.edit', compact('categoryStyle'));
    }

    public function update(CategoryStyleUpdateRequest $request, CategoryStyle $categoryStyle)
    {
        // category is read-only after create: only bg/label are mutated.
        $categoryStyle->update($request->validated());

        return redirect()->route('admin.category-styles.index')
            ->with('sukses', 'Gaya kategori berhasil diperbarui.');
    }

    public function destroy(CategoryStyle $categoryStyle)
    {
        // Block delete when any car references this category (design B.10/B.11).
        if (Car::where('category', $categoryStyle->category)->exists()) {
            return redirect()->route('admin.category-styles.index')
                ->with('gagal', 'Kategori "'.$categoryStyle->category.'" masih dipakai oleh mobil, tidak dapat dihapus.');
        }

        $categoryStyle->delete();

        return redirect()->route('admin.category-styles.index')
            ->with('sukses', 'Gaya kategori berhasil dihapus.');
    }
}
