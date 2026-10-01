<?php

namespace Tests\Feature\Admin;

use App\Models\MarqueeItem;
use App\Models\User;
use Database\Seeders\MarqueeItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMarqueeItemTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'text' => 'Teks Uji',
            'icon' => 'fa-star',
            'color' => '#0a5fd1',
            'sort_order' => 0,
        ], $overrides);
    }

    public function test_index_lists_items_for_authed_user(): void
    {
        $user = User::factory()->create();
        MarqueeItem::create($this->payload(['text' => 'Daftar Uji']));

        $this->actingAs($user)
            ->get(route('admin.marquee-items.index'))
            ->assertStatus(200)
            ->assertSee('Daftar Uji');
    }

    public function test_store_persists_and_redirects_with_flash(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.marquee-items.store'), $this->payload())
            ->assertRedirect(route('admin.marquee-items.index'))
            ->assertSessionHas('sukses', 'Teks berjalan berhasil ditambahkan.');

        $this->assertDatabaseHas('marquee_items', ['text' => 'Teks Uji', 'icon' => 'fa-star', 'color' => '#0a5fd1']);
    }

    public function test_store_requires_text(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.marquee-items.create'))
            ->post(route('admin.marquee-items.store'), $this->payload(['text' => '']))
            ->assertSessionHasErrors('text');
    }

    public function test_store_rejects_invalid_color(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.marquee-items.create'))
            ->post(route('admin.marquee-items.store'), $this->payload(['color' => 'notahex']))
            ->assertSessionHasErrors('color');
    }

    public function test_update_changes_item(): void
    {
        $user = User::factory()->create();
        $item = MarqueeItem::create($this->payload());

        $this->actingAs($user)
            ->put(route('admin.marquee-items.update', $item), $this->payload(['text' => 'Diperbarui']))
            ->assertRedirect(route('admin.marquee-items.index'))
            ->assertSessionHas('sukses', 'Teks berjalan berhasil diperbarui.');

        $this->assertDatabaseHas('marquee_items', ['id' => $item->id, 'text' => 'Diperbarui']);
    }

    public function test_destroy_removes_item(): void
    {
        $user = User::factory()->create();
        $item = MarqueeItem::create($this->payload());

        $this->actingAs($user)
            ->delete(route('admin.marquee-items.destroy', $item))
            ->assertRedirect(route('admin.marquee-items.index'))
            ->assertSessionHas('sukses', 'Teks berjalan berhasil dihapus.');

        $this->assertDatabaseMissing('marquee_items', ['id' => $item->id]);
    }

    public function test_bulk_destroy_removes_selected_items(): void
    {
        $user = User::factory()->create();

        $a = MarqueeItem::create($this->payload(['text' => 'A', 'sort_order' => 0]));
        $b = MarqueeItem::create($this->payload(['text' => 'B', 'sort_order' => 1]));
        $c = MarqueeItem::create($this->payload(['text' => 'C', 'sort_order' => 2]));

        $this->actingAs($user)
            ->delete(route('admin.marquee-items.bulk-destroy'), ['ids' => [$a->id, $b->id]])
            ->assertRedirect(route('admin.marquee-items.index'))
            ->assertSessionHas('sukses', '2 data berhasil dihapus.');

        $this->assertDatabaseMissing('marquee_items', ['id' => $a->id]);
        $this->assertDatabaseMissing('marquee_items', ['id' => $b->id]);
        $this->assertDatabaseHas('marquee_items', ['id' => $c->id]);
    }

    public function test_seeder_is_idempotent_and_reproduces_six_pills(): void
    {
        (new MarqueeItemSeeder)->run();
        (new MarqueeItemSeeder)->run();

        $this->assertSame(6, MarqueeItem::count());
        $this->assertDatabaseHas('marquee_items', ['text' => 'Irit BBM', 'icon' => 'fa-gas-pump', 'color' => '#0a5fd1', 'sort_order' => 0]);
        $this->assertDatabaseHas('marquee_items', ['text' => 'Dealer Resmi', 'icon' => 'fa-award', 'color' => '#4aa3ff', 'sort_order' => 5]);
    }

    public function test_public_home_renders_each_pill_twice(): void
    {
        (new MarqueeItemSeeder)->run();

        $body = $this->get('/')->getContent();

        // Two identical groups -> each icon/text appears twice.
        $this->assertSame(2, substr_count($body, 'fa-gas-pump'));
        $this->assertStringContainsString('Irit BBM', $body);
        $this->assertStringContainsString('Dealer Resmi', $body);
    }
}
