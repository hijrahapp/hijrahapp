<div class="kt-modal hidden" data-kt-modal="true" id="expert_add_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[600px] top-[15%]">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">Add Expert</span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="closeModal">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form wire:submit.prevent="saveUser">
            <div class="kt-modal-body p-5 flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email <span class="text-destructive">*</span></label>
                    <input type="email" class="kt-input w-full" wire:model.defer="email" />
                    @error('email')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Name <span class="text-destructive">*</span></label>
                    <input type="text" class="kt-input w-full" wire:model.defer="name" />
                    @error('name')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Password <span class="text-destructive">*</span></label>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" class="kt-input w-full pr-10" wire:model.defer="password" />
                        <button type="button" @click="show = !show" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500">
                            <template x-if="show">
                                <i class="ki-filled ki-eye-slash"></i>
                            </template>
                            <template x-if="!show">
                                <i class="ki-filled ki-eye"></i>
                            </template>
                        </button>
                    </div>
                    @error('password')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Role <span class="text-destructive">*</span></label>
                    <select class="kt-select w-full" wire:model.defer="roleId" disabled>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('roleId')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                @if($error)
                    <div class="kt-error-message text-destructive">{{ $error }}</div>
                @endif
                <div class="flex justify-end mt-4">
                    <button type="button" class="kt-btn kt-btn-outline me-2" wire:click="closeModal">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
