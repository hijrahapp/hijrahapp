<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'gender' => $this->gender,
            'birthDate' => $this->birthDate->format('Y-m-d'),
            'role' => $this->role->name->value,
        ];
    }
}
