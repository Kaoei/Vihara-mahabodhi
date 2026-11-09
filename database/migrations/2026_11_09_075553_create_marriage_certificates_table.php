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
        Schema::create('marriage_certificates', function (Blueprint $table): void {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->string('document_type')->default('Surat Pemberkatan Perkawinan');
            $table->date('registration_date');
            $table->date('wedding_date');
            $table->string('wedding_time')->default('09:00 - 11:00 WIB');
            $table->string('wedding_location')->default('Dharmasala Utama Wihara Mahabodhi Buddhist Centre Semarang');

            // Groom Details
            $table->string('groom_name');
            $table->string('groom_buddhist_name')->nullable();
            $table->string('groom_nik')->nullable();
            $table->string('groom_birth_info');
            $table->string('groom_father_name');
            $table->string('groom_mother_name');
            $table->text('groom_address');

            // Bride Details
            $table->string('bride_name');
            $table->string('bride_buddhist_name')->nullable();
            $table->string('bride_nik')->nullable();
            $table->string('bride_birth_info');
            $table->string('bride_father_name');
            $table->string('bride_mother_name');
            $table->text('bride_address');

            // Officiant & Witnesses
            $table->string('officiant_name');
            $table->string('witness_one');
            $table->string('witness_two');

            // Administrative Status & Attachments
            $table->string('status')->default('Selesai Pemberkatan');
            $table->boolean('premarital_course_done')->default(true);
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marriage_certificates');
    }
};
