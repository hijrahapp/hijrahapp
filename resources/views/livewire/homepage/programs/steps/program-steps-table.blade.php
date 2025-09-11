<div>
    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        <div class="kt-card-header flex justify-between items-center">
            <h3 class="kt-card-title">Steps</h3>
            <div class="flex gap-2 items-center">
                <div class="kt-input max-w-48">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="Search Steps" wire:model.live="search" />
                </div>
                <button class="kt-btn kt-btn-outline flex items-center justify-center" title="Add Step" wire:click="openAddModal">
                    <i class="ki-filled ki-plus"></i>
                </button>
            </div>
        </div>
        <div class="kt-card-table">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border table-fixed w-full">
                    <thead>
                        <tr>
                            <th class="">Name</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Duration</th>
                            <th class="w-20 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($steps as $index => $step)
                            <tr>
                                <td class="">{{ $step->name }}</td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                                        {{ $step->type_display }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="kt-badge kt-badge-light-secondary">
                                        {{ $step->formatted_duration }}
                                    </span>
                                </td>
                                <td class="text-center" wire:ignore>
                                    <div data-kt-dropdown="true" data-kt-dropdown-trigger="click">
                                        <button class="kt-btn kt-btn-outline" data-kt-dropdown-toggle="true">
                                            <i class="ki-filled ki-dots-horizontal text-secondary-foreground"></i>
                                        </button>
                                        <div class="kt-dropdown-menu" data-kt-dropdown-menu="true">
                                            <ul class="kt-dropdown-menu-sub">
                                                @if($step->type === 'quiz')
                                                    <li>
                                                        <a class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" wire:click="manageQuestions({{ $step->id }})">
                                                            <i class="ki-filled ki-questionnaire-tablet"></i>
                                                            Manage Questions
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <a class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true" wire:click="openEditModal({{ $step->id }})">
                                                        <i class="ki-filled ki-pencil"></i>
                                                        Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="kt-dropdown-menu-link text-danger" data-kt-dropdown-dismiss="true" wire:click="confirmDelete('{{ $step->id }}', 'deleteStep')">
                                                        <i class="ki-filled ki-trash"></i>
                                                        Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No steps found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="kt-card-footer flex-col justify-center gap-5 text-sm font-medium text-secondary-foreground md:flex-row md:justify-between">
            <div class="order-2 flex items-center gap-2 md:order-1"></div>
            <div class="order-1 flex items-center gap-4 md:order-2">
                <span>
                    Showing {{ $steps->firstItem() ?? 0 }} to {{ $steps->lastItem() ?? 0 }} of {{ $steps->total() ?? 0 }} Steps
                </span>
            </div>
        </div>
    </div>

    {{-- Pagination outside the table card --}}
    <x-ktui-pagination :paginator="$steps" />

    {{-- Step Questions Modal --}}
    <livewire:homepage.programs.steps.step-questions-modal />
</div>
