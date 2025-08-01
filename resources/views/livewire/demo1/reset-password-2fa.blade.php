<div class="flex items-center justify-center grow bg-center bg-no-repeat page-bg">
    <div class="kt-card max-w-[440px] w-full">
        <form wire:submit.prevent="submit" class="kt-card-content flex flex-col gap-5 p-10">
            <div class="text-center mb-2">
                <h3 class="text-lg font-medium text-mono mb-5">
                    Enter OTP
                </h3>
                <div class="flex flex-col">
                    <span class="text-sm text-secondary-foreground mb-1.5">
                        We have sent a OTP to your email
                    </span>
                    <span class="text-sm font-medium text-mono">
                        {{ $email }}
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap justify-between gap-2.5">
                <input id="otp1" class="kt-input focus:border-primary/10 focus:ring-3 focus:ring-primary/10 size-10 shrink-0 px-0 text-center w-16 text-xl" maxlength="1" type="text" wire:model="otp1" />
                <input id="otp2" class="kt-input focus:border-primary/10 focus:ring-3 focus:ring-primary/10 size-10 shrink-0 px-0 text-center w-16 text-xl" maxlength="1" type="text" wire:model="otp2" />
                <input id="otp3" class="kt-input focus:border-primary/10 focus:ring-3 focus:ring-primary/10 size-10 shrink-0 px-0 text-center w-16 text-xl" maxlength="1" type="text" wire:model="otp3" />
                <input id="otp4" class="kt-input focus:border-primary/10 focus:ring-3 focus:ring-primary/10 size-10 shrink-0 px-0 text-center w-16 text-xl" maxlength="1" type="text" wire:model="otp4" />
            </div>
            @if($error)
                <div class="kt-error-message">{{ $error }}</div>
            @endif
            @if($message)
                <div class="kt-error-message text-green-600">{{ $message }}</div>
            @endif
            <button class="kt-btn kt-btn-lg flex justify-center grow" type="submit">
                Verify
            </button>
            <div class="flex items-center justify-center mb-2">
                <span class="text-2sm text-secondary-foreground me-1.5">
                    Didn’t receive a code?
                </span>
                <a class="text-2sm kt-link-underlined" href="" wire:click="resendOtp">
                    Resend
                </a>
            </div>
        </form>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const inputs = [
                    document.getElementById('otp1'),
                    document.getElementById('otp2'),
                    document.getElementById('otp3'),
                    document.getElementById('otp4')
                ];
                inputs.forEach((input, idx) => {
                    input.addEventListener('input', function () {
                        if (this.value.length === 1 && idx < inputs.length - 1) {
                            inputs[idx + 1].focus();
                        }
                    });
                    input.addEventListener('keydown', function (e) {
                        if (e.key === 'Backspace' && this.value === '' && idx > 0) {
                            inputs[idx - 1].focus();
                        }
                    });
                });
            });
        </script>
    </div>
</div>
