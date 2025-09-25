<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Enrichment;
use App\Models\Interest;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnrichmentSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key constraints temporarily
        Schema::disableForeignKeyConstraints();

        // Clear existing data and reset auto-increment
        DB::table('user_enrichments')->truncate();
        DB::table('enrichments')->truncate();

        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();

        // First, create some tags if they don't exist
        $tags = [
            'تطوير الذات',
            'اللياقة البدنية',
            'التغذية الصحية',
            'الصحة النفسية',
            'الاستثمار',
            'الادخار',
            'ريادة الأعمال',
            'التعليم المالي',
            'تربية الأطفال',
            'العلاقات الزوجية',
            'صلة الأرحام',
            'التوازن الأسري',
            'الصلاة',
            'القرآن الكريم',
            'الذكر والدعاء',
            'الأخلاق الإسلامية',
        ];

        $tagIds = [];
        foreach ($tags as $tagTitle) {
            $tag = Tag::firstOrCreate(['title' => $tagTitle], ['active' => true]);
            $tagIds[] = $tag->id;
        }

        // Get existing interest IDs
        $interestIds = Interest::pluck('id')->toArray();

        // Create categories
        $categories = [
            ['name' => 'الصحة', 'icon' => 'https://picsum.photos/64/64?random=health'],
            ['name' => 'المال', 'icon' => 'https://picsum.photos/64/64?random=money'],
            ['name' => 'الأسرة', 'icon' => 'https://picsum.photos/64/64?random=family'],
            ['name' => 'الروحانية', 'icon' => 'https://picsum.photos/64/64?random=spirituality'],
        ];

        $categoryIds = [];
        foreach ($categories as $categoryData) {
            $category = Category::firstOrCreate(
                ['name' => $categoryData['name']],
                [
                    'icon' => $categoryData['icon'],
                    'active' => true,
                ]
            );
            $categoryIds[] = $category->id;
        }

        // Helper function to randomly select 1-3 categories
        $getRandomCategories = function () use ($categoryIds) {
            $numCategories = rand(1, 3);
            $shuffledIds = $categoryIds;
            shuffle($shuffledIds);

            return array_slice($shuffledIds, 0, $numCategories);
        };

        // Articles (content filled)
        Enrichment::create([
            'title' => 'فوائد الرياضة الصباحية لصحة الجسم والعقل',
            'description' => 'تعرف على الفوائد المذهلة للرياضة الصباحية وكيف تؤثر إيجابياً على صحتك الجسدية والنفسية',
            'type' => 'article',
            'img_url' => 'https://picsum.photos/600/400?random=1',
            'header' => 'الرياضة الصباحية: بداية مثالية ليوم صحي ومفعم بالنشاط',
            'content' => 'تعتبر ممارسة الرياضة في الصباح من أفضل العادات التي يمكن أن تبدأ بها يومك. فعندما تنهض من نومك وتبدأ في تحريك جسدك، فإنك تحفز الدورة الدموية وتزيد من إفراز الهرمونات الإيجابية مثل الإندورفين، مما يجعلك تشعر بالنشاط والحيوية طوال اليوم. كما أن الرياضة الصباحية تساعد في تحسين التركيز والذاكرة، وتقلل من مستويات التوتر والقلق. بالإضافة إلى ذلك، فإن ممارسة الرياضة بانتظام في الصباح تساعد في تنظيم الشهية والحفاظ على وزن صحي.',
            'content_url' => null,
            'content_image' => null,
            'interests' => array_slice($interestIds, 0, 3),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[0], $tagIds[1], $tagIds[2]], // تطوير الذات، اللياقة البدنية، التغذية الصحية
        ]);

        Enrichment::create([
            'title' => 'كيفية بناء صندوق الطوارئ المالي',
            'description' => 'دليل شامل لبناء صندوق الطوارئ المالي وأهميته في تحقيق الاستقرار المالي',
            'type' => 'article',
            'img_url' => 'https://picsum.photos/600/400?random=2',
            'header' => 'صندوق الطوارئ: درعك المالي ضد الأزمات غير المتوقعة',
            'content' => 'يعد صندوق الطوارئ المالي أحد أهم الأسس للاستقرار المالي. يجب أن يغطي هذا الصندوق نفقات 3-6 أشهر من المعيشة الأساسية. ابدأ بوضع هدف صغير مثل 500 ريال، ثم زد المبلغ تدريجياً. اختر حساباً منفصلاً يسهل الوصول إليه عند الحاجة، لكن ليس سهلاً لدرجة الإغراء بالإنفاق منه. تذكر أن صندوق الطوارئ مخصص للحالات الطارئة الحقيقية فقط مثل فقدان الوظيفة أو المصاريف الطبية غير المتوقعة. بناء هذا الصندوق يمنحك راحة البال ويحميك من الديون عند مواجهة الأزمات.',
            'content_url' => null,
            'content_image' => null,
            'interests' => array_slice($interestIds, 1, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[4], $tagIds[5], $tagIds[7]], // الاستثمار، الادخار، التعليم المالي
        ]);

        // Videos (content_url filled)
        Enrichment::create([
            'title' => 'محاضرة: فن التعامل مع الأطفال في العصر الرقمي',
            'description' => 'محاضرة مفيدة حول كيفية تربية الأطفال وتوجيههم في ظل التطور التكنولوجي',
            'type' => 'video',
            'img_url' => 'https://picsum.photos/600/400?random=3',
            'header' => null,
            'content' => null,
            'content_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
            'content_image' => null,
            'interests' => array_slice($interestIds, 2, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[8], $tagIds[11]], // تربية الأطفال، التوازن الأسري
        ]);

        Enrichment::create([
            'title' => 'دورة الاستثمار للمبتدئين - الحلقة الأولى',
            'description' => 'تعلم أساسيات الاستثمار وكيفية بناء محفظة استثمارية متوازنة',
            'type' => 'video',
            'img_url' => 'https://picsum.photos/600/400?random=4',
            'header' => null,
            'content' => null,
            'content_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
            'content_image' => null,
            'interests' => array_slice($interestIds, 0, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[4], $tagIds[6], $tagIds[7]], // الاستثمار، ريادة الأعمال، التعليم المالي
        ]);

        // Audio (content_url filled)
        Enrichment::create([
            'title' => 'بودكاست: رحلة في أسرار الذكر والتسبيح',
            'description' => 'حلقة بودكاست روحانية تتحدث عن فضائل الذكر وأثره على النفس',
            'type' => 'audio',
            'img_url' => 'https://picsum.photos/600/400?random=5',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3',
            'content_image' => null,
            'interests' => array_slice($interestIds, 3, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[14], $tagIds[15]], // الذكر والدعاء، الأخلاق الإسلامية
        ]);

        Enrichment::create([
            'title' => 'بودكاست: أسرار الصحة النفسية والسعادة',
            'description' => 'حلقة مفيدة عن كيفية الحفاظ على الصحة النفسية وتحقيق السعادة الداخلية',
            'type' => 'audio',
            'img_url' => 'https://picsum.photos/600/400?random=6',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3',
            'content_image' => null,
            'interests' => array_slice($interestIds, 1, 3),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[0], $tagIds[3]], // تطوير الذات، الصحة النفسية
        ]);

        // Books (content_url and content_image filled)
        Enrichment::create([
            'title' => 'كتاب: دليل الأسرة المسلمة في التربية الإيجابية',
            'description' => 'كتاب شامل يقدم استراتيجيات عملية للتربية الإيجابية وفق المنهج الإسلامي',
            'type' => 'book',
            'img_url' => 'https://picsum.photos/600/400?random=7',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.junkybooks.com/administrator/thebooks/68c8262133e8c-the-working-student-a-comprehensive-guide-of-a-working-student.pdf',
            'content_image' => 'https://picsum.photos/300/400?random=book1',
            'interests' => array_slice($interestIds, 2, 3),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[8], $tagIds[9], $tagIds[15]], // تربية الأطفال، العلاقات الزوجية، الأخلاق الإسلامية
        ]);

        Enrichment::create([
            'title' => 'كتاب: الثراء الحلال - طريقك إلى الاستقلال المالي',
            'description' => 'كتاب يوضح كيفية بناء الثروة بطرق مشروعة ووفق الضوابط الشرعية',
            'type' => 'book',
            'img_url' => 'https://picsum.photos/600/400?random=8',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.junkybooks.com/administrator/thebooks/68c8262133e8c-the-working-student-a-comprehensive-guide-of-a-working-student.pdf',
            'content_image' => 'https://picsum.photos/300/400?random=book2',
            'interests' => array_slice($interestIds, 0, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[4], $tagIds[5], $tagIds[6]], // الاستثمار، الادخار، ريادة الأعمال
        ]);

        // Short Videos (content_url filled)
        Enrichment::create([
            'title' => 'نصائح سريعة: 5 دقائق لصحة أفضل',
            'description' => 'فيديو قصير يقدم نصائح بسيطة يمكن تطبيقها يومياً لتحسين الصحة',
            'type' => 'short-video',
            'img_url' => 'https://picsum.photos/600/400?random=9',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/05/sample-mp4-file.mp4',
            'content_image' => null,
            'interests' => array_slice($interestIds, 1, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[1], $tagIds[2], $tagIds[3]], // اللياقة البدنية، التغذية الصحية، الصحة النفسية
        ]);

        Enrichment::create([
            'title' => 'فيديو قصير: آية وحكمة - التوكل على الله',
            'description' => 'فيديو روحاني قصير يتحدث عن معنى التوكل على الله وأثره في حياة المؤمن',
            'type' => 'short-video',
            'img_url' => 'https://picsum.photos/600/400?random=10',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/05/sample-mp4-file.mp4',
            'content_image' => null,
            'interests' => array_slice($interestIds, 3, 1),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[13], $tagIds[14], $tagIds[15]], // القرآن الكريم، الذكر والدعاء، الأخلاق الإسلامية
        ]);

        Enrichment::create([
            'title' => 'لحظة تأمل: قوة العلاقات الأسرية',
            'description' => 'فيديو قصير يسلط الضوء على أهمية تقوية الروابط الأسرية',
            'type' => 'short-video',
            'img_url' => 'https://picsum.photos/600/400?random=11',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/05/sample-mp4-file.mp4',
            'content_image' => null,
            'interests' => array_slice($interestIds, 2, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[9], $tagIds[10], $tagIds[11]], // العلاقات الزوجية، صلة الأرحام، التوازن الأسري
        ]);

        // Additional articles for better content variety
        Enrichment::create([
            'title' => 'طرق تعزيز الروابط الأسرية في زمن التقنية',
            'description' => 'مقال يقدم حلول عملية لتقوية العلاقات الأسرية رغم انشغال العصر الحديث',
            'type' => 'article',
            'img_url' => 'https://picsum.photos/600/400?random=12',
            'header' => 'الأسرة أولاً: كيف نوازن بين التقنية والعلاقات الإنسانية',
            'content' => 'في عصر تهيمن فيه التقنية على معظم جوانب حياتنا، أصبح من الضروري إيجاد توازن صحي بين استخدام الأجهزة الذكية والتفاعل الحقيقي مع أفراد الأسرة. يمكن للعائلات تخصيص أوقات خالية من الهواتف للحديث والتفاعل، كما يمكن استغلال التقنية نفسها لتقوية الروابط من خلال الألعاب التعليمية الجماعية أو مشاهدة محتوى مفيد معاً. المهم هو وضع قواعد واضحة للاستخدام والالتزام بها من جميع أفراد الأسرة.',
            'content_url' => null,
            'content_image' => null,
            'interests' => array_slice($interestIds, 2, 3),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[8], $tagIds[10], $tagIds[11]], // تربية الأطفال، صلة الأرحام، التوازن الأسري
        ]);

        Enrichment::create([
            'title' => 'فهم أساسيات الصيام الصحي والآمن',
            'description' => 'دليل طبي لممارسة الصيام بطريقة صحية وآمنة مع نصائح غذائية مهمة',
            'type' => 'article',
            'img_url' => 'https://picsum.photos/600/400?random=13',
            'header' => 'الصيام الصحي: بين الفوائد الروحية والجسدية',
            'content' => 'الصيام ليس فقط عبادة روحية، بل له فوائد علمية مثبتة للصحة الجسدية والنفسية. عند ممارسة الصيام، يجب التأكد من تناول وجبة سحور متوازنة تحتوي على البروتين والألياف، وتجنب الإفراط في الطعام عند الإفطار. شرب كميات كافية من الماء خلال ساعات الإفطار أمر ضروري. كما يُنصح بممارسة رياضة خفيفة قبل الإفطار بساعة. الأشخاص الذين يعانون من حالات صحية خاصة يجب عليهم استشارة الطبيب قبل الصيام.',
            'content_url' => null,
            'content_image' => null,
            'interests' => array_slice($interestIds, 1, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[2], $tagIds[3], $tagIds[12]], // التغذية الصحية، الصحة النفسية، الصلاة
        ]);

        // Additional Videos using same working URLs
        Enrichment::create([
            'title' => 'ورشة عمل: استراتيجيات النجاح في ريادة الأعمال',
            'description' => 'ورشة تدريبية شاملة تقدم أهم الاستراتيجيات للنجاح في عالم الأعمال',
            'type' => 'video',
            'img_url' => 'https://picsum.photos/600/400?random=14',
            'header' => null,
            'content' => null,
            'content_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
            'content_image' => null,
            'interests' => array_slice($interestIds, 1, 3),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[6], $tagIds[7], $tagIds[0]], // ريادة الأعمال، التعليم المالي، تطوير الذات
        ]);

        Enrichment::create([
            'title' => 'محاضرة: أهمية الصحة النفسية في بناء الشخصية',
            'description' => 'محاضرة متخصصة تناقش دور الصحة النفسية في تكوين شخصية متوازنة وسليمة',
            'type' => 'video',
            'img_url' => 'https://picsum.photos/600/400?random=15',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/05/sample-mp4-file.mp4',
            'content_image' => null,
            'interests' => array_slice($interestIds, 2, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[3], $tagIds[0], $tagIds[15]], // الصحة النفسية، تطوير الذات، الأخلاق الإسلامية
        ]);

        // Additional Audio content using same working URL
        Enrichment::create([
            'title' => 'بودكاست: رحلة التطوير المهني والشخصي',
            'description' => 'حلقة بودكاست تناقش خطوات التطوير المهني وبناء مسار وظيفي ناجح',
            'type' => 'audio',
            'img_url' => 'https://picsum.photos/600/400?random=16',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3',
            'content_image' => null,
            'interests' => array_slice($interestIds, 0, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[0], $tagIds[6], $tagIds[7]], // تطوير الذات، ريادة الأعمال، التعليم المالي
        ]);

        Enrichment::create([
            'title' => 'بودكاست: فن إدارة الوقت والأولويات',
            'description' => 'حلقة تفاعلية حول كيفية تنظيم الوقت وترتيب الأولويات بفعالية',
            'type' => 'audio',
            'img_url' => 'https://picsum.photos/600/400?random=17',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3',
            'content_image' => null,
            'interests' => array_slice($interestIds, 1, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[0], $tagIds[11]], // تطوير الذات، التوازن الأسري
        ]);

        // Additional Books using same working URL
        Enrichment::create([
            'title' => 'كتاب: أسرار التواصل الفعال مع الآخرين',
            'description' => 'دليل شامل لتطوير مهارات التواصل وبناء علاقات إيجابية مع الآخرين',
            'type' => 'book',
            'img_url' => 'https://picsum.photos/600/400?random=18',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.junkybooks.com/administrator/thebooks/68c8262133e8c-the-working-student-a-comprehensive-guide-of-a-working-student.pdf',
            'content_image' => 'https://picsum.photos/300/400?random=book3',
            'interests' => array_slice($interestIds, 2, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[0], $tagIds[9], $tagIds[10]], // تطوير الذات، العلاقات الزوجية، صلة الأرحام
        ]);

        Enrichment::create([
            'title' => 'كتاب: التربية الإيجابية في الإسلام - منهج متكامل',
            'description' => 'مرجع شامل لأساليب التربية الإيجابية المستمدة من القرآن والسنة النبوية',
            'type' => 'book',
            'img_url' => 'https://picsum.photos/600/400?random=19',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.junkybooks.com/administrator/thebooks/68c8262133e8c-the-working-student-a-comprehensive-guide-of-a-working-student.pdf',
            'content_image' => 'https://picsum.photos/300/400?random=book4',
            'interests' => array_slice($interestIds, 3, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[8], $tagIds[15], $tagIds[13]], // تربية الأطفال، الأخلاق الإسلامية، القرآن الكريم
        ]);

        // Additional Short Videos using same working URL
        Enrichment::create([
            'title' => 'فيديو سريع: 3 عادات يومية لحياة أكثر إنتاجية',
            'description' => 'فيديو قصير يقدم ثلاث عادات بسيطة يمكن تطبيقها لزيادة الإنتاجية اليومية',
            'type' => 'short-video',
            'img_url' => 'https://picsum.photos/600/400?random=20',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/05/sample-mp4-file.mp4',
            'content_image' => null,
            'interests' => array_slice($interestIds, 0, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[0], $tagIds[11]], // تطوير الذات، التوازن الأسري
        ]);

        Enrichment::create([
            'title' => 'نصيحة سريعة: كيف تبدأ يومك بطاقة إيجابية',
            'description' => 'فيديو قصير يوضح كيفية استقبال اليوم بروح إيجابية ونشاط',
            'type' => 'short-video',
            'img_url' => 'https://picsum.photos/600/400?random=21',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/05/sample-mp4-file.mp4',
            'content_image' => null,
            'interests' => array_slice($interestIds, 1, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[0], $tagIds[3], $tagIds[14]], // تطوير الذات، الصحة النفسية، الذكر والدعاء
        ]);

        Enrichment::create([
            'title' => 'دقيقة تأمل: فضل صلاة الفجر في حياة المؤمن',
            'description' => 'فيديو قصير يسلط الضوء على الفوائد الروحية والصحية لصلاة الفجر',
            'type' => 'short-video',
            'img_url' => 'https://picsum.photos/600/400?random=22',
            'header' => null,
            'content' => null,
            'content_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/05/sample-mp4-file.mp4',
            'content_image' => null,
            'interests' => array_slice($interestIds, 3, 1),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[12], $tagIds[14], $tagIds[15]], // الصلاة، الذكر والدعاء، الأخلاق الإسلامية
        ]);

        // Additional Articles for more content variety
        Enrichment::create([
            'title' => 'كيفية بناء عادات صحية تدوم مدى الحياة',
            'description' => 'مقال يوضح الطرق العلمية المؤكدة لبناء عادات صحية والمحافظة عليها',
            'type' => 'article',
            'img_url' => 'https://picsum.photos/600/400?random=23',
            'header' => 'العادات الصحية: رحلة تغيير تبدأ بخطوة واحدة',
            'content' => 'بناء العادات الصحية يتطلب فهماً لآلية عمل الدماغ وكيفية تكوين الروتين. ابدأ بعادة واحدة بسيطة جداً، مثل شرب كوب ماء عند الاستيقاظ. اربط العادة الجديدة بعادة موجودة مسبقاً. حدد مكافأة صغيرة لنفسك عند القيام بالعادة. تذكر أن الأمر يستغرق في المتوسط 66 يوماً لتكوين عادة جديدة. لا تستسلم إذا فشلت يوماً واحداً، بل عد مباشرة في اليوم التالي. التدرج والصبر هما مفتاح النجاح في بناء نمط حياة صحي مستدام.',
            'content_url' => null,
            'content_image' => null,
            'interests' => array_slice($interestIds, 1, 2),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[0], $tagIds[1], $tagIds[2]], // تطوير الذات، اللياقة البدنية، التغذية الصحية
        ]);

        Enrichment::create([
            'title' => 'استراتيجيات الادخار الذكي للأسر الشابة',
            'description' => 'دليل عملي لوضع خطة ادخار مناسبة للأسر حديثة التكوين مع دخل محدود',
            'type' => 'article',
            'img_url' => 'https://picsum.photos/600/400?random=24',
            'header' => 'الادخار الذكي: كيف تبني مستقبلاً مالياً آمناً لأسرتك',
            'content' => 'الادخار للأسر الشابة يتطلب تخطيطاً واقعياً ومرونة في التنفيذ. ابدأ بحساب الدخل والمصروفات الشهرية بدقة. ضع مبلغ الادخار كبند ثابت في الميزانية، حتى لو كان صغيراً. استخدم قاعدة 50/30/20: 50% للضروريات، 30% للرغبات، 20% للادخار. افتح حساب ادخار منفصل بعائد جيد. تجنب الديون غير الضرورية. ابحث عن طرق إضافية للدخل. علم الأطفال قيمة المال منذ الصغر. راجع خطة الادخار كل 6 أشهر وعدلها حسب الظروف.',
            'content_url' => null,
            'content_image' => null,
            'interests' => array_slice($interestIds, 0, 3),
            'categories' => $getRandomCategories(),
            'tags' => [$tagIds[5], $tagIds[7], $tagIds[11]], // الادخار، التعليم المالي، التوازن الأسري
        ]);
    }
}
