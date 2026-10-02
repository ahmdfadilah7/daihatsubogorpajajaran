<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterPhoneLinkTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A configured contact_phone drives the footer tel: href, normalized to
     * a '+62...' international form (spaces/dashes stripped).
     */
    public function test_contact_phone_drives_footer_tel_href(): void
    {
        SiteSetting::updateOrCreate(['key' => 'contact_phone'], ['value' => '+62 21 5000 1234']);
        SiteSetting::flushCache();

        $this->get('/')
            ->assertOk()
            ->assertSee('href="tel:+622150001234"', false)
            ->assertDontSee('tel:+622100000000', false);
    }

    /**
     * With contact_phone empty, the footer tel: href falls back to the
     * normalized contact_whatsapp number (0->62).
     */
    public function test_empty_contact_phone_falls_back_to_whatsapp(): void
    {
        SiteSetting::updateOrCreate(['key' => 'contact_phone'], ['value' => '']);
        SiteSetting::updateOrCreate(['key' => 'contact_whatsapp'], ['value' => '0877-1111-2222']);
        SiteSetting::flushCache();

        $this->get('/')
            ->assertOk()
            ->assertSee('href="tel:+6287711112222"', false);
    }

    /**
     * With both contact_phone and contact_whatsapp empty, the footer tel: href
     * falls back to the historical default number (a working link).
     */
    public function test_both_empty_falls_back_to_default(): void
    {
        SiteSetting::updateOrCreate(['key' => 'contact_phone'], ['value' => '']);
        SiteSetting::updateOrCreate(['key' => 'contact_whatsapp'], ['value' => '']);
        SiteSetting::flushCache();

        $this->get('/')
            ->assertOk()
            ->assertSee('href="tel:+6281234567890"', false);
    }
}
