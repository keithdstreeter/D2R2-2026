<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuesheet extends Model
{
    /** @use HasFactory<\Database\Factories\CuesheetFactory> */
    use HasFactory;

    protected $fillable = [
        'ride',
        'turn',
        'notes',
        'distance',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
                'ride' => 'string',
            'turn' => 'string',
            'notes' => 'string',
            'distance' => 'float',
        ];
    }

    /**
     * @return BelongsTo<AgeGroup, $this>
     */
    // public function ageGroup(): BelongsTo
    // {
    //     return $this->belongsTo(AgeGroup::class);
    // }

    /**
     * @return HasMany<Question, $this>
     */
    // public function questions(): HasMany
    // {
    //     return $this->hasMany(Question::class);
    // }

    /**
     * @param  Builder<Cuesheet>  $query
     * @return Builder<Cuesheet>
     */
    // public function scopeActive(Builder $query): Builder
    // {
    //     return $query->where('is_active', true);
    // }
}
