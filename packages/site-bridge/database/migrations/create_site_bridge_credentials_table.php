<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_bridge_credentials')) {
            return;
        }

        Schema::create('site_bridge_credentials', function (Blueprint $table) {
            $table->id();

            // The long-lived key, encrypted at rest with this app's key.
            $table->text('key');
            $table->string('key_prefix', 16);

            // Where this site checks in, the id it was assigned, and the hub's
            // Ed25519 public key it pins (trust on first use over TLS at claim).
            $table->string('hub_url');
            $table->string('site_id');
            $table->text('hub_public_key');

            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }
};
