<?php

namespace Tests\Feature;

use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProgramModuleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Program $program;

    protected Methodology $methodology;

    protected Module $module;

    protected Pillar $pillar;

    protected function setUp(): void
    {
        parent::setUp();

        // Create role first to avoid foreign key constraint issues
        Role::factory()->create(['id' => 1, 'name' => 'Customer']);

        $this->user = User::factory()->create();
        $this->program = Program::factory()->create();
        $this->methodology = Methodology::factory()->create(['active' => true]);
        $this->module = Module::factory()->create(['active' => true]);
        $this->pillar = Pillar::factory()->create(['active' => true]);

        // Set up relationships
        $this->methodology->modules()->attach($this->module->id);

        // Create methodology_pillar relationship
        \DB::table('methodology_pillar')->insert([
            'methodology_id' => $this->methodology->id,
            'pillar_id' => $this->pillar->id,
            'sequence' => 1,
            'weight' => 1.0,
            'section' => 'first',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_program_modules_table_renders_correctly(): void
    {
        Livewire::test('homepage.programs.modules.program-modules-table', ['program' => $this->program])
            ->assertStatus(200)
            ->assertSee('Related Modules')
            ->assertSee('No modules assigned to this program');
    }

    public function test_add_module_modal_renders_correctly(): void
    {
        Livewire::test('homepage.programs.modules.program-module-add-modal')
            ->assertStatus(200)
            ->assertSee('Add Module to Program');
    }

    public function test_can_add_module_to_program(): void
    {
        Livewire::test('homepage.programs.modules.program-module-add-modal')
            ->set('programId', $this->program->id)
            ->set('selectedMethodologyId', $this->methodology->id)
            ->set('selectedModuleId', $this->module->id)
            ->set('minScore', 70.0)
            ->set('maxScore', 90.0)
            ->call('addModule')
            ->assertHasNoErrors();

        // Verify the module was attached to the program
        $this->assertDatabaseHas('program_module', [
            'program_id' => $this->program->id,
            'module_id' => $this->module->id,
            'methodology_id' => $this->methodology->id,
            'min_score' => 70.0,
            'max_score' => 90.0,
        ]);
    }

    public function test_can_add_module_with_pillar_to_program(): void
    {
        // First create the pillar_module relationship with methodology_id
        \DB::table('pillar_module')->insert([
            'methodology_id' => $this->methodology->id,
            'pillar_id' => $this->pillar->id,
            'module_id' => $this->module->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test the add module functionality directly with required scores
        Livewire::test('homepage.programs.modules.program-module-add-modal')
            ->set('programId', $this->program->id)
            ->set('selectedMethodologyId', $this->methodology->id)
            ->set('selectedPillarId', $this->pillar->id)
            ->set('selectedModuleId', $this->module->id)
            ->set('minScore', 70.0)
            ->set('maxScore', 90.0)
            ->call('addModule')
            ->assertHasNoErrors();

        // Verify the module was attached to the program with pillar
        $this->assertDatabaseHas('program_module', [
            'program_id' => $this->program->id,
            'module_id' => $this->module->id,
            'methodology_id' => $this->methodology->id,
            'pillar_id' => $this->pillar->id,
            'min_score' => 70.0,
            'max_score' => 90.0,
        ]);
    }

    public function test_cannot_add_duplicate_module_to_program(): void
    {
        // First, add the module to the program
        $this->program->modules()->attach($this->module->id, [
            'methodology_id' => $this->methodology->id,
            'pillar_id' => null,
        ]);

        // Try to add the same module again
        Livewire::test('homepage.programs.modules.program-module-add-modal')
            ->set('programId', $this->program->id)
            ->set('selectedMethodologyId', $this->methodology->id)
            ->set('selectedModuleId', $this->module->id)
            ->call('addModule')
            ->assertSet('error', 'This module is already assigned to this program with the same methodology and pillar.');
    }

    public function test_can_remove_module_from_program(): void
    {
        // First, add the module to the program
        $this->program->modules()->attach($this->module->id, [
            'methodology_id' => $this->methodology->id,
            'pillar_id' => null,
        ]);

        Livewire::test('homepage.programs.modules.program-modules-table', ['program' => $this->program])
            ->call('removeModule', $this->module->id);

        // Verify the module was removed from the program
        $this->assertDatabaseMissing('program_module', [
            'program_id' => $this->program->id,
            'module_id' => $this->module->id,
        ]);
    }

    public function test_search_filters_modules_correctly(): void
    {
        // Create a module with a specific name for testing
        $testModule = Module::factory()->create(['active' => true, 'name' => 'Searchable Test Module']);
        $this->methodology->modules()->attach($testModule->id);

        // Add the module to the program
        $this->program->modules()->attach($testModule->id, [
            'methodology_id' => $this->methodology->id,
            'pillar_id' => null,
        ]);

        $component = Livewire::test('homepage.programs.modules.program-modules-table', ['program' => $this->program]);

        // First verify the module appears without search
        $component->assertSee('Searchable Test Module');

        // Test search functionality - search for part of the name
        $component->set('search', 'Searchable')
            ->assertSee('Searchable Test Module');

        $component->set('search', 'nonexistent_search_term')
            ->assertDontSee('Searchable Test Module');
    }

    public function test_methodology_filter_works_correctly(): void
    {
        // Create specific modules with distinct names
        $module1 = Module::factory()->create(['active' => true, 'name' => 'Module One']);
        $module2 = Module::factory()->create(['active' => true, 'name' => 'Module Two']);

        // Create another methodology
        $methodology2 = Methodology::factory()->create(['active' => true]);

        // Attach modules to their respective methodologies
        $this->methodology->modules()->attach($module1->id);
        $methodology2->modules()->attach($module2->id);

        // Add both modules to the program with different methodologies
        $this->program->modules()->attach($module1->id, [
            'methodology_id' => $this->methodology->id,
            'pillar_id' => null,
        ]);
        $this->program->modules()->attach($module2->id, [
            'methodology_id' => $methodology2->id,
            'pillar_id' => null,
        ]);

        $component = Livewire::test('homepage.programs.modules.program-modules-table', ['program' => $this->program]);

        // First verify both modules appear without filter
        $component->assertSee('Module One')
            ->assertSee('Module Two');

        // Test methodology filter
        $component->set('selectedMethodologyId', $this->methodology->id)
            ->assertSee('Module One')
            ->assertDontSee('Module Two');
    }

    public function test_validation_requires_methodology_module_and_scores(): void
    {
        Livewire::test('homepage.programs.modules.program-module-add-modal')
            ->set('programId', $this->program->id)
            ->call('addModule')
            ->assertHasErrors(['selectedMethodologyId', 'selectedModuleId', 'minScore', 'maxScore']);
    }

    public function test_validation_requires_scores_when_methodology_and_module_provided(): void
    {
        Livewire::test('homepage.programs.modules.program-module-add-modal')
            ->set('programId', $this->program->id)
            ->set('selectedMethodologyId', $this->methodology->id)
            ->set('selectedModuleId', $this->module->id)
            ->call('addModule')
            ->assertHasErrors(['minScore', 'maxScore']);
    }

    public function test_score_range_validation(): void
    {
        Livewire::test('homepage.programs.modules.program-module-add-modal')
            ->set('programId', $this->program->id)
            ->set('selectedMethodologyId', $this->methodology->id)
            ->set('selectedModuleId', $this->module->id)
            ->set('minScore', 90.0)
            ->set('maxScore', 80.0) // max less than min
            ->call('addModule')
            ->assertHasErrors(['maxScore']);

        Livewire::test('homepage.programs.modules.program-module-add-modal')
            ->set('programId', $this->program->id)
            ->set('selectedMethodologyId', $this->methodology->id)
            ->set('selectedModuleId', $this->module->id)
            ->set('minScore', -10.0) // negative value
            ->call('addModule')
            ->assertHasErrors(['minScore']);

        Livewire::test('homepage.programs.modules.program-module-add-modal')
            ->set('programId', $this->program->id)
            ->set('selectedMethodologyId', $this->methodology->id)
            ->set('selectedModuleId', $this->module->id)
            ->set('maxScore', 150.0) // over 100
            ->call('addModule')
            ->assertHasErrors(['maxScore']);
    }
}
