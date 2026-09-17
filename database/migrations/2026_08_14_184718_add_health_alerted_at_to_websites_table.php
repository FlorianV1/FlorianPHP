<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Tracks whether a "site is red" alert has been pushed for the site's current
| bad spell, so the health check notifies once on the way down and once on
| recovery instead of every minute in between.
*/
return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table): void {
            $table->timestamp('health_alerted_at')->nullable()->after('last_heartbeat_at');
        });
    }
};
