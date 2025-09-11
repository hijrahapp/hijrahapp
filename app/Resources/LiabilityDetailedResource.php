<?php

namespace App\Resources;

use App\Services\ContextStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiabilityDetailedResource extends JsonResource
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
            'title' => $this->title,
            'header' => $this->header,
            'todos' => [],
        ];

        // Transform todos with completion status
        if ($this->todos && $request->authUserId) {
            $userProgress = $this->userProgress()
                ->where('user_id', $request->authUserId)
                ->first();

            $completedTodos = $userProgress ? ($userProgress->completed_todos ?? []) : [];

            $array['todos'] = collect($this->todos)->map(function ($todo, $index) use ($completedTodos) {
                return [
                    'id' => $index,
                    'text' => $todo,
                    'is_completed' => in_array($index, $completedTodos),
                ];
            })->values()->toArray();

            // Add liability status for the authenticated user
            $contextStatusService = app(ContextStatusService::class);
            $array['status'] = $contextStatusService->getLiabilityStatus($request->authUserId, $this->id);
        } elseif ($this->todos) {
            // If no authenticated user, return todos without completion status
            $array['todos'] = collect($this->todos)->map(function ($todo, $index) {
                return [
                    'id' => $index,
                    'text' => $todo,
                    'is_completed' => false,
                ];
            })->values()->toArray();
        }

        return $array;
    }
}
