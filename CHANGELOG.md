# Changelog

All notable changes to `laravel-filament-client-upload-cos` will be documented in this file.

## [1.2.1] - 2024-01-25

### Fixed
- 修复 FilePond 组件 input 元素缺少 data 属性的问题
- 改进 JavaScript 智能检测机制，支持 FilePond 动态创建的 input 元素
- 增强父元素配置检测，自动复制 COS 配置到 FilePond input
- 更新 SOLUTION.md 文档，添加 FilePond 相关问题的解决方案
- 新增测试页面 test-filament-integration.html 用于验证 Filament 集成

### Enhanced
- 优化 shouldEnableCosUpload 方法，支持多层级配置检测
- 改进 observeDOM 方法，配合新的智能检测逻辑
- 增强对 Alpine.js 数据绑定的支持

## [1.2.0] - 2024-01-25

### Added
- 新增 TestCosUploadCommand Artisan 命令用于测试 COS 上传配置
- 新增 SOLUTION.md 文档，提供完整的问题解决方案
- 新增 USAGE_GUIDE.md 详细使用指南和调试步骤
- 新增 debug-cos-upload.js 浏览器调试脚本
- 新增 test-upload.html 测试页面用于验证上传功能

### Fixed
- 修复 JavaScript 事件绑定问题，支持 Filament 动态 SPA 应用
- 修复文件上传时的选项传递问题
- 改进 MutationObserver 和 Livewire 事件监听
- 修复 getUploadSignature 方法的参数传递
- 统一 JSON 字段命名（filename -> file_name）

### Enhanced
- 改进 JavaScript 代码以支持动态组件绑定
- 增强错误处理和调试功能
- 优化文件上传流程和用户体验
- 完善文档和示例代码

### Changed
- 更新 FilamentCosUploadServiceProvider 以注册测试命令
- 改进前端 JavaScript 的初始化和事件绑定逻辑

## [1.1.3] - 2024-01-XX

### Fixed
- 修复命名空间中的双反斜杠问题
- 修复 PHP 文件中的命名空间声明错误
- 修复 use 语句中的命名空间引用问题
- 确保所有文件的命名空间格式正确

### Changed
- 优化命名空间结构，提高代码可读性
- 统一所有文件的命名空间格式

## [1.1.1] - 2024-01-XX

### Fixed
- 修复命名空间中的双反斜杠问题
- 修复 PHP 文件中的命名空间声明错误
- 修复 use 语句中的命名空间引用问题
- 确保所有文件的命名空间格式正确

### Changed
- 优化命名空间结构，提高代码可读性
- 统一所有文件的命名空间格式

## [1.1.0] - 2024-01-XX

### Changed
- 更新包命名空间从 `YourVendor\FilamentCosUpload` 到 `Jackillll\FilamentCosUpload`
- 更新 composer.json 配置
- 改进项目结构和文档

## [1.0.0] - 2024-01-XX

### Added
- 初始版本发布
- 腾讯云 COS 客户端直传功能
- Filament FileUpload 组件扩展
- 安全的签名生成机制
- 前端 JavaScript 直传逻辑
- 进度显示和错误处理
- 配置文件和环境变量支持
- 完整的文档和使用示例

### Features
- 支持单文件和多文件上传
- 自定义上传路径
- 文件类型和大小限制
- 上传成功回调
- 与 Filament 完美集成
- 响应式设计支持

### Security
- 临时签名机制，避免密钥泄露
- 服务端验证文件类型和大小
- 签名时效性控制