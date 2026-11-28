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
        Schema::table('documents', function (Blueprint $table): void {
            $table->json('attachments')->nullable()->after('attachment_name');
        });

        Schema::table('marriage_certificates', function (Blueprint $table): void {
            $table->json('attachments')->nullable()->after('attachment_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table): void {
            $table->dropColumn('attachments');
        });

        Schema::table('marriage_certificates', function (Blueprint $table): void {
            $table->dropColumn('attachments');
        });
    }
};
