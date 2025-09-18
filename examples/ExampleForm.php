<?php

namespace App\Filament\Resources\ExampleResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateExample extends CreateRecord
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // 基本用法 - 启用 COS 直传
                Forms\Components\FileUpload::make('avatar')
                    ->label('头像')
                    ->cosUpload() // 启用腾讯云 COS 直传
                    ->image()
                    ->imageEditor()
                    ->maxSize(5120), // 5MB

                // 自定义上传路径
                Forms\Components\FileUpload::make('document')
                    ->label('文档')
                    ->cosUpload('documents/') // 自定义上传路径
                    ->acceptedFileTypes(['application/pdf', 'application/msword'])
                    ->maxSize(10240), // 10MB

                // 多文件上传
                Forms\Components\FileUpload::make('images')
                    ->label('图片集')
                    ->cosUpload('gallery/')
                    ->image()
                    ->multiple()
                    ->maxFiles(5)
                    ->maxSize(2048), // 2MB per file

                // 配置上传选项
                Forms\Components\FileUpload::make('attachment')
                    ->label('附件')
                    ->cosUpload()
                    ->cosUploadConfig([
                        'max_file_size' => 20971520, // 20MB
                        'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
                    ])
                    ->cosUploadSuccess(function ($file) {
                        // 上传成功后的处理
                        \Log::info('文件上传成功', [
                            'file_url' => $file['url'],
                            'file_size' => $file['size'],
                            'file_key' => $file['key'],
                        ]);
                    }),

                // 使用自定义组件
                \Jackillll\FilamentCosUpload\Components\FileUpload::make('custom_file')
                    ->label('自定义文件上传')
                    ->cosUpload('custom/')
                    ->cosUploadSuccess(function ($file) {
                        // 可以在这里添加自定义逻辑
                        // 比如保存文件信息到数据库
                        \App\Models\FileRecord::create([
                            'filename' => basename($file['key']),
                            'url' => $file['url'],
                            'size' => $file['size'],
                            'uploaded_at' => now(),
                        ]);
                    }),

                // 条件性启用 COS 上传
                Forms\Components\FileUpload::make('conditional_file')
                    ->label('条件性文件上传')
                    ->cosUpload()
                    ->visible(fn () => config('filament-cos-upload.secret_id') !== null),
            ]);
    }
}
