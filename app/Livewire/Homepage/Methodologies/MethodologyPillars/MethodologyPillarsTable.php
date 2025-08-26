<?php

namespace App\Livewire\Homepage\Methodologies\MethodologyPillars;

use App\Models\Methodology;
use App\Models\Pillar;
use App\Models\Tag;
use App\Traits\WithoutUrlPagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MethodologyPillarsTable extends Component
{
    use \App\Traits\HasTagTitles, WithoutUrlPagination;

    public int $methodologyId;

    public string $search = '';

    public int $perPage = 10;

    public string $tagFilter = '';

    public string $tagSearch = '';

    public array $tagSuggestions = [];

    public bool $showTagSuggestions = false;

    protected $listeners = [
        'refreshTable' => '$refresh',
        'confirm-delete-methodology-pillar' => 'deleteMethodologyPillar',
    ];

    #[Computed]
    public function pillars(): LengthAwarePaginator
    {
        $query = Pillar::query()
            ->where('pillars.name', 'like', '%'.$this->search.'%')
            ->whereHas('methodologies', function ($q) {
                $q->where('methodology_id', $this->methodologyId);
            })
            ->when($this->tagFilter, function ($q) {
                $q->whereJsonContains('pillars.tags', (int) $this->tagFilter);
            })
            ->join('methodology_pillar as mp', function ($join) {
                $join->on('mp.pillar_id', '=', 'pillars.id')
                    ->where('mp.methodology_id', '=', $this->methodologyId);
            })
            ->select('pillars.*')
            ->selectRaw('mp.weight as mp_weight, mp.sequence as mp_sequence')
            ->selectRaw('(
				SELECT COUNT(DISTINCT pm.module_id)
				FROM pillar_module pm
				WHERE pm.pillar_id = pillars.id AND pm.methodology_id = ?
			) as modules_count', [$this->methodologyId])
            ->orderBy('mp.sequence', 'asc')
            ->orderBy('mp.created_at', 'asc');

        $page = $this->getPage();

        return $query->paginate($this->perPage, ['*'], 'page', $page);
    }

    /**
     * Get names of pillars that the given pillar depends on within the current methodology.
     *
     * @param  int  $$pillarId
     * @return array<int, string>
     */
    public function getDependencyNames(int $moduleId): array
    {
        return \DB::table('pillar_dependencies as pd')
            ->join('pillars as p', 'p.id', '=', 'pd.depends_on_pillar_id')
            ->where('pd.methodology_id', $this->methodologyId)
            ->where('pd.pillar_id', $moduleId)
            ->orderBy('pd.id')
            ->pluck('p.name')
            ->toArray();
    }

    public function updatedTagSearch(): void
    {
        if (strlen($this->tagSearch) >= 1) {
            $this->tagSuggestions = Tag::where('title', 'like', '%'.$this->tagSearch.'%')
                ->where('active', true)
                ->limit(7)
                ->get(['id', 'title'])
                ->toArray();
            $this->showTagSuggestions = true;
        } else {
            $this->tagFilter = '';
            $this->showTagSuggestions = false;
        }
    }

    public function selectTagFilter($tagId, $tagTitle): void
    {
        $this->tagFilter = (string) $tagId;
        $this->tagSearch = $tagTitle;
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
    }

    public function clearTagFilter(): void
    {
        $this->tagFilter = '';
        $this->showTagSuggestions = false;
        $this->tagSearch = '';
    }

    public function openAddModal(): void
    {
        $this->dispatch('open-add-methodology-pillar', methodologyId: $this->methodologyId);
        $this->dispatch('show-modal', selector: '#methodology_pillar_add_modal');
    }

    public function openEditModal(int $pillarId): void
    {
        $this->dispatch('open-edit-methodology-pillar', methodologyId: $this->methodologyId, pillarId: $pillarId);
        $this->dispatch('show-modal', selector: '#methodology_pillar_add_modal');
    }

    public function openDeleteModal(int $pillarId): void
    {
        $hasDependents = \DB::table('pillar_dependencies')
            ->where('methodology_id', $this->methodologyId)
            ->where('depends_on_pillar_id', $pillarId)
            ->exists();

        if ($hasDependents) {
            $this->dispatch('show-toast', type: 'error', message: "Can't remove this pillar; remove dependent pillars first.");

            return;
        }

        $modal = [
            'title' => 'Remove Pillar',
            'message' => 'Removing this pillar will remove all associated modules. Are you sure?',
            'note' => null,
            'action' => 'Remove',
            'callback' => 'confirm-delete-methodology-pillar',
            'object' => ['methodologyId' => $this->methodologyId, 'pillarId' => $pillarId],
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function deleteMethodologyPillar($request): void
    {
        $methodologyId = (int) ($request['methodologyId'] ?? 0);
        $pillarId = (int) ($request['pillarId'] ?? 0);

        if (! $methodologyId || ! $pillarId) {
            return;
        }

        $hasDependents = \DB::table('pillar_dependencies')
            ->where('methodology_id', $methodologyId)
            ->where('depends_on_pillar_id', $pillarId)
            ->exists();

        if ($hasDependents) {
            $this->dispatch('show-toast', type: 'error', message: "Can't remove this pillar; remove dependent pillars first.");

            return;
        }

        \DB::transaction(function () use ($methodologyId, $pillarId) {
            \DB::table('pillar_dependencies')
                ->where('methodology_id', $methodologyId)
                ->where(function ($q) use ($pillarId) {
                    $q->where('pillar_id', $pillarId)->orWhere('depends_on_pillar_id', $pillarId);
                })
                ->delete();

            \DB::table('module_question')
                ->where('methodology_id', $methodologyId)
                ->where('pillar_id', $pillarId)
                ->delete();

            \DB::table('pillar_question')
                ->where('methodology_id', $methodologyId)
                ->where('pillar_id', $pillarId)
                ->delete();

            \DB::table('pillar_module')
                ->where('methodology_id', $methodologyId)
                ->where('pillar_id', $pillarId)
                ->delete();

            \DB::table('methodology_pillar')
                ->where('methodology_id', $methodologyId)
                ->where('pillar_id', $pillarId)
                ->delete();
        });

        $this->dispatch('refreshTable');
        $this->dispatch('show-toast', type: 'success', message: 'Removed successfully');
    }

    public function render()
    {
        $methodology = Methodology::find($this->methodologyId);

        return view('livewire.homepage.methodologies.methodologyPillars.methodology-pillars-table', [
            'pillars' => $this->pillars,
            'methodology' => $methodology,
        ]);
    }
}
