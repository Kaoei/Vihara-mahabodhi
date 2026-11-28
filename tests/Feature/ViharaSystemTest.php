<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Document;
use App\Models\Gallery;
use App\Models\MarriageCertificate;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\User;
use App\Models\ViharaProfile;
use App\Services\ReceiptExcelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

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
        $response->assertSee('Wihara Mahabodhi Buddhist Centre');
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
     * Mandatory Feature 1: Multi-item Tanda Terima CRUD, Word (.docx) custom template upload, and web editor workflow.
     */
    public function test_receipt_multi_item_and_word_template_editor_workflow(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Create a Receipt with multiple goods/items
        $storeResponse = $this->actingAs($admin)->post(route('management.receipts.store'), [
            'receipt_type' => 'Barang',
            'receipt_date' => '2026-10-27',
            'donor_name' => 'Bpk. Dharmaputra Wijaya',
            'donor_institution' => 'Simpatisan Wihara Semarang',
            'donor_contact' => '08123456789',
            'items' => [
                ['name' => 'Beras Premium 25 Kg', 'quantity' => '4 Karung', 'nominal' => 1400000],
                ['name' => 'Minyak Goreng Kemasan', 'quantity' => '6 Dus', 'nominal' => 1100000],
            ],
            'receiver_name' => 'Upasika Metta Kusuma',
        ]);

        $storeResponse->assertRedirect(route('management.dashboard', ['tab' => 'receipts']));

        $receipt = Receipt::firstOrFail();
        $this->assertCount(2, $receipt->normalizedItems());
        $this->assertSame('Beras Premium 25 Kg', $receipt->normalizedItems()[0]['name']);
        $this->assertSame('Minyak Goreng Kemasan', $receipt->normalizedItems()[1]['name']);
        $this->assertEquals(2500000.0, (float) $receipt->nominal);

        // Separate Edit page for Receipt displays both items
        $this->actingAs($admin)
            ->get(route('management.receipts.edit', $receipt))
            ->assertStatus(200)
            ->assertSee($receipt->receipt_number)
            ->assertSee('Beras Premium 25 Kg')
            ->assertSee('Minyak Goreng Kemasan');

        // Confirm Excel export/import routes were removed from Receipts
        $this->assertFalse(Route::has('management.receipts.export'));
        $this->assertFalse(Route::has('management.receipts.import'));
        $this->assertFalse(Route::has('management.receipts.template'));

        // 2. Access dedicated Word Template Editor page
        $this->actingAs($admin)
            ->get(route('management.receipts.template.edit'))
            ->assertStatus(200)
            ->assertSee('Editor Template Surat Tanda Terima');

        // 3. Upload a valid .docx Word template and verify HTML conversion
        $docxTmp = tempnam(sys_get_temp_dir(), 'tpl_docx_');
        $zip = new ZipArchive;
        $zip->open($docxTmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
            .'<w:p><w:r><w:rPr><w:b/></w:rPr><w:t>SURAT TANDA TERIMA KHUSUS WIHARA</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Terima kasih kepada {{nama_donatur}} atas bantuan:</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>{{daftar_barang}}</w:t></w:r></w:p>'
            .'</w:body></w:document>';
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->close();

        $docxBinary = (string) file_get_contents($docxTmp);
        @unlink($docxTmp);

        $docxFile = UploadedFile::fake()->createWithContent('template_tanda_terima.docx', $docxBinary);

        $uploadResp = $this->actingAs($admin)->post(route('management.receipts.template.upload-docx'), [
            'target_template' => 'receipt',
            'docx_file' => $docxFile,
        ]);
        $uploadResp->assertRedirect(route('management.receipts.template.edit', ['editor' => 'receipt']));

        $profile = ViharaProfile::current()->fresh();
        $this->assertStringContainsString('SURAT TANDA TERIMA KHUSUS WIHARA', (string) $profile->receipt_template_html);
        $this->assertStringContainsString('{{nama_donatur}}', (string) $profile->receipt_template_html);
        $this->assertStringContainsString('{{daftar_barang}}', (string) $profile->receipt_template_html);

        // 4. Edit & save custom template directly in the web editor (with XSS sanitization)
        $saveResp = $this->actingAs($admin)->put(route('management.receipts.template.update'), [
            'receipt_template_html' => '<h3>Tanda Terima Diedit di Web</h3><p>Nomor: {{nomor}}</p><script>alert(1)</script>',
            'thank_you_template_html' => '<h3>Ucapan Anumodana Custom</h3><p>Kepada {{nama_donatur}}</p>',
        ]);
        $saveResp->assertRedirect(route('management.receipts.template.edit'));

        $profile = ViharaProfile::current()->fresh();
        $this->assertStringContainsString('Tanda Terima Diedit di Web', (string) $profile->receipt_template_html);
        $this->assertStringNotContainsString('<script>', (string) $profile->receipt_template_html);
        $this->assertStringContainsString('Ucapan Anumodana Custom', (string) $profile->thank_you_template_html);

        // 5. Reset template back to built-in default
        $resetResp = $this->actingAs($admin)->post(route('management.receipts.template.reset'), [
            'target_template' => 'all',
        ]);
        $resetResp->assertRedirect(route('management.receipts.template.edit'));

        $profile = ViharaProfile::current()->fresh();
        $this->assertNull($profile->receipt_template_html);
        $this->assertNull($profile->thank_you_template_html);
    }

    /**
     * Excel Export, Template Download, and Bulk Import for Administrasi Lain (Documents & Marriages).
     */
    public function test_documents_and_marriages_excel_export_and_import_workflow(): void
    {
        $admin = User::factory()->admin()->create();
        $excelService = app(ReceiptExcelService::class);

        // 1. Documents Export & Template Download
        $this->actingAs($admin)
            ->get(route('management.documents.export'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($admin)
            ->get(route('management.documents.template'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // 2. Documents Excel Import
        $docXlsx = $excelService->generateXlsxBinary([
            ['Nomor Surat / Dokumen', 'Kategori', 'Judul / Perihal Dokumen', 'Tanggal Surat', 'Tanggal Diterima', 'Pengirim', 'Penerima', 'Ukuran Kertas', 'Kode Arsip', 'Ringkasan'],
            ['201/SM/WMBC/XI/2026', 'Surat Edaran', 'Edaran Kegiatan Kathina Bersama', '2026-11-10', '2026-11-10', 'Sangha Theravada Indonesia', 'Pengurus Wihara Mahabodhi', 'A4', 'ARS-A4-201', 'Edaran resmi peringatan Kathina.'],
        ]);
        $docFile = UploadedFile::fake()->createWithContent('import_dokumen.xlsx', $docXlsx);

        $this->actingAs($admin)
            ->post(route('management.documents.import'), ['excel_file' => $docFile])
            ->assertRedirect(route('management.dashboard', ['tab' => 'documents']));

        $this->assertDatabaseHas('documents', [
            'document_number' => '201/SM/WMBC/XI/2026',
            'category' => 'Surat Edaran',
            'title' => 'Edaran Kegiatan Kathina Bersama',
        ]);

        // 3. Marriages Export & Template Download
        $this->actingAs($admin)
            ->get(route('management.marriages.export'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($admin)
            ->get(route('management.marriages.template'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // 4. Marriages Excel Import
        $marriageXlsx = $excelService->generateXlsxBinary([
            ['Nomor Arsip', 'Jenis Dokumen Nikah', 'Tanggal Pemberkatan', 'Nama Mempelai Pria', 'Nama Mempelai Wanita', 'Status Arsip', 'Catatan'],
            ['055/SPP-BDH/WMBC/XI/2026', 'Surat Pemberkatan Perkawinan', '2026-11-20', 'Budi Santoso', 'Lina Hartono', 'Selesai Pemberkatan', 'Import arsip pemberkatan'],
        ]);
        $marriageFile = UploadedFile::fake()->createWithContent('import_nikah.xlsx', $marriageXlsx);

        $this->actingAs($admin)
            ->post(route('management.marriages.import'), ['excel_file' => $marriageFile])
            ->assertRedirect(route('management.dashboard', ['tab' => 'marriages']));

        $this->assertDatabaseHas('marriage_certificates', [
            'certificate_number' => '055/SPP-BDH/WMBC/XI/2026',
            'groom_name' => 'Budi Santoso',
            'bride_name' => 'Lina Hartono',
            'status' => 'Selesai Pemberkatan',
        ]);
    }

    /**
     * Dynamic Category Management across all 6 management modules (form & inline AJAX).
     */
    public function test_dynamic_category_management_across_all_modules(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Add category via standard POST form
        $this->actingAs($admin)
            ->post(route('management.categories.store'), [
                'module' => 'receipts',
                'name' => 'Dana Kathina',
            ])
            ->assertRedirect(route('management.dashboard', ['tab' => 'receipts']));

        $this->assertDatabaseHas('categories', [
            'module' => 'receipts',
            'name' => 'Dana Kathina',
        ]);

        // 2. Add category via AJAX JSON (inline + button)
        $jsonResp = $this->actingAs($admin)->postJson(route('management.categories.store'), [
            'module' => 'articles',
            'name' => 'Warta Sangha',
        ]);
        $jsonResp->assertStatus(201)->assertJson([
            'module' => 'articles',
            'name' => 'Warta Sangha',
        ]);

        // 3. Create records using newly created custom categories
        $this->actingAs($admin)->post(route('management.schedules.store'), [
            'title' => 'Latihan Vipassana Khusus',
            'category' => 'Retret Meditasi',
            'day_of_week' => 'Sabtu',
            'start_time' => '06:00',
            'end_time' => '08:00',
            'location' => 'Ruang Meditasi',
            'description' => 'Sesi retret singkat akhir pekan.',
        ])->assertRedirect(route('management.dashboard', ['tab' => 'schedules']));

        $this->assertDatabaseHas('categories', [
            'module' => 'schedules',
            'name' => 'Retret Meditasi',
        ]);
        $this->assertDatabaseHas('schedules', [
            'title' => 'Latihan Vipassana Khusus',
            'category' => 'Retret Meditasi',
        ]);

        // 4. Delete a category
        $createdCat = Category::where('module', 'articles')->where('name', 'Warta Sangha')->firstOrFail();
        $this->actingAs($admin)
            ->delete(route('management.categories.destroy', $createdCat))
            ->assertRedirect(route('management.dashboard', ['tab' => 'articles']));

        $this->assertDatabaseMissing('categories', ['id' => $createdCat->id]);
    }

    /**
     * Mandatory Feature 2: Master Dokumen CRUD, separate Edit page, and multi-file attachments.
     */
    public function test_master_document_a4_crud_and_attachment(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $pdfFile = UploadedFile::fake()->create('surat_undangan_sangha.pdf', 120, 'application/pdf');
        $jpgFile = UploadedFile::fake()->create('lampiran_denah.jpg', 80, 'image/jpeg');

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
            'attachments' => [$pdfFile, $jpgFile],
        ]);

        $response->assertRedirect(route('management.dashboard', ['tab' => 'documents']));

        $doc = Document::where('document_number', '099/SM/WMBC/X/2026')->firstOrFail();
        $files = $doc->allAttachments();
        $this->assertCount(2, $files);
        Storage::disk('public')->assertExists($files[0]['path']);
        Storage::disk('public')->assertExists($files[1]['path']);

        // Separate Edit page for Document
        $this->actingAs($admin)
            ->get(route('management.documents.edit', $doc))
            ->assertStatus(200)
            ->assertSee('099/SM/WMBC/X/2026')
            ->assertSee('surat_undangan_sangha.pdf')
            ->assertSee('lampiran_denah.jpg');

        // Serve & download both uploaded document files by index
        $this->actingAs($admin)
            ->get(route('management.documents.file', ['document' => $doc, 'index' => 0]))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get(route('management.documents.file', ['document' => $doc, 'index' => 0, 'download' => 1]))
            ->assertStatus(200)
            ->assertDownload('surat_undangan_sangha.pdf');

        $this->actingAs($admin)
            ->get(route('management.documents.file', ['document' => $doc, 'index' => 1, 'download' => 1]))
            ->assertStatus(200)
            ->assertDownload('lampiran_denah.jpg');
    }

    /**
     * Buddhist Marriage Archive CRUD, separate Edit page, and multi-file viewing/downloading.
     */
    public function test_marriage_certificate_administration_crud_and_attachment(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $pdfFile = UploadedFile::fake()->create('berkas_nikah_kevin_clara.pdf', 150, 'application/pdf');
        $ktpFile = UploadedFile::fake()->create('scan_ktp_mempelai.jpg', 90, 'image/jpeg');

        $response = $this->actingAs($admin)->post(route('management.marriages.store'), [
            'certificate_number' => '010/SPP-BDH/WMBC/XI/2026',
            'document_type' => 'Surat Pemberkatan Perkawinan',
            'wedding_date' => '2026-11-15',
            'groom_name' => 'Kevin Wijaya Kusuma',
            'bride_name' => 'Clara Anastasia Tan',
            'status' => 'Selesai Pemberkatan',
            'notes' => 'Scan arsip surat pemberkatan perkawinan.',
            'attachments' => [$pdfFile, $ktpFile],
        ]);

        $response->assertRedirect(route('management.dashboard', ['tab' => 'marriages']));

        $marriage = MarriageCertificate::where('certificate_number', '010/SPP-BDH/WMBC/XI/2026')->firstOrFail();
        $files = $marriage->allAttachments();
        $this->assertCount(2, $files);
        Storage::disk('public')->assertExists($files[0]['path']);
        Storage::disk('public')->assertExists($files[1]['path']);

        // Separate Edit page for Marriage Archive
        $this->actingAs($admin)
            ->get(route('management.marriages.edit', $marriage))
            ->assertStatus(200)
            ->assertSee('010/SPP-BDH/WMBC/XI/2026')
            ->assertSee('Kevin Wijaya Kusuma')
            ->assertSee('berkas_nikah_kevin_clara.pdf')
            ->assertSee('scan_ktp_mempelai.jpg');

        // Serve & download both uploaded marriage archive files
        $this->actingAs($admin)
            ->get(route('management.marriages.file', ['marriage' => $marriage, 'index' => 0]))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get(route('management.marriages.file', ['marriage' => $marriage, 'index' => 0, 'download' => 1]))
            ->assertStatus(200)
            ->assertDownload('berkas_nikah_kevin_clara.pdf');

        $this->actingAs($admin)
            ->get(route('management.marriages.file', ['marriage' => $marriage, 'index' => 1, 'download' => 1]))
            ->assertStatus(200)
            ->assertDownload('scan_ktp_mempelai.jpg');

        // Update: add a 3rd file and remove the 1st file
        $extraFile = UploadedFile::fake()->create('akta_dukcapil.pdf', 110, 'application/pdf');
        $updateResp = $this->actingAs($admin)->put(route('management.marriages.update', $marriage), [
            'certificate_number' => '010/SPP-BDH/WMBC/XI/2026',
            'document_type' => 'Surat Pemberkatan Perkawinan',
            'wedding_date' => '2026-11-15',
            'groom_name' => 'Kevin Wijaya Kusuma',
            'bride_name' => 'Clara Anastasia Tan',
            'status' => 'Tercatat di Dukcapil',
            'notes' => 'Ditambah akta Dukcapil.',
            'remove_attachments' => [$files[0]['path']],
            'attachments' => [$extraFile],
        ]);
        $updateResp->assertRedirect(route('management.dashboard', ['tab' => 'marriages']));

        $updatedFiles = $marriage->fresh()->allAttachments();
        $this->assertCount(2, $updatedFiles);
        Storage::disk('public')->assertMissing($files[0]['path']);
        $this->assertSame('scan_ktp_mempelai.jpg', $updatedFiles[0]['name']);
        $this->assertSame('akta_dukcapil.pdf', $updatedFiles[1]['name']);
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
            'position' => 'Kepala Wihara',
        ]);

        $response = $this->actingAs($user)->get(route('management.profile.edit'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Akun &amp; Profil Pengurus', false);
        $response->assertSee('dharmavira@mahabodhi.or.id');

        $updateResponse = $this->actingAs($user)->put(route('management.profile.update'), [
            'name' => 'Bhiksu Dharmavira Mahathera',
            'email' => 'dharmavira.new@mahabodhi.or.id',
            'position' => 'Pimpinan Spiritual Wihara',
            'phone' => '081299887766',
        ]);

        $updateResponse->assertRedirect(route('management.dashboard', ['tab' => 'profile']));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Bhiksu Dharmavira Mahathera',
            'email' => 'dharmavira.new@mahabodhi.or.id',
            'position' => 'Pimpinan Spiritual Wihara',
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
        $dashboardResponse->assertSee('Kalender Jadwal Ibadah, Puja Bakti &amp; Kegiatan Wihara', false);
        $dashboardResponse->assertDontSee('Akses Cepat Pengurus Wihara');
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

    public function test_admin_can_edit_wihara_profile_and_manage_landing_info_cards(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $heroFile = UploadedFile::fake()->create('hero-wihara-baru.jpg', 300, 'image/jpeg');
        $logoFile = UploadedFile::fake()->create('logo-mahabodhi.png', 100, 'image/png');

        // 1. Update main Wihara profile, custom logo, hero image & footer content
        $updateResp = $this->actingAs($admin)->put(route('management.vihara-profile.update'), [
            'name' => 'Wihara Mahabodhi Buddhist Centre Semarang Baru',
            'short_name' => 'Wihara Mahabodhi Baru',
            'tagline' => 'Cahaya Dhamma & Cinta Kasih Universal',
            'about' => 'Deskripsi terbaru profil Wihara Mahabodhi yang diperbarui dari dashboard.',
            'address_short' => 'Jl. Seroja Timur No. 9-11 Semarang Tengah',
            'address' => 'Jl. Seroja Timur No. 9-11, Karangkidul, Semarang Tengah 50136',
            'phone' => '(024) 841-5509',
            'email' => 'info@mahabodhi.or.id',
            'operational_hours' => 'Setiap Hari: 06.30 – 21.00 WIB',
            'logo_file' => $logoFile,
            'hero_image_file' => $heroFile,
            'footer_description' => 'Pusat pembelajaran Dhamma dan pelayanan sosial umat Buddha di Kota Semarang.',
            'footer_services_title' => 'Layanan Umat & Sekretariat',
            'footer_services_text' => "Konsultasi Dharma\nPemberkatan Perkawinan\nBakti Sosial Rutin",
            'footer_copyright' => 'Semoga Semua Makhluk Berbahagia.',
        ]);

        $updateResp->assertRedirect(route('management.dashboard', ['tab' => 'vihara-profile']));
        $this->assertDatabaseHas('vihara_profiles', [
            'name' => 'Wihara Mahabodhi Buddhist Centre Semarang Baru',
            'operational_hours' => 'Setiap Hari: 06.30 – 21.00 WIB',
            'footer_description' => 'Pusat pembelajaran Dhamma dan pelayanan sosial umat Buddha di Kota Semarang.',
            'footer_services_title' => 'Layanan Umat & Sekretariat',
            'footer_copyright' => 'Semoga Semua Makhluk Berbahagia.',
        ]);

        $profileAfterUpdate = ViharaProfile::current()->fresh();
        $this->assertStringContainsString('/storage/hero/', (string) $profileAfterUpdate->hero_image);
        $this->assertStringContainsString('/storage/logos/', (string) $profileAfterUpdate->logo_url);

        $mgmtDashboard = $this->actingAs($admin)->get(route('management.dashboard', ['tab' => 'vihara-profile']));
        $mgmtDashboard->assertStatus(200);
        $mgmtDashboard->assertDontSee('Edit Penandatangan');
        $mgmtDashboard->assertSee('Logo Resmi Wihara');
        $mgmtDashboard->assertSee($profileAfterUpdate->logo_url, false);
        $mgmtDashboard->assertSee('Gambar Hero Utama');
        $mgmtDashboard->assertSee('Konten Footer Halaman Publik');

        // 2. Add a new facility / information card
        $createPage = $this->actingAs($admin)->get(route('management.vihara-profile.facilities.create'));
        $createPage->assertStatus(200);

        $storeResp = $this->actingAs($admin)->post(route('management.vihara-profile.facilities.store'), [
            'title' => 'Sekolah Minggu Buddhis',
            'description' => 'Pembinaan karakter dan ajaran Dhamma untuk anak-anak dan remaja setiap hari Minggu.',
            'icon' => 'fa-graduation-cap',
        ]);
        $storeResp->assertRedirect(route('management.dashboard', ['tab' => 'vihara-profile']));

        $profile = ViharaProfile::current()->fresh();
        $addedFacility = collect($profile->facilities)->firstWhere('title', 'Sekolah Minggu Buddhis');
        $this->assertNotNull($addedFacility);
        $facilityId = $addedFacility['id'];

        // 3. Edit the newly added facility / information card
        $editPage = $this->actingAs($admin)->get(route('management.vihara-profile.facilities.edit', $facilityId));
        $editPage->assertStatus(200);

        $editResp = $this->actingAs($admin)->put(route('management.vihara-profile.facilities.update', $facilityId), [
            'title' => 'Sekolah Minggu Buddhis Mahabodhi',
            'description' => 'Kelas Dhamma anak dan remaja setiap Minggu pagi pukul 09.00 WIB.',
            'icon' => 'fa-users',
        ]);
        $editResp->assertRedirect(route('management.dashboard', ['tab' => 'vihara-profile']));

        // 4. Verify updated profile, hero image, footer content, and new card appear on the public landing page
        $landingResp = $this->get(route('landing'));
        $landingResp->assertStatus(200);
        $landingResp->assertSee('Wihara Mahabodhi Buddhist Centre Semarang Baru');
        $landingResp->assertSee('Deskripsi terbaru profil Wihara Mahabodhi yang diperbarui dari dashboard.');
        $landingResp->assertSee('Setiap Hari: 06.30 – 21.00 WIB');
        $landingResp->assertSee('Sekolah Minggu Buddhis Mahabodhi');
        $landingResp->assertSee('Kelas Dhamma anak dan remaja setiap Minggu pagi pukul 09.00 WIB.');
        $landingResp->assertSee($profileAfterUpdate->hero_image, false);
        $landingResp->assertSee($profileAfterUpdate->logo_url, false);
        $landingResp->assertSee('Pusat pembelajaran Dhamma dan pelayanan sosial umat Buddha di Kota Semarang.');
        $landingResp->assertSee('Layanan Umat &amp; Sekretariat', false);
        $landingResp->assertSee('Bakti Sosial Rutin');
        $landingResp->assertSee('Semoga Semua Makhluk Berbahagia.');

        // 5. Delete the facility / information card
        $deleteResp = $this->actingAs($admin)->delete(route('management.vihara-profile.facilities.destroy', $facilityId));
        $deleteResp->assertRedirect(route('management.dashboard', ['tab' => 'vihara-profile']));

        $landingAfterDelete = $this->get(route('landing'));
        $landingAfterDelete->assertDontSee('Sekolah Minggu Buddhis Mahabodhi');
    }
}
