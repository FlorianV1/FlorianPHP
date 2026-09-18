<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('site_bridge_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();

            // Only the sha256 hash is ever stored; the plaintext sb_live_ key
            // is shown once at creation and never persisted.
            $table->string('key_hash')->unique();
            $table->string('key_prefix', 16);

            // The domain a key first checked in from; later check-ins from a
            // different domain get flagged (not rejected).
            $table->string('first_seen_domain')->nullable();
            $table->string('last_seen_domain')->nullable();
            $table->timestamp('domain_mismatch_at')->nullable();

            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['website_id', 'revoked_at']);
        });
    }
};
