<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Seed the default site settings. Idempotent via updateOrCreate per key;
     * defaults match the CURRENT public site so nothing changes until edited.
     */
    public function run(): void
    {
        $defaults = [
            'site_name' => 'Daihatsu Sahabat',
            'tagline' => '',
            'logo' => '',
            'favicon' => '',
            'meta_title' => 'Daihatsu Sahabat | Dealer Resmi Daihatsu',
            'meta_description' => 'Daihatsu Sahabat - Dealer resmi Daihatsu. Temukan Ayla, Sigra, Terios, Rocky, Xenia & lainnya dengan promo, cicilan ringan, dan servis terpercaya.',
            'meta_keywords' => 'Daihatsu, dealer Daihatsu, Ayla, Sigra, Terios, Rocky, Xenia, promo mobil, kredit mobil',
            'meta_author' => 'Daihatsu Sahabat',
            'og_image' => '',
            'og_title' => '',
            'og_description' => '',
            'contact_whatsapp' => '',
            'contact_email' => '',
            'contact_address' => '',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_youtube' => '',
        ];

        foreach ($defaults as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        SiteSetting::flushCache();
    }
}
