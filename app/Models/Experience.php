<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Experience extends Model
{
    protected $fillable = [
        'title',
        'company',
        'company_logo',
        'company_url',
        'location',
        'employment_type',
        'period',
        'started_at',
        'ended_at',
        'is_current',
        'description',
        'responsibilities',
        'skills',
        'order',
        'is_active',
    ];

    protected $casts = [
        'responsibilities' => 'array',
        'skills' => 'array',
        'started_at' => 'date',
        'ended_at' => 'date',
        'is_current' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderByDesc('started_at');
    }

    public function getDurationAttribute()
    {
        if (!$this->started_at) return null;

        $end = $this->is_current ? Carbon::now() : ($this->ended_at ?? Carbon::now());
        $diff = $this->started_at->diff($end);

        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' yr' . ($diff->y > 1 ? 's' : '');
        if ($diff->m > 0) $parts[] = $diff->m . ' mo' . ($diff->m > 1 ? 's' : '');

        return implode(' ', $parts) ?: 'Less than a month';
    }

    public function getPeriodLabelAttribute()
    {
        if (!$this->started_at) return $this->period;

        $start = $this->started_at->format('M Y');
        $end = $this->is_current ? 'Present' : ($this->ended_at ? $this->ended_at->format('M Y') : 'Present');

        return "$start - $end";
    }

    protected static function booted()
    {
        static::saving(function ($experience) {
            // Auto-generate period from dates
            if ($experience->started_at) {
                $start = $experience->started_at->format('M Y');
                $end = $experience->is_current ? 'Present' : ($experience->ended_at ? $experience->ended_at->format('M Y') : 'Present');
                $experience->period = "$start - $end";
            }
        });
    }
}
