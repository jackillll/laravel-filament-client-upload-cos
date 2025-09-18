<?php

namespace YourVendor\FilamentCosUpload\Components;

use Filament\Forms\Components\FileUpload as BaseFileUpload;
use YourVendor\FilamentCosUpload\Components\Concerns\HasCosUpload;

class FileUpload extends BaseFileUpload
{
    use HasCosUpload;

    protected string $view = 'filament-cos-upload::components.file-upload';

    /**
     * Get the view data for the component
     */
    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'cosUploadEnabled' => $this->isCosUploadEnabled(),
            'cosUploadPath' => $this->getCosUploadPath(),
            'cosUploadOptions' => $this->getCosUploadOptions(),
            'signatureUrl' => route('filament-cos-upload.signature'),
            'callbackUrl' => route('filament-cos-upload.callback'),
        ]);
    }
}