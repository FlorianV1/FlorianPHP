<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'name',
        'role',
        'tagline',
        'subtitle',
        'status_text',
        'status_available',
        'email',
        'location',
        'location_timezone',
        'hero_cta_primary_label',
        'hero_cta_primary_url',
        'hero_cta_secondary_label',
        'hero_cta_secondary_url',
        'contact_intro',
        'stat_1_value',
        'stat_1_label',
        'stat_2_value',
        'stat_2_label',
        'stat_3_value',
        'stat_3_label',
        'about_stack_primary',
        'about_stack_secondary',
        'footer_tagline',
        'about_text',
        'social_links',
    ];

    protected $casts = [
        'status_available' => 'boolean',
        'social_links' => 'array',
    ];
}
