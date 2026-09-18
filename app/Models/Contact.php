<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    /**
     * Every column except the key and timestamps. These models are reached
     * only through the admin panel and internal services, never from request
     * input, but they are listed explicitly rather than unguarded so that a
     * new column has to be opted in deliberately.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'name',
        'role',
        'email',
        'phone',
    ];

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
