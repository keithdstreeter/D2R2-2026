<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    /** @use HasFactory<\Database\Factories\RegistrationFactory> */
    use HasFactory;

    protected $fillable = [
        'bib',
        'first_name',
        'last_name',
        'category_entered',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bib' => 'string',
            'first_name' => 'string',
            'last_name' => 'string',
            'category_entered' => 'string',
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
     * @param  Builder<Registration>  $query
     * @return Builder<Registration>
     */
    // public function scopeActive(Builder $query): Builder
    // {
    //     return $query->where('is_active', true);
    // }
}
