<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

class InterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $interests = [
            [
                'name' => 'التكنولوجيا والبرمجة',
                'img_url' => 'https://example.com/images/technology.jpg',
            ],
            [
                'name' => 'الرياضة واللياقة البدنية',
                'img_url' => 'https://example.com/images/sports.jpg',
            ],
            [
                'name' => 'الطبخ والطعام',
                'img_url' => 'https://example.com/images/cooking.jpg',
            ],
            [
                'name' => 'السفر والسياحة',
                'img_url' => 'https://example.com/images/travel.jpg',
            ],
            [
                'name' => 'القراءة والكتب',
                'img_url' => 'https://example.com/images/reading.jpg',
            ],
            [
                'name' => 'الموسيقى والفنون',
                'img_url' => 'https://example.com/images/music.jpg',
            ],
            [
                'name' => 'الصحة والطب',
                'img_url' => 'https://example.com/images/health.jpg',
            ],
            [
                'name' => 'التعليم والتطوير الذاتي',
                'img_url' => 'https://example.com/images/education.jpg',
            ],
            [
                'name' => 'الأعمال وريادة الأعمال',
                'img_url' => 'https://example.com/images/business.jpg',
            ],
            [
                'name' => 'البيئة والطبيعة',
                'img_url' => 'https://example.com/images/nature.jpg',
            ],
            [
                'name' => 'التصوير والتصميم',
                'img_url' => 'https://example.com/images/photography.jpg',
            ],
            [
                'name' => 'الألعاب والترفيه',
                'img_url' => 'https://example.com/images/gaming.jpg',
            ],
        ];

        foreach ($interests as $interest) {
            Interest::create($interest);
        }
    }
}
