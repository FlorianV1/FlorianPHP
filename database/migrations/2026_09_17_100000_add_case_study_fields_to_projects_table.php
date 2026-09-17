<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // `impact` already held one-sentence result copy; the rename makes the
        // column say what it is. No data is touched.
        if (Schema::hasColumn('projects', 'impact') && ! Schema::hasColumn('projects', 'outcome')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->renameColumn('impact', 'outcome');
            });
        }

        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }

            if (! Schema::hasColumn('projects', 'screenshots')) {
                $table->json('screenshots')->nullable()->after('logo');
            }

            if (! Schema::hasColumn('projects', 'case_study_body')) {
                $table->longText('case_study_body')->nullable()->after('responsibilities');
            }

            if (! Schema::hasColumn('projects', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('case_study_body');
            }

            if (! Schema::hasColumn('projects', 'meta_description')) {
                $table->string('meta_description', 500)->nullable()->after('meta_title');
            }
        });

        // Backfill slugs from existing titles, de-duplicating where two projects
        // would collide.
        $seen = [];

        foreach (DB::table('projects')->select('id', 'title', 'slug')->orderBy('id')->get() as $project) {
            if (filled($project->slug)) {
                $seen[$project->slug] = true;

                continue;
            }

            $base = Str::slug($project->title) ?: 'project-' . $project->id;
            $slug = $base;
            $n = 2;

            while (isset($seen[$slug])) {
                $slug = $base . '-' . $n++;
            }

            $seen[$slug] = true;

            DB::table('projects')->where('id', $project->id)->update(['slug' => $slug]);
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'screenshots', 'case_study_body', 'meta_title', 'meta_description']);
        });

        if (Schema::hasColumn('projects', 'outcome') && ! Schema::hasColumn('projects', 'impact')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->renameColumn('outcome', 'impact');
            });
        }
    }
};
