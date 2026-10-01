<?php

namespace Database\Seeders;

use App\Models\CategoryStyle;
use Illuminate\Database\Seeder;

class CategoryStyleSeeder extends Seeder
{
    public function run(): void
    {
        $styles = [
            ['category' => 'LCGC',  'bg' => '#2e86ff', 'label' => 'Hemat'],
            ['category' => 'MPV',   'bg' => '#0a5fd1', 'label' => 'Keluarga'],
            ['category' => 'SUV',   'bg' => '#123a8f', 'label' => 'SUV'],
            ['category' => 'Niaga', 'bg' => '#5b6a8c', 'label' => 'Niaga'],
        ];

        foreach ($styles as $style) {
            CategoryStyle::create($style);
        }
    }
}
