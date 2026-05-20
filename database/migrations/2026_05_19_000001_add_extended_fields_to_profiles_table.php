<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('location')->nullable()->after('email');
            $table->string('location_timezone')->nullable()->after('location');

            $table->string('hero_cta_primary_label')->default('View my work')->after('location_timezone');
            $table->string('hero_cta_primary_url')->default('#projects')->after('hero_cta_primary_label');
            $table->string('hero_cta_secondary_label')->default('Get in touch')->after('hero_cta_primary_url');
            $table->string('hero_cta_secondary_url')->default('#contact')->after('hero_cta_secondary_label');

            $table->text('contact_intro')->nullable()->after('hero_cta_secondary_url');

            $table->string('stat_1_value')->nullable()->after('contact_intro');
            $table->string('stat_1_label')->nullable()->after('stat_1_value');
            $table->string('stat_2_value')->nullable()->after('stat_1_label');
            $table->string('stat_2_label')->nullable()->after('stat_2_value');
            $table->string('stat_3_value')->nullable()->after('stat_2_label');
            $table->string('stat_3_label')->nullable()->after('stat_3_value');

            $table->string('about_stack_primary')->nullable()->after('stat_3_label');
            $table->string('about_stack_secondary')->nullable()->after('about_stack_primary');

            $table->string('footer_tagline')->nullable()->after('about_stack_secondary');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'location', 'location_timezone',
                'hero_cta_primary_label', 'hero_cta_primary_url',
                'hero_cta_secondary_label', 'hero_cta_secondary_url',
                'contact_intro',
                'stat_1_value', 'stat_1_label',
                'stat_2_value', 'stat_2_label',
                'stat_3_value', 'stat_3_label',
                'about_stack_primary', 'about_stack_secondary',
                'footer_tagline',
            ]);
        });
    }
};
