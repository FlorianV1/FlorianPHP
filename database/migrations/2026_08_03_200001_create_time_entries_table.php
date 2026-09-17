<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('website_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_line_id')->nullable()->constrained()->nullOnDelete();
            $table->date('work_date')->index();
            $table->decimal('hours', 5, 2);
            $table->string('description');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->timestamps();

            $table->index(['client_id', 'invoice_line_id']);
        });
    }
};
