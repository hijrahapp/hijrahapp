<?php

namespace App\Http\Controllers;

use App\Http\Repositories\EnrichmentRepository;
use App\Models\Category;
use App\Models\User;
use App\Resources\EnrichmentDetailedResource;
use App\Resources\EnrichmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EnrichmentController extends Controller
{
    public function __construct(private EnrichmentRepository $enrichmentRepository) {}

    /**
     * Get all enrichments with filters.
     */
    public function all(Request $request): JsonResponse
    {
        try {
            // Handle category parameter - can be single name or array of names
            $categoryIds = [];
            if ($request->has('category')) {
                $categoryNames = is_array($request->category) ? $request->category : [$request->category];

                // Convert category names to IDs
                $categoryIds = Category::whereIn('name', $categoryNames)
                    ->where('active', true)
                    ->pluck('id')
                    ->toArray();
            }

            $enrichments = $this->enrichmentRepository->getAllWithFilters(
                $categoryIds,
                $request->type,
                $request->search
            );

            $metadata = [
                'count' => $enrichments->count(),
            ];

            if ($request->search && ! $request->category) {
                // Add categories metadata
                // Extract all unique category IDs from enrichments
                $categoryIds = $enrichments
                    ->pluck('categories') // Get all categories arrays
                    ->flatten() // Flatten arrays into single array
                    ->unique() // Get unique category IDs
                    ->filter(); // Remove any null values

                // Fetch actual Category objects and format them
                $categories = Category::whereIn('id', $categoryIds)
                    ->where('active', true)
                    ->orderBy('name')
                    ->get()
                    ->map(function ($category) {
                        return [
                            'value' => $category->name,
                            'label' => $category->name,
                        ];
                    });
                $metadata['categories'] = $categories;
            }

            if ($request->search && $request->category) {
                $types = $enrichments->pluck('type')->unique()->map(function ($type) {
                    return [
                        'value' => $type,
                        'label' => __('lookups.'.$type),
                    ];
                })->values();
                $metadata['types'] = $types;
            }

            return response()->json([
                'metadata' => $metadata,
                'list' => EnrichmentResource::collection($enrichments),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_enrichments'),
                'error' => $e->getMessage(),
                'stack' => $e->getTrace(),
            ], 500);
        }
    }

    /**
     * Get explore enrichments for explore page.
     */
    public function explore(): JsonResponse
    {
        try {
            $data = $this->enrichmentRepository->getExploreData();

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_explore_enrichments'),
                'error' => $e->getMessage(),
                'stack' => $e->getTrace(),
            ], 500);
        }
    }

    /**
     * Get enrichment details.
     */
    public function get(int $enrichmentId): JsonResponse
    {
        try {
            $enrichment = $this->enrichmentRepository->findById($enrichmentId);

            if (! $enrichment) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.enrichment_not_found'),
                ], 404);
            }

            return response()->json(new EnrichmentDetailedResource($enrichment));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_enrichment'),
            ], 500);
        }
    }

    /**
     * Toggle like status for an enrichment.
     */
    public function toggleLike(Request $request, int $enrichmentId): JsonResponse
    {
        try {
            $userId = $request->authUserId ?? $request->authUser?->id;

            $user = User::find($userId);
            if (! $user) {
                return response()->json(['message' => __('messages.user_not_found')], 404);
            }

            $enrichment = $this->enrichmentRepository->findById($enrichmentId);
            if (! $enrichment) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.enrichment_not_found'),
                ], 404);
            }

            $result = $this->enrichmentRepository->toggleLike($userId, $enrichmentId);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_liked' => $result['is_liked'],
                    'message' => $result['is_liked'] ?
                        __('messages.enrichment_liked') :
                        __('messages.enrichment_unliked'),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_toggling_like'),
            ], 500);
        }
    }

    /**
     * Toggle favorite status for an enrichment.
     */
    public function toggleFavorite(Request $request, int $enrichmentId): JsonResponse
    {
        try {
            $userId = $request->authUserId ?? $request->authUser?->id;

            $user = User::find($userId);
            if (! $user) {
                return response()->json(['message' => __('messages.user_not_found')], 404);
            }

            $enrichment = $this->enrichmentRepository->findById($enrichmentId);
            if (! $enrichment) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.enrichment_not_found'),
                ], 404);
            }

            $result = $this->enrichmentRepository->toggleFavorite($userId, $enrichmentId);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_favorited' => $result['is_favorited'],
                    'message' => $result['is_favorited'] ?
                        __('messages.enrichment_favorited') :
                        __('messages.enrichment_unfavorited'),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_toggling_favorite'),
            ], 500);
        }
    }
}
