# Laravel Filament Client Upload COS

一个为 Laravel Filament 提供腾讯云 COS 客户端直传功能的扩展包。

## 特性

- 🚀 客户端直传到腾讯云 COS，减轻服务器负担
- 🔒 安全的签名机制，保护您的密钥
- 📱 支持进度显示和错误处理
- 🎨 与 Filament 完美集成
- ⚡ 简单易用的 API

## 安装

使用 Composer 安装扩展包：

```bash
composer require your-vendor/laravel-filament-client-upload-cos
```

发布配置文件：

```bash
php artisan vendor:publish --tag="filament-cos-upload-config"
```

## 配置

在 `.env` 文件中添加腾讯云 COS 配置：

```env
COS_SECRET_ID=your_secret_id
COS_SECRET_KEY=your_secret_key
COS_REGION=ap-beijing
COS_BUCKET=your_bucket_name
COS_DOMAIN=https://your_bucket_name.cos.ap-beijing.myqcloud.com
COS_UPLOAD_PATH=uploads/
COS_MAX_FILE_SIZE=10485760
```

## 使用方法

### 基本用法

在 Filament 表单中使用 COS 直传：

```php
use Filament\Forms\Components\FileUpload;

FileUpload::make('avatar')
    ->cosUpload() // 启用 COS 直传
    ->acceptedFileTypes(['image/jpeg', 'image/png'])
    ->maxSize(5120); // 5MB
```

### 自定义上传路径

```php
FileUpload::make('document')
    ->cosUpload('documents/') // 自定义上传路径
    ->acceptedFileTypes(['application/pdf']);
```

### 配置上传选项

```php
FileUpload::make('image')
    ->cosUpload()
    ->cosUploadConfig([
        'max_file_size' => 2097152, // 2MB
        'allowed_extensions' => ['jpg', 'png', 'gif'],
    ]);
```

### 上传成功回调

```php
FileUpload::make('file')
    ->cosUpload()
    ->cosUploadSuccess(function ($file) {
        // 处理上传成功后的逻辑
        Log::info('File uploaded successfully', ['file' => $file]);
    });
```

### 使用自定义组件

如果您需要更多控制，可以使用扩展包提供的自定义组件：

```php
use YourVendor\FilamentCosUpload\Components\FileUpload;

FileUpload::make('attachment')
    ->cosUpload('attachments/')
    ->cosUploadSuccess(function ($file) {
        // 自定义处理逻辑
    });
```

## 配置选项

在 `config/filament-cos-upload.php` 中可以配置以下选项：

```php
return [
    // 腾讯云 COS 配置
    'secret_id' => env('COS_SECRET_ID'),
    'secret_key' => env('COS_SECRET_KEY'),
    'region' => env('COS_REGION', 'ap-beijing'),
    'bucket' => env('COS_BUCKET'),
    'domain' => env('COS_DOMAIN'),
    
    // 上传配置
    'max_file_size' => env('COS_MAX_FILE_SIZE', 10485760), // 10MB
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
    'upload_path' => env('COS_UPLOAD_PATH', 'uploads/'),
    
    // 安全配置
    'signature_expires' => 3600, // 1小时
    'callback_url' => env('COS_CALLBACK_URL'),
];
```

## 工作原理

1. 用户选择文件后，前端 JavaScript 向服务器请求上传签名
2. 服务器生成安全的上传签名并返回给前端
3. 前端使用签名直接上传文件到腾讯云 COS
4. 上传完成后，前端通知服务器更新文件信息

## 安全性

- 使用临时签名，避免在前端暴露密钥
- 支持文件类型和大小限制
- 签名有时效性，防止滥用

## 要求

- PHP 8.1+
- Laravel 10.0+ 或 11.0+
- Filament 3.0+

## 许可证

MIT License

## 贡献

欢迎提交 Issue 和 Pull Request！

## 支持

如果您在使用过程中遇到问题，请：

1. 查看文档和示例
2. 搜索已有的 Issues
3. 创建新的 Issue 并提供详细信息