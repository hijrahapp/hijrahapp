<?php

namespace Database\Seeders;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Question;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Helper to truncate if table exists
        $safeTruncate = function (string $table): void {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        };

        // Clear data and reset autoincrement counters
        $safeTruncate('module_answer_dependencies');
        $safeTruncate('module_dependencies');
        $safeTruncate('pillar_dependencies');
        $safeTruncate('pillar_module');
        $safeTruncate('methodology_module');
        $safeTruncate('methodology_pillar');
        $safeTruncate('module_question');
        $safeTruncate('pillar_question');
        $safeTruncate('methodology_question');
        $safeTruncate('question_answer_weights');
        $safeTruncate('answer_contexts');
        $safeTruncate('questions_answers');
        $safeTruncate('questions');
        $safeTruncate('pillars');
        $safeTruncate('modules');
        $safeTruncate('methodology');
        $safeTruncate('tags');

        Schema::enableForeignKeyConstraints();

        // Tags
        $tagSimple = Tag::create(['title' => 'Simple', 'active' => true]);
        $tagComplex = Tag::create(['title' => 'Complex', 'active' => true]);
        $tagTwoSection = Tag::create(['title' => 'TwoSection', 'active' => true]);
        $allTagIds = [$tagSimple->id, $tagComplex->id, $tagTwoSection->id];

        // Dummy image generator
        $img = function (string $seed): string {
            return 'https://picsum.photos/seed/'.urlencode($seed).'/600/400';
        };

        // Helper to fetch or create answers
        $findAnswerIds = function (array $titles): array {
            return Answer::whereIn('title', $titles)->pluck('id', 'title')->all();
        };

        // Ensure MCQ options exist
        $mcqOptions = ['Option A', 'Option B', 'Option C', 'Option D'];
        $existingMcq = Answer::whereIn('title', $mcqOptions)->pluck('title')->all();
        foreach (array_diff($mcqOptions, $existingMcq) as $opt) {
            Answer::create(['title' => $opt]);
        }

        // Determine answer-context table name
        $answerContextTable = Schema::hasTable('answer_contexts') ? 'answer_contexts' : 'question_answer_weights';

        // Questions: one per type
        $questionsByType = [];
        foreach (QuestionType::cases() as $type) {
            $questionsByType[$type->value] = Question::create([
                'title' => $type->getLabel().' question',
                'type' => $type,
                'tags' => $allTagIds,
                'active' => true,
            ]);

            // Attach base answers
            $answersToAttach = [];
            if ($type->requiresCustomAnswers()) {
                $answersToAttach = $findAnswerIds($mcqOptions);
            } else {
                $answersToAttach = $findAnswerIds($type->getAnswers());
            }
            if (! empty($answersToAttach)) {
                $questionsByType[$type->value]->answers()->sync(array_values($answersToAttach));
            }
        }

        // Modules
        $makeModule = function (string $name, array $tags) use ($img) {
            $data = [
                'name' => $name,
                'description' => $name.' description',
                'definition' => $name.' definition',
                'objectives' => $name.' objectives',
                'tags' => $tags,
            ];
            if (Schema::hasColumn('modules', 'img_url')) {
                $data['img_url'] = $img($name);
            }
            if (Schema::hasColumn('modules', 'questions_description')) {
                $data['questions_description'] = 'Answer these module questions.';
            }
            if (Schema::hasColumn('modules', 'questions_estimated_time')) {
                $data['questions_estimated_time'] = '15m';
            }
            if (Schema::hasColumn('modules', 'active')) {
                $data['active'] = true;
            }

            return Module::create($data);
        };

        $simpleModule1 = $makeModule('Simple Module 1', [$tagSimple->id]);
        $simpleModule2 = $makeModule('Simple Module 2', [$tagSimple->id]);
        $complexP1M1 = $makeModule('Complex Pillar 1 Module 1', [$tagComplex->id]);
        $complexP1M2 = $makeModule('Complex Pillar 1 Module 2', [$tagComplex->id]);
        $complexP2M1 = $makeModule('Complex Pillar 2 Module 1', [$tagComplex->id]);
        $complexP2M2 = $makeModule('Complex Pillar 2 Module 2', [$tagComplex->id]);
        $twoS1P1M1 = $makeModule('TwoSection Section 1 Pillar 1 Module 1', [$tagTwoSection->id]);
        $twoS1P1M2 = $makeModule('TwoSection Section 1 Pillar 1 Module 2', [$tagTwoSection->id]);
        $twoS1P2M1 = $makeModule('TwoSection Section 1 Pillar 2 Module 1', [$tagTwoSection->id]);
        $twoS1P2M2 = $makeModule('TwoSection Section 1 Pillar 2 Module 2', [$tagTwoSection->id]);
        $twoS2P1M1 = $makeModule('TwoSection Section 2 Pillar 1 Module 1', [$tagTwoSection->id]);
        $twoS2P1M2 = $makeModule('TwoSection Section 2 Pillar 1 Module 2', [$tagTwoSection->id]);
        $twoS2P2M1 = $makeModule('TwoSection Section 2 Pillar 2 Module 1', [$tagTwoSection->id]);
        $twoS2P2M2 = $makeModule('TwoSection Section 2 Pillar 2 Module 2', [$tagTwoSection->id]);

        // Pillars
        $makePillar = function (string $name, array $tags) use ($img) {
            $data = [
                'name' => $name,
                'description' => $name.' description',
                'definition' => $name.' definition',
                'objectives' => $name.' objectives',
                'tags' => $tags,
            ];
            if (Schema::hasColumn('pillars', 'img_url')) {
                $data['img_url'] = $img($name);
            }
            if (Schema::hasColumn('pillars', 'questions_description')) {
                $data['questions_description'] = 'Answer these pillar questions.';
            }
            if (Schema::hasColumn('pillars', 'questions_estimated_time')) {
                $data['questions_estimated_time'] = '20m';
            }
            if (Schema::hasColumn('pillars', 'active')) {
                $data['active'] = true;
            }

            return Pillar::create($data);
        };

        $complexPillar1 = $makePillar('Complex Pillar 1', [$tagComplex->id]);
        $complexPillar2 = $makePillar('Complex Pillar 2', [$tagComplex->id]);
        $twoS1Pillar1 = $makePillar('TwoSection Section 1 Pillar 1', [$tagTwoSection->id]);
        $twoS1Pillar2 = $makePillar('TwoSection Section 1 Pillar 2', [$tagTwoSection->id]);
        $twoS2Pillar1 = $makePillar('TwoSection Section 2 Pillar 1', [$tagTwoSection->id]);
        $twoS2Pillar2 = $makePillar('TwoSection Section 2 Pillar 2', [$tagTwoSection->id]);

        // Methodologies
        $simpleMethodData = [
            'name' => 'Simple Methodology',
            'description' => 'Simple Methodology description',
            'definition' => 'Simple Methodology definition',
            'objectives' => 'Simple Methodology objectives',
            'type' => 'simple',
            'tags' => [$tagSimple->id],
        ];
        if (Schema::hasColumn('methodology', 'img_url')) {
            $simpleMethodData['img_url'] = $img('Simple Methodology');
        }
        if (Schema::hasColumn('methodology', 'questions_description')) {
            $simpleMethodData['questions_description'] = 'Answer the following questions for the methodology.';
        }
        if (Schema::hasColumn('methodology', 'questions_estimated_time')) {
            $simpleMethodData['questions_estimated_time'] = 30;
        }
        if (Schema::hasColumn('methodology', 'modules_definition')) {
            $simpleMethodData['modules_definition'] = 'This simple methodology comprises 2 modules.';
        }
        $simpleMethod = Methodology::create($simpleMethodData);

        $complexMethodData = [
            'name' => 'Complex Methodology',
            'description' => 'Complex Methodology description',
            'definition' => 'Complex Methodology definition',
            'objectives' => 'Complex Methodology objectives',
            'type' => 'complex',
            'tags' => [$tagComplex->id],
        ];
        if (Schema::hasColumn('methodology', 'img_url')) {
            $complexMethodData['img_url'] = $img('Complex Methodology');
        }
        if (Schema::hasColumn('methodology', 'questions_description')) {
            $complexMethodData['questions_description'] = 'Answer the following questions for the methodology.';
        }
        if (Schema::hasColumn('methodology', 'questions_estimated_time')) {
            $complexMethodData['questions_estimated_time'] = 45;
        }
        if (Schema::hasColumn('methodology', 'pillars_definition')) {
            $complexMethodData['pillars_definition'] = 'This complex methodology contains thematic pillars.';
        }
        $complexMethod = Methodology::create($complexMethodData);

        $twoSectionMethodData = [
            'name' => 'TwoSection Methodology',
            'description' => 'TwoSection Methodology description',
            'definition' => 'TwoSection Methodology definition',
            'objectives' => 'TwoSection Methodology objectives',
            'type' => 'twoSection',
            'first_section_name' => 'Section 1',
            'second_section_name' => 'Section 2',
            'tags' => [$tagTwoSection->id],
        ];
        if (Schema::hasColumn('methodology', 'img_url')) {
            $twoSectionMethodData['img_url'] = $img('TwoSection Methodology');
        }
        if (Schema::hasColumn('methodology', 'questions_description')) {
            $twoSectionMethodData['questions_description'] = 'Answer the following questions for the methodology.';
        }
        if (Schema::hasColumn('methodology', 'questions_estimated_time')) {
            $twoSectionMethodData['questions_estimated_time'] = 60;
        }
        if (Schema::hasColumn('methodology', 'first_section_description')) {
            $twoSectionMethodData['first_section_description'] = 'Overview of section 1.';
        }
        if (Schema::hasColumn('methodology', 'first_section_definition')) {
            $twoSectionMethodData['first_section_definition'] = 'Definition of section 1.';
        }
        if (Schema::hasColumn('methodology', 'first_section_objectives')) {
            $twoSectionMethodData['first_section_objectives'] = 'Objectives of section 1.';
        }
        if (Schema::hasColumn('methodology', 'first_section_img_url')) {
            $twoSectionMethodData['first_section_img_url'] = $img('TwoSection Section 1');
        }
        if (Schema::hasColumn('methodology', 'first_section_pillars_definition')) {
            $twoSectionMethodData['first_section_pillars_definition'] = 'Two pillars in section one.';
        }

        if (Schema::hasColumn('methodology', 'second_section_description')) {
            $twoSectionMethodData['second_section_description'] = 'Overview of section 2.';
        }
        if (Schema::hasColumn('methodology', 'second_section_definition')) {
            $twoSectionMethodData['second_section_definition'] = 'Definition of section 2.';
        }
        if (Schema::hasColumn('methodology', 'second_section_objectives')) {
            $twoSectionMethodData['second_section_objectives'] = 'Objectives of section 2.';
        }
        if (Schema::hasColumn('methodology', 'second_section_img_url')) {
            $twoSectionMethodData['second_section_img_url'] = $img('TwoSection Section 2');
        }
        if (Schema::hasColumn('methodology', 'second_section_pillars_definition')) {
            $twoSectionMethodData['second_section_pillars_definition'] = 'Two pillars in section two.';
        }

        $twoSectionMethod = Methodology::create($twoSectionMethodData);

        // Relate: Simple methodology -> modules
        foreach ([[$simpleModule1, 1], [$simpleModule2, 2]] as [$module, $seq]) {
            $row = [
                'methodology_id' => $simpleMethod->id,
                'module_id' => $module->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('methodology_module', 'weight')) {
                $row['weight'] = 50.00;
            }
            if (Schema::hasColumn('methodology_module', 'minutes')) {
                $row['minutes'] = 15;
            }
            if (Schema::hasColumn('methodology_module', 'report')) {
                $row['report'] = $module->name.' report';
            }
            if (Schema::hasColumn('methodology_module', 'questions_description')) {
                $row['questions_description'] = 'Module questions';
            }
            if (Schema::hasColumn('methodology_module', 'questions_estimated_time')) {
                $row['questions_estimated_time'] = 15;
            }
            DB::table('methodology_module')->insert($row);
        }

        // Relate: Complex methodology -> pillars and modules under pillars
        $modulePillarMap = [];
        $cmpPillars = [[$complexPillar1, 'first', 1, [[$complexP1M1, 1], [$complexP1M2, 2]]], [$complexPillar2, 'first', 2, [[$complexP2M1, 1], [$complexP2M2, 2]]]];
        foreach ($cmpPillars as [$pillar, $section, $seq, $mods]) {
            $mpRow = [
                'methodology_id' => $complexMethod->id,
                'pillar_id' => $pillar->id,
                'section' => $section,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('methodology_pillar', 'sequence')) {
                $mpRow['sequence'] = $seq;
            }
            if (Schema::hasColumn('methodology_pillar', 'weight')) {
                $mpRow['weight'] = 50.00;
            }
            if (Schema::hasColumn('methodology_pillar', 'questions_description')) {
                $mpRow['questions_description'] = 'Pillar questions';
            }
            if (Schema::hasColumn('methodology_pillar', 'questions_estimated_time')) {
                $mpRow['questions_estimated_time'] = 20;
            }
            DB::table('methodology_pillar')->insert($mpRow);
            foreach ($mods as [$mod, $modSeq]) {
                $pmRow = [
                    'methodology_id' => $complexMethod->id,
                    'pillar_id' => $pillar->id,
                    'module_id' => $mod->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (Schema::hasColumn('pillar_module', 'weight')) {
                    $pmRow['weight'] = 50.00;
                }
                if (Schema::hasColumn('pillar_module', 'minutes')) {
                    $pmRow['minutes'] = 15;
                }
                if (Schema::hasColumn('pillar_module', 'report')) {
                    $pmRow['report'] = $mod->name.' report';
                }
                if (Schema::hasColumn('pillar_module', 'questions_description')) {
                    $pmRow['questions_description'] = 'Module questions';
                }
                if (Schema::hasColumn('pillar_module', 'questions_estimated_time')) {
                    $pmRow['questions_estimated_time'] = 15;
                }
                DB::table('pillar_module')->insert($pmRow);

                // Map module to pillar for complex methodology
                $modulePillarMap[$complexMethod->id][$mod->id] = $pillar->id;
            }
        }

        // Relate: TwoSection methodology -> pillars and modules under pillars
        $twoSPillars = [
            [$twoS1Pillar1, 'first', 1, [[$twoS1P1M1, 1], [$twoS1P1M2, 2]]],
            [$twoS1Pillar2, 'first', 2, [[$twoS1P2M1, 1], [$twoS1P2M2, 2]]],
            [$twoS2Pillar1, 'second', 1, [[$twoS2P1M1, 1], [$twoS2P1M2, 2]]],
            [$twoS2Pillar2, 'second', 2, [[$twoS2P2M1, 1], [$twoS2P2M2, 2]]],
        ];
        foreach ($twoSPillars as [$pillar, $section, $seq, $mods]) {
            $mpRow = [
                'methodology_id' => $twoSectionMethod->id,
                'pillar_id' => $pillar->id,
                'section' => $section,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('methodology_pillar', 'sequence')) {
                $mpRow['sequence'] = $seq;
            }
            if (Schema::hasColumn('methodology_pillar', 'weight')) {
                $mpRow['weight'] = 25.00;
            }
            if (Schema::hasColumn('methodology_pillar', 'questions_description')) {
                $mpRow['questions_description'] = 'Pillar questions';
            }
            if (Schema::hasColumn('methodology_pillar', 'questions_estimated_time')) {
                $mpRow['questions_estimated_time'] = 20;
            }
            DB::table('methodology_pillar')->insert($mpRow);
            foreach ($mods as [$mod, $modSeq]) {
                $pmRow = [
                    'methodology_id' => $twoSectionMethod->id,
                    'pillar_id' => $pillar->id,
                    'module_id' => $mod->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (Schema::hasColumn('pillar_module', 'weight')) {
                    $pmRow['weight'] = 50.00;
                }
                if (Schema::hasColumn('pillar_module', 'minutes')) {
                    $pmRow['minutes'] = 15;
                }
                if (Schema::hasColumn('pillar_module', 'report')) {
                    $pmRow['report'] = $mod->name.' report';
                }
                if (Schema::hasColumn('pillar_module', 'questions_description')) {
                    $pmRow['questions_description'] = 'Module questions';
                }
                if (Schema::hasColumn('pillar_module', 'questions_estimated_time')) {
                    $pmRow['questions_estimated_time'] = 15;
                }
                DB::table('pillar_module')->insert($pmRow);

                // Map module to pillar for two-section methodology
                $modulePillarMap[$twoSectionMethod->id][$mod->id] = $pillar->id;
            }
        }

        // Helper: evenly weight answers for a given context row
        $weightAnswersEvenly = function (string $contextType, int $contextId, Question $question) use ($answerContextTable): void {
            $answerIds = $question->answers()->pluck('answers.id')->all();
            if (empty($answerIds)) {
                return;
            }
            $count = count($answerIds);
            $weight = round(100.0 / max(1, $count), 2);
            $rows = [];
            foreach ($answerIds as $aid) {
                $rows[] = [
                    'context_type' => $contextType,
                    'context_id' => $contextId,
                    'answer_id' => $aid,
                    'weight' => $weight,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table($answerContextTable)->upsert(
                $rows,
                ['context_type', 'context_id', 'answer_id'],
                ['weight', 'updated_at']
            );
        };

        // Methodology-level questions: add 4 mixed questions per methodology
        $allQuestions = array_values($questionsByType);
        $methodologies = [
            [$simpleMethod, [0, 1, 2, 3]], // YesNo, TrueFalse, MCQSingle, MCQMultiple
            [$complexMethod, [4, 5, 6, 0]], // Rating1to5, Rating1to10, Scale, YesNo
            [$twoSectionMethod, [1, 2, 6, 4]], // TrueFalse, MCQSingle, Scale, Rating1to5
        ];
        foreach ($methodologies as [$methodology, $idxs]) {
            $sequence = 1;
            foreach ($idxs as $i) {
                $q = $allQuestions[$i];
                $mqRow = [
                    'methodology_id' => $methodology->id,
                    'question_id' => $q->id,
                    'weight' => round(100.0 / 4, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (Schema::hasColumn('methodology_question', 'sequence')) {
                    $mqRow['sequence'] = $sequence;
                }
                $pivotId = DB::table('methodology_question')->insertGetId($mqRow);
                $weightAnswersEvenly('methodology_question', $pivotId, $q);
                $sequence++;
            }
        }

        // Module-level questions: add 3 mixed questions per module in sequence and set dependencies
        $moduleSets = [
            $simpleMethod->id => [
                $simpleModule1,
                $simpleModule2,
            ],
            $complexMethod->id => [
                $complexP1M1,
                $complexP1M2,
                $complexP2M1,
                $complexP2M2,
            ],
            $twoSectionMethod->id => [
                $twoS1P1M1,
                $twoS1P1M2,
                $twoS1P2M1,
                $twoS1P2M2,
                $twoS2P1M1,
                $twoS2P1M2,
                $twoS2P2M1,
                $twoS2P2M2,
            ],
        ];

        $moduleQuestionIndices = [0, 2, 4]; // pick a stable set: YesNo, MCQSingle, Rating1to5

        foreach ($moduleSets as $methodologyId => $mods) {
            foreach ($mods as $mod) {
                $sequence = 1;
                $modulePivotIds = [];
                $chosen = array_map(fn ($idx) => $allQuestions[$idx], $moduleQuestionIndices);
                foreach ($chosen as $q) {
                    $pillarId = $modulePillarMap[$methodologyId][$mod->id] ?? null;
                    $modQRow = [
                        'methodology_id' => $methodologyId,
                        'module_id' => $mod->id,
                        'pillar_id' => $pillarId,
                        'question_id' => $q->id,
                        'weight' => round(100.0 / 3, 2),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    if (Schema::hasColumn('module_question', 'sequence')) {
                        $modQRow['sequence'] = $sequence;
                    }
                    $pivotId = DB::table('module_question')->insertGetId($modQRow);
                    $modulePivotIds[] = ['id' => $pivotId, 'question' => $q];
                    $weightAnswersEvenly('module_question', $pivotId, $q);
                    $sequence++;
                }

                // Dependencies: q1 -> q2, q2 -> q3 (for all answers)
                if (count($modulePivotIds) === 3 && Schema::hasTable($answerContextTable)) {
                    [$mq1, $mq2, $mq3] = $modulePivotIds;
                    // For all answers in question 1 context, set dependency to question 2 context
                    DB::table($answerContextTable)
                        ->where('context_type', 'module_question')
                        ->where('context_id', $mq1['id'])
                        ->update([
                            'dependent_context_type' => 'module_question',
                            'dependent_context_id' => $mq2['id'],
                            'updated_at' => now(),
                        ]);
                    // For all answers in question 2 context, set dependency to question 3 context
                    DB::table($answerContextTable)
                        ->where('context_type', 'module_question')
                        ->where('context_id', $mq2['id'])
                        ->update([
                            'dependent_context_type' => 'module_question',
                            'dependent_context_id' => $mq3['id'],
                            'updated_at' => now(),
                        ]);
                }
            }
        }

        // Module dependencies
        if (Schema::hasTable('module_dependencies')) {
            DB::table('module_dependencies')->insert([
                ['methodology_id' => $simpleMethod->id, 'module_id' => $simpleModule2->id, 'depends_on_module_id' => $simpleModule1->id, 'created_at' => now(), 'updated_at' => now()],
                ['methodology_id' => $complexMethod->id, 'module_id' => $complexP1M2->id, 'depends_on_module_id' => $complexP1M1->id, 'created_at' => now(), 'updated_at' => now()],
                ['methodology_id' => $complexMethod->id, 'module_id' => $complexP2M2->id, 'depends_on_module_id' => $complexP2M1->id, 'created_at' => now(), 'updated_at' => now()],
                ['methodology_id' => $twoSectionMethod->id, 'module_id' => $twoS1P1M2->id, 'depends_on_module_id' => $twoS1P1M1->id, 'created_at' => now(), 'updated_at' => now()],
                ['methodology_id' => $twoSectionMethod->id, 'module_id' => $twoS1P2M2->id, 'depends_on_module_id' => $twoS1P2M1->id, 'created_at' => now(), 'updated_at' => now()],
                ['methodology_id' => $twoSectionMethod->id, 'module_id' => $twoS2P1M2->id, 'depends_on_module_id' => $twoS2P1M1->id, 'created_at' => now(), 'updated_at' => now()],
                ['methodology_id' => $twoSectionMethod->id, 'module_id' => $twoS2P2M2->id, 'depends_on_module_id' => $twoS2P2M1->id, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Pillar dependencies
        DB::table('pillar_dependencies')->insert([
            ['methodology_id' => $complexMethod->id, 'pillar_id' => $complexPillar2->id, 'depends_on_pillar_id' => $complexPillar1->id, 'created_at' => now(), 'updated_at' => now()],
            ['methodology_id' => $twoSectionMethod->id, 'pillar_id' => $twoS1Pillar2->id, 'depends_on_pillar_id' => $twoS1Pillar1->id, 'created_at' => now(), 'updated_at' => now()],
            ['methodology_id' => $twoSectionMethod->id, 'pillar_id' => $twoS2Pillar1->id, 'depends_on_pillar_id' => $twoS1Pillar1->id, 'created_at' => now(), 'updated_at' => now()],
            // Assuming intended: Section 2 Pillar 2 depends on Section 2 Pillar 1
            ['methodology_id' => $twoSectionMethod->id, 'pillar_id' => $twoS2Pillar2->id, 'depends_on_pillar_id' => $twoS2Pillar1->id, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
