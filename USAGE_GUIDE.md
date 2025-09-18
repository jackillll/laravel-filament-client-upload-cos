# COS 直传使用指南

## 问题诊断

如果您的文件上传仍然使用 `/livewire/upload-file` 接口而不是 COS 直传，请按以下步骤检查：

## 1. 确保正确使用 cosUpload() 方法

### ❌ 错误用法（仍会使用 Livewire 上传）
```php
use Filament\Forms\Components\FileUpload;

FileUpload::make('avatar')
    ->label('头像')
    ->image(); // 没有调用 cosUpload()
```

### ✅ 正确用法（启用 COS 直传）
```php
use Filament\Forms\Components\FileUpload;

FileUpload::make('avatar')
    ->label('头像')
    ->cosUpload() // 必须调用此方法启用 COS 直传
    ->image();
```

## 2. 检查配置文件

确保 `config/filament-cos-upload.php` 配置正确：

```php
return [
    'secret_id' => env('COS_SECRET_ID'),
    'secret_key' => env('COS_SECRET_KEY'),
    'region' => env('COS_REGION', 'ap-beijing'),
    'bucket' => env('COS_BUCKET'),
    'domain' => env('COS_DOMAIN'), // 可选，自定义域名
    'upload_path' => env('COS_UPLOAD_PATH', 'uploads/'),
    'expires' => env('COS_EXPIRES', 3600), // 签名有效期（秒）
];
```

## 3. 检查环境变量

确保 `.env` 文件包含必要的配置：

```env
COS_SECRET_ID=your_secret_id
COS_SECRET_KEY=your_secret_key
COS_REGION=ap-beijing
COS_BUCKET=your-bucket-name
COS_DOMAIN=https://your-bucket.cos.ap-beijing.myqcloud.com
COS_UPLOAD_PATH=uploads/
```

## 4. 验证 JavaScript 是否正确加载

### 检查浏览器开发者工具

1. 打开浏览器开发者工具（F12）
2. 切换到 Network 标签
3. 刷新页面
4. 查找 `filament-cos-upload.js` 文件是否成功加载

### 检查控制台错误

1. 切换到 Console 标签
2. 查看是否有 JavaScript 错误
3. 确认没有 404 或其他加载错误

## 5. 验证 HTML 属性

启用 COS 上传后，文件输入框应该包含以下属性：

```html
<input type="file" 
       data-cos-upload="true" 
       data-cos-path="uploads/" 
       data-cos-options="{}" />
```

### 检查方法：
1. 右键点击文件上传组件
2. 选择"检查元素"
3. 确认 `data-cos-upload="true"` 属性存在

## 6. 测试路由是否可访问

在浏览器中访问以下路由，确保返回正确响应：

- `GET /filament-cos-upload/signature` - 应该返回 405 Method Not Allowed（因为需要 POST）
- 使用 Postman 或类似工具 POST 到该路由进行测试

## 7. 完整示例

```php
<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // 基本用法
                Forms\Components\FileUpload::make('avatar')
                    ->label('头像')
                    ->cosUpload() // 🔥 关键：必须调用此方法
                    ->image()
                    ->maxSize(5120),

                // 自定义路径
                Forms\Components\FileUpload::make('document')
                    ->label('文档')
                    ->cosUpload('documents/') // 🔥 自定义上传路径
                    ->acceptedFileTypes(['pdf', 'doc'])
                    ->maxSize(10240),

                // 多文件上传
                Forms\Components\FileUpload::make('images')
                    ->label('图片')
                    ->cosUpload('gallery/') // 🔥 启用 COS 直传
                    ->image()
                    ->multiple()
                    ->maxFiles(5),

                // 配置上传选项
                Forms\Components\FileUpload::make('file')
                    ->label('文件')
                    ->cosUpload()
                    ->cosUploadConfig([
                        'maxSize' => 2048,
                        'allowedTypes' => ['jpg', 'png', 'gif']
                    ]),
            ]);
    }
}
```

## 8. 快速调试

### 🔧 自动调试脚本
在浏览器控制台中运行以下代码进行自动检查：
```javascript
// 复制并粘贴 debug-cos-upload.js 的内容到控制台
```

或者加载调试脚本：
```html
<script src="debug-cos-upload.js"></script>
```

### 🔍 手动调试步骤

#### 1. 检查JavaScript加载
打开浏览器开发者工具，在Console中输入：
```javascript
console.log(typeof FilamentCosUpload);
```
- ✅ 应该输出 `function`
- ❌ 如果是 `undefined` 说明JavaScript没有加载

#### 2. 检查HTML属性
在Elements面板中查看文件输入框，应该有以下属性：
```html
<input type="file" 
       data-cos-upload="true" 
       data-cos-options="{...}"
       data-cos-bound="true">
```

#### 3. 检查事件绑定
在Console中运行：
```javascript
document.querySelectorAll('input[type="file"][data-cos-upload="true"]').length
```
应该返回大于0的数字。

#### 4. 检查网络请求
在Network面板中，选择文件后应该看到：
- ✅ 请求到 `/filament-cos-upload/signature` 获取签名
- ✅ 请求到腾讯云COS进行上传
- ❌ 而不是 `/livewire/upload-file`

#### 5. 检查控制台日志
选择文件时应该看到类似日志：
```
COS Upload: Intercepted file selection [File]
COS Upload: Starting upload for file test.jpg
COS Upload: Upload completed {url: "...", key: "..."}
```

### 🛠️ 系统调试步骤

如果仍然有问题，请按以下步骤调试：

#### 步骤 1：检查服务提供者注册
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 步骤 2：检查资源是否发布
```bash
php artisan vendor:publish --tag=filament-cos-upload-config
```

#### 步骤 3：清除缓存
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### 步骤 4：检查 Filament 资源注册
确保在 `AppServiceProvider` 或 `FilamentServiceProvider` 中没有冲突的资源注册。

## 9. 常见问题

### Q: JavaScript 已加载但仍使用 Livewire 上传
**A:** 确保在 FileUpload 组件上调用了 `->cosUpload()` 方法

### Q: 获取签名失败
**A:** 检查环境变量配置和路由是否正确注册

### Q: 上传到 COS 失败
**A:** 检查 COS 配置（密钥、区域、存储桶）是否正确

### Q: 文件上传成功但前端没有响应
**A:** 检查回调路由和 JavaScript 事件处理

## 10. 验证是否正常工作

正常工作时，您应该看到：

1. 文件选择后，网络请求先访问 `/filament-cos-upload/signature`
2. 然后直接上传到腾讯云 COS（域名包含 `cos.ap-xxx.myqcloud.com`）
3. 上传完成后访问 `/filament-cos-upload/callback`
4. **不会** 看到 `/livewire/upload-file` 请求

如果仍然看到 `/livewire/upload-file` 请求，说明没有正确启用 COS 直传功能。