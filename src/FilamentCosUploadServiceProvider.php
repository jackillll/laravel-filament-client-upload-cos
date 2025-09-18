<?php

namespace Jackillll\FilamentCosUpload;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Jackillll\FilamentCosUpload\Macros\FileUploadMacros;
use Jackillll\FilamentCosUpload\Services\CosSignatureService;
use Jackillll\FilamentCosUpload\Commands\TestCosUploadCommand;

class FilamentCosUploadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-cos-upload.php',
            'filament-cos-upload'
        );

        // Register COS signature service
        $this->app->singleton(CosSignatureService::class, function ($app) {
            return new CosSignatureService();
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-cos-upload');

        $this->publishes([
            __DIR__ . '/../config/filament-cos-upload.php' => config_path('filament-cos-upload.php'),
        ], 'filament-cos-upload-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-cos-upload'),
        ], 'filament-cos-upload-views');

        FilamentAsset::register([
            Js::make('filament-cos-upload', __DIR__ . '/../resources/js/filament-cos-upload.js'),
        ], 'jackillll/laravel-filament-client-upload-cos');

        // Register FileUpload macros
        FileUploadMacros::register();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                TestCosUploadCommand::class,
            ]);
        }
    }
}
