<?php

namespace App\Domains\CMS\Support;

use App\Domains\CMS\Models\CmsMediaAsset;
use App\Domains\CMS\Models\CmsPage;
use App\Domains\CMS\Models\CmsSection;

/**
 * The single definition of everything the public website lets an admin edit.
 *
 * Each page lists its sections in the order they appear on the site; each
 * section lists exactly the fields that site renders, with the text it shows
 * today as the default. The CMS editor renders from this, the controller
 * saves against it, and sync() brings the database in line with it — so a
 * field that isn't here can't be edited, and a section that isn't here is
 * removed.
 *
 * Field keys title_ar/title_en/subtitle_ar/subtitle_en/badge_ar/badge_en are
 * stored in the section's columns; every other key lives in its content JSON.
 * Field types: text, textarea, url, icon, select, plan, repeater.
 */
class CmsSchema
{
    public const COLUMN_FIELDS = ['title_ar', 'title_en', 'subtitle_ar', 'subtitle_en', 'badge_ar', 'badge_en'];

    public static function pages(): array
    {
        return [
            'home' => [
                'title_ar' => 'الصفحة الرئيسية',
                'title_en' => 'Home page',
                'icon' => 'home',
                'url' => '/',
                'seo' => [
                    'meta_title_ar' => 'UlaSpace — مساحات العمل والمكاتب الافتراضية الذكية',
                    'meta_desc_ar' => 'مساحات عمل افتراضية غامرة تجمع فرق العمل عن بعد مع صوت وفيديو مكاني، وتخطيط خرائط المكاتب، والاجتماعات التفاعلية.',
                ],
                'sections' => self::homeSections(),
            ],
            'site' => [
                'title_ar' => 'الهيدر والفوتر',
                'title_en' => 'Header & footer',
                'icon' => 'web',
                'url' => '/',
                'sections' => self::siteSections(),
            ],
            'auth' => [
                'title_ar' => 'تسجيل الدخول وإنشاء الحساب',
                'title_en' => 'Log in & sign up',
                'icon' => 'login',
                'url' => '/login',
                'sections' => self::authSections(),
            ],
        ];
    }

    public static function page(string $slug): ?array
    {
        return self::pages()[$slug] ?? null;
    }

    /** Section definition by key, with its page slug under 'page'. */
    public static function section(string $key): ?array
    {
        foreach (self::pages() as $slug => $page) {
            if (isset($page['sections'][$key])) {
                return $page['sections'][$key] + ['page' => $slug];
            }
        }

        return null;
    }

    public static function isColumn(string $field): bool
    {
        return in_array($field, self::COLUMN_FIELDS, true);
    }

    /** Field defaults for a section, keyed by field. */
    public static function defaults(string $key): array
    {
        $def = self::section($key);

        return $def ? array_map(fn ($f) => $f['default'] ?? null, $def['fields']) : [];
    }

    /** An unsaved section holding the defaults — what the site shows when a required section is missing. */
    public static function makeDefault(string $key): CmsSection
    {
        $section = new CmsSection(['section_key' => $key, 'is_active' => true, 'content' => []]);
        $content = [];
        foreach (self::defaults($key) as $field => $value) {
            if (self::isColumn($field)) {
                $section->{$field} = $value;
            } else {
                $content[$field] = $value;
            }
        }
        $section->content = $content;

        return $section;
    }

