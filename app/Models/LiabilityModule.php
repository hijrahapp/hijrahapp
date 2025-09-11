<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiabilityModule extends Model
{
    /** @use HasFactory<\Database\Factories\LiabilityModuleFactory> */
    use HasFactory;

    protected $table = 'liability_module';

    protected $fillable = [
        'liability_id',
        'module_id',
        'methodology_id',
        'pillar_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/

    /**
     * The liability this record belongs to.
     */
    public function liability(): BelongsTo
    {
        return $this->belongsTo(Liability::class);
    }

    /**
     * The module this record belongs to.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * The methodology this record belongs to.
     */
    public function methodology(): BelongsTo
    {
        return $this->belongsTo(Methodology::class);
    }

    /**
     * The pillar this record belongs to.
     */
    public function pillar(): BelongsTo
    {
        return $this->belongsTo(Pillar::class);
    }
}
