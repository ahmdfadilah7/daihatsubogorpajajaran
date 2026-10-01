<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        $cars = [
            ['id' => 1, 'model' => 'Ayla',     'type' => '1.2 R Deluxe',  'category' => 'LCGC',  'year' => 2024, 'price' => 165000000, 'transmission' => 'CVT',      'fuel' => 'Bensin', 'seats' => 5, 'badge' => 'Irit',     'accent1' => '#2e86ff', 'accent2' => '#0a5fd1', 'img' => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=900&q=80'],
            ['id' => 2, 'model' => 'Sigra',    'type' => '1.2 R',         'category' => 'LCGC',  'year' => 2024, 'price' => 175000000, 'transmission' => 'Manual',   'fuel' => 'Bensin', 'seats' => 7, 'badge' => 'Keluarga', 'accent1' => '#ffc529', 'accent2' => '#f59e0b', 'img' => 'https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&w=900&q=80'],
            ['id' => 3, 'model' => 'Xenia',    'type' => '1.3 X CVT',     'category' => 'MPV',   'year' => 2024, 'price' => 245000000, 'transmission' => 'CVT',      'fuel' => 'Bensin', 'seats' => 7, 'badge' => 'Terlaris', 'accent1' => '#0a5fd1', 'accent2' => '#123a8f', 'img' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=900&q=80'],
            ['id' => 4, 'model' => 'Terios',   'type' => '1.5 R AT',      'category' => 'SUV',   'year' => 2024, 'price' => 305000000, 'transmission' => 'Otomatis', 'fuel' => 'Bensin', 'seats' => 7, 'badge' => 'Tangguh',  'accent1' => '#123a8f', 'accent2' => '#2e86ff', 'img' => 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=900&q=80'],
            ['id' => 5, 'model' => 'Rocky',    'type' => '1.0 Turbo ASA', 'category' => 'SUV',   'year' => 2024, 'price' => 285000000, 'transmission' => 'CVT',      'fuel' => 'Bensin', 'seats' => 5, 'badge' => 'Turbo',    'accent1' => '#2e86ff', 'accent2' => '#123a8f', 'img' => 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=900&q=80'],
            ['id' => 6, 'model' => 'Gran Max', 'type' => 'Blind Van',     'category' => 'Niaga', 'year' => 2023, 'price' => 165000000, 'transmission' => 'Manual',   'fuel' => 'Bensin', 'seats' => 2, 'badge' => null,       'accent1' => '#5b6a8c', 'accent2' => '#2e86ff', 'img' => 'https://images.unsplash.com/photo-1600661653561-629509216228?auto=format&fit=crop&w=900&q=80'],
            ['id' => 7, 'model' => 'Sirion',   'type' => '1.3 AT',        'category' => 'MPV',   'year' => 2023, 'price' => 235000000, 'transmission' => 'Otomatis', 'fuel' => 'Bensin', 'seats' => 5, 'badge' => 'Gaya',     'accent1' => '#4aa3ff', 'accent2' => '#ffc529', 'img' => 'https://images.unsplash.com/photo-1502877338535-766e1452684a?auto=format&fit=crop&w=900&q=80'],
            ['id' => 8, 'model' => 'Luxio',    'type' => '1.5 X',         'category' => 'MPV',   'year' => 2023, 'price' => 215000000, 'transmission' => 'Manual',   'fuel' => 'Bensin', 'seats' => 8, 'badge' => 'Lega',     'accent1' => '#0a5fd1', 'accent2' => '#ffc529', 'img' => 'https://images.unsplash.com/photo-1519245659620-e859806a8d3b?auto=format&fit=crop&w=900&q=80'],
            ['id' => 9, 'model' => 'Terios',   'type' => '1.5 X MT',      'category' => 'SUV',   'year' => 2022, 'price' => 268000000, 'transmission' => 'Manual',   'fuel' => 'Bensin', 'seats' => 7, 'badge' => null,       'accent1' => '#123a8f', 'accent2' => '#4aa3ff', 'img' => 'https://images.unsplash.com/photo-1533106418989-88406c7cc8ca?auto=format&fit=crop&w=900&q=80'],
        ];

        foreach ($cars as $index => $car) {
            $car['sort_order'] = $index;
            Car::create($car);
        }
    }
}
