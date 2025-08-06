<?php

namespace Tests\Feature;

use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeededModulePillarTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_question_pillar_id_is_populated()
    {
        // Run the seeders
        $this->artisan('db:seed');

        // Get all module_question records
        $moduleQuestions = \DB::table('module_question')->get();
        
        // Verify we have module questions
        $this->assertGreaterThan(0, $moduleQuestions->count(), 'Should have module questions');

        // Check each module question
        foreach ($moduleQuestions as $moduleQuestion) {
            // Verify required fields are present
            $this->assertNotNull($moduleQuestion->methodology_id, 'methodology_id should not be null');
            $this->assertNotNull($moduleQuestion->module_id, 'module_id should not be null');
            $this->assertNotNull($moduleQuestion->question_id, 'question_id should not be null');
            $this->assertNotNull($moduleQuestion->weight, 'weight should not be null');

            // Check if this module belongs to a pillar
            $pillarModule = \DB::table('pillar_module')
                ->where('module_id', $moduleQuestion->module_id)
                ->first();

            if ($pillarModule) {
                // Module belongs to a pillar, so pillar_id should be set
                $this->assertNotNull($moduleQuestion->pillar_id, 
                    "Module {$moduleQuestion->module_id} belongs to pillar {$pillarModule->pillar_id}, but pillar_id is null in module_question");
                $this->assertEquals($pillarModule->pillar_id, $moduleQuestion->pillar_id,
                    "Module question pillar_id should match pillar_module pillar_id");
            } else {
                // Module is direct to methodology, so pillar_id can be null
                $this->assertNull($moduleQuestion->pillar_id, 
                    "Module {$moduleQuestion->module_id} is direct to methodology, but pillar_id is not null");
            }

            // Verify the methodology_id is correct
            $methodologyModule = \DB::table('methodology_module')
                ->where('module_id', $moduleQuestion->module_id)
                ->first();

            if ($methodologyModule) {
                // Direct module to methodology
                $this->assertEquals($methodologyModule->methodology_id, $moduleQuestion->methodology_id);
            } else {
                // Module belongs to a pillar
                $pillarModule = \DB::table('pillar_module')
                    ->where('module_id', $moduleQuestion->module_id)
                    ->first();
                
                if ($pillarModule) {
                    $methodologyPillar = \DB::table('methodology_pillar')
                        ->where('pillar_id', $pillarModule->pillar_id)
                        ->first();
                    
                    $this->assertEquals($methodologyPillar->methodology_id, $moduleQuestion->methodology_id);
                }
            }
        }
    }

    public function test_modules_in_pillars_have_correct_context()
    {
        // Run the seeders
        $this->artisan('db:seed');

        // Get all modules that belong to pillars
        $modulesInPillars = \DB::table('pillar_module')->get();

        foreach ($modulesInPillars as $pillarModule) {
            // Get the methodology for this pillar
            $methodologyPillar = \DB::table('methodology_pillar')
                ->where('pillar_id', $pillarModule->pillar_id)
                ->first();

            // Get module questions for this module
            $moduleQuestions = \DB::table('module_question')
                ->where('module_id', $pillarModule->module_id)
                ->get();

            foreach ($moduleQuestions as $moduleQuestion) {
                // Verify the context is correct
                $this->assertEquals($methodologyPillar->methodology_id, $moduleQuestion->methodology_id,
                    "Module question methodology_id should match pillar's methodology");
                $this->assertEquals($pillarModule->pillar_id, $moduleQuestion->pillar_id,
                    "Module question pillar_id should match pillar_module pillar_id");
            }
        }
    }

    public function test_direct_modules_have_null_pillar_id()
    {
        // Run the seeders
        $this->artisan('db:seed');

        // Get all modules that are direct to methodologies
        $directModules = \DB::table('methodology_module')->get();

        foreach ($directModules as $methodologyModule) {
            // Get module questions for this module
            $moduleQuestions = \DB::table('module_question')
                ->where('module_id', $methodologyModule->module_id)
                ->get();

            foreach ($moduleQuestions as $moduleQuestion) {
                // Verify pillar_id is null for direct modules
                $this->assertNull($moduleQuestion->pillar_id,
                    "Direct module {$methodologyModule->module_id} should have null pillar_id");
                $this->assertEquals($methodologyModule->methodology_id, $moduleQuestion->methodology_id,
                    "Direct module question methodology_id should match methodology_module methodology_id");
            }
        }
    }
} 