<?php

namespace Jackillll\FilamentCosUpload\Commands;

use Illuminate\Console\Command;
use Jackillll\FilamentCosUpload\Services\CosSignatureService;

class TestCosUploadCommand extends Command
{
    protected $signature = 'filament-cos-upload:test';
    
    protected $description = 'Test COS upload configuration';

    public function handle()
    {
        $this->info('🔍 Testing COS Upload Configuration...');
        $this->newLine();

        // 1. 检查配置文件
        $this->info('1. Checking configuration...');
        $config = config('filament-cos-upload');
        
        if (!$config) {
            $this->error('❌ Configuration not found. Please publish the config file:');
            $this->line('   php artisan vendor:publish --tag=filament-cos-upload-config');
            return 1;
        }
        
        $this->info('✅ Configuration loaded');
        
        // 2. 检查必要的配置项
        $this->info('2. Checking required configuration values...');
        $required = ['region', 'bucket', 'app_id', 'secret_id', 'secret_key'];
        $missing = [];
        
        foreach ($required as $key) {
            if (empty($config[$key])) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            $this->error('❌ Missing required configuration: ' . implode(', ', $missing));
            $this->line('   Please update your .env file or config/filament-cos-upload.php');
            return 1;
        }
        
        $this->info('✅ All required configuration values are set');
        
        // 3. 检查COS服务
        $this->info('3. Testing COS service...');
        try {
            $service = app(CosSignatureService::class);
            $signature = $service->generateSignature('test.jpg', 1024, 'image/jpeg');
            
            if ($signature && isset($signature['url'], $signature['key'])) {
                $this->info('✅ COS service is working');
                $this->line('   Upload URL: ' . $signature['url']);
                $this->line('   File Key: ' . $signature['key']);
            } else {
                $this->error('❌ COS service returned invalid signature');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ COS service error: ' . $e->getMessage());
            return 1;
        }
        
        // 4. 检查路由
        $this->info('4. Checking routes...');
        $routes = app('router')->getRoutes();
        $cosRoutes = [];
        
        foreach ($routes as $route) {
            $uri = $route->uri();
            if (str_contains($uri, 'filament-cos-upload')) {
                $cosRoutes[] = $uri;
            }
        }
        
        if (empty($cosRoutes)) {
            $this->error('❌ COS upload routes not found');
            $this->line('   Please ensure the service provider is registered');
            return 1;
        }
        
        $this->info('✅ COS upload routes registered:');
        foreach ($cosRoutes as $route) {
            $this->line('   - ' . $route);
        }
        
        // 5. 检查JavaScript资源
        $this->info('5. Checking JavaScript assets...');
        $jsPath = __DIR__ . '/../../resources/js/filament-cos-upload.js';
        
        if (!file_exists($jsPath)) {
            $this->error('❌ JavaScript file not found: ' . $jsPath);
            return 1;
        }
        
        $this->info('✅ JavaScript file exists');
        
        // 6. 总结
        $this->newLine();
        $this->info('🎉 All tests passed! COS upload should be working correctly.');
        $this->newLine();
        
        $this->info('Next steps:');
        $this->line('1. Add ->cosUpload() to your FileUpload components');
        $this->line('2. Test file upload in your Filament forms');
        $this->line('3. Check browser console for any JavaScript errors');
        $this->line('4. Use the debug script in browser console if needed');
        
        return 0;
    }
}