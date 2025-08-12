<?php

namespace App\Http\Controllers;

use App\Http\Repositories\MethodologyRepository;
use App\Resources\MethodologyResource;
use App\Resources\MethodologyDetailedResource;
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
