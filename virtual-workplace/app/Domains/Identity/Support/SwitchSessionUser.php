<?php

namespace App\Domains\Identity\Support;

use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Switches the signed-in user of the current browser session: entering a company as the super
 * admin, a company admin acting as a member, and returning from either.
 *
 * The session remembers a hash of the signed-in user's password ("password_hash_web"), and
 * every API request from the page checks it against the current user. A plain Auth::login()
 * left the previous user's hash behind, so the first API call after switching logged the whole
 * session out. Here the hash is moved to the new user, and the session id is renewed.
 */
class SwitchSessionUser
{
    public static function to(User $user): void
    {
        Auth::guard('web')->login($user);

        $session = session();
        $session->migrate(true); // new id, same data (impersonation markers included)

        foreach (array_unique(array_merge(['web'], (array) config('sanctum.guard', ['web']))) as $guard) {
            $session->put('password_hash_'.$guard, $user->getAuthPassword());
        }
    }
}
