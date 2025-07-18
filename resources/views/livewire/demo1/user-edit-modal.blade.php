<div class="kt-modal hidden" data-kt-modal="true" id="user_edit_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[600px] top-[15%]">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">Edit User</span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="closeModal">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form wire:submit.prevent="saveUser">
            <div class="kt-modal-body p-5 flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email <span class="text-destructive">*</span></label>
                    <input type="email" class="kt-input w-full" wire:model.defer="email" disabled />
                    @error('email')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Name <span class="text-destructive">*</span></label>
                    <input type="text" class="kt-input w-full" wire:model.defer="name" />
                    @error('name')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Gender <span class="text-destructive">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="gender" value="male" />
                            Male
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="gender" value="female" />
                            Female
                        </label>
                    </div>
                    @error('gender')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Date of Birth <span class="text-destructive">*</span></label>
                    <input type="date" class="kt-input w-full" wire:model.defer="birthDate" max="{{ date('Y-m-d') }}" />
                    @error('birthDate')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Role <span class="text-destructive">*</span></label>
                    <select class="kt-select w-full" wire:model.defer="roleId">
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('roleId')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" class="kt-btn kt-btn-outline me-2" wire:click="closeModal">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
