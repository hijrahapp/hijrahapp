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

class DemoArabicSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $safeTruncate = function (string $table): void {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        };

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

        // Determine answer-context table name
        $answerContextTable = Schema::hasTable('answer_contexts') ? 'answer_contexts' : 'question_answer_weights';

        // Tags
        $tagSimple = Tag::create(['title' => 'بسيط', 'active' => true]);
        $tagComplex = Tag::create(['title' => 'معقد', 'active' => true]);
        $tagTwoSection = Tag::create(['title' => 'قسمان', 'active' => true]);
        $allTagIds = [$tagSimple->id, $tagComplex->id, $tagTwoSection->id];
        $img = function (string $seed): string {
            return 'https://picsum.photos/seed/'.urlencode($seed).'/600/400';
        };

        $findAnswerIds = function (array $titles): array {
            return Answer::whereIn('title', $titles)->pluck('id', 'title')->all();
        };

        // Ensure MCQ options exist (Arabic)
        $mcqOptions = ['الخيار أ', 'الخيار ب', 'الخيار ج', 'الخيار د'];
        $existingMcq = Answer::whereIn('title', $mcqOptions)->pluck('title')->all();
        foreach (array_diff($mcqOptions, $existingMcq) as $opt) {
            Answer::create(['title' => $opt]);
        }

        // Questions: one per type (Arabic titles)
        $labelMap = [
            QuestionType::YesNo->value => 'سؤال نعم/لا',
            QuestionType::TrueFalse->value => 'سؤال صح/خطأ',
            QuestionType::MCQSingle->value => 'سؤال اختيار متعدد (إجابة واحدة)',
            QuestionType::MCQMultiple->value => 'سؤال اختيار متعدد (إجابات متعددة)',
            QuestionType::Rating1to5->value => 'سؤال تقييم من 1 إلى 5',
            QuestionType::Rating1to10->value => 'سؤال تقييم من 1 إلى 10',
            QuestionType::ScaleAgreeDisagree->value => 'سؤال مقياس الموافقة',
        ];

        $questionsByType = [];
        foreach (QuestionType::cases() as $type) {
            $questionsByType[$type->value] = Question::create([
                'title' => $labelMap[$type->value],
                'type' => $type,
                'tags' => $allTagIds,
                'active' => true,
            ]);

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
                'description' => $name.' الوصف',
                'definition' => $name.' التعريف',
                'objectives' => $name.' الأهداف',
                'tags' => $tags,
            ];
            if (Schema::hasColumn('modules', 'img_url')) {
                $data['img_url'] = $img($name);
            }
            if (Schema::hasColumn('modules', 'questions_description')) {
                $data['questions_description'] = 'أجب عن أسئلة هذا المكون.';
            }
            if (Schema::hasColumn('modules', 'questions_estimated_time')) {
                $data['questions_estimated_time'] = '15د';
            }
            if (Schema::hasColumn('modules', 'questions_count')) {
                $data['questions_count'] = 0;
            }
            if (Schema::hasColumn('modules', 'active')) {
                $data['active'] = true;
            }

            return Module::create($data);
        };

        $simpleModule1 = $makeModule('مكون بسيط 1', [$tagSimple->id]);
        $simpleModule2 = $makeModule('مكون بسيط 2', [$tagSimple->id]);
        $complexP1M1 = $makeModule('مكون معقد - عمود 1 - 1', [$tagComplex->id]);
        $complexP1M2 = $makeModule('مكون معقد - عمود 1 - 2', [$tagComplex->id]);
        $complexP2M1 = $makeModule('مكون معقد - عمود 2 - 1', [$tagComplex->id]);
        $complexP2M2 = $makeModule('مكون معقد - عمود 2 - 2', [$tagComplex->id]);
        $twoS1P1M1 = $makeModule('قسمان - القسم 1 - العمود 1 - 1', [$tagTwoSection->id]);
        $twoS1P1M2 = $makeModule('قسمان - القسم 1 - العمود 1 - 2', [$tagTwoSection->id]);
        $twoS1P2M1 = $makeModule('قسمان - القسم 1 - العمود 2 - 1', [$tagTwoSection->id]);
        $twoS1P2M2 = $makeModule('قسمان - القسم 1 - العمود 2 - 2', [$tagTwoSection->id]);
        $twoS2P1M1 = $makeModule('قسمان - القسم 2 - العمود 1 - 1', [$tagTwoSection->id]);
        $twoS2P1M2 = $makeModule('قسمان - القسم 2 - العمود 1 - 2', [$tagTwoSection->id]);
        $twoS2P2M1 = $makeModule('قسمان - القسم 2 - العمود 2 - 1', [$tagTwoSection->id]);
        $twoS2P2M2 = $makeModule('قسمان - القسم 2 - العمود 2 - 2', [$tagTwoSection->id]);

        // Pillars
        $makePillar = function (string $name, array $tags) use ($img) {
            $data = [
                'name' => $name,
                'description' => $name.' الوصف',
                'definition' => $name.' التعريف',
                'objectives' => $name.' الأهداف',
                'tags' => $tags,
            ];
            if (Schema::hasColumn('pillars', 'img_url')) {
                $data['img_url'] = $img($name);
            }
            if (Schema::hasColumn('pillars', 'questions_description')) {
                $data['questions_description'] = 'أجب عن أسئلة هذا العمود.';
            }
            if (Schema::hasColumn('pillars', 'questions_estimated_time')) {
                $data['questions_estimated_time'] = '20د';
            }
            if (Schema::hasColumn('pillars', 'questions_count')) {
                $data['questions_count'] = 0;
            }
            if (Schema::hasColumn('pillars', 'active')) {
                $data['active'] = true;
            }

            return Pillar::create($data);
        };

        $complexPillar1 = $makePillar('عمود معقد 1', [$tagComplex->id]);
        $complexPillar2 = $makePillar('عمود معقد 2', [$tagComplex->id]);
        $twoS1Pillar1 = $makePillar('قسمان - القسم 1 - العمود 1', [$tagTwoSection->id]);
        $twoS1Pillar2 = $makePillar('قسمان - القسم 1 - العمود 2', [$tagTwoSection->id]);
        $twoS2Pillar1 = $makePillar('قسمان - القسم 2 - العمود 1', [$tagTwoSection->id]);
        $twoS2Pillar2 = $makePillar('قسمان - القسم 2 - العمود 2', [$tagTwoSection->id]);

        // Methodologies
        $simpleMethodData = [
            'name' => 'منهجية بسيطة',
            'description' => 'وصف المنهجية البسيطة',
            'definition' => 'تعريف المنهجية البسيطة',
            'objectives' => 'أهداف المنهجية البسيطة',
            'type' => 'simple',
            'tags' => [$tagSimple->id],
        ];
        if (Schema::hasColumn('methodology', 'img_url')) {
            $simpleMethodData['img_url'] = $img('منهجية بسيطة');
        }
        if (Schema::hasColumn('methodology', 'questions_description')) {
            $simpleMethodData['questions_description'] = 'أجب عن الأسئلة التالية الخاصة بالمنهجية.';
        }
        if (Schema::hasColumn('methodology', 'questions_estimated_time')) {
            $simpleMethodData['questions_estimated_time'] = 30;
        }
        if (Schema::hasColumn('methodology', 'questions_count')) {
            $simpleMethodData['questions_count'] = 0;
        }
        if (Schema::hasColumn('methodology', 'modules_definition')) {
            $simpleMethodData['modules_definition'] = 'هذه المنهجية البسيطة تتكون من مكونين.';
        }
        $simpleMethod = Methodology::create($simpleMethodData);

        $complexMethodData = [
            'name' => 'منهجية معقدة',
            'description' => 'وصف المنهجية المعقدة',
            'definition' => 'تعريف المنهجية المعقدة',
            'objectives' => 'أهداف المنهجية المعقدة',
            'type' => 'complex',
            'tags' => [$tagComplex->id],
        ];
        if (Schema::hasColumn('methodology', 'img_url')) {
            $complexMethodData['img_url'] = $img('منهجية معقدة');
        }
        if (Schema::hasColumn('methodology', 'questions_description')) {
            $complexMethodData['questions_description'] = 'أجب عن الأسئلة التالية الخاصة بالمنهجية.';
        }
        if (Schema::hasColumn('methodology', 'questions_estimated_time')) {
            $complexMethodData['questions_estimated_time'] = 45;
        }
        if (Schema::hasColumn('methodology', 'questions_count')) {
            $complexMethodData['questions_count'] = 0;
        }
        if (Schema::hasColumn('methodology', 'pillars_definition')) {
            $complexMethodData['pillars_definition'] = 'هذه المنهجية المعقدة تحتوي على أعمدة موضوعية.';
        }
        if (Schema::hasColumn('methodology', 'number_of_pillars')) {
            $complexMethodData['number_of_pillars'] = '2';
        }
        $complexMethod = Methodology::create($complexMethodData);

        $twoSectionMethodData = [
            'name' => 'منهجية قسمان',
            'description' => 'وصف منهجية القسمين',
            'definition' => 'تعريف منهجية القسمين',
            'objectives' => 'أهداف منهجية القسمين',
            'type' => 'twoSection',
            'first_section_name' => 'القسم الأول',
            'second_section_name' => 'القسم الثاني',
            'tags' => [$tagTwoSection->id],
        ];
        if (Schema::hasColumn('methodology', 'img_url')) {
            $twoSectionMethodData['img_url'] = $img('منهجية قسمان');
        }
        if (Schema::hasColumn('methodology', 'questions_description')) {
            $twoSectionMethodData['questions_description'] = 'أجب عن الأسئلة التالية الخاصة بالمنهجية.';
        }
        if (Schema::hasColumn('methodology', 'questions_estimated_time')) {
            $twoSectionMethodData['questions_estimated_time'] = 60;
        }
        if (Schema::hasColumn('methodology', 'questions_count')) {
            $twoSectionMethodData['questions_count'] = 0;
        }
        if (Schema::hasColumn('methodology', 'first_section_description')) {
            $twoSectionMethodData['first_section_description'] = 'نظرة عامة على القسم الأول.';
        }
        if (Schema::hasColumn('methodology', 'first_section_definition')) {
            $twoSectionMethodData['first_section_definition'] = 'تعريف القسم الأول.';
        }
        if (Schema::hasColumn('methodology', 'first_section_objectives')) {
            $twoSectionMethodData['first_section_objectives'] = 'أهداف القسم الأول.';
        }
        if (Schema::hasColumn('methodology', 'first_section_img_url')) {
            $twoSectionMethodData['first_section_img_url'] = $img('القسم الأول');
        }
        if (Schema::hasColumn('methodology', 'first_section_number_of_pillars')) {
            $twoSectionMethodData['first_section_number_of_pillars'] = '2';
        }
        if (Schema::hasColumn('methodology', 'first_section_pillars_definition')) {
            $twoSectionMethodData['first_section_pillars_definition'] = 'عمودان في القسم الأول.';
        }

        if (Schema::hasColumn('methodology', 'second_section_description')) {
            $twoSectionMethodData['second_section_description'] = 'نظرة عامة على القسم الثاني.';
        }
        if (Schema::hasColumn('methodology', 'second_section_definition')) {
            $twoSectionMethodData['second_section_definition'] = 'تعريف القسم الثاني.';
        }
        if (Schema::hasColumn('methodology', 'second_section_objectives')) {
            $twoSectionMethodData['second_section_objectives'] = 'أهداف القسم الثاني.';
        }
        if (Schema::hasColumn('methodology', 'second_section_img_url')) {
            $twoSectionMethodData['second_section_img_url'] = $img('القسم الثاني');
        }
        if (Schema::hasColumn('methodology', 'second_section_number_of_pillars')) {
            $twoSectionMethodData['second_section_number_of_pillars'] = '2';
        }
        if (Schema::hasColumn('methodology', 'second_section_pillars_definition')) {
            $twoSectionMethodData['second_section_pillars_definition'] = 'عمودان في القسم الثاني.';
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
            if (Schema::hasColumn('methodology_module', 'number_of_questions')) {
                $row['number_of_questions'] = 3;
            }
            if (Schema::hasColumn('methodology_module', 'weight')) {
                $row['weight'] = 50.00;
            }
            if (Schema::hasColumn('methodology_module', 'minutes')) {
                $row['minutes'] = 15;
            }
            if (Schema::hasColumn('methodology_module', 'report')) {
                $row['report'] = $module->name.' تقرير';
            }
            if (Schema::hasColumn('methodology_module', 'questions_description')) {
                $row['questions_description'] = 'أسئلة المكون';
            }
            if (Schema::hasColumn('methodology_module', 'questions_estimated_time')) {
                $row['questions_estimated_time'] = 15;
            }
            DB::table('methodology_module')->insert($row);
        }

        // Relate: Complex methodology -> pillars and modules
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
            if (Schema::hasColumn('methodology_pillar', 'number_of_modules')) {
                $mpRow['number_of_modules'] = count($mods);
            }
            if (Schema::hasColumn('methodology_pillar', 'weight')) {
                $mpRow['weight'] = 50.00;
            }
            if (Schema::hasColumn('methodology_pillar', 'questions_description')) {
                $mpRow['questions_description'] = 'أسئلة العمود';
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
                if (Schema::hasColumn('pillar_module', 'number_of_questions')) {
                    $pmRow['number_of_questions'] = 3;
                }
                if (Schema::hasColumn('pillar_module', 'weight')) {
                    $pmRow['weight'] = 50.00;
                }
                if (Schema::hasColumn('pillar_module', 'minutes')) {
                    $pmRow['minutes'] = 15;
                }
                if (Schema::hasColumn('pillar_module', 'report')) {
                    $pmRow['report'] = $mod->name.' تقرير';
                }
                if (Schema::hasColumn('pillar_module', 'questions_description')) {
                    $pmRow['questions_description'] = 'أسئلة المكون';
                }
                if (Schema::hasColumn('pillar_module', 'questions_estimated_time')) {
                    $pmRow['questions_estimated_time'] = 15;
                }
                DB::table('pillar_module')->insert($pmRow);
                $modulePillarMap[$complexMethod->id][$mod->id] = $pillar->id;
            }
        }

        // Relate: TwoSection methodology -> pillars and modules
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
            if (Schema::hasColumn('methodology_pillar', 'number_of_modules')) {
                $mpRow['number_of_modules'] = count($mods);
            }
            if (Schema::hasColumn('methodology_pillar', 'weight')) {
                $mpRow['weight'] = 25.00;
            }
            if (Schema::hasColumn('methodology_pillar', 'questions_description')) {
                $mpRow['questions_description'] = 'أسئلة العمود';
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
                if (Schema::hasColumn('pillar_module', 'number_of_questions')) {
                    $pmRow['number_of_questions'] = 3;
                }
                if (Schema::hasColumn('pillar_module', 'weight')) {
                    $pmRow['weight'] = 1.00;
                }
                if (Schema::hasColumn('pillar_module', 'minutes')) {
                    $pmRow['minutes'] = 15;
                }
                if (Schema::hasColumn('pillar_module', 'report')) {
                    $pmRow['report'] = $mod->name.' تقرير';
                }
                if (Schema::hasColumn('pillar_module', 'questions_description')) {
                    $pmRow['questions_description'] = 'أسئلة المكون';
                }
                if (Schema::hasColumn('pillar_module', 'questions_estimated_time')) {
                    $pmRow['questions_estimated_time'] = 15;
                }
                DB::table('pillar_module')->insert($pmRow);
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
            [$simpleMethod, [0, 1, 2, 3]],
            [$complexMethod, [4, 5, 6, 0]],
            [$twoSectionMethod, [1, 2, 6, 4]],
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
            $methodology->update(['questions_count' => 4]);
        }

        // Module-level questions and dependencies
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

        $moduleQuestionIndices = [0, 2, 4];

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

                if (count($modulePivotIds) === 3 && Schema::hasTable($answerContextTable)) {
                    [$mq1, $mq2, $mq3] = $modulePivotIds;
                    DB::table($answerContextTable)
                        ->where('context_type', 'module_question')
                        ->where('context_id', $mq1['id'])
                        ->update([
                            'dependent_context_type' => 'module_question',
                            'dependent_context_id' => $mq2['id'],
                            'updated_at' => now(),
                        ]);
                    DB::table($answerContextTable)
                        ->where('context_type', 'module_question')
                        ->where('context_id', $mq2['id'])
                        ->update([
                            'dependent_context_type' => 'module_question',
                            'dependent_context_id' => $mq3['id'],
                            'updated_at' => now(),
                        ]);
                }

                $mod->update(['questions_count' => 3]);
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
            ['methodology_id' => $twoSectionMethod->id, 'pillar_id' => $twoS2Pillar2->id, 'depends_on_pillar_id' => $twoS2Pillar1->id, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
