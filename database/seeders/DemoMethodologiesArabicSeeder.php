<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Question;
use App\Models\QuestionAnswerWeight;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoMethodologiesArabicSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure base answers exist (Yes/No True/False Rating 1..5 etc.).
        $this->seedBaseAnswers();

        /* ------------------------------------------------------------------
         | 1) METHODOLOGY SIMPLE - ARABIC
         |------------------------------------------------------------------*/
        $simple = Methodology::create([
            'name' => 'المنهجية البسيطة',
            'description' => 'منهجية بسيطة مع وحدات مباشرة',
            'definition' => 'تركز هذه المنهجية على المفاهيم الأساسية والعلاقات المباشرة بين الوحدات.',
            'objectives' => 'تهدف هذه المنهجية إلى توفير فهم أساسي للمفاهيم الأساسية وتطوير المهارات الأولية.',
            'type' => 'simple',
            'questions_description' => 'تحتوي هذه المنهجية على أسئلة تقيّم الفهم الأساسي للمفاهيم الأساسية.',
            'questions_estimated_time' => '15 دقيقة',
            'questions_count' => 4,
            'first_section_description' => 'توفر الوحدات الأساسية المعرفة الأساسية والتقييم الأساسي.',
            'second_section_description' => null,
        ]);

        $this->attachQuestions($simple, 4);

        // 3 modules, each with 4 questions
        for ($m = 1; $m <= 3; $m++) {
            $module = Module::factory()->create([
                'name' => "الوحدة $m",
                'definition' => "توفر الوحدة $m المعرفة والمهارات الأساسية للفهم الأساسي.",
                'questions_description' => "تحتوي الوحدة $m على أسئلة تقيّم المفاهيم الأساسية والكفاءات الأساسية.",
                'questions_estimated_time' => '5-7 دقائق',
                'questions_count' => 4,
            ]);
            $simple->modules()->attach($module->id);
            $this->attachQuestions($module, 4);
        }

        /* ------------------------------------------------------------------
         | 2) METHODOLOGY COMPLEX - ARABIC
         |------------------------------------------------------------------*/
        $complex = Methodology::create([
            'name' => 'المنهجية المعقدة',
            'description' => 'منهجية معقدة مع أعمدة متعددة',
            'definition' => 'منهجية شاملة تتضمن أعمدة متعددة وعلاقات معقدة بين الوحدات.',
            'objectives' => 'تهدف هذه المنهجية إلى توفير فهم شامل ومتقدم للمفاهيم المعقدة.',
            'type' => 'complex',
            'questions_description' => 'تحتوي هذه المنهجية على أسئلة شاملة تقيّم الفهم المتقدم عبر أعمدة متعددة.',
            'questions_estimated_time' => '45-60 دقيقة',
            'questions_count' => 4,
            'first_section_description' => 'توفر الأعمدة المتقدمة تحليلاً متعمقاً وتقييماً شاملاً.',
            'second_section_description' => null,
        ]);

        $this->attachQuestions($complex, 4);

        for ($p = 1; $p <= 4; $p++) {
            $pillar = Pillar::factory()->create([
                'name' => "العمود $p",
                'definition' => "يمثل العمود $p مجالاً أساسياً من المعرفة والخبرة.",
                'questions_description' => "يحتوي العمود $p على أسئلة تقيّم الفهم الشامل لهذا المجال.",
                'questions_estimated_time' => '10-15 دقيقة',
                'questions_count' => 4,
            ]);
            // attach pillar to methodology (section irrelevant here)
            $complex->pillars()->attach($pillar->id, ['section' => 'first']);
            $this->attachQuestions($pillar, 4);

            // Each pillar -> 4 modules
            for ($m=1; $m<=4; $m++) {
                $module = Module::factory()->create([
                    'name' => "العمود{$p}-الوحدة{$m}",
                    'definition' => "توفر الوحدة {$m} داخل العمود {$p} معرفة ومهارات متخصصة.",
                    'questions_description' => "تحتوي هذه الوحدة على أسئلة تقيّم كفاءات محددة داخل العمود {$p}.",
                    'questions_estimated_time' => '5-7 دقائق',
                    'questions_count' => 4,
                ]);
                $pillar->modules()->attach($module->id);
                $this->attachQuestions($module, 4);
            }
        }

        /* ------------------------------------------------------------------
         | 3) TWO SECTION METHODOLOGY - ARABIC
         |------------------------------------------------------------------*/
        $twoSection = Methodology::create([
            'name' => 'المنهجية ذات القسمين',
            'description' => 'منهجية منظمة مع قسمين متميزين',
            'definition' => 'منهجية منظمة تتضمن قسمين متميزين مع تبعيات بين الأعمدة.',
            'objectives' => 'تهدف هذه المنهجية إلى توفير فهم منظم ومتدرج للمفاهيم المعقدة.',
            'type' => 'twoSection',
            'first_section_name' => 'القسم الأول',
            'second_section_name' => 'القسم الثاني',
            'questions_description' => 'تحتوي هذه المنهجية على أسئلة منظمة عبر قسمين متميزين مع تبعيات.',
            'questions_estimated_time' => '60-75 دقيقة',
            'questions_count' => 68,
            'first_section_description' => 'يوفر القسم الأول المعرفة الأساسية ويؤسس المفاهيم الأساسية.',
            'second_section_description' => 'يبني القسم الثاني على القسم الأول بمفاهيم متقدمة وتقييمات تابعة.',
        ]);
        $this->attachQuestions($twoSection, 4);

        // Helper arrays to keep references for dependencies
        $section1Pillars = [];
        $section2Pillars = [];

        // Section 1: 2 pillars
        for ($p=1; $p<=2; $p++) {
            $pillar = Pillar::factory()->create([
                'name' => "ق1-العمود $p",
                'definition' => "يوفر العمود $p في القسم الأول المعرفة الأساسية للمنهجية.",
                'questions_description' => "يحتوي هذا العمود على أسئلة تقيّم المفاهيم الأساسية والكفاءات الأساسية.",
                'questions_estimated_time' => '10-15 دقيقة',
                'questions_count' => 4,
            ]);
            $twoSection->pillars()->attach($pillar->id, ['section' => 'first']);
            $this->attachQuestions($pillar, 4);
            $section1Pillars[$p] = $pillar;
            // 4 modules each
            $this->createModulesForPillar($pillar, $p);
        }

        // Section 2: 2 pillars
        for ($p=1; $p<=2; $p++) {
            $pillar = Pillar::factory()->create([
                'name' => "ق2-العمود $p",
                'definition' => "يبني العمود $p في القسم الثاني على المعرفة الأساسية بمفاهيم متقدمة.",
                'questions_description' => "يحتوي هذا العمود على أسئلة تقيّم المفاهيم المتقدمة والكفاءات التابعة.",
                'questions_estimated_time' => '10-15 دقيقة',
                'questions_count' => 4,
            ]);
            $twoSection->pillars()->attach($pillar->id, ['section' => 'second']);
            $this->attachQuestions($pillar, 4);
            $section2Pillars[$p] = $pillar;
            $this->createModulesForPillar($pillar, $p);
        }

        // Dependencies
        // pillar 1 of section 2 depends on pillar 1 of section 1
        $this->addDependency($twoSection, $section2Pillars[1], $section1Pillars[1]);
        // pillar 2 of section 1 depends on pillar 1 of section 1
        $this->addDependency($twoSection, $section1Pillars[2], $section1Pillars[1]);
        // pillar 2 of section 2 depends on pillar 2 of section 1
        $this->addDependency($twoSection, $section2Pillars[2], $section1Pillars[2]);
    }

    /**
     * Seed base answers for questions
     */
    private function seedBaseAnswers(): void
    {
        $answers = [
            'Yes' => 'نعم',
            'No' => 'لا',
            'True' => 'صحيح',
            'False' => 'خطأ',
            'Option A' => 'الخيار أ',
            'Option B' => 'الخيار ب',
            'Option C' => 'الخيار ج',
            'Option D' => 'الخيار د',
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
        ];

        foreach ($answers as $english => $arabic) {
            Answer::firstOrCreate(
                ['title' => $english],
                ['title' => $english]
            );
        }
    }

    /**
     * Attach questions to a context (methodology, pillar, or module)
     */
    private function attachQuestions($contextModel, int $count): void
    {
        $questionTypes = ['YesNo', 'TrueFalse', 'MCQMultiple', 'Rating1to5'];
        $questions = [];

        // Create questions for this context
        for ($i = 1; $i <= $count; $i++) {
            $type = $questionTypes[array_rand($questionTypes)];
            $question = Question::firstOrCreate(
                ['type' => $type],
                [
                    'title' => Str::title(str_replace('_', ' ', $type)) . ' Question',
                ]
            );
            
            $questions[] = $question;
        }

        // Attach all questions to context
        foreach ($questions as $question) {
            // -----------------------------------------------------------------
            // Attach question to context & grab the pivot id for later weighting
            // -----------------------------------------------------------------
            $pivotId   = null;
            $pivotType = null;

            if ($contextModel instanceof Methodology) {
                // Check if already attached to avoid duplicates
                $existingPivot = \DB::table('methodology_question')
                    ->where('methodology_id', $contextModel->id)
                    ->where('question_id', $question->id)
                    ->first();
                
                if (!$existingPivot) {
                    $contextModel->questions()->attach($question->id, ['weight' => 1.0]);
                }
                
                $pivotId = \DB::table('methodology_question')
                    ->where('methodology_id', $contextModel->id)
                    ->where('question_id', $question->id)
                    ->value('id');
                $pivotType = 'methodology_question';
            } elseif ($contextModel instanceof Pillar) {
                // For pillars, we need to find the methodology context
                $methodologyId = $this->findMethodologyForPillar($contextModel);
                
                // Use firstOrCreate to avoid duplicates
                $pivotId = \DB::table('pillar_question')
                    ->where('methodology_id', $methodologyId)
                    ->where('pillar_id', $contextModel->id)
                    ->where('question_id', $question->id)
                    ->value('id');
                
                if (!$pivotId) {
                    \DB::table('pillar_question')->insert([
                        'methodology_id' => $methodologyId,
                        'pillar_id' => $contextModel->id,
                        'question_id' => $question->id,
                        'weight' => 1.0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $pivotId = \DB::table('pillar_question')
                        ->where('methodology_id', $methodologyId)
                        ->where('pillar_id', $contextModel->id)
                        ->where('question_id', $question->id)
                        ->value('id');
                }
                
                $pivotType = 'pillar_question';
            } elseif ($contextModel instanceof Module) {
                // For modules, we need to find the methodology and pillar context
                $context = $this->findMethodologyAndPillarForModule($contextModel);
                
                // Use firstOrCreate to avoid duplicates
                $pivotId = \DB::table('module_question')
                    ->where('methodology_id', $context['methodology_id'])
                    ->where('module_id', $contextModel->id)
                    ->where('question_id', $question->id)
                    ->value('id');
                
                if (!$pivotId) {
                    \DB::table('module_question')->insert([
                        'methodology_id' => $context['methodology_id'],
                        'module_id' => $contextModel->id,
                        'pillar_id' => $context['pillar_id'],
                        'question_id' => $question->id,
                        'weight' => 1.0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $pivotId = \DB::table('module_question')
                        ->where('methodology_id', $context['methodology_id'])
                        ->where('module_id', $contextModel->id)
                        ->where('question_id', $question->id)
                        ->value('id');
                }
                
                $pivotType = 'module_question';
            }

            // Ensure answers are attached to question (only if not already attached)
            $answerIds = $this->attachAnswersByType($question, $question->type->value);

            // -----------------------------------------------------------------
            // Seed default weights (1.00) for each answer in context
            // -----------------------------------------------------------------
            if ($pivotId && $answerIds) {
                foreach ($answerIds as $aId) {
                    QuestionAnswerWeight::firstOrCreate([
                        'context_type' => $pivotType,
                        'context_id'   => $pivotId,
                        'answer_id'    => $aId,
                    ], [
                        'weight' => 1.0,
                    ]);
                }
            }
        }
    }

    /**
     * Attach answers by type and return the IDs attached.
     * Only attaches if not already attached to avoid duplicates.
     */
    private function attachAnswersByType(Question $question, string $type): array
    {
        switch ($type) {
            case 'YesNo':
                $answers = Answer::whereIn('title', ['Yes', 'No'])->pluck('id');
                break;
            case 'TrueFalse':
                $answers = Answer::whereIn('title', ['True', 'False'])->pluck('id');
                break;
            case 'MCQMultiple':
                $answers = Answer::whereIn('title', ['Option A', 'Option B', 'Option C', 'Option D'])->pluck('id');
                break;
            case 'Rating1to5':
                $answers = Answer::whereIn('title', ['1','2','3','4','5'])->pluck('id');
                break;
            default:
                $answers = collect();
        }

        // Only attach if not already attached
        $existingAnswerIds = $question->answers()->pluck('answers.id');
        $newAnswerIds = $answers->diff($existingAnswerIds);
        
        if ($newAnswerIds->isNotEmpty()) {
            $question->answers()->attach($newAnswerIds->toArray());
        }

        return $answers->toArray();
    }

    private function createModulesForPillar(Pillar $pillar, int $index): void
    {
        for ($m=1; $m<=4; $m++) {
            $module = Module::factory()->create([
                'name' => $pillar->name."-الوحدة $m",
                'definition' => "توفر الوحدة {$m} داخل {$pillar->name} معرفة ومهارات متخصصة.",
                'questions_description' => "تحتوي هذه الوحدة على أسئلة تقيّم كفاءات محددة داخل {$pillar->name}.",
                'questions_estimated_time' => '5-7 دقائق',
                'questions_count' => 4,
            ]);
            $pillar->modules()->attach($module->id);
            $this->attachQuestions($module, 4);
        }
    }

    private function addDependency(Methodology $methodology, Pillar $pillar, Pillar $dependsOn): void
    {
        \DB::table('pillar_dependencies')->insert([
            'methodology_id' => $methodology->id,
            'pillar_id' => $pillar->id,
            'depends_on_pillar_id' => $dependsOn->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Find the methodology that contains this pillar
     */
    private function findMethodologyForPillar(Pillar $pillar): int
    {
        $methodologyPillar = \DB::table('methodology_pillar')
            ->where('pillar_id', $pillar->id)
            ->first();
        
        if (!$methodologyPillar) {
            throw new \Exception("Pillar {$pillar->id} is not associated with any methodology");
        }
        
        return $methodologyPillar->methodology_id;
    }

    /**
     * Find the methodology and pillar that contains this module
     * Returns array with 'methodology_id' and 'pillar_id' (null if direct to methodology)
     */
    private function findMethodologyAndPillarForModule(Module $module): array
    {
        // First check if module is directly attached to a methodology
        $methodologyModule = \DB::table('methodology_module')
            ->where('module_id', $module->id)
            ->first();
        
        if ($methodologyModule) {
            return [
                'methodology_id' => $methodologyModule->methodology_id,
                'pillar_id' => null
            ];
        }
        
        // If not, check if module is attached to a pillar
        $pillarModule = \DB::table('pillar_module')
            ->where('module_id', $module->id)
            ->first();
        
        if ($pillarModule) {
            $methodologyPillar = \DB::table('methodology_pillar')
                ->where('pillar_id', $pillarModule->pillar_id)
                ->first();
            
            if ($methodologyPillar) {
                return [
                    'methodology_id' => $methodologyPillar->methodology_id,
                    'pillar_id' => $pillarModule->pillar_id
                ];
            }
        }
        
        throw new \Exception("Module {$module->id} is not associated with any methodology");
    }

    /**
     * Find the methodology that contains this module (for backward compatibility)
     */
    private function findMethodologyForModule(Module $module): int
    {
        $result = $this->findMethodologyAndPillarForModule($module);
        return $result['methodology_id'];
    }
} 