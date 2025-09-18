<?php

namespace Jackillll\\FilamentCosUpload\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Jackillll\\FilamentCosUpload\Services\CosSignatureService;
use Illuminate\Support\Facades\Validator;

class CosUploadController extends Controller
{
    protected CosSignatureService $signatureService;

    public function __construct(CosSignatureService $signatureService)
    {
        $this->signatureService = $signatureService;
    }

    /**
     * Get upload signature for client-side upload
     */
    public function getSignature(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required|string|max:255',
            'file_size' => 'required|integer|max:' . config('filament-cos-upload.max_file_size'),
            'file_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $filename = $request->input('filename');
        $fileSize = $request->input('file_size');
        $fileType = $request->input('file_type');

        // Check file extension
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExtensions = config('filament-cos-upload.allowed_extensions');
        
        if (!in_array($extension, $allowedExtensions)) {
            return response()->json([
                'success' => false,
                'message' => 'File type not allowed'
            ], 422);
        }

        try {
            $signature = $this->signatureService->generateUploadSignature($filename, [
                ['content-length-range', $fileSize, $fileSize],
                ['starts-with', '$Content-Type', $fileType],
            ]);

            return response()->json([
                'success' => true,
                'data' => $signature
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate signature: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle upload callback
     */
    public function callback(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'url' => 'required|url',
            'size' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Here you can add custom logic to handle the upload callback
        // For example, save file information to database, trigger events, etc.

        return response()->json([
            'success' => true,
            'message' => 'Upload completed successfully',
            'data' => [
                'key' => $request->input('key'),
                'url' => $request->input('url'),
                'size' => $request->input('size'),
            ]
        ]);
    }
}
