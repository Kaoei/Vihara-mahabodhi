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
        Schema::table('marriage_certificates', function (Blueprint $table): void {
            $table->date('registration_date')->nullable()->change();
            $table->string('wedding_time')->nullable()->change();
            $table->string('wedding_location')->nullable()->change();
            $table->string('groom_birth_info')->nullable()->change();
            $table->string('groom_father_name')->nullable()->change();
            $table->string('groom_mother_name')->nullable()->change();
            $table->text('groom_address')->nullable()->change();
            $table->string('bride_birth_info')->nullable()->change();
            $table->string('bride_father_name')->nullable()->change();
            $table->string('bride_mother_name')->nullable()->change();
            $table->text('bride_address')->nullable()->change();
            $table->string('officiant_name')->nullable()->change();
            $table->string('witness_one')->nullable()->change();
            $table->string('witness_two')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marriage_certificates', function (Blueprint $table): void {
            $table->date('registration_date')->nullable(false)->change();
            $table->string('wedding_time')->nullable(false)->change();
            $table->string('wedding_location')->nullable(false)->change();
            $table->string('groom_birth_info')->nullable(false)->change();
            $table->string('groom_father_name')->nullable(false)->change();
            $table->string('groom_mother_name')->nullable(false)->change();
            $table->text('groom_address')->nullable(false)->change();
            $table->string('bride_birth_info')->nullable(false)->change();
            $table->string('bride_father_name')->nullable(false)->change();
            $table->string('bride_mother_name')->nullable(false)->change();
            $table->text('bride_address')->nullable(false)->change();
            $table->string('officiant_name')->nullable(false)->change();
            $table->string('witness_one')->nullable(false)->change();
            $table->string('witness_two')->nullable(false)->change();
        });
    }
};
