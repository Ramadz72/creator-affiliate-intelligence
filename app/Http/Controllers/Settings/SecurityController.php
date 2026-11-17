<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class SecurityController extends Controller
{
    /**
     * Show the user's security settings page.
     */
    public function edit(TwoFactorAuthenticationRequest $request): Response
    {
        $user = $request->user();
        $twoFactorSecret = $user->two_factor_secret;
        $twoFactorPending = $twoFactorSecret !== null && $user->two_factor_confirmed_at === null;

        $props = [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
            'twoFactor' => [
                'enabled' => $twoFactorSecret !== null,
                'confirmed' => $user->two_factor_confirmed_at !== null,
                'qrSvg' => $twoFactorPending ? $user->twoFactorQrCodeSvg() : null,
                'secretKey' => $twoFactorPending && $twoFactorSecret !== null ? decrypt($twoFactorSecret) : null,
                'recoveryCodes' => $twoFactorSecret !== null && ! $twoFactorPending ? $user->recoveryCodes() : [],
            ],
        ];

        if (Features::enabled(Features::twoFactorAuthentication())) {
            $props['canManageTwoFactor'] = true;
            $props['twoFactorEnabled'] = $user->hasEnabledTwoFactorAuthentication();
            $props['requiresConfirmation'] = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        } else {
            $props['canManageTwoFactor'] = false;
        }

        return Inertia::render('settings/Security', $props);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Password updated.')]);

        return back();
    }
}
