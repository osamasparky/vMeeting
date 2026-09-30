<?php

namespace Database\Seeders;

use App\Domains\CMS\Models\CmsMediaAsset;
use App\Domains\CMS\Models\CmsPage;
use App\Domains\CMS\Models\CmsSection;
use App\Domains\CMS\Models\CmsThemeSetting;
use App\Domains\CMS\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class CmsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Theme Settings
        $themeTokens = [
            'color_deep_space' => '#071A16',
            'color_dark_green' => '#0B2922',
            'color_emerald' => '#13A879',
            'color_mint' => '#6FE7C2',
            'color_soft_mint' => '#DDF8EF',
            'color_white' => '#FFFFFF',
            'color_text_dark' => '#10231F',
            'color_text_light' => '#F4FBF7',
            'color_text_muted' => '#8BA69C',
            'font_family_latin' => "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            'font_family_arabic' => "'Cairo', 'Inter', sans-serif",
            'radius_btn' => '12px',
            'radius_card' => '20px',
            'glass_blur' => '24px',
            'glass_bg' => 'rgba(11, 41, 34, 0.72)',
            'glass_border' => 'rgba(111, 231, 194, 0.15)',
        ];

        foreach ($themeTokens as $key => $val) {
            CmsThemeSetting::updateOrCreate(['setting_key' => $key], ['setting_value' => $val]);
        }

        // 2. Seed Feature Flags
        $flags = [
            [
                'flag_key' => 'spatial_audio',
                'name_en' => 'Spatial Proximity Audio & Video',
                'name_ar' => 'الصوت والفيديو المكاني التفاعلي',
                'description_en' => 'Enables proximity-based audio volume attenuation and automatic video bubble popup when avatars approach each other.',
                'description_ar' => 'تفعيل تدرج الصوت التلقائي وظهور الكاميرات عند اقتراب الأفاتار في مساحة العمل.',
                'category' => 'spatial',
                'is_enabled' => true,
            ],
            [
                'flag_key' => 'ai_office_generator',
                'name_en' => 'AI Architectural Office Generator',
                'name_ar' => 'مولد المكاتب المعمارية بالذكاء الاصطناعي',
                'description_en' => 'Allows organizations to generate bespoke 2D architectural office maps using OpenAI DALL-E & GPT Image models.',
                'description_ar' => 'إتاحة توليد خرائط ومخططات معمارية ثنائية الأبعاد للمكاتب باستخدام نماذج OpenAI.',
                'category' => 'ai',
                'is_enabled' => true,
            ],
            [
                'flag_key' => 'whiteboard',
                'name_en' => 'Interactive Collaborative Whiteboard',
                'name_ar' => 'اللوح الأبيض التفاعلي التشاركي',
                'description_en' => 'Enables live brainstorming canvas with freehand drawing, shapes, and sticky notes in virtual rooms.',
                'description_ar' => 'تفعيل لوحة الرسم التشاركي والعصف الذهني المباشر داخل غرف الاجتماعات.',
                'category' => 'collaboration',
                'is_enabled' => true,
            ],
            [
                'flag_key' => 'kanban',
                'name_en' => 'Kanban Workflow & Task Boards',
                'name_ar' => 'لوحات المهام والكانبان التفاعلية',
                'description_en' => 'Enables project management boards with drag-and-drop task progression and assignments.',
                'description_ar' => 'إتاحة إدارة المشروعات وتوزيع المهام بالسحب والإفلات وتعيين المسؤوليات.',
                'category' => 'productivity',
                'is_enabled' => true,
            ],
            [
                'flag_key' => 'time_tracking',
                'name_en' => 'Workplace Attendance & Time Tracking',
                'name_ar' => 'تتبع ساعات العمل والحضور والانصراف',
                'description_en' => 'Tracks clock-in/out logs, active desk presence, and automated weekly timesheets.',
                'description_ar' => 'احتساب ساعات العمل اليومية وحضور الموظفين على المكاتب وإصدار تقارير الإنتاجية.',
                'category' => 'productivity',
                'is_enabled' => true,
            ],
            [
                'flag_key' => 'floorplan_editor',
                'name_en' => 'Custom Map & Floorplan Studio',
                'name_ar' => 'استوديو ومحرر الخرائط المعمارية',
                'description_en' => 'Enables visual zone drawing, soundproofing isolation boxes, and desk placement.',
                'description_ar' => 'إمكانية رسم مناطق العزل الصوتي وتحديد نقاط البداية وتوزيع المقاعد يدوياً.',
                'category' => 'spatial',
                'is_enabled' => true,
            ],
        ];

        foreach ($flags as $f) {
            FeatureFlag::updateOrCreate(['flag_key' => $f['flag_key']], $f);
        }

        // 3. Seed Default Media Asset
        $defaultBlueprint = CmsMediaAsset::updateOrCreate(
            ['name' => 'Default 3D Living Office Floorplan'],
            [
                'asset_type' => 'image',
                'file_path' => '/images/office_floorplan.jpg',
                'dimensions' => '1792x1024',
                'version_tag' => 'hero-office-v1',
                'tags' => ['hero', 'blueprint', 'office', '3d'],
                'is_active' => true,
            ]
        );

        // 4. Seed Homepage & Sections
        $homePage = CmsPage::updateOrCreate(
            ['slug' => 'home'],
            [
                'title_en' => 'NextSpace — Spatial Virtual Workplace & Meetings',
                'title_ar' => 'NextSpace — بيئة العمل الافتراضية المكانية والاجتماعات الذكية',
                'meta_title_en' => 'NextSpace | Spatial Virtual Workplace for Remote Teams',
                'meta_title_ar' => 'NextSpace | بيئة العمل الافتراضية المكانية للشركات وفرق العمل عن بُعد',
                'meta_desc_en' => 'Experience the future of remote work. An interactive 3D spatial virtual office combining proximity audio/video, AI blueprint generation, meetings, and project tools.',
                'meta_desc_ar' => 'مكتب افتراضي مكاني متكامل يجمع بين الصوت والفيديو التفاعلي عند الاقتراب، غرف الاجتماعات، توليد المكاتب بالذكاء الاصطناعي، وإدارة المهام.',
                'status' => 'published',
            ]
        );

        $sections = [
            [
                'section_type' => 'hero_3d',
                'section_key' => 'home_hero',
                'title_en' => 'A more human way to work together.',
                'title_ar' => 'اجمع فريقك في مساحة واحدة ذكية',
                'subtitle_en' => 'Live virtual offices with voice, video and chat. Easy meetings, closer collaboration.',
                'subtitle_ar' => 'مكاتب افتراضية نابضة بالحياة بالصوت والصورة والمحادثات. اجتماعات سهلة، ومشاركة أقرب.',
                'badge_en' => 'Next-Generation Virtual Workplace',
                'badge_ar' => 'الجيل القادم من بيئات العمل الافتراضية',
                'media_asset_id' => $defaultBlueprint->id,
                'display_order' => 1,
                'content' => [
                    'cta_primary_text_en' => 'Enter your space',
                    'cta_primary_text_ar' => 'ادخل إلى مساحتك',
                    'cta_primary_link' => '/register',
                    'cta_secondary_text_en' => 'Watch the demo',
                    'cta_secondary_text_ar' => 'شاهد العرض',
                    'cta_secondary_link' => '#office-preview',
                    'highlights' => [
                        ['icon' => '🎧', 'text_en' => 'Proximity Audio/Video', 'text_ar' => 'صوت وفيديو مكاني تفاعلي'],
                        ['icon' => '🤖', 'text_en' => 'AI Blueprint Studio', 'text_ar' => 'توليد المكاتب بالذكاء الاصطناعي'],
                        ['icon' => '🔒', 'text_en' => 'Acoustic Sound Isolation', 'text_ar' => 'مناطق عزل صوتي للغرف'],
                        ['icon' => '⚡', 'text_en' => 'Zero-Latency WebRTC/SFU', 'text_ar' => 'بث صوتي ومرئي فائق السرعة'],
                    ],
                    'rooms_preview' => [
                        [
                            'id' => 'meeting_room',
                            'title_en' => 'Executive Meeting Room',
                            'title_ar' => 'غرفة الاجتماعات التنفيذية',
                            'desc_en' => 'Soundproof room for 8 members with 4K screen sharing and interactive reactions.',
                            'desc_ar' => 'غرفة معزولة صوتياً لـ 8 أشخاص مع مشاركة شاشة 4K وتفاعلات حية.',
                            'icon' => '📹',
                        ],
                        [
                            'id' => 'open_workspace',
                            'title_en' => 'Open Workspace Area',
                            'title_ar' => 'مساحة العمل المشتركة',
                            'desc_en' => 'Interactive desks with status indicators. Move closer to start organic voice conversations.',
                            'desc_ar' => 'مكاتب تفاعلية مع بيان حالة التواجد. اقترب من زملائك للمحادثة العفوية.',
                            'icon' => '💼',
                        ],
                        [
                            'id' => 'lounge_coffee',
                            'title_en' => 'Team Lounge & Coffee Bar',
                            'title_ar' => 'ردهة الاستراحة وركن القهوة',
                            'desc_en' => 'Relaxed area for casual water-cooler chats and informal team bonding.',
                            'desc_ar' => 'مساحة ودية للأحاديث الجانبية والتواصل الإنساني بين أعضاء الفريق.',
                            'icon' => '☕',
                        ],
                        [
                            'id' => 'focus_booths',
                            'title_en' => 'Private Focus Booths',
                            'title_ar' => 'كبائن التركيز الفردي',
                            'desc_en' => 'Individual soundproof pods with Do Not Disturb mode for deep concentrated work.',
                            'desc_ar' => 'كبائن فردية معزولة مع تفعيل وضع عدم الإزعاج لإنجاز المهام بتركيز.',
                            'icon' => '🎧',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'spatial_presence',
                'section_key' => 'home_spatial_presence',
                'title_en' => 'Conversations that happen naturally.',
                'title_ar' => 'محادثات عفوية ولقاءات تحدث بتلقائية تامة.',
                'subtitle_en' => 'Step away from calendar links. Move your avatar through the 2D workplace and communicate dynamically based on proximity — exactly like a real physical office.',
                'subtitle_ar' => 'ودّع روابط الاجتماعات المزعجة وجداول التقويم المزدحمة. تحرك بأفاتارك داخل المكتب واقترب من زملائك لتتحدث معهم مباشرة بصوت وفيديو فوري.',
                'badge_en' => '🔊 Spatial Proximity Technology',
                'badge_ar' => '🔊 تقنية الصوت والمحاكاة المكانية',
                'display_order' => 2,
                'content' => [
                    'features' => [
                        [
                            'icon' => '🚶‍♂️',
                            'title_en' => 'Proximity-Based Audio Attenuation',
                            'title_ar' => 'تدرج الصوت حسب المسافة',
                            'desc_en' => 'Voices fade in as you approach a colleague and fade out as you walk away, creating a true office ambience.',
                            'desc_ar' => 'يعلو الصوت تدريجياً كلما اقتربت من زميلك وينخفض كلما ابتعدت لمحاكاة الواقع التام.',
                        ],
                        [
                            'icon' => '🚪',
                            'title_en' => 'Acoustic Sound Isolation Zones',
                            'title_ar' => 'مناطق العزل الصوتي الذكي',
                            'desc_en' => 'Step inside closed meeting rooms or private offices and external office noise is completely muted.',
                            'desc_ar' => 'بمجرد دخولك لأي غرفة اجتماعات مقفلة ينعزل الصوت تماماً عن باقي مساحة المكتب المفتوحة.',
                        ],
                        [
                            'icon' => '✊',
                            'title_en' => 'Door Knocking & Privacy Permissions',
                            'title_ar' => 'طرق الباب وطلب الإذن',
                            'desc_en' => 'Lock private rooms with one click. Team members knock on the door and request access before joining.',
                            'desc_ar' => 'قفل الغرف الخاصة وإمكانية طرق الباب لطلب الإذن من المتواجدين بالداخل قبل الانضمام.',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'meetings',
                'section_key' => 'home_meetings',
                'title_en' => 'Meetings without the meeting fatigue.',
                'title_ar' => 'اجتماعات غامرة بدون إرهاق الشاشات التقليدي.',
                'subtitle_en' => 'Crystal-clear HD video calls powered by LiveKit SFU infrastructure, high-definition screen sharing, live chat, and emoji reactions.',
                'subtitle_ar' => 'اتصال صوتي ومرئي فائق الدقة مدعوم بأحدث خوادم LiveKit SFU مع مشاركة الشاشة بدقة عالية وشات تفاعلي مباشر.',
                'badge_en' => '⚡ Enterprise LiveKit WebRTC',
                'badge_ar' => '⚡ بنية تحتية فائقة السرعة للمكالمات',
                'display_order' => 3,
                'content' => [
                    'metrics' => [
                        ['value' => '4K / 60fps', 'label_en' => 'Ultra HD Screen Sharing', 'label_ar' => 'مشاركة شاشة بدقة فائقة'],
                        ['value' => '< 50ms', 'label_en' => 'Ultra-Low Latency', 'label_ar' => 'استجابة صوتية ومرئية فورية'],
                        ['value' => '100%', 'label_en' => 'STUN / TURN Traversal', 'label_ar' => 'تخطي الجدران النارية واستقرار تام'],
                    ],
                ],
            ],
            [
                'section_type' => 'floorplan_editor',
                'section_key' => 'home_floorplan_editor',
                'title_en' => 'A virtual office that feels like your real one.',
                'title_ar' => 'مكتب افتراضي يشبه مكتبك الحقيقي',
                'subtitle_en' => 'Design your floor once, and every teammate walks in to the same place — desks, meeting rooms, lounges, and quiet corners.',
                'subtitle_ar' => 'صمّم طابقك مرة واحدة، وسيدخل كل زميل إلى المكان نفسه: المكاتب وقاعات الاجتماعات والاستراحات والزوايا الهادئة.',
                'badge_en' => 'Smart Spaces',
                'badge_ar' => 'المساحات الذكية',
                'display_order' => 4,
                'content' => [
                    'bullets' => [
                        'Upload custom blueprint JPG, PNG, or WebP floorplans',
                        'Draw custom polygon & rectangular sound isolation boxes',
                        'Set spawn points, desk tags, and room occupancy limits',
                        'Instant live map synchronization via WebSockets without page reload',
                    ],
                ],
            ],
            [
                'section_type' => 'ai_generator',
                'section_key' => 'home_ai_generator',
                'title_en' => 'Describe your office. Let AI build it.',
                'title_ar' => 'صف مكتب أحلامك.. ودع الذكاء الاصطناعي يبنيه في ثوانٍ.',
                'subtitle_en' => 'Turn simple text prompts into production-ready 2D architectural blueprints. Powered by GPT Image 1 Mini and DALL-E with token compression for 95% cost savings.',
                'subtitle_ar' => 'حوّل النصوص الوصفية إلى مخططات معمارية متكاملة بنقرة زر، مع تقنية ضغط التوكنز الفائقة لتوفير 95% من التكلفة لكل توليد.',
                'badge_en' => '🤖 AI Office Blueprint Engine',
                'badge_ar' => '🤖 محرك الذكاء الاصطناعي لتوليد المكاتب',
                'display_order' => 5,
                'content' => [
                    'prompt_example' => 'Modern tech company office for 25 engineers with 2 conference rooms, 4 focus booths, open lounge, and coffee bar.',
                    'prompt_example_ar' => 'مكتب شركة تقنية حديث لـ 25 موظفاً مع غرفتي اجتماعات، 4 كبائن تركيز، مساحة استراحة مفتوحة وركن قهوة.',
                    'cost_badge' => '~$0.015 per generated floorplan',
                    'cost_badge_ar' => 'تكلفة تقارب 0.015$ فقط لكل مخطط مولد',
                ],
            ],
            [
                'section_type' => 'collaboration',
                'section_key' => 'home_collaboration',
                'title_en' => 'Built for modern workplaces that bring heritage and innovation together.',
                'title_ar' => 'مصمم لبيئات العمل الحديثة التي تجمع بين التراث والابتكار',
                'subtitle_en' => 'Everything you need to run a high-trust, collaborative virtual headquarters.',
                'subtitle_ar' => 'كل ما تحتاجه لإدارة مقر افتراضي تعاوني قائم على الثقة.',
                'badge_en' => 'Platform Capabilities',
                'badge_ar' => 'قدرات المنصة',
                'display_order' => 6,
                'content' => [
                    'modules' => [
                        [
                            'title_en' => 'Infinite Collaborative Whiteboard',
                            'title_ar' => 'اللوح الأبيض التشاركي اللانهائي',
                            'desc_en' => 'Real-time drawing, diagrams, and sticky notes for interactive brainstorming.',
                            'desc_ar' => 'رسم تشاركي وتخطيط وملاحظات لاصقة للعصف الذهني في الوقت الفعلي.',
                            'icon' => '🎨',
                        ],
                        [
                            'title_en' => 'Kanban Workflow Task Boards',
                            'title_ar' => 'لوحات المهام والكانبان بالسحب والإفلات',
                            'desc_en' => 'Organize tasks into To-Do, In-Progress, and Done with assigned owners and deadlines.',
                            'desc_ar' => 'تنظيم المشروعات وتوزيع المهام وتحديد الأولويات ومواعيد الإنجاز.',
                            'icon' => '📋',
                        ],
                        [
                            'title_en' => 'Attendance & Time Tracking',
                            'title_ar' => 'تتبع ساعات العمل والحضور الذكي',
                            'desc_en' => 'Automatic desk presence logs, clock-in/out tracking, and exportable weekly timesheets.',
                            'desc_ar' => 'تسجيل الحضور والانصراف وإحصائيات العمل اليومية للموظفين.',
                            'icon' => '⏱️',
                        ],
                        [
                            'title_en' => 'Secure Document & Asset Hub',
                            'title_ar' => 'مكتبة الملفات والمستندات المشتركة',
                            'desc_en' => 'Centralized file repository for company guides, roadmaps, and meeting attachments.',
                            'desc_ar' => 'مستودع مركزي لمشاركة ملفات وأدلة وتصاميم المشروعات.',
                            'icon' => '📁',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'company_workspace',
                'section_key' => 'home_company_workspace',
                'title_en' => 'The future of work. A Saudi spirit.',
                'title_ar' => 'مستقبل العمل.. بروح سعودية',
                'subtitle_en' => 'Inspired by AlUla for teams that work from anywhere: Saudi hospitality in the details, world-class engineering in performance.',
                'subtitle_ar' => 'صُمّمت UlaSpace بإلهام من العلا لفرق تعمل من كل مكان: ضيافة سعودية في التفاصيل، وهندسة عالمية في الأداء.',
                'badge_en' => 'From Saudi Arabia, to the world.',
                'badge_ar' => 'من السعودية … إلى العالم',
                'display_order' => 7,
                'content' => [
                    'pillars' => [
                        ['title_en' => 'Custom Branding & Logo', 'title_ar' => 'الهوية والشعار الخاص', 'desc_en' => 'Your company logo and colors throughout the dashboard and office.', 'desc_ar' => 'ظهور شعار وهوية شركتك في كامل لوحة التحكم والمكتب.'],
                        ['title_en' => 'Dedicated SMTP Server', 'title_ar' => 'خادم بريد SMTP مخصص', 'desc_en' => 'Send meeting invites and alerts from your own company email domain.', 'desc_ar' => 'إرسال دعوات الاجتماعات والتنبيهات ببريد وهوية شركتك الخاصة.'],
                        ['title_en' => 'Role-Based Permissions (RBAC)', 'title_ar' => 'نظام الصلاحيات والأدوار', 'desc_en' => 'Granular permissions for Owners, Admins, Managers, and Members.', 'desc_ar' => 'تحكم دقيق بصلاحيات المالك، المشرف، المدير، والموظفين.'],
                    ],
                ],
            ],
            [
                'section_type' => 'pricing',
                'section_key' => 'home_pricing',
                'title_en' => 'Plans that fit your team.',
                'title_ar' => 'باقة تناسب حجم فريقك',
                'subtitle_en' => 'Start free, upgrade any time as your team grows.',
                'subtitle_ar' => 'ابدأ مجاناً، ورقِّ باقتك في أي وقت مع نمو فريقك.',
                'badge_en' => 'Plans & Pricing',
                'badge_ar' => 'الباقات والأسعار',
                'display_order' => 8,
                'content' => [
                    'currencies' => ['SAR', 'EGP', 'AED', 'USD'],
                    'default_currency' => 'SAR',
                    'billing_periods' => ['monthly', 'annual'],
                    'annual_discount_percentage' => 20,
                ],
            ],
            [
                'section_type' => 'testimonials',
                'section_key' => 'home_testimonials',
                'title_en' => 'Loved by high-performing distributed teams.',
                'title_ar' => 'موثوق ومحبوب من آلاف فرق العمل عن بُعد.',
                'subtitle_en' => 'See how leading remote companies use NextSpace to boost team happiness and accelerate execution.',
                'subtitle_ar' => 'تعرف على كيفية استخدام الشركات الرائدة لـ NextSpace لتعزيز روح الفريق ومضاعفة الإنتاجية.',
                'badge_en' => '⭐ Customer Stories',
                'badge_ar' => '⭐ تجارب وقصص النجاح',
                'display_order' => 9,
                'content' => [
                    'testimonials' => [
                        [
                            'name' => 'Sara Al-Ghamdi',
                            'role_en' => 'VP of Engineering at CloudTech',
                            'role_ar' => 'نائب رئيس الهندسة في كلاودتك',
                            'quote_en' => 'NextSpace completely eliminated our Zoom fatigue. Being able to just walk over to a desk and talk feels like we are in the same Silicon Valley office.',
                            'quote_ar' => 'NextSpace قضت تماماً على إرهاق اجتماعات زووم. التواجد في نفس المكتب الافتراضي جعل فريقنا الموزع يعمل كأنه في غرفة واحدة.',
                            'avatar' => '👩‍💼',
                            'company' => 'CloudTech KSA',
                        ],
                        [
                            'name' => 'Ahmed Mansour',
                            'role_en' => 'CEO at Nexa Growth Studio',
                            'role_ar' => 'الرئيس التنفيذي لـ Nexa Studio',
                            'quote_en' => 'Generating our office layout with AI took 30 seconds. Our designers and devs love the spatial proximity audio.',
                            'quote_ar' => 'توليد مخطط مكتبنا بالذكاء الاصطناعي استغرق 30 ثانية فقط! المطورون والمصممون يعشقون الصوت المكاني والاجتماعات العفوية.',
                            'avatar' => '👨‍💼',
                            'company' => 'Nexa Growth Studio',
                        ],
                        [
                            'name' => 'Layla Hassan',
                            'role_en' => 'Head of People & Culture at ScaleUp',
                            'role_ar' => 'مديرة الموارد البشرية في ScaleUp',
                            'quote_en' => 'Team engagement doubled in the first week. The team lounge area has brought back the human connection remote work was missing.',
                            'quote_ar' => 'تفاعل الموظفين تضاعف خلال أول أسبوع. ردهة الاستراحة الافتراضية أعادت الروح والتواصل الإنساني الذي افتقدناه بالعمل عن بُعد.',
                            'avatar' => '👩‍💻',
                            'company' => 'ScaleUp MENA',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'faq',
                'section_key' => 'home_faq',
                'title_en' => 'Frequently Asked Questions',
                'title_ar' => 'الأسئلة الشائعة والمكررة',
                'subtitle_en' => 'Everything you need to know about NextSpace spatial workplace.',
                'subtitle_ar' => 'إجابات على كافة استفساراتك حول المنصة والاشتراكات والتقنيات المستخدمة.',
                'badge_en' => '❓ FAQ',
                'badge_ar' => '❓ الأسئلة الشائعة',
                'display_order' => 10,
                'content' => [
                    'faqs' => [
                        [
                            'question_en' => 'Do team members need to install any software?',
                            'question_ar' => 'هل يحتاج أعضاء الفريق لتثبيت أي برامج أو تطبيقات؟',
                            'answer_en' => 'No! NextSpace runs 100% in modern web browsers (Chrome, Edge, Safari, Firefox) on desktop and tablet with WebRTC and WebGL.',
                            'answer_ar' => 'كلا! تعمل المنصة مباشرة عبر المتصفح بدون أي تثبيت على الإطلاق وتدعم كروم وإيدج وسفاري وفايرفوكس.',
                        ],
                        [
                            'question_en' => 'How does the AI Office Generator work?',
                            'question_ar' => 'كيف يعمل مولد المكاتب بالذكاء الاصطناعي؟',
                            'answer_en' => 'Simply describe your team size and desired room types. NextSpace AI synthesizes a tailored 2D architectural blueprint in seconds, which you can edit or use immediately.',
                            'answer_ar' => 'فقط اكتب وصفاً لعدد أفراد فريقك والغرف المطلوبة، وسيقوم الذكاء الاصطناعي برسم وتوليد مخطط معماري متكامل خلال ثوانٍ معدودة.',
                        ],
                        [
                            'question_en' => 'What payment methods and currencies are supported?',
                            'question_ar' => 'ما هي طرق الدفع والعملات المدعومة للاشتراك؟',
                            'answer_en' => 'We support local bank wire transfers with official IBAN/Swift, digital wallets (Instapay in Egypt, STC Pay in Saudi Arabia, Vodafone Cash), and multi-currency billing in SAR, EGP, AED, and USD.',
                            'answer_ar' => 'ندعم التحويل البنكي المباشر (IBAN)، والمحافظ الرقمية (إنستاباي مصر، STC Pay السعودية، فودافون كاش)، وبدعم متعدد للعملات.',
                        ],
                        [
                            'question_en' => 'Can we customize our office rooms and sound isolation?',
                            'question_ar' => 'هل يمكننا تعديل غرف المكتب وتخصيص العزل الصوتي؟',
                            'answer_en' => 'Yes! The built-in spatial editor lets you draw private sound isolation zones, assign desk labels, set door knock permissions, and upload custom office floorplans.',
                            'answer_ar' => 'بالتأكيد! يتيح لك المحرر المكاني رسم وتعديل مناطق العزل الصوتي وتحديد مقاعد الموظفين وقفل الغرف بكل سهولة.',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'cta',
                'section_key' => 'home_cta',
                'title_en' => 'Ready to move your team into the workplace of the future?',
                'title_ar' => 'جاهز لنقل فريقك إلى بيئة عمل المستقبل؟',
                'subtitle_en' => 'Join hundreds of leading companies building a strong, connected work culture with UlaSpace.',
                'subtitle_ar' => 'انضم إلى المئات من الشركات الرائدة التي تبني ثقافة عمل قوية ومتصلة مع UlaSpace.',
                'badge_en' => 'Start free',
                'badge_ar' => 'ابدأ الآن مجاناً',
                'display_order' => 11,
                'content' => [
                    'btn_text_en' => 'Create Organization for Free',
                    'btn_text_ar' => 'إنشاء مساحة عمل مجاناً',
                    'btn_link' => '/register',
                    'note_en' => '⚡ Instant setup • No credit card required • Free forever tier available',
                    'note_ar' => '⚡ تفعيل فوري • بدون بطاقة ائتمان • باقة مجانية متوفرة دائماً',
                ],
            ],
        ];

        foreach ($sections as $s) {
            CmsSection::updateOrCreate(
                ['page_id' => $homePage->id, 'section_key' => $s['section_key']],
                $s
            );
        }
    }
}
