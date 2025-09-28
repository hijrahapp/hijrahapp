<div class="kt-container-fixed kt-main-container" id="feedbackUserListContainer">
    <div class="flex flex-wrap items-center gap-2 pb-4">
        <button wire:click="backToFeedback" class="kt-btn kt-btn-outline flex items-center justify-center">
            <i class="ki-filled ki-arrow-left"></i>
        </button>
        <h1 class="text-xl font-medium leading-none text-mono">
            Program Feedback Users
        </h1>
    </div>

    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        <div class="kt-card-header flex justify-between items-center">
            <h3 class="kt-card-title">{{ $program->name }} - Feedback Submissions</h3>
            <div class="flex items-center gap-2 w-52">
                <div class="kt-input">
                    <i class="ki-filled ki-magnifier"></i>
                    <input type="text" class="kt-input" placeholder="Search by name or email" wire:model.live="search">
                </div>
            </div>
        </div>
        <div class="kt-card-table">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border table-fixed w-full">
                    <thead>
                        <tr>
                            <th class="w-20 text-center">Profile</th>
                            <th wire:click="setSort('name')" class="cursor-pointer">
                                Name
                                @if ($sortBy === 'name')
                                    <i class="ki-filled {{ $sortDirection === 'asc' ? 'ki-arrow-up' : 'ki-arrow-down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="setSort('email')" class="cursor-pointer">
                                Email
                                @if ($sortBy === 'email')
                                    <i class="ki-filled {{ $sortDirection === 'asc' ? 'ki-arrow-up' : 'ki-arrow-down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="setSort('submitted_at')" class="text-center cursor-pointer">
                                Submission Date
                                @if ($sortBy === 'submitted_at')
                                    <i class="ki-filled {{ $sortDirection === 'asc' ? 'ki-arrow-up' : 'ki-arrow-down' }}"></i>
                                @endif
                            </th>
                            <th class="text-center">Rating</th>
                            <th class="w-20 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="text-center flex justify-center">
                                    <img src="{{ $user->profile_picture ?? '/assets/media/avatars/blank.png' }}" class="size-9 rounded-full border-2 border-gray-300" />
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td class="text-center">{{ $user->submitted_at_formatted ?? 'N/A' }}</td>
                                <td class="text-center">
                                    @if($user->feedback_rating)
                                        <div class="flex items-center justify-center gap-1">
                                            <span class="font-medium text-sm">{{ $user->feedback_rating }}</span>
                                            <div class="flex">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="ki-filled ki-star text-xs 
                                                        @if($i <= $user->feedback_rating) text-yellow-500
                                                        @else text-gray-300
                                                        @endif">
                                                    </i>
                                                @endfor
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">No rating</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="kt-btn kt-btn-outline flex items-center justify-center" wire:click="viewUserFeedback({{ $user->id }})" title="View Feedback Details">
                                        <i class="ki-filled ki-eye text-secondary-foreground"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8">
                                    <div class="flex flex-col items-center gap-3">
                                        <i class="ki-filled ki-message-question text-4xl text-gray-400"></i>
                                        <div>
                                            <p class="text-lg font-medium text-gray-600">No feedback submissions found</p>
                                            <p class="text-sm text-gray-500">This program has not received any feedback yet.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="kt-card-footer flex-col justify-center gap-5 text-sm font-medium text-secondary-foreground md:flex-row md:justify-between">
            <div class="order-2 flex items-center gap-2 md:order-1">
            </div>
            <div class="order-1 flex items-center gap-4 md:order-2">
                <span>
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() ?? 0 }} users
                </span>
            </div>
        </div>
    </div>

    {{-- Pagination outside the table card --}}
    <x-ktui-pagination :paginator="$users" />
</div>
