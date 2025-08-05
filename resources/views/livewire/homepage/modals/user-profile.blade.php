<div class="kt-modal hidden" data-kt-modal="true" id="user_profile_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[600px] top-[15%]">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">My Profile</span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="close">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form wire:submit.prevent="save">
            <div class="kt-modal-body p-5 flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" class="kt-input w-full" wire:model="email" disabled />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Name <span class="text-destructive">*</span></label>
                    <input type="text" class="kt-input w-full" wire:model="name" />
                    @error('name')<span class="text-destructive text-xs">{{ $message }}</span>@enderror
                </div>
{{--                <div>--}}
{{--                    <label class="block text-sm font-medium mb-1">Gender <span class="text-destructive">*</span></label>--}}
{{--                    <div class="flex gap-4">--}}
{{--                        <label class="flex items-center gap-2">--}}
{{--                            <input type="radio" wire:model="gender" value="male" />--}}
{{--                            Male--}}
{{--                        </label>--}}
{{--                        <label class="flex items-center gap-2">--}}
{{--                            <input type="radio" wire:model="gender" value="female" />--}}
{{--                            Female--}}
{{--                        </label>--}}
{{--                    </div>--}}
{{--                    @error('gender')<span class="text-destructive text-xs">{{ $message }}</span>@enderror--}}
{{--                </div>--}}
{{--                <div>--}}
{{--                    <label class="block text-sm font-medium mb-1">Date of birth <span class="text-destructive">*</span></label>--}}
{{--                    <input type="date" class="kt-input w-full" wire:model="birthdate" value="{{ $birthdate }}" max="{{ date('Y-m-d') }}" />--}}
{{--                    @error('birthdate')<span class="text-destructive text-xs">{{ $message }}</span>@enderror--}}
{{--                </div>--}}
                @if($error)
                    <div class="kt-error-message text-destructive">{{ $error }}</div>
                @endif
                <div class="flex justify-between items-center mt-4">
                    <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-toggle="#change_password_modal">Change Password</button>
                    <button type="submit" class="kt-btn kt-btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
