<?php

namespace App\Livewire\Homepage\Methodologies\MethodologyPillars;

use App\Models\Methodology;
use App\Models\Pillar;
use Livewire\Component;

class MethodologyPillarAddModal extends Component
{
	public int $methodologyId;

	public bool $isEditMode = false;
	public ?int $editingPillarId = null;
	public bool $isActiveMethodology = false;
	public string $pillarName = '';

	public string $pillarSearch = '';
	public ?int $selectedPillarId = null;
	public array $pillarSuggestions = [];
	public bool $showPillarSuggestions = false;

	public string $numberOfModules = '';
	public string $weight = '';
	public string $sectionNumber = '1';
	public string $methodologyType = '';

	// General questions meta for the pillar
	public string $questionsDescription = '';
	public string $questionsEstimatedTime = '';

	public array $dependencyIds = [];
	public array $dependencySuggestions = [];
	public string $dependencySearch = '';
	public bool $showDependencySuggestions = false;

	protected $listeners = [
		'reset-modal' => 'resetForm',
		'open-add-methodology-pillar' => 'open',
		'open-edit-methodology-pillar' => 'openEdit',
	];

	public function open(int $methodologyId): void
	{
		$this->resetForm();
		$this->methodologyId = $methodologyId;
		$methodology = Methodology::findOrFail($methodologyId);
		$this->isActiveMethodology = (bool) $methodology->active;
		$this->methodologyType = $methodology->type;
	}

	protected function rules(): array
	{
		return [
			'selectedPillarId' => 'required|integer|exists:pillars,id',
			'numberOfModules' => 'required|integer|min:0',
			'weight' => 'nullable|numeric',
			'questionsDescription' => 'nullable|string',
			'questionsEstimatedTime' => 'nullable|integer|min:0',
			'dependencyIds' => 'array',
			'dependencyIds.*' => 'integer|exists:pillars,id',
			'sectionNumber' => 'nullable|in:1,2',
		];
	}

	public function updatedPillarSearch(): void
	{
		if (strlen($this->pillarSearch) < 1) {
			$this->pillarSuggestions = [];
			$this->showPillarSuggestions = false;
			return;
		}

		$excludedIds = \DB::table('methodology_pillar')
			->where('methodology_id', $this->methodologyId)
			->pluck('pillar_id')
			->toArray();

		$this->pillarSuggestions = Pillar::where('name', 'like', "%{$this->pillarSearch}%")
			->where('active', true)
			->whereNotIn('id', $excludedIds)
			->limit(7)
			->get(['id', 'name'])
			->toArray();
		$this->showPillarSuggestions = true;
	}

	public function selectPillar(int $pillarId, string $pillarName): void
	{
		$this->selectedPillarId = $pillarId;
		$this->pillarSearch = $pillarName;
		$this->pillarSuggestions = [];
		$this->showPillarSuggestions = false;
		$this->dependencyIds = array_values(array_diff($this->dependencyIds, [$pillarId]));

		// Clear questions meta; will be set explicitly for this methodology-pillar link
		$this->questionsDescription = '';
		$this->questionsEstimatedTime = '';
	}

	public function updatedDependencySearch(): void
	{
		if (strlen($this->dependencySearch) < 1) {
			$this->dependencySuggestions = [];
			$this->showDependencySuggestions = false;
			return;
		}

		$linkedPillarIds = \DB::table('methodology_pillar')
			->where('methodology_id', $this->methodologyId)
			->pluck('pillar_id')
			->toArray();

		$this->dependencySuggestions = Pillar::whereIn('id', $linkedPillarIds)
			->where('name', 'like', "%{$this->dependencySearch}%")
			->limit(7)
			->get(['id', 'name'])
			->toArray();
		$this->showDependencySuggestions = true;
	}

