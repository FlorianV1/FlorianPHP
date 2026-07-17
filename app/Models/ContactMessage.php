<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'message', 'read_at', 'is_spam'];

    protected $casts = [
        'read_at' => 'datetime',
        'is_spam' => 'boolean',
    ];

    /**
     * Hide spam from every query by default (admin list, widgets, counts).
     * Use ContactMessage::onlySpam() to review the quarantine.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('notSpam', function ($query) {
            $query->where('is_spam', false);
        });
    }

    public function scopeOnlySpam($query)
    {
        return $query->withoutGlobalScope('notSpam')->where('is_spam', true);
    }

    public function scopeWithSpam($query)
    {
        return $query->withoutGlobalScope('notSpam');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if (! $this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }
}
