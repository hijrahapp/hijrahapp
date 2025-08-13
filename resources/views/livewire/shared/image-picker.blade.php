<div class="space-y-2">
    <!-- Label -->
    <label class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    <!-- Image Container -->
    <div class="relative inline-block">
        <!-- File Input (invisible overlay) -->
        <input 
            type="file" 
            accept="image/*" 
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
            onchange="handleImageSelect(this, '{{ $this->getId() }}')"
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
            Supports PNG, JPG, JPEG (max 2MB)
        @endif
    </p>

    <!-- Error Display -->
    @error('image')
        <p class="text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

<script>
function handleImageSelect(input, componentId) {
    const file = input.files[0];
    if (!file) return;

    // Validate file type
    if (!file.type.startsWith('image/')) {
        showToast('Please select a valid image file', 'error');
        input.value = '';
        return;
    }

    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
        showToast('File size must be less than 2MB', 'error');
        input.value = '';
        return;
    }

    // Convert to base64
    const reader = new FileReader();
    reader.onload = function(e) {
        // Update Livewire component
        Livewire.find(componentId).set('value', e.target.result);
        
        // Update preview immediately
        updatePreview(componentId, e.target.result);
    };
    
    reader.onerror = function() {
        showToast('Error reading file', 'error');
        input.value = '';
    };
    
    reader.readAsDataURL(file);
}

function updatePreview(componentId, imageSrc) {
    const preview = document.getElementById('preview-' + componentId);
    const placeholder = document.getElementById('placeholder-' + componentId);
    
    if (preview) {
        preview.src = imageSrc;
    }
}

function showToast(message, type = 'info') {
    // Use Livewire's global dispatch to show toast
    Livewire.dispatch('show-toast', { 
        type: type, 
        message: message 
    });
}
</script>