<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    protected $fillable = [
        'quote',
        'author_name',
        'author_role',
        'company',
        'project_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * "Jane Doe, CTO at Acme" — whichever parts are filled in.
     */
    public function getAttributionAttribute(): string
    {
        return collect([
            $this->author_role,
            $this->company,
        ])->filter()->implode(' at ');
    }
}
