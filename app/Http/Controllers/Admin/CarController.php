<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesImageField;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CarStoreRequest;
use App\Http\Requests\Admin\CarUpdateRequest;
use App\Models\Car;

class CarController extends Controller
{
    use ResolvesImageField;

    public function index()
    {
        $cars = Car::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.cars.index', compact('cars'));
    }

    public function create()
    {
        $car = new Car;

        return view('admin.cars.create', compact('car'));
    }

    public function store(CarStoreRequest $request)
    {
        $data = $request->validated();
        $data['img'] = $this->resolveImage($request, 'img', null);
        unset($data['image']);

        Car::create($data);

        return redirect()->route('admin.cars.index')
            ->with('sukses', 'Mobil berhasil ditambahkan.');
    }

    public function edit(Car $car)
    {
        return view('admin.cars.edit', compact('car'));
    }

    public function update(CarUpdateRequest $request, Car $car)
    {
        $data = $request->validated();
        $data['img'] = $this->resolveImage($request, 'img', $car->img);
        unset($data['image']);

        $car->update($data);

        return redirect()->route('admin.cars.index')
            ->with('sukses', 'Mobil berhasil diperbarui.');
    }

    public function destroy(Car $car)
    {
        $car->delete();

        return redirect()->route('admin.cars.index')
            ->with('sukses', 'Mobil berhasil dihapus.');
    }
}
