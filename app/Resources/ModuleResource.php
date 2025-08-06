<?php

namespace App\Resources;

use App\Traits\HasTagTitles;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    use HasTagTitles;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'definition' => $this->definition,
            'objectives' => $this->objectives,
            'tags' => $this->getTagTitles($this->tags),
            'questions' => QuestionResource::collection($this->questions),
        ];
    }
}