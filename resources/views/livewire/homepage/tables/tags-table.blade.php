<div class="kt-card kt-card-grid kt-card-div h-full min-w-full">
    <div class="kt-card-header flex justify-between items-center">
        <h3 class="kt-card-title">Tags</h3>
        <div class="flex gap-2 items-center">
            <div class="kt-input max-w-48">
                <i class="ki-filled ki-magnifier"></i>
                <input type="text" class="kt-input" placeholder="Search Tags" wire:input="setSearchProperty($event.target.value)" />
            </div>
            <button class="kt-btn kt-btn-outline flex items-center justify-center" data-kt-modal-toggle="#tag_add_modal" title="Add Tag">
                <i class="ki-filled ki-plus"></i>
            </button>
        </div>
    </div>
    <div class="kt-card-table">
        <div class="kt-scrollable-x-auto">
            <table class="kt-table kt-table-border table-fixed w-full">
                <thead>
                    <tr>
                        <th class="w-20 text-center">#</th>
                        <th class="">Title</th>
                        <th class="text-center">Activate/Deactivate</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tags as $index => $tag)
                        <tr>
                            <td class="text-center">{{ $tags->firstItem() + $index }}</td>
                            <td>{{ $tag->title }}</td>
                            <td class="text-center justify-center">
                                @if($tag->active)
                                    <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-destructive" x-on:click="$wire.call('openTagStatusModal', {{ Js::from(['id' => $tag->id, 'active' => false]) }})" title="Deactivate Tag">
                                        Deactivate
                                    </button>
                                @else
                                    <button class="kt-btn kt-btn-outline kt-btn-sm kt-btn-primary" x-on:click="$wire.call('openTagStatusModal', {{ Js::from(['id' => $tag->id, 'active' => true]) }})" title="Activate Tag">
                                        Activate
                                    </button>
                                @endif
                            </td>
                            <td class="text-center flex gap-2 justify-center">
                                <button
                                    class="kt-btn kt-btn-outline flex items-center justify-center"
                                    x-on:click="$wire.call('openTagDeleteModal', {{ Js::from([ 'id' => $tag->id ]) }})"
                                    title="Delete Tag">
                                    <i class="ki-filled ki-trash text-destructive"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan = "4"  class="text-center py-4">No Tags found.</td>
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
                Showing {{ $tags->firstItem() ?? 0 }} to {{ $tags->lastItem() ?? 0 }} of {{ $tags->total() ?? 0 }} Tags
            </span>
            <div>
                {{ $tags->links() }}
            </div>
        </div>
    </div>
</div>
