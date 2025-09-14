<div class="space-y-2">
    <!-- Label -->
    <label class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-destructive">*</span>
        @endif
    </label>

    <!-- Input Container -->
    <div class="space-y-3">
        <!-- URL Input -->
        <div>
            <input 
                type="url" 
                class="kt-input w-full @error('contentUrl') border-red-500 @enderror" 
                wire:model.live="contentUrl"
                placeholder="{{ $placeholder }}"
            >
            @error('contentUrl')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- OR Divider -->
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">OR</span>
            </div>
        </div>

        <!-- File Upload Area -->
        <div class="relative">
            <!-- File Input (invisible overlay) -->
            <input 
                type="file" 
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                wire:model="file"
                @if(!empty($allowedTypes)) accept=".{{ implode(',.', $allowedTypes) }}" @endif
            >

            <!-- Upload Display -->
            <div class="w-full min-h-[100px] border-2 border-dashed border-gray-300 rounded-lg overflow-hidden bg-gray-50 flex items-center justify-center relative hover:border-gray-400 transition-colors">
                @if($file || $contentUrl)
                    <!-- File Selected or Existing File -->
                    <div class="text-center p-4">
                        <div class="flex items-center justify-center mb-2">
                            @if($file)
                                <!-- New uploaded file -->
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            @else
                                <!-- Existing file from URL -->
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                            @endif
                        </div>
                        <p class="text-sm font-medium text-gray-900">
                            @if($file)
                                {{ $this->getFileName() }}
                            @else
                                {{ basename(parse_url($contentUrl, PHP_URL_PATH)) ?: 'External File' }}
                            @endif
                        </p>
                        @if($file)
                            <p class="text-xs text-gray-500">{{ $this->getFileSize() }}</p>
                        @else
                            @php
                                $urlPath = parse_url($contentUrl, PHP_URL_PATH);
                                $extension = strtoupper(pathinfo($urlPath, PATHINFO_EXTENSION));
                            @endphp
                            <p class="text-xs text-gray-500">{{ $extension ?: 'FILE' }} • External URL</p>
                        @endif
                        
                        <!-- Action Buttons -->
                        <div class="mt-2 flex items-center justify-center gap-3">
                            <button
                                type="button"
                                onclick="window.open('{{ $contentUrl ?: ($file ? $this->getFileUrl() : '') }}', '_blank')"
                                class="text-blue-500 hover:text-blue-700 text-xs underline"
                            >
                                View
                            </button>
                            <button
                                type="button"
                                wire:click="clear"
                                class="text-red-500 hover:text-red-700 text-xs underline"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Upload Placeholder -->
                    <div class="text-center p-4" id="upload-placeholder-{{ $this->getId() }}">
                        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="text-sm text-gray-600 mb-1">Click to upload file</p>
                        @if(!empty($allowedTypes))
                            <p class="text-xs text-gray-500">
                                Allowed types: {{ implode(', ', $allowedTypes) }}
                            </p>
                        @endif
                        <p class="text-xs text-gray-500">
                            Max size: {{ $this->formatFileSize($maxSize * 1024 * 1024) }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Help Text -->
    @if($helpText)
        <p class="text-xs text-gray-500">{{ $helpText }}</p>
    @else
        <p class="text-xs text-gray-500">
            @if(!empty($allowedTypes))
                Enter a URL ending with {{ implode(', ', $allowedTypes) }} or upload a file (max {{ $this->formatFileSize($maxSize * 1024 * 1024) }})
            @else
                Enter a file URL or upload a file (max {{ $this->formatFileSize($maxSize * 1024 * 1024) }})
            @endif
        </p>
    @endif

    <!-- File Error Display -->
    @error('file')
        <p class="text-xs text-destructive">{{ $message }}</p>
    @enderror

    <!-- Current Selection Display -->
    @if($contentUrl || $file)
        <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-xs">
            <div class="flex items-center justify-between">
                <span class="text-blue-700">
                    @if($file)
                        <strong>File:</strong> {{ $this->getFileName() }} ({{ $this->getFileSize() }})
                    @else
                        <strong>URL:</strong> {{ $contentUrl }}
                    @endif
                </span>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="window.open('{{ $contentUrl ?: ($file ? $this->getFileUrl() : '') }}', '_blank')"
                        class="text-blue-600 hover:text-blue-800 underline"
                    >
                        View
                    </button>
                    <button
                        type="button"
                        wire:click="clear"
                        class="text-blue-600 hover:text-blue-800 underline"
                    >
                        Clear
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
