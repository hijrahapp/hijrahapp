<div class="flex flex-col items-center justify-center grow">
    <div class="kt-card max-w-[440px] w-full">
        <form wire:submit.prevent="login" class="kt-card-content flex flex-col gap-5 p-10">
            <div class="text-center mb-2.5">
                <div class="flex justify-center mb-2.5"><img class="max-w-16" src="/assets/media/app/logo.png" /></div>
                <h1 class="text-2xl font-bold text-center mb-5 text-primary">Welcome to Hijrah</h1>
                <h2 class="text-lg font-medium text-mono leading-none mb-2.5">
                    Login
                </h2>
                <h3 class="text-md font-medium text-mono leading-none mb-2.5 opacity-80">
                    Access administration panel
                </h3>
            </div>
            <div class="flex flex-col gap-1 mb-2.5">
                <label class="kt-form-label font-normal text-mono opacity-80 mb-2.5">Email address</label>
                <div class="kt-input kt-input-lg flex items-center">
                    <i class="ki-filled ki-sms"></i>
                    <input class="grow" placeholder="email@email.com" type="email" wire:model="email" />
                </div>
                @error('email')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
            </div>
            <div class="flex flex-col gap-1 mb-2.5">
                <div class="flex items-center justify-between gap-1 mb-2.5">
                    <label class="kt-form-label font-normal text-mono opacity-80">Password</label>
                    <a class="text-xs kt-link-underlined shrink-0" href="{{ route('password.enter-email') }}">
                        Forgot Password?
                    </a>
                </div>
                <div class="kt-input kt-input-lg flex items-center" x-data="{ show: false }">
                    <i class="ki-filled ki-lock-2"></i>
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
                @error('password')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
            </div>
            <!-- <label class="kt-label">
                <input class="kt-checkbox kt-checkbox-sm" name="check" type="checkbox" wire:model="remember" />
                <span class="kt-checkbox-label">Remember me</span>
            </label> -->
            @if($error)
                <div class="kt-error-message">{{ $error }}</div>
            @endif
            <button class="kt-btn kt-btn-lg flex justify-center grow" type="submit">
                Login
            </button>
        </form>
    </div>
</div>
