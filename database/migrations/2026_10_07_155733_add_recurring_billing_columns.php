<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Turn retainer billing from a dashboard button into something a cron can
     * run unattended.
     *
     * The period columns are what make that safe: a retainer invoice is keyed
     * to the period it covers, and the unique index means a second run — a
     * retried job, an overlapping schedule, a hand-click right after the cron
     * — collides instead of billing the client twice. `retainer_id` is
     * nullable, so manually created invoices stay out of that index entirely
     * (NULLs are distinct in a unique index on both MySQL and SQLite).
     *
     * `period_start` doubles as the date of supply, which a VAT invoice has
     * to state for a service billed over a period.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreignId('retainer_id')->nullable()->after('website_id')->constrained()->nullOnDelete();
            $table->date('period_start')->nullable()->after('retainer_id');
            $table->date('period_end')->nullable()->after('period_start');

            $table->unique(['retainer_id', 'period_start'], 'invoices_retainer_period_unique');
        });

        Schema::table('retainers', function (Blueprint $table): void {
            $table->boolean('auto_invoice')->default(false)->after('active')->index();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropUnique('invoices_retainer_period_unique');
            $table->dropConstrainedForeignId('retainer_id');
            $table->dropColumn(['period_start', 'period_end']);
        });

        Schema::table('retainers', function (Blueprint $table): void {
            $table->dropColumn('auto_invoice');
        });
    }
};
