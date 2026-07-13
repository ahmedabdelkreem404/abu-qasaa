<?php

namespace Tests\Feature;

use App\Modules\Core\Application\Services\SafeUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionReadinessPhaseFourteenTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_safe_operational_metadata(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonStructure([
                'data' => ['status', 'application', 'environment', 'timestamp'],
            ])
            ->assertJsonMissing(['APP_KEY'])
            ->assertJsonMissing(['DB_PASSWORD']);
    }

    public function test_missing_api_routes_return_standard_json_error(): void
    {
        $this->getJson('/api/v1/does-not-exist')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Resource not found.');
    }

    public function test_login_is_rate_limited_after_repeated_invalid_attempts(): void
    {
        $this->seed();
        RateLimiter::clear('login:admin@abuqasaa.test|127.0.0.1');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'admin@abuqasaa.test',
                'password' => 'wrong-password',
            ])->assertStatus(422);
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@abuqasaa.test',
            'password' => 'wrong-password',
        ])
            ->assertTooManyRequests()
            ->assertJsonPath('success', false);
    }

    public function test_safe_upload_service_stores_allowed_media_with_safe_names_and_rejects_oversized_files(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $service = app(SafeUploadService::class);

        $storedPath = $service->store(
            UploadedFile::fake()->image('fresh-dates.jpg', 640, 480),
            'catalog',
        );

        Storage::disk('public')->assertExists($storedPath);
        $this->assertStringStartsWith('catalog/', $storedPath);
        $this->assertStringNotContainsString('fresh-dates', $storedPath);

        $privatePath = $service->store(
            UploadedFile::fake()->create('rfq-specification.pdf', 256, 'application/pdf'),
            '../rfq-documents',
            'local',
        );

        Storage::disk('local')->assertExists($privatePath);
        Storage::disk('public')->assertMissing($privatePath);
        $this->assertStringStartsWith('rfq-documents/', $privatePath);
        $this->assertStringNotContainsString('..', $privatePath);

        $this->expectException(\InvalidArgumentException::class);

        $service->store(
            UploadedFile::fake()->create('oversized-proof.jpg', 10 * 1024 + 1, 'image/jpeg'),
            'manual-proofs',
        );
    }

    public function test_safe_upload_service_rejects_executable_uploads(): void
    {
        Storage::fake('public');
        $service = app(SafeUploadService::class);

        $this->expectException(\InvalidArgumentException::class);

        $service->store(
            UploadedFile::fake()->create('payload.php', 1, 'application/x-php'),
            'catalog',
        );
    }

    public function test_frontend_uses_local_fonts_only(): void
    {
        $frontendRoot = dirname(__DIR__, 2).'/../frontend/src';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($frontendRoot));

        foreach ($files as $file) {
            if (! $file->isFile() || ! in_array($file->getExtension(), ['css', 'ts', 'tsx'], true)) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            $this->assertStringNotContainsString('next/font/google', $contents);
            $this->assertStringNotContainsString('fonts.googleapis.com', $contents);
            $this->assertStringNotContainsString('fonts.gstatic.com', $contents);
        }
    }
}