    /**
     * Bring the database in line with the schema. Idempotent and safe to
     * re-run: creates missing pages/sections with defaults, fills fields that
     * have never been set, drops content keys and sections the site no longer
     * uses. Text an admin already edited is never overwritten.
     */
    public static function sync(): void
    {
        foreach (self::pages() as $slug => $pageDef) {
            $page = CmsPage::firstOrNew(['slug' => $slug]);
            $page->title_ar = $pageDef['title_ar'];
            $page->title_en = $pageDef['title_en'];
            $page->status = 'published';
            foreach ($pageDef['seo'] ?? [] as $field => $default) {
                // Older rows still carry the pre-rebrand "NextSpace" copy.
                if (blank($page->{$field}) || str_contains((string) $page->{$field}, 'NextSpace')) {
                    $page->{$field} = $default;
                }
            }
            $page->save();

            CmsSection::where('page_id', $page->id)
                ->whereNotIn('section_key', array_keys($pageDef['sections']))
                ->delete();

            $order = 0;
            foreach ($pageDef['sections'] as $key => $def) {
                $section = CmsSection::firstOrNew(['page_id' => $page->id, 'section_key' => $key]);
                $isNew = ! $section->exists;
                $section->section_type = $def['type'];
                $section->display_order = ++$order;
                if ($isNew) {
                    $section->is_active = true;
                }

                $existing = $section->content ?? [];
                $content = [];
                foreach ($def['fields'] as $field => $fieldDef) {
                    $default = $fieldDef['default'] ?? null;
                    if (self::isColumn($field)) {
                        if ($section->{$field} === null) {
                            $section->{$field} = $default;
                        }
                    } else {
                        $content[$field] = array_key_exists($field, $existing) ? $existing[$field] : $default;
                    }
                }
                $section->content = $content;

                if ($isNew && ! empty($def['image']['default'])) {
                    $asset = CmsMediaAsset::firstOrCreate(
                        ['file_path' => $def['image']['default']],
                        ['name' => $def['name_en'].' image', 'asset_type' => 'image', 'version_tag' => 'v1', 'tags' => [$key], 'is_active' => true]
                    );
                    $section->media_asset_id = $asset->id;
                }

                $section->save();
            }
        }
    }

    /* ─────────────────────────── Field helpers ─────────────────────────── */

    private static function text(string $label, ?string $default = null, ?string $hint = null): array
    {
        return array_filter(['type' => 'text', 'label' => $label, 'default' => $default, 'hint' => $hint], fn ($v) => $v !== null);
    }

    private static function area(string $label, ?string $default = null, ?string $hint = null): array
    {
        return ['type' => 'textarea'] + self::text($label, $default, $hint);
    }

    private static function url(string $label, string $default): array
    {
        return ['type' => 'url', 'label' => $label, 'default' => $default, 'hint' => 'Starts with /, #, https:// or mailto:'];
    }

    private static function icon(string $default = 'star'): array
    {
        return ['type' => 'icon', 'label' => 'Icon', 'default' => $default];
    }

    private static function repeater(string $label, string $itemLabel, array $fields, array $default): array
    {
        return ['type' => 'repeater', 'label' => $label, 'item_label' => $itemLabel, 'fields' => $fields, 'default' => $default];
    }

    /* ─────────────────────────────── Pages ─────────────────────────────── */

