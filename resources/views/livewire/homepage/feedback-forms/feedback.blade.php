<div class="kt-container-fixed kt-main-container" id="feedbackContainer">
    <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
        <h1 class="text-xl font-medium leading-none text-mono">
            Feedback Management
        </h1>
        @php $role = session('user')["role"] ?? null; @endphp
        @if($role === 'SuperAdmin' || $role === 'Admin')
        <button wire:click="openFeedbackFormManagement" class="kt-btn kt-btn-primary kt-btn-outline">
            <i class="ki-filled ki-message-text-2"></i>
            Manage Feedback Form
        </button>
        @endif
    </div>
    <livewire:homepage.feedback-forms.feedback-table />
</div>
