<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('name');
            $table->string('url')->nullable()->after('logo');
            $table->dropColumn('category');
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->enum('category', [
                'languages',
                'frontend',
                'backend',
                'database',
                'devops',
                'tools',
                'cms',
                'testing',
                'other'
            ])->default('other')->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn(['logo', 'url']);
            $table->dropColumn('category');
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->enum('category', ['core', 'frontend', 'exploring'])->default('core');
        });
    }
};
