<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->text('billing_address')->nullable();
            $table->longText('notes')->nullable();
            $table->string('status')->default('prospect')->index();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->char('currency', 3)->default('EUR');
            $table->date('onboarded_at')->nullable();
            $table->timestamps();
        });
    }
};
