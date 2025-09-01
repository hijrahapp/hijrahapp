<?php

namespace App\Livewire\Homepage\Methodologies\Users;

use App\Models\Methodology;
use App\Models\User;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableSorting;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class MethodologyUserList extends Component
{
    use WithoutUrlPagination, WithTableSorting;

    public Methodology $methodology;

    public string $search = '';

    public function mount(Methodology $methodology)
    {
        $this->methodology = $methodology;
    }

    #[Computed]
    public function users()
    {
        $methodologyId = $this->methodology->id;

        // Users who have any answers in this methodology (any context)
        $query = User::query()
            ->where(function (Builder $q) use ($methodologyId) {
                $q->whereHas('userAnswers', function (Builder $qa) use ($methodologyId) {
                    $qa->where('context_type', 'methodology')
                        ->where('context_id', $methodologyId);
                })
                    ->orWhereHas('userAnswers', function (Builder $qa) use ($methodologyId) {
                        $qa->where('context_type', 'pillar')
                            ->whereIn('question_id', function ($sub) use ($methodologyId) {
                                $sub->select('question_id')
                                    ->from('pillar_question')
                                    ->where('methodology_id', $methodologyId);
                            });
                    })
                    ->orWhereHas('userAnswers', function (Builder $qa) use ($methodologyId) {
                        $qa->where('context_type', 'module')
                            ->whereIn('question_id', function ($sub) use ($methodologyId) {
                                $sub->select('question_id')
                                    ->from('module_question')
                                    ->where('methodology_id', $methodologyId);
                            });
                    });
            })
            ->when($this->search, function (Builder $q) {
                $q->where(function (Builder $s) {
                    $s->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->with(['userAnswers' => function ($qa) use ($methodologyId) {
                $qa->where(function (Builder $q) use ($methodologyId) {
                    $q->where(function (Builder $qm) use ($methodologyId) {
                        $qm->where('context_type', 'methodology')->where('context_id', $methodologyId);
                    })
                        ->orWhere(function (Builder $qp) use ($methodologyId) {
                            $qp->where('context_type', 'pillar')->whereIn('question_id', function ($sub) use ($methodologyId) {
                                $sub->select('question_id')->from('pillar_question')->where('methodology_id', $methodologyId);
                            });
                        })
                        ->orWhere(function (Builder $qmo) use ($methodologyId) {
                            $qmo->where('context_type', 'module')->whereIn('question_id', function ($sub) use ($methodologyId) {
                                $sub->select('question_id')->from('module_question')->where('methodology_id', $methodologyId);
                            });
                        });
                });
            }]);

        $query = $this->applySorting($query);

        $paginated = $query->paginate(10);

        // Append formatted timestamps for start and completion
        $paginated->getCollection()->transform(function ($user) {
            $answers = $user->userAnswers;
            $user->start_at_formatted = optional($answers->min('created_at'))?->format('Y-m-d, h:ia');
            $user->completed_at_formatted = optional($answers->max('created_at'))?->format('Y-m-d, h:ia');

            return $user;
        });

        return $paginated;
    }

    public function viewUserAnswers(User $user)
    {
        return redirect()->route('methodology.user.answers', [
            'methodology' => $this->methodology,
            'user' => $user,
        ]);
    }

    public function render()
    {
        return view('livewire.homepage.methodologies.users.methodology-user-list', [
            'users' => $this->users,
        ]);
    }
}
