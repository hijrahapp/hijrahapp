<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Answer;

class AnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $answers = [
            // Yes/No answers
            'Yes',
            'No',
            
            // True/False answers
            'True',
            'False',
            
            // Rating scale 1-10
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            
            // Agree/Disagree scale
            'Strongly Agree',
            'Agree',
            'Neutral',
            'Disagree',
            'Strongly Disagree',
        ];

        foreach ($answers as $answer) {
            Answer::create([
                'title' => $answer,
            ]);
        }
    }
} 