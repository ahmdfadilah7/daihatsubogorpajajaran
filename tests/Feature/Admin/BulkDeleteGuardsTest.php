<?php

namespace Tests\Feature\Admin;

use App\Models\Car;
use App\Models\CategoryStyle;
use App\Models\QuizOption;
use App\Models\QuizOptionScore;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guard behaviour on bulk delete:
 *  - category-styles still referenced by a car are skipped + reported, while
 *    unreferenced ones are deleted (partial success, no 500);
 *  - users exclude the current actor and never remove the last user;
 *  - quiz questions cascade their option/score children.
 */
class BulkDeleteGuardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_style_in_use_is_skipped_unused_is_deleted(): void
    {
        $user = User::factory()->create();

        $used = CategoryStyle::create(['category' => 'SUV', 'bg' => '#123a8f', 'label' => 'SUV']);
        $unused = CategoryStyle::create(['category' => 'LCGC', 'bg' => '#2e86ff', 'label' => 'Hemat']);

        Car::create([
            'model' => 'Ref', 'type' => 'X', 'category' => 'SUV', 'year' => 2024,
            'price' => 1, 'transmission' => 'CVT', 'fuel' => 'Bensin', 'seats' => 5,
            'accent1' => '#0a5fd1', 'accent2' => '#123a8f', 'img' => 'img/x.jpg',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.category-styles.bulk-destroy'), ['ids' => [$used->id, $unused->id]])
            ->assertRedirect(route('admin.category-styles.index'))
            ->assertSessionHas('sukses', '1 data berhasil dihapus.')
            ->assertSessionHas('gagal', '1 kategori dilewati (masih dipakai oleh mobil).');

        $this->assertDatabaseHas('category_styles', ['id' => $used->id]);
        $this->assertDatabaseMissing('category_styles', ['id' => $unused->id]);
    }

    public function test_users_bulk_delete_excludes_actor_and_removes_others(): void
    {
        $actor = User::factory()->create();
        $other1 = User::factory()->create();
        $other2 = User::factory()->create();

        $this->actingAs($actor)
            ->delete(route('admin.users.bulk-destroy'), ['ids' => [$actor->id, $other1->id, $other2->id]])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('sukses', '2 data berhasil dihapus.')
            ->assertSessionHas('gagal', '1 pengguna dilewati (tidak dapat menghapus akun sendiri).');

        $this->assertDatabaseHas('users', ['id' => $actor->id]);
        $this->assertDatabaseMissing('users', ['id' => $other1->id]);
        $this->assertDatabaseMissing('users', ['id' => $other2->id]);
    }

    public function test_users_bulk_delete_selecting_only_actor_deletes_nobody(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->delete(route('admin.users.bulk-destroy'), ['ids' => [$actor->id]])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionMissing('sukses')
            ->assertSessionHas('gagal', '1 pengguna dilewati (tidak dapat menghapus akun sendiri).');

        $this->assertDatabaseHas('users', ['id' => $actor->id]);
        $this->assertSame(1, User::count());
    }

    public function test_quiz_bulk_delete_cascades_children(): void
    {
        $user = User::factory()->create();

        $question = QuizQuestion::create(['question' => 'Q', 'icon' => 'fa-bullseye', 'sort_order' => 0]);
        $option = $question->options()->create(['text' => 'Opt', 'icon' => 'fa-city', 'sort_order' => 0]);
        $option->scores()->create(['car_model' => 'Ayla', 'points' => 2]);

        $this->actingAs($user)
            ->delete(route('admin.quiz-questions.bulk-destroy'), ['ids' => [$question->id]])
            ->assertSessionHas('sukses', '1 data berhasil dihapus.');

        $this->assertDatabaseMissing('quiz_questions', ['id' => $question->id]);
        $this->assertSame(0, QuizOption::count());
        $this->assertSame(0, QuizOptionScore::count());
    }
}
