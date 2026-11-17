<?php

namespace App\Providers;

use App\Support\Seo;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Seo::class, fn (): Seo => new Seo(config('seo')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        $this->configureRateLimiting();
        $this->configureDefaults();
    }

    protected function configureRateLimiting(): void
    {
        $key = fn (Request $request): string => $request->user()?->id
            ? 'user:'.$request->user()->id
            : 'ip:'.$request->ip();

        RateLimiter::for('web', fn (Request $request): Limit => Limit::perMinute(120)->by($key($request)));

        RateLimiter::for('imports.upload', fn (Request $request): Limit => Limit::perMinute(10)->by($key($request)));

        RateLimiter::for('ai', fn (Request $request): Limit => Limit::perMinute(10)->by($key($request)));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
