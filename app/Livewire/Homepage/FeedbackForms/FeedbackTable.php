<?php

namespace App\Livewire\Homepage\FeedbackForms;

use App\Models\Program;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Livewire\Attributes\Computed;
use Livewire\Component;

class FeedbackTable extends Component
{
    use WithoutUrlPagination, WithTableReload;

    public string $search = '';

    public int $perPage = 10;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
    ];

    #[Computed]
    public function programs()
    {
        return $this->handleReloadState(function () {
            // Get programs with feedback count
            $programs = Program::where('name', 'like', '%'.$this->search.'%')
                ->withCount('feedback')
                ->with('feedback')
                ->get();

            // Calculate average rating for each program and sort
            $programs = $programs->map(function ($program) {
                $program->calculated_avg_rating = $program->getDynamicAverageRating() ?? 0;

                return $program;
            })->sortByDesc('calculated_avg_rating');

            // Convert to paginated collection
            $page = $this->getPage();
            $total = $programs->count();
            $offset = ($page - 1) * $this->perPage;

            $items = $programs->skip($offset)->take($this->perPage)->values();

            return new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $this->perPage,
                $page,
                ['path' => request()->url()]
            );
        });
    }

    public function viewFeedback($programId)
    {
        $program = Program::findOrFail($programId);

        return redirect()->route('program.feedback.users', ['program' => $program]);
    }

    public function render()
    {
        return view('livewire.homepage.feedback-forms.feedback-table', [
            'programs' => $this->programs,
        ]);
    }
}
