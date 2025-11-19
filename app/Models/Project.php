<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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

    /**
     * Only projects that are currently ongoing.
     */
    public function scopeOngoing($query)
    {
        return $query->where('is_ongoing', true);
    }

    /**
     * Only projects that are posted / visible.
     */
    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }

    /**
     * Only featured projects.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Order projects by the "order" column if it exists, otherwise by created_at.
     */
    public function scopeOrdered($query)
    {
        if (Schema::hasColumn($this->getTable(), 'order')) {
            return $query->orderBy('order');
        }

        return $query->orderBy('created_at', 'desc');
    }
}
