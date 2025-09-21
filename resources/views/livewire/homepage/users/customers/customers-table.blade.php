<x-table-layout
    title="Customers"
    searchPlaceholder="Search Users"
    :paginator="$users"
    :showAddButton="false"
    emptyMessage="No users found"
    counterText="users"
>
    <x-slot name="tableHeader">
        <tr>
            <th class="w-20 text-center">#</th>
            <th class="w-20 text-center">Profile</th>
            <th class="">Name</th>
            <th class="w-80">Email</th>
            <th class="text-center">Date of Birth</th>
            <th class="text-center">Gender</th>
            <th class="text-center">Activate/Deactivate</th>
        </tr>
    </x-slot>

    <x-slot name="tableBody">
        @forelse($users as $index => $user)
            <tr>
                <td class="text-center">{{ $users->firstItem() + $index }}</td>
                <td class="text-center flex justify-center">
                    <img src="{{ $user->profile_picture ?? '/assets/media/avatars/blank.png' }}" class="size-9 rounded-full border-2 border-gray-300" />
                </td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td class="text-center">{{ $user->birthDate ? $user->birthDate->format('d M, Y') : '-' }}</td>
                <td class="text-center">{{ ucfirst($user->gender) }}</td>
                <td class="text-center justify-center">
                    @if($user->active)
                        <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-destructive"
                                x-on:click="$wire.call('handleUserStatusOpen', {{ Js::from(['userId' => $user->id,'status' => false]) }})"
                                title="Deactivate User"
                                @if(!$this->isUserEditable($user)) disabled @endif>
                            Deactivate
                        </button>
                    @else
                        <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-primary"
                                x-on:click="$wire.call('handleUserStatusOpen', {{ Js::from(['userId' => $user->id,'status' => true]) }})"
                                title="Activate User"
                                @if(!$this->isUserEditable($user)) disabled @endif>
                            Activate
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-4">No users found.</td>
            </tr>
        @endforelse
    </x-slot>
</x-table-layout>
