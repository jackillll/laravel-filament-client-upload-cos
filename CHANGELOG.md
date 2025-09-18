# Changelog

All notable changes to `laravel-filament-client-upload-cos` will be documented in this file.

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