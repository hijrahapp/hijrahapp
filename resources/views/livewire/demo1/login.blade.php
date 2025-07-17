<div class="flex flex-col items-center justify-center grow bg-center bg-no-repeat page-bg">
    <h1 class="text-2xl font-bold text-center mb-8">Welcome to Hijrah App</h1>
    <div class="kt-card max-w-[370px] w-full">
        <form wire:submit.prevent="login" class="kt-card-content flex flex-col gap-5 p-10">
            <div class="text-center mb-2.5">
                <h3 class="text-lg font-medium text-mono leading-none mb-2.5">
                    Sign in
                </h3>
            </div>
            @if($error)
                <div class="kt-error-message">{{ $error }}</div>
            @endif
            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono">Email</label>
                <input class="kt-input" placeholder="email@email.com" type="email" wire:model="email" />
            </div>
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between gap-1">
                    <label class="kt-form-label font-normal text-mono">Password</label>
                    <a class="text-sm kt-link shrink-0" href="{{ route('password.enter-email') }}">
                        Forgot Password?
                    </a>
                </div>
                <div class="kt-input flex items-center" x-data="{ show: false }">
                    <input name="user_password" placeholder="Enter Password" :type="show ? 'text' : 'password'" wire:model="password" class="grow" />
                    <button type="button" @click="show = !show" class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5 ms-2">
                        <template x-if="show">
                            <i class="ki-filled ki-eye-slash text-muted-foreground"></i>
                        </template>
                        <template x-if="!show">
                            <i class="ki-filled ki-eye text-muted-foreground"></i>
                        </template>
                    </button>
                </div>
            </div>
            <!-- <label class="kt-label">
                <input class="kt-checkbox kt-checkbox-sm" name="check" type="checkbox" wire:model="remember" />
                <span class="kt-checkbox-label">Remember me</span>
            </label> -->
            <button class="kt-btn kt-btn-primary flex justify-center grow" type="submit">
                Sign In
            </button>
        </form>
    </div>
</div>
