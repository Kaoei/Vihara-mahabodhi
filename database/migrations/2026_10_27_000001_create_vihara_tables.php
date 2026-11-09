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
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('permissions');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
            $table->string('position')->nullable()->after('email');
            $table->string('phone')->nullable()->after('position');
            $table->boolean('is_active')->default(true)->after('phone');
        });

        Schema::create('receipts', function (Blueprint $table): void {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->string('receipt_type'); // Bantuan Dana, Barang, Surat / Dokumen
            $table->date('receipt_date');
            $table->string('donor_name');
            $table->string('donor_institution')->nullable();
            $table->string('donor_contact')->nullable();
            $table->string('item_name');
            $table->string('quantity')->default('1 Paket');
            $table->decimal('nominal', 15, 2)->nullable();
            $table->string('receiver_name');
            $table->text('thank_you_message')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->string('document_number')->unique();
            $table->string('category'); // Surat Masuk, Surat Keluar, Dokumen Umum A4, SK / Piagam
            $table->string('title');
            $table->date('document_date');
            $table->date('received_date')->nullable();
            $table->string('sender');
            $table->string('recipient');
            $table->string('paper_size')->default('A4');
            $table->string('archive_code')->nullable();
            $table->text('summary');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('schedules', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('category'); // Puja Bakti, Meditasi, Kelas Dharma, Perayaan Hari Besar, Kegiatan Sosial
            $table->string('day_of_week');
            $table->date('event_date')->nullable();
            $table->string('start_time');
            $table->string('end_time');
            $table->string('speaker')->nullable();
            $table->string('location');
            $table->text('description');
            $table->boolean('is_recurring')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('galleries', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->string('media_type')->default('photo'); // photo, video
            $table->string('media_url');
            $table->string('thumbnail_url')->nullable();
            $table->date('event_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category'); // Berita Kegiatan, Renungan Dharma, Pengumuman, Artikel Buddhis
            $table->string('author');
            $table->text('excerpt');
            $table->longText('content');
            $table->string('cover_image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('receipts');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['position', 'phone', 'is_active']);
        });

        Schema::dropIfExists('roles');
    }
};
