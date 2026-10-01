<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            ['name' => 'Rina Kartika',  'city' => 'Bekasi',    'car' => 'Daihatsu Xenia',    'rating' => 5, 'color' => '#0a5fd1', 'img' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=700&q=80', 'text' => 'Xenia bikin mudik sekeluarga jadi nyaman banget. Bagasi luas, AC dingin sampai baris ketiga. Prosesnya juga cepat dan ramah.'],
            ['name' => 'Budi Santoso',  'city' => 'Depok',     'car' => 'Daihatsu Terios',   'rating' => 5, 'color' => '#123a8f', 'img' => 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=700&q=80', 'text' => 'Terios tangguh diajak ke mana saja, dari kota sampai jalan kampung. Servisnya gampang dan sparepart terjangkau. Puas!'],
            ['name' => 'Siti Aminah',   'city' => 'Tangerang', 'car' => 'Daihatsu Ayla',     'rating' => 4, 'color' => '#2e86ff', 'img' => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=700&q=80', 'text' => 'Ayla hemat BBM banget buat harian ngantor. Lincah di jalan sempit dan gampang parkir. Cicilannya juga ringan.'],
            ['name' => 'Andi Pratama',  'city' => 'Jakarta',   'car' => 'Daihatsu Rocky',    'rating' => 5, 'color' => '#4aa3ff', 'img' => 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=700&q=80', 'text' => 'Rocky turbo-nya responsif, desainnya keren dan modern. Fitur keselamatannya lengkap. Anak muda wajib coba!'],
            ['name' => 'Dewi Lestari',  'city' => 'Bogor',     'car' => 'Daihatsu Sigra',    'rating' => 5, 'color' => '#ffc529', 'img' => 'https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&w=700&q=80', 'text' => 'Mobil pertama keluarga kami. Sigra muat 7 orang, harganya bersahabat. Sales-nya sabar bantu kami sampai deal.'],
            ['name' => 'Hendra Wijaya', 'city' => 'Karawang',  'car' => 'Daihatsu Gran Max', 'rating' => 4, 'color' => '#0a5fd1', 'img' => 'https://images.unsplash.com/photo-1600661653561-629509216228?auto=format&fit=crop&w=700&q=80', 'text' => 'Buat usaha, Gran Max andalan saya. Muatan banyak, bandel, irit. Balik modalnya cepat. Recommended untuk pebisnis.'],
        ];

        foreach ($testimonials as $index => $testimonial) {
            $testimonial['sort_order'] = $index;
            Testimonial::create($testimonial);
        }
    }
}
