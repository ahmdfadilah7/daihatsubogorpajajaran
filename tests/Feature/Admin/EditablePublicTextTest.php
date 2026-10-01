<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditablePublicTextTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An authed admin can persist a public-text key, and the public page
     * reflects the new value.
     */
    public function test_admin_can_persist_a_text_key_and_public_page_reflects_it(): void
    {
        $admin = User::factory()->create();

        $sentinel = 'UJI HERO TITLE '.uniqid();

        $response = $this->actingAs($admin)
            ->put(route('admin.settings.update'), ['hero_title' => $sentinel]);

        $response->assertRedirect(route('admin.settings.edit'));

        // Stored value matches the sentinel (cache flushed by the controller).
        $this->assertSame($sentinel, SiteSetting::get('hero_title'));

        // Public page renders the new value (assertSee with no HTML escaping
        // so glyphs/entities match the rendered output).
        $this->get('/')->assertOk()->assertSee($sentinel, false);
    }

    /**
     * An empty stored value falls back to the inline default literal, so the
     * public page shows today's copy ("reset to default").
     */
    public function test_empty_value_falls_back_to_default_on_public_page(): void
    {
        $admin = User::factory()->create();

        // Store hero_title as an empty string via the update endpoint.
        $this->actingAs($admin)
            ->put(route('admin.settings.update'), ['hero_title' => '']);

        $this->assertSame('', SiteSetting::get('hero_title', ''));

        // Falls back to the verbatim literal in home.blade.php.
        $this->get('/')->assertOk()->assertSee('Mobil Keluarga', false);
    }

    /**
     * The hero WhatsApp href renders the message URL-encoded (rawurlencode),
     * i.e. the comma becomes %2C — the accepted canonical form.
     */
    public function test_hero_whatsapp_href_renders_encoded_message(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(
                'https://wa.me/6281234567890?text=Halo%2C%20saya%20mau%20test%20drive%20mobil%20Daihatsu',
                false
            );
    }
}
