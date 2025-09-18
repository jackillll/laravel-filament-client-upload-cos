class FilamentCosUpload {
    constructor() {
        this.init();
    }

    init() {
        // 立即绑定已存在的元素
        this.bindFileInputs();
        
        // 监听DOM变化以支持动态加载的组件
        this.observeDOM();
        
        // 监听Livewire组件更新
        this.bindLivewireEvents();
    }

    bindFileInputs() {
        const fileInputs = document.querySelectorAll('input[type="file"][data-cos-upload="true"]:not([data-cos-bound])');
        
        fileInputs.forEach(input => {
            // 标记已绑定，避免重复绑定
            input.setAttribute('data-cos-bound', 'true');
            
            // 绑定change事件
            input.addEventListener('change', (event) => {
                this.handleFileSelect(event);
            });
            
            console.log('COS Upload bound to input:', input);
        });
    }

    observeDOM() {
        // 使用MutationObserver监听DOM变化
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // 检查新添加的节点中是否有文件输入框
                            const fileInputs = node.querySelectorAll ? 
                                node.querySelectorAll('input[type="file"][data-cos-upload="true"]:not([data-cos-bound])') : [];
                            
                            fileInputs.forEach(input => {
                                input.setAttribute('data-cos-bound', 'true');
                                input.addEventListener('change', (event) => {
                                    this.handleFileSelect(event);
                                });
                                console.log('COS Upload bound to dynamically added input:', input);
                            });
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    bindLivewireEvents() {
        // 监听Livewire事件
        document.addEventListener('livewire:load', () => {
            console.log('Livewire loaded, binding COS upload inputs');
            this.bindFileInputs();
        });

        document.addEventListener('livewire:update', () => {
            console.log('Livewire updated, binding COS upload inputs');
            setTimeout(() => {
                this.bindFileInputs();
            }, 100);
        });

        // 监听Alpine.js事件（Filament v3使用Alpine.js）
        document.addEventListener('alpine:init', () => {
            console.log('Alpine.js initialized, binding COS upload inputs');
            this.bindFileInputs();
        });
    }

    async handleFileSelect(event) {
        const input = event.target;
        const files = Array.from(input.files);
        
        if (files.length === 0) return;

        // 阻止默认的Livewire上传行为
        event.preventDefault();
        event.stopPropagation();
        
        console.log('COS Upload: Intercepted file selection', files);

        // 清空input的值，防止Livewire处理
        const originalFiles = input.files;
        input.value = '';

        for (const file of files) {
            try {
                console.log('COS Upload: Starting upload for file', file.name);
                const result = await this.uploadFile(file, input);
                console.log('COS Upload: Upload completed', result);
                
                // 通知Livewire组件文件已上传
                this.notifyLivewireUpload(input, result);
                
            } catch (error) {
                console.error('COS Upload failed:', error);
                this.showError(input, error.message);
                
                // 如果上传失败，恢复原始文件选择
                this.restoreFileSelection(input, originalFiles);
            }
        }
    }

    notifyLivewireUpload(input, result) {
        // 创建一个自定义事件通知Livewire组件
        const event = new CustomEvent('cos-upload-complete', {
            detail: {
                url: result.url,
                key: result.key,
                size: result.size,
                input: input
            },
            bubbles: true
        });
        
        input.dispatchEvent(event);
        
        // 更新隐藏字段的值
        this.updateInputValue(input, result);
        
        // 触发Livewire的change事件
        const changeEvent = new Event('change', { bubbles: true });
        input.dispatchEvent(changeEvent);
    }

    restoreFileSelection(input, files) {
        // 尝试恢复文件选择（某些浏览器可能不支持）
        try {
            const dt = new DataTransfer();
            for (let i = 0; i < files.length; i++) {
                dt.items.add(files[i]);
            }
            input.files = dt.files;
        } catch (error) {
            console.warn('Cannot restore file selection:', error);
        }
    }

    async uploadFile(file, input, options = {}) {
        // 获取COS上传选项
        const cosOptions = JSON.parse(input.dataset.cosOptions || '{}');
        const mergedOptions = { ...cosOptions, ...options };
        
        console.log('COS Upload options:', mergedOptions);
        
        // Show upload progress
        this.showProgress(input, 0);
        
        try {
            // Get upload signature from server
            const signature = await this.getUploadSignature(file, mergedOptions);
            
            // Upload file to COS
            const result = await this.uploadToCos(file, signature, (progress) => {
                this.showProgress(input, progress);
            });
            
            // Notify server about successful upload
            await this.notifyUploadComplete(result);
            
            // Update input value with uploaded file URL
            this.updateInputValue(input, result);
            
            this.showSuccess(input);
            
            return result;
        } catch (error) {
            this.showError(input, error.message);
            throw error;
        }
    }

    async getUploadSignature(file, options = {}) {
        const response = await fetch('/filament-cos-upload/signature', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                file_name: file.name,
                file_size: file.size,
                file_type: file.type,
                options: options,
            }),
        });

        if (!response.ok) {
            throw new Error(`Failed to get upload signature: ${response.statusText}`);
        }

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to get upload signature');
        }
        
        return data.data;
    }

    async uploadToCos(file, signature, onProgress) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('key', signature.key);
            formData.append('policy', signature.policy);
            formData.append('q-sign-algorithm', 'sha1');
            formData.append('q-ak', signature.secret_id);
            formData.append('q-key-time', signature.expires);
            formData.append('q-signature', signature.signature);
            formData.append('file', file);

            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', (event) => {
                if (event.lengthComputable) {
                    const progress = Math.round((event.loaded / event.total) * 100);
                    onProgress(progress);
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status === 204) {
                    resolve({
                        key: signature.key,
                        url: `${signature.url}/${signature.key}`,
                        size: file.size,
                    });
                } else {
                    reject(new Error(`Upload failed with status: ${xhr.status}`));
                }
            });

            xhr.addEventListener('error', () => {
                reject(new Error('Upload failed due to network error'));
            });

            xhr.open('POST', signature.url);
            xhr.send(formData);
        });
    }

    async notifyUploadComplete(result) {
        const response = await fetch('/filament-cos-upload/callback', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(result),
        });

        const data = await response.json();
        
        if (!data.success) {
            console.warn('Upload callback failed:', data.message);
        }
    }

    updateInputValue(input, result) {
        // Create a hidden input to store the uploaded file URL
        let hiddenInput = input.parentNode.querySelector('input[name$="_cos_url"]');
        
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = input.name + '_cos_url';
            input.parentNode.appendChild(hiddenInput);
        }
        
        hiddenInput.value = result.url;
        
        // Trigger change event for Filament to detect the change
        const event = new Event('change', { bubbles: true });
        hiddenInput.dispatchEvent(event);
    }

    showProgress(input, progress) {
        let progressBar = input.parentNode.querySelector('.cos-upload-progress');
        
        if (!progressBar) {
            progressBar = document.createElement('div');
            progressBar.className = 'cos-upload-progress';
            progressBar.innerHTML = `
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
                <div class="progress-text">0%</div>
            `;
            input.parentNode.appendChild(progressBar);
        }
        
        const progressFill = progressBar.querySelector('.progress-fill');
        const progressText = progressBar.querySelector('.progress-text');
        
        progressFill.style.width = `${progress}%`;
        progressText.textContent = `${progress}%`;
        
        if (progress === 100) {
            setTimeout(() => {
                progressBar.remove();
            }, 1000);
        }
    }

    showSuccess(input) {
        this.showMessage(input, 'Upload completed successfully', 'success');
    }

    showError(input, message) {
        this.showMessage(input, message, 'error');
    }

    showMessage(input, message, type) {
        let messageEl = input.parentNode.querySelector('.cos-upload-message');
        
        if (!messageEl) {
            messageEl = document.createElement('div');
            messageEl.className = 'cos-upload-message';
            input.parentNode.appendChild(messageEl);
        }
        
        messageEl.className = `cos-upload-message ${type}`;
        messageEl.textContent = message;
        
        setTimeout(() => {
            messageEl.remove();
        }, 3000);
    }
}

// Initialize the upload handler
new FilamentCosUpload();

// Add CSS styles
const style = document.createElement('style');
style.textContent = `
    .cos-upload-progress {
        margin-top: 8px;
    }
    
    .progress-bar {
        width: 100%;
        height: 4px;
        background-color: #e5e7eb;
        border-radius: 2px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background-color: #3b82f6;
        transition: width 0.3s ease;
    }
    
    .progress-text {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }
    
    .cos-upload-message {
        margin-top: 8px;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .cos-upload-message.success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .cos-upload-message.error {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
`;
document.head.appendChild(style);