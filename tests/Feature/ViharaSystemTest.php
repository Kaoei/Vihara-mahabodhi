<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Document;
use App\Models\Gallery;
use App\Models\MarriageCertificate;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\User;
use App\Services\ReceiptExcelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ViharaSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Public landing page displays Wihara Mahabodhi profile, hero background, and separate article detail page.
     */
    public function test_public_landing_page_and_separate_article_page_work_properly(): void
    {
        Schedule::factory()->create([
            'title' => 'Puja Bakti Minggu Pagi Mahabodhi',
            'category' => 'Puja Bakti',
            'is_active' => true,
        ]);

        Gallery::factory()->create([
            'title' => 'Dokumentasi Penyalaan Pelita Dharma',
            'media_type' => 'photo',
        ]);

        $article = Article::factory()->create([
            'title' => 'Renungan Metta Bhavana di Semarang',
            'slug' => 'renungan-metta-bhavana-di-semarang',
            'category' => 'Renungan Dharma',
            'content' => 'Isi lengkap renungan Dharma untuk kebahagiaan semua makhluk.',
            'is_published' => true,
        ]);

        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('Vihara Mahabodhi Buddhist Centre');
        $response->assertSee('Jl. Seroja Timur No. 9-11 Semarang');
        $response->assertSee('Puja Bakti Minggu Pagi Mahabodhi');
        $response->assertSee('Dokumentasi Penyalaan Pelita Dharma');
        $response->assertSee(route('landing.article', $article->slug));

        // Verify clicking the article goes to a separate dedicated page
        $articlePageResponse = $this->get(route('landing.article', $article->slug));
        $articlePageResponse->assertStatus(200);
        $articlePageResponse->assertSee('Renungan Metta Bhavana di Semarang');
        $articlePageResponse->assertSee('Isi lengkap renungan Dharma untuk kebahagiaan semua makhluk.');
    }

    /**
     * Unauthenticated visitors cannot access internal management routes.
     */
    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get(route('management.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Admin can log in and access dashboard as well as separate create and edit pages.
     */
    public function test_admin_can_login_and_access_separate_create_and_edit_pages(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@mahabodhi.or.id',
        ]);

        $loginResponse = $this->post(route('login.attempt'), [
            'email' => 'admin@mahabodhi.or.id',
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect(route('management.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $dashboardResponse = $this->actingAs($admin)->get(route('management.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Namo Buddhaya, selamat datang kembali');
        $dashboardResponse->assertSee('Ringkasan Operasional Sekretariat');
        $dashboardResponse->assertSee('Tanda Terima Bantuan / Barang / Surat');
        $dashboardResponse->assertSee('Arsip Persuratan &amp; Dokumen Resmi Sekretariat', false);
        $dashboardResponse->assertSee('Surat Nikah &amp; Pemberkatan', false);
        $dashboardResponse->assertSee('Manajemen Role Pengurus');
        $dashboardResponse->assertSee('[logo]');

        // Separate Create pages do NOT show "Namo Buddhaya, selamat datang kembali"
        $this->actingAs($admin)->get(route('management.receipts.create'))
            ->assertStatus(200)
            ->assertDontSee('Namo Buddhaya, selamat datang kembali');
        $this->actingAs($admin)->get(route('management.documents.create'))
            ->assertStatus(200)
            ->assertDontSee('Namo Buddhaya, selamat datang kembali');
        $this->actingAs($admin)->get(route('management.marriages.create'))
            ->assertStatus(200)
            ->assertDontSee('Namo Buddhaya, selamat datang kembali');
        $this->actingAs($admin)->get(route('management.schedules.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('management.galleries.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('management.articles.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('management.roles.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('management.users.create'))->assertStatus(200);
    }

    /**
     * Mandatory Feature 1: Create, Edit on separate page, Print, Export, and Import Tanda Terima.
     */
    public function test_receipt_creation_separate_edit_and_excel_workflow(): void
    {
        $admin = User::factory()->admin()->create();

        $storeResponse = $this->actingAs($admin)->post(route('management.receipts.store'), [
            'receipt_type' => 'Bantuan Dana',
            'receipt_date' => '2026-10-27',
            'donor_name' => 'Bpk. Dharmaputra Wijaya',
            'donor_institution' => 'Simpatisan Vihara Semarang',
            'donor_contact' => '08123456789',
            'item_name' => 'Dana Operasional Puja Bakti & Altar',
            'quantity' => '1 Paket Dana',
            'nominal' => 2500000,
            'receiver_name' => 'Upasika Metta Kusuma',
        ]);

        $storeResponse->assertRedirect(route('management.dashboard', ['tab' => 'receipts']));

        $receipt = Receipt::firstOrFail();

        // Separate Edit page for Receipt
        $this->actingAs($admin)
            ->get(route('management.receipts.edit', $receipt))
            ->assertStatus(200)
            ->assertSee($receipt->receipt_number);

        // Verify Excel export returns valid OpenXML binary
        $exportResponse = $this->actingAs($admin)->get(route('management.receipts.export'));
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Verify Excel import from generated .xlsx template
        $excelService = app(ReceiptExcelService::class);
        $xlsxBinary = $excelService->generateXlsxBinary([
            ['No Tanda Terima', 'Jenis', 'Tanggal', 'Nama Donatur', 'Instansi', 'Kontak', 'Uraian', 'Jumlah', 'Nominal', 'Penerima', 'Ucapan', 'Catatan'],
            ['', 'Barang', '2026-10-27', 'Ibu Karuna Devi', 'Umat Semarang', '0811223344', 'Beras 50 Kg untuk Bakti Sosial', '5 Karung', '750000', 'Sekretariat', 'Anumodana atas bantuan beras.', 'Baik'],
        ]);

        $uploadedFile = UploadedFile::fake()->createWithContent('import_tanda_terima.xlsx', $xlsxBinary);

        $importResponse = $this->actingAs($admin)->post(route('management.receipts.import'), [
            'excel_file' => $uploadedFile,
        ]);

        $importResponse->assertRedirect(route('management.dashboard', ['tab' => 'receipts']));
        $this->assertDatabaseHas('receipts', [
            'donor_name' => 'Ibu Karuna Devi',
            'receipt_type' => 'Barang',
            'item_name' => 'Beras 50 Kg untuk Bakti Sosial',
        ]);
    }

    /**
     * Mandatory Feature 2: Master Dokumen A4 CRUD, separate Edit page, and attachment.
     */
    public function test_master_document_a4_crud_and_attachment(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $pdfFile = UploadedFile::fake()->create('surat_undangan_sangha.pdf', 120, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('management.documents.store'), [
            'document_number' => '099/SM/WMBC/X/2026',
            'category' => 'Surat Masuk',
            'title' => 'Undangan Perayaan Waisak Bersama Kota Semarang',
            'document_date' => '2026-10-27',
            'received_date' => '2026-10-27',
            'sender' => 'Majelis Agama Buddha Semarang',
            'recipient' => 'Pengurus Wihara Mahabodhi Buddhist Centre',
            'paper_size' => 'A4',
            'archive_code' => 'ARS-A4-SM-09',
            'summary' => 'Surat undangan resmi menghadiri koordinasi perayaan Waisak format A4.',
            'attachment' => $pdfFile,
        ]);

        $response->assertRedirect(route('management.dashboard', ['tab' => 'documents']));

        $doc = Document::where('document_number', '099/SM/WMBC/X/2026')->firstOrFail();
        $this->assertNotNull($doc->attachment_path);
        Storage::disk('public')->assertExists($doc->attachment_path);

        // Separate Edit page for Document
        $this->actingAs($admin)
            ->get(route('management.documents.edit', $doc))
            ->assertStatus(200)
            ->assertSee('099/SM/WMBC/X/2026');
    }

    /**
     * Buddhist Marriage Certificate / Administration CRUD, separate Edit page, and attachment.
     */
    public function test_marriage_certificate_administration_crud_and_attachment(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $pdfFile = UploadedFile::fake()->create('berkas_nikah_kevin_clara.pdf', 150, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('management.marriages.store'), [
            'certificate_number' => '010/SPP-BDH/WMBC/XI/2026',
            'document_type' => 'Surat Pemberkatan Perkawinan',
            'registration_date' => '2026-11-01',
            'wedding_date' => '2026-11-15',
            'wedding_time' => '09:00 - 11:00 WIB',
            'wedding_location' => 'Dharmasala Utama Wihara Mahabodhi Buddhist Centre Semarang',
            'groom_name' => 'Kevin Wijaya Kusuma',
            'groom_buddhist_name' => 'Upasaka Dhammapala',
            'groom_nik' => '3374011405960001',
            'groom_birth_info' => 'Semarang, 14 Mei 1996',
            'groom_father_name' => 'Hendra Wijaya',
            'groom_mother_name' => 'Lina Susanti',
            'groom_address' => 'Jl. Gajahmada No. 88, Semarang',
            'bride_name' => 'Clara Anastasia Tan',
            'bride_buddhist_name' => 'Upasika Metta Devi',
            'bride_nik' => '3374022208980002',
            'bride_birth_info' => 'Semarang, 22 Agustus 1998',
            'bride_father_name' => 'Budianto Tan',
            'bride_mother_name' => 'Siska Halim',
            'bride_address' => 'Jl. Pandanaran No. 45, Semarang',
            'officiant_name' => 'Romo Pandita Dharmaputra, S.Ag., M.Pd.B.',
            'witness_one' => 'Drs. Suryanto Gunawan',
            'witness_two' => 'Ir. Hartono Salim',
            'status' => 'Selesai Pemberkatan',
            'premarital_course_done' => '1',
            'notes' => 'Berkas persyaratan lengkap.',
            'attachment' => $pdfFile,
        ]);

        $response->assertRedirect(route('management.dashboard', ['tab' => 'marriages']));

        $marriage = MarriageCertificate::where('certificate_number', '010/SPP-BDH/WMBC/XI/2026')->firstOrFail();
        $this->assertNotNull($marriage->attachment_path);
        Storage::disk('public')->assertExists($marriage->attachment_path);

        // Separate Edit page for Marriage Certificate
        $this->actingAs($admin)
            ->get(route('management.marriages.edit', $marriage))
            ->assertStatus(200)
            ->assertSee('010/SPP-BDH/WMBC/XI/2026')
            ->assertSee('Kevin Wijaya Kusuma');
    }

    /**
     * Mandatory Feature 3: Role Management and Role-Based Access Control enforcement.
     */
    public function test_role_management_and_permission_enforcement(): void
    {
        $admin = User::factory()->admin()->create();

        $roleResponse = $this->actingAs($admin)->post(route('management.roles.store'), [
            'name' => 'Sekretaris Arsip A4',
            'description' => 'Hanya mengelola dokumen A4 dan tanda terima',
            'permissions' => ['manage_receipts', 'manage_documents'],
        ]);

        $roleResponse->assertRedirect(route('management.dashboard', ['tab' => 'roles']));
        $customRole = Role::where('slug', 'sekretaris-arsip-a4')->firstOrFail();

        $staffUser = User::factory()->create([
            'role_id' => $customRole->id,
            'is_active' => true,
        ]);

        $this->actingAs($staffUser)->post(route('management.receipts.store'), [
            'receipt_type' => 'Surat / Dokumen',
            'receipt_date' => '2026-10-27',
            'donor_name' => 'Kelurahan Karangkidul',
            'item_name' => 'Surat Pemberitahuan Kegiatan Lingkungan',
            'quantity' => '1 Berkas A4',
            'receiver_name' => $staffUser->name,
        ])->assertRedirect(route('management.dashboard', ['tab' => 'receipts']));

        $this->actingAs($staffUser)->post(route('management.roles.store'), [
            'name' => 'Unauthorized Role',
            'permissions' => ['manage_roles'],
        ])->assertStatus(403);
    }

    /**
     * Authenticated user can view profile configuration and update account details.
     */
    public function test_authenticated_user_can_view_and_update_profile_configuration(): void
    {
        $user = User::factory()->create([
            'name' => 'Bhiksu Dharmavira',
            'email' => 'dharmavira@mahabodhi.or.id',
            'position' => 'Kepala Vihara',
        ]);

        $response = $this->actingAs($user)->get(route('management.profile.edit'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Akun &amp; Profil Pengurus', false);
        $response->assertSee('dharmavira@mahabodhi.or.id');

        $updateResponse = $this->actingAs($user)->put(route('management.profile.update'), [
            'name' => 'Bhiksu Dharmavira Mahathera',
            'email' => 'dharmavira.new@mahabodhi.or.id',
            'position' => 'Pimpinan Spiritual Vihara',
            'phone' => '081299887766',
        ]);

        $updateResponse->assertRedirect(route('management.dashboard', ['tab' => 'profile']));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Bhiksu Dharmavira Mahathera',
            'email' => 'dharmavira.new@mahabodhi.or.id',
            'position' => 'Pimpinan Spiritual Vihara',
            'phone' => '081299887766',
        ]);
    }

    /**
     * Gallery supports photo file upload and requires YouTube URL for video documentation.
     */
    public function test_gallery_photo_file_upload_and_youtube_video_validation(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        // 1. Overview dashboard shows Calendar and does not show Total Nominal or Akses Cepat
        $dashboardResponse = $this->actingAs($admin)->get(route('management.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Kalender Jadwal Ibadah, Puja Bakti &amp; Kegiatan Vihara', false);
        $dashboardResponse->assertDontSee('Akses Cepat Pengurus Vihara');
        $dashboardResponse->assertDontSee('Total Nominal:');

        // 2. Photo Upload via file
        $photoFile = UploadedFile::fake()->create('puja-bakti-utama.jpg', 250, 'image/jpeg');
        $photoResp = $this->actingAs($admin)->post(route('management.galleries.store'), [
            'title' => 'Foto Puja Bakti Minggu Pagi',
            'category' => 'Puja Bakti',
            'media_type' => 'photo',
            'photo_file' => $photoFile,
            'event_date' => '2026-11-08',
            'description' => 'Dokumentasi puja bakti di Dharmasala Utama.',
        ]);
        $photoResp->assertRedirect(route('management.dashboard', ['tab' => 'galleries']));
        $this->assertDatabaseHas('galleries', [
            'title' => 'Foto Puja Bakti Minggu Pagi',
            'media_type' => 'photo',
        ]);

        // 3. Video requires valid YouTube URL and automatically generates embed & thumbnail URL
        $videoResp = $this->actingAs($admin)->post(route('management.galleries.store'), [
            'title' => 'Rekaman Ceramah Dhamma Waisak',
            'category' => 'Dhamma Talk',
            'media_type' => 'video',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'event_date' => '2026-11-09',
            'description' => 'Video dokumentasi ceramah Dhamma.',
        ]);
        $videoResp->assertRedirect(route('management.dashboard', ['tab' => 'galleries']));
        $this->assertDatabaseHas('galleries', [
            'title' => 'Rekaman Ceramah Dhamma Waisak',
            'media_type' => 'video',
            'media_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'thumbnail_url' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
        ]);
    }
}
