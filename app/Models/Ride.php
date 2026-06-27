<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ride extends Model
{
    /** @use HasFactory<\Database\Factories\RideFactory> */
    use HasFactory;

    protected $fillable = [
        'ride',
        'ride_desc',
        'distance_k',
        'distance_miles',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ride' => 'string',
            'ride_desc' => 'string',
            'distance_k' => 'float',
            'distance_miles' => 'float',
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
     * @param  Builder<Ride>  $query
     * @return Builder<Ride>
     */
    // public function scopeActive(Builder $query): Builder
    // {
    //     return $query->where('is_active', true);
    // }
}
