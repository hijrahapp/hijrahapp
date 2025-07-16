<div class="flex items-center justify-center grow bg-center bg-no-repeat page-bg">
    <div class="kt-card max-w-[370px] w-full">
        <form wire:submit.prevent="submit" class="kt-card-content flex flex-col gap-5 p-10">
            <div class="text-center">
                <h3 class="text-lg font-medium text-mono">
                    Reset Password
                </h3>
                <span class="text-sm text-secondary-foreground">
                    Enter your new password
                </span>
            </div>
            @if($error)
                <div class="kt-error-message">{{ $error }}</div>
            @endif
            <div class="flex flex-col gap-1">
                <label class="kt-form-label text-mono">
                    New Password
                </label>
                <label class="kt-input" x-data="{ show: false }">
                    <input name="user_new_password" placeholder="Enter a new password" :type="show ? 'text' : 'password'" wire:model="password" />
                    <button type="button" @click="show = !show" class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5 ms-2">
                        <template x-if="show">
                            <i class="ki-filled ki-eye-slash text-muted-foreground"></i>
                        </template>
                        <template x-if="!show">
                            <i class="ki-filled ki-eye text-muted-foreground"></i>
                        </template>
                    </button>
                </label>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono">
                    Confirm New Password
                </label>
                <label class="kt-input" x-data="{ show: false }">
                    <input name="user_confirm_password" placeholder="Re-enter a new Password" :type="show ? 'text' : 'password'" wire:model="password_confirmation" />
                    <button type="button" @click="show = !show" class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5 ms-2">
                        <template x-if="show">
                            <i class="ki-filled ki-eye-slash text-muted-foreground"></i>
                        </template>
                        <template x-if="!show">
                            <i class="ki-filled ki-eye text-muted-foreground"></i>
                        </template>
                    </button>
                </label>
            </div>
            <button class="kt-btn kt-btn-primary flex justify-center grow" type="submit">
                Submit
            </button>
        </form>
    </div>
</div> 