<?php

namespace YourVendor\FilamentCosUpload\Tests\Feature;

use Orchestra\Testbench\TestCase;
use YourVendor\FilamentCosUpload\FilamentCosUploadServiceProvider;
use YourVendor\FilamentCosUpload\Services\CosSignatureService;

class CosUploadTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            FilamentCosUploadServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('filament-cos-upload.secret_id', 'test_secret_id');
        $app['config']->set('filament-cos-upload.secret_key', 'test_secret_key');
        $app['config']->set('filament-cos-upload.region', 'ap-beijing');
        $app['config']->set('filament-cos-upload.bucket', 'test-bucket');
    }

    public function test_signature_service_can_be_resolved()
    {
        $service = $this->app->make(CosSignatureService::class);
        
        $this->assertInstanceOf(CosSignatureService::class, $service);
    }

    public function test_can_generate_upload_signature()
    {
        $service = $this->app->make(CosSignatureService::class);
        
        $signature = $service->generateUploadSignature('test.jpg');
        
        $this->assertIsArray($signature);
        $this->assertArrayHasKey('url', $signature);
        $this->assertArrayHasKey('key', $signature);
        $this->assertArrayHasKey('policy', $signature);
        $this->assertArrayHasKey('signature', $signature);
        $this->assertArrayHasKey('secret_id', $signature);
        $this->assertArrayHasKey('expires', $signature);
    }

    public function test_signature_routes_are_registered()
    {
        $response = $this->postJson('/filament-cos-upload/signature', [
            'filename' => 'test.jpg',
            'file_size' => 1024,
            'file_type' => 'image/jpeg',
        ]);

        // Should return validation error or success depending on configuration
        $this->assertTrue(in_array($response->status(), [200, 422, 500]));
    }
}