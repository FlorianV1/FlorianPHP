<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('url');
            $table->string('environment')->default('production')->index();
            $table->text('tech_stack_notes')->nullable();
            $table->string('hosting_provider')->nullable();
            $table->string('server_host')->nullable();
            $table->string('repository_url')->nullable();
            $table->string('management_url')->nullable();
            $table->string('mailcoach_url')->nullable();
            $table->string('bugsnag_project_url')->nullable();
            $table->text('bugsnag_project_key')->nullable();
            $table->string('bridge_base_url')->nullable();
            $table->text('bridge_api_token')->nullable();
            $table->unsignedTinyInteger('lock_level')->default(0)->index();
            $table->timestamp('last_seen_at')->nullable();
            $table->json('health')->nullable();
            $table->timestamps();
        });
    }
};
