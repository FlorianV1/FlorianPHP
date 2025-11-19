<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {

            if (!Schema::hasColumn('projects', 'role')) {
                $table->string('role')->nullable();
            }
            if (!Schema::hasColumn('projects', 'project_type')) {
                $table->string('project_type')->nullable();
            }
            if (!Schema::hasColumn('projects', 'complexity')) {
                $table->string('complexity')->nullable();
            }

            if (!Schema::hasColumn('projects', 'started_at')) {
                $table->date('started_at')->nullable();
            }
            if (!Schema::hasColumn('projects', 'finished_at')) {
                $table->date('finished_at')->nullable();
            }
            if (!Schema::hasColumn('projects', 'is_ongoing')) {
                $table->boolean('is_ongoing')->default(false);
            }

            if (!Schema::hasColumn('projects', 'responsibilities')) {
                $table->text('responsibilities')->nullable();
            }

            if (!Schema::hasColumn('projects', 'languages')) {
                $table->json('languages')->nullable();
            }
            if (!Schema::hasColumn('projects', 'tech_stack')) {
                $table->json('tech_stack')->nullable();
            }

            if (!Schema::hasColumn('projects', 'logo')) {
                $table->string('logo')->nullable();
            }

            if (!Schema::hasColumn('projects', 'code_url')) {
                $table->string('code_url')->nullable();
            }
            if (!Schema::hasColumn('projects', 'live_url')) {
                $table->string('live_url')->nullable();
            }

            if (!Schema::hasColumn('projects', 'order')) {
                $table->integer('order')->default(0);
            }
            if (!Schema::hasColumn('projects', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
            }
            if (!Schema::hasColumn('projects', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Only drop columns that exist to avoid rollback crashes
            foreach ([
                         'role',
                         'project_type',
                         'complexity',
                         'started_at',
                         'finished_at',
                         'is_ongoing',
                         'responsibilities',
                         'languages',
                         'tech_stack',
                         'logo',
                         'code_url',
                         'live_url',
                         'order',
                         'is_featured',
                         'is_active',
                     ] as $col) {
                if (Schema::hasColumn('projects', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
