<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesImageField;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialStoreRequest;
use App\Http\Requests\Admin\TestimonialUpdateRequest;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    use ResolvesImageField;

    public function index()
    {
        $testimonials = Testimonial::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        $testimonial = new Testimonial;

        return view('admin.testimonials.create', compact('testimonial'));
    }

    public function store(TestimonialStoreRequest $request)
    {
        $data = $request->validated();
        $data['img'] = $this->resolveImage($request, 'img', null);
        unset($data['image']);

        Testimonial::create($data);

        return redirect()->route('admin.testimonials.index')
            ->with('sukses', 'Testimoni berhasil ditambahkan.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(TestimonialUpdateRequest $request, Testimonial $testimonial)
    {
        $data = $request->validated();
        $data['img'] = $this->resolveImage($request, 'img', $testimonial->img);
        unset($data['image']);

        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')
            ->with('sukses', 'Testimoni berhasil diperbarui.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('sukses', 'Testimoni berhasil dihapus.');
    }
}
