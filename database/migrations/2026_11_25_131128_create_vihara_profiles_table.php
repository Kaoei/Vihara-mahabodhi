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
        Schema::create('vihara_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name');
            $table->text('address');
            $table->string('address_short');
            $table->string('phone');
            $table->string('email');
            $table->string('operational_hours');
            $table->string('tagline');
            $table->text('about');
            $table->json('facilities')->nullable();
            $table->json('dhammapada_quotes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vihara_profiles');
    }
};
