<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="john@example.com"),
 *     @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male"),
 *     @OA\Property(property="birthdate", type="date", format="date", example="2000-01-01"),
 *     @OA\Property(property="role", type="string", enum={"SuperAdmin", "Admin", "Expert", "Customer"}, example="Customer")
 *     @OA\Property(property="profilePhoto", type="string", example="localhost:8000/assets/media/avatars/blank.png"),
 * )
 */
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return $this->userArray();
    }

    public function userArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'gender' => $this->gender,
            'birthDate' => $this->birthDate ? $this->birthDate->format('Y-m-d') : null,
            'role' => $this->role ? $this->role->name->value : null,
            'profilePhoto' => $this->profile_picture ?? asset('/assets/media/avatars/blank.png'),
        ];
    }
}
