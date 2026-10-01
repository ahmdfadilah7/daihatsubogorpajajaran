<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesImageField;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HeroSlideStoreRequest;
use App\Http\Requests\Admin\HeroSlideUpdateRequest;
use App\Models\HeroSlide;

class HeroSlideController extends Controller
{
    use ResolvesImageField;

    public function index()
    {
        $heroSlides = HeroSlide::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.hero-slides.index', compact('heroSlides'));
    }

    public function create()
    {
        $heroSlide = new HeroSlide;

        return view('admin.hero-slides.create', compact('heroSlide'));
    }

    public function store(HeroSlideStoreRequest $request)
    {
        $data = $request->validated();
        $data['img'] = $this->resolveImage($request, 'img', null);
        unset($data['image']);

        HeroSlide::create($data);

        return redirect()->route('admin.hero-slides.index')
            ->with('sukses', 'Slide hero berhasil ditambahkan.');
    }

    public function edit(HeroSlide $heroSlide)
    {
        return view('admin.hero-slides.edit', compact('heroSlide'));
    }

    public function update(HeroSlideUpdateRequest $request, HeroSlide $heroSlide)
    {
        $data = $request->validated();
        $data['img'] = $this->resolveImage($request, 'img', $heroSlide->img);
        unset($data['image']);

        $heroSlide->update($data);

        return redirect()->route('admin.hero-slides.index')
            ->with('sukses', 'Slide hero berhasil diperbarui.');
    }

    public function destroy(HeroSlide $heroSlide)
    {
        $heroSlide->delete();

        return redirect()->route('admin.hero-slides.index')
            ->with('sukses', 'Slide hero berhasil dihapus.');
    }
}
