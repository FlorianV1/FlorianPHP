<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'outcome',
        'role',
        'project_type',
        'complexity',
        'started_at',
        'finished_at',
        'is_ongoing',
        'responsibilities',
        'case_study_body',
        'meta_title',
        'meta_description',
        'languages',
        'tech_stack',
        'logo',
        'screenshots',
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
        'screenshots'   => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            if (blank($project->slug) && filled($project->title)) {
                $project->slug = static::uniqueSlug($project->title, $project->id);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'project';
        $slug = $base;
        $n = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    /**
     * A case study exists when there is a body to show and the project is
     * public — no separate flag to keep in sync.
     */
    public function hasCaseStudy(): bool
    {
        return filled($this->case_study_body) && (bool) $this->is_posted;
    }

    /**
     * Only projects that are currently ongoing.
     */
    public function scopeOngoing($query)
    {
        return $query->where('is_ongoing', true);
    }

    /**
     * Only projects that are posted / visible on the site.
     */
    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }

    /**
     * Posted projects that carry a case study body.
     */
    public function scopeWithCaseStudy($query)
    {
        return $query->posted()
            ->whereNotNull('case_study_body')
            ->where('case_study_body', '!=', '');
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
