<?php

namespace Database\Seeders;

use App\Models\WheelPrize;
use Illuminate\Database\Seeder;

class WheelPrizeSeeder extends Seeder
{
    public function run(): void
    {
        $prizes = [
            ['label' => 'Diskon 5 Juta',     'short' => "Diskon\n5 Juta",     'color' => '#0a5fd1', 'weight' => 3, 'msg' => 'Voucher Diskon Rp 5 Juta'],
            ['label' => 'Gratis Servis 1th',  'short' => "Gratis\nServis",     'color' => '#ffc529', 'weight' => 3, 'msg' => 'Gratis Servis 1 Tahun'],
            ['label' => 'Voucher BBM',        'short' => "Voucher\nBBM",       'color' => '#2e86ff', 'weight' => 4, 'msg' => 'Voucher BBM Rp 500rb'],
            ['label' => 'Kaca Film Gratis',   'short' => "Kaca Film\nGratis",  'color' => '#123a8f', 'weight' => 3, 'msg' => 'Kaca Film Gratis'],
            ['label' => 'Diskon 10 Juta',     'short' => "Diskon\n10 Juta",    'color' => '#25d366', 'weight' => 1, 'msg' => 'JACKPOT! Diskon Rp 10 Juta'],
            ['label' => 'Cashback 2 Juta',    'short' => "Cashback\n2 Juta",   'color' => '#4aa3ff', 'weight' => 3, 'msg' => 'Cashback Rp 2 Juta'],
        ];

        foreach ($prizes as $index => $prize) {
            $prize['sort_order'] = $index;
            WheelPrize::create($prize);
        }
    }
}
