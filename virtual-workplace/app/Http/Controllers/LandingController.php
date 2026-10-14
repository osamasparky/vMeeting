<?php

namespace App\Http\Controllers;

use App\Domains\CMS\Models\CmsPage;
use App\Domains\CMS\Support\Cms;
use App\Domains\CMS\Support\CmsSchema;
use App\Domains\Tenancy\Models\Plan;

class LandingController extends Controller
{
    public function home()
    {
        // A fresh install with no CMS rows yet gets the default content.
        if (! CmsPage::where('slug', 'home')->exists()) {
            CmsSchema::sync();
        }

        $page = CmsPage::where('slug', 'home')->first();
        $sections = Cms::activeSections('home');
        $plans = Plan::where('is_active', true)->orderBy('price', 'asc')->get();

        return view('landing.home', compact('page', 'sections', 'plans'));
    }
}
