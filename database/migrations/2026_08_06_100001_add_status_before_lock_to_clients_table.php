<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Remembers the client's status from just before an auto-lock so lifting the
| lock restores it, instead of forcing every unlocked client to Active and
| silently losing a Paused/Prospect/Archived state.
*/
return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('status_before_lock')->nullable()->after('status');
        });
    }
};
