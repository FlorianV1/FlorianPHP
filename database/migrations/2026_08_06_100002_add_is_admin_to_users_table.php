<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Gates panel access on an explicit flag rather than "is authenticated".
| The panel holds the client kill-switch, so a new/scoped account must not
| inherit it by default — only users flagged is_admin can reach the panel.
*/
return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_admin')->default(false)->after('email');
        });
    }
};
