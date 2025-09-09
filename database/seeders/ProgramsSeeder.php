<?php

namespace Database\Seeders;

use App\Models\Methodology;
use App\Models\Program;
use App\Models\Step;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProgramsSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Clear existing programs and related data
        DB::table('user_programs')->truncate();
        DB::table('program_module')->truncate();
        DB::table('steps')->truncate();
        DB::table('programs')->truncate();

        Schema::enableForeignKeyConstraints();

        // Get existing methodologies, pillars, and modules
        $methodologies = Methodology::with(['modules', 'pillars.modules'])->get();

        if ($methodologies->isEmpty()) {
            $this->command->warn('No methodologies found. Please run the demo seeders first.');

            return;
        }

        // Arabic program definitions
        $programsData = [
            [
                'name' => 'برنامج التحول الرقمي المتقدم',
                'description' => 'برنامج شامل لتطوير المهارات الرقمية والتكنولوجية في بيئة العمل الحديثة',
                'definition' => 'يهدف هذا البرنامج إلى تزويد المشاركين بالمعرفة والمهارات اللازمة لقيادة التحول الرقمي في مؤسساتهم',
                'objectives' => 'تطوير فهم عميق للتكنولوجيات الناشئة وتطبيقها في بيئة العمل',
                'methodology_modules' => [
                    ['methodology_type' => 'simple', 'min_score' => 0, 'max_score' => 100],
                    ['methodology_type' => 'complex', 'min_score' => 0, 'max_score' => 100],
                ],
            ],
            [
                'name' => 'برنامج القيادة الاستراتيجية',
                'description' => 'برنامج متخصص في تطوير مهارات القيادة والتفكير الاستراتيجي',
                'definition' => 'يركز هذا البرنامج على بناء قادة قادرين على اتخاذ القرارات الاستراتيجية الصحيحة',
                'objectives' => 'تنمية القدرات القيادية والتفكير النقدي والتخطيط الاستراتيجي',
                'methodology_modules' => [
                    ['methodology_type' => 'complex', 'min_score' => 0, 'max_score' => 100],
                    ['methodology_type' => 'twoSection', 'min_score' => 0, 'max_score' => 100],
                ],
            ],
            [
                'name' => 'برنامج الابتكار وريادة الأعمال',
                'description' => 'برنامج مصمم لتنمية روح الابتكار وتطوير المشاريع الريادية',
                'definition' => 'يهدف إلى إعداد رواد أعمال قادرين على تطوير وإدارة مشاريع مبتكرة ومستدامة',
                'objectives' => 'تطوير المهارات الريادية والإبداعية وإدارة المخاطر',
                'methodology_modules' => [
                    ['methodology_type' => 'simple', 'min_score' => 0, 'max_score' => 100],
                    ['methodology_type' => 'twoSection', 'min_score' => 0, 'max_score' => 100],
                ],
            ],
            [
                'name' => 'برنامج إدارة الجودة الشاملة',
                'description' => 'برنامج متقدم في أنظمة إدارة الجودة والتحسين المستمر',
                'definition' => 'يركز على تطبيق مفاهيم الجودة الشاملة وأدوات التحسين المستمر في بيئة العمل',
                'objectives' => 'إتقان أدوات إدارة الجودة وتطبيق معايير التميز المؤسسي',
                'methodology_modules' => [
                    ['methodology_type' => 'complex', 'min_score' => 0, 'max_score' => 100],
                ],
            ],
            [
                'name' => 'برنامج التسويق الرقمي المتقدم',
                'description' => 'برنامج شامل في استراتيجيات التسويق الرقمي والتجارة الإلكترونية',
                'definition' => 'يغطي أحدث استراتيجيات التسويق الرقمي ووسائل التواصل الاجتماعي',
                'objectives' => 'تطوير مهارات التسويق الرقمي وإدارة الحملات الإعلانية الإلكترونية',
                'methodology_modules' => [
                    ['methodology_type' => 'simple', 'min_score' => 0, 'max_score' => 100],
                    ['methodology_type' => 'twoSection', 'min_score' => 0, 'max_score' => 100],
                ],
            ],
        ];

        // Step types and their Arabic data
        $stepTypes = [
            'journal' => [
                'names' => [
                    'يوميات التطوير المهني',
                    'تسجيل رحلة التعلم',
                    'مذكرات الخبرات العملية',
                    'يوميات الإنجازات',
                ],
                'data' => [
                    'header' => 'اكتب تأملاتك اليومية حول رحلة التعلم',
                ],
                'time' => [1, 1, 2, 2],
                'time_type' => 'hours',
            ],
            'article' => [
                'names' => [
                    'مقال حول أفضل الممارسات',
                    'تحليل دراسة حالة',
                    'مراجعة الأدبيات المتخصصة',
                    'مقال تحليلي متخصص',
                ],
                'data' => [
                    'content' => 'اقرأ المقال المرفق وقم بتحليل النقاط الرئيسية والاستفادة من المعلومات المقدمة',
                ],
                'time' => [1, 2, 2],
                'time_type' => 'hours',
            ],
            'advice' => [
                'names' => [
                    'نصائح الخبراء',
                    'إرشادات عملية للتطبيق',
                    'توصيات المتخصصين',
                    'نصائح للتطوير المهني',
                ],
                'data' => [
                    'header' => 'نصائح قيمة للنجاح في المجال',
                    'advices' => [
                        'ابدأ بوضع أهداف واضحة ومحددة',
                        'اعتمد على التعلم المستمر والتطوير الذاتي',
                        'بناء شبكة علاقات مهنية قوية',
                        'المثابرة والصبر في تحقيق الأهداف',
                        'الاستفادة من التغذية الراجعة البناءة',
                    ],
                ],
                'time' => [1, 1, 1],
                'time_type' => 'hours',
            ],
            'daily_mission' => [
                'names' => [
                    'مهمة يومية للتطبيق',
                    'تحدي يومي للتطوير',
                    'مهمة عملية للممارسة',
                    'نشاط يومي للنمو',
                ],
                'data' => [
                    'header' => 'مهمتك لهذا اليوم',
                    'content' => 'قم بتطبيق إحدى المهارات التي تعلمتها في موقف عملي واقعي وسجل النتائج',
                ],
                'time' => [1, 2, 2],
                'time_type' => 'hours',
            ],
            'quiz' => [
                'names' => [
                    'اختبار الفهم والاستيعاب',
                    'تقييم المعرفة المكتسبة',
                    'اختبار المهارات العملية',
                    'تقييم شامل للمحتوى',
                ],
                'data' => [
                    'questions' => [1, 2, 3, 4, 5], // Mock question IDs
                ],
                'time' => [1, 1, 2],
                'time_type' => 'hours',
            ],
            'video' => [
                'names' => [
                    'فيديو تعليمي متخصص',
                    'محاضرة مرئية للخبراء',
                    'شرح مفصل بالفيديو',
                    'ورشة عمل مصورة',
                ],
                'data' => [
                    'content_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'description' => 'فيديو تعليمي يغطي المفاهيم الأساسية والتطبيقات العملية للموضوع',
                ],
                'time' => [1, 2, 2, 3],
                'time_type' => 'hours',
            ],
            'audio' => [
                'names' => [
                    'بودكاست متخصص',
                    'محاضرة صوتية',
                    'مقابلة مع خبير',
                    'جلسة حوارية صوتية',
                ],
                'data' => [
                    'content_url' => 'https://soundcloud.com/example/audio-lesson',
                    'description' => 'محتوى صوتي يقدم رؤى متعمقة من خبراء المجال',
                ],
                'time' => [1, 2, 2],
                'time_type' => 'hours',
            ],
            'book' => [
                'names' => [
                    'كتاب مرجعي متخصص',
                    'دليل شامل للممارسة',
                    'مرجع علمي معتمد',
                    'كتيب الخبير المتخصص',
                ],
                'data' => [
                    'content_url' => 'https://example.com/book.pdf',
                    'cover_image' => 'https://picsum.photos/seed/book/300/400',
                    'description' => 'مرجع شامل يغطي جميع جوانب الموضوع بتفصيل علمي ودقيق',
                ],
                'time' => [2, 3, 4, 5],
                'time_type' => 'hours',
            ],
            'challenge' => [
                'names' => [
                    'تحدي الثلاثين يومًا',
                    'برنامج التطوير المكثف',
                    'تحدي المهارات العملية',
                    'مشروع التطبيق الشامل',
                ],
                'data' => [
                    'description' => 'تحدي شامل لتطبيق جميع المهارات المكتسبة في مشروع عملي',
                    'challenges' => [
                        'أسبوع 1: التخطيط ووضع الاستراتيجية',
                        'أسبوع 2: تنفيذ المرحلة الأولى من المشروع',
                        'أسبوع 3: المراجعة والتطوير والتحسين',
                        'أسبوع 4: العرض النهائي وقياس النتائج',
                    ],
                ],
                'time' => [4, 6, 8],
                'time_type' => 'weeks',
            ],
        ];

        $createdPrograms = [];

        // Create programs
        foreach ($programsData as $programData) {
            $program = Program::create([
                'name' => $programData['name'],
                'description' => $programData['description'],
                'definition' => $programData['definition'],
                'objectives' => $programData['objectives'],
            ]);

            $createdPrograms[] = [
                'program' => $program,
                'modules_config' => $programData['methodology_modules'],
            ];
        }

        $this->command->info('Created '.count($createdPrograms).' programs');

        // Link programs to methodology modules
        foreach ($createdPrograms as $programInfo) {
            $program = $programInfo['program'];
            $modulesConfig = $programInfo['modules_config'];

            foreach ($modulesConfig as $config) {
                $methodologyType = $config['methodology_type'];
                $minScore = $config['min_score'];
                $maxScore = $config['max_score'];

                // Find methodology by type
                $methodology = $methodologies->where('type', $methodologyType)->first();

                if (! $methodology) {
                    continue;
                }

                // Link direct methodology modules (for simple methodology)
                if ($methodology->type === 'simple') {
                    foreach ($methodology->modules as $module) {
                        // Check if this combination already exists
                        $exists = DB::table('program_module')
                            ->where('program_id', $program->id)
                            ->where('module_id', $module->id)
                            ->where('methodology_id', $methodology->id)
                            ->whereNull('pillar_id')
                            ->exists();

                        if (! $exists) {
                            DB::table('program_module')->insert([
                                'program_id' => $program->id,
                                'module_id' => $module->id,
                                'methodology_id' => $methodology->id,
                                'pillar_id' => null,
                                'min_score' => $minScore,
                                'max_score' => $maxScore,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }

                // Link pillar modules (for complex and twoSection methodologies)
                if (in_array($methodology->type, ['complex', 'twoSection'])) {
                    foreach ($methodology->pillars as $pillar) {
                        foreach ($pillar->modules as $module) {
                            // Check if this module belongs to this pillar in this methodology
                            $moduleExists = DB::table('pillar_module')
                                ->where('methodology_id', $methodology->id)
                                ->where('pillar_id', $pillar->id)
                                ->where('module_id', $module->id)
                                ->exists();

                            if ($moduleExists) {
                                // Check if this combination already exists in program_module
                                $programModuleExists = DB::table('program_module')
                                    ->where('program_id', $program->id)
                                    ->where('module_id', $module->id)
                                    ->where('methodology_id', $methodology->id)
                                    ->where('pillar_id', $pillar->id)
                                    ->exists();

                                if (! $programModuleExists) {
                                    DB::table('program_module')->insert([
                                        'program_id' => $program->id,
                                        'module_id' => $module->id,
                                        'methodology_id' => $methodology->id,
                                        'pillar_id' => $pillar->id,
                                        'min_score' => $minScore,
                                        'max_score' => $maxScore,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        // Create steps for each program
        foreach ($createdPrograms as $index => $programInfo) {
            $program = $programInfo['program'];

            // Create at least one step from each type
            foreach ($stepTypes as $type => $typeData) {
                $name = $typeData['names'][array_rand($typeData['names'])];
                $time = $typeData['time'][array_rand($typeData['time'])];
                $data = $typeData['data'];

                Step::create([
                    'program_id' => $program->id,
                    'name' => $name,
                    'type' => $type,
                    'time_to_finish' => $time,
                    'time_type' => $typeData['time_type'],
                    // Individual columns
                    'header' => $data['header'] ?? null,
                    'content' => $data['content'] ?? null,
                    'description' => $data['description'] ?? null,
                    'content_url' => $data['content_url'] ?? null,
                    'content_image' => $data['cover_image'] ?? null,
                    'advices' => $data['advices'] ?? [],
                    'challenges' => $data['challenges'] ?? [],
                ]);
            }

            // Add a few more random steps for variety
            $extraSteps = rand(2, 4);
            for ($i = 0; $i < $extraSteps; $i++) {
                $randomType = array_rand($stepTypes);
                $typeData = $stepTypes[$randomType];
                $name = $typeData['names'][array_rand($typeData['names'])];
                $time = $typeData['time'][array_rand($typeData['time'])];
                $data = $typeData['data'];

                Step::create([
                    'program_id' => $program->id,
                    'name' => $name,
                    'type' => $randomType,
                    'time_to_finish' => $time,
                    'time_type' => $typeData['time_type'],
                    // Individual columns
                    'header' => $data['header'] ?? null,
                    'content' => $data['content'] ?? null,
                    'description' => $data['description'] ?? null,
                    'content_url' => $data['content_url'] ?? null,
                    'content_image' => $data['cover_image'] ?? null,
                    'advices' => $data['advices'] ?? [],
                    'challenges' => $data['challenges'] ?? [],
                ]);
            }
        }

        $this->command->info('Successfully created programs with steps and module relationships:');

        // Display summary
        $totalSteps = Step::count();
        $totalProgramModules = DB::table('program_module')->count();

        foreach ($createdPrograms as $programInfo) {
            $program = $programInfo['program'];
            $stepCount = Step::where('program_id', $program->id)->count();
            $moduleCount = DB::table('program_module')->where('program_id', $program->id)->count();

            $this->command->line("• {$program->name}: {$stepCount} steps, {$moduleCount} modules");
        }

        $this->command->info("Total: {$totalSteps} steps, {$totalProgramModules} program-module relationships");
    }
}
