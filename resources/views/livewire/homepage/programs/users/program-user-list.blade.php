<div class="kt-container-fixed kt-main-container" id="userListContainer">
    <div class="flex flex-wrap items-center gap-2 pb-4">
        <a href="{{ route('programs') }}" class="kt-btn kt-btn-outline flex items-center justify-center">
            <i class="ki-filled ki-arrow-left"></i>
        </a>
        <h1 class="text-xl font-medium leading-none text-mono">
            Program Users
        </h1>
    </div>

    <div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
        <div class="kt-card-header flex justify-between items-center">
            <h3 class="kt-card-title">{{ $program->name }}</h3>
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
                            <th>
                                Name
                                @if ($sortBy === 'name')
                                    <i class="ki-filled {{ $sortDirection === 'asc' ? 'ki-arrow-up' : 'ki-arrow-down' }}"></i>
                                @endif
                            </th>
                            <th>
                                Email
                                @if ($sortBy === 'email')
                                    <i class="ki-filled {{ $sortDirection === 'asc' ? 'ki-arrow-up' : 'ki-arrow-down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="setSort('started_at')" class="text-center cursor-pointer">
                                Start Date
                                @if ($sortBy === 'started_at')
                                    <i class="ki-filled {{ $sortDirection === 'asc' ? 'ki-arrow-up' : 'ki-arrow-down' }}"></i>
                                @endif
                            </th>
                            <th class="text-center">Completion Date</th>
                            <th class="text-center">Status</th>
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
                                <td class="text-center">{{ $user->start_at_formatted ?? 'N/A' }}</td>
                                <td class="text-center">{{ $user->completed_at_formatted ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($user->status === 'completed')
                                        bg-green-50 text-green-600
                                        @else
                                        bg-blue-50 text-blue-600
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $user->status)) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="kt-btn kt-btn-outline flex items-center justify-center" wire:click="viewUserAnswers({{ $user->id }})">
                                        <i class="ki-filled ki-eye text-secondary-foreground"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No users have interacted with this program yet.</td>
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
