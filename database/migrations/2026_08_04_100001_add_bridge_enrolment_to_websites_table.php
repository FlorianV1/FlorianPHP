<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            // Stable public identifier a site pins at claim time and checks
            // against every signed lock directive it receives.
            $table->uuid('bridge_site_id')->nullable()->unique()->after('id');

            // Lock the hub *wants* the site at, distinct from `lock_level`
            // which is the level the site last reported enforcing.
            $table->unsignedTinyInteger('desired_lock_level')->default(0)->after('lock_level');
            $table->string('lock_reason')->nullable()->after('desired_lock_level');

            // Set from the site's outbound heartbeats (the new transport),
            // separate from `last_seen_at` which the legacy pull still writes.
            $table->timestamp('last_heartbeat_at')->nullable()->after('last_seen_at');
        });

        DB::table('websites')->whereNull('bridge_site_id')->orderBy('id')->each(function (object $website): void {
            DB::table('websites')->where('id', $website->id)->update([
                'bridge_site_id' => (string) Str::uuid(),
            ]);
        });
    }
};
