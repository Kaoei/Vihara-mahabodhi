<?php

namespace App\Http\Controllers;

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
use App\Services\DocxTemplateService;
use App\Services\ReceiptExcelService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagementController extends Controller
{
    public function __construct(
        public ReceiptExcelService $excelService,
        public DocxTemplateService $docxService,
    ) {}

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

        $viharaProfile = ViharaProfile::current();
        $categoriesByModule = Category::allGrouped();
        $categoryNames = Category::namesGrouped();

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
            'viharaProfile' => $viharaProfile,
            'availableFacilityIcons' => ViharaProfile::availableIcons(),
            'availablePermissions' => Role::availablePermissions(),
            'categoriesByModule' => $categoriesByModule,
            'categoryNames' => $categoryNames,
            'categoryModuleLabels' => Category::moduleLabels(),
            'templatePlaceholders' => DocxTemplateService::availablePlaceholders(),
            'defaultReceiptTemplateHtml' => DocxTemplateService::defaultReceiptTemplateHtml(),
            'defaultThankYouTemplateHtml' => DocxTemplateService::defaultThankYouTemplateHtml(),
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
            'viharaProfile' => ViharaProfile::current(),
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
     * Extract normalized multi-item goods list from a Receipt form request.
     *
     * @param  array<string, mixed>  $validated
     * @return array{
     *     items: array<int, array{name: string, quantity: string, nominal: ?float}>,
     *     item_name: string,
     *     quantity: string,
     *     nominal: ?float
     * }
     */
    private function extractReceiptItemsPayload(array $validated): array
    {
        $items = [];
        $rawItems = $validated['items'] ?? [];

        if (is_array($rawItems)) {
            foreach ($rawItems as $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                $name = trim((string) ($entry['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $qty = trim((string) ($entry['quantity'] ?? ''));
                $nom = isset($entry['nominal']) && $entry['nominal'] !== '' && $entry['nominal'] !== null
                    ? (float) $entry['nominal']
                    : null;

                $items[] = [
                    'name' => $name,
                    'quantity' => $qty !== '' ? $qty : '1 Paket',
                    'nominal' => $nom,
                ];
            }
        }

        if ($items === [] && ! empty($validated['item_name'])) {
            $items[] = [
                'name' => trim((string) $validated['item_name']),
                'quantity' => trim((string) ($validated['quantity'] ?? '')) ?: '1 Paket',
                'nominal' => isset($validated['nominal']) && $validated['nominal'] !== '' && $validated['nominal'] !== null
                    ? (float) $validated['nominal']
                    : null,
            ];
        }

        $names = array_column($items, 'name');
        $quantities = array_column($items, 'quantity');

        $summaryName = count($names) <= 2
            ? implode(', ', $names)
            : $names[0].', '.$names[1].' (+'.(count($names) - 2).' lainnya)';

        $summaryQuantity = count($items) === 1
            ? $quantities[0]
            : count($items).' Item ('.implode(', ', array_slice($quantities, 0, 2)).(count($quantities) > 2 ? ', ...' : '').')';

        $itemNominalSum = 0.0;
        $hasItemNominal = false;
        foreach ($items as $it) {
            if ($it['nominal'] !== null) {
                $itemNominalSum += $it['nominal'];
                $hasItemNominal = true;
            }
        }

        $explicitNominal = isset($validated['nominal']) && $validated['nominal'] !== '' && $validated['nominal'] !== null
            ? (float) $validated['nominal']
            : null;

        $finalNominal = $explicitNominal ?? ($hasItemNominal ? $itemNominalSum : null);

        return [
            'items' => $items,
            'item_name' => Str::limit($summaryName, 250, '...'),
            'quantity' => Str::limit($summaryQuantity, 95, '...'),
            'nominal' => $finalNominal,
        ];
    }

    /**
     * Store a newly created Tanda Terima & Ucapan Terima Kasih (supports multiple goods items).
     */
    public function storeReceipt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receipt_number' => ['nullable', 'string', 'max:100', 'unique:receipts,receipt_number'],
            'receipt_type' => ['required', 'string', 'max:100'],
            'receipt_date' => ['required', 'date'],
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_institution' => ['nullable', 'string', 'max:255'],
            'donor_contact' => ['nullable', 'string', 'max:100'],
            'item_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'string', 'max:100'],
            'items' => ['nullable', 'array'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'string', 'max:100'],
            'items.*.nominal' => ['nullable', 'numeric', 'min:0'],
            'nominal' => ['nullable', 'numeric', 'min:0'],
            'receiver_name' => ['required', 'string', 'max:255'],
            'thank_you_message' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $itemsPayload = $this->extractReceiptItemsPayload($validated);
        if ($itemsPayload['items'] === []) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'Minimal harus mengisi 1 uraian barang / bantuan.']);
        }

        Category::ensureExists('receipts', $validated['receipt_type']);

        if (empty($validated['receipt_number'])) {
            $validated['receipt_number'] = Receipt::generateNextNumber();
        }

        $thankYou = $validated['thank_you_message'] ?? '';
        if (trim($thankYou) === '') {
            $thankYou = sprintf(
                'Sabbadanam Dhammadanam Jinati. Pengurus Wihara Mahabodhi Buddhist Centre Semarang mengucapkan Anumodana dan terima kasih yang tulus kepada %s atas penyerahan %s (%s). Semoga timbunan kebajikan ini membawa berkah kesehatan, kebahagiaan, dan kedamaian bagi Bapak/Ibu beserta keluarga.',
                $validated['donor_name'],
                strtolower($validated['receipt_type']),
                $itemsPayload['item_name']
            );
        }

        $receipt = Receipt::create([
            'receipt_number' => $validated['receipt_number'],
            'receipt_type' => $validated['receipt_type'],
            'receipt_date' => $validated['receipt_date'],
            'donor_name' => $validated['donor_name'],
            'donor_institution' => $validated['donor_institution'] ?? null,
            'donor_contact' => $validated['donor_contact'] ?? null,
            'item_name' => $itemsPayload['item_name'],
            'quantity' => $itemsPayload['quantity'],
            'items' => $itemsPayload['items'],
            'nominal' => $itemsPayload['nominal'],
            'receiver_name' => $validated['receiver_name'],
            'thank_you_message' => $thankYou,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

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
            'receipt_type' => ['required', 'string', 'max:100'],
            'receipt_date' => ['required', 'date'],
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_institution' => ['nullable', 'string', 'max:255'],
            'donor_contact' => ['nullable', 'string', 'max:100'],
            'item_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'string', 'max:100'],
            'items' => ['nullable', 'array'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'string', 'max:100'],
            'items.*.nominal' => ['nullable', 'numeric', 'min:0'],
            'nominal' => ['nullable', 'numeric', 'min:0'],
            'receiver_name' => ['required', 'string', 'max:255'],
            'thank_you_message' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $itemsPayload = $this->extractReceiptItemsPayload($validated);
        if ($itemsPayload['items'] === []) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'Minimal harus mengisi 1 uraian barang / bantuan.']);
        }

        Category::ensureExists('receipts', $validated['receipt_type']);

        $receipt->update([
            'receipt_number' => $validated['receipt_number'],
            'receipt_type' => $validated['receipt_type'],
            'receipt_date' => $validated['receipt_date'],
            'donor_name' => $validated['donor_name'],
            'donor_institution' => $validated['donor_institution'] ?? null,
            'donor_contact' => $validated['donor_contact'] ?? null,
            'item_name' => $itemsPayload['item_name'],
            'quantity' => $itemsPayload['quantity'],
            'items' => $itemsPayload['items'],
            'nominal' => $itemsPayload['nominal'],
            'receiver_name' => $validated['receiver_name'],
            'thank_you_message' => $validated['thank_you_message'] ?? $receipt->thank_you_message,
            'notes' => $validated['notes'] ?? null,
        ]);

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
     * Show the Receipt & Thank You Word Template Upload & Web Editor page.
     */
    public function editReceiptTemplate(): View
    {
        return $this->renderManagementView([
            'pageMode' => 'edit_receipt_template',
            'activeTab' => 'receipts',
        ]);
    }

    /**
     * Save edited custom HTML templates for Receipt and/or Thank You letter.
     */
    public function updateReceiptTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receipt_template_html' => ['nullable', 'string'],
            'thank_you_template_html' => ['nullable', 'string'],
        ]);

        $profile = ViharaProfile::current();
        $receiptHtml = trim((string) ($validated['receipt_template_html'] ?? ''));
        $thankYouHtml = trim((string) ($validated['thank_you_template_html'] ?? ''));

        $profile->update([
            'receipt_template_html' => $receiptHtml !== '' ? $this->docxService->sanitizeHtml($receiptHtml) : null,
            'thank_you_template_html' => $thankYouHtml !== '' ? $this->docxService->sanitizeHtml($thankYouHtml) : null,
        ]);

        return redirect()
            ->route('management.receipts.template.edit')
            ->with('success', 'Template surat Tanda Terima & Ucapan Terima Kasih berhasil disimpan dan siap digunakan saat cetak A4.');
    }

    /**
     * Upload a Microsoft Word (.docx) file and convert it into an editable web template.
     */
    public function uploadReceiptDocxTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_template' => ['required', 'string', Rule::in(['receipt', 'thank_you'])],
            'docx_file' => ['required', 'file', 'mimes:docx', 'max:5120'],
        ]);

        $file = $request->file('docx_file');
        if ($file === null) {
            return back()->withErrors(['docx_file' => 'File Word (.docx) tidak ditemukan.']);
        }

        $parsedHtml = $this->docxService->parseDocxToHtml($file);
        if ($parsedHtml === '') {
            return back()->withErrors(['docx_file' => 'Gagal membaca isi file Word (.docx). Pastikan file berformat Microsoft Word .docx yang valid dan tidak kosong.']);
        }

        $profile = ViharaProfile::current();
        if ($validated['target_template'] === 'thank_you') {
            $profile->update(['thank_you_template_html' => $parsedHtml]);
            $label = 'Surat Ucapan Terima Kasih';
        } else {
            $profile->update(['receipt_template_html' => $parsedHtml]);
            $label = 'Bukti Tanda Terima';
        }

        return redirect()
            ->route('management.receipts.template.edit', ['editor' => $validated['target_template']])
            ->with('success', "Template Word (.docx) berhasil diimpor ke editor {$label}. Anda dapat mengedit isi maupun placeholder-nya langsung di bawah.");
    }

    /**
     * Reset custom Receipt / Thank You template back to the built-in default.
     */
    public function resetReceiptTemplate(Request $request): RedirectResponse
    {
        $target = (string) $request->input('target_template', 'all');
        $profile = ViharaProfile::current();

        if ($target === 'receipt') {
            $profile->update(['receipt_template_html' => null]);
        } elseif ($target === 'thank_you') {
            $profile->update(['thank_you_template_html' => null]);
        } else {
            $profile->update([
                'receipt_template_html' => null,
                'thank_you_template_html' => null,
            ]);
        }

        return redirect()
            ->route('management.receipts.template.edit')
            ->with('success', 'Template surat berhasil dikembalikan ke desain bawaan sistem.');
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
     * Store uploaded archive files (supports multiple attachments[] and legacy single attachment).
     *
     * @return array<int, array{path: string, name: string}>
     */
    private function collectUploadedArchiveFiles(Request $request, string $directory): array
    {
        $stored = [];
        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            $multi = $request->file('attachments');
            if (is_array($multi)) {
                $uploadedFiles = array_merge($uploadedFiles, $multi);
            } elseif ($multi !== null) {
                $uploadedFiles[] = $multi;
            }
        }

        if ($request->hasFile('attachment')) {
            $single = $request->file('attachment');
            if ($single !== null) {
                $uploadedFiles[] = $single;
            }
        }

        foreach ($uploadedFiles as $file) {
            if ($file !== null && $file->isValid()) {
                $stored[] = [
                    'path' => $file->store($directory, 'public'),
                    'name' => $file->getClientOriginalName(),
                ];
            }
        }

        return $stored;
    }

    /**
     * Store a new Master Dokumen (Surat Masuk / Surat Keluar / SK & Piagam) with uploaded file(s).
     */
    public function storeDocument(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'document_number' => ['required', 'string', 'max:100', 'unique:documents,document_number'],
            'category' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'document_date' => ['required', 'date'],
            'received_date' => ['nullable', 'date'],
            'sender' => ['required', 'string', 'max:255'],
            'recipient' => ['required', 'string', 'max:255'],
            'paper_size' => ['nullable', 'string', 'max:20'],
            'archive_code' => ['nullable', 'string', 'max:50'],
            'summary' => ['nullable', 'string'],
            'attachments' => ['required_without:attachment', 'array', 'min:1'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        Category::ensureExists('documents', $validated['category']);

        $storedFiles = $this->collectUploadedArchiveFiles($request, 'documents');
        $firstFile = $storedFiles[0] ?? null;

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
            'summary' => $validated['summary'] ?? '',
            'attachment_path' => $firstFile['path'] ?? null,
            'attachment_name' => $firstFile['name'] ?? null,
            'attachments' => $storedFiles,
            'created_by' => $request->user()?->id,
        ]);

        $fileCount = count($storedFiles);

        return redirect()
            ->route('management.dashboard', ['tab' => 'documents'])
            ->with('success', "Arsip dokumen {$document->document_number} ({$fileCount} file) berhasil disimpan.");
    }

    /**
     * Serve or download one of the uploaded files for a Master Dokumen record.
     */
    public function showDocumentFile(Request $request, Document $document): StreamedResponse
    {
        $files = $document->allAttachments();
        $index = max(0, (int) $request->query('index', 0));
        $target = $files[$index] ?? null;

        abort_if(
            $target === null || ! Storage::disk('public')->exists($target['path']),
            404,
            'File dokumen belum diunggah atau tidak ditemukan.'
        );

        $filename = $target['name'] ?: basename($target['path']);

        if ($request->boolean('download')) {
            return Storage::disk('public')->download($target['path'], $filename);
        }

        return Storage::disk('public')->response($target['path'], $filename);
    }

    /**
     * Update an existing Master Dokumen record (can add more files or remove existing ones).
     */
    public function updateDocument(Request $request, Document $document): RedirectResponse
    {
        $validated = $request->validate([
            'document_number' => ['required', 'string', 'max:100', Rule::unique('documents', 'document_number')->ignore($document->id)],
            'category' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'document_date' => ['required', 'date'],
            'received_date' => ['nullable', 'date'],
            'sender' => ['required', 'string', 'max:255'],
            'recipient' => ['required', 'string', 'max:255'],
            'paper_size' => ['nullable', 'string', 'max:20'],
            'archive_code' => ['nullable', 'string', 'max:50'],
            'summary' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240'],
            'remove_attachments' => ['nullable', 'array'],
            'remove_attachments.*' => ['string'],
        ]);

        Category::ensureExists('documents', $validated['category']);

        $existingFiles = $document->allAttachments();
        $removePaths = $validated['remove_attachments'] ?? [];

        $remainingFiles = [];
        foreach ($existingFiles as $item) {
            if (in_array($item['path'], $removePaths, true)) {
                Storage::disk('public')->delete($item['path']);

                continue;
            }
            $remainingFiles[] = $item;
        }

        $newFiles = $this->collectUploadedArchiveFiles($request, 'documents');
        $finalFiles = array_values(array_merge($remainingFiles, $newFiles));
        $firstFile = $finalFiles[0] ?? null;

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
            'summary' => $validated['summary'] ?? '',
            'attachment_path' => $firstFile['path'] ?? null,
            'attachment_name' => $firstFile['name'] ?? null,
            'attachments' => $finalFiles,
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'documents'])
            ->with('success', "Arsip dokumen {$document->document_number} berhasil diperbarui.");
    }

    /**
     * Delete a Master Dokumen record and all its uploaded files.
     */
    public function destroyDocument(Document $document): RedirectResponse
    {
        foreach ($document->allAttachments() as $item) {
            Storage::disk('public')->delete($item['path']);
        }

        $docNum = $document->document_number;
        $document->delete();

        return redirect()
            ->route('management.dashboard', ['tab' => 'documents'])
            ->with('success', "Dokumen {$docNum} berhasil dihapus dari arsip.");
    }

    /**
     * Export all Arsip Persuratan & Dokumen records as an OpenXML .xlsx file.
     */
    public function exportDocumentsExcel(): Response
    {
        $documents = Document::query()
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->get();

        $rows = [
            [
                'Nomor Surat / Dokumen',
                'Kategori',
                'Judul / Perihal',
                'Tanggal Surat',
                'Tanggal Diterima',
                'Pengirim',
                'Penerima',
                'Ukuran Kertas',
                'Kode Arsip',
                'Ringkasan / Catatan',
                'Jumlah File Terlampir',
            ],
        ];

        foreach ($documents as $doc) {
            $rows[] = [
                $doc->document_number,
                $doc->category,
                $doc->title,
                $doc->document_date->format('Y-m-d'),
                $doc->received_date ? $doc->received_date->format('Y-m-d') : '',
                $doc->sender,
                $doc->recipient,
                $doc->paper_size ?? 'A4',
                $doc->archive_code ?? '',
                $doc->summary ?? '',
                count($doc->allAttachments()),
            ];
        }

        $binary = $this->excelService->generateXlsxBinary($rows, 'Arsip Dokumen');
        $filename = 'Arsip_Dokumen_Wihara_Mahabodhi_'.now()->format('Ymd_His').'.xlsx';

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Import Arsip Persuratan & Dokumen records from an uploaded Excel (.xlsx) or CSV file.
     */
    public function importDocumentsExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'max:5120'],
        ]);

        $file = $request->file('excel_file');
        if ($file === null) {
            return redirect()
                ->route('management.dashboard', ['tab' => 'documents'])
                ->withErrors(['excel_file' => 'Berkas Excel tidak ditemukan.']);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx', 'xls', 'csv', 'txt'], true)) {
            return redirect()
                ->route('management.dashboard', ['tab' => 'documents'])
                ->withErrors(['excel_file' => 'Format file harus berupa .xlsx, .csv, atau .xls.']);
        }

        $rows = $this->excelService->parseDocumentsFile($file);
        if ($rows === []) {
            return redirect()
                ->route('management.dashboard', ['tab' => 'documents'])
                ->withErrors(['excel_file' => 'Tidak ada baris data dokumen valid yang dapat diimpor dari file tersebut.']);
        }

        $importedCount = 0;
        foreach ($rows as $rowData) {
            Category::ensureExists('documents', (string) $rowData['category']);
            $rowData['created_by'] = $request->user()?->id;
            Document::create($rowData);
            $importedCount++;
        }

        return redirect()
            ->route('management.dashboard', ['tab' => 'documents'])
            ->with('success', "Berhasil mengimpor {$importedCount} data Arsip Dokumen dari file Excel/CSV.");
    }

    /**
     * Download the example Excel template for Arsip Dokumen import.
     */
    public function downloadDocumentTemplate(): Response
    {
        $rows = $this->excelService->getDocumentTemplateRows();
        $binary = $this->excelService->generateXlsxBinary($rows, 'Template Dokumen');
        $filename = 'Template_Import_Arsip_Dokumen_WMBC.xlsx';

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Dedicated Create Page for Arsip Surat Nikah & Pemberkatan Perkawinan Buddhis.
     */
    public function createMarriage(): View
    {
        return $this->renderManagementView([
            'pageMode' => 'create_marriage',
            'activeTab' => 'marriages',
        ]);
    }

    /**
     * Dedicated Edit Page for Arsip Surat Nikah & Pemberkatan Perkawinan Buddhis.
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
     * Store a new Buddhist Marriage Archive document record with uploaded file(s).
     */
    public function storeMarriage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'certificate_number' => ['nullable', 'string', 'max:100', 'unique:marriage_certificates,certificate_number'],
            'document_type' => ['required', 'string', 'max:100'],
            'wedding_date' => ['required', 'date'],
            'groom_name' => ['required', 'string', 'max:255'],
            'bride_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in([
                'Terdaftar & Verifikasi Berkas',
                'Selesai Pemberkatan',
                'Tercatat di Dukcapil',
            ])],
            'notes' => ['nullable', 'string'],
            'attachments' => ['required_without:attachment', 'array', 'min:1'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        Category::ensureExists('marriages', $validated['document_type']);

        if (empty($validated['certificate_number'])) {
            $validated['certificate_number'] = MarriageCertificate::generateNextNumber();
        }

        $storedFiles = $this->collectUploadedArchiveFiles($request, 'marriages');
        $firstFile = $storedFiles[0] ?? null;

        $marriage = MarriageCertificate::create([
            'certificate_number' => $validated['certificate_number'],
            'document_type' => $validated['document_type'],
            'registration_date' => $validated['wedding_date'],
            'wedding_date' => $validated['wedding_date'],
            'groom_name' => $validated['groom_name'],
            'bride_name' => $validated['bride_name'],
            'status' => $validated['status'],
            'premarital_course_done' => true,
            'attachment_path' => $firstFile['path'] ?? null,
            'attachment_name' => $firstFile['name'] ?? null,
            'attachments' => $storedFiles,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        $fileCount = count($storedFiles);

        return redirect()
            ->route('management.dashboard', ['tab' => 'marriages'])
            ->with('success', "Arsip Surat Nikah {$marriage->certificate_number} ({$fileCount} file) berhasil disimpan.");
    }

    /**
     * Serve or download one of the uploaded files for a Marriage Archive record.
     */
    public function showMarriageFile(Request $request, MarriageCertificate $marriage): StreamedResponse
    {
        $files = $marriage->allAttachments();
        $index = max(0, (int) $request->query('index', 0));
        $target = $files[$index] ?? null;

        abort_if(
            $target === null || ! Storage::disk('public')->exists($target['path']),
            404,
            'File arsip pernikahan belum diunggah atau tidak ditemukan.'
        );

        $filename = $target['name'] ?: basename($target['path']);

        if ($request->boolean('download')) {
            return Storage::disk('public')->download($target['path'], $filename);
        }

        return Storage::disk('public')->response($target['path'], $filename);
    }

    /**
     * Update an existing Buddhist Marriage Archive record (can add more files or remove existing ones).
     */
    public function updateMarriage(Request $request, MarriageCertificate $marriage): RedirectResponse
    {
        $validated = $request->validate([
            'certificate_number' => ['required', 'string', 'max:100', Rule::unique('marriage_certificates', 'certificate_number')->ignore($marriage->id)],
            'document_type' => ['required', 'string', 'max:100'],
            'wedding_date' => ['required', 'date'],
            'groom_name' => ['required', 'string', 'max:255'],
            'bride_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in([
                'Terdaftar & Verifikasi Berkas',
                'Selesai Pemberkatan',
                'Tercatat di Dukcapil',
            ])],
            'notes' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240'],
            'remove_attachments' => ['nullable', 'array'],
            'remove_attachments.*' => ['string'],
        ]);

        Category::ensureExists('marriages', $validated['document_type']);

        $existingFiles = $marriage->allAttachments();
        $removePaths = $validated['remove_attachments'] ?? [];

        $remainingFiles = [];
        foreach ($existingFiles as $item) {
            if (in_array($item['path'], $removePaths, true)) {
                Storage::disk('public')->delete($item['path']);

                continue;
            }
            $remainingFiles[] = $item;
        }

        $newFiles = $this->collectUploadedArchiveFiles($request, 'marriages');
        $finalFiles = array_values(array_merge($remainingFiles, $newFiles));
        $firstFile = $finalFiles[0] ?? null;

        $marriage->update([
            'certificate_number' => $validated['certificate_number'],
            'document_type' => $validated['document_type'],
            'wedding_date' => $validated['wedding_date'],
            'groom_name' => $validated['groom_name'],
            'bride_name' => $validated['bride_name'],
            'status' => $validated['status'],
            'attachment_path' => $firstFile['path'] ?? null,
            'attachment_name' => $firstFile['name'] ?? null,
            'attachments' => $finalFiles,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'marriages'])
            ->with('success', "Arsip Surat Nikah {$marriage->certificate_number} berhasil diperbarui.");
    }

    /**
     * Delete a Buddhist Marriage Archive record and all its uploaded files.
     */
    public function destroyMarriage(MarriageCertificate $marriage): RedirectResponse
    {
        foreach ($marriage->allAttachments() as $item) {
            Storage::disk('public')->delete($item['path']);
        }

        $certNum = $marriage->certificate_number;
        $marriage->delete();

        return redirect()
            ->route('management.dashboard', ['tab' => 'marriages'])
            ->with('success', "Arsip Surat Nikah {$certNum} berhasil dihapus.");
    }

    /**
     * Export all Arsip Surat Nikah & Pemberkatan records as an OpenXML .xlsx file.
     */
    public function exportMarriagesExcel(): Response
    {
        $marriages = MarriageCertificate::query()
            ->orderByDesc('wedding_date')
            ->orderByDesc('id')
            ->get();

        $rows = [
            [
                'Nomor Arsip / Surat',
                'Jenis Dokumen Nikah',
                'Tanggal Pemberkatan',
                'Nama Mempelai Pria',
                'Nama Mempelai Wanita',
                'Status Arsip',
                'Catatan / Keterangan',
                'Jumlah File Terlampir',
            ],
        ];

        foreach ($marriages as $m) {
            $rows[] = [
                $m->certificate_number,
                $m->document_type,
                $m->wedding_date->format('Y-m-d'),
                $m->groom_name,
                $m->bride_name,
                $m->status,
                $m->notes ?? '',
                count($m->allAttachments()),
            ];
        }

        $binary = $this->excelService->generateXlsxBinary($rows, 'Arsip Surat Nikah');
        $filename = 'Arsip_Surat_Nikah_Wihara_Mahabodhi_'.now()->format('Ymd_His').'.xlsx';

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Import Arsip Surat Nikah & Pemberkatan records from an uploaded Excel (.xlsx) or CSV file.
     */
    public function importMarriagesExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'max:5120'],
        ]);

        $file = $request->file('excel_file');
        if ($file === null) {
            return redirect()
                ->route('management.dashboard', ['tab' => 'marriages'])
                ->withErrors(['excel_file' => 'Berkas Excel tidak ditemukan.']);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx', 'xls', 'csv', 'txt'], true)) {
            return redirect()
                ->route('management.dashboard', ['tab' => 'marriages'])
                ->withErrors(['excel_file' => 'Format file harus berupa .xlsx, .csv, atau .xls.']);
        }

        $rows = $this->excelService->parseMarriagesFile($file);
        if ($rows === []) {
            return redirect()
                ->route('management.dashboard', ['tab' => 'marriages'])
                ->withErrors(['excel_file' => 'Tidak ada baris data surat nikah valid yang dapat diimpor dari file tersebut.']);
        }

        $importedCount = 0;
        foreach ($rows as $rowData) {
            Category::ensureExists('marriages', (string) $rowData['document_type']);
            $rowData['created_by'] = $request->user()?->id;
            MarriageCertificate::create($rowData);
            $importedCount++;
        }

        return redirect()
            ->route('management.dashboard', ['tab' => 'marriages'])
            ->with('success', "Berhasil mengimpor {$importedCount} data Arsip Surat Nikah dari file Excel/CSV.");
    }

    /**
     * Download the example Excel template for Arsip Surat Nikah import.
     */
    public function downloadMarriageTemplate(): Response
    {
        $rows = $this->excelService->getMarriageTemplateRows();
        $binary = $this->excelService->generateXlsxBinary($rows, 'Template Surat Nikah');
        $filename = 'Template_Import_Arsip_Surat_Nikah_WMBC.xlsx';

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
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
            'category' => ['required', 'string', 'max:100'],
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

        Category::ensureExists('schedules', $validated['category']);

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
            'category' => ['required', 'string', 'max:100'],
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

        Category::ensureExists('schedules', $validated['category']);

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

        Category::ensureExists('galleries', $validated['category']);

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

        Category::ensureExists('galleries', $validated['category']);

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
            'category' => ['required', 'string', 'max:100'],
            'author' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        Category::ensureExists('articles', $validated['category']);

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
            'category' => ['required', 'string', 'max:100'],
            'author' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        Category::ensureExists('articles', $validated['category']);

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
            'position' => $validated['position'] ?? 'Pengurus Wihara',
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

    /**
     * Update the public landing page Wihara profile and contact information.
     */
    public function updateViharaProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:120'],
            'tagline' => ['required', 'string', 'max:255'],
            'about' => ['required', 'string', 'max:3000'],
            'address_short' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'operational_hours' => ['required', 'string', 'max:255'],
            'logo_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'reset_logo' => ['nullable', 'boolean'],
            'hero_image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hero_image_url' => ['nullable', 'string', 'max:500'],
            'reset_hero_image' => ['nullable', 'boolean'],
            'footer_description' => ['required', 'string', 'max:1000'],
            'footer_services_title' => ['required', 'string', 'max:150'],
            'footer_services_text' => ['required', 'string', 'max:1500'],
            'footer_copyright' => ['required', 'string', 'max:255'],
        ]);

        $profile = ViharaProfile::current();

        $logoUrl = $profile->logo_url;
        if ($request->boolean('reset_logo')) {
            $logoUrl = null;
        } elseif ($request->hasFile('logo_file')) {
            $storedLogo = $request->file('logo_file')->store('logos', 'public');
            $logoUrl = asset('storage/'.$storedLogo);
        }

        $heroImage = $profile->hero_image ?: '/Assets/Img/hero.png';
        if ($request->boolean('reset_hero_image')) {
            $heroImage = '/Assets/Img/hero.png';
        } elseif ($request->hasFile('hero_image_file')) {
            $storedPath = $request->file('hero_image_file')->store('hero', 'public');
            $heroImage = asset('storage/'.$storedPath);
        } elseif (! empty($validated['hero_image_url'])) {
            $heroImage = trim($validated['hero_image_url']);
        }

        $footerServices = array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $validated['footer_services_text']) ?: []),
            static fn (string $line): bool => $line !== ''
        ));

        $profile->update([
            'name' => $validated['name'],
            'short_name' => $validated['short_name'],
            'tagline' => $validated['tagline'],
            'about' => $validated['about'],
            'address_short' => $validated['address_short'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'operational_hours' => $validated['operational_hours'],
            'logo_url' => $logoUrl,
            'hero_image' => $heroImage,
            'footer_description' => $validated['footer_description'],
            'footer_services_title' => $validated['footer_services_title'],
            'footer_services' => $footerServices,
            'footer_copyright' => $validated['footer_copyright'],
        ]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'vihara-profile'])
            ->with('success', 'Profil, gambar hero, dan konten footer wihara pada halaman landing page berhasil diperbarui.');
    }

    /**
     * Show the create form for a new Wihara facility / information card.
     */
    public function createViharaFacility(): View
    {
        return $this->renderManagementView([
            'pageMode' => 'create_vihara_facility',
            'activeTab' => 'vihara-profile',
        ]);
    }

    /**
     * Store a newly created Wihara facility / information card.
     */
    public function storeViharaFacility(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:1000'],
            'icon' => ['required', 'string', Rule::in(array_keys(ViharaProfile::availableIcons()))],
        ]);

        $profile = ViharaProfile::current();
        $facilities = is_array($profile->facilities) ? $profile->facilities : [];

        $facilities[] = [
            'id' => Str::slug($validated['title']).'-'.Str::lower(Str::random(4)),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'icon' => $validated['icon'],
        ];

        $profile->update(['facilities' => array_values($facilities)]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'vihara-profile'])
            ->with('success', "Informasi/fasilitas '{$validated['title']}' berhasil ditambahkan ke halaman landing page.");
    }

    /**
     * Show the edit form for an existing Wihara facility / information card.
     */
    public function editViharaFacility(string $facilityId): View
    {
        $profile = ViharaProfile::current();
        $facilities = is_array($profile->facilities) ? $profile->facilities : [];
        $facility = collect($facilities)->firstWhere('id', $facilityId);

        abort_if(! is_array($facility), 404);

        return $this->renderManagementView([
            'pageMode' => 'edit_vihara_facility',
            'activeTab' => 'vihara-profile',
            'editingRecord' => (object) $facility,
        ]);
    }

    /**
     * Update an existing Wihara facility / information card.
     */
    public function updateViharaFacility(Request $request, string $facilityId): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:1000'],
            'icon' => ['required', 'string', Rule::in(array_keys(ViharaProfile::availableIcons()))],
        ]);

        $profile = ViharaProfile::current();
        $facilities = is_array($profile->facilities) ? $profile->facilities : [];
        $found = false;

        foreach ($facilities as $index => $item) {
            if (($item['id'] ?? null) === $facilityId) {
                $facilities[$index] = [
                    'id' => $facilityId,
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'icon' => $validated['icon'],
                ];
                $found = true;
                break;
            }
        }

        abort_if(! $found, 404);

        $profile->update(['facilities' => array_values($facilities)]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'vihara-profile'])
            ->with('success', "Informasi/fasilitas '{$validated['title']}' berhasil diperbarui.");
    }

    /**
     * Delete a Wihara facility / information card.
     */
    public function destroyViharaFacility(string $facilityId): RedirectResponse
    {
        $profile = ViharaProfile::current();
        $facilities = is_array($profile->facilities) ? $profile->facilities : [];
        $removedTitle = null;

        $remaining = [];
        foreach ($facilities as $item) {
            if (($item['id'] ?? null) === $facilityId) {
                $removedTitle = $item['title'] ?? 'Informasi';

                continue;
            }
            $remaining[] = $item;
        }

        abort_if($removedTitle === null, 404);

        $profile->update(['facilities' => array_values($remaining)]);

        return redirect()
            ->route('management.dashboard', ['tab' => 'vihara-profile'])
            ->with('success', "Informasi/fasilitas '{$removedTitle}' berhasil dihapus dari landing page.");
    }

    /**
     * Store a new custom category for any management module (supports both form submit and inline AJAX).
     */
    public function storeCategory(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'module' => ['required', 'string', Rule::in(array_keys(Category::defaultsByModule()))],
            'name' => ['required', 'string', 'max:100'],
        ]);

        $module = $validated['module'];
        $name = trim($validated['name']);
        $permissionMap = [
            'receipts' => 'manage_receipts',
            'documents' => 'manage_documents',
            'marriages' => 'manage_marriages',
            'schedules' => 'manage_schedules',
            'galleries' => 'manage_galleries',
            'articles' => 'manage_articles',
        ];

        $requiredPermission = $permissionMap[$module] ?? null;
        if ($requiredPermission !== null && ! $request->user()?->hasPermission($requiredPermission)) {
            abort(403, 'Anda tidak memiliki izin untuk menambah kategori pada modul ini.');
        }

        $category = Category::ensureExists($module, $name);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $category->id,
                'module' => $category->module,
                'name' => $category->name,
                'message' => "Kategori '{$category->name}' berhasil ditambahkan.",
            ], 201);
        }

        return redirect()
            ->route('management.dashboard', ['tab' => $module])
            ->with('success', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    /**
     * Delete a custom category from a management module.
     */
    public function destroyCategory(Request $request, Category $category): RedirectResponse
    {
        $module = $category->module;
        $permissionMap = [
            'receipts' => 'manage_receipts',
            'documents' => 'manage_documents',
            'marriages' => 'manage_marriages',
            'schedules' => 'manage_schedules',
            'galleries' => 'manage_galleries',
            'articles' => 'manage_articles',
        ];

        $requiredPermission = $permissionMap[$module] ?? null;
        if ($requiredPermission !== null && ! $request->user()?->hasPermission($requiredPermission)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus kategori pada modul ini.');
        }

        $name = $category->name;
        $category->delete();

        return redirect()
            ->route('management.dashboard', ['tab' => $module])
            ->with('success', "Kategori '{$name}' berhasil dihapus.");
    }
}
