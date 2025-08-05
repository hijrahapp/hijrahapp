<div class="flex items-center justify-center grow bg-center bg-no-repeat page-bg">
    <div class="kt-card max-w-[440px] w-full">
        <form wire:submit.prevent="submit" class="kt-card-content flex flex-col gap-5 p-10">
            <div class="text-center">
                <h3 class="text-lg font-medium text-mono">
                    Forget Password
                </h3>
                <span class="text-sm text-secondary-foreground">
                    Enter your email to send otp
                </span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono opacity-80 mb-2.5">
                    Email address
                </label>
                <div class="kt-input kt-input-lg flex items-center">
                    <i class="ki-filled ki-sms"></i>
                    <input class="grow" placeholder="email@email.com" type="email" wire:model="email" />
                </div>
                @error('email')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
            </div>
            @if($error)
                <div class="kt-error-message">{{ $error }}</div>
            @endif
            <button class="kt-btn kt-btn-lg flex justify-center grow" type="submit">
                Verify
            </button>
        </form>
    </div>
</div>
