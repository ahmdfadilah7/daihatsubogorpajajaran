<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoDeadlineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The admin settings form exposes a datetime-local field for the hero
     * promo countdown deadline.
     */
    public function test_settings_form_has_promo_deadline_field(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('name="promo_deadline"', false)
            ->assertSee('type="datetime-local"', false);
    }

    /**
     * With promo_deadline unset/empty, the public bootstrap emits an empty
     * string so main.js falls back to end-of-month (today's behavior).
     */
    public function test_home_emits_empty_promo_deadline_by_default(): void
    {
        SiteSetting::updateOrCreate(['key' => 'promo_deadline'], ['value' => '']);
        SiteSetting::flushCache();

        $this->get('/')
            ->assertOk()
            ->assertSee('App.PROMO_DEADLINE = ""', false);
    }

    /**
     * A configured promo_deadline flows into the bootstrap verbatim so the
     * client-side countdown can target it.
     */
    public function test_home_emits_configured_promo_deadline(): void
    {
        SiteSetting::updateOrCreate(['key' => 'promo_deadline'], ['value' => '2030-12-31T23:59']);
        SiteSetting::flushCache();

        $this->get('/')
            ->assertOk()
            ->assertSee('App.PROMO_DEADLINE = "2030-12-31T23:59"', false);
    }

    /**
     * Saving a valid promo_deadline via the settings form persists it.
     */
    public function test_admin_can_save_promo_deadline(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Dealer Baru',
                'promo_deadline' => '2030-12-31T23:59',
            ])
            ->assertRedirect(route('admin.settings.edit'));

        SiteSetting::flushCache();

        $this->assertSame('2030-12-31T23:59', SiteSetting::get('promo_deadline'));
    }

    /**
     * An invalid promo_deadline value is rejected by validation.
     */
    public function test_promo_deadline_rejects_invalid_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Dealer Baru',
                'promo_deadline' => 'bukan-tanggal',
            ])
            ->assertSessionHasErrors('promo_deadline');
    }
}
