<?php

namespace Database\Seeders;

use App\Models\QuizOption;
use App\Models\QuizOptionScore;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            [
                'question' => 'Untuk apa mobil ini terutama akan digunakan?',
                'icon' => 'fa-bullseye',
                'options' => [
                    ['text' => 'Harian di kota & ngantor', 'icon' => 'fa-city',        'score' => ['Ayla' => 3, 'Sirion' => 2, 'Rocky' => 1]],
                    ['text' => 'Antar-jemput keluarga',    'icon' => 'fa-people-roof',  'score' => ['Xenia' => 3, 'Sigra' => 2, 'Luxio' => 2]],
                    ['text' => 'Petualangan & jalan jauh', 'icon' => 'fa-mountain-sun', 'score' => ['Terios' => 3, 'Rocky' => 2]],
                    ['text' => 'Usaha / angkut barang',    'icon' => 'fa-truck-fast',   'score' => ['Gran Max' => 3, 'Luxio' => 1]],
                ],
            ],
            [
                'question' => 'Berapa orang yang biasa ikut?',
                'icon' => 'fa-users',
                'options' => [
                    ['text' => '1–2 orang', 'icon' => 'fa-user',          'score' => ['Ayla' => 2, 'Sirion' => 2, 'Gran Max' => 1]],
                    ['text' => '3–5 orang', 'icon' => 'fa-user-group',    'score' => ['Rocky' => 2, 'Sirion' => 1, 'Ayla' => 1]],
                    ['text' => '6–8 orang', 'icon' => 'fa-people-group',  'score' => ['Xenia' => 3, 'Sigra' => 2, 'Luxio' => 3, 'Terios' => 1]],
                ],
            ],
            [
                'question' => 'Apa yang paling kamu utamakan?',
                'icon' => 'fa-heart',
                'options' => [
                    ['text' => 'Irit bahan bakar', 'icon' => 'fa-gas-pump',            'score' => ['Ayla' => 3, 'Sigra' => 2, 'Sirion' => 1]],
                    ['text' => 'Gaya & modern',    'icon' => 'fa-wand-magic-sparkles', 'score' => ['Rocky' => 3, 'Sirion' => 2, 'Terios' => 1]],
                    ['text' => 'Tangguh & lega',   'icon' => 'fa-shield-halved',       'score' => ['Terios' => 3, 'Luxio' => 2, 'Xenia' => 1]],
                    ['text' => 'Harga terjangkau', 'icon' => 'fa-tag',                 'score' => ['Sigra' => 3, 'Ayla' => 2, 'Gran Max' => 2]],
                ],
            ],
            [
                'question' => 'Berapa perkiraan bujet kamu?',
                'icon' => 'fa-wallet',
                'options' => [
                    ['text' => 'Di bawah 180 Juta', 'icon' => 'fa-coins',     'score' => ['Ayla' => 3, 'Sigra' => 3, 'Gran Max' => 2]],
                    ['text' => '180 – 260 Juta',    'icon' => 'fa-money-bill', 'score' => ['Luxio' => 2, 'Sirion' => 2, 'Xenia' => 1]],
                    ['text' => 'Di atas 260 Juta',  'icon' => 'fa-gem',        'score' => ['Terios' => 3, 'Rocky' => 3, 'Xenia' => 2]],
                ],
            ],
        ];

        foreach ($questions as $qIndex => $questionData) {
            $question = QuizQuestion::create([
                'question' => $questionData['question'],
                'icon' => $questionData['icon'],
                'sort_order' => $qIndex,
            ]);

            foreach ($questionData['options'] as $oIndex => $optionData) {
                $option = QuizOption::create([
                    'quiz_question_id' => $question->id,
                    'text' => $optionData['text'],
                    'icon' => $optionData['icon'],
                    'sort_order' => $oIndex,
                ]);

                foreach ($optionData['score'] as $carModel => $points) {
                    QuizOptionScore::create([
                        'quiz_option_id' => $option->id,
                        'car_model' => $carModel,
                        'points' => $points,
                    ]);
                }
            }
        }
    }
}
