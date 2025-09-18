<?php

namespace Jackillll\FilamentCosUpload\Macros;

use Closure;
use Filament\Forms\Components\FileUpload;

class FileUploadMacros
{
    public static function register(): void
    {
        FileUpload::macro('cosUpload', function (
            ?string $path = null,
            ?Closure $callback = null,
            array $options = []
        ): FileUpload {
            /** @var FileUpload $this */
            
            // Add COS upload attributes
            $this->extraAttributes([
                'data-cos-upload' => 'true',
                'data-cos-path' => $path ?? config('filament-cos-upload.upload_path'),
                'data-cos-options' => json_encode($options),
            ]);

            // Store callback for later use
            if ($callback) {
                $this->afterStateUpdated($callback);
            }

            return $this;
        });

        FileUpload::macro('cosUploadConfig', function (array $config): FileUpload {
            /** @var FileUpload $this */
            
            $existingOptions = json_decode($this->getExtraAttributes()['data-cos-options'] ?? '{}', true);
            $mergedOptions = array_merge($existingOptions, $config);
            
            $this->extraAttributes([
                'data-cos-options' => json_encode($mergedOptions),
            ]);

            return $this;
        });

        FileUpload::macro('cosUploadSuccess', function (Closure $callback): FileUpload {
            /** @var FileUpload $this */
            
            $this->afterStateUpdated($callback);
            
            return $this;
        });
    }
}
