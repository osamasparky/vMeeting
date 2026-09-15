<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InternationalizationAuditTest extends TestCase
{
    /**
     * Test that en.json and ar.json exist and contain valid JSON.
     */
    public function test_translation_files_exist_and_are_valid_json(): void
    {
        $enPath = base_path('lang/en.json');
        $arPath = base_path('lang/ar.json');

        $this->assertFileExists($enPath, 'lang/en.json must exist');
        $this->assertFileExists($arPath, 'lang/ar.json must exist');

        $enData = json_decode(File::get($enPath), true);
        $arData = json_decode(File::get($arPath), true);

        $this->assertIsArray($enData, 'en.json must be valid JSON array');
        $this->assertIsArray($arData, 'ar.json must be valid JSON array');
    }

    /**
     * Test that en.json values contain 0 Arabic Unicode characters (100% pure English UI).
     */
    public function test_en_json_contains_no_arabic_characters(): void
    {
        $enPath = base_path('lang/en.json');
        $enData = json_decode(File::get($enPath), true);

        $arabicRegex = '/[\x{0600}-\x{06FF}]/u';
        $violations = [];

        foreach ($enData as $key => $value) {
            if (is_string($value) && preg_match($arabicRegex, $value)) {
                $violations[$key] = $value;
            }
        }

        $this->assertEmpty(
            $violations,
            'en.json must not contain Arabic characters in its values. Found violations: '.json_encode($violations, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Test 100% key parity between en.json and ar.json.
     */
    public function test_translation_files_have_complete_key_parity(): void
    {
        $enPath = base_path('lang/en.json');
        $arPath = base_path('lang/ar.json');

        $enData = json_decode(File::get($enPath), true);
        $arData = json_decode(File::get($arPath), true);

        $missingInAr = array_diff_key($enData, $arData);
        $missingInEn = array_diff_key($arData, $enData);

        $this->assertEmpty($missingInAr, 'Keys in en.json missing in ar.json: '.implode(', ', array_keys($missingInAr)));
        $this->assertEmpty($missingInEn, 'Keys in ar.json missing in en.json: '.implode(', ', array_keys($missingInEn)));
    }

    /**
     * Test that ar.json values are localized and do not contain untranslated core UI strings.
     */
    public function test_ar_json_contains_localized_arabic_values(): void
    {
        $arPath = base_path('lang/ar.json');
        $arData = json_decode(File::get($arPath), true);

        $coreKeysToCheck = [
            'Participants' => 'المشاركون',
            'Reactions' => 'التفاعلات',
            'Leave' => 'مغادرة',
            'Record' => 'تسجيل',
            'Screen Sharing' => 'مشاركة الشاشة',
            'Chat' => 'المحادثة',
            'Mute' => 'كتم الصوت',
            'Meeting Room' => 'غرفة اجتماعات',
            'Collaborative Whiteboard' => 'السبورة التفاعلية',
        ];

        foreach ($coreKeysToCheck as $key => $expectedArabic) {
            $this->assertArrayHasKey($key, $arData, "Key '{$key}' must exist in ar.json");
            $this->assertEquals($expectedArabic, $arData[$key], "ar.json value for '{$key}' should be '{$expectedArabic}', found '{$arData[$key]}'");
        }
    }

    /**
     * Test that public rendered pages respect locale and set correct lang and dir attributes.
     */
    public function test_rendered_pages_have_correct_html_lang_and_dir_attributes(): void
    {
        // Arabic session
        $responseAr = $this->withSession(['locale' => 'ar'])->get('/login');
        $responseAr->assertStatus(200);
        $responseAr->assertSee('lang="ar"', false);
        $responseAr->assertSee('dir="rtl"', false);

        // English session
        $responseEn = $this->withSession(['locale' => 'en'])->get('/login');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('lang="en"', false);
        $responseEn->assertSee('dir="ltr"', false);
    }

    /**
     * Test that language switching endpoint sets session and cookie properly.
     */
    public function test_language_switching_sets_session_and_cookie(): void
    {
        $respAr = $this->get('/lang/ar');
        $respAr->assertRedirect();
        $respAr->assertSessionHas('locale', 'ar');
        $respAr->assertCookie('locale', 'ar');

        $respEn = $this->get('/lang/en');
        $respEn->assertRedirect();
        $respEn->assertSessionHas('locale', 'en');
        $respEn->assertCookie('locale', 'en');
    }
}
