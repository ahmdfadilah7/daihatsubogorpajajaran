<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterSocialMapsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Default-safe: with the seeded sample maps_url and empty social_tiktok,
     * the footer renders the sample map (link + output=embed iframe) and the
     * TikTok icon falls back to '#'.
     */
    public function test_footer_is_default_safe_from_seeded_values(): void
    {
        $this->seed(DatabaseSeeder::class);

        $body = $this->get('/')->getContent();

        // Clickable "Buka di Google Maps" link uses the seeded sample URL.
        $this->assertStringContainsString('href="https://www.google.com/maps?q=Jl.+Jenderal+Sudirman,+Jakarta"', $body);
        // The iframe embed src is derived with output=embed appended. Blade
        // escapes the query '&' to '&amp;' (valid HTML; browser-equivalent).
        $this->assertStringContainsString('src="https://www.google.com/maps?q=Jl.+Jenderal+Sudirman,+Jakarta&amp;output=embed"', $body);
        // Empty social_tiktok => the TikTok anchor falls back to '#'.
        $this->assertStringContainsString('href="#" aria-label="TikTok"', $body);
    }

    /**
     * Edited values flow through to the footer: the TikTok anchor uses the
     * set link, and both the maps link href and the iframe src reflect
     * maps_url (iframe gets output=embed appended).
     */
    public function test_footer_reflects_edited_tiktok_and_maps_url(): void
    {
        $this->seed(DatabaseSeeder::class);

        SiteSetting::updateOrCreate(['key' => 'social_tiktok'], ['value' => 'https://tiktok.com/@daihatsu']);
        SiteSetting::updateOrCreate(['key' => 'maps_url'], ['value' => 'https://www.google.com/maps?q=-6.2,106.8']);
        SiteSetting::flushCache();

        $body = $this->get('/')->getContent();

        $this->assertStringContainsString('href="https://tiktok.com/@daihatsu" aria-label="TikTok"', $body);
        $this->assertStringContainsString('href="https://www.google.com/maps?q=-6.2,106.8"', $body);
        $this->assertStringContainsString('src="https://www.google.com/maps?q=-6.2,106.8&amp;output=embed"', $body);
    }

    /**
     * A non-google maps URL (e.g. a maps.app.goo.gl short link, which Google
     * does not allow inside an iframe) falls back to a q-based embed while the
     * clickable link still points at the admin's URL as-is.
     */
    public function test_short_link_falls_back_to_query_embed(): void
    {
        $this->seed(DatabaseSeeder::class);

        SiteSetting::updateOrCreate(['key' => 'contact_address'], ['value' => 'Jl. Merdeka No. 1, Bandung']);
        SiteSetting::updateOrCreate(['key' => 'maps_url'], ['value' => 'https://maps.app.goo.gl/abc123']);
        SiteSetting::flushCache();

        $body = $this->get('/')->getContent();

        // Link uses the short URL unchanged.
        $this->assertStringContainsString('href="https://maps.app.goo.gl/abc123"', $body);
        // Iframe cannot embed a short link, so it falls back to a q-embed of
        // the contact address (Blade escapes the '&' to '&amp;').
        $this->assertStringContainsString('src="https://www.google.com/maps?q='.urlencode('Jl. Merdeka No. 1, Bandung').'&amp;output=embed"', $body);
    }

    /**
     * Saving social_tiktok + maps_url via the admin settings form persists
     * them through the key-value store.
     */
    public function test_admin_can_save_tiktok_and_maps_url(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Dealer Baru',
                'social_tiktok' => 'https://tiktok.com/@daihatsu',
                'maps_url' => 'https://www.google.com/maps?q=-6.2,106.8',
            ])
            ->assertRedirect(route('admin.settings.edit'));

        SiteSetting::flushCache();

        $this->assertSame('https://tiktok.com/@daihatsu', SiteSetting::get('social_tiktok'));
        $this->assertSame('https://www.google.com/maps?q=-6.2,106.8', SiteSetting::get('maps_url'));
    }

    /**
     * maps_url rejects values beyond the 1000-char limit.
     */
    public function test_maps_url_cannot_exceed_limit(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Dealer Baru',
                'maps_url' => str_repeat('a', 1001),
            ])
            ->assertSessionHasErrors('maps_url');
    }
}
