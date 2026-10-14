<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Domains\Administration\Models\SystemSetting;
use App\Domains\Tenancy\Models\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Super Admin — Platform settings (AI, payments, general) and translations.
 * Moved verbatim from SuperAdminController (refactor phase 7).
 */
class PlatformSettingsController extends Controller
{
    /**
     * System Settings & Payment Configuration.
     */
    public function settings()
    {
        $user = Auth::user();
        $plans = Plan::where('is_active', true)->get();

        $defaultBankAccounts = [
            [
                'bank_name' => 'Al Rajhi Bank (مصرف الراجحي)',
                'account_name' => 'مؤسسة مساحة الغد لتقنية المعلومات',
                'account_name_en' => 'Next Space Information Technology Est.',
                'iban' => 'SA4580000412608010123456',
                'account_number' => '412608010123456',
                'swift_code' => 'RJHISARI',
                'currency' => 'SAR',
                'badge' => 'Direct Instant Local Transfer',
            ],
            [
                'bank_name' => 'Saudi National Bank - SNB (البنك الأهلي السعودي)',
                'account_name' => 'مؤسسة مساحة الغد لتقنية المعلومات',
                'account_name_en' => 'Next Space Information Technology Est.',
                'iban' => 'SA1210000001234567890123',
                'account_number' => '01234567890123',
                'swift_code' => 'NCBKSAJI',
                'currency' => 'SAR',
                'badge' => 'Corporate Settlement',
            ],
            [
                'bank_name' => 'Riyad Bank (بنك الرياض)',
                'account_name' => 'مؤسسة مساحة الغد لتقنية المعلومات',
                'account_name_en' => 'Next Space Information Technology Est.',
                'iban' => 'SA7820000009876543210987',
                'account_number' => '09876543210987',
                'swift_code' => 'RIBLSARI',
                'currency' => 'SAR / USD',
                'badge' => 'International Wire / SWIFT',
            ],
        ];

        $paymentSettings = SystemSetting::get('payment_settings', [
            'usd_to_sar_rate' => 3.75,
            'usd_to_egp_rate' => 48.5,
            'usd_to_aed_rate' => 3.67,
            'default_currency' => 'SAR',
            'tax_percentage' => 15,
            'tax_number' => '300012345600003',
            'bank_accounts' => $defaultBankAccounts,
            'instapay_handle' => 'nextspace@instapay',
            'instapay_phone' => '+201000000000',
            'stc_pay_phone' => '+966500000000',
            'vodafone_cash_phone' => '+201000000000',
            'checkout_terms_ar' => 'يتم تفعيل الاشتراك فور مراجعة إيصال التحويل البنكي أو الدفع الإلكتروني من قبل المشرفين.',
            'checkout_terms_en' => 'Your subscription will be activated immediately upon review of the payment receipt by our administration team.',
            'enable_bank_transfer' => true,
            'enable_instapay' => true,
            'enable_wallets' => true,
        ]);

        $globalSettings = SystemSetting::get('global_settings', [
            'platform_name' => 'Virtual Workplace SaaS',
            'default_plan_id' => $plans->where('slug', 'free')->first()?->id ?? $plans->first()?->id,
            'ws_url' => 'ws://127.0.0.1:8080',
            'stun_server' => 'stun:173.212.248.192:3478',
        ]);

        $aiSettings = SystemSetting::get('openai_settings', [
            'api_key' => env('OPENAI_API_KEY', ''),
            'model' => 'dall-e-3',
            'image_size' => '1792x1024',
            'quality' => 'standard',
            'prompt_prefix' => "A clean, photorealistic direct top-down 2D architectural floor plan blueprint of a modern virtual workplace office (straight 90-degree overhead bird's-eye plan view with cutaway interior walls).",
            'is_enabled' => true,
        ]);

        return view('superadmin.settings', compact('user', 'plans', 'paymentSettings', 'globalSettings', 'aiSettings'));
    }

    /**
     * Update OpenAI & AI Workplace Generator Settings.
     */
    public function updateAiSettings(Request $request)
    {
        $aiSettings = [
            'api_key' => trim($request->input('api_key', '')),
            'model' => $request->input('model', 'gpt-image-1'),
            'image_size' => $request->input('image_size', '1024x1024'),
            'quality' => $request->input('quality', 'standard'),
            'prompt_prefix' => trim($request->input('prompt_prefix', "A clean, photorealistic direct top-down 2D architectural floor plan blueprint of a modern virtual workplace office (straight 90-degree overhead bird's-eye plan view with cutaway interior walls).")),
            'is_enabled' => $request->boolean('is_enabled'), // the checkbox decides; a saved key doesn't switch the generator on
        ];

        SystemSetting::set('openai_settings', $aiSettings);

        return back()->with('success', __('AI Office Generator & OpenAI settings saved successfully.'));
    }

