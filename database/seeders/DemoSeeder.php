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

class DemoSeeder extends Seeder
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
        // Disable foreign key checks (MySQL) or use SQLite-friendly approach
        try {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } catch (\Throwable $e) {
            // Likely SQLite; use pragma
            \DB::statement('PRAGMA foreign_keys = OFF');
        }

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
        try {
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Throwable $e) {
            \DB::statement('PRAGMA foreign_keys = ON');
        }

        // Reset auto-increment counters where supported
        try {
            \DB::statement('ALTER TABLE methodology AUTO_INCREMENT = 1');
            \DB::statement('ALTER TABLE pillars AUTO_INCREMENT = 1');
            \DB::statement('ALTER TABLE modules AUTO_INCREMENT = 1');
            \DB::statement('ALTER TABLE questions AUTO_INCREMENT = 1');
            \DB::statement('ALTER TABLE answers AUTO_INCREMENT = 1');
        } catch (\Throwable $e) {
            // SQLite does not support ALTER TABLE AUTO_INCREMENT; ignore.
        }

        echo "All existing data cleared successfully and counters reset.\n";
    }

    /**
     * Seed base answers for all question types
     */
    private function seedBaseAnswers(): void
    {
        $answers = [
            // Yes/No answers
            'Yes',
            'No',
            
            // True/False answers
            'True',
            'False',
            
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
            'Strongly Agree',
            'Agree',
            'Neutral',
            'Disagree',
            'Strongly Disagree',
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
            'Do you understand the basic concepts?',
            'Is this statement correct?',
            'How would you rate your knowledge level?',
            'Rate your confidence level from 1 to 10',
            'Do you agree with this approach?',
            'Which option best describes your experience?',
            'Select all that apply to your situation',
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
                $answers = Answer::whereIn('title', ['Yes', 'No'])->get();
                break;
            case QuestionType::TrueFalse:
                $answers = Answer::whereIn('title', ['True', 'False'])->get();
                break;
            case QuestionType::Rating1to5:
                $answers = Answer::whereIn('title', ['1', '2', '3', '4', '5'])->get();
                break;
            case QuestionType::Rating1to10:
                $answers = Answer::whereIn('title', ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'])->get();
                break;
            case QuestionType::ScaleAgreeDisagree:
                $answers = Answer::whereIn('title', [
                    'Strongly Agree',
                    'Agree',
                    'Neutral',
                    'Disagree',
                    'Strongly Disagree'
                ])->get();
                break;
            case QuestionType::MCQSingle:
            case QuestionType::MCQMultiple:
                // Create custom answers for MCQ questions
                $customAnswers = [
                    'Option A - Basic understanding',
                    'Option B - Intermediate knowledge',
                    'Option C - Advanced expertise',
                    'Option D - Expert level',
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
            'name' => 'Simple Assessment Methodology',
            'description' => 'A straightforward methodology for basic assessment and evaluation.',
            'definition' => 'This methodology provides a simple framework for evaluating basic competencies.',
            'objectives' => 'To assess fundamental knowledge and skills in a structured manner.',
            'img_url' => 'https://picsum.photos/seed/methodology-simple/800/400',
            'type' => 'simple',
            'first_section_name' => 'Core Modules',
            'second_section_name' => null,
            'pillars_definition' => 'Not applicable for simple methodology.',
            'modules_definition' => 'Core modules provide foundational knowledge and basic assessment capabilities.',
            'questions_description' => 'Basic questions covering essential concepts and skills.',
            'questions_estimated_time' => '15-20 minutes',
            'questions_count' => 7,
            'first_section_description' => 'Core modules provide foundational knowledge assessment.',
            'first_section_definition' => 'Definition for core modules section.',
            'first_section_objectives' => 'Objectives for core modules section.',
            'first_section_img_url' => 'https://picsum.photos/seed/section1-simple/800/400',
            'second_section_description' => null,
            'second_section_definition' => null,
            'second_section_objectives' => null,
            'second_section_img_url' => null,
            'tags' => ['simple', 'basic', 'assessment'],
        ]);

        // Attach all 7 questions to methodology
        foreach ($questions as $question) {
            $methodology->questions()->attach($question->id, ['weight' => 1]);
        }

        // Create 2 modules
        for ($i = 1; $i <= 2; $i++) {
            $module = Module::create([
                'name' => "Module $i",
                'description' => "Module $i provides essential knowledge and skills.",
                'definition' => "Module $i covers fundamental concepts and basic competencies.",
                'objectives' => "To assess understanding of core concepts in module $i.",
                'img_url' => 'https://picsum.photos/seed/module-simple-'.$i.'/800/400',
                'questions_description' => "Module $i contains questions that assess fundamental concepts.",
                'questions_estimated_time' => '5-7 minutes',
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
            'name' => 'Complex Assessment Methodology',
            'description' => 'A comprehensive methodology for advanced assessment and evaluation.',
            'definition' => 'This methodology provides a complex framework for evaluating advanced competencies.',
            'objectives' => 'To assess advanced knowledge and skills across multiple domains.',
            'img_url' => 'https://picsum.photos/seed/methodology-complex/800/400',
            'type' => 'complex',
            'first_section_name' => 'Advanced Pillars',
            'second_section_name' => null,
            'pillars_definition' => 'Advanced pillars represent core areas of knowledge and expertise that require comprehensive assessment.',
            'modules_definition' => 'Specialized modules within pillars provide focused assessment of specific competencies.',
            'questions_description' => 'Advanced questions covering comprehensive concepts and skills.',
            'questions_estimated_time' => '45-60 minutes',
            'questions_count' => 7,
            'first_section_description' => 'Advanced pillars provide in-depth analysis and assessment.',
            'first_section_definition' => 'Definition for advanced pillars section.',
            'first_section_objectives' => 'Objectives for advanced pillars section.',
            'first_section_img_url' => 'https://picsum.photos/seed/section1-complex/800/400',
            'second_section_description' => null,
            'second_section_definition' => null,
            'second_section_objectives' => null,
            'second_section_img_url' => null,
            'tags' => ['complex', 'advanced', 'assessment'],
        ]);

        // Attach all 7 questions to methodology
        foreach ($questions as $question) {
            $methodology->questions()->attach($question->id, ['weight' => 1]);
        }

        // Create 2 pillars
        for ($i = 1; $i <= 2; $i++) {
            $pillar = Pillar::create([
                'name' => "Pillar $i",
                'description' => "Pillar $i represents a core area of knowledge and expertise.",
                'definition' => "Pillar $i covers advanced concepts and specialized competencies.",
                'objectives' => "To assess comprehensive understanding of pillar $i domain.",
                'img_url' => 'https://picsum.photos/seed/pillar-complex-'.$i.'/800/400',
                'questions_description' => "Pillar $i contains questions that assess advanced understanding.",
                'questions_estimated_time' => '10-15 minutes',
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
                    'name' => "Pillar{$i}-Module{$j}",
                    'description' => "Module $j within Pillar $i provides specialized knowledge.",
                    'definition' => "Module $j covers specific competencies within Pillar $i.",
                    'objectives' => "To assess specialized skills in module $j of pillar $i.",
                    'img_url' => 'https://picsum.photos/seed/module-complex-'.$i.'-'.$j.'/800/400',
                    'questions_description' => "This module contains questions for specific competencies.",
                    'questions_estimated_time' => '5-7 minutes',
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
            'name' => 'Two-Section Assessment Methodology',
            'description' => 'A methodology with two sections and complex dependencies.',
            'definition' => 'This methodology provides a structured approach with interdependent sections.',
            'objectives' => 'To assess knowledge and skills with consideration of dependencies.',
            'img_url' => 'https://picsum.photos/seed/methodology-two/800/400',
            'type' => 'twoSection',
            'first_section_name' => 'Foundation Section',
            'second_section_name' => 'Advanced Section',
            'pillars_definition' => 'Pillars represent interdependent knowledge domains where later sections depend on earlier ones.',
            'modules_definition' => 'Modules within pillars provide specialized assessment with consideration of dependencies.',
            'questions_description' => 'Structured questions with section-based dependencies.',
            'questions_estimated_time' => '60-90 minutes',
            'questions_count' => 7,
            'first_section_description' => 'First section provides foundational assessment.',
            'first_section_definition' => 'Definition for foundation section.',
            'first_section_objectives' => 'Objectives for foundation section.',
            'first_section_img_url' => 'https://picsum.photos/seed/section1-two/800/400',
            'second_section_description' => 'Second section builds upon first section results.',
            'second_section_definition' => 'Definition for advanced section.',
            'second_section_objectives' => 'Objectives for advanced section.',
            'second_section_img_url' => 'https://picsum.photos/seed/section2-two/800/400',
            'tags' => ['two-section', 'dependent', 'assessment'],
        ]);

        // Attach all 7 questions to methodology
        foreach ($questions as $question) {
            $methodology->questions()->attach($question->id, ['weight' => 1]);
        }

        // Create section 1 pillars
        $section1Pillar1 = Pillar::create([
            'name' => 'Section 1 - Pillar 1',
            'description' => 'First pillar of section 1 provides foundational knowledge.',
            'definition' => 'This pillar covers basic concepts and fundamental skills.',
            'objectives' => 'To establish foundational understanding.',
            'img_url' => 'https://picsum.photos/seed/pillar-s1-1/800/400',
            'questions_description' => 'Basic questions for foundational assessment.',
            'questions_estimated_time' => '10-15 minutes',
            'questions_count' => 7,
            'tags' => ['section1', 'foundational'],
        ]);

        $section1Pillar2 = Pillar::create([
            'name' => 'Section 1 - Pillar 2',
            'description' => 'Second pillar of section 1 builds on pillar 1.',
            'definition' => 'This pillar covers intermediate concepts dependent on pillar 1.',
            'objectives' => 'To assess intermediate skills with pillar 1 dependency.',
            'img_url' => 'https://picsum.photos/seed/pillar-s1-2/800/400',
            'questions_description' => 'Intermediate questions with pillar 1 dependency.',
            'questions_estimated_time' => '10-15 minutes',
            'questions_count' => 7,
            'tags' => ['section1', 'intermediate'],
        ]);

        // Create section 2 pillars
        $section2Pillar1 = Pillar::create([
            'name' => 'Section 2 - Pillar 1',
            'description' => 'First pillar of section 2 depends on section 1 pillar 1.',
            'definition' => 'This pillar covers advanced concepts dependent on section 1 pillar 1.',
            'objectives' => 'To assess advanced skills with section 1 pillar 1 dependency.',
            'img_url' => 'https://picsum.photos/seed/pillar-s2-1/800/400',
            'questions_description' => 'Advanced questions with section 1 pillar 1 dependency.',
            'questions_estimated_time' => '10-15 minutes',
            'questions_count' => 7,
            'tags' => ['section2', 'advanced'],
        ]);

        $section2Pillar2 = Pillar::create([
            'name' => 'Section 2 - Pillar 2',
            'description' => 'Second pillar of section 2 depends on section 1 pillar 2.',
            'definition' => 'This pillar covers specialized concepts dependent on section 1 pillar 2.',
            'objectives' => 'To assess specialized skills with section 1 pillar 2 dependency.',
            'img_url' => 'https://picsum.photos/seed/pillar-s2-2/800/400',
            'questions_description' => 'Specialized questions with section 1 pillar 2 dependency.',
            'questions_estimated_time' => '10-15 minutes',
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
                    'name' => "{$pillar->name}-Module{$j}",
                    'description' => "Module $j within {$pillar->name} provides specialized knowledge.",
                    'definition' => "Module $j covers specific competencies within {$pillar->name}.",
                    'objectives' => "To assess specialized skills in module $j of {$pillar->name}.",
                    'img_url' => 'https://picsum.photos/seed/module-'.$pillarIndex.'-'.$j.'/800/400',
                    'questions_description' => "This module contains questions for specific competencies.",
                    'questions_estimated_time' => '5-7 minutes',
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