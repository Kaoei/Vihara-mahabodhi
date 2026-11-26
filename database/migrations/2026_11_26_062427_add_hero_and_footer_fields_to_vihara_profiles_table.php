<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vihara_profiles', function (Blueprint $table) {
            $table->string('hero_image')->nullable()->after('about');
            $table->text('footer_description')->nullable()->after('default_signer_name');
            $table->string('footer_services_title')->nullable()->after('footer_description');
            $table->json('footer_services')->nullable()->after('footer_services_title');
            $table->string('footer_copyright')->nullable()->after('footer_services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vihara_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'hero_image',
                'footer_description',
                'footer_services_title',
                'footer_services',
                'footer_copyright',
            ]);
        });
    }
};
