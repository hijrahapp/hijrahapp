<?php

namespace App\Http\Controllers;

use App\Http\Repositories\MethodologyRepository;
use App\Resources\MethodologyResource;
use App\Resources\MethodologyDetailedResource;
use App\Resources\PillarDetailedResource;
use App\Resources\ModuleDetailedResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MethodologyController
{
    public function __construct(private MethodologyRepository $methodologyRepo) {}

    public function all(Request $request): JsonResponse
    {
        try {
            $methodologies = $this->methodologyRepo->getAllBasic();
            
            // Pass user ID to resources
            $methodologies->each(function ($methodology) use ($request) {
                $methodology->setAttribute('user_id', $request->authUser->id);
            });
            
            return response()->json(MethodologyResource::collection($methodologies));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_methodologies'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function get(Request $request, int $methodologyId): JsonResponse
    {
        try {
            $methodology = $this->methodologyRepo->findByIdWithFullDetails($methodologyId);
            
            if (!$methodology) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.methodology_not_found')
                ], 404);
            }

            // Pass user ID to resource
            $methodology->setAttribute('user_id', $request->authUser->id);

            return response()->json(new MethodologyDetailedResource($methodology));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_methodology'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get methodology with a specific pillar only
     */
    public function getPillar(Request $request, int $methodologyId, int $pillarId): JsonResponse
    {
        try {
            $methodology = $this->methodologyRepo->findByIdWithSpecificPillar($methodologyId, $pillarId);

            if (!$methodology) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.methodology_not_found')
                ], 404);
            }

            // Ensure the pillar exists within the methodology
            $hasPillar = $methodology->relationLoaded('pillars') && $methodology->pillars->contains('id', $pillarId);
            if (!$hasPillar) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.pillar_not_found_in_methodology')
                ], 404);
            }

            // Return pillar resource with user context
            $pillar = $methodology->pillars->firstWhere('id', $pillarId);
            $pillar->setAttribute('user_id', $request->authUser->id);
            return response()->json(new PillarDetailedResource($pillar));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_methodology'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get methodology with a specific direct module only
     */
    public function getModule(Request $request, int $methodologyId, int $moduleId): JsonResponse
    {
        try {
            $methodology = $this->methodologyRepo->findByIdWithSpecificModule($methodologyId, $moduleId);

            if (!$methodology) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.methodology_not_found')
                ], 404);
            }

            // Ensure the module exists within the methodology
            $hasModule = $methodology->relationLoaded('modules') && $methodology->modules->contains('id', $moduleId);
            if (!$hasModule) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.module_not_found_in_methodology')
                ], 404);
            }

            $module = $methodology->modules->firstWhere('id', $moduleId);
            $module->setAttribute('user_id', $request->authUser->id);
            return response()->json(new ModuleDetailedResource($module));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_methodology'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get methodology with a specific pillar and specific module under that pillar
     */
    public function getPillarModule(Request $request, int $methodologyId, int $pillarId, int $moduleId): JsonResponse
    {
        try {
            $methodology = $this->methodologyRepo->findByIdWithSpecificPillarModule($methodologyId, $pillarId, $moduleId);

            if (!$methodology) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.methodology_not_found')
                ], 404);
            }

            // Ensure both pillar and module exist and are related
            $hasPillar = $methodology->relationLoaded('pillars') && $methodology->pillars->contains('id', $pillarId);
            if (!$hasPillar) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.pillar_not_found_in_methodology')
                ], 404);
            }
            $pillar = $methodology->pillars->firstWhere('id', $pillarId);
            $hasModuleInPillar = $pillar && $pillar->relationLoaded('modules') && $pillar->modules->contains('id', $moduleId);
            if (!$hasModuleInPillar) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.module_not_found_in_pillar')
                ], 404);
            }

            // Return specific module under pillar
            $module = $pillar->modules->firstWhere('id', $moduleId);
            $module->setAttribute('user_id', $request->authUser->id);
            $module->setAttribute('pillar_id', $pillarId);
            return response()->json(new ModuleDetailedResource($module));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_methodology'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific methodology by ID with pillars from a specific section
     */
    public function getBySection(Request $request, int $methodologyId, int $sectionNumber): JsonResponse
    {
        try {
            // Validate section number
            if (!in_array($sectionNumber, [1, 2])) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.invalid_section_number')
                ], 400);
            }

            $methodology = $this->methodologyRepo->findByIdWithSectionPillars($methodologyId, $sectionNumber);
            
            if (!$methodology) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.methodology_not_found')
                ], 404);
            }

            // Validate that methodology is of twoSection type
            if ($methodology->type !== 'twoSection') {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.methodology_not_two_section_type')
                ], 400);
            }

            // Pass user ID and selected section to resource
            $methodology->setAttribute('user_id', $request->authUser->id);
            $methodology->setAttribute('section_number', $sectionNumber);

            return response()->json(new MethodologyDetailedResource($methodology));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_methodology_section'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
