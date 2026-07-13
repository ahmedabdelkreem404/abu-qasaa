<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StagingReadinessPhaseFifteenTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_reports_safe_readiness_checks(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.checks.database', 'ok')
            ->assertJsonPath('data.checks.storage', 'ok')
            ->assertJsonMissing(['APP_KEY'])
            ->assertJsonMissing(['DB_PASSWORD'])
            ->assertJsonMissing(['PAYMOB_HMAC_SECRET']);
    }

    public function test_api_responses_include_safe_security_headers(): void
    {
        $this->getJson('/api/v1/health')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }
}
