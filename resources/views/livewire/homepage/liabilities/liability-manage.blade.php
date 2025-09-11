<div class="kt-container-fixed kt-main-container" id="liabilityManageContainer">
    <div class="flex flex-wrap items-center gap-2 pb-4">
        <button wire:click="backToList" class="kt-btn kt-btn-outline flex items-center justify-center">
            <i class="ki-filled ki-arrow-left"></i>
        </button>
        <h1 class="text-xl font-medium leading-none text-mono">
            Manage Liability
        </h1>
    </div>

    <div class="grid grid-cols-1 gap-6 pb-4">
        <!-- Liability Details Section -->
        <div data-kt-accordion="true" class="kt-accordion" id="liability_manage_accordion">
            <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                <button
                    id="basic_info_toggle"
                    data-kt-accordion-toggle="true"
                    aria-controls="basic_info_content"
                    class="kt-accordion-toggle kt-card-header"
                >
                    <h3 class="kt-card-title">Liability Details</h3>
                    <span aria-hidden="true" class="kt-accordion-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                    </span>
                </button>
                <div class="kt-accordion-content" aria-labelledby="basic_info_toggle" id="basic_info_content" x-data="{ dirty: $wire.entangle('isBasicDirty') }" x-on:input="dirty = true" x-on:change="dirty = true" x-on:section-saved.window="if ($event.detail.section === 'basic') dirty = false">
                    <div class="kt-card-body p-5">
                        <div>
                            <label class="block text-sm font-medium mb-1">Name <span class="text-destructive">*</span></label>
                            <input type="text" class="kt-input w-full" wire:model.defer="name" placeholder="Enter liability name" />
                            @error('name')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Description <span class="text-destructive">*</span></label>
                            <textarea class="kt-textarea w-full" rows="3" wire:model.defer="description" placeholder="Enter liability description" maxlength="200"></textarea>
                            @error('description')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Title <span class="text-destructive">*</span></label>
                            <input type="text" class="kt-input w-full" wire:model.defer="title" placeholder="Enter liability title" />
                            @error('title')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Header</label>
                            <textarea class="kt-textarea w-full" rows="2" wire:model.defer="header" placeholder="Enter liability header"></textarea>
                            @error('header')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="kt-card-footer flex items-center justify-end gap-2">
                        <button class="kt-btn kt-btn-primary disabled:opacity-50 disabled:cursor-not-allowed" wire:click="saveBasicDetails" :disabled="!dirty" wire:loading.attr="disabled" wire:target="saveBasicDetails">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Placeholder for Liability Modules Management Section -->
        <div data-kt-accordion="true" class="kt-accordion">
            <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                <button
                    id="modules_management_toggle"
                    data-kt-accordion-toggle="true"
                    aria-controls="modules_management_content"
                    class="kt-accordion-toggle kt-card-header"
                >
                    <h3 class="kt-card-title">Liability Modules</h3>
                    <span aria-hidden="true" class="kt-accordion-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                    </span>
                </button>
                <div class="kt-accordion-content" aria-labelledby="modules_management_toggle" id="modules_management_content">
                    <div class="kt-card-body p-5">
                        <livewire:homepage.liabilities.modules.liability-modules-table :liability="$liability" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>