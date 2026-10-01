<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkDestroy;
use App\Http\Controllers\Admin\Concerns\ResolvesImageField;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CarStoreRequest;
use App\Http\Requests\Admin\CarUpdateRequest;
use App\Models\Car;

class CarController extends Controller
{
    use HandlesBulkDestroy;
    use ResolvesImageField;

    protected function bulkModelClass(): string
    {
        return Car::class;
    }

    protected function bulkRouteName(): string
    {
        return 'admin.cars.index';
    }

    protected function bulkImageField(): ?string
    {
        return 'img';
    }

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
        $data['features'] = $this->parseFeatures($request->input('features_text'));
        unset($data['image'], $data['features_text']);

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
        $old = $car->img;
        $data['img'] = $this->resolveImage($request, 'img', $car->img);
        $data['features'] = $this->parseFeatures($request->input('features_text'));
        unset($data['image'], $data['features_text']);

        $car->update($data);

        if ($data['img'] !== $old) {
            $this->deleteUploadedImage($old);
        }

        return redirect()->route('admin.cars.index')
            ->with('sukses', 'Mobil berhasil diperbarui.');
    }

    public function destroy(Car $car)
    {
        $old = $car->img;
        $car->delete();
        $this->deleteUploadedImage($old);

        return redirect()->route('admin.cars.index')
            ->with('sukses', 'Mobil berhasil dihapus.');
    }

    /**
     * Turn the one-feature-per-line textarea into a clean list of strings.
     * Returns null when nothing was entered so the public fallback kicks in.
     *
     * @return array<int, string>|null
     */
    private function parseFeatures(?string $text): ?array
    {
        $features = collect(preg_split('/\r\n|\r|\n/', (string) $text))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        return $features ?: null;
    }
}
