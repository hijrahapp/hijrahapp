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

class DemoMethodologiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure base answers exist (Yes/No True/False Rating 1..5 etc.).
        $this->seedBaseAnswers();

        /* ------------------------------------------------------------------
         | 1) SIMPLE METHODOLOGY
         |------------------------------------------------------------------*/
        $simple = Methodology::create([
            'name' => 'Methodology Simple',
            'description' => fake()->paragraph(),
            'definition' => fake()->sentence(),
            'objectives' => fake()->paragraphs(3, true),
            'img_url' => fake()->imageUrl(),
            'type' => 'simple',
            'questions_description' => 'This methodology contains questions that assess basic understanding of core concepts.',
            'questions_estimated_time' => '15-20 minutes',
            'questions_count' => 0, // Will be updated after questions are attached
            'first_section_description' => 'Core modules provide foundational knowledge and basic assessment.',
            'first_section_definition' => 'Definition for simple methodology core section.',
            'first_section_objectives' => 'Objectives for simple methodology core section.',
            'first_section_img_url' => fake()->imageUrl(),
            'second_section_description' => null,
            'second_section_definition' => null,
            'second_section_objectives' => null,
            'second_section_img_url' => null,
        ]);

        $this->attachQuestions($simple, 4);
        $simple->update(['questions_count' => $simple->questions()->count()]);

        // 3 modules, each with 4 questions
        for ($m = 1; $m <= 3; $m++) {
            $module = Module::factory()->create([
                'name' => "Module $m",
                'definition' => "Module $m provides essential knowledge and skills for basic understanding.",
                'img_url' => fake()->imageUrl(),
                'questions_description' => "Module $m contains questions that assess fundamental concepts and basic competencies.",
                'questions_estimated_time' => '5-7 minutes',
                'questions_count' => 4,
            ]);
            $simple->modules()->attach($module->id);
            $this->attachQuestions($module, 4);
        }

        /* ------------------------------------------------------------------
         | 2) COMPLEX METHODOLOGY
         |------------------------------------------------------------------*/
        $complex = Methodology::create([
            'name' => 'Methodology Complex',
            'description' => fake()->paragraph(),
            'definition' => fake()->sentence(),
            'objectives' => fake()->paragraphs(3, true),
            'img_url' => fake()->imageUrl(),
            'type' => 'complex',
            'questions_description' => 'This methodology contains comprehensive questions that assess advanced understanding across multiple pillars.',
            'questions_estimated_time' => '45-60 minutes',
            'questions_count' => 0, // Will be updated after questions are attached
            'first_section_description' => 'Advanced pillars provide in-depth analysis and comprehensive assessment.',
            'first_section_definition' => 'Definition for complex methodology section.',
            'first_section_objectives' => 'Objectives for complex methodology section.',
            'first_section_img_url' => fake()->imageUrl(),
            'second_section_description' => null,
            'second_section_definition' => null,
            'second_section_objectives' => null,
            'second_section_img_url' => null,
        ]);

        $this->attachQuestions($complex, 4);
        $complex->update(['questions_count' => $complex->questions()->count()]);

        for ($p = 1; $p <= 4; $p++) {
            $pillar = Pillar::factory()->create([
                'name' => "Pillar $p",
                'definition' => "Pillar $p represents a core area of knowledge and expertise.",
                'img_url' => fake()->imageUrl(),
                'questions_description' => "Pillar $p contains questions that assess comprehensive understanding of this domain.",
                'questions_estimated_time' => '10-15 minutes',
                'questions_count' => 0, // Will be updated after questions are attached
            ]);
            // attach pillar to methodology (section irrelevant here)
            $complex->pillars()->attach($pillar->id, ['section' => 'first']);
            $this->attachQuestions($pillar, 4);
            $pillar->update(['questions_count' => $pillar->questions()->count()]);

            // Each pillar -> 4 modules
            for ($m=1; $m<=4; $m++) {
                $module = Module::factory()->create([
                    'name' => "Pillar{$p}-Module{$m}",
                    'definition' => "Module {$m} within Pillar {$p} provides specialized knowledge and skills.",
                    'img_url' => fake()->imageUrl(),
                    'questions_description' => "This module contains questions that assess specific competencies within Pillar {$p}.",
                    'questions_estimated_time' => '5-7 minutes',
                    'questions_count' => 0, // Will be updated after questions are attached
                ]);
                
                // Attach module to pillar with methodology context
                \DB::table('pillar_module')->insert([
                    'methodology_id' => $complex->id,
                    'pillar_id' => $pillar->id,
                    'module_id' => $module->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->attachQuestions($module, 4);
                $module->update(['questions_count' => $module->questions()->count()]);
            }
        }

        /* ------------------------------------------------------------------
         | 3) TWO SECTION METHODOLOGY
         |------------------------------------------------------------------*/
        $twoSection = Methodology::create([
            'name' => 'Methodology Two Section',
            'description' => fake()->paragraph(),
            'definition' => fake()->sentence(),
            'objectives' => fake()->paragraphs(3, true),
            'img_url' => fake()->imageUrl(),
            'type' => 'twoSection',
            'first_section_name' => 'Section 1',
            'second_section_name' => 'Section 2',
            'questions_description' => 'This methodology contains structured questions across two distinct sections with dependencies.',
            'questions_estimated_time' => '60-75 minutes',
            'questions_count' => 0, // Will be updated after questions are attached
            'first_section_description' => 'Section 1 provides foundational knowledge and establishes core concepts.',
            'first_section_definition' => 'Definition for section 1.',
            'first_section_objectives' => 'Objectives for section 1.',
            'first_section_img_url' => fake()->imageUrl(),
            'second_section_description' => 'Section 2 builds upon Section 1 with advanced concepts and dependent assessments.',
            'second_section_definition' => 'Definition for section 2.',
            'second_section_objectives' => 'Objectives for section 2.',
            'second_section_img_url' => fake()->imageUrl(),
        ]);
        $this->attachQuestions($twoSection, 4);
        $twoSection->update(['questions_count' => $twoSection->questions()->count()]);

        // Helper arrays to keep references for dependencies
        $section1Pillars = [];
        $section2Pillars = [];

        // Section 1: 2 pillars
        for ($p=1; $p<=2; $p++) {
            $pillar = Pillar::factory()->create([
                'name' => "S1-Pillar $p",
                'definition' => "Section 1 Pillar $p provides foundational knowledge for the methodology.",
                'img_url' => fake()->imageUrl(),
                'questions_description' => "This pillar contains questions that assess foundational concepts and basic competencies.",
                'questions_estimated_time' => '10-15 minutes',
                'questions_count' => 0, // Will be updated after questions are attached
            ]);
            $twoSection->pillars()->attach($pillar->id, ['section' => 'first']);
            $this->attachQuestions($pillar, 4);
            $pillar->update(['questions_count' => $pillar->questions()->count()]);
            $section1Pillars[$p] = $pillar;
            // 4 modules each
            $this->createModulesForPillar($pillar, $p);
        }

        // Section 2: 2 pillars
        for ($p=1; $p<=2; $p++) {
            $pillar = Pillar::factory()->create([
                'name' => "S2-Pillar $p",
                'definition' => "Section 2 Pillar $p builds upon foundational knowledge with advanced concepts.",
                'img_url' => fake()->imageUrl(),
                'questions_description' => "This pillar contains questions that assess advanced concepts and dependent competencies.",
                'questions_estimated_time' => '10-15 minutes',
                'questions_count' => 0, // Will be updated after questions are attached
            ]);
            $twoSection->pillars()->attach($pillar->id, ['section' => 'second']);
            $this->attachQuestions($pillar, 4);
            $pillar->update(['questions_count' => $pillar->questions()->count()]);
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

    /* ----------------------------------------------------------------------
     | Helpers
     |---------------------------------------------------------------------*/

    private function seedBaseAnswers(): void
    {
        $base = [
            'Yes', 'No', 'True', 'False', '1', '2', '3', '4', '5',
            'Option A', 'Option B', 'Option C', 'Option D',
        ];
        foreach ($base as $title) {
            Answer::firstOrCreate(['title' => $title]);
        }
    }

    private function attachQuestions($contextModel, int $count): void
    {
        // The order of types per requirement
        $types = ['YesNo', 'TrueFalse', 'MCQMultiple', 'Rating1to5'];
        
        // Get or create questions by type to reuse them
        $questions = [];
        for ($i = 0; $i < $count; $i++) {
            $type = $types[$i % count($types)];
            
            // Get existing question of this type or create new one
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
                $contextModel->questions()->attach($question->id, ['weight' => 1.0]);
                $pivotId   = \DB::table('methodology_question')
                    ->where('methodology_id', $contextModel->id)
                    ->where('question_id', $question->id)
                    ->value('id');
                $pivotType = 'methodology_question';
            } elseif ($contextModel instanceof Pillar) {
                // For pillars, we need to find the methodology context
                $methodologyId = $this->findMethodologyForPillar($contextModel);
                \DB::table('pillar_question')->insert([
                    'methodology_id' => $methodologyId,
                    'pillar_id' => $contextModel->id,
                    'question_id' => $question->id,
                    'weight' => 1.0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $pivotId   = \DB::table('pillar_question')
                    ->where('methodology_id', $methodologyId)
                    ->where('pillar_id', $contextModel->id)
                    ->where('question_id', $question->id)
                    ->value('id');
                $pivotType = 'pillar_question';
            } elseif ($contextModel instanceof Module) {
                // For modules, we need to find the methodology and pillar context
                $context = $this->findMethodologyAndPillarForModule($contextModel);
                \DB::table('module_question')->insert([
                    'methodology_id' => $context['methodology_id'],
                    'module_id' => $contextModel->id,
                    'pillar_id' => $context['pillar_id'],
                    'question_id' => $question->id,
                    'weight' => 1.0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $pivotId   = \DB::table('module_question')
                    ->where('methodology_id', $context['methodology_id'])
                    ->where('module_id', $contextModel->id)
                    ->where('question_id', $question->id)
                    ->value('id');
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
        
        if ($answers->isNotEmpty()) {
            // Only attach if not already attached
            $existingAnswerIds = $question->answers()->pluck('answers.id');
            $newAnswerIds = $answers->diff($existingAnswerIds);
            
            if ($newAnswerIds->isNotEmpty()) {
                $question->answers()->attach($newAnswerIds);
            }
        }

        // Return array of answer IDs for further processing
        return $answers->toArray();
    }

    private function createModulesForPillar(Pillar $pillar, int $index): void
    {
        // Find the methodology that contains this pillar
        $methodologyId = $this->findMethodologyForPillar($pillar);
        
        for ($m=1; $m<=4; $m++) {
            $module = Module::factory()->create([
                'name' => $pillar->name."-Module $m",
                'definition' => "Module {$m} within {$pillar->name} provides specialized knowledge and skills.",
                'questions_description' => "This module contains questions that assess specific competencies within {$pillar->name}.",
                'questions_estimated_time' => '5-7 minutes',
                'questions_count' => 4,
            ]);
            
            // Attach module to pillar with methodology context
            \DB::table('pillar_module')->insert([
                'methodology_id' => $methodologyId,
                'pillar_id' => $pillar->id,
                'module_id' => $module->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
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
        
        // If not, check if module is attached to a pillar (now with methodology_id)
        $pillarModule = \DB::table('pillar_module')
            ->where('module_id', $module->id)
            ->first();
        
        if ($pillarModule) {
            return [
                'methodology_id' => $pillarModule->methodology_id,
                'pillar_id' => $pillarModule->pillar_id
            ];
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