    private static function homeSections(): array
    {
        return [
            'home_hero' => [
                'type' => 'hero',
                'name_ar' => 'البانر الرئيسي',
                'name_en' => 'Hero banner',
                'icon' => 'panorama',
                'required' => true,
                'image' => ['hint' => 'Full-width background · 1920×1080 JPG or WebP', 'default' => '/images/office_floorplan.jpg'],
                'fields' => [
                    'badge_en' => self::text('Eyebrow line (English, shown in capitals)', 'Next-Generation Virtual Workplace'),
                    'title_ar' => self::text('Headline (Arabic)', 'اجمع فريقك في مساحة واحدة ذكية'),
                    'subtitle_ar' => self::area('Description (Arabic)', 'مكاتب افتراضية نابضة بالحياة بالصوت والصورة والمحادثات. اجتماعات سهلة، ومشاركة أقرب.'),
                    'title_en' => self::text('English companion line', 'A more human way to work together.'),
                    'cta_primary_text_ar' => self::text('Main button text (Arabic)', 'ادخل إلى مساحتك'),
                    'cta_primary_link' => self::url('Main button link (visitors)', '/register'),
                    'cta_secondary_text_ar' => self::text('Second button text (Arabic)', 'شاهد العرض'),
                    'cta_secondary_link' => self::url('Second button link', '#spaces'),
                    'dashboard_text_ar' => self::text('Dashboard link text (Arabic)', 'لوحة التحكم'),
                    'overlay_title_ar' => self::area('Overlay tagline on the image (Arabic)', "أكثر من\nمكان العمل", 'Each line break shows as a new line.'),
                    'overlay_title_en' => self::area('Overlay tagline (English)', "A more human\nplace to work."),
                ],
            ],
            'home_features' => [
                'type' => 'feature_strip',
                'name_ar' => 'شريط المميزات',
                'name_en' => 'Feature strip',
                'icon' => 'view_column',
                'fields' => [
                    'items' => self::repeater('Features', 'Feature', [
                        'icon' => self::icon('apartment'),
                        'text_ar' => self::text('Text (Arabic)'),
                        'text_en' => self::text('Text (English)'),
                    ], [
                        ['icon' => 'apartment', 'text_ar' => 'مكاتب افتراضية حية', 'text_en' => 'Live Virtual Offices'],
                        ['icon' => 'videocam', 'text_ar' => 'اجتماعات سلسة', 'text_en' => 'Seamless Meetings'],
                        ['icon' => 'person_add', 'text_ar' => 'دعوات بضغطة واحدة', 'text_en' => '1-Click Guest Access'],
                        ['icon' => 'palette', 'text_ar' => 'تصميم مرن لمكتبك', 'text_en' => 'Design Your Space'],
                    ]),
                ],
            ],
            'home_company_workspace' => [
                'type' => 'identity',
                'name_ar' => 'الهوية — بروح سعودية',
                'name_en' => 'Identity',
                'icon' => 'flag',
                'image' => ['hint' => 'Side photo · 1200×800 JPG or WebP'],
                'fields' => [
                    'title_ar' => self::text('Headline (Arabic)', 'مستقبل العمل.. بروح سعودية'),
                    'title_en' => self::text('English companion line', 'The future of work. A Saudi spirit.'),
                    'subtitle_ar' => self::area('Description (Arabic)', 'صُمّمت UlaSpace بإلهام من العلا لفرق تعمل من كل مكان: ضيافة سعودية في التفاصيل، وهندسة عالمية في الأداء.'),
                    'badge_ar' => self::text('Photo caption (Arabic)', 'من السعودية … إلى العالم'),
                    'badge_en' => self::text('Photo caption (English)', 'From Saudi Arabia, to the world.'),
                ],
            ],
            'home_floorplan_editor' => [
                'type' => 'spaces',
                'name_ar' => 'المساحات الذكية',
                'name_en' => 'Smart spaces',
                'icon' => 'map',
                'image' => ['hint' => 'Floor preview inside the dark card · 1200×600 PNG, JPG or WebP'],
                'fields' => [
                    'badge_ar' => self::text('Eyebrow (Arabic)', 'المساحات الذكية'),
                    'title_ar' => self::text('Headline (Arabic)', 'مكتب افتراضي يشبه مكتبك الحقيقي'),
                    'subtitle_en' => self::area('English companion line', 'Design your floor once, and every teammate walks in to the same place — desks, meeting rooms, lounges, and quiet corners.'),
                    'tiles' => self::repeater('Space tiles', 'Tile', [
                        'icon' => self::icon('chair'),
                        'text_ar' => self::text('Text (Arabic)'),
                        'text_en' => self::text('Text (English)'),
                    ], [
                        ['icon' => 'chair', 'text_ar' => 'مكاتب فردية ومساحات عمل مشتركة', 'text_en' => 'Private desks and open collaboration zones'],
                        ['icon' => 'meeting_room', 'text_ar' => 'قاعات اجتماعات قابلة للقفل', 'text_en' => 'Lockable meeting rooms with knock-to-enter'],
                        ['icon' => 'workspaces', 'text_ar' => 'صالات استراحة وزوايا هادئة', 'text_en' => 'Lounges and quiet corners for focus work'],
                    ]),
                    'floor_name_ar' => self::text('Floor card title (Arabic)', 'الطابق الأول · المقر الرئيسي'),
                    'floor_name_en' => self::text('Floor card title (English)', 'Floor 1 · Main Headquarters'),
                    'online_count' => self::text('Online count', '18'),
                    'online_label_ar' => self::text('Online label (Arabic)', 'عضواً متصلاً'),
                    'rooms' => self::repeater('Rooms in the floor card', 'Room', [
                        'icon' => self::icon('meeting_room'),
                        'name_ar' => self::text('Room name (Arabic)'),
                        'detail_en' => self::text('Detail line (English)'),
                        'status_ar' => self::text('Status (Arabic)'),
                        'tone' => ['type' => 'select', 'label' => 'Status colour', 'options' => ['success' => 'Green — busy / in call', 'highlight' => 'Gold — available'], 'default' => 'success'],
                    ], [
                        ['icon' => 'meeting_room', 'name_ar' => 'قاعة النخيل', 'detail_en' => 'Palm Boardroom · 4 In Call', 'status_ar' => 'في مكالمة', 'tone' => 'success'],
                        ['icon' => 'chair', 'name_ar' => 'مساحة الابتكار', 'detail_en' => 'Innovation Lounge · 2 Desks', 'status_ar' => 'مكتبان متاحان', 'tone' => 'highlight'],
                    ]),
                ],
            ],
            'home_collaboration' => [
                'type' => 'capabilities',
                'name_ar' => 'قدرات المنصة',
                'name_en' => 'Capabilities',
                'icon' => 'hub',
                'fields' => [
                    'badge_en' => self::text('Eyebrow (English)', 'Platform Capabilities'),
                    'title_ar' => self::text('Headline (Arabic)', 'مصمم لبيئات العمل الحديثة التي تجمع بين التراث والابتكار'),
                    'subtitle_en' => self::text('English companion line', 'Everything you need to run a high-trust, collaborative virtual headquarters.'),
                    'cards' => self::repeater('Capability cards', 'Card', [
                        'icon' => self::icon('graphic_eq'),
                        'title_ar' => self::text('Title (Arabic)'),
                        'body_ar' => self::area('Description (Arabic)'),
                        'caption_en' => self::text('Caption (English)'),
                    ], [
                        ['icon' => 'graphic_eq', 'title_ar' => 'الصوت والفيديو المكاني', 'body_ar' => 'تواصل تلقائي يحاكي الواقع، حيث يقوى الصوت تدريجياً كلما اقتربت من زملائك على خريطة المقر دون روابط مكالمات معقدة.', 'caption_en' => 'Proximity-based WebRTC Mesh'],
                        ['icon' => 'lock', 'title_ar' => 'قاعات اجتماعات ذكية وأبواب خاصة', 'body_ar' => 'أبواب غرف قابلة للقفل مع جرس استئذان ومشاركة شاشة بدقة عالية وعزل صوتي كامل لسرية المحادثات.', 'caption_en' => 'Isolated Audio Zones & Knocking'],
                        ['icon' => 'architecture', 'title_ar' => 'محرر الخرائط ومكتبة الأثاث', 'body_ar' => 'صمم مخطط مكتبك بسحب وإفلات المكاتب والنباتات والسبورات البيضاء والشاشات التفاعلية بسهولة.', 'caption_en' => 'Custom Floorplan Architect & Catalog'],
                    ]),
                ],
            ],
            'home_quote' => [
                'type' => 'quote',
                'name_ar' => 'الاقتباس',
                'name_en' => 'Quote',
                'icon' => 'format_quote',
                'fields' => [
                    'quote_ar' => self::area('Quote (Arabic)', 'المكان ليس مجرد جدران، بل مساحة تلتقي فيها العقول وتتدفق فيها الأفكار بحرية وشغف.'),
                    'attribution_en' => self::text('Attribution (English)', 'UlaSpace — Designed with Heritage & Modern Luxury'),
                ],
            ],
            'home_pricing' => [
                'type' => 'pricing',
                'name_ar' => 'الباقات والأسعار',
                'name_en' => 'Pricing',
                'icon' => 'payments',
                'note' => 'Plan names, prices and features come from Plans & Billing.',
                'fields' => [
                    'title_ar' => self::text('Headline (Arabic)', 'باقة تناسب حجم فريقك'),
                    'subtitle_en' => self::text('English companion line', 'Start free, upgrade any time as your team grows.'),
                    'featured_plan' => ['type' => 'plan', 'label' => 'Highlighted plan', 'default' => 'business'],
                    'popular_label_ar' => self::text('Highlight badge (Arabic)', 'الأكثر شيوعاً'),
                    'button_text_ar' => self::text('Plan button text (Arabic)', 'ابدأ الآن'),
                ],
            ],
            'home_cta' => [
                'type' => 'cta',
                'name_ar' => 'بانر الدعوة للانضمام',
                'name_en' => 'Closing call to action',
                'icon' => 'rocket_launch',
                'fields' => [
                    'title_ar' => self::text('Headline (Arabic)', 'جاهز لنقل فريقك إلى بيئة عمل المستقبل؟'),
                    'subtitle_ar' => self::area('Description (Arabic)', 'انضم إلى المئات من الشركات الرائدة التي تبني ثقافة عمل قوية ومتصلة مع UlaSpace.'),
                    'cta_primary_text_ar' => self::text('Main button text (Arabic)', 'ابدأ التجربة المجانية الآن'),
                    'cta_primary_link' => self::url('Main button link', '/register'),
                    'cta_secondary_text_ar' => self::text('Second button text (Arabic)', 'تسجيل الدخول'),
                    'cta_secondary_link' => self::url('Second button link', '/login'),
                ],
            ],
        ];
    }

