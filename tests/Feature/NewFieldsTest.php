<?php

namespace Tests\Feature;

use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_fields_are_populated_in_methodology()
    {
        // Run the seeders
        $this->artisan('db:seed');

        // Get all methodologies
        $methodologies = Methodology::all();

        foreach ($methodologies as $methodology) {
            // Check that new fields are populated
            $this->assertNotNull($methodology->questions_description, 'questions_description should not be null');
            $this->assertNotNull($methodology->questions_estimated_time, 'questions_estimated_time should not be null');
            $this->assertGreaterThan(0, $methodology->questions_count, 'questions_count should be greater than 0');
            
            // Check section descriptions based on type
            if ($methodology->type === 'twoSection') {
                $this->assertNotNull($methodology->first_section_description, 'first_section_description should not be null for twoSection');
                $this->assertNotNull($methodology->second_section_description, 'second_section_description should not be null for twoSection');
            } else {
                $this->assertNull($methodology->second_section_description, 'second_section_description should be null for non-twoSection');
            }
        }
    }

    public function test_new_fields_are_populated_in_pillars()
    {
        // Run the seeders
        $this->artisan('db:seed');

        // Get all pillars
        $pillars = Pillar::all();

        foreach ($pillars as $pillar) {
            // Check that new fields are populated
            $this->assertNotNull($pillar->definition, 'definition should not be null');
            $this->assertNotNull($pillar->questions_description, 'questions_description should not be null');
            $this->assertNotNull($pillar->questions_estimated_time, 'questions_estimated_time should not be null');
            $this->assertGreaterThan(0, $pillar->questions_count, 'questions_count should be greater than 0');
        }
    }

    public function test_new_fields_are_populated_in_modules()
    {
        // Run the seeders
        $this->artisan('db:seed');

        // Get all modules
        $modules = Module::all();

        foreach ($modules as $module) {
            // Check that new fields are populated
            $this->assertNotNull($module->definition, 'definition should not be null');
            $this->assertNotNull($module->questions_description, 'questions_description should not be null');
            $this->assertNotNull($module->questions_estimated_time, 'questions_estimated_time should not be null');
            $this->assertGreaterThan(0, $module->questions_count, 'questions_count should be greater than 0');
        }
    }

    public function test_questions_count_matches_actual_questions()
    {
        // Run the seeders
        $this->artisan('db:seed');

        // Check methodology questions count
        $methodologies = Methodology::all();
        foreach ($methodologies as $methodology) {
            $actualCount = $methodology->questions()->count();
            $this->assertEquals($actualCount, $methodology->questions_count, 
                "Methodology {$methodology->id} questions_count should match actual questions");
        }

        // Check pillar questions count
        $pillars = Pillar::all();
        foreach ($pillars as $pillar) {
            $actualCount = $pillar->questions()->count();
            $this->assertEquals($actualCount, $pillar->questions_count, 
                "Pillar {$pillar->id} questions_count should match actual questions");
        }

        // Check module questions count
        $modules = Module::all();
        foreach ($modules as $module) {
            $actualCount = $module->questions()->count();
            $this->assertEquals($actualCount, $module->questions_count, 
                "Module {$module->id} questions_count should match actual questions");
        }
    }

    public function test_arabic_content_is_present()
    {
        // Run both seeders
        $this->artisan('db:seed');
        $this->artisan('db:seed', ['--class' => 'DemoMethodologiesArabicSeeder']);

        // Check for Arabic methodologies
        $arabicMethodologies = Methodology::where('name', 'like', '%المنهجية%')->get();
        $this->assertGreaterThan(0, $arabicMethodologies->count(), 'Should have Arabic methodologies');

        // Check for Arabic pillars
        $arabicPillars = Pillar::where('name', 'like', '%العمود%')->get();
        $this->assertGreaterThan(0, $arabicPillars->count(), 'Should have Arabic pillars');

        // Check for Arabic modules
        $arabicModules = Module::where('name', 'like', '%الوحدة%')->get();
        $this->assertGreaterThan(0, $arabicModules->count(), 'Should have Arabic modules');

        // Verify Arabic content has proper descriptions
        foreach ($arabicMethodologies as $methodology) {
            $this->assertNotNull($methodology->questions_description, 'Arabic methodology should have questions_description');
            $this->assertNotNull($methodology->questions_estimated_time, 'Arabic methodology should have questions_estimated_time');
        }

        foreach ($arabicPillars as $pillar) {
            $this->assertNotNull($pillar->definition, 'Arabic pillar should have definition');
            $this->assertNotNull($pillar->questions_description, 'Arabic pillar should have questions_description');
        }

        foreach ($arabicModules as $module) {
            $this->assertNotNull($module->definition, 'Arabic module should have definition');
            $this->assertNotNull($module->questions_description, 'Arabic module should have questions_description');
        }
    }
} 