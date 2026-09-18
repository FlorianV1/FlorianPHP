<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    public const SOURCES = [
        'manual' => 'Manual — use the value below',
        'projects_count' => 'Auto — number of posted projects',
        'years_experience' => 'Auto — years since the earliest experience',
    ];

    protected $fillable = [
        'value',
        'suffix',
        'label_singular',
        'label_plural',
        'auto_source',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'value' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The number actually rendered. Auto sources recompute so the figure can
     * never drift away from the content it describes.
     */
    public function getResolvedValueAttribute(): int
    {
        return match ($this->auto_source) {
            'projects_count' => Project::posted()->count(),
            'years_experience' => $this->yearsOfExperience(),
            default => (int) $this->value,
        };
    }

    public function getDisplayValueAttribute(): string
    {
        return $this->resolved_value . ($this->suffix ?? '');
    }

    public function getDisplayLabelAttribute(): string
    {
        return $this->resolved_value === 1
            ? $this->label_singular
            : ($this->label_plural ?: $this->label_singular);
    }

    private function yearsOfExperience(): int
    {
        $start = Experience::query()
            ->where('entry_type', 'work')
            ->whereNotNull('started_at')
            ->min('started_at');

        if (! $start) {
            return (int) $this->value;
        }

        return (int) floor(now()->diffInYears($start instanceof \DateTimeInterface ? $start : \Carbon\Carbon::parse($start), true));
    }
}
