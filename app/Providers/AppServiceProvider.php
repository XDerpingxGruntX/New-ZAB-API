<?php

namespace App\Providers;

use App\Services\VATUSA;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(VATUSA::class, function () {
            return new VATUSA(config('services.vatusa.api_url'), config('services.vatusa.api_key'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('vatsim', \SocialiteProviders\Vatsim\Provider::class);
        });
    }
}
