<?php

namespace App\Http\Controllers;

use App\Domains\Administration\Models\SystemSetting;
use App\Domains\CMS\Models\CmsPage;
use App\Domains\CMS\Services\ThemeEngineService;
use App\Domains\Tenancy\Models\Plan;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function home(Request $request)
    {
        $page = CmsPage::with(['activeSections.mediaAsset'])
            ->where('slug', 'home')
            ->where('status', 'published')
            ->first();

        // If not seeded yet, create fallback
        if (! $page) {
            \Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CmsDatabaseSeeder']);
            $page = CmsPage::with(['activeSections.mediaAsset'])
                ->where('slug', 'home')
                ->first();
        }

        $sections = $page ? $page->activeSections->keyBy('section_key') : collect();
        $plans = Plan::where('is_active', true)->orderBy('price', 'asc')->get();

        // Payment & Currency Rates
        $paymentSetting = SystemSetting::where('key', 'payment_gateways')->first();
        $paymentConfig = $paymentSetting ? json_decode($paymentSetting->value, true) : [];
        $rates = [
            'USD' => 1.0,
            'SAR' => (float) ($paymentConfig['usd_to_sar_rate'] ?? 3.75),
            'EGP' => (float) ($paymentConfig['usd_to_egp_rate'] ?? 48.5),
            'AED' => (float) ($paymentConfig['usd_to_aed_rate'] ?? 3.67),
        ];

        $dynamicCssVariables = ThemeEngineService::generateCssVariables();

        return view('landing.home', compact('page', 'sections', 'plans', 'rates', 'dynamicCssVariables'));
    }
}
