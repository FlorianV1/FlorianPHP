<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('role')->nullable();
            $table->string('project_type')->nullable();
            $table->string('complexity')->nullable();
            $table->text('responsibilities')->nullable();
            $table->json('languages')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_ongoing')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'project_type',
                'complexity',
                'responsibilities',
                'languages',
                'logo',
                'is_ongoing',
            ]);
        });
    }
};
