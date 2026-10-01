<?php

namespace Database\Seeders;

use App\Models\CornerImage;
use Illuminate\Database\Seeder;

class CornerImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            ['src' => 'img/halo.jpeg',    'alt' => 'Halo dari Daihatsu Sahabat'],
            ['src' => 'img/bingung.jpeg', 'alt' => 'Bingung pilih mobil?'],
            ['src' => 'img/hubungi.jpeg', 'alt' => 'Hubungi kami sekarang'],
        ];

        foreach ($images as $index => $image) {
            $image['sort_order'] = $index;
            CornerImage::create($image);
        }
    }
}
