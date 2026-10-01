<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CategoryStyleSeeder::class,
            CarSeeder::class,
            QuizSeeder::class,
            WheelPrizeSeeder::class,
            CornerImageSeeder::class,
            HeroSlideSeeder::class,
            TestimonialSeeder::class,
            SiteSettingSeeder::class,
            MarqueeItemSeeder::class,
        ]);
    }
}
