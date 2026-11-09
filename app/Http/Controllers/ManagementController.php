<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Document;
use App\Models\Gallery;
use App\Models\MarriageCertificate;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\User;
use App\Services\ReceiptExcelService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ManagementController extends Controller
{
    public function __construct(public ReceiptExcelService $excelService) {}

    /**
     * Build the base shared data for the management layout.
     *
     * @param  array<string, mixed>  $overrides
     */
    private function renderManagementView(array $overrides = []): View
    {
        $receipts = Receipt::query()
            ->with('creator')
            ->orderByDesc('receipt_date')
            ->orderByDesc('id')
            ->get();

        $documents = Document::query()
            ->with('creator')
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->get();

        $marriages = MarriageCertificate::query()
            ->with('creator')
            ->orderByDesc('wedding_date')
            ->orderByDesc('id')
            ->get();

        $schedules = Schedule::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $galleries = Gallery::query()
            ->orderByDesc('is_featured')
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->get();

        $articles = Article::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        $roles = Role::query()
            ->withCount('users')
            ->orderBy('id')
            ->get();

        $users = User::query()
            ->with('role')
            ->orderBy('id')
            ->get();

        $stats = [
            'total_receipts' => $receipts->count(),
            'total_nominal' => (float) $receipts->sum('nominal'),
            'total_documents' => $documents->count(),
            'surat_masuk_count' => $documents->where('category', 'Surat Masuk')->count(),
            'surat_keluar_count' => $documents->where('category', 'Surat Keluar')->count(),
            'total_marriages' => $marriages->count(),
            'active_schedules' => $schedules->where('is_active', true)->count(),
            'total_galleries' => $galleries->count(),
            'published_articles' => $articles->where('is_published', true)->count(),
            'total_users' => $users->count(),
            'total_roles' => $roles->count(),
        ];

        return view('management', array_merge([
            'isLoginPage' => false,
            'pageMode' => 'index',
            'activeTab' => 'overview',
            'editingRecord' => null,
            'printType' => null,
            'printRecord' => null,
            'stats' => $stats,
            'receipts' => $receipts,
            'nextReceiptNumber' => Receipt::generateNextNumber(),
            'documents' => $documents,
            'marriages' => $marriages,
            'nextMarriageNumber' => MarriageCertificate::generateNextNumber(),
            'schedules' => $schedules,
            'galleries' => $galleries,
            'articles' => $articles,
            'roles' => $roles,
            'users' => $users,
            'availablePermissions' => Role::availablePermissions(),
        ], $overrides));
    }

    /**
     * Show the internal management login screen inside management.blade.php.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('management.dashboard');
        }

        return view('management', [
            'isLoginPage' => true,
            'activeTab' => 'login',
        ]);
    }

    /**
     * Authenticate an internal user into the management system.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik."]);
        }

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau kata sandi yang Anda masukkan tidak sesuai.']);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'Akun pengurus Anda sedang tidak aktif. Hubungi Administrator.']);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('management.dashboard'));
    }

    /**
     * Log the user out of the internal management system.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem manajemen.');
    }

    /**
     * Display the internal management dashboard and list modules.
     */
    public function dashboard(Request $request): View
    {
        $activeTab = (string) $request->query('tab', 'overview');
        $printType = $request->query('print_type');
        $printId = $request->query('print_id');

        $printRecord = null;
        if (in_array($printType, ['receipt', 'thank_you'], true) && is_numeric($printId)) {
            $printRecord = Receipt::find((int) $printId);
        } elseif ($printType === 'document' && is_numeric($printId)) {
            $printRecord = Document::find((int) $printId);
        } elseif ($printType === 'marriage' && is_numeric($printId)) {
            $printRecord = MarriageCertificate::find((int) $printId);
        }

        return $this->renderManagementView([
            'pageMode' => 'index',
            'activeTab' => $activeTab,
            'printType' => $printType,
            'printRecord' => $printRecord,
        ]);
    }

    /**
     * Dedicated Create Page for Tanda Terima.
     */
    public function createReceipt(): View
    {
        return $this->renderManagementView([
            'pageMode' => 'create_receipt',
            'activeTab' => 'receipts',
        ]);
    }

    /**
     * Dedicated Edit Page for Tanda Terima.
     */
    public function editReceipt(Receipt $receipt): View
    {
        return $this->renderManagementView([
            'pageMode' => 'edit_receipt',
            'activeTab' => 'receipts',
            'editingRecord' => $receipt,
        ]);
    }

    /**
     * Store a newly created Tanda Terima & Ucapan Terima Kasih.
     */
    public function storeReceipt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receipt_number' => ['nullable', 'string', 'max:100', 'unique:receipts,receipt_number'],
            'receipt_type' => ['required', 'string', Rule::in(['Bantuan Dana', 'Barang', 'Surat / Dokumen'])],
            'receipt_date' => ['required', 'date'],
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_institution' => ['nullable', 'string', 'max:255'],
            'donor_contact' => ['nullable', 'string', 'max:100'],
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'string', 'max:100'],
            'nominal' => ['nullable', 'numeric', 'min:0'],
            'receiver_name' => ['required', 'string', 'max:255'],
            'thank_you_message' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if (empty($validated['receipt_number'])) {
            $validated['receipt_number'] = Receipt::generateNextNumber();
        }

        if (empty($validated['thank_you_message'])) {
            $validated['thank_you_message'] = sprintf(
                'Sabbadanam Dhammadanam Jinati. Pengurus Wihara Mahabodhi Buddhist Centre Semarang mengucapkan Anumodana dan terima kasih yang tulus kepada %s atas penyerahan %s (%s). Semoga timbunan kebajikan ini membawa berkah kesehatan, kebahagiaan, dan kedamaian bagi Bapak/Ibu beserta keluarga.',
                $validated['donor_name'],
                strtolower($validated['receipt_type']),
                $validated['item_name']
            );
        }

        $validated['created_by'] = $request->user()?->id;

        $receipt = Receipt::create($validated);

        return redirect()
            ->route('management.dashboard', ['tab' => 'receipts'])
            ->with('success', "Tanda Terima {$receipt->receipt_number} untuk {$receipt->donor_name} berhasil diterbitkan.");
    }

    /**
     * Update an existing Tanda Terima record.
     */
    public function updateReceipt(Request $request, Receipt $receipt): RedirectResponse
    {
        $validated = $request->validate([
            'receipt_number' => ['required', 'string', 'max:100', Rule::unique('receipts', 'receipt_number')->ignore($receipt->id)],
            'receipt_type' => ['required', 'string', Rule::in(['Bantuan Dana', 'Barang', 'Surat / Dokumen'])],
            'receipt_date' => ['required', 'date'],
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_institution' => ['nullable', 'string', 'max:255'],
            'donor_contact' => ['nullable', 'string', 'max:100'],
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'string', 'max:100'],
            'nominal' => ['nullable', 'numeric', 'min:0'],
            'receiver_name' => ['required', 'string', 'max:255'],
            'thank_you_message' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $receipt->update($validated);

        return redirect()
            ->route('management.dashboard', ['tab' => 'receipts'])
            ->with('success', "Data Tanda Terima {$receipt->receipt_number} berhasil diperbarui.");
    }

    /**
     * Delete a Tanda Terima record.
     */
    public function destroyReceipt(Receipt $receipt): RedirectResponse
    {
        $number = $receipt->receipt_number;
        $receipt->delete();

        return redirect()
            ->route('management.dashboard', ['tab' => 'receipts'])
            ->with('success', "Tanda Terima {$number} telah dihapus.");
    }

    /**
     * Import Tanda Terima records from an uploaded Excel (.xlsx) or CSV (.csv) file.
     */
    public function importReceiptsExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'max:5120'],
        ]);

        $file = $request->file('excel_file');
        if ($file === null) {
            return redirect()
                ->route('management.dashboard', ['tab' => 'receipts'])
                ->withErrors(['excel_file' => 'Berkas Excel tidak ditemukan.']);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx', 'xls', 'csv', 'txt'], true)) {
            return redirect()
                ->route('management.dashboard', ['tab' => 'receipts'])
                ->withErrors(['excel_file' => 'Format file harus berupa .xlsx, .csv, atau .xls.']);
        }

        $defaultReceiver = $request->user()?->name ?? 'Sekretariat Wihara Mahabodhi';
        $rows = $this->excelService->parseUploadedFile($file, $defaultReceiver);

        if ($rows === []) {
            return redirect()
                ->route('management.dashboard', ['tab' => 'receipts'])
                ->withErrors(['excel_file' => 'Tidak ada baris data valid yang dapat diimpor dari file tersebut.']);
        }

        $importedCount = 0;
        foreach ($rows as $rowData) {
            $rowData['created_by'] = $request->user()?->id;
            Receipt::create($rowData);
            $importedCount++;
        }

        return redirect()
            ->route('management.dashboard', ['tab' => 'receipts'])
            ->with('success', "Berhasil mengimpor {$importedCount} data Tanda Terima dari file Excel/CSV.");
    }

    /**
     * Export all Tanda Terima records as an OpenXML .xlsx file.
     */
    public function exportReceiptsExcel(): Response
    {
        $receipts = Receipt::query()
            ->orderByDesc('receipt_date')
            ->orderByDesc('id')
            ->get();

        $rows = [
            [
                'No Tanda Terima',
                'Jenis',
                'Tanggal',
                'Nama Penyerah / Donatur',
                'Instansi / Alamat',
                'Kontak',
                'Uraian Bantuan / Barang / Surat',
                'Jumlah / Kuantitas',
                'Nominal (Rp)',
                'Petugas Penerima',
                'Ucapan Terima Kasih',
                'Catatan',
            ],
        ];

        foreach ($receipts as $receipt) {
            $rows[] = [
                $receipt->receipt_number,
                $receipt->receipt_type,
                $receipt->receipt_date->format('Y-m-d'),
                $receipt->donor_name,
                $receipt->donor_institution ?? '',
                $receipt->donor_contact ?? '',
                $receipt->item_name,
                $receipt->quantity,
                $receipt->nominal !== null ? (float) $receipt->nominal : '',
                $receipt->receiver_name,
                $receipt->thank_you_message ?? '',
                $receipt->notes ?? '',
            ];
        }

        $binary = $this->excelService->generateXlsxBinary($rows, 'Data Tanda Terima');
        $filename = 'Tanda_Terima_Wihara_Mahabodhi_'.now()->format('Ymd_His').'.xlsx';

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Download the blank/example Excel template for Tanda Terima import.
     */
    public function downloadReceiptTemplate(): Response
    {
        $rows = $this->excelService->getTemplateRows();
        $binary = $this->excelService->generateXlsxBinary($rows, 'Template Import');
        $filename = 'Template_Import_Tanda_Terima_WMBC.xlsx';

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Dedicated Create Page for Master Dokumen A4.
     */
    public function createDocument(): View
    {
        return $this->renderManagementView([
            'pageMode' => 'create_document',
            'activeTab' => 'documents',
        ]);
    }

    /**
     * Dedicated Edit Page for Master Dokumen A4.
     */
    public function editDocument(Document $document): View
    {
        return $this->renderManagementView([
            'pageMode' => 'edit_document',
            'activeTab' => 'documents',
            'editingRecord' => $document,
        ]);
    }

    /**
     * Store a new Master Dokumen (Surat Masuk / Surat Keluar / Dokumen A4).
     */
    public function storeDocument(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'document_number' => ['required', 'string', 'max:100', 'unique:documents,document_number'],
            'category' => ['required', 'string', Rule::in(['Surat Masuk', 'Surat Keluar', 'Dokumen Umum A4', 'SK / Piagam'])],
            'title' => ['required', 'string', 'max:255'],
            'document_date' => ['required', 'date'],
            'received_date' => ['nullable', 'date'],
            'sender' => ['required', 'string', 'max:255'],
            'recipient' => ['required', 'string', 'max:255'],
            'paper_size' => ['nullable', 'string', 'max:20'],
            'archive_code' => ['nullable', 'string', 'max:50'],
            'summary' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if ($file !== null) {
                $attachmentPath = $file->store('documents', 'public');
                $attachmentName = $file->getClientOriginalName();
            }
        }

        $document = Document::create([
            'document_number' => $validated['document_number'],
            'category' => $validated['category'],
            'title' => $validated['title'],
            'document_date' => $validated['document_date'],
            'received_date' => $validated['received_date'] ?? $validated['document_date'],
            'sender' => $validated['sender'],
            'recipient' => $validated['recipient'],
            'paper_size' => $validated['paper_size'] ?: 'A4',
            'archive_code' => $validated['archive_code'] ?? 'ARS-A4-01',
            'summary' => $validated['summary'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'documents'])
            ->with('success', "Dokumen {$document->document_number} ({$document->category}) berhasil disimpan ke Master Dokumen A4.");
    }

    /**
     * Update an existing Master Dokumen record.
     */
    public function updateDocument(Request $request, Document $document): RedirectResponse
    {
        $validated = $request->validate([
            'document_number' => ['required', 'string', 'max:100', Rule::unique('documents', 'document_number')->ignore($document->id)],
            'category' => ['required', 'string', Rule::in(['Surat Masuk', 'Surat Keluar', 'Dokumen Umum A4', 'SK / Piagam'])],
            'title' => ['required', 'string', 'max:255'],
            'document_date' => ['required', 'date'],
            'received_date' => ['nullable', 'date'],
            'sender' => ['required', 'string', 'max:255'],
            'recipient' => ['required', 'string', 'max:255'],
            'paper_size' => ['nullable', 'string', 'max:20'],
            'archive_code' => ['nullable', 'string', 'max:50'],
            'summary' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if ($file !== null) {
                if ($document->attachment_path) {
                    Storage::disk('public')->delete($document->attachment_path);
                }
                $document->attachment_path = $file->store('documents', 'public');
                $document->attachment_name = $file->getClientOriginalName();
            }
        }

        $document->update([
            'document_number' => $validated['document_number'],
            'category' => $validated['category'],
            'title' => $validated['title'],
            'document_date' => $validated['document_date'],
            'received_date' => $validated['received_date'] ?? $validated['document_date'],
            'sender' => $validated['sender'],
            'recipient' => $validated['recipient'],
            'paper_size' => $validated['paper_size'] ?: 'A4',
            'archive_code' => $validated['archive_code'],
            'summary' => $validated['summary'],
            'attachment_path' => $document->attachment_path,
            'attachment_name' => $document->attachment_name,
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'documents'])
            ->with('success', "Dokumen {$document->document_number} berhasil diperbarui.");
    }

    /**
     * Delete a Master Dokumen record.
     */
    public function destroyDocument(Document $document): RedirectResponse
    {
        if ($document->attachment_path) {
            Storage::disk('public')->delete($document->attachment_path);
        }

        $docNum = $document->document_number;
        $document->delete();

        return redirect()
            ->route('management.dashboard', ['tab' => 'documents'])
            ->with('success', "Dokumen {$docNum} berhasil dihapus dari arsip.");
    }

    /**
     * Dedicated Create Page for Administrasi Surat Nikah & Pemberkatan Perkawinan Buddhis.
     */
    public function createMarriage(): View
    {
        return $this->renderManagementView([
            'pageMode' => 'create_marriage',
            'activeTab' => 'marriages',
        ]);
    }

    /**
     * Dedicated Edit Page for Administrasi Surat Nikah & Pemberkatan Perkawinan Buddhis.
     */
    public function editMarriage(MarriageCertificate $marriage): View
    {
        return $this->renderManagementView([
            'pageMode' => 'edit_marriage',
            'activeTab' => 'marriages',
            'editingRecord' => $marriage,
        ]);
    }

    /**
     * Store a new Buddhist Marriage Administration / Blessing Certificate record.
     */
    public function storeMarriage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'certificate_number' => ['nullable', 'string', 'max:100', 'unique:marriage_certificates,certificate_number'],
            'document_type' => ['required', 'string', Rule::in([
                'Surat Pemberkatan Perkawinan',
                'Berita Acara Pemberkatan',
                'Sertifikat Bimbingan Pra-Nikah',
                'Surat Pengantar Dukcapil',
            ])],
            'registration_date' => ['required', 'date'],
            'wedding_date' => ['required', 'date'],
            'wedding_time' => ['required', 'string', 'max:100'],
            'wedding_location' => ['required', 'string', 'max:255'],
            'groom_name' => ['required', 'string', 'max:255'],
            'groom_buddhist_name' => ['nullable', 'string', 'max:255'],
            'groom_nik' => ['nullable', 'string', 'max:32'],
            'groom_birth_info' => ['required', 'string', 'max:255'],
            'groom_father_name' => ['required', 'string', 'max:255'],
            'groom_mother_name' => ['required', 'string', 'max:255'],
            'groom_address' => ['required', 'string'],
            'bride_name' => ['required', 'string', 'max:255'],
            'bride_buddhist_name' => ['nullable', 'string', 'max:255'],
            'bride_nik' => ['nullable', 'string', 'max:32'],
            'bride_birth_info' => ['required', 'string', 'max:255'],
            'bride_father_name' => ['required', 'string', 'max:255'],
            'bride_mother_name' => ['required', 'string', 'max:255'],
            'bride_address' => ['required', 'string'],
            'officiant_name' => ['required', 'string', 'max:255'],
            'witness_one' => ['required', 'string', 'max:255'],
            'witness_two' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in([
                'Terdaftar & Verifikasi Berkas',
                'Selesai Pemberkatan',
                'Tercatat di Dukcapil',
            ])],
            'premarital_course_done' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        if (empty($validated['certificate_number'])) {
            $validated['certificate_number'] = MarriageCertificate::generateNextNumber();
        }

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if ($file !== null) {
                $attachmentPath = $file->store('marriages', 'public');
                $attachmentName = $file->getClientOriginalName();
            }
        }

        $marriage = MarriageCertificate::create([
            'certificate_number' => $validated['certificate_number'],
            'document_type' => $validated['document_type'],
            'registration_date' => $validated['registration_date'],
            'wedding_date' => $validated['wedding_date'],
            'wedding_time' => $validated['wedding_time'],
            'wedding_location' => $validated['wedding_location'],
            'groom_name' => $validated['groom_name'],
            'groom_buddhist_name' => $validated['groom_buddhist_name'] ?? null,
            'groom_nik' => $validated['groom_nik'] ?? null,
            'groom_birth_info' => $validated['groom_birth_info'],
            'groom_father_name' => $validated['groom_father_name'],
            'groom_mother_name' => $validated['groom_mother_name'],
            'groom_address' => $validated['groom_address'],
            'bride_name' => $validated['bride_name'],
            'bride_buddhist_name' => $validated['bride_buddhist_name'] ?? null,
            'bride_nik' => $validated['bride_nik'] ?? null,
            'bride_birth_info' => $validated['bride_birth_info'],
            'bride_father_name' => $validated['bride_father_name'],
            'bride_mother_name' => $validated['bride_mother_name'],
            'bride_address' => $validated['bride_address'],
            'officiant_name' => $validated['officiant_name'],
            'witness_one' => $validated['witness_one'],
            'witness_two' => $validated['witness_two'],
            'status' => $validated['status'],
            'premarital_course_done' => $request->boolean('premarital_course_done', true),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'marriages'])
            ->with('success', "Administrasi Surat Nikah {$marriage->certificate_number} ({$marriage->groom_name} & {$marriage->bride_name}) berhasil disimpan.");
    }

    /**
     * Update an existing Buddhist Marriage Administration record.
     */
    public function updateMarriage(Request $request, MarriageCertificate $marriage): RedirectResponse
    {
        $validated = $request->validate([
            'certificate_number' => ['required', 'string', 'max:100', Rule::unique('marriage_certificates', 'certificate_number')->ignore($marriage->id)],
            'document_type' => ['required', 'string', Rule::in([
                'Surat Pemberkatan Perkawinan',
                'Berita Acara Pemberkatan',
                'Sertifikat Bimbingan Pra-Nikah',
                'Surat Pengantar Dukcapil',
            ])],
            'registration_date' => ['required', 'date'],
            'wedding_date' => ['required', 'date'],
            'wedding_time' => ['required', 'string', 'max:100'],
            'wedding_location' => ['required', 'string', 'max:255'],
            'groom_name' => ['required', 'string', 'max:255'],
            'groom_buddhist_name' => ['nullable', 'string', 'max:255'],
            'groom_nik' => ['nullable', 'string', 'max:32'],
            'groom_birth_info' => ['required', 'string', 'max:255'],
            'groom_father_name' => ['required', 'string', 'max:255'],
            'groom_mother_name' => ['required', 'string', 'max:255'],
            'groom_address' => ['required', 'string'],
            'bride_name' => ['required', 'string', 'max:255'],
            'bride_buddhist_name' => ['nullable', 'string', 'max:255'],
            'bride_nik' => ['nullable', 'string', 'max:32'],
            'bride_birth_info' => ['required', 'string', 'max:255'],
            'bride_father_name' => ['required', 'string', 'max:255'],
            'bride_mother_name' => ['required', 'string', 'max:255'],
            'bride_address' => ['required', 'string'],
            'officiant_name' => ['required', 'string', 'max:255'],
            'witness_one' => ['required', 'string', 'max:255'],
            'witness_two' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in([
                'Terdaftar & Verifikasi Berkas',
                'Selesai Pemberkatan',
                'Tercatat di Dukcapil',
            ])],
            'premarital_course_done' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if ($file !== null) {
                if ($marriage->attachment_path) {
                    Storage::disk('public')->delete($marriage->attachment_path);
                }
                $marriage->attachment_path = $file->store('marriages', 'public');
                $marriage->attachment_name = $file->getClientOriginalName();
            }
        }

        $marriage->update([
            'certificate_number' => $validated['certificate_number'],
            'document_type' => $validated['document_type'],
            'registration_date' => $validated['registration_date'],
            'wedding_date' => $validated['wedding_date'],
            'wedding_time' => $validated['wedding_time'],
            'wedding_location' => $validated['wedding_location'],
            'groom_name' => $validated['groom_name'],
            'groom_buddhist_name' => $validated['groom_buddhist_name'] ?? null,
            'groom_nik' => $validated['groom_nik'] ?? null,
            'groom_birth_info' => $validated['groom_birth_info'],
            'groom_father_name' => $validated['groom_father_name'],
            'groom_mother_name' => $validated['groom_mother_name'],
            'groom_address' => $validated['groom_address'],
            'bride_name' => $validated['bride_name'],
            'bride_buddhist_name' => $validated['bride_buddhist_name'] ?? null,
            'bride_nik' => $validated['bride_nik'] ?? null,
            'bride_birth_info' => $validated['bride_birth_info'],
            'bride_father_name' => $validated['bride_father_name'],
            'bride_mother_name' => $validated['bride_mother_name'],
            'bride_address' => $validated['bride_address'],
            'officiant_name' => $validated['officiant_name'],
            'witness_one' => $validated['witness_one'],
            'witness_two' => $validated['witness_two'],
            'status' => $validated['status'],
            'premarital_course_done' => $request->boolean('premarital_course_done', false),
            'attachment_path' => $marriage->attachment_path,
            'attachment_name' => $marriage->attachment_name,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'marriages'])
            ->with('success', "Data Surat Nikah {$marriage->certificate_number} berhasil diperbarui.");
    }

    /**
     * Delete a Buddhist Marriage Administration record.
     */
    public function destroyMarriage(MarriageCertificate $marriage): RedirectResponse
    {
        if ($marriage->attachment_path) {
            Storage::disk('public')->delete($marriage->attachment_path);
        }

        $certNum = $marriage->certificate_number;
        $marriage->delete();

        return redirect()
            ->route('management.dashboard', ['tab' => 'marriages'])
            ->with('success', "Arsip Surat Nikah {$certNum} berhasil dihapus.");
    }

    /**
     * Dedicated Create Page for Schedule.
     */
    public function createSchedule(): View
    {
        return $this->renderManagementView([
            'pageMode' => 'create_schedule',
            'activeTab' => 'schedules',
        ]);
    }

    /**
     * Dedicated Edit Page for Schedule.
     */
    public function editSchedule(Schedule $schedule): View
    {
        return $this->renderManagementView([
            'pageMode' => 'edit_schedule',
            'activeTab' => 'schedules',
            'editingRecord' => $schedule,
        ]);
    }

    /**
     * Store a new Schedule (Jadwal Ibadah, Puja Bakti, Meditasi, Kegiatan).
     */
    public function storeSchedule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(['Puja Bakti', 'Meditasi', 'Kelas Dharma', 'Perayaan Hari Besar', 'Kegiatan Sosial'])],
            'day_of_week' => ['required', 'string', 'max:100'],
            'event_date' => ['nullable', 'date'],
            'start_time' => ['required', 'string', 'max:20'],
            'end_time' => ['required', 'string', 'max:20'],
            'speaker' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'is_recurring' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_recurring'] = $request->boolean('is_recurring', true);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? (Schedule::count() + 1));

        Schedule::create($validated);

        return redirect()
            ->route('management.dashboard', ['tab' => 'schedules'])
            ->with('success', "Jadwal kegiatan '{$validated['title']}' berhasil ditambahkan ke Landing Page.");
    }

    /**
     * Update an existing Schedule.
     */
    public function updateSchedule(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(['Puja Bakti', 'Meditasi', 'Kelas Dharma', 'Perayaan Hari Besar', 'Kegiatan Sosial'])],
            'day_of_week' => ['required', 'string', 'max:100'],
            'event_date' => ['nullable', 'date'],
            'start_time' => ['required', 'string', 'max:20'],
            'end_time' => ['required', 'string', 'max:20'],
            'speaker' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'is_recurring' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_recurring'] = $request->boolean('is_recurring');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? $schedule->sort_order);

        $schedule->update($validated);

        return redirect()
            ->route('management.dashboard', ['tab' => 'schedules'])
            ->with('success', "Jadwal kegiatan '{$schedule->title}' berhasil diperbarui.");
    }

    /**
     * Delete a Schedule.
     */
    public function destroySchedule(Schedule $schedule): RedirectResponse
    {
        $title = $schedule->title;
        $schedule->delete();

        return redirect()
            ->route('management.dashboard', ['tab' => 'schedules'])
            ->with('success', "Jadwal kegiatan '{$title}' telah dihapus.");
    }

    /**
     * Dedicated Create Page for Gallery.
     */
    public function createGallery(): View
    {
        return $this->renderManagementView([
            'pageMode' => 'create_gallery',
            'activeTab' => 'galleries',
        ]);
    }

    /**
     * Dedicated Edit Page for Gallery.
     */
    public function editGallery(Gallery $gallery): View
    {
        return $this->renderManagementView([
            'pageMode' => 'edit_gallery',
            'activeTab' => 'galleries',
            'editingRecord' => $gallery,
        ]);
    }

    /**
     * Extract YouTube 11-character video ID from standard watch, short, or embed URLs.
     */
    private function extractYoutubeId(string $url): ?string
    {
        $pattern = '/(?:youtube\.com\/(?:watch\?.*v=|embed\/|shorts\/|v\/)|youtu\.be\/)([A-Za-z0-9_-]{6,15})/i';
        if (preg_match($pattern, trim($url), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Store a new Gallery item (Photo Upload or YouTube Video).
     */
    public function storeGallery(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'media_type' => ['required', 'string', Rule::in(['photo', 'video'])],
            'photo_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'youtube_url' => ['nullable', 'string', 'max:500'],
            'media_url' => ['nullable', 'string', 'max:500'],
            'thumbnail_url' => ['nullable', 'string', 'max:500'],
            'event_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        if ($validated['media_type'] === 'photo') {
            if ($request->hasFile('photo_file')) {
                $file = $request->file('photo_file');
                $storedPath = $file->store('galleries', 'public');
                $mediaUrl = asset('storage/'.$storedPath);
                $thumbnailUrl = $mediaUrl;
            } elseif (! empty($validated['media_url'])) {
                $mediaUrl = $validated['media_url'];
                $thumbnailUrl = $validated['thumbnail_url'] ?: $mediaUrl;
            } else {
                return back()
                    ->withInput()
                    ->withErrors(['photo_file' => 'Silakan unggah file foto kegiatan (.jpg, .png, atau .webp).']);
            }
        } else {
            $rawYoutubeUrl = $validated['youtube_url'] ?: ($validated['media_url'] ?? '');
            $youtubeId = $this->extractYoutubeId((string) $rawYoutubeUrl);

            if ($youtubeId === null) {
                return back()
                    ->withInput()
                    ->withErrors(['youtube_url' => 'Untuk tipe Video, wajib memasukkan tautan YouTube yang valid (contoh: https://www.youtube.com/watch?v=... atau https://youtu.be/...).']);
            }

            $mediaUrl = 'https://www.youtube.com/embed/'.$youtubeId;
            $thumbnailUrl = ! empty($validated['thumbnail_url'])
                ? $validated['thumbnail_url']
                : 'https://img.youtube.com/vi/'.$youtubeId.'/hqdefault.jpg';
        }

        Gallery::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'media_type' => $validated['media_type'],
            'media_url' => $mediaUrl,
            'thumbnail_url' => $thumbnailUrl,
            'event_date' => $validated['event_date'] ?? now()->toDateString(),
            'description' => $validated['description'] ?? null,
            'is_featured' => $request->boolean('is_featured', true),
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'galleries'])
            ->with('success', "Dokumentasi galeri '{$validated['title']}' berhasil ditambahkan.");
    }

    /**
     * Update a Gallery item (Photo Upload or YouTube Video).
     */
    public function updateGallery(Request $request, Gallery $gallery): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'media_type' => ['required', 'string', Rule::in(['photo', 'video'])],
            'photo_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'youtube_url' => ['nullable', 'string', 'max:500'],
            'media_url' => ['nullable', 'string', 'max:500'],
            'thumbnail_url' => ['nullable', 'string', 'max:500'],
            'event_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $mediaUrl = $gallery->media_url;
        $thumbnailUrl = $gallery->thumbnail_url;

        if ($validated['media_type'] === 'photo') {
            if ($request->hasFile('photo_file')) {
                $file = $request->file('photo_file');
                $storedPath = $file->store('galleries', 'public');
                $mediaUrl = asset('storage/'.$storedPath);
                $thumbnailUrl = $mediaUrl;
            } elseif (! empty($validated['media_url'])) {
                $mediaUrl = $validated['media_url'];
                $thumbnailUrl = $validated['thumbnail_url'] ?: $mediaUrl;
            }
        } else {
            $rawYoutubeUrl = $validated['youtube_url'] ?: ($validated['media_url'] ?: $gallery->media_url);
            $youtubeId = $this->extractYoutubeId((string) $rawYoutubeUrl);

            if ($youtubeId === null) {
                return back()
                    ->withInput()
                    ->withErrors(['youtube_url' => 'Untuk tipe Video, wajib memasukkan tautan YouTube yang valid (contoh: https://www.youtube.com/watch?v=... atau https://youtu.be/...).']);
            }

            $mediaUrl = 'https://www.youtube.com/embed/'.$youtubeId;
            $thumbnailUrl = ! empty($validated['thumbnail_url'])
                ? $validated['thumbnail_url']
                : 'https://img.youtube.com/vi/'.$youtubeId.'/hqdefault.jpg';
        }

        $gallery->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'media_type' => $validated['media_type'],
            'media_url' => $mediaUrl,
            'thumbnail_url' => $thumbnailUrl,
            'event_date' => $validated['event_date'] ?? $gallery->event_date,
            'description' => $validated['description'] ?? null,
            'is_featured' => $request->boolean('is_featured', true),
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'galleries'])
            ->with('success', "Dokumentasi galeri '{$gallery->title}' berhasil diperbarui.");
    }

    /**
     * Delete a Gallery item.
     */
    public function destroyGallery(Gallery $gallery): RedirectResponse
    {
        $title = $gallery->title;
        $gallery->delete();

        return redirect()
            ->route('management.dashboard', ['tab' => 'galleries'])
            ->with('success', "Dokumentasi galeri '{$title}' telah dihapus.");
    }

    /**
     * Dedicated Create Page for Article.
     */
    public function createArticle(): View
    {
        return $this->renderManagementView([
            'pageMode' => 'create_article',
            'activeTab' => 'articles',
        ]);
    }

    /**
     * Dedicated Edit Page for Article.
     */
    public function editArticle(Article $article): View
    {
        return $this->renderManagementView([
            'pageMode' => 'edit_article',
            'activeTab' => 'articles',
            'editingRecord' => $article,
        ]);
    }

    /**
     * Store a new Article / Berita Kegiatan / Renungan Dharma / Pengumuman.
     */
    public function storeArticle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(['Berita Kegiatan', 'Renungan Dharma', 'Pengumuman', 'Artikel Buddhis'])],
            'author' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (Article::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        Article::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'category' => $validated['category'],
            'author' => $validated['author'],
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'cover_image' => $validated['cover_image'] ?: 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1000&q=80',
            'published_at' => now(),
            'is_published' => $request->boolean('is_published', true),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'articles'])
            ->with('success', "Publikasi '{$validated['title']}' berhasil diterbitkan.");
    }

    /**
     * Update an existing Article.
     */
    public function updateArticle(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(['Berita Kegiatan', 'Renungan Dharma', 'Pengumuman', 'Artikel Buddhis'])],
            'author' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $article->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'author' => $validated['author'],
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'cover_image' => $validated['cover_image'] ?: $article->cover_image,
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'articles'])
            ->with('success', "Publikasi '{$article->title}' berhasil diperbarui.");
    }

    /**
     * Delete an Article.
     */
    public function destroyArticle(Article $article): RedirectResponse
    {
        $title = $article->title;
        $article->delete();

        return redirect()
            ->route('management.dashboard', ['tab' => 'articles'])
            ->with('success', "Publikasi '{$title}' telah dihapus.");
    }

    /**
     * Dedicated Create Page for Role.
     */
    public function createRole(): View
    {
        return $this->renderManagementView([
            'pageMode' => 'create_role',
            'activeTab' => 'roles',
        ]);
    }

    /**
     * Dedicated Edit Page for Role.
     */
    public function editRole(Role $role): View
    {
        return $this->renderManagementView([
            'pageMode' => 'edit_role',
            'activeTab' => 'roles',
            'editingRecord' => $role,
        ]);
    }

    /**
     * Store a new Role in Role Management.
     */
    public function storeRole(Request $request): RedirectResponse
    {
        $validPermissionKeys = array_keys(Role::availablePermissions());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:roles,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', Rule::in($validPermissionKeys)],
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        if (Role::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'permissions' => array_values($validated['permissions']),
            'is_system' => false,
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'roles'])
            ->with('success', "Role '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Update an existing Role's permissions and metadata.
     */
    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $validPermissionKeys = array_keys(Role::availablePermissions());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', Rule::in($validPermissionKeys)],
        ]);

        $permissions = $role->is_system
            ? $validPermissionKeys
            : array_values($validated['permissions']);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $permissions,
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'roles'])
            ->with('success', "Hak akses Role '{$role->name}' berhasil diperbarui.");
    }

    /**
     * Delete a custom Role (system roles cannot be deleted).
     */
    public function destroyRole(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return redirect()
                ->route('management.dashboard', ['tab' => 'roles'])
                ->withErrors(['role' => 'Role sistem utama tidak dapat dihapus.']);
        }

        $name = $role->name;
        $role->delete();

        return redirect()
            ->route('management.dashboard', ['tab' => 'roles'])
            ->with('success', "Role '{$name}' berhasil dihapus.");
    }

    /**
     * Dedicated Create Page for User.
     */
    public function createUser(): View
    {
        return $this->renderManagementView([
            'pageMode' => 'create_user',
            'activeTab' => 'roles',
        ]);
    }

    /**
     * Dedicated Edit Page for User.
     */
    public function editUser(User $user): View
    {
        return $this->renderManagementView([
            'pageMode' => 'edit_user',
            'activeTab' => 'roles',
            'editingRecord' => $user,
        ]);
    }

    /**
     * Store a new internal User and assign a Role.
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'position' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => (int) $validated['role_id'],
            'position' => $validated['position'] ?? 'Pengurus Vihara',
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'roles'])
            ->with('success', "Akun pengurus '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Update an existing internal User and their assigned Role.
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'position' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => (int) $validated['role_id'],
            'position' => $validated['position'] ?? $user->position,
            'phone' => $validated['phone'] ?? $user->phone,
            'is_active' => $request->boolean('is_active', true),
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()
            ->route('management.dashboard', ['tab' => 'roles'])
            ->with('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Delete an internal User account (cannot delete self).
     */
    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
            return redirect()
                ->route('management.dashboard', ['tab' => 'roles'])
                ->withErrors(['user' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.']);
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('management.dashboard', ['tab' => 'roles'])
            ->with('success', "Akun pengguna '{$name}' berhasil dihapus.");
    }

    /**
     * Show the Account Configuration / Profile settings view.
     */
    public function editProfile(): View
    {
        return $this->renderManagementView([
            'pageMode' => 'index',
            'activeTab' => 'profile',
        ]);
    }

    /**
     * Update the currently authenticated user's profile and password.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'position' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'position' => $validated['position'] ?? $user->position,
            'phone' => $validated['phone'] ?? $user->phone,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()
            ->route('management.dashboard', ['tab' => 'profile'])
            ->with('success', 'Konfigurasi akun dan profil Anda berhasil diperbarui.');
    }
}
