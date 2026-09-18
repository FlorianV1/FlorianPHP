<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Lets the hub pull open-error counts from Bugsnag itself, using one
| agency-wide auth token plus the project each website maps to. The
| existing bugsnag_project_key (a project's notifier API key) stays: it is
| what `bugsnag:link-projects` matches a website to its project by.
*/
return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table): void {
            $table->string('bugsnag_project_id')->nullable()->after('bugsnag_project_key');
            $table->unsignedInteger('bugsnag_open_errors')->nullable()->after('bugsnag_project_id');
            $table->timestamp('bugsnag_synced_at')->nullable()->after('bugsnag_open_errors');
            $table->string('bugsnag_sync_error')->nullable()->after('bugsnag_synced_at');
        });
    }
};
