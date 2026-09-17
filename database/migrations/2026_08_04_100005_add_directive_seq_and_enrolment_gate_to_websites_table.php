<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            // Monotonic per-site counter carried in every signed directive.
            // The site rejects any directive at or below the last it accepted,
            // which blocks replay of an old (e.g. level-0) directive regardless
            // of clock skew.
            $table->unsignedBigInteger('directive_seq')->default(0)->after('lock_reason');

            // Set the moment a site first claims a key. Once set, the legacy
            // pull/push path is a no-op for this site — the heartbeat +
            // directive path is the single authoritative source of lock state.
            $table->timestamp('enrolled_at')->nullable()->after('last_heartbeat_at');
        });
    }
};
