<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('site_bridge_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_bridge_key_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip')->nullable();
            $table->string('domain')->nullable();
            $table->unsignedTinyInteger('reported_lock_level')->nullable();
            $table->timestamp('received_at');

            $table->index(['website_id', 'received_at']);
        });
    }
};
