<?php

use Illuminate\Support\Facades\Route;
use Jackillll\\FilamentCosUpload\Http\Controllers\CosUploadController;

Route::prefix('filament-cos-upload')->group(function () {
    Route::post('/signature', [CosUploadController::class, 'getSignature'])->name('filament-cos-upload.signature');
    Route::post('/callback', [CosUploadController::class, 'callback'])->name('filament-cos-upload.callback');
});
