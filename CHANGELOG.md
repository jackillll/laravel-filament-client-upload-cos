# Changelog

All notable changes to `laravel-filament-client-upload-cos` will be documented in this file.

## [1.1.0] - 2024-12-19

### Added
- 改进了包的命名空间和vendor名称
- 优化了代码结构和文档
- 增强了错误处理机制
- 改进了前端JavaScript的兼容性

### Changed
- 更新了composer.json中的包名和命名空间
- 优化了服务提供者的注册逻辑
- 改进了配置文件的默认值

### Fixed
- 修复了在某些环境下的兼容性问题
- 改进了文件上传的错误提示

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