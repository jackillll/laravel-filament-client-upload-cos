# Changelog

All notable changes to `laravel-filament-client-upload-cos` will be documented in this file.

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