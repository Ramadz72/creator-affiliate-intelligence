<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Autentikasi Google gagal. Silakan coba lagi.']);
        }

        $email = $googleUser->getEmail();

        if (! $email || ! ($googleUser->user['email_verified'] ?? false)) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Email Google belum terverifikasi.']);
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'profile_photo' => $googleUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            }
        }

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName()
                    ?: $googleUser->getNickname()
                    ?: 'Google User',
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'profile_photo' => $googleUser->getAvatar(),
                'password' => Str::random(40),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, true);

        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}