<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            ['name' => 'Daihatsu Terios', 'description' => 'SUV tangguh 7 penumpang',   'tag' => 'Terlaris', 'price' => 'Rp 219 Jt', 'img' => 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1000&q=80'],
            ['name' => 'Daihatsu Rocky',  'description' => 'Compact SUV bermesin turbo', 'tag' => 'Turbo',    'price' => 'Rp 285 Jt', 'img' => 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=1000&q=80'],
            ['name' => 'Daihatsu Xenia',  'description' => 'MPV keluarga paling nyaman', 'tag' => 'Favorit',  'price' => 'Rp 245 Jt', 'img' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1000&q=80'],
            ['name' => 'Daihatsu Ayla',   'description' => 'Mobil kota super irit',      'tag' => 'Hemat',    'price' => 'Rp 165 Jt', 'img' => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=1000&q=80'],
        ];

        foreach ($slides as $index => $slide) {
            $slide['sort_order'] = $index;
            HeroSlide::create($slide);
        }
    }
}
