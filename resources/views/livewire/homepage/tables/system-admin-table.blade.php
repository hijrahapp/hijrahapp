<div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
    @php $role = session('user')["role"] ?? null; @endphp
    <div class="kt-card-header flex justify-between items-center">
        <h3 class="kt-card-title">System Users</h3>
        <div class="flex gap-2 items-center">
            <div class="kt-input max-w-48">
                <i class="ki-filled ki-magnifier"></i>
                <input type="text" class="kt-input" placeholder="Search Users" wire:input="setSearchProperty($event.target.value)" />
            </div>
            @if($role === 'SuperAdmin')
            <button class="kt-btn kt-btn-outline flex items-center justify-center" data-kt-modal-toggle="#user_add_modal" title="Add User">
                <i class="ki-filled ki-plus"></i>
            </button>
            @endif
        </div>
    </div>
    <div class="kt-card-table">
        <div class="kt-scrollable-x-auto">
            <table class="kt-table kt-table-border table-fixed w-full">
                <thead>
                    <tr>
                        <th class="w-20 text-center">#</th>
                        <th class="">Name</th>
                        <th class="w-80">Email</th>
{{--                        <th class="text-center">Role</th>--}}
                        @if($role === 'SuperAdmin')
                        <th class="text-center">Activate/Deactivate</th>
                        @else
                        <th class="text-center">Status</th>
                        @endif
                        @if($role === 'SuperAdmin')
                        <th class="text-center">Edit</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr>
                            <td class="text-center">{{ $users->firstItem() + $index }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
{{--                            <td class="text-center">--}}
{{--                                <span class="kt-badge kt-badge-sm {{--}}
{{--                                    match($user->role->name->value ?? '') {--}}
{{--                                        'SuperAdmin' => 'kt-badge-destructive',--}}
{{--                                        'Admin' => 'kt-badge-warning',--}}
{{--                                        'Expert' => 'kt-badge-success',--}}
{{--                                        'Customer' => 'kt-badge-secondary',--}}
{{--                                        default => 'kt-badge-secondary',--}}
{{--                                    }--}}
{{--                                }} kt-badge-outline">--}}
{{--                                    {{ $user->role->name->value ?? '-' }}--}}
{{--                                </span>--}}
                            <td class="text-center justify-center">
                                @if($role === 'SuperAdmin')
                                @if($user->active)
                                    <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-destructive" data-kt-modal-toggle="#user_status_modal" x-on:click="$wire.call('handleUserStatusOpen', {{ Js::from(['userId' => $user->id,'status' => false]) }})" title="Deactivate User" @if(!$this->isUserEditable($user)) disabled @endif>
                                        Deactivate
                                    </button>
                                @else
                                    <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-primary" data-kt-modal-toggle="#user_status_modal" x-on:click="$wire.call('handleUserStatusOpen', {{ Js::from(['userId' => $user->id,'status' => true]) }})" title="Activate User" @if(!$this->isUserEditable($user)) disabled @endif>
                                        Activate
                                    </button>
                                @endif
                                @else
                                    @if($user->active)
                                        <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">
                                            Active
                                        </span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">
                                            Suspended
                                        </span>
                                    @endif
                                @endif
                            </td>
                            @if($role === 'SuperAdmin')
                            <td class="text-center flex gap-2 justify-center">
                                <button
                                    class="kt-btn kt-btn-outline flex items-center justify-center"
                                    data-kt-modal-toggle="#user_edit_modal"
                                    x-on:click="$wire.call('handleUserEditOpen', {{ Js::from([
                                                                                    'id' => $user->id,
                                                                                    'name' => $user->name,
                                                                                    'email' => $user->email,
                                                                                    'gender' => $user->gender,
                                                                                    'birthDate' => optional($user->birthDate)->toDateString(),
                                                                                    'roleId' => $user->roleId,
                                                                                ]) }})"
                                    title="Edit User" @if(!$this->isUserEditable($user)) disabled @endif>
                                    <i class="ki-filled ki-pencil"></i>
                                </button>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td @if($role === 'SuperAdmin') colspan="5" @else colspan = "4" @endif  class="text-center py-4">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="kt-card-footer flex-col justify-center gap-5 text-sm font-medium text-secondary-foreground md:flex-row md:justify-between">
        <div class="order-2 flex items-center gap-2 md:order-1">
{{--            Show--}}
{{--            <select class="kt-select w-16" wire:model="perPage">--}}
{{--                <option value="5">5</option>--}}
{{--                <option value="10">10</option>--}}
{{--                <option value="25">25</option>--}}
{{--                <option value="50">50</option>--}}
{{--            </select>--}}
{{--            per page--}}
        </div>
        <div class="order-1 flex items-center gap-4 md:order-2">
            <span>
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() ?? 0 }} users
            </span>
            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
