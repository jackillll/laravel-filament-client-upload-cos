/**
 * COS上传调试脚本
 * 在浏览器控制台中运行此脚本来检查COS上传配置
 */

(function() {
    console.log('🔍 开始COS上传调试检查...');
    
    // 1. 检查JavaScript是否已加载
    console.log('\n1. 检查JavaScript加载状态:');
    if (typeof FilamentCosUpload !== 'undefined') {
        console.log('✅ FilamentCosUpload类已加载');
    } else {
        console.log('❌ FilamentCosUpload类未加载');
        console.log('   请检查JavaScript文件是否正确引入');
    }
    
    // 2. 检查文件输入框
    console.log('\n2. 检查文件输入框:');
    const allFileInputs = document.querySelectorAll('input[type="file"]');
    const cosFileInputs = document.querySelectorAll('input[type="file"][data-cos-upload="true"]');
    const boundInputs = document.querySelectorAll('input[type="file"][data-cos-bound="true"]');
    
    console.log(`   总文件输入框数量: ${allFileInputs.length}`);
    console.log(`   启用COS上传的输入框: ${cosFileInputs.length}`);
    console.log(`   已绑定事件的输入框: ${boundInputs.length}`);
    
    if (cosFileInputs.length === 0) {
        console.log('❌ 没有找到启用COS上传的文件输入框');
        console.log('   请确保在FileUpload组件上调用了->cosUpload()方法');
    } else {
        console.log('✅ 找到启用COS上传的文件输入框');
        
        cosFileInputs.forEach((input, index) => {
            console.log(`   输入框 ${index + 1}:`);
            console.log(`     - data-cos-upload: ${input.dataset.cosUpload}`);
            console.log(`     - data-cos-options: ${input.dataset.cosOptions || '未设置'}`);
            console.log(`     - data-cos-bound: ${input.dataset.cosBound || '未绑定'}`);
            console.log(`     - name: ${input.name || '未设置'}`);
        });
    }
    
    // 3. 检查CSRF Token
    console.log('\n3. 检查CSRF Token:');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        console.log('✅ CSRF Token已设置');
    } else {
        console.log('❌ CSRF Token未设置');
        console.log('   请确保页面包含<meta name="csrf-token" content="...">');
    }
    
    // 4. 检查路由
    console.log('\n4. 检查COS上传路由:');
    fetch('/filament-cos-upload/signature', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '',
        },
        body: JSON.stringify({
            file_name: 'test.jpg',
            file_size: 1024,
            file_type: 'image/jpeg',
        }),
    })
    .then(response => {
        if (response.ok) {
            console.log('✅ COS签名路由可访问');
            return response.json();
        } else {
            console.log(`❌ COS签名路由返回错误: ${response.status} ${response.statusText}`);
            throw new Error(`HTTP ${response.status}`);
        }
    })
    .then(data => {
        console.log('   签名响应:', data);
    })
    .catch(error => {
        console.log(`❌ COS签名路由测试失败: ${error.message}`);
        console.log('   请检查路由是否正确注册');
    });
    
    // 5. 检查事件监听器
    console.log('\n5. 检查事件监听器:');
    let hasChangeListeners = false;
    cosFileInputs.forEach((input, index) => {
        const listeners = getEventListeners ? getEventListeners(input) : null;
        if (listeners && listeners.change && listeners.change.length > 0) {
            hasChangeListeners = true;
            console.log(`   输入框 ${index + 1} 有 ${listeners.change.length} 个change事件监听器`);
        }
    });
    
    if (!hasChangeListeners && typeof getEventListeners === 'undefined') {
        console.log('   无法检查事件监听器（需要Chrome DevTools）');
    } else if (!hasChangeListeners) {
        console.log('❌ 文件输入框没有change事件监听器');
        console.log('   请检查JavaScript是否正确绑定事件');
    }
    
    // 6. 提供手动测试函数
    console.log('\n6. 手动测试函数:');
    window.testCosUpload = function() {
        console.log('开始手动测试COS上传...');
        const input = document.querySelector('input[type="file"][data-cos-upload="true"]');
        if (!input) {
            console.log('❌ 没有找到COS上传输入框');
            return;
        }
        
        // 创建一个测试文件
        const testFile = new File(['test content'], 'test.txt', { type: 'text/plain' });
        
        // 模拟文件选择事件
        const event = new Event('change', { bubbles: true });
        Object.defineProperty(event, 'target', { value: input });
        Object.defineProperty(input, 'files', { value: [testFile] });
        
        input.dispatchEvent(event);
        console.log('已触发文件选择事件');
    };
    
    console.log('   运行 testCosUpload() 来手动测试上传功能');
    
    // 7. 检查Livewire/Alpine.js
    console.log('\n7. 检查前端框架:');
    if (typeof Livewire !== 'undefined') {
        console.log('✅ Livewire已加载');
    } else {
        console.log('⚠️  Livewire未检测到');
    }
    
    if (typeof Alpine !== 'undefined') {
        console.log('✅ Alpine.js已加载');
    } else {
        console.log('⚠️  Alpine.js未检测到');
    }
    
    console.log('\n🔍 调试检查完成!');
    console.log('\n如果发现问题，请按照以下步骤排查:');
    console.log('1. 确保在FileUpload组件上调用了->cosUpload()方法');
    console.log('2. 检查JavaScript文件是否正确加载');
    console.log('3. 检查COS上传路由是否正确注册');
    console.log('4. 检查CSRF Token是否设置');
    console.log('5. 在浏览器Network面板中查看请求是否发送到正确的URL');
})();