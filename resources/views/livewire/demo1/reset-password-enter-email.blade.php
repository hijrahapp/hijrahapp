<div class="flex items-center justify-center grow bg-center bg-no-repeat page-bg">
    <div class="kt-card max-w-[370px] w-full">
        <form wire:submit.prevent="submit" class="kt-card-content flex flex-col gap-5 p-10">
            <div class="text-center">
                <h3 class="text-lg font-medium text-mono">
                    Your Email
                </h3>
                <span class="text-sm text-secondary-foreground">
                    Enter your email to reset password
                </span>
            </div>
            @if($error)
                <div class="kt-error-message">{{ $error }}</div>
            @endif
            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono">
                    Email
                </label>
                <input class="kt-input" placeholder="email@email.com" type="email" wire:model="email" />
            </div>
            <button class="kt-btn kt-btn-primary flex justify-center grow" type="submit">
                Continue
                <i class="ki-filled ki-black-right"></i>
            </button>
        </form>
    </div>
</div> 