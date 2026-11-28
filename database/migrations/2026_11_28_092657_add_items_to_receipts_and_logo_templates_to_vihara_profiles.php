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
        Schema::table('receipts', function (Blueprint $table): void {
            $table->json('items')->nullable()->after('quantity');
        });

        Schema::table('vihara_profiles', function (Blueprint $table): void {
            $table->string('logo_url')->nullable()->after('hero_image');
            $table->longText('receipt_template_html')->nullable()->after('footer_copyright');
            $table->longText('thank_you_template_html')->nullable()->after('receipt_template_html');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table): void {
            $table->dropColumn('items');
        });

        Schema::table('vihara_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'logo_url',
                'receipt_template_html',
                'thank_you_template_html',
            ]);
        });
    }
};
