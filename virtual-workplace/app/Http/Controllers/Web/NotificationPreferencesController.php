<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\NotificationPreferences;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** The signed-in user's notification switches, email and bell (profile page). */
class NotificationPreferencesController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Unchecked switches aren't submitted, so every key is read explicitly.
        $prefs = [];
        foreach (array_keys(NotificationPreferences::DEFAULTS) as $key) {
            $prefs[$key] = $request->boolean("email_{$key}");
        }
        foreach (array_keys(NotificationPreferences::INAPP_DEFAULTS) as $key) {
            $prefs['inapp'][$key] = $request->boolean("inapp_{$key}");
        }

        $user->forceFill(['notification_preferences' => $prefs])->save();

        return back()->with('success', __('pm.prefs_saved'));
    }
}
