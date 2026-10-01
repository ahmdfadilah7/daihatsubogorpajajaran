<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CategoryStyle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryStyleTest extends TestCase
{
    use RefreshDatabase;

    public function test_referenced_category_cannot_be_deleted(): void
    {
        $user = User::factory()->create();

        $style = CategoryStyle::create(['category' => 'SUV', 'bg' => '#123a8f', 'label' => 'SUV']);
        Car::create([
            'model' => 'Ref', 'type' => 'X', 'category' => 'SUV', 'year' => 2024,
            'price' => 1, 'transmission' => 'CVT', 'fuel' => 'Bensin', 'seats' => 5,
            'accent1' => '#0a5fd1', 'accent2' => '#123a8f', 'img' => 'img/x.jpg',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.category-styles.destroy', $style))
            ->assertSessionHas('gagal');

        $this->assertDatabaseHas('category_styles', ['id' => $style->id]);
    }

    public function test_unreferenced_category_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $style = CategoryStyle::create(['category' => 'LCGC', 'bg' => '#2e86ff', 'label' => 'Hemat']);

        $this->actingAs($user)
            ->delete(route('admin.category-styles.destroy', $style))
            ->assertSessionHas('sukses');

        $this->assertDatabaseMissing('category_styles', ['id' => $style->id]);
    }

    public function test_update_cannot_change_the_category_key(): void
    {
        $user = User::factory()->create();
        $style = CategoryStyle::create(['category' => 'MPV', 'bg' => '#0a5fd1', 'label' => 'Keluarga']);

        // Even if an attacker submits a new category, it must be ignored.
        $this->actingAs($user)->put(route('admin.category-styles.update', $style), [
            'category' => 'HACKED',
            'bg' => '#111111',
            'label' => 'Baru',
        ])->assertRedirect(route('admin.category-styles.index'));

        $fresh = $style->fresh();
        $this->assertSame('MPV', $fresh->category);
        $this->assertSame('#111111', $fresh->bg);
        $this->assertSame('Baru', $fresh->label);
    }
}
