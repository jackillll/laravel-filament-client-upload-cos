@php
    $id = $getId();
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            cosUploadEnabled: {{ $cosUploadEnabled ? 'true' : 'false' }},
            cosUploadPath: '{{ $cosUploadPath }}',
            cosUploadOptions: {{ json_encode($cosUploadOptions) }},
            signatureUrl: '{{ $signatureUrl }}',
            callbackUrl: '{{ $callbackUrl }}',
        }"
        {{ $attributes
            ->merge($getExtraAttributes(), escape: false)
            ->class([
                'filament-forms-file-upload-component',
                'cos-upload-wrapper' => $cosUploadEnabled,
            ])
        }}
    >
        <input
            {!! $isDisabled ? 'disabled' : null !!}
            id="{{ $id }}"
            type="file"
            {{ $getExtraInputAttributeBag()->class([
                'block w-full transition duration-75 rounded-lg shadow-sm border-gray-300 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70',
                'dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:border-primary-500' => config('filament.dark_mode'),
            ]) }}
            @if ($cosUploadEnabled)
                data-cos-upload="true"
                data-cos-path="{{ $cosUploadPath }}"
                data-cos-options="{{ json_encode($cosUploadOptions) }}"
            @endif
        />
        
        @if ($cosUploadEnabled)
            <div class="cos-upload-info mt-2 text-sm text-gray-600">
                <p>{{ __('Files will be uploaded directly to Tencent Cloud COS') }}</p>
            </div>
        @endif
    </div>
</x-dynamic-component>
