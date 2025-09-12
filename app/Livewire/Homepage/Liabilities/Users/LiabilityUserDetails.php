<?php

namespace App\Livewire\Homepage\Liabilities\Users;

use App\Models\Liability;
use App\Models\User;
use App\Models\UserLiabilityProgress;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class LiabilityUserDetails extends Component
{
    public Liability $liability;

    public User $user;

    public function mount(Liability $liability, User $user)
    {
        $this->liability = $liability;
        $this->user = $user;
    }

    #[Computed]
    public function userLiabilityProgress()
    {
        return UserLiabilityProgress::where('user_id', $this->user->id)
            ->where('liability_id', $this->liability->id)
            ->first();
    }

    #[Computed]
    public function liabilityProgress()
    {
        $userProgress = $this->userLiabilityProgress;

        if (! $userProgress) {
            return [
                'completion_percentage' => 0,
                'completed_todos' => 0,
                'total_todos' => count($this->liability->todos ?? []),
                'status' => 'not_started',
            ];
        }

        $totalTodos = count($this->liability->todos ?? []);
        $completedTodos = $userProgress->getCompletedTodosCount();
        $completionPercentage = $totalTodos > 0 ? round(($completedTodos / $totalTodos) * 100, 1) : 0;

        $status = 'in_progress';
        if ($userProgress->is_completed) {
            $status = 'completed';
        } elseif ($completedTodos === 0) {
            $status = 'not_started';
        }

        return [
            'completion_percentage' => $completionPercentage,
            'completed_todos' => $completedTodos,
            'total_todos' => $totalTodos,
            'status' => $status,
        ];
    }

    #[Computed]
    public function todosWithStatus()
    {
        $todos = $this->liability->todos ?? [];
        $userProgress = $this->userLiabilityProgress;
        $completedTodos = $userProgress?->completed_todos ?? [];

        return collect($todos)->map(function ($todo, $index) use ($completedTodos) {
            return [
                'id' => $index,
                'title' => $todo,
                'is_completed' => in_array($index, $completedTodos),
            ];
        });
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'completed' => 'bg-green-50 text-green-600',
            'in_progress' => 'bg-blue-50 text-blue-600',
            'not_started' => 'bg-gray-50 text-gray-600',
            default => 'bg-gray-50 text-gray-600',
        };
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'completed' => 'Completed',
            'in_progress' => 'In Progress',
            'not_started' => 'Not Started',
            default => 'Unknown',
        };
    }

    public function render()
    {
        return view('livewire.homepage.liabilities.users.liability-user-details');
    }
}