    private static function siteSections(): array
    {
        return [
            'site_header' => [
                'type' => 'header',
                'name_ar' => 'الهيدر وقائمة التنقل',
                'name_en' => 'Header & navigation',
                'icon' => 'menu',
                'required' => true,
                'fields' => [
                    'nav' => self::repeater('Navigation links', 'Link', [
                        'label_ar' => self::text('Label (Arabic)'),
                        'label_en' => self::text('Label (English)'),
                        'url' => ['type' => 'url', 'label' => 'Link'],
                    ], [
                        ['label_ar' => 'الحلول', 'label_en' => 'Solutions', 'url' => '#features'],
                        ['label_ar' => 'المنتج', 'label_en' => 'Product', 'url' => '#spaces'],
                        ['label_ar' => 'الموارد', 'label_en' => 'Resources', 'url' => '#identity'],
                        ['label_ar' => 'الأسعار', 'label_en' => 'Pricing', 'url' => '#pricing'],
                    ]),
                    'login_text_ar' => self::text('Log-in button (Arabic)', 'تسجيل الدخول'),
                    'login_text_en' => self::text('Log-in button (English)', 'Log In'),
                    'cta_text_ar' => self::text('Sign-up button (Arabic)', 'تجربة الآن'),
                    'cta_text_en' => self::text('Sign-up button (English)', 'Try it now'),
                    'dashboard_text_ar' => self::text('Dashboard button, signed in (Arabic)', 'لوحة التحكم'),
                    'dashboard_text_en' => self::text('Dashboard button, signed in (English)', 'Dashboard'),
                    'office_text_ar' => self::text('Office button, signed in (Arabic)', 'ادخل إلى المكتب'),
                    'office_text_en' => self::text('Office button, signed in (English)', 'Enter the office'),
                ],
            ],
            'site_footer' => [
                'type' => 'footer',
                'name_ar' => 'الفوتر',
                'name_en' => 'Footer',
                'icon' => 'bottom_navigation',
                'required' => true,
                'fields' => [
                    'tagline_en' => self::text('Tagline (English)', 'Spaces carved, not built.'),
                    'copyright_en' => self::text('Copyright line', '© {year} · AlUla · Saudi Arabia · The world', '{year} is replaced with the current year.'),
                ],
            ],
        ];
    }

