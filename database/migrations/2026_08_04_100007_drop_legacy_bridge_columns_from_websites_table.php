<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Removes the legacy hub-pull transport columns. Sites now check in via
| outbound signed heartbeats; the dashboard no longer stores a per-site
| base URL or bearer token to pull from.
*/
return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table): void {
            $table->dropColumn(['bridge_base_url', 'bridge_api_token']);
        });
    }
};