	public function toggleDependency(int $pillarId): void
	{
		if ($this->selectedPillarId && $pillarId === $this->selectedPillarId) {
			$this->dispatch('show-toast', type: 'error', message: 'A pillar cannot depend on itself.');
			return;
		}
		if (in_array($pillarId, $this->dependencyIds, true)) {
			$this->dependencyIds = array_values(array_diff($this->dependencyIds, [$pillarId]));
		} else {
			$this->dependencyIds[] = $pillarId;
		}

		$this->dependencySearch = '';
		$this->dependencySuggestions = [];
		$this->showDependencySuggestions = false;
	}

	public function save(): void
	{
		if ($this->isEditMode) {
			$this->validate([
				'weight' => 'nullable|numeric',
				'questionsDescription' => 'nullable|string',
				'questionsEstimatedTime' => 'nullable|integer|min:0',
			]);
		} else {
			$rules = $this->rules();
			if ($this->methodologyType === 'twoSection' && $this->sectionNumber === '2') {
				$rules['dependencyIds'] = 'required|array|min:1';
				$rules['dependencyIds.*'] = 'integer|exists:pillars,id';
			}
			$this->validate($rules);
		}

		if ($this->selectedPillarId && in_array($this->selectedPillarId, $this->dependencyIds, true)) {
			$this->dispatch('show-toast', type: 'error', message: 'A pillar cannot depend on itself.');
			return;
		}

		if ($this->isEditMode && $this->editingPillarId) {
//			if ($this->isActiveMethodology) {
//				$this->dispatch('show-toast', type: 'error', message: 'This pillar exists in an active methodology');
//				return;
//			}

			// If pillar changed, re-attach and move dependencies accordingly
			$targetPillarId = $this->selectedPillarId ?: $this->editingPillarId;

			$existing = \DB::table('methodology_pillar')
				->where('methodology_id', $this->methodologyId)
				->where('pillar_id', $this->editingPillarId)
				->first();

			if ($existing) {
				$sequence = property_exists($existing, 'sequence') ? ($existing->sequence ?? null) : null;
				\DB::table('methodology_pillar')
					->where('methodology_id', $this->methodologyId)
					->where('pillar_id', $this->editingPillarId)
					->delete();

				\DB::table('methodology_pillar')->updateOrInsert(
					[
						'methodology_id' => $this->methodologyId,
						'pillar_id' => $targetPillarId,
					],
					[
						'number_of_modules' => $existing->number_of_modules ?? null,
						'weight' => $this->weight !== '' ? (float)$this->weight : ($existing->weight ?? null),
						'sequence' => $sequence,
						'section' => $this->methodologyType === 'twoSection'
							? ($this->sectionNumber === '2' ? 'second' : 'first')
							: ($existing->section ?? 'first'),
						'updated_at' => now(),
						'created_at' => $existing->created_at ?? now(),
					]
				);
			}

			$this->dispatch('show-toast', type: 'success', message: 'Pillar updated successfully');
		} else {
			$maxSequence = (int) \DB::table('methodology_pillar')
				->where('methodology_id', $this->methodologyId)
				->max('sequence');

			\DB::table('methodology_pillar')->updateOrInsert(
				[
					'methodology_id' => $this->methodologyId,
					'pillar_id' => $this->selectedPillarId,
				],
				[
					'number_of_modules' => (int) $this->numberOfModules,
					'weight' => $this->weight,
					'questions_description' => $this->questionsDescription !== '' ? $this->questionsDescription : null,
					'questions_estimated_time' => is_numeric($this->questionsEstimatedTime)
						? (int) $this->questionsEstimatedTime
						: null,
					'sequence' => $maxSequence + 1,
					'section' => $this->methodologyType === 'twoSection'
						? ($this->sectionNumber === '2' ? 'second' : 'first')
						: 'first',
					'updated_at' => now(),
					'created_at' => now(),
				]
			);


			if ($this->selectedPillarId) {
				\DB::table('pillar_dependencies')
					->where('methodology_id', $this->methodologyId)
					->where('pillar_id', $this->selectedPillarId)
					->delete();

				$deps = array_unique(array_filter($this->dependencyIds, fn($id) => $id !== $this->selectedPillarId));

				foreach ($deps as $dependsOnId) {
					\DB::table('pillar_dependencies')->insert([
						'methodology_id' => $this->methodologyId,
						'pillar_id' => $this->selectedPillarId,
						'depends_on_pillar_id' => $dependsOnId,
						'created_at' => now(),
						'updated_at' => now(),
					]);
				}
			}

			$this->dispatch('show-toast', type: 'success', message: 'Pillar added successfully');
		}

		$this->dispatch('refreshTable');
		$this->dispatch('click');
		$this->resetForm();
	}

