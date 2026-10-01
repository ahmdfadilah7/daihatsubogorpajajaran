<?php

namespace Database\Seeders;

use App\Models\MarqueeItem;
use Illuminate\Database\Seeder;

class MarqueeItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['text' => 'Irit BBM',          'icon' => 'fa-gas-pump',           'color' => '#0a5fd1'],
            ['text' => 'Garansi 3 Tahun',   'icon' => 'fa-shield-halved',      'color' => '#2e86ff'],
            ['text' => 'Servis Mudah',      'icon' => 'fa-wrench',             'color' => '#ffc529'],
            ['text' => 'Cicilan Ringan',    'icon' => 'fa-hand-holding-dollar', 'color' => '#123a8f'],
            ['text' => 'Nyaman Sekeluarga', 'icon' => 'fa-users',              'color' => '#25d366'],
            ['text' => 'Dealer Resmi',      'icon' => 'fa-award',              'color' => '#4aa3ff'],
        ];

        // Idempotent on the stable `text` key so re-seeding an already-seeded
        // DB (migrate, not migrate:fresh) never duplicates the pills.
        foreach ($items as $index => $item) {
            $item['sort_order'] = $index;
            MarqueeItem::updateOrCreate(['text' => $item['text']], $item);
        }
    }
}
