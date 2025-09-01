<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialiteController extends Controller
{
    public function redirectToProvider(string $provider)
    {
        if (! config()->has('services.' . $provider)) {
            throw new InvalidStateException;
        }

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider): ?RedirectResponse
    {
        if ($provider === 'vatsim') {
            return $this->handleVatsimCallback();
        }

        throw new InvalidStateException;
    }

    protected function handleVatsimCallback()
    {
        $socialite = Socialite::driver('vatsim')->user();

        $user = User::updateOrCreate([
            'cid' => $socialite->id,
        ], [
            'first_name' => trim(explode(' ', $socialite->name)[0]),
            'last_name' => trim(explode(' ', $socialite->name)[1]),
            'email' => $socialite->email,
        ]);

        auth()->login($user);

        return redirect('/');
    }
}
