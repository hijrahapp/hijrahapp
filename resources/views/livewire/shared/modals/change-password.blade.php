<div class="kt-modal hidden" data-kt-modal="true" id="change_password_modal">
    <div class="kt-modal-content max-w-[600px] top-[15%]">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">Change Password</span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="close">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form wire:submit.prevent="save">
            <div class="kt-modal-body p-5 flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Current Password <span class="text-destructive">*</span></label>
                    <label class="kt-input" x-data="{ show: false }">
                        <input type="password" :type="show ? 'text' : 'password'" class="kt-input w-full" wire:model="current_password" required />
                        <button type="button" @click="show = !show" class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5 ms-2">
                            <template x-if="show">
                                <i class="ki-filled ki-eye-slash text-muted-foreground"></i>
                            </template>
                            <template x-if="!show">
                                <i class="ki-filled ki-eye text-muted-foreground"></i>
                            </template>
                        </button>
                    </label>
                    @error('current_password')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">New Password <span class="text-destructive">*</span></label>
                    <label class="kt-input" x-data="{ show: false }">
                        <input type="password" :type="show ? 'text' : 'password'" class="kt-input w-full" wire:model="new_password" required />
                        <button type="button" @click="show = !show" class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5 ms-2">
                            <template x-if="show">
                                <i class="ki-filled ki-eye-slash text-muted-foreground"></i>
                            </template>
                            <template x-if="!show">
                                <i class="ki-filled ki-eye text-muted-foreground"></i>
                            </template>
                        </button>
                    </label>
                    @error('new_password')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Confirm New Password <span class="text-destructive">*</span></label>
                    <label class="kt-input" x-data="{ show: false }">
                        <input type="password" :type="show ? 'text' : 'password'" class="kt-input w-full" wire:model="confirm_password" required />
                        <button type="button" @click="show = !show" class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5 ms-2">
                            <template x-if="show">
                                <i class="ki-filled ki-eye-slash text-muted-foreground"></i>
                            </template>
                            <template x-if="!show">
                                <i class="ki-filled ki-eye text-muted-foreground"></i>
                            </template>
                        </button>
                    </label>
                    @error('confirm_password')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
                @if($error)
                    <div class="kt-error-message text-destructive">{{ $error }}</div>
                @endif
                @if($success)
                    <div class="kt-success-message text-success">{{ $success }}</div>
                @endif
                <div class="flex justify-between items-center mt-4">
                    <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-toggle="#user_profile_modal">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-primary">Change Password</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
window.addEventListener('show-change-password-modal', function () {
    const modal = document.getElementById('change_password_modal');
    if (modal && typeof KTModal !== 'undefined' && KTModal.getInstance) {
        let instance = KTModal.getInstance(modal);
        if (!instance) {
            instance = new KTModal(modal);
        }
        instance.show();
    } else if (modal) {
        modal.classList.remove('hidden');
    }
});
</script>
