<?php

namespace Database\Seeders;

use App\Models\FeedbackForm;
use Illuminate\Database\Seeder;

class FeedbackFormsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Arabic feedback form based on the mobile UI image
        FeedbackForm::create([
            'version' => '1.0',
            'name' => 'تقييم البرنامج',
            'language' => 'ar',
            'description' => 'نموذج تقييم البرنامج بعد الانتهاء من المحتوى',
            'form_structure' => [
                'title' => 'شاركنا رأيك',
                'questions' => [
                    [
                        'key' => 'overall_rating',
                        'type' => 'rating',
                        'text' => 'ما مدى رضاك عن البرنامج بشكل عام؟',
                        'required' => true,
                        'min_value' => 1,
                        'max_value' => 5,
                        'display_type' => 'stars',
                    ],
                    [
                        'key' => 'life_improvement',
                        'type' => 'single_choice',
                        'text' => 'هل ساعدك البرنامج في تحسين جانب من حياتك؟',
                        'required' => true,
                        'options' => [
                            ['label' => 'نعم', 'value' => 'yes'],
                            ['label' => 'إلى حد ما', 'value' => 'somewhat'],
                            ['label' => 'لا', 'value' => 'no'],
                        ],
                    ],
                    [
                        'key' => 'content_clarity',
                        'type' => 'single_choice',
                        'text' => 'كيف تقيم وضوح وسهولة المحتوى؟',
                        'required' => true,
                        'options' => [
                            ['label' => 'ممتاز', 'value' => 'excellent'],
                            ['label' => 'جيد', 'value' => 'good'],
                            ['label' => 'يحتاج تحسين', 'value' => 'needs_improvement'],
                        ],
                    ],
                    [
                        'key' => 'most_beneficial_content',
                        'type' => 'multiple_choice',
                        'text' => 'أي نوع من المحتوى كان الأكثر فائدة لك؟',
                        'required' => true,
                        'options' => [
                            ['label' => 'المقالات', 'value' => 'articles'],
                            ['label' => 'الفيديوهات', 'value' => 'videos'],
                            ['label' => 'الكتيبات', 'value' => 'ebooks'],
                            ['label' => 'التحدي', 'value' => 'challenges'],
                            ['label' => 'المعاملات اليومية', 'value' => 'daily_practices'],
                            ['label' => 'الأختبارات السريعة', 'value' => 'quick_tasks'],
                            ['label' => 'المهام اليومية', 'value' => 'daily_tasks'],
                            ['label' => 'الملفات الصوتية', 'value' => 'audio_files'],
                        ],
                    ],
                    [
                        'key' => 'improvement_suggestions',
                        'type' => 'text',
                        'text' => 'شاركنا اقتراحك أو ملاحظتك لتطوير البرامج القادمة',
                        'required' => false,
                        'placeholder' => 'اكتب اقتراحك هنا...',
                        'max_length' => 1000,
                        'multiline' => true,
                    ],
                ],
            ],
        ]);

        // English version of the same form
        FeedbackForm::create([
            'version' => '1.0',
            'name' => 'Program Feedback',
            'language' => 'en',
            'description' => 'Program evaluation form after content completion',
            'form_structure' => [
                'title' => 'Share Your Opinion',
                'questions' => [
                    [
                        'key' => 'overall_rating',
                        'type' => 'rating',
                        'text' => 'How satisfied are you with the program overall?',
                        'required' => true,
                        'min_value' => 1,
                        'max_value' => 5,
                        'display_type' => 'stars',
                    ],
                    [
                        'key' => 'life_improvement',
                        'type' => 'single_choice',
                        'text' => 'Did the program help you improve an aspect of your life?',
                        'required' => true,
                        'options' => [
                            ['label' => 'Yes', 'value' => 'yes'],
                            ['label' => 'Somewhat', 'value' => 'somewhat'],
                            ['label' => 'No', 'value' => 'no'],
                        ],
                    ],
                    [
                        'key' => 'content_clarity',
                        'type' => 'single_choice',
                        'text' => 'How do you rate the clarity and ease of the content?',
                        'required' => true,
                        'options' => [
                            ['label' => 'Excellent', 'value' => 'excellent'],
                            ['label' => 'Good', 'value' => 'good'],
                            ['label' => 'Needs Improvement', 'value' => 'needs_improvement'],
                        ],
                    ],
                    [
                        'key' => 'most_beneficial_content',
                        'type' => 'multiple_choice',
                        'text' => 'Which type of content was most beneficial to you?',
                        'required' => true,
                        'options' => [
                            ['label' => 'Articles', 'value' => 'articles'],
                            ['label' => 'Videos', 'value' => 'videos'],
                            ['label' => 'E-books', 'value' => 'ebooks'],
                            ['label' => 'Challenges', 'value' => 'challenges'],
                            ['label' => 'Daily Practices', 'value' => 'daily_practices'],
                            ['label' => 'Quick Tasks', 'value' => 'quick_tasks'],
                            ['label' => 'Daily Tasks', 'value' => 'daily_tasks'],
                            ['label' => 'Audio Files', 'value' => 'audio_files'],
                        ],
                    ],
                    [
                        'key' => 'improvement_suggestions',
                        'type' => 'text',
                        'text' => 'Share your suggestions or feedback for improving future programs',
                        'required' => false,
                        'placeholder' => 'Write your suggestion here...',
                        'max_length' => 1000,
                        'multiline' => true,
                    ],
                ],
            ],
        ]);
    }
}
