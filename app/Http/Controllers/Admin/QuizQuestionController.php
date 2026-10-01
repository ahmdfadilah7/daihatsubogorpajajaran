<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuizQuestionRequest;
use App\Models\Car;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\DB;

class QuizQuestionController extends Controller
{
    public function index()
    {
        $questions = QuizQuestion::withCount('options')
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.quiz-questions.index', compact('questions'));
    }

    public function create()
    {
        $question = new QuizQuestion;
        $question->setRelation('options', collect());

        return view('admin.quiz-questions.create', [
            'question' => $question,
            'carModels' => $this->carModels(),
        ]);
    }

    public function store(QuizQuestionRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $question = QuizQuestion::create([
                'question' => $data['question'],
                'icon' => $data['icon'],
                'sort_order' => QuizQuestion::max('sort_order') + 1,
            ]);

            $this->syncOptions($question, $data['options']);
        });

        return redirect()->route('admin.quiz-questions.index')
            ->with('sukses', 'Pertanyaan kuis berhasil ditambahkan.');
    }

    public function edit(QuizQuestion $quizQuestion)
    {
        $quizQuestion->load('options.scores');

        return view('admin.quiz-questions.edit', [
            'question' => $quizQuestion,
            'carModels' => $this->carModels(),
        ]);
    }

    public function update(QuizQuestionRequest $request, QuizQuestion $quizQuestion)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $quizQuestion) {
            $quizQuestion->update([
                'question' => $data['question'],
                'icon' => $data['icon'],
            ]);

            // Replace children atomically. Cascade delete removes scores.
            $quizQuestion->options()->delete();
            $this->syncOptions($quizQuestion, $data['options']);
        });

        return redirect()->route('admin.quiz-questions.index')
            ->with('sukses', 'Pertanyaan kuis berhasil diperbarui.');
    }

    public function destroy(QuizQuestion $quizQuestion)
    {
        $quizQuestion->delete();

        return redirect()->route('admin.quiz-questions.index')
            ->with('sukses', 'Pertanyaan kuis berhasil dihapus.');
    }

    /**
     * Create option + score child rows for a question. Runs inside the
     * caller's DB::transaction so a bad child rolls the whole thing back.
     *
     * @param  array<int, array<string, mixed>>  $options
     */
    protected function syncOptions(QuizQuestion $question, array $options): void
    {
        foreach (array_values($options) as $i => $opt) {
            $option = $question->options()->create([
                'text' => $opt['text'],
                'icon' => $opt['icon'],
                'sort_order' => $i,
            ]);

            foreach ($opt['scores'] ?? [] as $score) {
                // Skip fully empty rows (validator allows nullable scores).
                if (! isset($score['car_model'], $score['points'])
                    || $score['car_model'] === '' || $score['points'] === '') {
                    continue;
                }

                $option->scores()->create([
                    'car_model' => $score['car_model'],
                    'points' => $score['points'],
                ]);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    protected function carModels(): array
    {
        return Car::query()->distinct()->orderBy('model')->pluck('model')->all();
    }
}
