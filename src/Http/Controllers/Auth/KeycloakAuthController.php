<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class KeycloakAuthController extends Controller
{
    public function redirectToProvider(): RedirectResponse
    {
        return Socialite::driver('keycloak')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function handleProviderCallback(Request $request): RedirectResponse
    {
        $socialiteUser = Socialite::driver('keycloak')
            ->user();

        $raw = $socialiteUser->user ?? [];

        $keycloakId = $socialiteUser->getId();
        $email = $socialiteUser->getEmail();
        $name = $socialiteUser->getName() ?: ($raw['preferred_username'] ?? 'Unknown User');

        $realmRoles = data_get($raw, 'realm_access.roles', []);
        $groups = data_get($raw, 'groups', []);

        $user = User::query()->firstOrNew([
            'keycloak_id' => $keycloakId,
        ]);

        if (! $user->exists && $email) {
            $existingByEmail = User::query()->where('email', $email)->first();

            if ($existingByEmail) {
                $user = $existingByEmail;
                $user->keycloak_id = $keycloakId;
            }
        }

        $user->name = $name;
        $user->email = $email ?? sprintf('%s@keycloak.local', Str::uuid());
        $user->keycloak_roles = $realmRoles;
        $user->keycloak_groups = $groups;
        $user->password = $user->password ?: bcrypt(Str::random(32));
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirectUri = config('app.url');

        return redirect(
            Socialite::driver('keycloak')->getLogoutUrl(
                $redirectUri,
                config('services.keycloak.client_id')
            )
        );
    }
}

