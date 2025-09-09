<div class="kt-container-fixed kt-main-container" id="programManageContainer">
    <div class="flex flex-wrap items-center gap-2 pb-4">
        <button wire:click="backToList" class="kt-btn kt-btn-outline flex items-center justify-center">
            <i class="ki-filled ki-arrow-left"></i>
        </button>
        <h1 class="text-xl font-medium leading-none text-mono">
            Manage Program
        </h1>
    </div>

    <div class="grid grid-cols-1 gap-6 pb-4">
        <!-- Program Details Section -->
        <div data-kt-accordion="true" class="kt-accordion" id="program_manage_accordion">
            <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                <button
                    id="basic_info_toggle"
                    data-kt-accordion-toggle="true"
                    aria-controls="basic_info_content"
                    class="kt-accordion-toggle kt-card-header"
                >
                    <h3 class="kt-card-title">Program Details</h3>
                    <span aria-hidden="true" class="kt-accordion-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                    </span>
                </button>
                <div class="kt-accordion-content" aria-labelledby="basic_info_toggle" id="basic_info_content" x-data="{ dirty: $wire.entangle('isBasicDirty') }" x-on:input="dirty = true" x-on:change="dirty = true" x-on:section-saved.window="if ($event.detail.section === 'basic') dirty = false">
                    <div class="kt-card-body p-5">
                        <div>
                            <label class="block text-sm font-medium mb-1">Name <span class="text-destructive">*</span></label>
                            <input type="text" class="kt-input w-full" wire:model.defer="name" placeholder="Enter program name" />
                            @error('name')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div class="mt-4">
                            <livewire:shared.components.textarea :label="'Description'" wire:model.defer="description" :required="true" :placeholder="'Enter program description'" :rows="3" :maxlength="500" />
                            @error('description')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div class="mt-4">
                            <livewire:shared.components.textarea :label="'Definition'" wire:model.defer="definition" :required="true" :placeholder="'Enter program definition'" :rows="3" />
                            @error('definition')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Steps</label>
                            <livewire:shared.components.rich-text :model="$steps" wire:model="steps" :placeholder="'Enter program steps'" :editorId="'program_steps_editor'" :minHeight="'150px'" />
                            @error('steps')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="kt-card-footer flex items-center justify-end gap-2">
                        <button class="kt-btn kt-btn-primary disabled:opacity-50 disabled:cursor-not-allowed" wire:click="saveBasicDetails" :disabled="!dirty" wire:loading.attr="disabled" wire:target="saveBasicDetails">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Program Modules Management Section -->
        <div data-kt-accordion="true" class="kt-accordion">
            <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                <button
                    id="modules_management_toggle"
                    data-kt-accordion-toggle="true"
                    aria-controls="modules_management_content"
                    class="kt-accordion-toggle kt-card-header"
                >
                    <h3 class="kt-card-title">Program Modules</h3>
                    <span aria-hidden="true" class="kt-accordion-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                    </span>
                </button>
                <div class="kt-accordion-content" aria-labelledby="modules_management_toggle" id="modules_management_content">
                    <div class="kt-card-body p-5">
                        <div class="flex justify-between items-center mb-4">
                            <p class="text-sm text-gray-600">Manage modules associated with this program</p>
                            <button class="kt-btn kt-btn-outline" data-kt-modal-toggle="#program_module_add_modal">
                                <i class="ki-filled ki-plus"></i>
                                Add Module
                            </button>
                        </div>

                        <div class="kt-scrollable-x-auto">
                            <table class="kt-table kt-table-border table-fixed w-full">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Module Name</th>
                                        <th>Methodology</th>
                                        <th>Pillar</th>
                                        <th class="text-center">Score Range</th>
                                        <th class="w-20 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No modules assigned to this program</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Program Steps Management Section -->
        <div data-kt-accordion="true" class="kt-accordion">
            <div class="kt-card kt-accordion-item active" data-kt-accordion-item="true">
                <button
                    id="steps_management_toggle"
                    data-kt-accordion-toggle="true"
                    aria-controls="steps_management_content"
                    class="kt-accordion-toggle kt-card-header"
                >
                    <h3 class="kt-card-title">Program Steps</h3>
                    <span aria-hidden="true" class="kt-accordion-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus kt-accordion-indicator-on" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus kt-accordion-indicator-off" aria-hidden="true"><path d="M5 12h14"></path></svg>
                    </span>
                </button>
                <div class="kt-accordion-content" aria-labelledby="steps_management_toggle" id="steps_management_content">
                    <div class="kt-card-body p-5">
                        <livewire:homepage.programs.steps.program-steps-table :program="$program" />
                        <livewire:homepage.programs.steps.program-step-add-modal />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
