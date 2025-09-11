<div>
    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        <div class="kt-card-header flex-col gap-3 md:flex-row md:justify-between md:items-center">
            <h3 class="kt-card-title">Related Modules</h3>
            <div class="flex flex-col gap-2 md:flex-row md:gap-2 md:items-center">
                <!-- Search Input -->
                <div class="kt-input max-w-48">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="Search Modules" wire:model.live="search" />
                </div>
                
                <!-- Methodology Filter -->
                @if($methodologies && count($methodologies) > 0)
                    <select class="kt-select max-w-48" wire:model.live="selectedMethodologyId">
                        <option value="">All Methodologies</option>
                        @foreach($methodologies as $methodology)
                            <option value="{{ $methodology->id }}">{{ $methodology->name }}</option>
                        @endforeach
                    </select>
                @else
                    <div class="max-w-48 p-2 bg-gray-50 border border-gray-200 rounded-md">
                        <span class="text-gray-500 text-xs">No methodologies available</span>
                    </div>
                @endif
                
                <!-- Pillar Filter (shown only when methodology is selected) -->
                @if($selectedMethodologyId && $pillars && count($pillars) > 0)
                    <select class="kt-select max-w-48" wire:model.live="selectedPillarId">
                        <option value="">All Pillars</option>
                        @foreach($pillars as $pillar)
                            <option value="{{ $pillar->id }}">{{ $pillar->name }}</option>
                        @endforeach
                    </select>
                @endif
                
                <!-- Add Module Button -->
                <button class="kt-btn kt-btn-outline flex items-center justify-center" title="Add Module" wire:click="openAddModal">
                    <i class="ki-filled ki-plus"></i>
                </button>
            </div>
        </div>

        <div class="kt-card-table">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border table-fixed w-full">
                    <thead>
                        <tr>
                            <th class="">Module Name</th>
                            <th class="text-center">Methodology</th>
                            <th class="text-center">Pillar</th>
                            <th class="text-center">Score Range</th>
                            <th class="w-20 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $module)
                            <tr>
                                <td class="">
                                        <span class="font-medium">{{ $module->name }}</span>
                                </td>
                                <td class="text-center">
                                    @if($module->methodology)
                                        <span class="kt-badge kt-badge-light-primary">
                                            {{ $module->methodology->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($module->pillar)
                                        <span class="kt-badge kt-badge-light-secondary">
                                            {{ $module->pillar->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Direct</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($module->pivot->min_score || $module->pivot->max_score)
                                        <span class="kt-badge kt-badge-light-info">
                                            {{ $module->pivot->min_score ?? 0 }}% - {{ $module->pivot->max_score ?? 100 }}%
                                        </span>
                                    @else
                                        <span class="text-gray-400">Not set</span>
                                    @endif
                                </td>
                                <td class="text-center" wire:ignore>
                                    <div data-kt-dropdown="true" data-kt-dropdown-trigger="click">
                                        <button class="kt-btn kt-btn-outline" data-kt-dropdown-toggle="true">
                                            <i class="ki-filled ki-dots-horizontal text-secondary-foreground"></i>
                                        </button>
                                        <div class="kt-dropdown-menu" data-kt-dropdown-menu="true">
                                            <ul class="kt-dropdown-menu-sub">
                                                <li>
                                                    <a class="kt-dropdown-menu-link text-danger" data-kt-dropdown-dismiss="true" 
                                                       wire:click="removeModule({{ $module->id }})" 
                                                       wire:confirm="Are you sure you want to remove this module from the program?">
                                                        <i class="ki-filled ki-trash"></i>
                                                        Remove
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8">
                                    <div class="flex flex-col items-center">
                                        <i class="ki-filled ki-element-4 text-4xl text-gray-400 mb-2"></i>
                                        <p class="text-gray-500">No modules assigned to this program</p>
                                        <button class="kt-btn kt-btn-sm kt-btn-outline mt-3" wire:click="openAddModal">
                                            <i class="ki-filled ki-plus"></i>
                                            Add Module
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($modules->hasPages())
            <div class="kt-card-footer flex-col justify-center gap-5 text-sm font-medium text-secondary-foreground md:flex-row md:justify-between">
                <div class="order-2 flex items-center gap-2 md:order-1"></div>
                <div class="order-1 flex items-center gap-4 md:order-2">
                    <span>
                        Showing {{ $modules->firstItem() ?? 0 }} to {{ $modules->lastItem() ?? 0 }} of {{ $modules->total() ?? 0 }} Modules
                    </span>
                </div>
            </div>
        @endif
    </div>

    <x-ktui-pagination :paginator="$modules" />

    {{-- Module Add Modal --}}
    <livewire:homepage.programs.modules.program-module-add-modal />

</div>
