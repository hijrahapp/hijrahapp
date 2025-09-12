<?php

namespace App\Livewire\Homepage\Programs\Users;

use App\Models\Program;
use App\Models\User;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableSorting;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class ProgramUserList extends Component
{
    use WithoutUrlPagination, WithTableSorting;

    public Program $program;

    public string $search = '';

    public function mount(Program $program)
    {
        $this->program = $program;
    }

    #[Computed]
    public function users()
    {
        $programId = $this->program->id;

        // Users who have any progress in this program
        $query = User::query()
            ->whereHas('userPrograms', function (Builder $q) use ($programId) {
                $q->where('program_id', $programId);
            })
            ->when($this->search, function (Builder $q) {
                $q->where(function (Builder $s) {
                    $s->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->with(['userPrograms' => function ($q) use ($programId) {
                $q->where('program_id', $programId);
            }]);

        $query = $this->applySorting($query);

        $paginated = $query->paginate(10);

        // Append formatted timestamps for start and completion
        $paginated->getCollection()->transform(function ($user) {
            $userProgram = $user->userPrograms->first();
            $user->start_at_formatted = optional($userProgram->started_at)?->format('Y-m-d, h:ia');
            $user->completed_at_formatted = optional($userProgram->completed_at)?->format('Y-m-d, h:ia');
            $user->status = $userProgram->status ?? 'in_progress';

            return $user;
        });

        return $paginated;
    }

    public function viewUserAnswers(User $user)
    {
        return redirect()->route('program.user.answers', [
            'program' => $this->program,
            'user' => $user,
        ]);
    }

    public function render()
    {
        return view('livewire.homepage.programs.users.program-user-list', [
            'users' => $this->users,
        ]);
    }
}
