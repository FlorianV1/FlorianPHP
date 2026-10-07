<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Freeze the sender identity onto each invoice.
     *
     * The PDF used to read `config('app.name')` and the live settings, which
     * means the day a KvK number or a VAT id is filled in, every invoice ever
     * sent would silently reprint with details it did not carry when it went
     * out. Dutch bookkeeping keeps these for seven years, so the document has
     * to stay the document.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->json('issuer')->nullable()->after('website_id');
            $table->string('vat_regime')->default('not_registered')->after('vat_rate');
            $table->text('vat_note')->nullable()->after('vat_regime');
        });

        // Rows that predate the column were all created with the hardcoded
        // 21%, so they are standard-regime invoices. Left on the column
        // default they would reprint as a nota, which is the opposite of what
        // they said when they went out. `issuer` stays null for them and the
        // PDF falls back to today's settings — the most that can be said
        // about a document that never recorded one.
        DB::table('invoices')->where('vat_rate', '>', 0)->update(['vat_regime' => 'standard']);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn(['issuer', 'vat_regime', 'vat_note']);
        });
    }
};
