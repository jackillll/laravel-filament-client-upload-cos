# COS直传问题解决方案

## 🎯 问题描述
JavaScript已经加载，但文件上传仍然使用 `/livewire/upload-file` 接口上传，而不是COS直传。

## 🔧 解决方案

### 1. 确保正确调用cosUpload()方法

**❌ 错误用法：**
```php
FileUpload::make('avatar')
    ->acceptedFileTypes(['image/*']);
```

**✅ 正确用法：**
```php
FileUpload::make('avatar')
    ->cosUpload()  // 🔥 必须添加这个方法
    ->acceptedFileTypes(['image/*']);
```

### 2. 检查JavaScript是否正确加载和绑定

更新后的JavaScript现在支持：
- ✅ 动态组件绑定（MutationObserver）
- ✅ Livewire事件监听
- ✅ Alpine.js事件监听
- ✅ 防止重复绑定
- ✅ 详细的调试日志

### 3. 使用调试工具

#### 快速检查
在浏览器控制台运行：
```javascript
// 检查JavaScript是否加载
console.log(typeof FilamentCosUpload);

// 检查COS输入框数量
console.log(document.querySelectorAll('input[type="file"][data-cos-upload="true"]').length);
```

#### 完整调试
加载调试脚本：
```html
<script src="debug-cos-upload.js"></script>
```

#### 系统测试
运行Artisan命令：
```bash
php artisan filament-cos-upload:test
```

## 🚀 完整示例

### Form类示例
```php
<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;

class CreateUser extends CreateRecord
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('avatar')
                    ->label('头像')
                    ->cosUpload()  // 启用COS直传
                    ->image()
                    ->maxSize(1024),
                    
                Forms\Components\FileUpload::make('documents')
                    ->label('文档')
                    ->cosUpload('documents/')  // 指定上传路径
                    ->multiple()
                    ->acceptedFileTypes(['pdf', 'doc', 'docx']),
                    
                Forms\Components\FileUpload::make('gallery')
                    ->label('图片库')
                    ->cosUpload()
                    ->cosUploadConfig([
                        'maxSize' => 2048,
                        'allowedTypes' => ['jpg', 'png', 'gif']
                    ])
                    ->multiple()
                    ->maxFiles(5),
            ]);
    }
}
```

### 自定义组件示例
```php
<?php

namespace App\Forms\Components;

use Jackillll\FilamentCosUpload\Components\FileUpload as BaseFileUpload;

class FileUpload extends BaseFileUpload
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // 默认启用COS上传
        $this->cosUpload();
    }
}
```

## 🔍 验证步骤

### 1. 检查HTML输出
文件输入框应该有这些属性：
```html
<input type="file" 
       data-cos-upload="true" 
       data-cos-options="{...}"
       data-cos-bound="true">
```

**注意：** 如果你看到的是 FilePond 生成的 input（如 `class="filepond--browser"`），这是正常的。我们的 JavaScript 代码会自动检测并配置这些元素。

### 2. 检查网络请求
选择文件后，Network面板应该显示：
- ✅ POST `/filament-cos-upload/signature` (获取签名)
- ✅ POST `https://xxx.cos.ap-xxx.myqcloud.com/` (COS上传)
- ❌ 不应该有 `/livewire/upload-file` 请求

### 3. 检查控制台日志
应该看到类似输出：
```
COS Upload bound to input: <input...>
COS Upload: Intercepted file selection [File]
COS Upload: Starting upload for file test.jpg
COS Upload: Upload completed {url: "...", key: "..."}
```

## 🛠️ 故障排除

### 问题1：JavaScript未加载
**解决方案：**
```bash
php artisan config:cache
php artisan view:clear
```

### 问题2：事件未绑定
**解决方案：**
检查是否调用了 `->cosUpload()` 方法

### 问题3：路由不存在
**解决方案：**
```bash
php artisan route:list | grep cos-upload
```

### 问题4：配置错误
**解决方案：**
```bash
php artisan filament-cos-upload:test
```

### 问题5：FilePond input 缺少 data 属性
**症状：** input 元素显示为 `<input class="filepond--browser" type="file" ...>` 但没有 `data-cos-upload` 等属性

**原因：** FilePond 库会创建自己的 input 元素，这些元素不会自动继承原始配置

**解决方案：**
1. **确保调用了 cosUpload() 方法：**
   ```php
   FileUpload::make('avatar')
       ->cosUpload() // 🔥 必须调用此方法
       ->image();
   ```

2. **检查父元素配置：**
   我们的 JavaScript 会自动检测父元素的 data 属性并复制到 FilePond 的 input 元素

3. **手动调试：**
   ```javascript
   // 在浏览器控制台运行
   document.querySelectorAll('input[type="file"]').forEach(input => {
       console.log('Input:', input);
       console.log('data-cos-upload:', input.dataset.cosUpload);
       console.log('Parent data-cos-upload:', input.parentElement.dataset.cosUpload);
   });
   ```

4. **强制重新绑定：**
   ```javascript
   // 在浏览器控制台运行
   new FilamentCosUpload();
   ```

## 📝 测试清单

- [ ] 在FileUpload组件上调用了 `->cosUpload()` 方法
- [ ] JavaScript文件已正确加载
- [ ] HTML输入框有 `data-cos-upload="true"` 属性
- [ ] 浏览器控制台没有JavaScript错误
- [ ] 网络请求发送到COS而不是Livewire
- [ ] 文件上传成功并返回COS URL

## 🎉 成功标志

当一切配置正确时，你应该看到：
1. 选择文件后立即开始上传进度
2. 网络请求直接发送到腾讯云COS
3. 上传完成后显示成功消息
4. 表单字段包含COS文件URL

如果仍有问题，请使用提供的调试工具进行详细检查。