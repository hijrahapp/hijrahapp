<?php

namespace App\Livewire\Homepage\FeedbackForms\Users;

use App\Models\Program;
use App\Models\User;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableSorting;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class ProgramFeedbackUsers extends Component
{
    use WithoutUrlPagination, WithTableSorting;

    public Program $program;

    public string $search = '';

    public function mount(Program $program)
    {
        // Check if user has a valid session token
        if (! session('jwt_token')) {
            return redirect()->route('login');
        }

        $this->program = $program;

        // Set default sorting by submission date descending
        $this->sortBy = 'submitted_at';
        $this->sortDirection = 'desc';
    }

    #[Computed]
    public function users()
    {
        $programId = $this->program->id;

        // Users who have submitted feedback for this program
        $query = User::query()
            ->whereHas('programFeedback', function (Builder $q) use ($programId) {
                $q->where('program_id', $programId);
            })
            ->when($this->search, function (Builder $q) {
                $q->where(function (Builder $s) {
                    $s->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->with(['programFeedback' => function ($q) use ($programId) {
                $q->where('program_id', $programId);
            }]);

        // Handle sorting - special case for submitted_at since it's from the related table
        if ($this->sortBy === 'submitted_at') {
            $query->leftJoin('program_feedback', function ($join) use ($programId) {
                $join->on('users.id', '=', 'program_feedback.user_id')
                    ->where('program_feedback.program_id', '=', $programId);
            })
                ->orderBy('program_feedback.submitted_at', $this->sortDirection)
                ->select('users.*'); // Select only user fields to avoid conflicts
        } else {
            $query = $this->applySorting($query);
        }

        $paginated = $query->paginate(10);

        // Append feedback data for each user
        $paginated->getCollection()->transform(function ($user) {
            $feedback = $user->programFeedback->first();
            $user->submitted_at_formatted = optional($feedback->submitted_at)?->format('Y-m-d, h:ia');

            // Calculate rating from feedback responses
            $rating = null;
            if ($feedback && $feedback->responses) {
                foreach ($feedback->responses as $key => $value) {
                    if (is_numeric($value) && $value >= 1 && $value <= 10) {
                        $rating = (float) $value;
                        break;
                    }
                }
            }
            $user->feedback_rating = $rating;
            $user->feedback_id = $feedback->id ?? null;

            return $user;
        });

        return $paginated;
    }

    public function viewUserFeedback(User $user)
    {
        // Navigate to detailed feedback view
        return redirect()->route('program.feedback.user.details', [
            'program' => $this->program,
            'user' => $user,
        ]);
    }

    public function backToFeedback()
    {
        return redirect()->route('feedback');
    }

    public function render()
    {
        return view('livewire.homepage.feedback-forms.users.program-feedback-users', [
            'users' => $this->users,
        ]);
    }
}
