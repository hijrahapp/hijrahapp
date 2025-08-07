<?php

namespace Database\Seeders;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Question;
use App\Models\QuestionAnswerWeight;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoArabicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        $this->clearExistingData();

        // Ensure base answers exist
        $this->seedBaseAnswers();

        // Create questions for each type
        $questions = $this->createQuestions();

        // Create methodologies
        $this->createMethodology1($questions);
        $this->createMethodology2($questions);
        $this->createMethodology3($questions);
    }

    /**
     * Clear existing data from all related tables
     */
    private function clearExistingData(): void
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear pivot tables first (due to foreign key constraints)
        \DB::table('pillar_dependencies')->delete();
        \DB::table('module_question')->delete();
        \DB::table('pillar_question')->delete();
        \DB::table('methodology_question')->delete();
        \DB::table('pillar_module')->delete();
        \DB::table('methodology_module')->delete();
        \DB::table('methodology_pillar')->delete();
        \DB::table('questions_answers')->delete();

        // Clear main tables
        \DB::table('question_answer_weights')->delete();
        \DB::table('user_answers')->delete();
        
        // Clear core tables
        \DB::table('modules')->delete();
        \DB::table('pillars')->delete();
        \DB::table('questions')->delete();
        \DB::table('answers')->delete();
        \DB::table('methodology')->delete();

        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Reset auto-increment counters
        \DB::statement('ALTER TABLE methodology AUTO_INCREMENT = 1');
        \DB::statement('ALTER TABLE pillars AUTO_INCREMENT = 1');
        \DB::statement('ALTER TABLE modules AUTO_INCREMENT = 1');
        \DB::statement('ALTER TABLE questions AUTO_INCREMENT = 1');
        \DB::statement('ALTER TABLE answers AUTO_INCREMENT = 1');

        echo "تم مسح جميع البيانات الموجودة وإعادة تعيين العدادات بنجاح.\n";
    }

    /**
     * Seed base answers for all question types
     */
    private function seedBaseAnswers(): void
    {
        $answers = [
            // Yes/No answers
            'نعم',
            'لا',
            
            // True/False answers
            'صحيح',
            'خطأ',
            
            // Rating scale 1-5
            '1',
            '2',
            '3',
            '4',
            '5',
            
            // Rating scale 1-10
            '6',
            '7',
            '8',
            '9',
            '10',
            
            // Agree/Disagree scale
            'أوافق بشدة',
            'أوافق',
            'محايد',
            'لا أوافق',
            'لا أوافق بشدة',
        ];

        foreach ($answers as $answer) {
            Answer::firstOrCreate(['title' => $answer]);
        }
    }

    /**
     * Create questions for each question type
     */
    private function createQuestions(): array
    {
        $questionTypes = [
            QuestionType::YesNo,
            QuestionType::TrueFalse,
            QuestionType::Rating1to5,
            QuestionType::Rating1to10,
            QuestionType::ScaleAgreeDisagree,
            QuestionType::MCQSingle,
            QuestionType::MCQMultiple,
        ];

        $questions = [];
        $questionTitles = [
            'هل تفهم المفاهيم الأساسية؟',
            'هل هذا البيان صحيح؟',
            'كيف تقيم مستوى معرفتك؟',
            'قيّم مستوى ثقتك من 1 إلى 10',
            'هل توافق على هذا النهج؟',
            'أي خيار يصف تجربتك بشكل أفضل؟',
            'اختر جميع ما ينطبق على وضعك',
        ];

        foreach ($questionTypes as $index => $type) {
            $question = Question::firstOrCreate([
                'title' => $questionTitles[$index],
                'type' => $type,
                'tags' => ['demo', 'assessment', $type->value],
            ]);

            // Attach answers based on question type
            $this->attachAnswersToQuestion($question, $type);
            
            $questions[$type->value] = $question;
        }

        return $questions;
    }

    /**
     * Attach appropriate answers to a question based on its type
     */
    private function attachAnswersToQuestion(Question $question, QuestionType $type): void
    {
        $answers = collect();
        
        switch ($type) {
            case QuestionType::YesNo:
                $answers = Answer::whereIn('title', ['نعم', 'لا'])->get();
                break;
            case QuestionType::TrueFalse:
                $answers = Answer::whereIn('title', ['صحيح', 'خطأ'])->get();
                break;
            case QuestionType::Rating1to5:
                $answers = Answer::whereIn('title', ['1', '2', '3', '4', '5'])->get();
                break;
            case QuestionType::Rating1to10:
                $answers = Answer::whereIn('title', ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'])->get();
                break;
            case QuestionType::ScaleAgreeDisagree:
                $answers = Answer::whereIn('title', [
                    'أوافق بشدة',
                    'أوافق',
                    'محايد',
                    'لا أوافق',
                    'لا أوافق بشدة'
                ])->get();
                break;
            case QuestionType::MCQSingle:
            case QuestionType::MCQMultiple:
                // Create custom answers for MCQ questions
                $customAnswers = [
                    'الخيار أ - فهم أساسي',
                    'الخيار ب - معرفة متوسطة',
                    'الخيار ج - خبرة متقدمة',
                    'الخيار د - مستوى خبير',
                ];
                
                foreach ($customAnswers as $answerTitle) {
                    $answer = Answer::firstOrCreate(['title' => $answerTitle]);
                    $answers->push($answer);
                }
                break;
        }

        $question->answers()->sync($answers->pluck('id'));
    }

    /**
     * Create Methodology 1 (Simple type)
     */
    private function createMethodology1(array $questions): void
    {
        $methodology = Methodology::create([
            'name' => 'منهجية التقييم البسيطة',
            'description' => 'منهجية مباشرة للتقييم والتقييم الأساسي.',
            'definition' => 'توفر هذه المنهجية إطار عمل بسيط لتقييم الكفاءات الأساسية.',
            'objectives' => 'تقييم المعرفة والمهارات الأساسية بطريقة منظمة.',
            'type' => 'simple',
            'first_section_name' => 'الوحدات الأساسية',
            'second_section_name' => null,
            'pillars_definition' => 'غير قابل للتطبيق للمنهجية البسيطة.',
            'modules_definition' => 'توفر الوحدات الأساسية المعرفة الأساسية وقدرات التقييم الأساسية.',
            'questions_description' => 'أسئلة أساسية تغطي المفاهيم والمهارات الأساسية.',
            'questions_estimated_time' => '15-20 دقيقة',
            'questions_count' => 7,
            'first_section_description' => 'توفر الوحدات الأساسية تقييم المعرفة الأساسية.',
            'second_section_description' => null,
            'tags' => ['simple', 'basic', 'assessment'],
        ]);

        // Attach all 7 questions to methodology
        foreach ($questions as $question) {
            $methodology->questions()->attach($question->id, ['weight' => 1]);
        }

        // Create 2 modules
        for ($i = 1; $i <= 2; $i++) {
            $module = Module::create([
                'name' => "الوحدة $i",
                'description' => "توفر الوحدة $i المعرفة والمهارات الأساسية.",
                'definition' => "تغطي الوحدة $i المفاهيم الأساسية والكفاءات الأساسية.",
                'objectives' => "تقييم فهم المفاهيم الأساسية في الوحدة $i.",
                'questions_description' => "تحتوي الوحدة $i على أسئلة تقيم المفاهيم الأساسية.",
                'questions_estimated_time' => '5-7 دقائق',
                'questions_count' => 7,
                'tags' => ['module', 'basic'],
            ]);

            // Attach module to methodology
            $methodology->modules()->attach($module->id);

            // Attach all 7 questions to module
            foreach ($questions as $question) {
                $module->questions()->attach($question->id, [
                    'methodology_id' => $methodology->id,
                    'pillar_id' => null,
                    'weight' => 1
                ]);
            }
        }
    }

    /**
     * Create Methodology 2 (Complex type)
     */
    private function createMethodology2(array $questions): void
    {
        $methodology = Methodology::create([
            'name' => 'منهجية التقييم المعقدة',
            'description' => 'منهجية شاملة للتقييم والتقييم المتقدم.',
            'definition' => 'توفر هذه المنهجية إطار عمل معقد لتقييم الكفاءات المتقدمة.',
            'objectives' => 'تقييم المعرفة والمهارات المتقدمة عبر مجالات متعددة.',
            'type' => 'complex',
            'first_section_name' => 'الأركان المتقدمة',
            'second_section_name' => null,
            'pillars_definition' => 'تمثل الأركان المتقدمة مجالات أساسية للمعرفة والخبرة التي تتطلب تقييم شامل.',
            'modules_definition' => 'توفر الوحدات المتخصصة داخل الأركان تقييم مركز للمكفاءات المحددة.',
            'questions_description' => 'أسئلة متقدمة تغطي المفاهيم والمهارات الشاملة.',
            'questions_estimated_time' => '45-60 دقيقة',
            'questions_count' => 7,
            'first_section_description' => 'توفر الأركان المتقدمة تحليل متعمق وتقييم شامل.',
            'second_section_description' => null,
            'tags' => ['complex', 'advanced', 'assessment'],
        ]);

        // Attach all 7 questions to methodology
        foreach ($questions as $question) {
            $methodology->questions()->attach($question->id, ['weight' => 1]);
        }

        // Create 2 pillars
        for ($i = 1; $i <= 2; $i++) {
            $pillar = Pillar::create([
                'name' => "الركن $i",
                'description' => "يمثل الركن $i مجال أساسي للمعرفة والخبرة.",
                'definition' => "يغطي الركن $i المفاهيم المتقدمة والكفاءات المتخصصة.",
                'objectives' => "تقييم الفهم الشامل لمجال الركن $i.",
                'questions_description' => "يحتوي الركن $i على أسئلة تقيم الفهم المتقدم.",
                'questions_estimated_time' => '10-15 دقيقة',
                'questions_count' => 7,
                'tags' => ['pillar', 'advanced'],
            ]);

            // Attach pillar to methodology
            $methodology->pillars()->attach($pillar->id, ['section' => 'first']);

            // Attach all 7 questions to pillar
            foreach ($questions as $question) {
                $pillar->questions()->attach($question->id, [
                    'methodology_id' => $methodology->id,
                    'weight' => 1
                ]);
            }

            // Create 2 modules for each pillar
            for ($j = 1; $j <= 2; $j++) {
                $module = Module::create([
                    'name' => "الركن{$i}-الوحدة{$j}",
                    'description' => "توفر الوحدة $j داخل الركن $i معرفة متخصصة.",
                    'definition' => "تغطي الوحدة $j كفاءات محددة داخل الركن $i.",
                    'objectives' => "تقييم المهارات المتخصصة في الوحدة $j من الركن $i.",
                    'questions_description' => "تحتوي هذه الوحدة على أسئلة للكفاءات المحددة.",
                    'questions_estimated_time' => '5-7 دقائق',
                    'questions_count' => 7,
                    'tags' => ['module', 'specialized'],
                ]);

                // Attach module to pillar with methodology context
                \DB::table('pillar_module')->insert([
                    'methodology_id' => $methodology->id,
                    'pillar_id' => $pillar->id,
                    'module_id' => $module->id,
                ]);

                // Attach all 7 questions to module
                foreach ($questions as $question) {
                    $module->questions()->attach($question->id, [
                        'methodology_id' => $methodology->id,
                        'pillar_id' => $pillar->id,
                        'weight' => 1
                    ]);
                }
            }
        }
    }

    /**
     * Create Methodology 3 (Two section type with dependencies)
     */
    private function createMethodology3(array $questions): void
    {
        $methodology = Methodology::create([
            'name' => 'منهجية التقييم ذات القسمين',
            'description' => 'منهجية مع قسمين وتبعيات معقدة.',
            'definition' => 'توفر هذه المنهجية نهج منظم مع أقسام مترابطة.',
            'objectives' => 'تقييم المعرفة والمهارات مع مراعاة التبعيات.',
            'type' => 'twoSection',
            'first_section_name' => 'قسم الأساسيات',
            'second_section_name' => 'قسم المتقدم',
            'pillars_definition' => 'تمثل الأركان مجالات معرفة مترابطة حيث تعتمد الأقسام اللاحقة على الأقسام السابقة.',
            'modules_definition' => 'توفر الوحدات داخل الأركان تقييم متخصص مع مراعاة التبعيات.',
            'questions_description' => 'أسئلة منظمة مع تبعيات قائمة على الأقسام.',
            'questions_estimated_time' => '60-90 دقيقة',
            'questions_count' => 7,
            'first_section_description' => 'يوفر القسم الأول تقييم أساسي.',
            'second_section_description' => 'يبني القسم الثاني على نتائج القسم الأول.',
            'tags' => ['two-section', 'dependent', 'assessment'],
        ]);

        // Attach all 7 questions to methodology
        foreach ($questions as $question) {
            $methodology->questions()->attach($question->id, ['weight' => 1]);
        }

        // Create section 1 pillars
        $section1Pillar1 = Pillar::create([
            'name' => 'القسم 1 - الركن 1',
            'description' => 'يوفر الركن الأول من القسم الأول المعرفة الأساسية.',
            'definition' => 'يغطي هذا الركن المفاهيم الأساسية والمهارات الأساسية.',
            'objectives' => 'إرساء الفهم الأساسي.',
            'questions_description' => 'أسئلة أساسية للتقييم الأساسي.',
            'questions_estimated_time' => '10-15 دقيقة',
            'questions_count' => 7,
            'tags' => ['section1', 'foundational'],
        ]);

        $section1Pillar2 = Pillar::create([
            'name' => 'القسم 1 - الركن 2',
            'description' => 'يبني الركن الثاني من القسم الأول على الركن الأول.',
            'definition' => 'يغطي هذا الركن المفاهيم المتوسطة المعتمدة على الركن الأول.',
            'objectives' => 'تقييم المهارات المتوسطة مع تبعية الركن الأول.',
            'questions_description' => 'أسئلة متوسطة مع تبعية الركن الأول.',
            'questions_estimated_time' => '10-15 دقيقة',
            'questions_count' => 7,
            'tags' => ['section1', 'intermediate'],
        ]);

        // Create section 2 pillars
        $section2Pillar1 = Pillar::create([
            'name' => 'القسم 2 - الركن 1',
            'description' => 'يعتمد الركن الأول من القسم الثاني على الركن الأول من القسم الأول.',
            'definition' => 'يغطي هذا الركن المفاهيم المتقدمة المعتمدة على الركن الأول من القسم الأول.',
            'objectives' => 'تقييم المهارات المتقدمة مع تبعية الركن الأول من القسم الأول.',
            'questions_description' => 'أسئلة متقدمة مع تبعية الركن الأول من القسم الأول.',
            'questions_estimated_time' => '10-15 دقيقة',
            'questions_count' => 7,
            'tags' => ['section2', 'advanced'],
        ]);

        $section2Pillar2 = Pillar::create([
            'name' => 'القسم 2 - الركن 2',
            'description' => 'يعتمد الركن الثاني من القسم الثاني على الركن الثاني من القسم الأول.',
            'definition' => 'يغطي هذا الركن المفاهيم المتخصصة المعتمدة على الركن الثاني من القسم الأول.',
            'objectives' => 'تقييم المهارات المتخصصة مع تبعية الركن الثاني من القسم الأول.',
            'questions_description' => 'أسئلة متخصصة مع تبعية الركن الثاني من القسم الأول.',
            'questions_estimated_time' => '10-15 دقيقة',
            'questions_count' => 7,
            'tags' => ['section2', 'specialized'],
        ]);

        // Attach pillars to methodology with sections
        $methodology->pillars()->attach($section1Pillar1->id, ['section' => 'first']);
        $methodology->pillars()->attach($section1Pillar2->id, ['section' => 'first']);
        $methodology->pillars()->attach($section2Pillar1->id, ['section' => 'second']);
        $methodology->pillars()->attach($section2Pillar2->id, ['section' => 'second']);

        // Attach questions to all pillars
        $allPillars = [$section1Pillar1, $section1Pillar2, $section2Pillar1, $section2Pillar2];
        foreach ($allPillars as $pillar) {
            foreach ($questions as $question) {
                $pillar->questions()->attach($question->id, [
                    'methodology_id' => $methodology->id,
                    'weight' => 1
                ]);
            }
        }

        // Create modules for each pillar
        foreach ($allPillars as $pillarIndex => $pillar) {
            for ($j = 1; $j <= 2; $j++) {
                $module = Module::create([
                    'name' => "{$pillar->name}-الوحدة{$j}",
                    'description' => "توفر الوحدة $j داخل {$pillar->name} معرفة متخصصة.",
                    'definition' => "تغطي الوحدة $j كفاءات محددة داخل {$pillar->name}.",
                    'objectives' => "تقييم المهارات المتخصصة في الوحدة $j من {$pillar->name}.",
                    'questions_description' => "تحتوي هذه الوحدة على أسئلة للكفاءات المحددة.",
                    'questions_estimated_time' => '5-7 دقائق',
                    'questions_count' => 7,
                    'tags' => ['module', 'specialized'],
                ]);

                // Attach module to pillar with methodology context
                \DB::table('pillar_module')->insert([
                    'methodology_id' => $methodology->id,
                    'pillar_id' => $pillar->id,
                    'module_id' => $module->id,
                ]);

                // Attach all 7 questions to module
                foreach ($questions as $question) {
                    $module->questions()->attach($question->id, [
                        'methodology_id' => $methodology->id,
                        'pillar_id' => $pillar->id,
                        'weight' => 1
                    ]);
                }
            }
        }

        // Set up dependencies
        // Section 1 Pillar 2 depends on Section 1 Pillar 1
        $this->addDependency($methodology, $section1Pillar2, $section1Pillar1);
        
        // Section 2 Pillar 1 depends on Section 1 Pillar 1
        $this->addDependency($methodology, $section2Pillar1, $section1Pillar1);
        
        // Section 2 Pillar 2 depends on Section 1 Pillar 2
        $this->addDependency($methodology, $section2Pillar2, $section1Pillar2);
    }

    /**
     * Add dependency between pillars
     */
    private function addDependency(Methodology $methodology, Pillar $pillar, Pillar $dependsOn): void
    {
        \DB::table('pillar_dependencies')->insert([
            'methodology_id' => $methodology->id,
            'pillar_id' => $pillar->id,
            'depends_on_pillar_id' => $dependsOn->id,
        ]);
    }
} 