<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureRequestSizeIsAllowed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RequestLimitingTest extends TestCase
{
    public function test_named_limiters_are_registered_with_expected_limits(): void
    {
        $this->assertSame(120, RateLimiter::limiter('web')(Request::create('/'))->maxAttempts);
        $this->assertSame(10, RateLimiter::limiter('imports.upload')(Request::create('/'))->maxAttempts);
        $this->assertSame(10, RateLimiter::limiter('ai')(Request::create('/'))->maxAttempts);
    }

    public function test_sensitive_routes_use_the_expected_throttles(): void
    {
        $this->assertContains('throttle:web', Route::getRoutes()->getByName('dashboard')->gatherMiddleware());
        $this->assertContains('throttle:imports.upload', Route::getRoutes()->getByName('imports.affiliate.store')->gatherMiddleware());
        $this->assertNotContains('throttle:imports.upload', Route::getRoutes()->getByName('imports.progress')->gatherMiddleware());
        $this->assertContains('throttle:ai', Route::getRoutes()->getByName('creators.analysis.run')->gatherMiddleware());
        $this->assertContains('throttle:ai', Route::getRoutes()->getByName('insights.index')->gatherMiddleware());
    }

    public function test_authenticated_routes_require_verified_email(): void
    {
        $authRoutes = array_filter(
            Route::getRoutes()->getRoutes(),
            fn ($route) => in_array('auth', $route->gatherMiddleware(), true),
        );

        $this->assertNotEmpty($authRoutes);

        foreach ($authRoutes as $route) {
            $this->assertContains(
                'verified',
                $route->gatherMiddleware(),
                $route->getName() ?? $route->uri(),
            );
        }
    }

    public function test_oversized_request_is_rejected(): void
    {
        $request = Request::create('/', 'POST');
        $request->headers->set('Content-Length', (string) (config('app.max_request_bytes') + 1));
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');

        $response = (new EnsureRequestSizeIsAllowed)->handle(
            $request,
            fn () => new Response('ok'),
        );

        $this->assertSame(413, $response->getStatusCode());
    }

    public function test_normal_request_passes_through(): void
    {
        $request = Request::create('/', 'POST');
        $request->headers->set('Content-Length', '1024');

        $response = (new EnsureRequestSizeIsAllowed)->handle(
            $request,
            fn () => new Response('ok'),
        );

        $this->assertSame('ok', $response->getContent());
    }
}
