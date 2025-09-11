<?php

namespace App\Resources;

use App\Services\ContextStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $array = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'todos_count' => count($this->todos ?? []),
            'completed_todos_count' => 0,
        ];

        // Add completed todos count and status for the authenticated user
        if ($request->authUserId) {
            $userProgress = $this->userProgress()
                ->where('user_id', $request->authUserId)
                ->first();

            if ($userProgress) {
                $array['completed_todos_count'] = $userProgress->getCompletedTodosCount();
            }

            // Add liability status for the authenticated user
            $contextStatusService = app(ContextStatusService::class);
            $array['status'] = $contextStatusService->getLiabilityStatus($request->authUserId, $this->id);
        }

        // Include scoring information if this liability comes from user eligibility query
        if (isset($this->qualifying_module_id)) {
            $array['module_id'] = $this->qualifying_module_id;
            $array['module_name'] = $this->module_name;
            $array['methodology_id'] = $this->methodology_id;
            $array['methodology_name'] = $this->methodology_name;

            // Include pillar information only if available
            if ($this->pillar_id) {
                $array['pillar_id'] = $this->pillar_id;
                $array['pillar_name'] = $this->pillar_name;
            }
        }

        return $array;
    }
}