    /**
     * Test OpenAI API connectivity.
     */
    public function testAiConnection(Request $request)
    {
        $apiKey = trim($request->input('api_key', ''));
        if (empty($apiKey)) {
            $aiSettings = SystemSetting::get('openai_settings', []);
            $apiKey = $aiSettings['api_key'] ?? env('OPENAI_API_KEY', '');
        }

        if (empty($apiKey)) {
            return response()->json(['success' => false, 'message' => __('OpenAI API key is missing. Please enter an API key.')], 422);
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(15)
                ->get('https://api.openai.com/v1/models');

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => __('✅ Connection to OpenAI API successful! Account is active and authenticated.')]);
            } else {
                $err = $response->json();
                $errMsg = $err['error']['message'] ?? $response->body();

                return response()->json(['success' => false, 'message' => 'OpenAI Error: '.$errMsg], 400);
            }
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Network error connecting to OpenAI: '.$e->getMessage()], 500);
        }
    }

    /**
     * Update global system settings.
     */
    public function updateSettings(Request $request)
    {
        $globalSettings = [
            'platform_name' => $request->input('platform_name', 'Virtual Workplace SaaS'),
            'default_plan_id' => $request->input('default_plan_id'),
            'ws_url' => $request->input('ws_url', 'ws://127.0.0.1:8080'),
            'stun_server' => $request->input('stun_server', 'stun:173.212.248.192:3478'),
        ];

        SystemSetting::set('global_settings', $globalSettings);

        return back()->with('success', __('System settings saved successfully.'));
    }

    /**
     * Update Payment & Checkout settings.
     */
    public function updatePaymentSettings(Request $request)
    {
        $bankAccounts = [];
        $bankNames = $request->input('bank_name', []);
        $accountNames = $request->input('account_name', []);
        $accountNamesEn = $request->input('account_name_en', []);
        $ibans = $request->input('iban', []);
        $accountNumbers = $request->input('account_number', []);
        $swiftCodes = $request->input('swift_code', []);
        $currencies = $request->input('currency', []);
        $badges = $request->input('badge', []);

        for ($i = 0; $i < count($bankNames); $i++) {
            if (! empty(trim($bankNames[$i]))) {
                $bankAccounts[] = [
                    'bank_name' => trim($bankNames[$i]),
                    'account_name' => trim($accountNames[$i] ?? ''),
                    'account_name_en' => trim($accountNamesEn[$i] ?? ''),
                    'iban' => trim($ibans[$i] ?? ''),
                    'account_number' => trim($accountNumbers[$i] ?? ''),
                    'swift_code' => trim($swiftCodes[$i] ?? ''),
                    'currency' => trim($currencies[$i] ?? 'SAR'),
                    'badge' => trim($badges[$i] ?? 'Official Bank Account'),
                ];
            }
        }

        $paymentSettings = [
            'usd_to_sar_rate' => (float) $request->input('usd_to_sar_rate', 3.75),
            'usd_to_egp_rate' => (float) $request->input('usd_to_egp_rate', 48.5),
            'usd_to_aed_rate' => (float) $request->input('usd_to_aed_rate', 3.67),
            'default_currency' => $request->input('default_currency', 'SAR'),
            'tax_percentage' => (float) $request->input('tax_percentage', 15),
            'tax_number' => $request->input('tax_number', ''),
            'bank_accounts' => $bankAccounts,
            'instapay_handle' => $request->input('instapay_handle', ''),
            'instapay_phone' => $request->input('instapay_phone', ''),
            'stc_pay_phone' => $request->input('stc_pay_phone', ''),
            'vodafone_cash_phone' => $request->input('vodafone_cash_phone', ''),
            'checkout_terms_ar' => $request->input('checkout_terms_ar', ''),
            'checkout_terms_en' => $request->input('checkout_terms_en', ''),
            'enable_bank_transfer' => $request->has('enable_bank_transfer'),
            'enable_instapay' => $request->has('enable_instapay'),
            'enable_wallets' => $request->has('enable_wallets'),
        ];

        SystemSetting::set('payment_settings', $paymentSettings);

        return back()->with('success', __('Payment & Checkout settings saved successfully.'));
    }

    /**
     * Translations & Localization Management Console.
     */
    public function translations(Request $request)
    {
        $user = Auth::user();
        $search = trim((string) $request->input('search', ''));
        $selectedLang = $request->input('lang', 'ar');

        $arPath = base_path('lang/ar.json');
        $enPath = base_path('lang/en.json');

        $arJson = file_exists($arPath) ? json_decode(file_get_contents($arPath), true) ?: [] : [];
        $enJson = file_exists($enPath) ? json_decode(file_get_contents($enPath), true) ?: [] : [];

        // Collect all unique keys
        $allKeys = array_unique(array_merge(array_keys($arJson), array_keys($enJson)));
        sort($allKeys);

        $translations = [];
        foreach ($allKeys as $key) {
            $arVal = $arJson[$key] ?? '';
            $enVal = $enJson[$key] ?? $key;

            if ($search !== '') {
                $matchesKey = (stripos($key, $search) !== false);
                $matchesAr = (stripos($arVal, $search) !== false);
                $matchesEn = (stripos($enVal, $search) !== false);
                if (! $matchesKey && ! $matchesAr && ! $matchesEn) {
                    continue;
                }
            }

            $translations[] = [
                'key' => $key,
                'ar' => $arVal,
                'en' => $enVal,
            ];
        }

        $totalCount = count($allKeys);
        $filteredCount = count($translations);

        // Pagination
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        $paginatedItems = array_slice($translations, $offset, $perPage);
        $totalPages = max(1, (int) ceil($filteredCount / $perPage));

        return view('superadmin.translations', compact(
            'user',
            'paginatedItems',
            'totalCount',
            'filteredCount',
            'search',
            'selectedLang',
            'page',
            'perPage',
            'totalPages'
        ));
    }

    /**
     * Save updated translations.
     */
    public function updateTranslations(Request $request)
    {
        try {
            $keys = $request->input('keys', []);
            $arVals = $request->input('ar', []);
            $enVals = $request->input('en', []);

            $arPath = base_path('lang/ar.json');
            $enPath = base_path('lang/en.json');

            $arJson = file_exists($arPath) ? json_decode(file_get_contents($arPath), true) ?: [] : [];
            $enJson = file_exists($enPath) ? json_decode(file_get_contents($enPath), true) ?: [] : [];

            for ($i = 0; $i < count($keys); $i++) {
                $key = $keys[$i];
                if ($key !== '') {
                    if (isset($arVals[$i])) {
                        $arJson[$key] = $arVals[$i];
                    }
                    if (isset($enVals[$i])) {
                        $enJson[$key] = $enVals[$i];
                    }
                }
            }

            if (! file_exists(dirname($arPath))) {
                @mkdir(dirname($arPath), 0775, true);
            }
            if (file_exists($arPath) && ! is_writable($arPath)) {
                @chmod($arPath, 0666);
            }
            if (file_exists($enPath) && ! is_writable($enPath)) {
                @chmod($enPath, 0666);
            }

            file_put_contents($arPath, json_encode($arJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            file_put_contents($enPath, json_encode($enJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Artisan::call('view:clear');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => __('Translations saved and cache cleared.')]);
            }

            return back()->with('success', __('Translations saved successfully across the system.'));
        } catch (\Throwable $e) {
            Log::error('Error saving translations: '.$e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to save translations: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Failed to save translations: '.$e->getMessage());
        }
    }

    /**
     * Add new translation key.
     */
    public function addTranslationKey(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:1000',
            'ar' => 'required|string|max:2000',
            'en' => 'nullable|string|max:2000',
        ]);

        try {
            $key = trim($request->input('key'));
            $arVal = trim($request->input('ar'));
            $enVal = trim($request->input('en', $key)) ?: $key;

            $arPath = base_path('lang/ar.json');
            $enPath = base_path('lang/en.json');

            $arJson = file_exists($arPath) ? json_decode(file_get_contents($arPath), true) ?: [] : [];
            $enJson = file_exists($enPath) ? json_decode(file_get_contents($enPath), true) ?: [] : [];

            $arJson[$key] = $arVal;
            $enJson[$key] = $enVal;

            if (file_exists($arPath) && ! is_writable($arPath)) {
                @chmod($arPath, 0666);
            }
            if (file_exists($enPath) && ! is_writable($enPath)) {
                @chmod($enPath, 0666);
            }

            file_put_contents($arPath, json_encode($arJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            file_put_contents($enPath, json_encode($enJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Artisan::call('view:clear');

            return back()->with('success', __('New phrase ":key" added successfully.', ['key' => $key]));
        } catch (\Throwable $e) {
            Log::error('Error adding translation: '.$e->getMessage());

            return back()->with('error', 'Failed to add phrase: '.$e->getMessage());
        }
    }

    /**
     * Delete translation key.
     */
    public function deleteTranslationKey(Request $request)
    {
        $request->validate(['key' => 'required|string']);
        $key = $request->input('key');

        try {
            $arPath = base_path('lang/ar.json');
            $enPath = base_path('lang/en.json');

            $arJson = file_exists($arPath) ? json_decode(file_get_contents($arPath), true) ?: [] : [];
            $enJson = file_exists($enPath) ? json_decode(file_get_contents($enPath), true) ?: [] : [];

            unset($arJson[$key]);
            unset($enJson[$key]);

            if (file_exists($arPath) && ! is_writable($arPath)) {
                @chmod($arPath, 0666);
            }
            if (file_exists($enPath) && ! is_writable($enPath)) {
                @chmod($enPath, 0666);
            }

            file_put_contents($arPath, json_encode($arJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            file_put_contents($enPath, json_encode($enJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Artisan::call('view:clear');

            return back()->with('success', __('Phrase removed from system.'));
        } catch (\Throwable $e) {
            Log::error('Error deleting translation: '.$e->getMessage());

            return back()->with('error', 'Failed to delete phrase: '.$e->getMessage());
        }
    }
}
