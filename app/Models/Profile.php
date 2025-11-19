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
        'about_text',
        'social_links',
    ];

    protected $casts = [
        'status_available' => 'boolean',
        'social_links' => 'array',
    ];
}
