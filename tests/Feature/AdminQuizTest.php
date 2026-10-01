<?php

namespace Tests\Feature;

use App\Models\QuizOption;
use App\Models\QuizOptionScore;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_store_writes_options_and_scores(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.quiz-questions.store'), [
            'question' => 'Pertanyaan uji?',
            'icon' => 'fa-bullseye',
            'options' => [
                [
                    'text' => 'Pilihan A',
                    'icon' => 'fa-city',
                    'scores' => [
                        ['car_model' => 'Ayla', 'points' => 3],
                        ['car_model' => 'Rocky', 'points' => 1],
                    ],
                ],
            ],
        ])->assertRedirect(route('admin.quiz-questions.index'));

        $this->assertDatabaseHas('quiz_questions', ['question' => 'Pertanyaan uji?']);
        $this->assertSame(1, QuizOption::count());
        $this->assertSame(2, QuizOptionScore::count());
    }

    public function test_bad_child_input_rolls_back_the_whole_question(): void
    {
        $user = User::factory()->create();

        // points out of range (0..100) must fail validation -> nothing persisted.
        $this->actingAs($user)->post(route('admin.quiz-questions.store'), [
            'question' => 'Harus gagal?',
            'icon' => 'fa-bullseye',
            'options' => [
                [
                    'text' => 'Pilihan A',
                    'icon' => 'fa-city',
                    'scores' => [
                        ['car_model' => 'Ayla', 'points' => 999],
                    ],
                ],
            ],
        ])->assertSessionHasErrors();

        $this->assertSame(0, QuizQuestion::count());
        $this->assertSame(0, QuizOption::count());
        $this->assertSame(0, QuizOptionScore::count());
    }

    public function test_quiz_update_replaces_children_atomically(): void
    {
        $user = User::factory()->create();

        $question = QuizQuestion::create(['question' => 'Asli', 'icon' => 'fa-bullseye', 'sort_order' => 0]);
        $option = $question->options()->create(['text' => 'Lama', 'icon' => 'fa-city', 'sort_order' => 0]);
        $option->scores()->create(['car_model' => 'Ayla', 'points' => 2]);

        $this->actingAs($user)->put(route('admin.quiz-questions.update', $question), [
            'question' => 'Diperbarui',
            'icon' => 'fa-wallet',
            'options' => [
                ['text' => 'Baru', 'icon' => 'fa-gem', 'scores' => [
                    ['car_model' => 'Terios', 'points' => 5],
                ]],
            ],
        ])->assertRedirect(route('admin.quiz-questions.index'));

        $this->assertDatabaseHas('quiz_questions', ['id' => $question->id, 'question' => 'Diperbarui']);
        $this->assertSame(1, QuizOption::count());
        $this->assertDatabaseHas('quiz_options', ['text' => 'Baru']);
        $this->assertDatabaseMissing('quiz_options', ['text' => 'Lama']);
        $this->assertDatabaseHas('quiz_option_scores', ['car_model' => 'Terios', 'points' => 5]);
    }
}