	public function resetForm(): void
	{
		$this->resetErrorBag();
		$this->isEditMode = false;
		$this->editingPillarId = null;
		$this->pillarName = '';
		$this->pillarSearch = '';
		$this->selectedPillarId = null;
		$this->pillarSuggestions = [];
		$this->showPillarSuggestions = false;
		$this->numberOfModules = '';
		$this->weight = '';
		$this->questionsDescription = '';
		$this->questionsEstimatedTime = '';
		$this->dependencyIds = [];
		$this->dependencySuggestions = [];
		$this->dependencySearch = '';
		$this->showDependencySuggestions = false;
		$this->sectionNumber = '1';
	}

	public function closeModal(): void
	{
		$this->resetForm();
		$this->dispatch('click');
	}

	public function render()
	{
		$selectedDependencyNames = [];
		if (count($this->dependencyIds) > 0) {
			$selectedDependencyNames = Pillar::whereIn('id', $this->dependencyIds)
				->pluck('name', 'id')
				->toArray();
		}

		return view('livewire.homepage.methodologies.methodologyPillars.methodology-pillar-add-modal', [
			'selectedDependencyNames' => $selectedDependencyNames,
			'isEditMode' => $this->isEditMode,
		]);
	}

	public function openEdit(int $methodologyId, int $pillarId): void
	{
		$this->resetForm();
		$this->isEditMode = true;
		$this->methodologyId = $methodologyId;
		$this->editingPillarId = $pillarId;
		$methodology = Methodology::findOrFail($methodologyId);
		$this->isActiveMethodology = (bool) $methodology->active;
		$this->methodologyType = $methodology->type;

		$pillar = Pillar::findOrFail($pillarId);
		$this->pillarName = $pillar->name;
		$this->selectedPillarId = $pillarId;
		$this->pillarSearch = $pillar->name;

		// Prefill questions meta
		$this->questionsDescription = $pillar->questions_description ?? '';
		$this->questionsEstimatedTime = is_numeric($pillar->questions_estimated_time ?? null)
			? (string) ((int) $pillar->questions_estimated_time)
			: '';

		$pivot = \DB::table('methodology_pillar')
			->where('methodology_id', $methodologyId)
			->where('pillar_id', $pillarId)
			->first();

		$this->numberOfModules = $pivot && $pivot->number_of_modules !== null ? (string)$pivot->number_of_modules : '';
		$this->weight = $pivot && $pivot->weight !== null ? (int)$pivot->weight : 0;
		if ($pivot && property_exists($pivot, 'section')) {
			$this->sectionNumber = $pivot->section === 'second' ? '2' : '1';
		}

		// Prefill questions meta from pivot
		if ($pivot) {
			$this->questionsDescription = property_exists($pivot, 'questions_description') && $pivot->questions_description !== null
				? (string) $pivot->questions_description
				: '';
			$this->questionsEstimatedTime = property_exists($pivot, 'questions_estimated_time') && is_numeric($pivot->questions_estimated_time)
				? (string) ((int) $pivot->questions_estimated_time)
				: '';
		}

		$this->dependencyIds = \DB::table('pillar_dependencies')
			->where('methodology_id', $this->methodologyId)
			->where('pillar_id', $this->editingPillarId)
			->pluck('depends_on_pillar_id')
			->toArray();
	}
}


