<?php

namespace App\Livewire\Homepage\Liabilities\Users;

use App\Models\Liability;
use App\Models\User;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableSorting;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class LiabilityUserList extends Component
{
    use WithoutUrlPagination, WithTableSorting;

    public Liability $liability;

    public string $search = '';

    public function mount(Liability $liability)
    {
        $this->liability = $liability;
    }

    #[Computed]
    public function users()
    {
        $liabilityId = $this->liability->id;

        // Users who have any progress in this liability
        $query = User::query()
            ->whereHas('liabilityProgress', function (Builder $q) use ($liabilityId) {
                $q->where('liability_id', $liabilityId);
            })
            ->when($this->search, function (Builder $q) {
                $q->where(function (Builder $s) {
                    $s->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->with(['liabilityProgress' => function ($q) use ($liabilityId) {
                $q->where('liability_id', $liabilityId);
            }]);

        $query = $this->applySorting($query);

        $paginated = $query->paginate(10);

        // Append formatted data for liability progress
        $paginated->getCollection()->transform(function ($user) {
            $userProgress = $user->liabilityProgress->first();
            $user->started_at_formatted = optional($userProgress->created_at)?->format('Y-m-d, h:ia');
            $user->completed_at_formatted = optional($userProgress->updated_at)?->format('Y-m-d, h:ia');
            $user->status = $userProgress->is_completed ? 'completed' : 'in_progress';
            $user->completed_todos_count = $userProgress->getCompletedTodosCount();
            $user->total_todos_count = count($this->liability->todos ?? []);
            $user->completion_percentage = $user->total_todos_count > 0
                ? round(($user->completed_todos_count / $user->total_todos_count) * 100, 1)
                : 0;

            return $user;
        });

        return $paginated;
    }

    public function viewUserDetails(User $user)
    {
        return redirect()->route('liability.user.details', [
            'liability' => $this->liability,
            'user' => $user,
        ]);
    }

    public function render()
    {
        return view('livewire.homepage.liabilities.users.liability-user-list', [
            'users' => $this->users,
        ]);
    }
}
