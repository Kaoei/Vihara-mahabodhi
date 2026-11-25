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
            $table->string('default_signer_title')->nullable()->after('about');
            $table->string('default_signer_name')->nullable()->after('default_signer_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vihara_profiles', function (Blueprint $table) {
            $table->dropColumn(['default_signer_title', 'default_signer_name']);
        });
    }
};
