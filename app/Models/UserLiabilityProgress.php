<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLiabilityProgress extends Model
{
    /** @use HasFactory<\Database\Factories\UserLiabilityProgressFactory> */
    use HasFactory;

    protected $table = 'user_liability_progress';

    protected $fillable = [
        'user_id',
        'liability_id',
        'completed_todos',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'completed_todos' => 'array',
            'is_completed' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/

    /**
     * The user who owns this progress record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The liability this progress belongs to.
     */
    public function liability(): BelongsTo
    {
        return $this->belongsTo(Liability::class);
    }

    /* -------------------------------------------------------------------------
     | Helper Methods
     |------------------------------------------------------------------------*/

    /**
     * Check if a specific todo is completed.
     */
    public function isTodoCompleted(int $todoId): bool
    {
        return in_array($todoId, $this->completed_todos ?? []);
    }

    /**
     * Mark a todo as completed.
     */
    public function markTodoCompleted(int $todoId): void
    {
        $completedTodos = $this->completed_todos ?? [];
        if (! in_array($todoId, $completedTodos)) {
            $completedTodos[] = $todoId;
            $this->completed_todos = $completedTodos;
        }
    }

    /**
     * Mark a todo as not completed.
     */
    public function markTodoNotCompleted(int $todoId): void
    {
        $completedTodos = $this->completed_todos ?? [];
        $this->completed_todos = array_values(array_filter($completedTodos, fn ($id) => $id !== $todoId));
    }

    /**
     * Get count of completed todos.
     */
    public function getCompletedTodosCount(): int
    {
        return count($this->completed_todos ?? []);
    }

    /**
     * Check if all todos are completed for the liability.
     */
    public function areAllTodosCompleted(): bool
    {
        $liability = $this->liability;
        if (! $liability || ! $liability->todos) {
            return false;
        }

        $totalTodos = count($liability->todos);
        $completedTodos = count($this->completed_todos ?? []);

        return $totalTodos === $completedTodos;
    }
}
