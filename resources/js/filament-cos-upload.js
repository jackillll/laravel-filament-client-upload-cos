class FilamentCosUpload {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.bindFileInputs();
        });
    }

    bindFileInputs() {
        const fileInputs = document.querySelectorAll('input[type="file"][data-cos-upload="true"]');
        
        fileInputs.forEach(input => {
            input.addEventListener('change', (event) => {
                this.handleFileSelect(event);
            });
        });
    }

    async handleFileSelect(event) {
        const input = event.target;
        const files = input.files;
        
        if (!files || files.length === 0) {
            return;
        }

        const file = files[0];
        const options = JSON.parse(input.dataset.cosOptions || '{}');
        
        try {
            await this.uploadFile(file, input, options);
        } catch (error) {
            console.error('Upload failed:', error);
            this.showError(input, error.message);
        }
    }

    async uploadFile(file, input, options) {
        // Show upload progress
        this.showProgress(input, 0);
        
        try {
            // Get upload signature from server
            const signature = await this.getUploadSignature(file);
            
            // Upload file to COS
            const result = await this.uploadToCos(file, signature, (progress) => {
                this.showProgress(input, progress);
            });
            
            // Notify server about successful upload
            await this.notifyUploadComplete(result);
            
            // Update input value with uploaded file URL
            this.updateInputValue(input, result);
            
            this.showSuccess(input);
            
        } catch (error) {
            this.showError(input, error.message);
            throw error;
        }
    }

    async getUploadSignature(file) {
        const response = await fetch('/filament-cos-upload/signature', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                filename: file.name,
                file_size: file.size,
                file_type: file.type,
            }),
        });

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