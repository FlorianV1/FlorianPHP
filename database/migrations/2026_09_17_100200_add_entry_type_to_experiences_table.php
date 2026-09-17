<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            // Education reuses the experience timeline rather than getting its
            // own table — same shape, same resource, one extra discriminator.
            $table->string('entry_type')->default('work')->after('id')->index();
            $table->string('credential')->nullable()->after('employment_type');
        });
    }

    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropIndex(['entry_type']);
            $table->dropColumn(['entry_type', 'credential']);
        });
    }
};
