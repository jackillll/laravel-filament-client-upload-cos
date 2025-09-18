<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tencent Cloud COS Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Tencent Cloud COS settings for client-side upload.
    |
    */

    'secret_id' => env('COS_SECRET_ID'),
    'secret_key' => env('COS_SECRET_KEY'),
    'region' => env('COS_REGION', 'ap-beijing'),
    'bucket' => env('COS_BUCKET'),
    'domain' => env('COS_DOMAIN'),
    
    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */
    
    'max_file_size' => env('COS_MAX_FILE_SIZE', 10485760), // 10MB
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
    'upload_path' => env('COS_UPLOAD_PATH', 'uploads/'),
    
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */
    
    'signature_expires' => 3600, // 1 hour
    'callback_url' => env('COS_CALLBACK_URL'),
];
