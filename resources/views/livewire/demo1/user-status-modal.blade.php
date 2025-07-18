<div class="kt-modal hidden" data-kt-modal="true" id="user_status_modal" wire:ignore.self>
    <div class="kt-modal-content max-w-[600px] top-[15%]">
        <div class="kt-modal-header py-4 px-5">
            <span class="kt-modal-title text-xl font-semibold">
                {{ $active ? 'Activate User' : 'Deactivate User' }}
            </span>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="closeModal">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body p-5 flex flex-col gap-4">
            <p>
                Are you sure you want to {{ $confirmAction }} this user?
            </p>
            <div class="flex justify-end mt-4 gap-2">
                <button type="button" class="kt-btn kt-btn-outline" wire:click="closeModal">Cancel</button>
                <button type="button" class="kt-btn kt-btn-primary" wire:click="performConfirmAction">Yes, {{ ucfirst($confirmAction) }}</button>
            </div>
        </div>
    </div>
</div>
