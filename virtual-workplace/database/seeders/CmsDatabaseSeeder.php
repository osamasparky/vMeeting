<?php

namespace Database\Seeders;

use App\Domains\CMS\Models\FeatureFlag;
use App\Domains\CMS\Support\CmsSchema;
use Illuminate\Database\Seeder;

class CmsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Feature flags
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

        // Website pages, sections and default content — defined in CmsSchema.
        CmsSchema::sync();
    }
}
