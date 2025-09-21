<?php

namespace Database\Seeders;

use App\Models\Enrichment;
use App\Models\Interest;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class EnrichmentSeeder extends Seeder
{
    public function run(): void
    {
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

        // Articles (content filled)
        Enrichment::create([
            'title' => 'فوائد الرياضة الصباحية لصحة الجسم والعقل',
            'description' => 'تعرف على الفوائد المذهلة للرياضة الصباحية وكيف تؤثر إيجابياً على صحتك الجسدية والنفسية',
            'category' => 'health',
            'type' => 'article',
            'img_url' => 'https://example.com/images/morning-exercise.jpg',
            'header' => 'الرياضة الصباحية: بداية مثالية ليوم صحي ومفعم بالنشاط',
            'content' => 'تعتبر ممارسة الرياضة في الصباح من أفضل العادات التي يمكن أن تبدأ بها يومك. فعندما تنهض من نومك وتبدأ في تحريك جسدك، فإنك تحفز الدورة الدموية وتزيد من إفراز الهرمونات الإيجابية مثل الإندورفين، مما يجعلك تشعر بالنشاط والحيوية طوال اليوم. كما أن الرياضة الصباحية تساعد في تحسين التركيز والذاكرة، وتقلل من مستويات التوتر والقلق. بالإضافة إلى ذلك، فإن ممارسة الرياضة بانتظام في الصباح تساعد في تنظيم الشهية والحفاظ على وزن صحي.',
            'content_url' => null,
            'content_image' => null,
            'interests' => array_slice($interestIds, 0, 3),
            'tags' => [$tagIds[0], $tagIds[1], $tagIds[2]], // تطوير الذات، اللياقة البدنية، التغذية الصحية
        ]);

        Enrichment::create([
            'title' => 'كيفية بناء صندوق الطوارئ المالي',
            'description' => 'دليل شامل لبناء صندوق الطوارئ المالي وأهميته في تحقيق الاستقرار المالي',
            'category' => 'money',
            'type' => 'article',
            'img_url' => 'https://example.com/images/emergency-fund.jpg',
            'header' => 'صندوق الطوارئ: درعك المالي ضد الأزمات غير المتوقعة',
            'content' => 'يعد صندوق الطوارئ المالي أحد أهم الأسس للاستقرار المالي. يجب أن يغطي هذا الصندوق نفقات 3-6 أشهر من المعيشة الأساسية. ابدأ بوضع هدف صغير مثل 500 ريال، ثم زد المبلغ تدريجياً. اختر حساباً منفصلاً يسهل الوصول إليه عند الحاجة، لكن ليس سهلاً لدرجة الإغراء بالإنفاق منه. تذكر أن صندوق الطوارئ مخصص للحالات الطارئة الحقيقية فقط مثل فقدان الوظيفة أو المصاريف الطبية غير المتوقعة. بناء هذا الصندوق يمنحك راحة البال ويحميك من الديون عند مواجهة الأزمات.',
            'content_url' => null,
            'content_image' => null,
            'interests' => array_slice($interestIds, 1, 2),
            'tags' => [$tagIds[4], $tagIds[5], $tagIds[7]], // الاستثمار، الادخار، التعليم المالي
        ]);

        // Videos (content_url filled)
        Enrichment::create([
            'title' => 'محاضرة: فن التعامل مع الأطفال في العصر الرقمي',
            'description' => 'محاضرة مفيدة حول كيفية تربية الأطفال وتوجيههم في ظل التطور التكنولوجي',
            'category' => 'family',
            'type' => 'video',
            'img_url' => 'https://example.com/images/digital-parenting.jpg',
            'header' => null,
            'content' => null,
            'content_url' => 'https://youtube.com/watch?v=digital-parenting-lecture',
            'content_image' => null,
            'interests' => array_slice($interestIds, 2, 2),
            'tags' => [$tagIds[8], $tagIds[11]], // تربية الأطفال، التوازن الأسري
        ]);

        Enrichment::create([
            'title' => 'دورة الاستثمار للمبتدئين - الحلقة الأولى',
            'description' => 'تعلم أساسيات الاستثمار وكيفية بناء محفظة استثمارية متوازنة',
            'category' => 'money',
            'type' => 'video',
            'img_url' => 'https://example.com/images/investment-basics.jpg',
            'header' => null,
            'content' => null,
            'content_url' => 'https://youtube.com/watch?v=investment-course-ep1',
            'content_image' => null,
            'interests' => array_slice($interestIds, 0, 2),
            'tags' => [$tagIds[4], $tagIds[6], $tagIds[7]], // الاستثمار، ريادة الأعمال، التعليم المالي
        ]);

        // Audio (content_url filled)
        Enrichment::create([
            'title' => 'بودكاست: رحلة في أسرار الذكر والتسبيح',
            'description' => 'حلقة بودكاست روحانية تتحدث عن فضائل الذكر وأثره على النفس',
            'category' => 'spirituality',
            'type' => 'audio',
            'img_url' => 'https://example.com/images/dhikr-podcast.jpg',
            'header' => null,
            'content' => null,
            'content_url' => 'https://soundcloud.com/dhikr-journey-podcast',
            'content_image' => null,
            'interests' => array_slice($interestIds, 3, 2),
            'tags' => [$tagIds[14], $tagIds[15]], // الذكر والدعاء، الأخلاق الإسلامية
        ]);

        Enrichment::create([
            'title' => 'بودكاست: أسرار الصحة النفسية والسعادة',
            'description' => 'حلقة مفيدة عن كيفية الحفاظ على الصحة النفسية وتحقيق السعادة الداخلية',
            'category' => 'health',
            'type' => 'audio',
            'img_url' => 'https://example.com/images/mental-health-podcast.jpg',
            'header' => null,
            'content' => null,
            'content_url' => 'https://spotify.com/episode/mental-health-happiness',
            'content_image' => null,
            'interests' => array_slice($interestIds, 1, 3),
            'tags' => [$tagIds[0], $tagIds[3]], // تطوير الذات، الصحة النفسية
        ]);

        // Books (content_url and content_image filled)
        Enrichment::create([
            'title' => 'كتاب: دليل الأسرة المسلمة في التربية الإيجابية',
            'description' => 'كتاب شامل يقدم استراتيجيات عملية للتربية الإيجابية وفق المنهج الإسلامي',
            'category' => 'family',
            'type' => 'book',
            'img_url' => 'https://example.com/images/positive-parenting-book.jpg',
            'header' => null,
            'content' => null,
            'content_url' => 'https://archive.org/details/positive-parenting-islamic-guide',
            'content_image' => 'https://example.com/images/positive-parenting-book-cover.jpg',
            'interests' => array_slice($interestIds, 2, 3),
            'tags' => [$tagIds[8], $tagIds[9], $tagIds[15]], // تربية الأطفال، العلاقات الزوجية، الأخلاق الإسلامية
        ]);

        Enrichment::create([
            'title' => 'كتاب: الثراء الحلال - طريقك إلى الاستقلال المالي',
            'description' => 'كتاب يوضح كيفية بناء الثروة بطرق مشروعة ووفق الضوابط الشرعية',
            'category' => 'money',
            'type' => 'book',
            'img_url' => 'https://example.com/images/halal-wealth-book.jpg',
            'header' => null,
            'content' => null,
            'content_url' => 'https://books.google.com/halal-wealth-building',
            'content_image' => 'https://example.com/images/halal-wealth-book-cover.jpg',
            'interests' => array_slice($interestIds, 0, 2),
            'tags' => [$tagIds[4], $tagIds[5], $tagIds[6]], // الاستثمار، الادخار، ريادة الأعمال
        ]);

        // Short Videos (content_url filled)
        Enrichment::create([
            'title' => 'نصائح سريعة: 5 دقائق لصحة أفضل',
            'description' => 'فيديو قصير يقدم نصائح بسيطة يمكن تطبيقها يومياً لتحسين الصحة',
            'category' => 'health',
            'type' => 'short-video',
            'img_url' => 'https://example.com/images/health-tips-short.jpg',
            'header' => null,
            'content' => null,
            'content_url' => 'https://youtube.com/shorts/5min-health-tips',
            'content_image' => null,
            'interests' => array_slice($interestIds, 1, 2),
            'tags' => [$tagIds[1], $tagIds[2], $tagIds[3]], // اللياقة البدنية، التغذية الصحية، الصحة النفسية
        ]);

        Enrichment::create([
            'title' => 'فيديو قصير: آية وحكمة - التوكل على الله',
            'description' => 'فيديو روحاني قصير يتحدث عن معنى التوكل على الله وأثره في حياة المؤمن',
            'category' => 'spirituality',
            'type' => 'short-video',
            'img_url' => 'https://example.com/images/tawakkul-short.jpg',
            'header' => null,
            'content' => null,
            'content_url' => 'https://youtube.com/shorts/tawakkul-wisdom',
            'content_image' => null,
            'interests' => array_slice($interestIds, 3, 1),
            'tags' => [$tagIds[13], $tagIds[14], $tagIds[15]], // القرآن الكريم، الذكر والدعاء، الأخلاق الإسلامية
        ]);

        Enrichment::create([
            'title' => 'لحظة تأمل: قوة العلاقات الأسرية',
            'description' => 'فيديو قصير يسلط الضوء على أهمية تقوية الروابط الأسرية',
            'category' => 'family',
            'type' => 'short-video',
            'img_url' => 'https://example.com/images/family-bonds-short.jpg',
            'header' => null,
            'content' => null,
            'content_url' => 'https://instagram.com/reel/family-strength',
            'content_image' => null,
            'interests' => array_slice($interestIds, 2, 2),
            'tags' => [$tagIds[9], $tagIds[10], $tagIds[11]], // العلاقات الزوجية، صلة الأرحام، التوازن الأسري
        ]);

        // Additional articles for better content variety
        Enrichment::create([
            'title' => 'طرق تعزيز الروابط الأسرية في زمن التقنية',
            'description' => 'مقال يقدم حلول عملية لتقوية العلاقات الأسرية رغم انشغال العصر الحديث',
            'category' => 'family',
            'type' => 'article',
            'img_url' => 'https://example.com/images/family-tech-balance.jpg',
            'header' => 'الأسرة أولاً: كيف نوازن بين التقنية والعلاقات الإنسانية',
            'content' => 'في عصر تهيمن فيه التقنية على معظم جوانب حياتنا، أصبح من الضروري إيجاد توازن صحي بين استخدام الأجهزة الذكية والتفاعل الحقيقي مع أفراد الأسرة. يمكن للعائلات تخصيص أوقات خالية من الهواتف للحديث والتفاعل، كما يمكن استغلال التقنية نفسها لتقوية الروابط من خلال الألعاب التعليمية الجماعية أو مشاهدة محتوى مفيد معاً. المهم هو وضع قواعد واضحة للاستخدام والالتزام بها من جميع أفراد الأسرة.',
            'content_url' => null,
            'content_image' => null,
            'interests' => array_slice($interestIds, 2, 3),
            'tags' => [$tagIds[8], $tagIds[10], $tagIds[11]], // تربية الأطفال، صلة الأرحام، التوازن الأسري
        ]);

        Enrichment::create([
            'title' => 'فهم أساسيات الصيام الصحي والآمن',
            'description' => 'دليل طبي لممارسة الصيام بطريقة صحية وآمنة مع نصائح غذائية مهمة',
            'category' => 'health',
            'type' => 'article',
            'img_url' => 'https://example.com/images/healthy-fasting.jpg',
            'header' => 'الصيام الصحي: بين الفوائد الروحية والجسدية',
            'content' => 'الصيام ليس فقط عبادة روحية، بل له فوائد علمية مثبتة للصحة الجسدية والنفسية. عند ممارسة الصيام، يجب التأكد من تناول وجبة سحور متوازنة تحتوي على البروتين والألياف، وتجنب الإفراط في الطعام عند الإفطار. شرب كميات كافية من الماء خلال ساعات الإفطار أمر ضروري. كما يُنصح بممارسة رياضة خفيفة قبل الإفطار بساعة. الأشخاص الذين يعانون من حالات صحية خاصة يجب عليهم استشارة الطبيب قبل الصيام.',
            'content_url' => null,
            'content_image' => null,
            'interests' => array_slice($interestIds, 1, 2),
            'tags' => [$tagIds[2], $tagIds[3], $tagIds[12]], // التغذية الصحية، الصحة النفسية، الصلاة
        ]);
    }
}
