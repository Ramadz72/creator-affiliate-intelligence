<?php

namespace Tests\Feature;

use App\Http\Middleware\AddSecurityHeaders;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_added(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_csrf_token_meta_tag_is_rendered(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee(
            'name="csrf-token" content="'.csrf_token().'"',
            false,
        );
    }

    public function test_hsts_is_added_only_on_secure_requests(): void
    {
        $middleware = new AddSecurityHeaders;

        $plainResponse = $middleware->handle(
            Request::create('/', 'GET'),
            fn () => new Response,
        );

        $this->assertFalse($plainResponse->headers->has('Strict-Transport-Security'));

        $secureResponse = $middleware->handle(
            Request::create('/', 'GET', [], [], [], ['HTTPS' => 'on']),
            fn () => new Response,
        );

        $this->assertSame(
            'max-age=31536000; includeSubDomains',
            $secureResponse->headers->get('Strict-Transport-Security'),
        );
    }
}
