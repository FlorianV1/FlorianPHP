<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            if (!Schema::hasColumn('experiences', 'location')) {
                $table->string('location')->nullable()->after('company');
            }
            if (!Schema::hasColumn('experiences', 'employment_type')) {
                $table->string('employment_type')->nullable()->after('location'); // full-time, part-time, contract, freelance
            }
            if (!Schema::hasColumn('experiences', 'company_logo')) {
                $table->string('company_logo')->nullable()->after('company');
            }
            if (!Schema::hasColumn('experiences', 'company_url')) {
                $table->string('company_url')->nullable()->after('company_logo');
            }
            if (!Schema::hasColumn('experiences', 'started_at')) {
                $table->date('started_at')->nullable()->after('period');
            }
            if (!Schema::hasColumn('experiences', 'ended_at')) {
                $table->date('ended_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('experiences', 'is_current')) {
                $table->boolean('is_current')->default(false)->after('ended_at');
            }
            if (!Schema::hasColumn('experiences', 'description')) {
                $table->text('description')->nullable()->after('responsibilities');
            }
            if (!Schema::hasColumn('experiences', 'skills')) {
                $table->json('skills')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $columns = ['location', 'employment_type', 'company_logo', 'company_url', 'started_at', 'ended_at', 'is_current', 'description', 'skills'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('experiences', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
