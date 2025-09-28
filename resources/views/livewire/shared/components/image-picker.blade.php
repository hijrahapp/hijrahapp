<div class="space-y-2">
    <!-- Label -->
    <label class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-destructive">*</span>
        @endif
    </label>

    <!-- Image Container -->
    <div class="relative inline-block">
        <!-- File Input (invisible overlay) -->
        <input 
            type="file" 
            accept="image/jpeg,image/jpg,image/png,image/gif,image/bmp,image/webp,image/svg+xml,.ico" 
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
            wire:model="file"
        >

        <!-- Image Display -->
        <div class="w-24 h-24 border-2 border-dashed border-gray-300 rounded-lg overflow-hidden bg-gray-50 flex items-center justify-center relative">
            @if($value)
                <!-- Current Image -->
                <img 
                    src="{{ $value }}" 
                    alt="Selected image" 
                    class="w-full h-full object-cover"
                    id="preview-{{ $this->getId() }}"
                >
                
                <!-- Remove Button -->
                <button
                    type="button"
                    wire:click="clearImage"
                    data-kt-tooltip="true"
                    data-kt-tooltip-trigger="hover"
                    data-kt-tooltip-placement="right"
                    data-kt-image-input-remove="true"
                    class="kt-image-input-remove absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors z-20"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-x"
                        aria-hidden="true"
                    >
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                    <span data-kt-tooltip-content="true" class="kt-tooltip">Click to remove or revert</span>
                </button>
            @else
                <!-- Placeholder -->
                <div class="text-center" id="placeholder-{{ $this->getId() }}">
                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-xs text-gray-500">Click to upload</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Help Text -->
    <p class="text-xs text-gray-500">
        @if($value)
            Click to change image or use × to remove
        @else
            Supports PNG, JPG, JPEG, SVG, ICO (max 2MB)
        @endif
    </p>

    <!-- Error Display -->
    @error('file')
        <p class="text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>
