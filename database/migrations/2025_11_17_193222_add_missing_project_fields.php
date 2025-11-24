<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'role')) {
                $table->string('role')->nullable();
            }

            if (! Schema::hasColumn('projects', 'project_type')) {
                $table->string('project_type')->nullable();
            }

            if (! Schema::hasColumn('projects', 'complexity')) {
                $table->string('complexity')->nullable();
            }

            if (! Schema::hasColumn('projects', 'responsibilities')) {
                $table->text('responsibilities')->nullable();
            }

            if (! Schema::hasColumn('projects', 'languages')) {
                $table->json('languages')->nullable();
            }

            if (! Schema::hasColumn('projects', 'logo')) {
                $table->string('logo')->nullable();
            }

            if (! Schema::hasColumn('projects', 'is_ongoing')) {
                $table->boolean('is_ongoing')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'role')) {
                $table->dropColumn('role');
            }

            if (Schema::hasColumn('projects', 'project_type')) {
                $table->dropColumn('project_type');
            }

            if (Schema::hasColumn('projects', 'complexity')) {
                $table->dropColumn('complexity');
            }

            if (Schema::hasColumn('projects', 'responsibilities')) {
                $table->dropColumn('responsibilities');
            }

            if (Schema::hasColumn('projects', 'languages')) {
                $table->dropColumn('languages');
            }

            if (Schema::hasColumn('projects', 'logo')) {
                $table->dropColumn('logo');
            }

            if (Schema::hasColumn('projects', 'is_ongoing')) {
                $table->dropColumn('is_ongoing');
            }
        });
    }
};
