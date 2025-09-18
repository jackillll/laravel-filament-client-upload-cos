<?php

namespace Jackillll\FilamentCosUpload\Components\Concerns;

use Closure;

trait HasCosUpload
{
    protected bool $cosUploadEnabled = false;
    protected ?string $cosUploadPath = null;
    protected ?Closure $cosUploadCallback = null;
    protected array $cosUploadOptions = [];

    /**
     * Enable Tencent Cloud COS direct upload
     */
    public function cosUpload(
        ?string $path = null,
        ?Closure $callback = null,
        array $options = []
    ): static {
        $this->cosUploadEnabled = true;
        $this->cosUploadPath = $path;
        $this->cosUploadCallback = $callback;
        $this->cosUploadOptions = $options;

        // Add necessary attributes for frontend JavaScript
        $this->extraAttributes([
            'data-cos-upload' => 'true',
            'data-cos-path' => $path ?? config('filament-cos-upload.upload_path'),
            'data-cos-options' => json_encode($options),
        ]);

        return $this;
    }

    /**
     * Check if COS upload is enabled
     */
    public function isCosUploadEnabled(): bool
    {
        return $this->cosUploadEnabled;
    }

    /**
     * Get COS upload path
     */
    public function getCosUploadPath(): ?string
    {
        return $this->cosUploadPath;
    }

    /**
     * Get COS upload callback
     */
    public function getCosUploadCallback(): ?Closure
    {
        return $this->cosUploadCallback;
    }

    /**
     * Get COS upload options
     */
    public function getCosUploadOptions(): array
    {
        return $this->cosUploadOptions;
    }

    /**
     * Set custom COS upload configuration
     */
    public function cosUploadConfig(array $config): static
    {
        $this->cosUploadOptions = array_merge($this->cosUploadOptions, $config);
        
        $this->extraAttributes([
            'data-cos-options' => json_encode($this->cosUploadOptions),
        ]);

        return $this;
    }

    /**
     * Set COS upload success callback
     */
    public function cosUploadSuccess(Closure $callback): static
    {
        $this->cosUploadCallback = $callback;
        return $this;
    }
}
