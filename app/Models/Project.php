<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'title',
        'description',
        'impact',
        'role',
        'project_type',
        'complexity',
        'started_at',
        'finished_at',
        'is_ongoing',
        'responsibilities',
        'languages',
        'tech_stack',
        'logo',
        'code_url',
        'live_url',
        'order',
        'is_featured',
        'is_posted',
    ];

    protected $casts = [
        'started_at'    => 'date',
        'finished_at'   => 'date',
        'is_ongoing'    => 'boolean',
        'is_featured'   => 'boolean',
        'is_posted'     => 'boolean',
        'languages'     => 'array',
        'tech_stack'    => 'array',
    ];

    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
