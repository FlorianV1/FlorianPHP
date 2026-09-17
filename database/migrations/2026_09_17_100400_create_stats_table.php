<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stats', function (Blueprint $table) {
            $table->id();

            // Integers only — the old profiles.stat_* columns were free text,
            // which is how "2.0 Current Projects" happened.
            $table->unsignedInteger('value')->default(0);
            $table->string('suffix', 10)->nullable();

            $table->string('label_singular');
            $table->string('label_plural');

            // 'manual' uses `value` as typed; the others recompute on render so
            // the number can never drift from the content.
            $table->string('auto_source')->default('manual');

            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stats');
    }
};
