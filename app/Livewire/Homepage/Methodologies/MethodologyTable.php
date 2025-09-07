<?php

namespace App\Livewire\Homepage\Methodologies;

use App\Models\Methodology;
use App\Models\Tag;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class MethodologyTable extends Component
{
    use WithFileUploads, WithoutUrlPagination, WithTableReload;

    public string $search = '';

    public int $perPage = 10;

    public string $tagFilter = '';

    public string $tagSearch = '';

    public array $tagSuggestions = [];

    public bool $showTagSuggestions = false;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'deleteMethodology' => 'deleteMethodology',
        'changeMethodologyStatus' => 'changeMethodologyStatus',
    ];

    #[Computed]
    public function methodologies()
    {
        return $this->handleReloadState(function () {
            $query = Methodology::where('name', 'like', '%'.$this->search.'%')
                ->when($this->tagFilter, function ($q) {
                    $q->whereJsonContains('tags', (int) $this->tagFilter);
                })
                ->withCount(['pillars', 'modules', 'questions'])
                ->orderBy('created_at', 'desc');

            // Use custom pagination without URL caching
            $page = $this->getPage();

            return $query->paginate($this->perPage, ['*'], 'page', $page);
        });
    }

    public function updatedTagSearch()
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
            $this->resetPageAndReload();
        }
    }

    public function selectTagFilter($tagId, $tagTitle)
    {
        $this->tagFilter = (string) $tagId;
        $this->tagSearch = $tagTitle;
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
        $this->resetPageAndReload();
    }

    public function clearTagFilter()
    {
        $this->tagFilter = '';
        $this->tagSearch = '';
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
        $this->resetPageAndReload();
    }

    public function getTagTitles($tagIds, $limit = 3)
    {
        if (empty($tagIds) || ! is_array($tagIds)) {
            return [
                'tags' => [],
                'hasMore' => false,
                'totalCount' => 0,
                'remainingCount' => 0,
            ];
        }

        $allTags = Tag::whereIn('id', $tagIds)
            ->where('active', true)
            ->pluck('title')
            ->toArray();

        $totalCount = count($allTags);
        $displayedTags = array_slice($allTags, 0, $limit);
        $hasMore = $totalCount > $limit;

        return [
            'tags' => $displayedTags,
            'hasMore' => $hasMore,
            'totalCount' => $totalCount,
            'remainingCount' => max(0, $totalCount - $limit),
        ];
    }

    public function openAddModal(): void
    {
        $this->dispatch('add-methodology');
    }

    public function editMethodology($methodologyId)
    {
        $this->dispatch('edit-methodology', $methodologyId);
    }

    public function openDeleteMethodologyModal($request)
    {
        $methodology = Methodology::findOrFail($request['id']);

        $modal = [
            'title' => __('messages.delete_methodology_title'),
            'message' => __('messages.delete_methodology_message'),
            'note' => __('messages.delete_methodology_note'),
            'action' => __('messages.delete_action'),
            'callback' => 'deleteMethodology',
            'object' => $request,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function openMethodologyStatusModal($request)
    {
        $methodology = Methodology::findOrFail($request['id']);

        if ($request['active']) {
            $title = __('messages.activate_methodology_title');
            $message = __('messages.activate_methodology_message');
            $action = __('messages.activate_action');
            $note = null;
        } else {
            $title = __('messages.deactivate_methodology_title');
            $message = __('messages.deactivate_methodology_message');
            $action = __('messages.deactivate_action');
            $note = __('messages.deactivate_methodology_note');
        }

        $modal = [
            'title' => $title,
            'message' => $message,
            'note' => $note,
            'action' => $action,
            'callback' => 'changeMethodologyStatus',
            'object' => $request,
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function changeMethodologyStatus($request)
    {
        $methodology = Methodology::findOrFail($request['id']);

        // If activating, validate the methodology meets all criteria
        if ($request['active']) {
            $validationResult = $this->validateMethodologyForActivation($methodology);
            if (!$validationResult['valid']) {
                $this->dispatch('show-toast', type: 'error', message: $validationResult['message']);
                return;
            }
        }

        $methodology->active = $request['active'];
        $methodology->save();
        $this->reloadTable();
        $this->dispatch('show-toast', type: 'success', message: $request['active'] ? 'Methodology activated successfully!' : 'Methodology deactivated successfully!');
    }

    /**
     * Validate if a methodology meets all criteria for activation.
     */
    private function validateMethodologyForActivation(Methodology $methodology): array
    {
        $errors = [];

        // 1. Check if methodology has at least 1 general question
        $generalQuestionsCount = $methodology->questions()->count();
        if ($generalQuestionsCount === 0) {
            $errors[] = 'Methodology must have at least 1 general question';
        }

        // 2. Type-specific validations
        if ($methodology->type === 'simple') {
            // For simple: at least 1 module
            $directModulesCount = $methodology->modules()->count();
            if ($directModulesCount === 0) {
                $errors[] = 'Simple methodology must have at least 1 module';
            }

            // Check each direct module has at least 1 question
            $modulesWithoutQuestions = [];
            foreach ($methodology->modules as $module) {
                // Skip if module is null or false
                if (!$module || !is_object($module)) {
                    continue;
                }

                $moduleQuestionsCount = $module->questionsForMethodology($methodology->id)->count();
                if ($moduleQuestionsCount === 0) {
                    $modulesWithoutQuestions[] = $module->name;
                }
            }
            if (!empty($modulesWithoutQuestions)) {
                $errors[] = 'The following modules must have at least 1 question each: ' . implode(', ', $modulesWithoutQuestions);
            }

        } elseif (in_array($methodology->type, ['complex', '2-section'])) {
            // For complex & 2-section: at least 1 pillar AND at least 1 module (direct or within pillars)
            $pillarsCount = $methodology->pillars()->count();
            if ($pillarsCount === 0) {
                $errors[] = ucfirst($methodology->type) . ' methodology must have at least 1 pillar';
            }

            $directModulesCount = $methodology->modules()->count();
            $pillarModulesCount = DB::table('pillar_module')
                ->where('methodology_id', $methodology->id)
                ->count();
            $totalModulesCount = $directModulesCount + $pillarModulesCount;

            if ($totalModulesCount === 0) {
                $errors[] = ucfirst($methodology->type) . ' methodology must have at least 1 module';
            }

            // Check each pillar has at least 1 module using direct database query
            $pillarsWithoutModules = DB::table('methodology_pillar as mp')
                ->join('pillars as p', 'mp.pillar_id', '=', 'p.id')
                ->leftJoin('pillar_module as pm', function($join) use ($methodology) {
                    $join->on('mp.pillar_id', '=', 'pm.pillar_id')
                         ->where('pm.methodology_id', '=', $methodology->id);
                })
                ->where('mp.methodology_id', $methodology->id)
                ->whereNull('pm.id')
                ->pluck('p.name')
                ->toArray();

            if (!empty($pillarsWithoutModules)) {
                $errors[] = 'The following pillars must have at least 1 module each: ' . implode(', ', $pillarsWithoutModules);
            }

            // Check direct modules have at least 1 question using direct database query
            $directModulesWithoutQuestions = DB::table('methodology_module as mm')
                ->join('modules as m', 'mm.module_id', '=', 'm.id')
                ->leftJoin('module_question as mq', function($join) use ($methodology) {
                    $join->on('mm.module_id', '=', 'mq.module_id')
                         ->where('mq.methodology_id', '=', $methodology->id)
                         ->whereNull('mq.pillar_id'); // Direct module questions have null pillar_id
                })
                ->where('mm.methodology_id', $methodology->id)
                ->whereNull('mq.id')
                ->pluck('m.name')
                ->toArray();

            // Check pillar modules have at least 1 question using direct database query
            $pillarModulesWithoutQuestions = DB::table('pillar_module as pm')
                ->join('modules as m', 'pm.module_id', '=', 'm.id')
                ->join('pillars as p', 'pm.pillar_id', '=', 'p.id')
                ->leftJoin('module_question as mq', function($join) use ($methodology) {
                    $join->on('pm.module_id', '=', 'mq.module_id')
                         ->on('pm.pillar_id', '=', 'mq.pillar_id')
                         ->where('mq.methodology_id', '=', $methodology->id);
                })
                ->where('pm.methodology_id', $methodology->id)
                ->whereNull('mq.id')
                ->select(DB::raw("CONCAT(m.name, ' (in ', p.name, ')') as module_name"))
                ->pluck('module_name')
                ->toArray();

            $allModulesWithoutQuestions = array_merge($directModulesWithoutQuestions, $pillarModulesWithoutQuestions);
            if (!empty($allModulesWithoutQuestions)) {
                $errors[] = 'The following modules must have at least 1 question each: ' . implode(', ', $allModulesWithoutQuestions);
            }
        }

        if (empty($errors)) {
            return ['valid' => true, 'message' => ''];
        }

        return [
            'valid' => false,
            'message' => 'Cannot activate methodology. Please fix the following issues:' . '<br>' . '• ' . implode('<br>' . '• ', $errors)
        ];
    }

    public function deleteMethodology($request)
    {
        $methodology = Methodology::findOrFail($request['id']);

        // Delete all associations
        $methodology->pillars()->detach();
        $methodology->modules()->detach();
        $methodology->questions()->detach();

        $methodology->delete();
        $this->reloadTable();
        $this->dispatch('show-toast', type: 'success', message: 'Methodology deleted successfully!');
    }

    public function managePillars($methodologyId)
    {
        $this->dispatch('manage-pillars', $methodologyId);
    }

    public function manageModules($methodologyId)
    {
        $this->dispatch('manage-modules', $methodologyId);
    }

    public function manageSections($methodologyId)
    {
        $this->dispatch('manage-sections', $methodologyId);
    }

    public function manageGeneralQuestions($methodologyId)
    {
        return redirect()->route('methodology.questions', ['methodologyId' => $methodologyId]);
    }

    public function manageModuleQuestions($methodologyId)
    {
        $this->dispatch('manage-module-questions', $methodologyId);
    }

    public function viewUsers($methodologyId)
    {
        return redirect()->route('methodology.users', ['methodology' => $methodologyId]);
    }

    public function render()
    {
        return view('livewire.homepage.methodologies.methodology-table', [
            'methodologies' => $this->methodologies,
        ]);
    }
}