    private static function authSections(): array
    {
        return [
            'auth_login' => [
                'type' => 'auth_login',
                'name_ar' => 'صفحة تسجيل الدخول',
                'name_en' => 'Log-in page',
                'icon' => 'login',
                'required' => true,
                'image' => ['hint' => 'Side panel next to the form · 1200×1600 portrait JPG or WebP'],
                'fields' => [
                    'heading_ar' => self::text('Form heading (Arabic)', 'أهلاً بعودتك'),
                    'heading_en' => self::text('Form heading (English)', 'Welcome back'),
                    'intro_ar' => self::text('Form intro (Arabic)', 'سجّل الدخول للوصول إلى مكتبك الافتراضي'),
                    'panel_title_ar' => self::text('Image headline (Arabic)', 'مكتبك الافتراضي بانتظارك'),
                    'panel_title_en' => self::text('Image headline (English)', 'Your Virtual Office Awaits'),
                ],
            ],
            'auth_register' => [
                'type' => 'auth_register',
                'name_ar' => 'صفحة إنشاء الحساب',
                'name_en' => 'Sign-up page',
                'icon' => 'person_add',
                'required' => true,
                'image' => ['hint' => 'Side panel next to the form · 1200×1600 portrait JPG or WebP'],
                'fields' => [
                    'heading_ar' => self::text('Form heading (Arabic)', 'أنشئ حسابك'),
                    'heading_en' => self::text('Form heading (English)', 'Create your account'),
                    'quote_ar' => self::area('Quote on the image (Arabic)', 'المكان ليس مجرد جدران، بل مساحة تلتقي فيها العقول.'),
                    'attribution_en' => self::text('Quote attribution (English)', 'UlaSpace'),
                ],
            ],
        ];
    }
}
