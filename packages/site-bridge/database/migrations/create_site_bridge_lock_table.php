<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_bridge_lock')) {
            return;
        }

        Schema::create('site_bridge_lock', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('lock_level')->default(0);
            $table->string('reason')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->string('locked_by')->nullable();
            // Sequence of the last hub directive accepted; anything at or below
            // it is rejected as a replay.
            $table->unsignedBigInteger('directive_seq')->default(0);
            $table->timestamps();
        });
    }
};
