<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Management Portal — Wihara Mahabodhi Buddhist Centre</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fdf2f2',
                            100: '#fbe6e6',
                            600: '#8b1e1e',
                            700: '#741818',
                            800: '#5e1414',
                            900: '#4a1010',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        html, body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Clean custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }

        /* Responsive Mobile Stacked Table Cards on Android & iPhone (< 768px) */
        @media screen and (max-width: 767px) {
            .mgmt-responsive-table thead {
                display: none;
            }
            .mgmt-responsive-table,
            .mgmt-responsive-table tbody {
                display: block;
                width: 100%;
            }
            .mgmt-responsive-table tbody tr {
                display: block;
                width: 100%;
                padding: 0.875rem 1rem;
                box-sizing: border-box;
            }
            .mgmt-responsive-table tbody tr.hidden {
                display: none !important;
            }
            .mgmt-responsive-table tbody td {
                display: block;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0.375rem 0 !important;
                text-align: left !important;
                box-sizing: border-box;
                overflow-wrap: anywhere;
            }
            .mgmt-responsive-table tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 0.6875rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: #64748b;
                margin-bottom: 0.2rem;
            }
            .mgmt-responsive-table tbody td.mgmt-actions-cell {
                padding-top: 0.65rem !important;
                margin-top: 0.35rem;
                border-top: 1px dashed #e2e8f0;
            }
            .mgmt-responsive-table tbody td.mgmt-actions-cell > div {
                justify-content: flex-start !important;
            }
        }

        /* Dedicated Single-Page A4 Print Styling (Strictly 1 Sheet) */
        @page {
            size: A4 portrait;
            margin: 6mm 9mm;
        }

        @media print {
            html, body {
                width: 210mm !important;
                height: auto !important;
                max-height: 282mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #FFFFFF !important;
                color: #0f172a !important;
                overflow: hidden !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print,
            #importExcelModal {
                display: none !important;
            }
            #a4PrintWrapper {
                display: block !important;
                position: static !important;
                width: 100% !important;
                max-height: 282mm !important;
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: hidden !important;
            }
            .a4-sheet {
                width: 100% !important;
                max-width: 100% !important;
                min-height: 0 !important;
                height: auto !important;
                max-height: 280mm !important;
                box-shadow: none !important;
                border: none !important;
                padding: 2mm 4mm !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
        }
    </style>
</head>
<body class="min-h-full bg-slate-50 text-slate-900 antialiased selection:bg-slate-900 selection:text-white">

@if (!empty($isLoginPage))
    <!-- ========================================================= -->
    <!-- STATE 1: CLEAN MODERN LOGIN PAGE                          -->
    <!-- ========================================================= -->
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
            <div class="bg-white py-8 px-6 shadow-sm rounded-2xl border border-slate-200/80 sm:px-10 space-y-6">
                <div class="text-center border-b border-slate-100 pb-5">
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">
                        Portal Pengurus
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Harap login untuk melanjutkan
                    </p>
                </div>

                @if (session('success'))
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <div>&bull; {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="loginEmail" class="block text-xs font-semibold text-slate-700 mb-1.5 whitespace-nowrap">Email Pengurus</label>
                        <input
                            type="email"
                            id="loginEmail"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition"
                            placeholder="Masukkan email pengurus"
                        >
                    </div>

                    <div>
                        <label for="loginPassword" class="block text-xs font-semibold text-slate-700 mb-1.5 whitespace-nowrap">Kata Sandi</label>
                        <input
                            type="password"
                            id="loginPassword"
                            name="password"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition"
                            placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                        >
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <label class="inline-flex items-center gap-2 cursor-pointer whitespace-nowrap">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                            <span>Ingat perangkat ini</span>
                        </label>
                        <a href="{{ route('landing') }}" class="font-medium text-slate-600 hover:text-slate-900 whitespace-nowrap">
                            &larr; Ke Halaman Publik
                        </a>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm shadow-xs transition whitespace-nowrap">
                        Masuk ke Dashboard
                    </button>
                </form>
            </div>
        </div>
    </div>
@else
    <!-- ========================================================= -->
    <!-- STATE 2: FIXED SIDEBAR MANAGEMENT DASHBOARD               -->
    <!-- ========================================================= -->
    @php
        /** @var \App\Models\User|null $currentUser */
        $currentUser = auth()->user();
        $canReceipts = $currentUser?->hasPermission('manage_receipts') ?? false;
        $canDocuments = $currentUser?->hasPermission('manage_documents') ?? false;
        $canMarriages = $currentUser?->hasPermission('manage_marriages') ?? false;
        $canSchedules = $currentUser?->hasPermission('manage_schedules') ?? false;
        $canGalleries = $currentUser?->hasPermission('manage_galleries') ?? false;
        $canArticles = $currentUser?->hasPermission('manage_articles') ?? false;
        $canRoles = $currentUser?->hasPermission('manage_roles') ?? false;
        $currentMode = $pageMode ?? 'index';
        $currentTab = $activeTab ?? 'overview';
    @endphp

    <div class="no-print min-h-screen bg-slate-50 w-full max-w-full overflow-x-hidden">
        <!-- Mobile Sidebar Backdrop -->
        <div id="sidebarBackdrop" onclick="toggleMobileSidebar()" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs hidden lg:hidden"></div>

        <!-- ===================================================== -->
        <!-- FIXED LEFT SIDEBAR NAVIGATION (TIDAK IKUT KE-SCROLL)  -->
        <!-- ===================================================== -->
        <aside id="appSidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-300 border-r border-slate-800 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out">
            <!-- Brand Header -->
            <div class="py-5 px-5 flex items-center justify-between border-b border-slate-800 shrink-0">
                <a href="{{ route('management.dashboard') }}" class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-sm tracking-wider shrink-0 shadow-xs">
                        <i class="fa-solid fa-dharmachakra text-base"></i>
                    </div>
                    <div class="leading-tight min-w-0 space-y-0.5">
                        <div class="text-sm font-bold text-white tracking-tight">Wihara Mahabodhi</div>
                        <div class="text-xs text-slate-400">Management Portal</div>
                    </div>
                </a>
                <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white shrink-0">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Sidebar Menu Links (No Truncation, No Count Badges, Full Text Display) -->
            <nav class="flex-1 px-3.5 py-5 space-y-5 overflow-y-auto font-medium">
                <!-- Group: Utama -->
                <div class="space-y-1">
                    <div class="px-3 pb-1.5 text-xs font-bold uppercase tracking-wider text-slate-400">
                        Menu Utama
                    </div>
                    @if ($currentMode === 'index')
                        <button type="button" data-tab-target="overview" class="mgmt-tab-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left transition text-sm font-semibold">
                            <i class="fa-solid fa-gauge-high w-5 text-center text-base shrink-0"></i>
                            <span class="leading-snug">Ringkasan Operasional</span>
                        </button>
                    @else
                        <a href="{{ route('management.dashboard', ['tab' => 'overview']) }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ $currentTab === 'overview' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <i class="fa-solid fa-gauge-high w-5 text-center text-base shrink-0"></i>
                            <span class="leading-snug">Ringkasan Operasional</span>
                        </a>
                    @endif
                </div>

                <!-- Group: Administrasi & Dokumen -->
                @if ($canReceipts || $canDocuments || $canMarriages)
                <div class="space-y-1">
                    <div class="px-3 pb-1.5 text-xs font-bold uppercase tracking-wider text-slate-400">
                        Administrasi Sekretariat
                    </div>
                    @if ($canReceipts)
                        @if ($currentMode === 'index')
                            <button type="button" data-tab-target="receipts" class="mgmt-tab-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left transition text-sm font-semibold">
                                <i class="fa-solid fa-receipt w-5 text-center text-base shrink-0"></i>
                                <span class="leading-snug">Tanda Terima &amp; Ucapan</span>
                            </button>
                        @else
                            <a href="{{ route('management.dashboard', ['tab' => 'receipts']) }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ $currentTab === 'receipts' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                <i class="fa-solid fa-receipt w-5 text-center text-base shrink-0"></i>
                                <span class="leading-snug">Tanda Terima &amp; Ucapan</span>
                            </a>
                        @endif
                    @endif
                    @if ($canDocuments)
                        @if ($currentMode === 'index')
                            <button type="button" data-tab-target="documents" class="mgmt-tab-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left transition text-sm font-semibold">
                                <i class="fa-solid fa-folder-open w-5 text-center text-base shrink-0"></i>
                                <span class="leading-snug">Arsip Persuratan &amp; Dokumen</span>
                            </button>
                        @else
                            <a href="{{ route('management.dashboard', ['tab' => 'documents']) }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ $currentTab === 'documents' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                <i class="fa-solid fa-folder-open w-5 text-center text-base shrink-0"></i>
                                <span class="leading-snug">Arsip Persuratan &amp; Dokumen</span>
                            </a>
                        @endif
                    @endif
                    @if ($canMarriages)
                        @if ($currentMode === 'index')
                            <button type="button" data-tab-target="marriages" class="mgmt-tab-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left transition text-sm font-semibold">
                                <i class="fa-solid fa-ring w-5 text-center text-base shrink-0"></i>
                                <span class="leading-snug">Surat Nikah &amp; Pemberkatan</span>
                            </button>
                        @else
                            <a href="{{ route('management.dashboard', ['tab' => 'marriages']) }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ $currentTab === 'marriages' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                <i class="fa-solid fa-ring w-5 text-center text-base shrink-0"></i>
                                <span class="leading-snug">Surat Nikah &amp; Pemberkatan</span>
                            </a>
                        @endif
                    @endif
                </div>
                @endif

                <!-- Group: Publikasi Landing Page -->
                @if ($canSchedules || $canGalleries || $canArticles)
                <div class="space-y-1">
                    <div class="px-3 pb-1.5 text-xs font-bold uppercase tracking-wider text-slate-400">
                        Konten Landing Page
                    </div>
                    @if ($canSchedules)
                        @if ($currentMode === 'index')
                            <button type="button" data-tab-target="schedules" class="mgmt-tab-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left transition text-sm font-semibold">
                                <i class="fa-solid fa-calendar-days w-5 text-center text-base shrink-0"></i>
                                <span class="leading-snug">Jadwal Ibadah &amp; Kegiatan</span>
                            </button>
                        @else
                            <a href="{{ route('management.dashboard', ['tab' => 'schedules']) }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ $currentTab === 'schedules' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                <i class="fa-solid fa-calendar-days w-5 text-center text-base shrink-0"></i>
                                <span class="leading-snug">Jadwal Ibadah &amp; Kegiatan</span>
                            </a>
                        @endif
                    @endif
                    @if ($canGalleries)
                        @if ($currentMode === 'index')
                            <button type="button" data-tab-target="galleries" class="mgmt-tab-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left transition text-sm font-semibold">
                                <i class="fa-solid fa-images w-5 text-center text-base shrink-0"></i>
                                <span class="leading-snug">Galeri Foto &amp; Video</span>
                            </button>
                        @else
                            <a href="{{ route('management.dashboard', ['tab' => 'galleries']) }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ $currentTab === 'galleries' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                <i class="fa-solid fa-images w-5 text-center text-base shrink-0"></i>
                                <span class="leading-snug">Galeri Foto &amp; Video</span>
                            </a>
                        @endif
                    @endif
                    @if ($canArticles)
                        @if ($currentMode === 'index')
                            <button type="button" data-tab-target="articles" class="mgmt-tab-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left transition text-sm font-semibold">
                                <i class="fa-solid fa-newspaper w-5 text-center text-base shrink-0"></i>
                                <span class="leading-snug">Berita &amp; Artikel Dharma</span>
                            </button>
                        @else
                            <a href="{{ route('management.dashboard', ['tab' => 'articles']) }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ $currentTab === 'articles' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                <i class="fa-solid fa-newspaper w-5 text-center text-base shrink-0"></i>
                                <span class="leading-snug">Berita &amp; Artikel Dharma</span>
                            </a>
                        @endif
                    @endif
                </div>
                @endif

                <!-- Group: Hak Akses -->
                @if ($canRoles)
                <div class="space-y-1">
                    <div class="px-3 pb-1.5 text-xs font-bold uppercase tracking-wider text-slate-400">
                        Pengaturan &amp; Akses
                    </div>
                    @if ($currentMode === 'index')
                        <button type="button" data-tab-target="roles" class="mgmt-tab-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left transition text-sm font-semibold">
                            <i class="fa-solid fa-users-gear w-5 text-center text-base shrink-0"></i>
                            <span class="leading-snug">Role &amp; Akun Pengurus</span>
                        </button>
                    @else
                        <a href="{{ route('management.dashboard', ['tab' => 'roles']) }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ $currentTab === 'roles' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <i class="fa-solid fa-users-gear w-5 text-center text-base shrink-0"></i>
                            <span class="leading-snug">Role &amp; Akun Pengurus</span>
                        </a>
                    @endif
                </div>
                @endif
            </nav>

            <!-- Sidebar Minimal Footer -->
            <div class="p-4 border-t border-slate-800 bg-slate-900/60 shrink-0">
                <a href="{{ route('landing') }}" target="_blank" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-800 text-slate-300 hover:text-white text-xs font-semibold transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-globe text-slate-400"></i>
                        <span>Lihat Web Publik</span>
                    </span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                </a>
            </div>
        </aside>

        <!-- ===================================================== -->
        <!-- MAIN WORKSPACE AREA (OFFSET BY lg:pl-72)              -->
        <!-- ===================================================== -->
        <div class="lg:pl-72 min-h-screen flex flex-col min-w-0 max-w-full overflow-x-hidden">
            <!-- Sticky Top Bar with Spacious Y-Padding & Margin -->
            <header class="py-3.5 sm:py-5 mb-2 bg-white border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-2.5 sm:gap-4 sticky top-0 z-20 shadow-2xs">
                <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0 my-0.5">
                    <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 shrink-0" aria-label="Buka Menu">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <div class="min-w-0 space-y-1">
                        <h1 id="topbarTitle" class="text-sm sm:text-xl font-bold text-slate-900 leading-snug line-clamp-1 sm:line-clamp-none break-words">Ringkasan Operasional Sekretariat</h1>
                        <p class="text-xs text-slate-500 hidden sm:block leading-snug">Wihara Mahabodhi Buddhist Centre &bull; Jl. Seroja Timur No. 9-11 Semarang</p>
                    </div>
                </div>

                <!-- Right Header: User Profile Dropdown -->
                <div class="relative shrink-0" id="profileDropdownContainer">
                    <button
                        type="button"
                        id="profileDropdownBtn"
                        onclick="toggleProfileDropdown()"
                        class="flex items-center gap-2 sm:gap-3 p-1.5 sm:px-3 sm:py-2 rounded-xl hover:bg-slate-100 transition border border-transparent hover:border-slate-200"
                    >
                        <div class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-xs ring-2 ring-slate-100">
                            {{ substr($currentUser->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="hidden sm:block text-left min-w-0">
                            <div class="text-sm font-bold text-slate-900 leading-tight">{{ $currentUser->name ?? 'Pengurus' }}</div>
                            <div class="text-xs text-slate-500 leading-tight">{{ $currentUser->role?->name ?? ($currentUser->position ?? 'Staff') }}</div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 shrink-0"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="profileDropdownMenu" class="hidden absolute right-0 top-full mt-2 w-64 sm:w-72 max-w-[calc(100vw-2rem)] rounded-2xl bg-white shadow-xl border border-slate-200 py-2 z-50 text-xs">
                        <div class="px-4 py-3.5 border-b border-slate-100 bg-slate-50/70 rounded-t-xl">
                            <div class="font-bold text-slate-900 text-sm truncate">{{ $currentUser->name ?? 'Pengurus Vihara' }}</div>
                            <div class="text-slate-500 text-xs truncate mt-0.5">{{ $currentUser->email ?? '' }}</div>
                            <span class="inline-flex items-center gap-1.5 mt-2 px-2.5 py-1 rounded-lg bg-slate-200 text-slate-800 font-semibold text-xs">
                                <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                {{ $currentUser->role?->name ?? 'Pengurus' }}
                            </span>
                        </div>

                        <div class="py-1.5">
                            <button
                                type="button"
                                onclick="switchManagementTab('profile'); closeProfileDropdown();"
                                class="w-full flex items-center gap-3 px-4 py-3 text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition text-left text-sm font-medium"
                            >
                                <i class="fa-solid fa-user-gear text-slate-500 w-5 text-center text-base"></i>
                                <span>Pengaturan Akun / Profil</span>
                            </button>

                            <a
                                href="{{ route('landing') }}"
                                target="_blank"
                                class="flex items-center gap-3 px-4 py-3 text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition text-sm font-medium"
                            >
                                <i class="fa-solid fa-globe text-slate-500 w-5 text-center text-base"></i>
                                <span>Lihat Web Publik &nearr;</span>
                            </a>
                        </div>

                        <div class="border-t border-slate-100 pt-1.5">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-rose-600 hover:bg-rose-50 transition font-semibold text-left text-sm"
                                >
                                    <i class="fa-solid fa-arrow-right-from-bracket text-rose-500 w-5 text-center text-base"></i>
                                    <span>Keluar (Logout)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Body (Full Width, No Horizontal Scroll Needed) -->
            <main class="flex-1 p-3.5 sm:p-6 lg:p-8 w-full min-w-0 max-w-full overflow-x-hidden space-y-6">

                <!-- Alerts -->
                @if (session('success'))
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-medium flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-600 shrink-0"></span>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm font-medium">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs space-y-1">
                        <div class="font-semibold">Mohon periksa kembali input Anda:</div>
                        @foreach ($errors->all() as $error)
                            <div>&bull; {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @if ($currentMode !== 'index')
                    <!-- ================================================= -->
                    <!-- DEDICATED CREATE / EDIT PAGES                     -->
                    <!-- ================================================= -->

                    @if (in_array($currentMode, ['create_receipt', 'edit_receipt'], true))
                        @php($isEdit = $currentMode === 'edit_receipt')
                        <div class="max-w-4xl mx-auto space-y-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2.5 py-1 rounded-md border border-amber-200 inline-block">{{ $isEdit ? 'Mode Edit Data' : 'Input Baru' }}</span>
                                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug mt-2 flex items-center gap-2">
                                        <i class="fa-solid fa-receipt text-amber-600 shrink-0"></i>
                                        <span class="break-words">{{ $isEdit ? 'Edit Tanda Terima '.$editingRecord->receipt_number : 'Buat Tanda Terima & Ucapan Terima Kasih' }}</span>
                                    </h2>
                                </div>
                                <a href="{{ route('management.dashboard', ['tab' => 'receipts']) }}" class="self-start sm:self-auto px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs sm:text-sm font-bold transition shrink-0 flex items-center gap-2 shadow-2xs">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>Kembali</span>
                                </a>
                            </div>

                            <form
                                method="POST"
                                action="{{ $isEdit ? route('management.receipts.update', $editingRecord) : route('management.receipts.store') }}"
                                class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-8 shadow-2xs space-y-5"
                            >
                                @csrf
                                @if ($isEdit) @method('PUT') @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nomor Tanda Terima *</label>
                                        <input type="text" name="receipt_number" value="{{ old('receipt_number', $isEdit ? $editingRecord->receipt_number : $nextReceiptNumber) }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Jenis Penerimaan *</label>
                                        <select name="receipt_type" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900 font-medium">
                                            @foreach (['Bantuan Dana', 'Barang', 'Surat / Dokumen'] as $opt)
                                                <option value="{{ $opt }}" @selected(old('receipt_type', $isEdit ? $editingRecord->receipt_type : 'Bantuan Dana') === $opt)>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Tanggal Penerimaan *</label>
                                        <input type="date" name="receipt_date" value="{{ old('receipt_date', $isEdit ? $editingRecord->receipt_date->format('Y-m-d') : now()->toDateString()) }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Petugas Penerima *</label>
                                        <input type="text" name="receiver_name" value="{{ old('receiver_name', $isEdit ? $editingRecord->receiver_name : ($currentUser?->name ?? 'Sekretariat Wihara Mahabodhi')) }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Penyerah / Donatur *</label>
                                        <input type="text" name="donor_name" value="{{ old('donor_name', $isEdit ? $editingRecord->donor_name : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Instansi / Alamat</label>
                                        <input type="text" name="donor_institution" value="{{ old('donor_institution', $isEdit ? $editingRecord->donor_institution : '') }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Kontak / No. Telepon</label>
                                        <input type="text" name="donor_contact" value="{{ old('donor_contact', $isEdit ? $editingRecord->donor_contact : '') }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Jumlah / Kuantitas *</label>
                                        <input type="text" name="quantity" value="{{ old('quantity', $isEdit ? $editingRecord->quantity : '1 Paket') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Uraian Bantuan / Barang / Perihal Surat *</label>
                                        <input type="text" name="item_name" value="{{ old('item_name', $isEdit ? $editingRecord->item_name : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nominal Rp (Opsional)</label>
                                        <input type="number" step="0.01" min="0" name="nominal" value="{{ old('nominal', $isEdit ? $editingRecord->nominal : '') }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Catatan</label>
                                        <input type="text" name="notes" value="{{ old('notes', $isEdit ? $editingRecord->notes : '') }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Pesan Ucapan Terima Kasih / Anumodana</label>
                                        <textarea name="thank_you_message" rows="3" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">{{ old('thank_you_message', $isEdit ? $editingRecord->thank_you_message : '') }}</textarea>
                                    </div>
                                </div>

                                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 sm:gap-3 pt-5 border-t border-slate-100">
                                    <a href="{{ route('management.dashboard', ['tab' => 'receipts']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm text-center">Batal</a>
                                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm sm:text-base transition flex items-center justify-center gap-2 shadow-xs">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan & Terbitkan Tanda Terima' }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                    @elseif (in_array($currentMode, ['create_document', 'edit_document'], true))
                        @php($isEdit = $currentMode === 'edit_document')
                        <div class="max-w-4xl mx-auto space-y-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md border border-blue-200 inline-block">{{ $isEdit ? 'Pembaruan Arsip Dokumen' : 'Registrasi Arsip Persuratan' }}</span>
                                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug mt-2 flex items-center gap-2">
                                        <i class="fa-solid fa-folder-open text-blue-600 shrink-0"></i>
                                        <span class="break-words">{{ $isEdit ? 'Edit Arsip Dokumen '.$editingRecord->document_number : 'Registrasi Arsip Surat & Dokumen Resmi' }}</span>
                                    </h2>
                                </div>
                                <a href="{{ route('management.dashboard', ['tab' => 'documents']) }}" class="self-start sm:self-auto px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs sm:text-sm font-bold transition shrink-0 flex items-center gap-2 shadow-2xs">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>Kembali</span>
                                </a>
                            </div>

                            <form
                                method="POST"
                                action="{{ $isEdit ? route('management.documents.update', $editingRecord) : route('management.documents.store') }}"
                                enctype="multipart/form-data"
                                class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-8 shadow-2xs space-y-5"
                            >
                                @csrf
                                @if ($isEdit) @method('PUT') @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nomor Surat / Dokumen *</label>
                                        <input type="text" name="document_number" value="{{ old('document_number', $isEdit ? $editingRecord->document_number : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Kategori Dokumen *</label>
                                        <select name="category" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900 font-medium">
                                            @foreach (['Surat Masuk', 'Surat Keluar', 'Dokumen Umum A4', 'SK / Piagam'] as $opt)
                                                <option value="{{ $opt }}" @selected(old('category', $isEdit ? $editingRecord->category : 'Surat Masuk') === $opt)>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Tanggal Surat *</label>
                                        <input type="date" name="document_date" value="{{ old('document_date', $isEdit ? $editingRecord->document_date->format('Y-m-d') : now()->toDateString()) }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Tanggal Diterima</label>
                                        <input type="date" name="received_date" value="{{ old('received_date', $isEdit ? $editingRecord->received_date?->format('Y-m-d') : now()->toDateString()) }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Judul / Perihal Dokumen *</label>
                                        <input type="text" name="title" value="{{ old('title', $isEdit ? $editingRecord->title : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Pengirim *</label>
                                        <input type="text" name="sender" value="{{ old('sender', $isEdit ? $editingRecord->sender : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Penerima / Tujuan *</label>
                                        <input type="text" name="recipient" value="{{ old('recipient', $isEdit ? $editingRecord->recipient : 'Pengurus Wihara Mahabodhi Buddhist Centre') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Ukuran Kertas</label>
                                        <input type="text" name="paper_size" value="{{ old('paper_size', $isEdit ? $editingRecord->paper_size : 'A4') }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Kode Map Arsip A4</label>
                                        <input type="text" name="archive_code" value="{{ old('archive_code', $isEdit ? $editingRecord->archive_code : 'ARS-A4-01') }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Lampiran Berkas Digital (PDF/DOCX/JPG)</label>
                                        <input type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full text-xs sm:text-sm text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border file:border-slate-300 file:bg-slate-50 file:text-slate-700">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Ringkasan Isi Surat / Dokumen A4 *</label>
                                        <textarea name="summary" rows="3" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">{{ old('summary', $isEdit ? $editingRecord->summary : '') }}</textarea>
                                    </div>
                                </div>

                                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 sm:gap-3 pt-5 border-t border-slate-100">
                                    <a href="{{ route('management.dashboard', ['tab' => 'documents']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm text-center">Batal</a>
                                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm sm:text-base transition flex items-center justify-center gap-2 shadow-xs">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Dokumen A4' }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                    @elseif (in_array($currentMode, ['create_marriage', 'edit_marriage'], true))
                        @php($isEdit = $currentMode === 'edit_marriage')
                        <div class="max-w-5xl mx-auto space-y-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold uppercase tracking-wider text-rose-700 bg-rose-50 px-2.5 py-1 rounded-md border border-rose-200 inline-block">{{ $isEdit ? 'Mode Edit Surat Nikah' : 'Registrasi Pemberkatan Perkawinan' }}</span>
                                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug mt-2 flex items-center gap-2.5">
                                        <i class="fa-solid fa-ring text-rose-600 shrink-0"></i>
                                        <span class="break-words">{{ $isEdit ? 'Edit Surat Nikah '.$editingRecord->certificate_number : 'Buat Administrasi Surat Nikah & Pemberkatan Perkawinan Buddhis' }}</span>
                                    </h2>
                                </div>
                                <a href="{{ route('management.dashboard', ['tab' => 'marriages']) }}" class="self-start sm:self-auto px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs sm:text-sm font-bold transition shrink-0 flex items-center gap-2 shadow-2xs">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>Kembali</span>
                                </a>
                            </div>

                            <form
                                method="POST"
                                action="{{ $isEdit ? route('management.marriages.update', $editingRecord) : route('management.marriages.store') }}"
                                enctype="multipart/form-data"
                                class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-8 shadow-2xs space-y-8"
                            >
                                @csrf
                                @if ($isEdit) @method('PUT') @endif

                                <!-- Section 1: Data Surat & Jadwal Pemberkatan -->
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2.5 pb-2.5 border-b border-slate-200">
                                        <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-xs">1</span>
                                        <h3 class="text-base font-bold text-slate-900">Data Nomor Surat &amp; Jadwal Pemberkatan Perkawinan</h3>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nomor Surat / Akta Pemberkatan *</label>
                                            <input type="text" name="certificate_number" value="{{ old('certificate_number', $isEdit ? $editingRecord->certificate_number : ($nextMarriageNumber ?? '001/SPP-BDH/WMBC/XI/2026')) }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Jenis Dokumen Nikah *</label>
                                            <select name="document_type" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900 font-medium">
                                                @foreach (['Surat Pemberkatan Perkawinan', 'Berita Acara Pemberkatan', 'Sertifikat Bimbingan Pra-Nikah', 'Surat Pengantar Dukcapil'] as $opt)
                                                    <option value="{{ $opt }}" @selected(old('document_type', $isEdit ? $editingRecord->document_type : 'Surat Pemberkatan Perkawinan') === $opt)>{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Status Administrasi *</label>
                                            <select name="status" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900 font-medium">
                                                @foreach (['Terdaftar & Verifikasi Berkas', 'Selesai Pemberkatan', 'Tercatat di Dukcapil'] as $opt)
                                                    <option value="{{ $opt }}" @selected(old('status', $isEdit ? $editingRecord->status : 'Selesai Pemberkatan') === $opt)>{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Tanggal Pendaftaran Berkas *</label>
                                            <input type="date" name="registration_date" value="{{ old('registration_date', $isEdit ? $editingRecord->registration_date->format('Y-m-d') : now()->toDateString()) }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Tanggal Pemberkatan Perkawinan *</label>
                                            <input type="date" name="wedding_date" value="{{ old('wedding_date', $isEdit ? $editingRecord->wedding_date->format('Y-m-d') : now()->toDateString()) }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Waktu Pemberkatan *</label>
                                            <input type="text" name="wedding_time" value="{{ old('wedding_time', $isEdit ? $editingRecord->wedding_time : '09:00 - 11:00 WIB') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Tempat / Altar Pemberkatan *</label>
                                            <input type="text" name="wedding_location" value="{{ old('wedding_location', $isEdit ? $editingRecord->wedding_location : 'Dharmasala Utama Wihara Mahabodhi Buddhist Centre Semarang') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div class="flex items-end pb-2">
                                            <label class="inline-flex items-center gap-2.5 cursor-pointer font-semibold text-sm text-slate-800">
                                                <input type="checkbox" name="premarital_course_done" value="1" @checked(old('premarital_course_done', $isEdit ? $editingRecord->premarital_course_done : true)) class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                                <span>Lulus Bimbingan Pra-Nikah Buddhis</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: Data Mempelai Pria -->
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2.5 pb-2.5 border-b border-slate-200">
                                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">2</span>
                                        <h3 class="text-base font-bold text-slate-900">Biodata Mempelai Pria (Upasaka)</h3>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Lengkap Mempelai Pria (Sesuai KTP) *</label>
                                            <input type="text" name="groom_name" value="{{ old('groom_name', $isEdit ? $editingRecord->groom_name : '') }}" required placeholder="Contoh: Kevin Wijaya Kusuma, S.Kom." class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Buddhis Pria (Opsional)</label>
                                            <input type="text" name="groom_buddhist_name" value="{{ old('groom_buddhist_name', $isEdit ? $editingRecord->groom_buddhist_name : '') }}" placeholder="Contoh: Upasaka Dhammapala" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">NIK KTP Mempelai Pria</label>
                                            <input type="text" name="groom_nik" value="{{ old('groom_nik', $isEdit ? $editingRecord->groom_nik : '') }}" placeholder="16 digit NIK KTP" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Tempat, Tanggal Lahir Pria *</label>
                                            <input type="text" name="groom_birth_info" value="{{ old('groom_birth_info', $isEdit ? $editingRecord->groom_birth_info : '') }}" required placeholder="Contoh: Semarang, 14 Mei 1996" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Ayah Kandung Pria *</label>
                                            <input type="text" name="groom_father_name" value="{{ old('groom_father_name', $isEdit ? $editingRecord->groom_father_name : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Ibu Kandung Pria *</label>
                                            <input type="text" name="groom_mother_name" value="{{ old('groom_mother_name', $isEdit ? $editingRecord->groom_mother_name : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Alamat Lengkap Mempelai Pria *</label>
                                            <input type="text" name="groom_address" value="{{ old('groom_address', $isEdit ? $editingRecord->groom_address : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 3: Data Mempelai Wanita -->
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2.5 pb-2.5 border-b border-slate-200">
                                        <span class="w-7 h-7 rounded-lg bg-pink-100 text-pink-700 flex items-center justify-center font-bold text-xs">3</span>
                                        <h3 class="text-base font-bold text-slate-900">Biodata Mempelai Wanita (Upasika)</h3>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Lengkap Mempelai Wanita (Sesuai KTP) *</label>
                                            <input type="text" name="bride_name" value="{{ old('bride_name', $isEdit ? $editingRecord->bride_name : '') }}" required placeholder="Contoh: Clara Anastasia Tan, S.E." class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Buddhis Wanita (Opsional)</label>
                                            <input type="text" name="bride_buddhist_name" value="{{ old('bride_buddhist_name', $isEdit ? $editingRecord->bride_buddhist_name : '') }}" placeholder="Contoh: Upasika Metta Devi" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">NIK KTP Mempelai Wanita</label>
                                            <input type="text" name="bride_nik" value="{{ old('bride_nik', $isEdit ? $editingRecord->bride_nik : '') }}" placeholder="16 digit NIK KTP" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Tempat, Tanggal Lahir Wanita *</label>
                                            <input type="text" name="bride_birth_info" value="{{ old('bride_birth_info', $isEdit ? $editingRecord->bride_birth_info : '') }}" required placeholder="Contoh: Semarang, 22 Agustus 1998" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Ayah Kandung Wanita *</label>
                                            <input type="text" name="bride_father_name" value="{{ old('bride_father_name', $isEdit ? $editingRecord->bride_father_name : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Ibu Kandung Wanita *</label>
                                            <input type="text" name="bride_mother_name" value="{{ old('bride_mother_name', $isEdit ? $editingRecord->bride_mother_name : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Alamat Lengkap Mempelai Wanita *</label>
                                            <input type="text" name="bride_address" value="{{ old('bride_address', $isEdit ? $editingRecord->bride_address : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 4: Pandita, Saksi & Berkas Persyaratan -->
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2.5 pb-2.5 border-b border-slate-200">
                                        <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs">4</span>
                                        <h3 class="text-base font-bold text-slate-900">Rohaniwan / Pandita Pemimpin Pemberkatan, Saksi &amp; Berkas</h3>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Pandita / Rohaniwan Pemimpin *</label>
                                            <input type="text" name="officiant_name" value="{{ old('officiant_name', $isEdit ? $editingRecord->officiant_name : 'Romo Pandita Dharmaputra, S.Ag., M.Pd.B.') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Saksi I (Pihak Pria) *</label>
                                            <input type="text" name="witness_one" value="{{ old('witness_one', $isEdit ? $editingRecord->witness_one : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Saksi II (Pihak Wanita) *</label>
                                            <input type="text" name="witness_two" value="{{ old('witness_two', $isEdit ? $editingRecord->witness_two : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                        </div>
                                        <div class="sm:col-span-3">
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Lampiran Berkas Persyaratan (PDF/JPG: KTP, KK, Surat N1-N4, Pas Foto Gandeng)</label>
                                            <input type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full text-xs sm:text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-slate-900 file:text-white file:font-semibold">
                                            @if ($isEdit && $editingRecord->attachment_name)
                                                <div class="text-xs text-slate-500 mt-1">Berkas tersimpan saat ini: {{ $editingRecord->attachment_name }}</div>
                                            @endif
                                        </div>
                                        <div class="sm:col-span-3">
                                            <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Catatan Kelengkapan Administrasi Sekretariat</label>
                                            <textarea name="notes" rows="3" placeholder="Contoh: Fotokopi KTP, KK, Akta Kelahiran, Surat Pengantar N1-N4 Kelurahan, dan Pas Foto Gandeng 4x6 telah diverifikasi lengkap." class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">{{ old('notes', $isEdit ? $editingRecord->notes : '') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 sm:gap-3 pt-5 border-t border-slate-100">
                                    <a href="{{ route('management.dashboard', ['tab' => 'marriages']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm text-center">Batal</a>
                                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm sm:text-base transition flex items-center justify-center gap-2 shadow-xs">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>{{ $isEdit ? 'Simpan Perubahan Data Nikah' : 'Simpan & Terbitkan Surat Nikah Buddhis' }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                    @elseif (in_array($currentMode, ['create_schedule', 'edit_schedule'], true))
                        @php($isEdit = $currentMode === 'edit_schedule')
                        <div class="max-w-3xl mx-auto space-y-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug flex items-center gap-2">
                                    <i class="fa-solid fa-calendar-days text-emerald-600 shrink-0"></i>
                                    <span class="break-words">{{ $isEdit ? 'Edit Jadwal Kegiatan' : 'Tambah Jadwal Ibadah & Kegiatan' }}</span>
                                </h2>
                                <a href="{{ route('management.dashboard', ['tab' => 'schedules']) }}" class="self-start sm:self-auto px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs sm:text-sm font-bold transition shrink-0 flex items-center gap-2 shadow-2xs">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>Kembali</span>
                                </a>
                            </div>

                            <form
                                method="POST"
                                action="{{ $isEdit ? route('management.schedules.update', $editingRecord) : route('management.schedules.store') }}"
                                class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-8 shadow-2xs space-y-5"
                            >
                                @csrf
                                @if ($isEdit) @method('PUT') @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Kegiatan *</label>
                                        <input type="text" name="title" value="{{ old('title', $isEdit ? $editingRecord->title : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Kategori *</label>
                                        <select name="category" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900 font-medium">
                                            @foreach (['Puja Bakti', 'Meditasi', 'Kelas Dharma', 'Perayaan Hari Besar', 'Kegiatan Sosial'] as $opt)
                                                <option value="{{ $opt }}" @selected(old('category', $isEdit ? $editingRecord->category : 'Puja Bakti') === $opt)>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Hari Pelaksanaan *</label>
                                        <input type="text" name="day_of_week" value="{{ old('day_of_week', $isEdit ? $editingRecord->day_of_week : 'Minggu') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Jam Mulai *</label>
                                        <input type="text" name="start_time" value="{{ old('start_time', $isEdit ? $editingRecord->start_time : '09:00') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Jam Selesai *</label>
                                        <input type="text" name="end_time" value="{{ old('end_time', $isEdit ? $editingRecord->end_time : '11:00') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Ruangan / Lokasi *</label>
                                        <input type="text" name="location" value="{{ old('location', $isEdit ? $editingRecord->location : 'Dharmasala Utama') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Pembimbing / Penceramah</label>
                                        <input type="text" name="speaker" value="{{ old('speaker', $isEdit ? $editingRecord->speaker : '') }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Deskripsi Kegiatan *</label>
                                        <textarea name="description" rows="3" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">{{ old('description', $isEdit ? $editingRecord->description : '') }}</textarea>
                                    </div>
                                </div>
                                <input type="hidden" name="is_recurring" value="1">
                                <input type="hidden" name="is_active" value="1">

                                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 sm:gap-3 pt-5 border-t border-slate-100">
                                    <a href="{{ route('management.dashboard', ['tab' => 'schedules']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm text-center">Batal</a>
                                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm sm:text-base transition flex items-center justify-center gap-2 shadow-xs">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>Simpan Jadwal</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                    @elseif (in_array($currentMode, ['create_gallery', 'edit_gallery'], true))
                        @php($isEdit = $currentMode === 'edit_gallery')
                        @php($currentMediaType = old('media_type', $isEdit ? $editingRecord->media_type : 'photo'))
                        <div class="max-w-3xl mx-auto space-y-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md border border-blue-200 inline-block">{{ $isEdit ? 'Mode Edit Galeri' : 'Input Dokumentasi Baru' }}</span>
                                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug mt-2 flex items-center gap-2">
                                        <i class="fa-solid fa-images text-blue-600 shrink-0"></i>
                                        <span class="break-words">{{ $isEdit ? 'Edit Dokumentasi Galeri' : 'Tambah Dokumentasi Galeri Foto & Video' }}</span>
                                    </h2>
                                </div>
                                <a href="{{ route('management.dashboard', ['tab' => 'galleries']) }}" class="self-start sm:self-auto px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs sm:text-sm font-bold transition shrink-0 flex items-center gap-2 shadow-2xs">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>Kembali</span>
                                </a>
                            </div>

                            <form
                                method="POST"
                                action="{{ $isEdit ? route('management.galleries.update', $editingRecord) : route('management.galleries.store') }}"
                                enctype="multipart/form-data"
                                class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-8 shadow-2xs space-y-5"
                            >
                                @csrf
                                @if ($isEdit) @method('PUT') @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Judul Dokumentasi Kegiatan *</label>
                                        <input type="text" name="title" value="{{ old('title', $isEdit ? $editingRecord->title : '') }}" required placeholder="Contoh: Puja Bakti Minggu Pagi & Dhamma Talk" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Kategori Kegiatan *</label>
                                        <input type="text" name="category" value="{{ old('category', $isEdit ? $editingRecord->category : 'Puja Bakti') }}" required placeholder="Puja Bakti / Meditasi / Bakti Sosial" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Jenis Media Dokumentasi *</label>
                                        <select name="media_type" id="galleryMediaTypeSelect" onchange="toggleGalleryMediaInput(this.value)" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900 font-semibold">
                                            <option value="photo" @selected($currentMediaType === 'photo')>Foto Kegiatan (Upload File Gambar)</option>
                                            <option value="video" @selected($currentMediaType === 'video')>Video Kegiatan (Link YouTube)</option>
                                        </select>
                                    </div>

                                    <!-- Conditional Input 1: Upload Photo File (When media_type === 'photo') -->
                                    <div id="galleryPhotoUploadBox" class="sm:col-span-2 {{ $currentMediaType === 'video' ? 'hidden' : '' }}">
                                        <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-200/80 space-y-3">
                                            <div class="flex items-center gap-2 text-blue-900 font-bold text-xs sm:text-sm">
                                                <i class="fa-solid fa-camera text-blue-600 shrink-0"></i>
                                                <span>Upload File Foto Kegiatan (.JPG, .PNG, .WEBP — Maks. 5 MB) {{ $isEdit ? '' : '*' }}</span>
                                            </div>
                                            <input
                                                type="file"
                                                name="photo_file"
                                                id="galleryPhotoFileInput"
                                                accept=".jpg,.jpeg,.png,.webp"
                                                class="w-full text-xs sm:text-sm text-slate-700 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-slate-900 file:text-white file:font-semibold hover:file:bg-slate-800"
                                            >
                                            @if ($isEdit && $editingRecord->media_type === 'photo' && $editingRecord->media_url)
                                                <div class="flex items-center gap-3 pt-1">
                                                    <img src="{{ $editingRecord->thumbnail_url ?: $editingRecord->media_url }}" alt="Preview" class="w-16 h-12 rounded-lg object-cover border border-slate-300 shrink-0">
                                                    <div class="text-xs text-slate-600">
                                                        <div class="font-semibold text-slate-800">Foto saat ini sudah tersimpan</div>
                                                        <div>Biarkan kosong jika tidak ingin mengganti file foto.</div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="media_url" value="{{ $editingRecord->media_url }}">
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Conditional Input 2: YouTube Video Link (When media_type === 'video') -->
                                    <div id="galleryYoutubeInputBox" class="sm:col-span-2 {{ $currentMediaType === 'video' ? '' : 'hidden' }}">
                                        <div class="p-4 rounded-2xl bg-rose-50/50 border border-rose-200/80 space-y-2.5">
                                            <label class="block font-bold text-rose-950 text-xs sm:text-sm flex items-center gap-2">
                                                <i class="fa-brands fa-youtube text-rose-600 text-lg shrink-0"></i>
                                                <span>Tautan / URL Video YouTube *</span>
                                            </label>
                                            <input
                                                type="url"
                                                name="youtube_url"
                                                id="galleryYoutubeUrlInput"
                                                value="{{ old('youtube_url', ($isEdit && $editingRecord->media_type === 'video') ? $editingRecord->media_url : '') }}"
                                                placeholder="Contoh: https://www.youtube.com/watch?v=dQw4w9WgXcQ atau https://youtu.be/..."
                                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-rose-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-rose-600"
                                            >
                                            <p class="text-xs text-rose-800/90">
                                                <i class="fa-solid fa-circle-info mr-1"></i>
                                                Khusus dokumentasi video, unggah video ke kanal <strong>YouTube</strong> terlebih dahulu lalu tempelkan tautan YouTube-nya di sini. Thumbnail video akan diambil otomatis dari YouTube.
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Tanggal Kegiatan</label>
                                        <input type="date" name="event_date" value="{{ old('event_date', $isEdit ? $editingRecord->event_date?->format('Y-m-d') : now()->toDateString()) }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Keterangan Dokumentasi</label>
                                        <textarea name="description" rows="2" placeholder="Deskripsi singkat dokumentasi kegiatan..." class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">{{ old('description', $isEdit ? $editingRecord->description : '') }}</textarea>
                                    </div>
                                </div>
                                <input type="hidden" name="is_featured" value="1">

                                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 sm:gap-3 pt-5 border-t border-slate-100">
                                    <a href="{{ route('management.dashboard', ['tab' => 'galleries']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm text-center">Batal</a>
                                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm sm:text-base transition flex items-center justify-center gap-2 shadow-xs">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>Simpan Dokumentasi</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <script>
                            function toggleGalleryMediaInput(type) {
                                const photoBox = document.getElementById('galleryPhotoUploadBox');
                                const ytBox = document.getElementById('galleryYoutubeInputBox');
                                if (!photoBox || !ytBox) return;
                                if (type === 'video') {
                                    photoBox.classList.add('hidden');
                                    ytBox.classList.remove('hidden');
                                } else {
                                    photoBox.classList.remove('hidden');
                                    ytBox.classList.add('hidden');
                                }
                            }
                        </script>

                    @elseif (in_array($currentMode, ['create_article', 'edit_article'], true))
                        @php($isEdit = $currentMode === 'edit_article')
                        <div class="max-w-4xl mx-auto space-y-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug flex items-center gap-2">
                                    <i class="fa-solid fa-newspaper text-rose-600 shrink-0"></i>
                                    <span class="break-words">{{ $isEdit ? 'Edit Artikel Dharma' : 'Terbitkan Artikel & Berita Baru' }}</span>
                                </h2>
                                <a href="{{ route('management.dashboard', ['tab' => 'articles']) }}" class="self-start sm:self-auto px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs sm:text-sm font-bold transition shrink-0 flex items-center gap-2 shadow-2xs">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>Kembali</span>
                                </a>
                            </div>

                            <form
                                method="POST"
                                action="{{ $isEdit ? route('management.articles.update', $editingRecord) : route('management.articles.store') }}"
                                class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-8 shadow-2xs space-y-5"
                            >
                                @csrf
                                @if ($isEdit) @method('PUT') @endif

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Judul Publikasi *</label>
                                        <input type="text" name="title" value="{{ old('title', $isEdit ? $editingRecord->title : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Kategori *</label>
                                        <select name="category" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900 font-medium">
                                            @foreach (['Berita Kegiatan', 'Renungan Dharma', 'Pengumuman', 'Artikel Buddhis'] as $opt)
                                                <option value="{{ $opt }}" @selected(old('category', $isEdit ? $editingRecord->category : 'Berita Kegiatan') === $opt)>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Penulis *</label>
                                        <input type="text" name="author" value="{{ old('author', $isEdit ? $editingRecord->author : ($currentUser?->name ?? 'Sekretariat Wihara Mahabodhi')) }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">URL Gambar Sampul (Opsional)</label>
                                        <input type="text" name="cover_image" value="{{ old('cover_image', $isEdit ? $editingRecord->cover_image : '') }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Ringkasan Singkat *</label>
                                        <input type="text" name="excerpt" value="{{ old('excerpt', $isEdit ? $editingRecord->excerpt : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Isi Artikel Lengkap *</label>
                                        <textarea name="content" rows="6" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">{{ old('content', $isEdit ? $editingRecord->content : '') }}</textarea>
                                    </div>
                                </div>
                                <input type="hidden" name="is_published" value="1">

                                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 sm:gap-3 pt-5 border-t border-slate-100">
                                    <a href="{{ route('management.dashboard', ['tab' => 'articles']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm text-center">Batal</a>
                                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm sm:text-base transition flex items-center justify-center gap-2 shadow-xs">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>Simpan Publikasi</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                    @elseif (in_array($currentMode, ['create_role', 'edit_role'], true))
                        @php($isEdit = $currentMode === 'edit_role')
                        <div class="max-w-3xl mx-auto space-y-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug flex items-center gap-2">
                                    <i class="fa-solid fa-user-shield text-purple-600 shrink-0"></i>
                                    <span class="break-words">{{ $isEdit ? 'Edit Role '.$editingRecord->name : 'Buat Role Pengurus Baru' }}</span>
                                </h2>
                                <a href="{{ route('management.dashboard', ['tab' => 'roles']) }}" class="self-start sm:self-auto px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs sm:text-sm font-bold transition shrink-0 flex items-center gap-2 shadow-2xs">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>Kembali</span>
                                </a>
                            </div>

                            <form
                                method="POST"
                                action="{{ $isEdit ? route('management.roles.update', $editingRecord) : route('management.roles.store') }}"
                                class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-8 shadow-2xs space-y-5"
                            >
                                @csrf
                                @if ($isEdit) @method('PUT') @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Role *</label>
                                        <input type="text" name="name" value="{{ old('name', $isEdit ? $editingRecord->name : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Deskripsi Tugas</label>
                                        <input type="text" name="description" value="{{ old('description', $isEdit ? $editingRecord->description : '') }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <label class="block font-bold text-slate-800 text-xs sm:text-sm">Hak Akses Modul *</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @foreach ($availablePermissions as $permKey => $permLabel)
                                            <label class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 cursor-pointer hover:bg-slate-100/80 transition">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $permKey }}"
                                                    @checked(in_array($permKey, old('permissions', $isEdit ? ($editingRecord->permissions ?? []) : []), true))
                                                    class="w-4 h-4 rounded text-slate-900 shrink-0"
                                                >
                                                <span class="text-xs sm:text-sm font-semibold text-slate-800 break-words">{{ $permLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 sm:gap-3 pt-5 border-t border-slate-100">
                                    <a href="{{ route('management.dashboard', ['tab' => 'roles']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm text-center">Batal</a>
                                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm sm:text-base transition flex items-center justify-center gap-2 shadow-xs">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>Simpan Role</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                    @elseif (in_array($currentMode, ['create_user', 'edit_user'], true))
                        @php($isEdit = $currentMode === 'edit_user')
                        <div class="max-w-3xl mx-auto space-y-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug flex items-center gap-2">
                                    <i class="fa-solid fa-user text-indigo-600 shrink-0"></i>
                                    <span class="break-words">{{ $isEdit ? 'Edit Akun Pengurus '.$editingRecord->name : 'Tambah Akun Pengurus Baru' }}</span>
                                </h2>
                                <a href="{{ route('management.dashboard', ['tab' => 'roles']) }}" class="self-start sm:self-auto px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs sm:text-sm font-bold transition shrink-0 flex items-center gap-2 shadow-2xs">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>Kembali</span>
                                </a>
                            </div>

                            <form
                                method="POST"
                                action="{{ $isEdit ? route('management.users.update', $editingRecord) : route('management.users.store') }}"
                                class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-8 shadow-2xs space-y-5"
                            >
                                @csrf
                                @if ($isEdit) @method('PUT') @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Lengkap *</label>
                                        <input type="text" name="name" value="{{ old('name', $isEdit ? $editingRecord->name : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Email *</label>
                                        <input type="email" name="email" value="{{ old('email', $isEdit ? $editingRecord->email : '') }}" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Kata Sandi {{ $isEdit ? '(Opsional)' : '*' }}</label>
                                        <input type="password" name="password" {{ $isEdit ? '' : 'required' }} class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Role Pengurus *</label>
                                        <select name="role_id" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900 font-medium">
                                            @foreach ($roles as $rOpt)
                                                <option value="{{ $rOpt->id }}" @selected((int) old('role_id', $isEdit ? $editingRecord->role_id : 0) === $rOpt->id)>{{ $rOpt->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Jabatan</label>
                                        <input type="text" name="position" value="{{ old('position', $isEdit ? $editingRecord->position : 'Pengurus Vihara') }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">No. Telepon</label>
                                        <input type="text" name="phone" value="{{ old('phone', $isEdit ? $editingRecord->phone : '') }}" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900">
                                    </div>
                                </div>
                                <input type="hidden" name="is_active" value="1">

                                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 sm:gap-3 pt-5 border-t border-slate-100">
                                    <a href="{{ route('management.dashboard', ['tab' => 'roles']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm text-center">Batal</a>
                                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm sm:text-base transition flex items-center justify-center gap-2 shadow-xs">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>Simpan Akun Pengurus</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                @else
                    <!-- ================================================= -->
                    <!-- TAB 0: OVERVIEW                                   -->
                    <!-- ================================================= -->
                    <section id="tab-overview" class="mgmt-tab-panel space-y-6">
                        <!-- SaaS Executive Header Banner (Greeting ONLY on Dashboard Ringkasan) -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-amber-600 mb-1 flex items-center gap-1.5">
                                    <i class="fa-solid fa-hands-praying"></i>
                                    <span>Namo Buddhaya, selamat datang kembali, {{ $currentUser->name ?? 'Pengurus' }}</span>
                                </div>
                                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 flex items-center gap-2.5">
                                    <i class="fa-solid fa-chart-pie text-amber-500"></i>
                                    <span>Ringkasan Operasional Sekretariat</span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1">Pusat kendali administrasi Wihara Mahabodhi: pemantauan tanda terima, arsip persuratan resmi, akta perkawinan Buddhis, jadwal kegiatan, dan publikasi.</p>
                            </div>
                        </div>

                        <!-- Metric Cards with SaaS Gradient Styling (Inspired by erp-credibug) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                            <!-- Card 1: Receipts -->
                            <div class="rounded-2xl p-5 sm:p-6 shadow-xs relative overflow-hidden text-white space-y-3" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
                                <div class="flex items-center justify-between gap-2 relative z-10">
                                    <span class="text-amber-100 text-xs uppercase font-bold tracking-wider">Tanda Terima Bantuan / Barang / Surat</span>
                                    <span class="px-2.5 py-1 rounded-full bg-white/20 text-white text-xs font-bold backdrop-blur-xs shrink-0">
                                        <i class="fa-solid fa-print mr-1"></i> A4 &amp; Excel
                                    </span>
                                </div>
                                <div class="relative z-10">
                                    <div class="text-3xl sm:text-4xl font-extrabold text-white mt-1">{{ $stats['total_receipts'] }} <span class="text-lg font-bold text-amber-200">Kuitansi</span></div>
                                </div>
                                <div class="text-xs sm:text-sm text-amber-100 pt-2 border-t border-amber-400/30 flex items-center gap-1.5 leading-normal relative z-10">
                                    <i class="fa-solid fa-file-invoice"></i>
                                    <span>Arsip Bukti Penerimaan &amp; Surat Ucapan Anumodana</span>
                                </div>
                                <i class="fa-solid fa-receipt absolute -right-3 -bottom-3 text-7xl text-white/10 pointer-events-none"></i>
                            </div>

                            <!-- Card 2: Documents -->
                            <div class="rounded-2xl p-5 sm:p-6 shadow-xs relative overflow-hidden text-white space-y-3" style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);">
                                <div class="flex items-center justify-between gap-2 relative z-10">
                                    <span class="text-blue-100 text-xs uppercase font-bold tracking-wider">Arsip Persuratan &amp; Dokumen Resmi</span>
                                    <span class="px-2.5 py-1 rounded-full bg-white/20 text-white text-xs font-bold backdrop-blur-xs shrink-0">
                                        <i class="fa-solid fa-file-shield mr-1"></i> Naskah Dinas
                                    </span>
                                </div>
                                <div class="relative z-10">
                                    <div class="text-3xl sm:text-4xl font-extrabold text-white mt-1">{{ $stats['total_documents'] }} <span class="text-lg font-bold text-blue-200">Berkas Arsip</span></div>
                                </div>
                                <div class="text-xs sm:text-sm text-blue-100 pt-2 border-t border-blue-400/30 flex items-center gap-1.5 leading-normal relative z-10">
                                    <i class="fa-solid fa-folder-open"></i>
                                    <span>Surat Masuk: <strong>{{ $stats['surat_masuk_count'] }}</strong> &bull; Surat Keluar: <strong>{{ $stats['surat_keluar_count'] }}</strong></span>
                                </div>
                                <i class="fa-solid fa-folder-open absolute -right-3 -bottom-3 text-7xl text-white/10 pointer-events-none"></i>
                            </div>

                            <!-- Card 3: Landing Content -->
                            <div class="rounded-2xl p-5 sm:p-6 shadow-xs relative overflow-hidden text-white space-y-3" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                                <div class="flex items-center justify-between gap-2 relative z-10">
                                    <span class="text-emerald-100 text-xs uppercase font-bold tracking-wider">Publikasi &amp; Kegiatan</span>
                                    <span class="px-2.5 py-1 rounded-full bg-white/20 text-white text-xs font-bold backdrop-blur-xs shrink-0">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Aktif
                                    </span>
                                </div>
                                <div class="relative z-10">
                                    <div class="text-3xl sm:text-4xl font-extrabold text-white mt-1">{{ $stats['active_schedules'] }} <span class="text-lg font-bold text-emerald-200">Jadwal Ibadah</span></div>
                                </div>
                                <div class="text-xs sm:text-sm text-emerald-100 pt-2 border-t border-emerald-400/30 flex items-center gap-1.5 leading-normal relative z-10">
                                    <i class="fa-solid fa-calendar-check"></i>
                                    <span>Galeri: <strong>{{ $stats['total_galleries'] }} Dokumentasi</strong> &bull; Warta: <strong>{{ $stats['published_articles'] }}</strong></span>
                                </div>
                                <i class="fa-solid fa-calendar-check absolute -right-3 -bottom-3 text-7xl text-white/10 pointer-events-none"></i>
                            </div>

                            <!-- Card 4: Roles & Users -->
                            <div class="rounded-2xl p-5 sm:p-6 shadow-xs relative overflow-hidden text-white space-y-3" style="background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);">
                                <div class="flex items-center justify-between gap-2 relative z-10">
                                    <span class="text-purple-100 text-xs uppercase font-bold tracking-wider">Manajemen Role Pengurus</span>
                                    <span class="px-2.5 py-1 rounded-full bg-white/20 text-white text-xs font-bold backdrop-blur-xs shrink-0">
                                        <i class="fa-solid fa-shield-halved mr-1"></i> RBAC
                                    </span>
                                </div>
                                <div class="relative z-10">
                                    <div class="text-3xl sm:text-4xl font-extrabold text-white mt-1">{{ $stats['total_roles'] }} <span class="text-lg font-bold text-purple-200">Role Sistem</span></div>
                                </div>
                                <div class="text-xs sm:text-sm text-purple-100 pt-2 border-t border-purple-400/30 flex items-center gap-1.5 leading-normal relative z-10">
                                    <i class="fa-solid fa-users"></i>
                                    <span>Total Pengurus Terdaftar: <strong>{{ $stats['total_users'] }} Orang</strong></span>
                                </div>
                                <i class="fa-solid fa-users-gear absolute -right-3 -bottom-3 text-7xl text-white/10 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- ========================================================= -->
                        <!-- KALENDER JADWAL IBADAH, PUJA BAKTI & KEGIATAN VIHARA      -->
                        <!-- ========================================================= -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-6 shadow-xs space-y-5 overflow-hidden">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2.5">
                                        <i class="fa-solid fa-calendar-days text-emerald-600 text-lg shrink-0"></i>
                                        <span class="break-words">Kalender Jadwal Ibadah, Puja Bakti &amp; Kegiatan Vihara</span>
                                    </h2>
                                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                                        Klik tanggal pada kalender untuk melihat rincian jadwal Puja Bakti, Meditasi, Kelas Dharma, dan Kegiatan Vihara.
                                    </p>
                                </div>
                                <div class="flex items-center flex-wrap gap-2">
                                    <button type="button" onclick="changeDashboardCalendarMonth(-1)" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold transition flex items-center gap-1">
                                        <i class="fa-solid fa-chevron-left text-xs"></i>
                                        <span>Sebelumnya</span>
                                    </button>
                                    <button type="button" onclick="resetDashboardCalendarToday()" class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs sm:text-sm font-bold transition">
                                        Hari Ini
                                    </button>
                                    <button type="button" onclick="changeDashboardCalendarMonth(1)" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold transition flex items-center gap-1">
                                        <span>Berikutnya</span>
                                        <i class="fa-solid fa-chevron-right text-xs"></i>
                                    </button>
                                    @if ($canSchedules)
                                        <a href="{{ route('management.schedules.create') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold transition flex items-center gap-1.5 shadow-2xs">
                                            <i class="fa-solid fa-circle-plus"></i>
                                            <span>+ Jadwal</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                                <!-- Left 8 Cols: Interactive Monthly Calendar Grid -->
                                <div class="lg:col-span-8 space-y-3 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200/80">
                                        <div id="dashboardCalendarMonthTitle" class="text-sm sm:text-base font-extrabold text-slate-900"></div>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600 font-semibold">
                                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Ada Kegiatan</span>
                                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Hari Ini</span>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                                        <!-- Day of week headers -->
                                        <div class="grid grid-cols-7 bg-slate-100 border-b border-slate-200 text-center text-[11px] sm:text-xs font-bold text-slate-700 uppercase tracking-wider">
                                            <div class="py-2 sm:py-2.5 text-rose-600">Min</div>
                                            <div class="py-2 sm:py-2.5">Sen</div>
                                            <div class="py-2 sm:py-2.5">Sel</div>
                                            <div class="py-2 sm:py-2.5">Rab</div>
                                            <div class="py-2 sm:py-2.5">Kam</div>
                                            <div class="py-2 sm:py-2.5">Jum</div>
                                            <div class="py-2 sm:py-2.5 text-blue-600">Sab</div>
                                        </div>
                                        <!-- Calendar Days Grid -->
                                        <div id="dashboardCalendarGrid" class="grid grid-cols-7 divide-x divide-y divide-slate-200 bg-white"></div>
                                    </div>
                                </div>

                                <!-- Right 4 Cols: Selected Day Schedule Details & Weekly List -->
                                <div class="lg:col-span-4 bg-slate-50 rounded-2xl border border-slate-200/90 p-4 sm:p-5 space-y-4 min-w-0">
                                    <div class="border-b border-slate-200 pb-3">
                                        <div class="text-xs font-bold uppercase tracking-wider text-emerald-700">Agenda Tanggal Terpilih</div>
                                        <h3 id="dashboardSelectedDateLabel" class="text-base sm:text-lg font-extrabold text-slate-900 mt-0.5 break-words">Hari Ini</h3>
                                    </div>

                                    <div id="dashboardSelectedDayEvents" class="space-y-2.5"></div>

                                    <div class="pt-3 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs">
                                        <span class="text-slate-500 font-medium">Total Jadwal Rutin: <strong>{{ $schedules->count() }} Kegiatan</strong></span>
                                        @if ($canSchedules)
                                            <button type="button" onclick="switchManagementTab('schedules')" class="font-bold text-slate-800 hover:underline flex items-center gap-1">
                                                <span>Lihat Tabel Jadwal</span>
                                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity Tables (Direct View without sliding) -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Recent Receipts -->
                            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-6 space-y-4 overflow-hidden min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3 border-b border-slate-100 pb-3">
                                    <div class="min-w-0">
                                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                            <i class="fa-solid fa-receipt text-amber-600 shrink-0"></i>
                                            <span class="break-words">Tanda Terima Bantuan / Barang / Surat</span>
                                        </h2>
                                        <p class="text-xs sm:text-sm text-slate-500">Penerimaan terbaru di sekretariat vihara</p>
                                    </div>
                                    @if ($canReceipts)
                                        <button type="button" onclick="switchManagementTab('receipts')" class="self-start sm:self-auto text-xs sm:text-sm font-bold text-slate-700 hover:text-slate-900 shrink-0 flex items-center gap-1.5">
                                            <span>Kelola Semua</span>
                                            <i class="fa-solid fa-arrow-right text-xs"></i>
                                        </button>
                                    @endif
                                </div>

                                <!-- Search & Info Bar -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="relative w-full sm:w-64">
                                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
                                        <input
                                            type="search"
                                            id="overviewReceiptsSearch"
                                            placeholder="Cari tanda terima..."
                                            class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-slate-400"
                                        >
                                    </div>
                                    <div id="overviewReceiptsInfo" class="text-xs text-slate-500 font-medium"></div>
                                </div>

                                <div id="overviewReceiptsContainer" class="divide-y divide-slate-100">
                                    @forelse ($receipts as $r)
                                        <div class="overview-receipt-row py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/70 transition">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center flex-wrap gap-2">
                                                    <span class="font-bold text-slate-900 text-sm sm:text-base leading-snug break-words">{{ $r->donor_name }}</span>
                                                    <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 shrink-0">{{ $r->receipt_type }}</span>
                                                </div>
                                                <div class="text-slate-600 text-xs sm:text-sm mt-0.5 leading-snug break-words">{{ $r->item_name }} &bull; {{ $r->quantity }}</div>
                                                <div class="text-xs text-slate-400 mt-0.5 break-words">{{ $r->receipt_number }} &bull; {{ $r->receipt_date->format('d M Y') }}</div>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                                <button type="button" onclick="openReceiptPrintModal(@js($r), 'receipt')" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-semibold transition shadow-xs flex items-center gap-1.5">
                                                    <i class="fa-solid fa-print"></i>
                                                    <span>Print A4</span>
                                                </button>
                                                <button type="button" onclick="openReceiptPrintModal(@js($r), 'thank_you')" class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-900 text-xs sm:text-sm font-semibold transition flex items-center gap-1.5">
                                                    <i class="fa-solid fa-envelope-open-text"></i>
                                                    <span>Ucapan</span>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="py-8 text-center text-slate-400 text-sm">Belum ada data tanda terima.</div>
                                    @endforelse
                                </div>

                                <div id="overviewReceiptsPagination" class="flex justify-center sm:justify-end pt-3 border-t border-slate-100"></div>
                            </div>

                            <!-- Recent Documents -->
                            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-6 space-y-4 overflow-hidden min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3 border-b border-slate-100 pb-3">
                                    <div class="min-w-0">
                                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                            <i class="fa-solid fa-folder-open text-blue-600 shrink-0"></i>
                                            <span class="break-words">Arsip Persuratan &amp; Dokumen Terbaru</span>
                                        </h2>
                                        <p class="text-xs sm:text-sm text-slate-500">Registrasi surat masuk, surat keluar &amp; naskah dinas sekretariat</p>
                                    </div>
                                    @if ($canDocuments)
                                        <button type="button" onclick="switchManagementTab('documents')" class="self-start sm:self-auto text-xs sm:text-sm font-bold text-slate-700 hover:text-slate-900 shrink-0 flex items-center gap-1.5">
                                            <span>Buka Arsip</span>
                                            <i class="fa-solid fa-arrow-right text-xs"></i>
                                        </button>
                                    @endif
                                </div>

                                <!-- Search & Info Bar -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="relative w-full sm:w-64">
                                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
                                        <input
                                            type="search"
                                            id="overviewDocsSearch"
                                            placeholder="Cari dokumen..."
                                            class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-slate-400"
                                        >
                                    </div>
                                    <div id="overviewDocsInfo" class="text-xs text-slate-500 font-medium"></div>
                                </div>

                                <div id="overviewDocsContainer" class="divide-y divide-slate-100">
                                    @forelse ($documents as $d)
                                        <div class="overview-doc-row py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/70 transition">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center flex-wrap gap-2">
                                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-xs shrink-0">{{ $d->category }}</span>
                                                    <span class="font-bold text-slate-900 text-sm sm:text-base leading-snug break-words">{{ $d->title }}</span>
                                                </div>
                                                <div class="text-slate-600 text-xs sm:text-sm mt-0.5 leading-snug break-words">{{ $d->document_number }} &bull; Dari: {{ $d->sender }}</div>
                                                <div class="text-xs text-slate-400 mt-0.5 break-words">{{ $d->document_date->format('d M Y') }} &bull; {{ $d->archive_code }}</div>
                                            </div>
                                            <button type="button" onclick="openDocumentPrintModal(@js($d))" class="self-start sm:self-auto px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-semibold shrink-0 transition shadow-xs flex items-center gap-1.5">
                                                <i class="fa-solid fa-print"></i>
                                                <span>Cetak A4</span>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="py-8 text-center text-slate-400 text-sm">Belum ada dokumen diarsipkan.</div>
                                    @endforelse
                                </div>

                                <div id="overviewDocsPagination" class="flex justify-center sm:justify-end pt-3 border-t border-slate-100"></div>
                            </div>
                        </div>
                    </section>

                    <!-- ================================================= -->
                    <!-- TAB 1: TANDA TERIMA & UCAPAN (PRINT A4 & EXCEL)   -->
                    <!-- ================================================= -->
                    @if ($canReceipts)
                    <section id="tab-receipts" class="mgmt-tab-panel hidden space-y-6">
                        <!-- Top Header Banner with Separated Primary Create Button -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="min-w-0">
                                <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2.5">
                                    <i class="fa-solid fa-receipt text-amber-600 text-xl shrink-0"></i>
                                    <span class="break-words">Tanda Terima Bantuan / Barang / Surat &amp; Ucapan Terima Kasih</span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                                    Kelola penerimaan vihara, cetak A4 berlogo <code>[logo]</code>, serta ekspor &amp; impor data Excel (<code>.xlsx</code>).
                                </p>
                            </div>
                            <div class="shrink-0">
                                <a href="{{ route('management.receipts.create') }}" class="w-full sm:w-auto justify-center px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-xs">
                                    <i class="fa-solid fa-circle-plus text-base"></i>
                                    <span>Buat Tanda Terima Baru</span>
                                </a>
                            </div>
                        </div>

                        <!-- Receipts Table Card (Responsive Stacked Mobile + Overflow-X Auto) -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                            <!-- Search, Filter & Excel Actions Toolbar -->
                            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 min-w-0">
                                    <div class="relative w-full sm:w-72">
                                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
                                        <input
                                            type="search"
                                            id="receiptSearchInput"
                                            placeholder="Cari nomor, donatur, atau barang..."
                                            class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-slate-400"
                                        >
                                    </div>
                                    <select id="receiptFilterType" class="w-full sm:w-auto px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 focus:outline-none focus:bg-white">
                                        <option value="all">Semua Jenis</option>
                                        <option value="Bantuan Dana">Bantuan Dana</option>
                                        <option value="Barang">Barang</option>
                                        <option value="Surat / Dokumen">Surat / Dokumen</option>
                                    </select>
                                </div>

                                <div class="flex items-center flex-wrap gap-2 justify-between lg:justify-end">
                                    <div class="flex items-center flex-wrap gap-2">
                                        <a href="{{ route('management.receipts.export') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs sm:text-sm font-semibold transition flex items-center gap-1.5 shadow-2xs">
                                            <i class="fa-solid fa-file-excel text-emerald-600"></i>
                                            <span>Export (.xlsx)</span>
                                        </a>
                                        <button type="button" onclick="openImportExcelModal()" class="px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs sm:text-sm font-bold transition flex items-center gap-1.5 shadow-2xs">
                                            <i class="fa-solid fa-file-import text-blue-600"></i>
                                            <span>Import Excel</span>
                                        </button>
                                    </div>
                                    <div id="receiptsCountInfo" class="text-xs sm:text-sm font-semibold text-slate-600 sm:pl-2 sm:border-l sm:border-slate-200"></div>
                                </div>
                            </div>

                            <!-- Responsive Table Wrapper -->
                            <div class="w-full overflow-x-auto">
                                <table class="mgmt-responsive-table w-full table-auto text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-200/80 text-slate-600 bg-slate-50/70 text-xs sm:text-sm font-bold uppercase tracking-wider">
                                            <th class="py-3.5 px-4 w-[18%]">No. &amp; Tanggal</th>
                                            <th class="py-3.5 px-3 w-[16%]">Jenis &amp; Nominal</th>
                                            <th class="py-3.5 px-4 w-[22%]">Donatur / Instansi</th>
                                            <th class="py-3.5 px-4 w-[24%]">Uraian &amp; Penerima</th>
                                            <th class="py-3.5 px-4 text-right w-[20%]">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="receiptsTableBody" class="divide-y divide-slate-100">
                                        @forelse ($receipts as $receipt)
                                            <tr data-category="{{ $receipt->receipt_type }}" class="receipt-row hover:bg-slate-50/80 transition">
                                                <td data-label="No. &amp; Tanggal" class="py-3.5 px-4 align-top">
                                                    <div class="font-bold text-slate-900 text-sm sm:text-base break-words">{{ $receipt->receipt_number }}</div>
                                                    <div class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ $receipt->receipt_date->format('d M Y') }}</div>
                                                </td>
                                                <td data-label="Jenis &amp; Nominal" class="py-3.5 px-3 align-top">
                                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                        {{ $receipt->receipt_type }}
                                                    </span>
                                                    <div class="font-bold text-emerald-700 text-sm sm:text-base mt-1.5">
                                                        {{ $receipt->nominal !== null ? 'Rp '.number_format((float) $receipt->nominal, 0, ',', '.') : '—' }}
                                                    </div>
                                                </td>
                                                <td data-label="Donatur / Instansi" class="py-3.5 px-4 align-top">
                                                    <div class="font-bold text-slate-900 text-sm sm:text-base leading-snug break-words">{{ $receipt->donor_name }}</div>
                                                    @if ($receipt->donor_institution)
                                                        <div class="text-xs sm:text-sm text-slate-600 leading-snug mt-0.5 break-words">{{ $receipt->donor_institution }}</div>
                                                    @endif
                                                    @if ($receipt->donor_contact)
                                                        <div class="text-xs text-slate-400 mt-0.5 break-words">{{ $receipt->donor_contact }}</div>
                                                    @endif
                                                </td>
                                                <td data-label="Uraian &amp; Penerima" class="py-3.5 px-4 align-top">
                                                    <div class="text-slate-900 font-semibold text-sm sm:text-base leading-snug break-words">{{ $receipt->item_name }}</div>
                                                    <div class="text-xs sm:text-sm text-slate-500 mt-1 leading-snug break-words">{{ $receipt->quantity }} &bull; Penerima: {{ $receipt->receiver_name }}</div>
                                                </td>
                                                <td data-label="Aksi" class="mgmt-actions-cell py-3.5 px-4 align-top text-right">
                                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                                        <button type="button" onclick="openReceiptPrintModal(@js($receipt), 'receipt')" class="px-2.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition shadow-xs inline-flex items-center gap-1.5">
                                                            <i class="fa-solid fa-print"></i>
                                                            <span>Print A4</span>
                                                        </button>
                                                        <button type="button" onclick="openReceiptPrintModal(@js($receipt), 'thank_you')" class="px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-900 font-semibold text-xs transition inline-flex items-center gap-1.5">
                                                            <i class="fa-solid fa-envelope-open-text"></i>
                                                            <span>Ucapan</span>
                                                        </button>
                                                        <a href="{{ route('management.receipts.edit', $receipt) }}" class="p-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition inline-flex items-center justify-center w-8 h-8" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('management.receipts.destroy', $receipt) }}" class="inline" onsubmit="return confirm('Hapus tanda terima ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 rounded-xl text-rose-600 hover:bg-rose-50 font-semibold text-xs transition inline-flex items-center justify-center w-8 h-8" title="Hapus">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-10 text-center text-slate-400 text-sm sm:text-base">Belum ada data Tanda Terima.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Footer -->
                            <div class="p-4 border-t border-slate-100 flex items-center justify-center sm:justify-end bg-slate-50/50" id="receiptsPaginationContainer"></div>
                        </div>
                    </section>
                    @endif

                    <!-- ================================================= -->
                    <!-- TAB 2: ARSIP PERSURATAN & DOKUMEN RESMI           -->
                    <!-- ================================================= -->
                    @if ($canDocuments)
                    <section id="tab-documents" class="mgmt-tab-panel hidden space-y-6">
                        <!-- Top Header & Action Bar -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="min-w-0">
                                <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2.5">
                                    <i class="fa-solid fa-folder-open text-blue-600 text-xl shrink-0"></i>
                                    <span class="break-words">Arsip Persuratan &amp; Dokumen Resmi Sekretariat</span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                                    Pengelolaan naskah dinas surat masuk, surat keluar, surat keputusan, dan arsip digital resmi Wihara Mahabodhi.
                                </p>
                            </div>
                            <a href="{{ route('management.documents.create') }}" class="w-full sm:w-auto justify-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold transition shrink-0 flex items-center gap-2 shadow-xs">
                                <i class="fa-solid fa-circle-plus text-base"></i>
                                <span>Registrasi Dokumen Baru</span>
                            </a>
                        </div>

                        <!-- Documents Table Card (Responsive Stacked Mobile + Overflow-X Auto) -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                            <!-- Search & Filter Category Pills -->
                            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                                <div class="flex flex-wrap items-center gap-2.5 min-w-0">
                                    <div class="relative w-full sm:w-72">
                                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
                                        <input
                                            type="search"
                                            id="docSearchInput"
                                            placeholder="Cari nomor, perihal, atau pengirim..."
                                            class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-slate-400"
                                        >
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        <button type="button" data-filter="all" class="doc-filter-pill px-3 py-1.5 rounded-xl text-xs sm:text-sm font-semibold bg-slate-900 text-white">Semua</button>
                                        <button type="button" data-filter="Surat Masuk" class="doc-filter-pill px-3 py-1.5 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 flex items-center gap-1.5"><i class="fa-solid fa-inbox text-blue-600"></i> Surat Masuk</button>
                                        <button type="button" data-filter="Surat Keluar" class="doc-filter-pill px-3 py-1.5 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 flex items-center gap-1.5"><i class="fa-solid fa-paper-plane text-emerald-600"></i> Surat Keluar</button>
                                        <button type="button" data-filter="Dokumen Umum A4" class="doc-filter-pill px-3 py-1.5 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 flex items-center gap-1.5"><i class="fa-solid fa-file-lines text-amber-600"></i> Dokumen Umum A4</button>
                                        <button type="button" data-filter="SK / Piagam" class="doc-filter-pill px-3 py-1.5 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 flex items-center gap-1.5"><i class="fa-solid fa-award text-purple-600"></i> SK / Piagam</button>
                                    </div>
                                </div>
                                <div id="docsCountInfo" class="text-xs sm:text-sm font-semibold text-slate-600"></div>
                            </div>

                            <!-- Responsive Table Wrapper -->
                            <div class="w-full overflow-x-auto">
                                <table class="mgmt-responsive-table w-full table-auto text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-200/80 text-slate-600 bg-slate-50/70 text-xs sm:text-sm font-bold uppercase tracking-wider">
                                            <th class="py-3.5 px-4 w-[20%]">No. &amp; Tanggal Dokumen</th>
                                            <th class="py-3.5 px-3 w-[14%]">Kategori</th>
                                            <th class="py-3.5 px-4 w-[30%]">Perihal &amp; Ringkasan</th>
                                            <th class="py-3.5 px-4 w-[18%]">Pengirim &rarr; Penerima</th>
                                            <th class="py-3.5 px-4 text-right w-[18%]">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="documentsTableBody" class="divide-y divide-slate-100">
                                        @forelse ($documents as $doc)
                                            <tr data-category="{{ $doc->category }}" class="doc-row hover:bg-slate-50/80 transition">
                                                <td data-label="No. &amp; Tanggal Dokumen" class="py-3.5 px-4 align-top">
                                                    <div class="font-bold text-slate-900 text-sm sm:text-base break-words">{{ $doc->document_number }}</div>
                                                    <div class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ $doc->document_date->format('d M Y') }} &bull; {{ $doc->archive_code }}</div>
                                                </td>
                                                <td data-label="Kategori" class="py-3.5 px-3 align-top">
                                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                        {{ $doc->category }}
                                                    </span>
                                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md bg-slate-900 text-white text-[10px] font-bold">
                                                        {{ $doc->paper_size }}
                                                    </span>
                                                </td>
                                                <td data-label="Perihal &amp; Ringkasan" class="py-3.5 px-4 align-top">
                                                    <div class="font-bold text-slate-900 text-sm sm:text-base leading-snug break-words">{{ $doc->title }}</div>
                                                    <div class="text-xs sm:text-sm text-slate-600 leading-snug mt-1 break-words">{{ $doc->summary }}</div>
                                                    @if ($doc->attachment_path)
                                                        <a href="{{ asset('storage/'.$doc->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1.5 mt-1.5 text-xs font-semibold text-blue-700 hover:text-blue-900 hover:underline break-all">
                                                            <i class="fa-solid fa-paperclip shrink-0"></i>
                                                            <span>Lampiran: {{ $doc->attachment_name ?? 'Berkas' }}</span>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td data-label="Pengirim &rarr; Penerima" class="py-3.5 px-4 align-top text-xs sm:text-sm text-slate-700 leading-snug break-words">
                                                    <div>Dari: <strong class="text-slate-900 font-bold">{{ $doc->sender }}</strong></div>
                                                    <div class="text-slate-500 mt-0.5">Ke: {{ $doc->recipient }}</div>
                                                </td>
                                                <td data-label="Aksi" class="mgmt-actions-cell py-3.5 px-4 align-top text-right">
                                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                                        <button type="button" onclick="openDocumentPrintModal(@js($doc))" class="px-2.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition shadow-xs inline-flex items-center gap-1.5">
                                                            <i class="fa-solid fa-print"></i>
                                                            <span>Cetak A4</span>
                                                        </button>
                                                        <a href="{{ route('management.documents.edit', $doc) }}" class="p-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition inline-flex items-center justify-center w-8 h-8" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('management.documents.destroy', $doc) }}" class="inline" onsubmit="return confirm('Hapus dokumen ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 rounded-xl text-rose-600 hover:bg-rose-50 font-semibold text-xs transition inline-flex items-center justify-center w-8 h-8" title="Hapus">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-10 text-center text-slate-400 text-sm sm:text-base">Belum ada dokumen tercatat.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Footer -->
                            <div class="p-4 border-t border-slate-100 flex items-center justify-center sm:justify-end bg-slate-50/50" id="documentsPaginationContainer"></div>
                        </div>
                    </section>
                    @endif

                    <!-- ================================================= -->
                    <!-- TAB 2B: ADMINISTRASI SURAT NIKAH & PEMBERKATAN    -->
                    <!-- ================================================= -->
                    @if ($canMarriages)
                    <section id="tab-marriages" class="mgmt-tab-panel hidden space-y-6">
                        <!-- Top Header Banner with Separated Primary Create Button -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="min-w-0">
                                <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2.5">
                                    <i class="fa-solid fa-ring text-rose-600 text-xl shrink-0"></i>
                                    <span class="break-words">Administrasi Surat Nikah &amp; Pemberkatan Perkawinan Buddhis</span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                                    Arsip resmi Akta / Surat Keterangan Pemberkatan Perkawinan Buddhis, Bimbingan Pra-Nikah, berita acara saksi, dan cetak piagam A4 berlogo <code>[logo]</code>.
                                </p>
                            </div>
                            <div class="shrink-0">
                                <a href="{{ route('management.marriages.create') }}" class="w-full sm:w-auto justify-center px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-xs">
                                    <i class="fa-solid fa-circle-plus text-base"></i>
                                    <span>Buat Surat Nikah Baru</span>
                                </a>
                            </div>
                        </div>

                        <!-- Marriages Table Card (Responsive Stacked Mobile + Overflow-X Auto) -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                            <!-- Search & Filter Toolbar -->
                            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 min-w-0">
                                    <div class="relative w-full sm:w-80">
                                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
                                        <input
                                            type="search"
                                            id="marriageSearchInput"
                                            placeholder="Cari no surat, nama mempelai, pandita, saksi..."
                                            class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-slate-400"
                                        >
                                    </div>
                                    <select id="marriageFilterStatus" class="w-full sm:w-auto px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 focus:outline-none focus:bg-white">
                                        <option value="all">Semua Status Administrasi</option>
                                        <option value="Terdaftar & Verifikasi Berkas">Terdaftar &amp; Verifikasi Berkas</option>
                                        <option value="Selesai Pemberkatan">Selesai Pemberkatan</option>
                                        <option value="Tercatat di Dukcapil">Tercatat di Dukcapil</option>
                                    </select>
                                </div>
                                <div id="marriagesCountInfo" class="text-xs sm:text-sm font-semibold text-slate-600"></div>
                            </div>

                            <!-- Responsive Table Wrapper -->
                            <div class="w-full overflow-x-auto">
                                <table class="mgmt-responsive-table w-full table-auto text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-200/80 text-slate-600 bg-slate-50/70 text-xs sm:text-sm font-bold uppercase tracking-wider">
                                            <th class="py-3.5 px-4 w-[21%]">No. Surat &amp; Status</th>
                                            <th class="py-3.5 px-4 w-[26%]">Mempelai Pria &amp; Wanita</th>
                                            <th class="py-3.5 px-4 w-[18%]">Jadwal &amp; Lokasi</th>
                                            <th class="py-3.5 px-4 w-[17%]">Pandita &amp; Saksi</th>
                                            <th class="py-3.5 px-4 text-right w-[18%]">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="marriagesTableBody" class="divide-y divide-slate-100">
                                        @forelse ($marriages ?? [] as $marriage)
                                            <tr data-category="{{ $marriage->status }}" class="marriage-row hover:bg-slate-50/80 transition">
                                                <td data-label="No. Surat &amp; Status" class="py-3.5 px-4 align-top">
                                                    <div class="font-bold text-slate-900 text-sm sm:text-base break-words">{{ $marriage->certificate_number }}</div>
                                                    <div class="flex flex-wrap items-center gap-1 mt-1">
                                                        <span class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                            {{ $marriage->document_type }}
                                                        </span>
                                                        @php($statusBadgeClass = $marriage->status === 'Tercatat di Dukcapil' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($marriage->status === 'Selesai Pemberkatan' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-amber-50 text-amber-800 border-amber-200'))
                                                        <span class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $statusBadgeClass }}">
                                                            {{ $marriage->status }}
                                                        </span>
                                                    </div>
                                                    <div class="mt-1.5 text-xs text-slate-500 flex items-center gap-1">
                                                        <i class="fa-solid {{ $marriage->premarital_course_done ? 'fa-circle-check text-emerald-600' : 'fa-clock text-amber-500' }}"></i>
                                                        <span>{{ $marriage->premarital_course_done ? 'Pra-Nikah Selesai' : 'Belum Pra-Nikah' }}</span>
                                                    </div>
                                                    @if ($marriage->attachment_path)
                                                        <a href="{{ asset('storage/'.$marriage->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1 mt-1 text-xs font-semibold text-blue-600 hover:underline">
                                                            <i class="fa-solid fa-paperclip"></i>
                                                            <span>Lihat Berkas</span>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td data-label="Mempelai Pria &amp; Wanita" class="py-3.5 px-4 align-top">
                                                    <div class="space-y-1.5">
                                                        <div>
                                                            <div class="font-bold text-slate-900 text-sm sm:text-base leading-snug break-words">
                                                                <i class="fa-solid fa-mars text-blue-600 mr-1"></i>{{ $marriage->groom_name }}
                                                            </div>
                                                            <div class="text-xs text-slate-500 leading-snug break-words">
                                                                {{ $marriage->groom_buddhist_name ? $marriage->groom_buddhist_name.' • ' : '' }}Putra Bpk. {{ $marriage->groom_father_name }} &amp; Ibu {{ $marriage->groom_mother_name }}
                                                            </div>
                                                        </div>
                                                        <div class="pt-1 border-t border-slate-100">
                                                            <div class="font-bold text-slate-900 text-sm sm:text-base leading-snug break-words">
                                                                <i class="fa-solid fa-venus text-pink-600 mr-1"></i>{{ $marriage->bride_name }}
                                                            </div>
                                                            <div class="text-xs text-slate-500 leading-snug break-words">
                                                                {{ $marriage->bride_buddhist_name ? $marriage->bride_buddhist_name.' • ' : '' }}Putri Bpk. {{ $marriage->bride_father_name }} &amp; Ibu {{ $marriage->bride_mother_name }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td data-label="Jadwal &amp; Lokasi" class="py-3.5 px-4 align-top text-xs sm:text-sm">
                                                    <div class="font-bold text-slate-900">{{ $marriage->wedding_date->format('d M Y') }}</div>
                                                    <div class="text-slate-600 mt-0.5">{{ $marriage->wedding_time }}</div>
                                                    <div class="text-xs text-slate-500 mt-1 leading-snug break-words">{{ $marriage->wedding_location }}</div>
                                                </td>
                                                <td data-label="Pandita &amp; Saksi" class="py-3.5 px-4 align-top text-xs sm:text-sm">
                                                    <div class="font-bold text-slate-900 leading-snug break-words">{{ $marriage->officiant_name }}</div>
                                                    <div class="text-xs text-slate-600 mt-1 break-words">Saksi I: {{ $marriage->witness_one }}</div>
                                                    <div class="text-xs text-slate-600 break-words">Saksi II: {{ $marriage->witness_two }}</div>
                                                </td>
                                                <td data-label="Aksi" class="mgmt-actions-cell py-3.5 px-4 align-top text-right">
                                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                                        <button type="button" onclick="openMarriagePrintModal(@js($marriage), 'certificate')" class="px-2.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition shadow-xs inline-flex items-center gap-1.5" title="Cetak Surat Pemberkatan Perkawinan A4">
                                                            <i class="fa-solid fa-print"></i>
                                                            <span>Surat A4</span>
                                                        </button>
                                                        <button type="button" onclick="openMarriagePrintModal(@js($marriage), 'checklist')" class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-800 font-semibold text-xs transition inline-flex items-center gap-1.5" title="Cetak Berita Acara & Saksi">
                                                            <i class="fa-solid fa-file-signature"></i>
                                                            <span>Berita Acara</span>
                                                        </button>
                                                        <a href="{{ route('management.marriages.edit', $marriage) }}" class="p-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition inline-flex items-center justify-center w-8 h-8" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('management.marriages.destroy', $marriage) }}" class="inline" onsubmit="return confirm('Hapus arsip surat nikah ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 rounded-xl text-rose-600 hover:bg-rose-50 font-semibold text-xs transition inline-flex items-center justify-center w-8 h-8" title="Hapus">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-10 text-center text-slate-400 text-sm sm:text-base">Belum ada arsip Surat Nikah / Pemberkatan Perkawinan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Footer -->
                            <div class="p-4 border-t border-slate-100 flex items-center justify-center sm:justify-end bg-slate-50/50" id="marriagesPaginationContainer"></div>
                        </div>
                    </section>
                    @endif

                    <!-- ================================================= -->
                    <!-- TAB 3: ROLE MANAGEMENT & USERS                    -->
                    <!-- ================================================= -->
                    @if ($canRoles)
                    <section id="tab-roles" class="mgmt-tab-panel hidden space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            <!-- Roles List Card -->
                            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-6 shadow-xs space-y-4 overflow-hidden min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                                    <div class="min-w-0">
                                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2">
                                            <i class="fa-solid fa-user-shield text-purple-600 shrink-0"></i>
                                            <span class="break-words">Manajemen Role Pengurus</span>
                                        </h2>
                                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Pengaturan hak akses modular untuk Admin dan pengurus internal.</p>
                                    </div>
                                    <a href="{{ route('management.roles.create') }}" class="self-start sm:self-auto px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold transition shrink-0 flex items-center gap-1.5 shadow-xs">
                                        <i class="fa-solid fa-circle-plus"></i>
                                        <span>Buat Role</span>
                                    </a>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="relative w-full sm:w-64">
                                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
                                        <input
                                            type="search"
                                            id="roleSearchInput"
                                            placeholder="Cari role..."
                                            class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm focus:outline-none focus:bg-white"
                                        >
                                    </div>
                                    <div id="rolesCountInfo" class="text-xs sm:text-sm font-semibold text-slate-600"></div>
                                </div>

                                <div id="rolesListContainer" class="space-y-3">
                                    @foreach ($roles as $role)
                                        <div class="role-item-row p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="flex items-center flex-wrap gap-2">
                                                        <h3 class="text-base font-bold text-slate-900 leading-snug break-words">{{ $role->name }}</h3>
                                                        <span class="px-2.5 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 text-xs font-bold shrink-0">{{ $role->users_count }} User</span>
                                                        @if ($role->is_system)
                                                            <span class="px-2.5 py-0.5 rounded-md bg-slate-900 text-white text-xs font-bold shrink-0">Sistem</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed break-words">{{ $role->description }}</p>
                                                </div>
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <a href="{{ route('management.roles.edit', $role) }}" class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-100 flex items-center gap-1.5 shadow-2xs">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                        <span>Edit Izin</span>
                                                    </a>
                                                    @if (! $role->is_system)
                                                        <form method="POST" action="{{ route('management.roles.destroy', $role) }}" onsubmit="return confirm('Hapus role ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 rounded-xl text-rose-600 hover:bg-rose-50 font-semibold text-xs sm:text-sm inline-flex items-center justify-center w-8 h-8" title="Hapus">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach ($role->permissions ?? [] as $permKey)
                                                    <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-medium text-slate-700 flex items-center gap-1.5 break-words">
                                                        <i class="fa-solid fa-check text-emerald-600 text-[10px] shrink-0"></i>
                                                        <span>{{ $availablePermissions[$permKey] ?? $permKey }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div id="rolesPaginationContainer" class="flex justify-center sm:justify-end pt-3 border-t border-slate-100"></div>
                            </div>

                            <!-- Users List Card -->
                            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-6 shadow-xs space-y-4 overflow-hidden min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                                    <div class="min-w-0">
                                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2">
                                            <i class="fa-solid fa-users text-indigo-600 shrink-0"></i>
                                            <span class="break-words">Akun Pengurus Internal</span>
                                        </h2>
                                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftar pengguna sistem manajemen.</p>
                                    </div>
                                    <a href="{{ route('management.users.create') }}" class="self-start sm:self-auto px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold transition shrink-0 flex items-center gap-1.5 shadow-xs">
                                        <i class="fa-solid fa-user-plus"></i>
                                        <span>Tambah</span>
                                    </a>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="relative w-full sm:w-64">
                                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
                                        <input
                                            type="search"
                                            id="userSearchInput"
                                            placeholder="Cari pengurus..."
                                            class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm focus:outline-none focus:bg-white"
                                        >
                                    </div>
                                    <div id="usersCountInfo" class="text-xs sm:text-sm font-semibold text-slate-600"></div>
                                </div>

                                <div id="usersListContainer" class="space-y-3">
                                    @foreach ($users as $u)
                                        <div class="user-item-row p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="font-bold text-slate-900 text-sm sm:text-base leading-snug break-words">{{ $u->name }}</div>
                                                <div class="text-slate-600 text-xs sm:text-sm mt-0.5 leading-snug break-all sm:break-words">{{ $u->email }} &bull; {{ $u->position }}</div>
                                                <span class="inline-flex items-center gap-1 mt-1.5 px-2.5 py-0.5 rounded-md bg-white border border-slate-200 text-slate-800 font-bold text-xs">
                                                    <i class="fa-solid fa-id-badge text-[10px] text-slate-400 shrink-0"></i>
                                                    <span>{{ $u->role?->name ?? 'Tanpa Role' }}</span>
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <a href="{{ route('management.users.edit', $u) }}" class="p-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 font-semibold text-xs sm:text-sm inline-flex items-center justify-center w-8 h-8" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                @if ($currentUser?->id !== $u->id)
                                                    <form method="POST" action="{{ route('management.users.destroy', $u) }}" onsubmit="return confirm('Hapus akun pengurus ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1.5 rounded-xl text-rose-600 hover:bg-rose-50 font-semibold text-xs sm:text-sm inline-flex items-center justify-center w-8 h-8" title="Hapus">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div id="usersPaginationContainer" class="flex justify-center sm:justify-end pt-3 border-t border-slate-100"></div>
                            </div>
                        </div>
                    </section>
                    @endif

                    <!-- ================================================= -->
                    <!-- TAB 4: SCHEDULES                                  -->
                    <!-- ================================================= -->
                    @if ($canSchedules)
                    <section id="tab-schedules" class="mgmt-tab-panel hidden space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4 overflow-hidden">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                                <div>
                                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fa-solid fa-calendar-days text-emerald-600"></i>
                                        <span>Jadwal Ibadah, Puja Bakti, Meditasi &amp; Kegiatan</span>
                                    </h2>
                                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Data jadwal ini tampil secara dinamis di halaman publik vihara.</p>
                                </div>
                                <a href="{{ route('management.schedules.create') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold shrink-0 flex items-center justify-center gap-1.5 shadow-xs">
                                    <i class="fa-solid fa-circle-plus"></i>
                                    <span>Tambah Jadwal</span>
                                </a>
                            </div>

                            <!-- Search & Filter Controls -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="relative w-full sm:w-72">
                                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
                                    <input
                                        type="search"
                                        id="scheduleSearchInput"
                                        placeholder="Cari jadwal, hari, atau lokasi..."
                                        class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm focus:outline-none focus:bg-white"
                                    >
                                </div>
                                <div id="schedulesCountInfo" class="text-xs sm:text-sm font-semibold text-slate-600"></div>
                            </div>

                            <!-- Responsive Table / Mobile Card View -->
                            <div class="w-full overflow-x-auto">
                                <table class="mgmt-responsive-table w-full table-auto text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-200/80 text-slate-600 bg-slate-50/70 text-xs sm:text-sm font-bold uppercase tracking-wider">
                                            <th class="py-3.5 px-4 w-[28%]">Kegiatan</th>
                                            <th class="py-3.5 px-3 w-[16%]">Kategori</th>
                                            <th class="py-3.5 px-4 w-[22%]">Waktu &amp; Hari</th>
                                            <th class="py-3.5 px-4 w-[22%]">Lokasi &amp; Pembimbing</th>
                                            <th class="py-3.5 px-4 text-right w-[12%]">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="schedulesTableBody" class="divide-y divide-slate-100">
                                        @forelse ($schedules as $sch)
                                            <tr class="schedule-row hover:bg-slate-50/80 transition">
                                                <td data-label="Kegiatan" class="py-3.5 px-4 align-top font-bold text-slate-900 text-sm sm:text-base leading-snug break-words">
                                                    {{ $sch->title }}
                                                </td>
                                                <td data-label="Kategori" class="py-3.5 px-3 align-top">
                                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                        {{ $sch->category }}
                                                    </span>
                                                </td>
                                                <td data-label="Waktu & Hari" class="py-3.5 px-4 align-top text-slate-700 font-semibold text-xs sm:text-sm leading-snug">
                                                    <i class="fa-regular fa-clock text-slate-400 mr-1"></i>
                                                    {{ $sch->day_of_week }}, {{ $sch->start_time }} – {{ $sch->end_time }} WIB
                                                </td>
                                                <td data-label="Lokasi & Pembimbing" class="py-3.5 px-4 align-top text-slate-700 text-xs sm:text-sm leading-snug break-words">
                                                    <div class="font-medium"><i class="fa-solid fa-location-dot text-slate-400 mr-1"></i> {{ $sch->location }}</div>
                                                    @if ($sch->speaker)
                                                        <div class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-chalkboard-user text-slate-400 mr-1"></i> {{ $sch->speaker }}</div>
                                                    @endif
                                                </td>
                                                <td data-label="Aksi" class="mgmt-actions-cell py-3.5 px-4 align-top text-right">
                                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                                        <a href="{{ route('management.schedules.edit', $sch) }}" class="p-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs sm:text-sm inline-flex items-center justify-center w-8 h-8" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('management.schedules.destroy', $sch) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 rounded-xl text-rose-600 hover:bg-rose-50 font-semibold text-xs sm:text-sm inline-flex items-center justify-center w-8 h-8" title="Hapus">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-10 text-center text-slate-400 text-sm sm:text-base">Belum ada jadwal kegiatan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="p-4 border-t border-slate-100 flex items-center justify-end bg-slate-50/50" id="schedulesPaginationContainer"></div>
                        </div>
                    </section>
                    @endif

                    <!-- ================================================= -->
                    <!-- TAB 5: GALLERIES (FULL-WIDTH TABLE VIEW)          -->
                    <!-- ================================================= -->
                    @if ($canGalleries)
                    <section id="tab-galleries" class="mgmt-tab-panel hidden space-y-6">
                        <!-- Top Header Banner with Separated Primary Create Button -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2.5">
                                    <i class="fa-solid fa-images text-blue-600 text-xl"></i>
                                    <span>Galeri Foto &amp; Video Kegiatan</span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                                    Kelola dokumentasi foto (upload file gambar) dan video dokumentasi kegiatan (tautan YouTube) yang tampil di halaman utama.
                                </p>
                            </div>
                            <a href="{{ route('management.galleries.create') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold shrink-0 flex items-center justify-center gap-2 shadow-xs">
                                <i class="fa-solid fa-circle-plus text-base"></i>
                                <span>Tambah Dokumentasi</span>
                            </a>
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                            <!-- Search & Filter Controls -->
                            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 min-w-0">
                                    <div class="relative w-full sm:w-72">
                                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
                                        <input
                                            type="search"
                                            id="gallerySearchInput"
                                            placeholder="Cari dokumentasi atau kategori..."
                                            class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm focus:outline-none focus:bg-white"
                                        >
                                    </div>
                                    <select id="galleryFilterType" class="w-full sm:w-auto px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 focus:outline-none focus:bg-white">
                                        <option value="all">Semua Tipe Media</option>
                                        <option value="photo">Foto Kegiatan (File Upload)</option>
                                        <option value="video">Video Kegiatan (YouTube)</option>
                                    </select>
                                </div>
                                <div id="galleriesCountInfo" class="text-xs sm:text-sm font-semibold text-slate-600"></div>
                            </div>

                            <!-- Responsive Table / Mobile Card View -->
                            <div class="w-full overflow-x-auto">
                                <table class="mgmt-responsive-table w-full table-auto text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-200/80 text-slate-600 bg-slate-50/70 text-xs sm:text-sm font-bold uppercase tracking-wider">
                                            <th class="py-3.5 px-4 w-[14%]">Pratinjau</th>
                                            <th class="py-3.5 px-4 w-[32%]">Judul &amp; Keterangan Kegiatan</th>
                                            <th class="py-3.5 px-3 w-[18%]">Tipe &amp; Sumber Media</th>
                                            <th class="py-3.5 px-3 w-[18%]">Kategori &amp; Tanggal</th>
                                            <th class="py-3.5 px-4 text-right w-[18%]">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="galleriesTableBody" class="divide-y divide-slate-100">
                                        @forelse ($galleries as $gal)
                                            <tr data-category="{{ $gal->media_type }}" class="gallery-row hover:bg-slate-50/80 transition">
                                                <td data-label="Pratinjau" class="py-3.5 px-4 align-top">
                                                    <a href="{{ $gal->media_url }}" target="_blank" class="relative block w-28 sm:w-24 h-18 sm:h-16 rounded-xl bg-slate-200 overflow-hidden border border-slate-200 shadow-2xs group">
                                                        <img src="{{ $gal->thumbnail_url ?: $gal->media_url }}" alt="{{ $gal->title }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                                                        @if ($gal->media_type === 'video')
                                                            <span class="absolute inset-0 bg-slate-900/40 flex items-center justify-center">
                                                                <i class="fa-brands fa-youtube text-rose-500 bg-white rounded-full px-1.5 py-0.5 text-sm shadow-xs"></i>
                                                            </span>
                                                        @endif
                                                    </a>
                                                </td>
                                                <td data-label="Judul & Keterangan Kegiatan" class="py-3.5 px-4 align-top">
                                                    <div class="font-bold text-slate-900 text-sm sm:text-base leading-snug break-words">{{ $gal->title }}</div>
                                                    @if ($gal->description)
                                                        <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-snug break-words">{{ $gal->description }}</p>
                                                    @endif
                                                </td>
                                                <td data-label="Tipe & Sumber Media" class="py-3.5 px-3 align-top">
                                                    @if ($gal->media_type === 'video')
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                            <i class="fa-brands fa-youtube text-rose-600"></i>
                                                            <span>Video YouTube</span>
                                                        </span>
                                                        <div class="mt-1">
                                                            <a href="{{ $gal->media_url }}" target="_blank" class="text-xs font-semibold text-rose-700 hover:underline inline-flex items-center gap-1">
                                                                <span>Putar YouTube</span>
                                                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                                            </a>
                                                        </div>
                                                    @else
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                            <i class="fa-solid fa-image text-blue-600"></i>
                                                            <span>Foto Upload</span>
                                                        </span>
                                                        <div class="mt-1">
                                                            <a href="{{ $gal->media_url }}" target="_blank" class="text-xs font-semibold text-blue-700 hover:underline inline-flex items-center gap-1">
                                                                <span>Lihat Foto</span>
                                                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td data-label="Kategori & Tanggal" class="py-3.5 px-3 align-top">
                                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                        {{ $gal->category }}
                                                    </span>
                                                    <div class="text-xs text-slate-500 mt-1.5">
                                                        {{ $gal->event_date ? $gal->event_date->format('d M Y') : 'Dokumentasi Vihara' }}
                                                    </div>
                                                </td>
                                                <td data-label="Aksi" class="mgmt-actions-cell py-3.5 px-4 align-top text-right">
                                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                                        <a href="{{ route('management.galleries.edit', $gal) }}" class="p-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs sm:text-sm inline-flex items-center justify-center w-8 h-8" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('management.galleries.destroy', $gal) }}" class="inline" onsubmit="return confirm('Hapus item galeri ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 rounded-xl text-rose-600 hover:bg-rose-50 font-semibold text-xs sm:text-sm inline-flex items-center justify-center w-8 h-8" title="Hapus">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-10 text-center text-slate-400 text-sm sm:text-base">Belum ada dokumentasi galeri foto atau video.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="p-4 border-t border-slate-100 flex items-center justify-end bg-slate-50/50" id="galleriesPaginationContainer"></div>
                        </div>
                    </section>
                    @endif

                    <!-- ================================================= -->
                    <!-- TAB 6: ARTICLES                                   -->
                    <!-- ================================================= -->
                    @if ($canArticles)
                    <section id="tab-articles" class="mgmt-tab-panel hidden space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4 overflow-hidden">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                                <div>
                                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fa-solid fa-newspaper text-rose-600"></i>
                                        <span>Berita Kegiatan, Pengumuman &amp; Renungan Dharma</span>
                                    </h2>
                                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola artikel dan warta vihara pada halaman utama.</p>
                                </div>
                                <a href="{{ route('management.articles.create') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold shrink-0 flex items-center justify-center gap-1.5 shadow-xs">
                                    <i class="fa-solid fa-circle-plus"></i>
                                    <span>Terbitkan Artikel Baru</span>
                                </a>
                            </div>

                            <!-- Search & Filter Controls -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="relative w-full sm:w-72">
                                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
                                    <input
                                        type="search"
                                        id="articleSearchInput"
                                        placeholder="Cari judul artikel atau pengumuman..."
                                        class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm focus:outline-none focus:bg-white"
                                    >
                                </div>
                                <div id="articlesCountInfo" class="text-xs sm:text-sm font-semibold text-slate-600"></div>
                            </div>

                            <!-- Responsive Table / Mobile Card View -->
                            <div class="w-full overflow-x-auto">
                                <table class="mgmt-responsive-table w-full table-auto text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-200/80 text-slate-600 bg-slate-50/70 text-xs sm:text-sm font-bold uppercase tracking-wider">
                                            <th class="py-3.5 px-4 w-[26%]">Judul Artikel</th>
                                            <th class="py-3.5 px-3 w-[14%]">Kategori</th>
                                            <th class="py-3.5 px-4 w-[18%]">Penulis &amp; Tanggal</th>
                                            <th class="py-3.5 px-4 w-[30%]">Ringkasan</th>
                                            <th class="py-3.5 px-4 text-right w-[12%]">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="articlesTableBody" class="divide-y divide-slate-100">
                                        @forelse ($articles as $art)
                                            <tr class="article-row hover:bg-slate-50/80 transition">
                                                <td data-label="Judul Artikel" class="py-3.5 px-4 align-top font-bold text-slate-900 text-sm sm:text-base leading-snug break-words">
                                                    <a href="{{ route('landing.article', $art->slug) }}" target="_blank" class="hover:underline inline-flex items-center gap-1.5">
                                                        <span>{{ $art->title }}</span>
                                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs text-slate-400"></i>
                                                    </a>
                                                </td>
                                                <td data-label="Kategori" class="py-3.5 px-3 align-top">
                                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                        {{ $art->category }}
                                                    </span>
                                                </td>
                                                <td data-label="Penulis & Tanggal" class="py-3.5 px-4 align-top text-slate-700 text-xs sm:text-sm leading-snug break-words">
                                                    <div class="font-semibold">{{ $art->author }}</div>
                                                    <div class="text-xs text-slate-500 mt-0.5">{{ $art->published_at ? $art->published_at->format('d M Y') : 'Draft' }}</div>
                                                </td>
                                                <td data-label="Ringkasan" class="py-3.5 px-4 align-top text-slate-600 text-xs sm:text-sm leading-relaxed break-words">
                                                    <p>{{ $art->excerpt }}</p>
                                                </td>
                                                <td data-label="Aksi" class="mgmt-actions-cell py-3.5 px-4 align-top text-right">
                                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                                        <a href="{{ route('management.articles.edit', $art) }}" class="p-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs sm:text-sm inline-flex items-center justify-center w-8 h-8" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('management.articles.destroy', $art) }}" onsubmit="return confirm('Hapus artikel ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 rounded-xl text-rose-600 hover:bg-rose-50 font-semibold text-xs sm:text-sm inline-flex items-center justify-center w-8 h-8" title="Hapus">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-10 text-center text-slate-400 text-sm sm:text-base">Belum ada berita atau artikel.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="p-4 border-t border-slate-100 flex items-center justify-end bg-slate-50/50" id="articlesPaginationContainer"></div>
                        </div>
                    </section>
                    @endif

                    <!-- ================================================= -->
                    <!-- TAB 7: PROFILE / ACCOUNT CONFIGURATION            -->
                    <!-- ================================================= -->
                    <section id="tab-profile" class="mgmt-tab-panel hidden space-y-6">
                        <div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-8 shadow-2xs space-y-6">
                            <div class="border-b border-slate-100 pb-4">
                                <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2.5">
                                    <i class="fa-solid fa-user-gear text-slate-700"></i>
                                    <span>Pengaturan Akun &amp; Profil Pengurus</span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola identitas akun pengurus, kontak, serta pembaruan kata sandi Anda.</p>
                            </div>

                            <form method="POST" action="{{ route('management.profile.update') }}" class="space-y-4">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Nama Lengkap *</label>
                                        <input
                                            type="text"
                                            name="name"
                                            value="{{ old('name', $currentUser?->name) }}"
                                            required
                                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900"
                                        >
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Alamat Email *</label>
                                        <input
                                            type="email"
                                            name="email"
                                            value="{{ old('email', $currentUser?->email) }}"
                                            required
                                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900"
                                        >
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Role Akses (Sistem)</label>
                                        <input
                                            type="text"
                                            value="{{ $currentUser?->role?->name ?? 'Pengurus' }}"
                                            disabled
                                            class="w-full px-4 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 font-semibold text-sm sm:text-base cursor-not-allowed"
                                        >
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">Jabatan di Vihara</label>
                                        <input
                                            type="text"
                                            name="position"
                                            value="{{ old('position', $currentUser?->position) }}"
                                            placeholder="Contoh: Sekretaris Vihara"
                                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900"
                                        >
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-1.5">No. Telepon / WhatsApp</label>
                                        <input
                                            type="text"
                                            name="phone"
                                            value="{{ old('phone', $currentUser?->phone) }}"
                                            placeholder="0812..."
                                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900"
                                        >
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-slate-100 space-y-3">
                                    <div class="font-bold text-slate-900 text-sm sm:text-base">Ganti Kata Sandi (Kosongkan bila tidak ingin mengubah)</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block font-semibold text-slate-700 text-xs sm:text-sm mb-1.5">Kata Sandi Baru</label>
                                            <input
                                                type="password"
                                                name="password"
                                                placeholder="Minimal 6 karakter"
                                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900"
                                            >
                                        </div>
                                        <div>
                                            <label class="block font-semibold text-slate-700 text-xs sm:text-sm mb-1.5">Konfirmasi Kata Sandi Baru</label>
                                            <input
                                                type="password"
                                                name="password_confirmation"
                                                placeholder="Ulangi kata sandi baru"
                                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-slate-900"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 pt-5 border-t border-slate-100">
                                    <button
                                        type="submit"
                                        class="w-full sm:w-auto justify-center px-6 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm sm:text-base transition flex items-center gap-2 shadow-xs"
                                    >
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>Simpan Perubahan Akun</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>
                @endif

            </main> 
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL IMPORT EXCEL DATA TANDA TERIMA DENGAN DOKUMENTASI   -->
    <!-- ========================================================= -->
    <div id="importExcelModal" class="fixed inset-0 z-50 hidden bg-slate-900/75 backdrop-blur-xs overflow-y-auto p-3 sm:p-6 flex flex-col items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-4xl w-full max-h-[92vh] flex flex-col overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-150">
            <!-- Modal Header -->
            <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/80 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                        <i class="fa-solid fa-file-excel"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-lg font-bold text-slate-900 leading-snug">Import Data Tanda Terima via Excel (.xlsx / .csv)</h3>
                        <p class="text-xs text-slate-500">Unggah berkas spreadsheet untuk memasukkan data penerimaan donasi dan bantuan secara massal.</p>
                    </div>
                </div>
                <button type="button" onclick="closeImportExcelModal()" class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/70 flex items-center justify-center shrink-0 transition" title="Tutup Modal">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-4 sm:p-6 overflow-y-auto space-y-6 flex-1 text-slate-700">
                <!-- Step 1: Download Template Callout -->
                <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fa-solid fa-file-arrow-down text-sm"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-emerald-950">Gunakan Format Template Resmi Wihara Mahabodhi</div>
                            <p class="text-xs text-emerald-800/90 mt-0.5">Pastikan susunan kolom pada berkas Excel Anda persis mengikuti template resmi agar seluruh baris data dapat diproses otomatis.</p>
                        </div>
                    </div>
                    <a href="{{ route('management.receipts.template') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shrink-0 flex items-center justify-center gap-2 shadow-xs transition">
                        <i class="fa-solid fa-download"></i>
                        <span>Unduh Template (.xlsx)</span>
                    </a>
                </div>

                <!-- Step 2: Upload File Form -->
                <form id="importExcelForm" method="POST" action="{{ route('management.receipts.import') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-800 text-xs sm:text-sm mb-2">Pilih Berkas Excel / CSV *</label>
                        <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-2xl p-5 sm:p-6 text-center bg-slate-50/60 hover:bg-emerald-50/30 transition cursor-pointer" onclick="document.getElementById('modalExcelInput').click()">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-emerald-600/70 mb-2"></i>
                            <div class="text-xs sm:text-sm font-bold text-slate-800 break-words" id="modalFileName">Klik di sini untuk memilih berkas spreadsheet (.xlsx, .xls, .csv)</div>
                            <p class="text-xs text-slate-400 mt-1">Maksimal ukuran berkas 5 MB &bull; Disarankan format OpenXML .xlsx</p>
                            <input
                                type="file"
                                id="modalExcelInput"
                                name="excel_file"
                                accept=".xlsx,.xls,.csv,.txt"
                                required
                                class="hidden"
                                onchange="updateModalFileName(this)"
                            >
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 pt-2">
                        <button type="button" onclick="closeImportExcelModal()" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs sm:text-sm transition">
                            Batal
                        </button>
                        <button type="submit" class="w-full sm:w-auto justify-center px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm transition flex items-center gap-2 shadow-xs">
                            <i class="fa-solid fa-upload"></i>
                            <span>Mulai Proses Import Data</span>
                        </button>
                    </div>
                </form>

                <!-- Step 3: Complete Field Documentation Table -->
                <div class="space-y-3 pt-4 border-t border-slate-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h4 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-book-open text-blue-600"></i>
                                <span>Dokumentasi Spesifikasi Field / Kolom Excel</span>
                            </h4>
                            <p class="text-xs text-slate-500 mt-0.5">Panduan aturan pengisian untuk setiap kolom pada baris data Excel.</p>
                        </div>
                        <span class="self-start sm:self-auto px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-bold text-xs shrink-0">Total: 12 Kolom</span>
                    </div>

                    <div class="w-full rounded-xl border border-slate-200 shadow-2xs overflow-x-auto">
                        <table class="w-full min-w-[640px] table-auto text-left text-xs border-collapse">
                            <thead class="bg-slate-100/90 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="p-3 w-[6%] text-center">No</th>
                                    <th class="p-3 w-[20%]">Header Kolom</th>
                                    <th class="p-3 w-[12%] text-center">Status</th>
                                    <th class="p-3 w-[14%]">Tipe Data</th>
                                    <th class="p-3 w-[32%]">Aturan &amp; Penjelasan Pengisian</th>
                                    <th class="p-3 w-[16%]">Contoh Nilai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center font-bold text-slate-400">1</td>
                                    <td class="p-3 font-bold text-slate-900">No Tanda Terima</td>
                                    <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold text-[11px]">Opsional</span></td>
                                    <td class="p-3 text-slate-600 font-medium">Teks</td>
                                    <td class="p-3 text-slate-600">Boleh dikosongkan. Jika kosong, sistem otomatis membuatkan nomor tanda terima resmi vihara (format: <code>001/TT-WMBC/XI/2026</code>).</td>
                                    <td class="p-3 text-slate-800 font-mono text-[11px]">001/TT-WMBC/XI/2026</td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center font-bold text-slate-400">2</td>
                                    <td class="p-3 font-bold text-slate-900">Jenis</td>
                                    <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 font-bold text-[11px]">Wajib</span></td>
                                    <td class="p-3 text-slate-600 font-medium">Pilihan Baku</td>
                                    <td class="p-3 text-slate-600">Wajib diisi persis salah satu dari tiga opsi: <code>Bantuan Dana</code>, <code>Barang</code>, atau <code>Surat / Dokumen</code>.</td>
                                    <td class="p-3 text-slate-800 font-semibold">Bantuan Dana</td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center font-bold text-slate-400">3</td>
                                    <td class="p-3 font-bold text-slate-900">Tanggal</td>
                                    <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 font-bold text-[11px]">Wajib</span></td>
                                    <td class="p-3 text-slate-600 font-medium">Format Tanggal</td>
                                    <td class="p-3 text-slate-600">Tanggal penyerahan donasi atau barang di sekretariat vihara. Format yang disarankan: <code>YYYY-MM-DD</code> atau <code>DD/MM/YYYY</code>.</td>
                                    <td class="p-3 text-slate-800 font-mono text-[11px]">2026-11-08</td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center font-bold text-slate-400">4</td>
                                    <td class="p-3 font-bold text-slate-900">Nama Penyerah / Donatur</td>
                                    <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 font-bold text-[11px]">Wajib</span></td>
                                    <td class="p-3 text-slate-600 font-medium">Teks Bebas</td>
                                    <td class="p-3 text-slate-600">Nama lengkap umat, perorangan, keluarga, atau donatur yang menyerahkan dana/barang/surat.</td>
                                    <td class="p-3 text-slate-800">Bpk. Suryadi Kusuma</td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center font-bold text-slate-400">5</td>
                                    <td class="p-3 font-bold text-slate-900">Instansi / Alamat</td>
                                    <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold text-[11px]">Opsional</span></td>
                                    <td class="p-3 text-slate-600 font-medium">Teks Bebas</td>
                                    <td class="p-3 text-slate-600">Nama instansi, badan usaha, yayasan, atau alamat domisili donatur.</td>
                                    <td class="p-3 text-slate-800">Umat Wihara Mahabodhi Semarang</td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center font-bold text-slate-400">6</td>
                                    <td class="p-3 font-bold text-slate-900">Kontak / Telepon</td>
                                    <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold text-[11px]">Opsional</span></td>
                                    <td class="p-3 text-slate-600 font-medium">Teks / Angka</td>
                                    <td class="p-3 text-slate-600">Nomor handphone atau WhatsApp donatur untuk kemudahan konfirmasi tanda terima.</td>
                                    <td class="p-3 text-slate-800">081234567890</td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center font-bold text-slate-400">7</td>
                                    <td class="p-3 font-bold text-slate-900">Uraian Bantuan / Barang / Surat</td>
                                    <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 font-bold text-[11px]">Wajib</span></td>
                                    <td class="p-3 text-slate-600 font-medium">Teks Bebas</td>
                                    <td class="p-3 text-slate-600">Keterangan rincian barang, peruntukan dana kebajikan, atau perihal dokumen surat yang diserahkan.</td>
                                    <td class="p-3 text-slate-800">Dana Paramita Operasional Puja Bakti &amp; Pelita Altar</td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center font-bold text-slate-400">8</td>
                                    <td class="p-3 font-bold text-slate-900">Jumlah / Kuantitas</td>
                                    <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 font-bold text-[11px]">Wajib</span></td>
                                    <td class="p-3 text-slate-600 font-medium">Teks / Satuan</td>
                                    <td class="p-3 text-slate-600">Besaran kuantitas bantuan, contoh: <code>1 Paket Dana</code>, <code>10 Karung @ 25 Kg</code>, <code>1 Berkas</code>.</td>
                                    <td class="p-3 text-slate-800">1 Paket Dana</td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center font-bold text-slate-400">9</td>
                                    <td class="p-3 font-bold text-slate-900">Nominal (Rp)</td>
                                    <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-bold text-[11px]">Khusus Dana</span></td>
                                    <td class="p-3 text-slate-600 font-medium">Angka Numerik</td>
                                    <td class="p-3 text-slate-600">Angka nominal murni <strong>tanpa simbol Rp, titik, atau koma</strong>. Wajib diisi jika Jenis adalah Bantuan Dana. Boleh kosong jika Barang/Surat.</td>
                                    <td class="p-3 text-slate-800 font-mono text-[11px]">1000000</td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center font-bold text-slate-400">10</td>
                                    <td class="p-3 font-bold text-slate-900">Petugas Penerima</td>
                                    <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold text-[11px]">Opsional</span></td>
                                    <td class="p-3 text-slate-600 font-medium">Teks Bebas</td>
                                    <td class="p-3 text-slate-600">Nama pengurus vihara yang bertugas menerima. Jika dikosongkan, sistem otomatis memakai nama pengurus yang sedang login saat import.</td>
                                    <td class="p-3 text-slate-800">Upasika Metta Kusuma</td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center font-bold text-slate-400">11</td>
                                    <td class="p-3 font-bold text-slate-900">Ucapan Terima Kasih (Anumodana)</td>
                                    <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold text-[11px]">Opsional</span></td>
                                    <td class="p-3 text-slate-600 font-medium">Teks Bebas</td>
                                    <td class="p-3 text-slate-600">Pesan anumodana khusus untuk dicetak pada lembar surat terima kasih. Jika kosong, sistem otomatis mengisi dengan kalimat doa baku.</td>
                                    <td class="p-3 text-slate-800">Anumodana atas kebajikan Bapak sekeluarga. Semoga semua makhluk berbahagia.</td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center font-bold text-slate-400">12</td>
                                    <td class="p-3 font-bold text-slate-900">Catatan</td>
                                    <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold text-[11px]">Opsional</span></td>
                                    <td class="p-3 text-slate-600 font-medium">Teks Bebas</td>
                                    <td class="p-3 text-slate-600">Catatan internal sekretariat vihara (misal: lokasi penyimpanan di gudang, transfer via rekening BCA, kondisi barang, dll).</td>
                                    <td class="p-3 text-slate-800">Diterima via Sekretariat Wihara</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-4 sm:px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-500 shrink-0">
                <div class="flex items-start sm:items-center gap-2">
                    <i class="fa-solid fa-lightbulb text-amber-500 mt-0.5 sm:mt-0"></i>
                    <span>Baris pertama file Excel harus berupa judul kolom (header). Baris kosong akan dilewati otomatis.</span>
                </div>
                <button type="button" onclick="closeImportExcelModal()" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold transition">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL & LEMBAR CETAK STANDAR KERTAS A4 DENGAN KOP [logo]  -->
    <!-- ========================================================= -->
    <div id="a4PrintWrapper" class="fixed inset-0 z-50 hidden bg-slate-900/75 backdrop-blur-xs overflow-y-auto p-3 sm:p-8 flex flex-col items-center">
        <!-- Control Bar -->
        <div class="no-print max-w-[210mm] w-full mb-4 bg-slate-900 text-white px-4 sm:px-5 py-3 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-lg">
            <div class="text-xs sm:text-sm flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-amber-400"></i>
                <span class="font-bold">Pratinjau Cetak Kertas A4</span>
                <span class="text-slate-400 ml-2 hidden sm:inline">Template surat resmi dengan placeholder [logo]</span>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button type="button" onclick="window.print()" class="flex-1 sm:flex-none justify-center px-4 py-2 rounded-xl bg-white text-slate-900 hover:bg-slate-100 text-xs sm:text-sm font-bold transition flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak / Simpan PDF (A4)</span>
                </button>
                <button type="button" onclick="closeA4PrintModal()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs sm:text-sm font-semibold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Tutup</span>
                </button>
            </div>
        </div>

        <!-- Actual A4 Sheet (Strictly 1-Page A4 Official Letter Standard) -->
        <div class="a4-sheet bg-white text-slate-900 w-full max-w-[210mm] p-4 sm:p-9 print:p-0 rounded-xl shadow-2xl border border-slate-300 space-y-4 print:space-y-3 overflow-x-auto">
            <div class="space-y-4 print:space-y-3">
                <!-- KOP SURAT RESMI STANDAR TATA NASKAH DINAS DENGAN PLACEHOLDER [logo] -->
                <div class="border-b-4 border-double border-slate-900 pb-3 flex items-center gap-4">
                    <div class="w-16 h-16 rounded-lg border-2 border-slate-800 bg-slate-50 flex flex-col items-center justify-center shrink-0 text-slate-900 p-1.5 text-center">
                        <i class="fa-solid fa-dharmachakra text-lg text-amber-700 mb-0.5"></i>
                        <span class="font-bold text-[11px] tracking-wider">[logo]</span>
                    </div>
                    <div class="flex-1 min-w-0 text-center pr-2">
                        <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-600">Majelis Agama Buddha &bull; Sekretariat Pengurus Vihara</div>
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 uppercase tracking-wide mt-0.5">
                            Wihara Mahabodhi Buddhist Centre
                        </h2>
                        <p class="text-[11px] text-slate-700 font-medium mt-0.5">
                            Jl. Seroja Timur No. 9-11, Kel. Karangkidul, Kec. Semarang Tengah, Kota Semarang, Jawa Tengah 50136
                        </p>
                        <p class="text-[10px] text-slate-600">
                            Telp: (024) 841-5509 / 0812-2500-8899 &bull; Email: sekretariat@mahabodhi.or.id &bull; Kode Pos: 50136
                        </p>
                    </div>
                </div>

                <!-- Dynamic A4 Document Body -->
                <div id="a4DocumentContent" class="space-y-3 print:space-y-2.5 text-xs leading-snug text-slate-900"></div>
            </div>

            <!-- A4 Official Signature Footer (Compact so 1-Page Print Never Overflows) -->
            <div class="pt-4 border-t border-slate-300 mt-4">
                <div class="grid grid-cols-2 gap-8 text-xs text-center">
                    <div class="flex flex-col justify-between">
                        <div>
                            <div class="text-[10px] text-slate-500">Mengetahui / Menyerahkan,</div>
                            <div class="font-bold text-slate-900 text-xs mt-0.5" id="a4LeftSignTitle">Pihak Penyerah / Pengirim</div>
                        </div>
                        <div class="pt-10">
                            <div class="font-bold underline text-slate-900 tracking-wide text-xs" id="a4LeftSignName">( ........................................ )</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">Tanda Tangan &amp; Nama Terang</div>
                        </div>
                    </div>
                    <div class="flex flex-col justify-between">
                        <div>
                            <div class="text-[10px] text-slate-700 font-medium">Semarang, <span id="a4SignDate" class="font-bold">{{ now()->format('d M Y') }}</span></div>
                            <div class="font-bold text-slate-900 text-xs mt-0.5" id="a4RightSignTitle">Petugas Sekretariat / Penerima</div>
                        </div>
                        <div class="pt-10">
                            <div class="font-bold underline text-slate-900 tracking-wide text-xs" id="a4RightSignName">Sekretariat Wihara Mahabodhi</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">[logo] Cap &amp; Stempel Resmi Sekretariat Vihara</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const tabTitles = {
            overview: 'Ringkasan Operasional Sekretariat',
            receipts: 'Tanda Terima & Ucapan Terima Kasih (A4 & Excel)',
            documents: 'Arsip Persuratan & Dokumen Resmi Sekretariat',
            marriages: 'Administrasi Surat Nikah & Pemberkatan Perkawinan Buddhis',
            schedules: 'Jadwal Ibadah, Puja Bakti & Meditasi',
            galleries: 'Galeri Dokumentasi Kegiatan',
            articles: 'Berita Kegiatan & Renungan Dharma',
            roles: 'Manajemen Role & Akun Pengurus',
            profile: 'Pengaturan Akun & Profil'
        };

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        function toggleProfileDropdown() {
            const menu = document.getElementById('profileDropdownMenu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        }

        function closeProfileDropdown() {
            const menu = document.getElementById('profileDropdownMenu');
            if (menu) {
                menu.classList.add('hidden');
            }
        }

        // Close dropdown when clicked outside
        document.addEventListener('click', function(e) {
            const container = document.getElementById('profileDropdownContainer');
            if (container && !container.contains(e.target)) {
                closeProfileDropdown();
            }
        });

        function switchManagementTab(tabName) {
            const panels = document.querySelectorAll('.mgmt-tab-panel');
            const buttons = document.querySelectorAll('.mgmt-tab-btn');
            const titleEl = document.getElementById('topbarTitle');

            panels.forEach(function (panel) {
                if (panel.id === 'tab-' + tabName) {
                    panel.classList.remove('hidden');
                } else {
                    panel.classList.add('hidden');
                }
            });

            buttons.forEach(function (btn) {
                if (btn.getAttribute('data-tab-target') === tabName) {
                    btn.className = 'mgmt-tab-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left transition bg-white text-slate-900 font-bold shadow-xs text-sm';
                } else {
                    btn.className = 'mgmt-tab-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left transition text-slate-300 hover:bg-slate-800/80 hover:text-white text-sm font-semibold';
                }
            });

            if (titleEl && tabTitles[tabName]) {
                titleEl.textContent = tabTitles[tabName];
            }

            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar && !sidebar.classList.contains('-translate-x-full') && window.innerWidth < 1024) {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        /* Reusable Client-Side Table Pagination & Search Controller */
        class ClientTablePaginator {
            constructor(options) {
                this.container = document.querySelector(options.container);
                this.rowSelector = options.rowSelector;
                this.searchInput = options.searchInput ? document.querySelector(options.searchInput) : null;
                this.filterSelect = options.filterSelect ? document.querySelector(options.filterSelect) : null;
                this.filterPills = options.filterPills ? document.querySelectorAll(options.filterPills) : null;
                this.paginationContainer = options.paginationContainer ? document.querySelector(options.paginationContainer) : null;
                this.infoContainer = options.infoContainer ? document.querySelector(options.infoContainer) : null;
                this.pageSize = options.pageSize || 8;
                this.currentPage = 1;
                this.searchQuery = '';
                this.activeFilter = 'all';

                if (!this.container) return;
                this.init();
            }

            getRows() {
                return Array.from(this.container.querySelectorAll(this.rowSelector));
            }

            init() {
                if (this.searchInput) {
                    this.searchInput.addEventListener('input', (e) => {
                        this.searchQuery = e.target.value.toLowerCase().trim();
                        this.currentPage = 1;
                        this.render();
                    });
                }

                if (this.filterSelect) {
                    this.filterSelect.addEventListener('change', (e) => {
                        this.activeFilter = e.target.value;
                        this.currentPage = 1;
                        this.render();
                    });
                }

                if (this.filterPills) {
                    this.filterPills.forEach(pill => {
                        pill.addEventListener('click', () => {
                            this.filterPills.forEach(p => {
                                p.classList.remove('bg-slate-900', 'text-white');
                                p.classList.add('bg-slate-100', 'text-slate-700', 'hover:bg-slate-200');
                            });
                            pill.classList.remove('bg-slate-100', 'text-slate-700', 'hover:bg-slate-200');
                            pill.classList.add('bg-slate-900', 'text-white');
                            this.activeFilter = pill.getAttribute('data-filter') || 'all';
                            this.currentPage = 1;
                            this.render();
                        });
                    });
                }

                this.render();
            }

            getMatchingRows() {
                const rows = this.getRows();
                return rows.filter(row => {
                    const text = row.textContent.toLowerCase();
                    const matchesSearch = !this.searchQuery || text.includes(this.searchQuery);
                    
                    let matchesFilter = true;
                    if (this.activeFilter && this.activeFilter !== 'all') {
                        const cat = row.getAttribute('data-category') || row.getAttribute('data-doc-cat') || '';
                        matchesFilter = (cat === this.activeFilter);
                    }

                    return matchesSearch && matchesFilter;
                });
            }

            render() {
                const allRows = this.getRows();
                const matchingRows = this.getMatchingRows();
                const totalItems = matchingRows.length;
                const totalPages = Math.ceil(totalItems / this.pageSize) || 1;

                if (this.currentPage > totalPages) this.currentPage = totalPages;
                if (this.currentPage < 1) this.currentPage = 1;

                const startIndex = (this.currentPage - 1) * this.pageSize;
                const endIndex = startIndex + this.pageSize;

                allRows.forEach(row => row.classList.add('hidden'));

                matchingRows.slice(startIndex, endIndex).forEach(row => {
                    row.classList.remove('hidden');
                });

                if (this.infoContainer) {
                    if (totalItems === 0) {
                        this.infoContainer.textContent = '0 data ditemukan';
                    } else {
                        const startDisplay = startIndex + 1;
                        const endDisplay = Math.min(endIndex, totalItems);
                        this.infoContainer.textContent = `${startDisplay}-${endDisplay} dari ${totalItems} data`;
                    }
                }

                if (this.paginationContainer) {
                    if (totalPages <= 1) {
                        this.paginationContainer.innerHTML = '';
                        return;
                    }

                    let html = '<div class="flex flex-wrap items-center justify-center sm:justify-end gap-1.5">';
                    
                    const prevDisabled = this.currentPage === 1;
                    html += `<button type="button" class="px-3.5 py-1.5 text-xs sm:text-sm rounded-xl border border-slate-200 transition flex items-center gap-1 ${prevDisabled ? 'opacity-40 cursor-not-allowed bg-slate-50 text-slate-400' : 'bg-white hover:bg-slate-100 text-slate-700 font-bold shadow-2xs'}" ${prevDisabled ? 'disabled' : ''} data-page="${this.currentPage - 1}"><i class="fa-solid fa-chevron-left text-[10px]"></i> Prev</button>`;

                    for (let i = 1; i <= totalPages; i++) {
                        if (i === 1 || i === totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                            const isActive = i === this.currentPage;
                            html += `<button type="button" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-xs sm:text-sm rounded-xl font-bold transition shadow-2xs ${isActive ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 hover:bg-slate-100 text-slate-700'}" data-page="${i}">${i}</button>`;
                        } else if (i === this.currentPage - 2 || i === this.currentPage + 2) {
                            html += `<span class="px-1 text-slate-400 text-xs sm:text-sm font-bold">...</span>`;
                        }
                    }

                    const nextDisabled = this.currentPage === totalPages;
                    html += `<button type="button" class="px-3.5 py-1.5 text-xs sm:text-sm rounded-xl border border-slate-200 transition flex items-center gap-1 ${nextDisabled ? 'opacity-40 cursor-not-allowed bg-slate-50 text-slate-400' : 'bg-white hover:bg-slate-100 text-slate-700 font-bold shadow-2xs'}" ${nextDisabled ? 'disabled' : ''} data-page="${this.currentPage + 1}">Next <i class="fa-solid fa-chevron-right text-[10px]"></i></button>`;
                    html += '</div>';

                    this.paginationContainer.innerHTML = html;

                    this.paginationContainer.querySelectorAll('button[data-page]').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const page = parseInt(btn.getAttribute('data-page'));
                            if (page >= 1 && page <= totalPages && page !== this.currentPage) {
                                this.currentPage = page;
                                this.render();
                            }
                        });
                    });
                }
            }
        }

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML;
        }

        function formatRupiah(nominal) {
            if (nominal === null || nominal === undefined || nominal === '') {
                return '—';
            }
            const num = Number(nominal);
            if (isNaN(num)) {
                return '—';
            }
            return 'Rp ' + num.toLocaleString('id-ID');
        }

        function openReceiptPrintModal(receipt, mode) {
            const wrapper = document.getElementById('a4PrintWrapper');
            const content = document.getElementById('a4DocumentContent');
            const dateStr = (receipt.receipt_date || '').substring(0, 10);

            document.getElementById('a4SignDate').textContent = dateStr;
            document.getElementById('a4LeftSignTitle').textContent = 'Pihak Penyerah / Donatur';
            document.getElementById('a4LeftSignName').textContent = receipt.donor_name || 'Donatur';
            document.getElementById('a4RightSignTitle').textContent = 'Petugas Penerima Sekretariat';
            document.getElementById('a4RightSignName').textContent = receipt.receiver_name || 'Sekretariat Wihara Mahabodhi';

            if (mode === 'thank_you') {
                content.innerHTML = `
                    <!-- Standard Formal Indonesian Letter Header -->
                    <div class="flex items-start justify-between gap-4 text-xs text-slate-800 pt-1">
                        <table class="border-collapse">
                            <tr>
                                <td class="py-0.5 pr-3 font-semibold w-24">Nomor</td>
                                <td class="py-0.5 pr-2">:</td>
                                <td class="py-0.5 font-bold text-slate-900">${escapeHtml(receipt.receipt_number)}</td>
                            </tr>
                            <tr>
                                <td class="py-0.5 pr-3 font-semibold">Lampiran</td>
                                <td class="py-0.5 pr-2">:</td>
                                <td class="py-0.5">1 (Satu) Lembar Salinan Tanda Terima</td>
                            </tr>
                            <tr>
                                <td class="py-0.5 pr-3 font-semibold">Perihal</td>
                                <td class="py-0.5 pr-2">:</td>
                                <td class="py-0.5 font-bold underline text-slate-900">Surat Ucapan Terima Kasih (Anumodana)</td>
                            </tr>
                        </table>
                        <div class="text-right font-medium">
                            Semarang, <strong>${escapeHtml(dateStr)}</strong>
                        </div>
                    </div>

                    <div class="space-y-3.5 text-xs sm:text-sm text-slate-800 pt-2">
                        <div class="p-3.5 rounded-lg border border-slate-300 bg-slate-50/70 w-full sm:w-2/3">
                            <div class="text-xs text-slate-500">Kepada Yth.</div>
                            <div class="font-bold text-slate-900 text-sm mt-0.5">Bapak / Ibu / Dermawan ${escapeHtml(receipt.donor_name)}</div>
                            <div class="text-xs text-slate-700">${escapeHtml(receipt.donor_institution || 'Di Tempat')}</div>
                        </div>

                        <p class="font-bold italic text-slate-900">Namo Sanghyang Adi Buddhaya, Namo Buddhaya.</p>

                        <p class="leading-relaxed text-justify">
                            Semoga pancaran cinta kasih (Metta) dan berkah Tiratana senantiasa melimpah kepada Bapak/Ibu/Saudara/i beserta seluruh keluarga. Melalui surat resmi ini, Pengurus <strong>Wihara Mahabodhi Buddhist Centre Semarang</strong> menyampaikan apresiasi serta ucapan terima kasih yang sebesar-besarnya (Anumodana) atas partisipasi kebajikan berupa:
                        </p>

                        <table class="w-full text-xs border border-slate-400 border-collapse">
                            <tr class="border-b border-slate-300">
                                <td class="p-2.5 font-bold bg-slate-100 w-1/3 border-r border-slate-300">Kategori Penerimaan</td>
                                <td class="p-2.5 font-semibold text-slate-900">${escapeHtml(receipt.receipt_type)}</td>
                            </tr>
                            <tr class="border-b border-slate-300">
                                <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Uraian &amp; Peruntukan</td>
                                <td class="p-2.5 font-bold text-slate-900">${escapeHtml(receipt.item_name)}</td>
                            </tr>
                            <tr class="border-b border-slate-300">
                                <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Kuantitas / Satuan</td>
                                <td class="p-2.5 text-slate-800">${escapeHtml(receipt.quantity)}</td>
                            </tr>
                            <tr class="border-b border-slate-300">
                                <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Nominal Tercatat</td>
                                <td class="p-2.5 font-extrabold text-slate-900 text-sm">${formatRupiah(receipt.nominal)}</td>
                            </tr>
                            <tr>
                                <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Diterima Oleh Sekretariat</td>
                                <td class="p-2.5 text-slate-800">${escapeHtml(receipt.receiver_name)} (Tanggal: ${escapeHtml(dateStr)})</td>
                            </tr>
                        </table>

                        <div class="p-3.5 rounded-lg bg-slate-50 border-l-4 border-slate-800 text-xs sm:text-sm leading-relaxed text-justify">
                            ${escapeHtml(receipt.thank_you_message || 'Semoga jasa kebajikan (Punnadhamma) yang telah Bapak/Ibu tanamkan berbuah kebahagiaan, kesehatan, kemakmuran, serta perlindungan Dharma bagi Bapak/Ibu sekeluarga.')}
                        </div>

                        <p class="leading-relaxed text-justify">
                            Demikian Surat Ucapan Terima Kasih ini kami sampaikan sebagai bukti pencatatan resmi Sekretariat Wihara Mahabodhi Buddhist Centre Semarang.
                        </p>

                        <p class="font-bold italic text-slate-900">Sabbe Satta Bhavantu Sukhitatta. Sadhu, Sadhu, Sadhu.</p>
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <!-- Formal Official Receipt Title -->
                    <div class="text-center border-b border-slate-300 pb-3 space-y-1">
                        <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Bukti Resmi Penerimaan Sekretariat Vihara</div>
                        <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 underline tracking-wide">
                            BUKTI TANDA TERIMA ${escapeHtml((receipt.receipt_type || '').toUpperCase())}
                        </h3>
                        <div class="text-xs text-slate-700">
                            Nomor Registrasi: <strong class="font-mono text-slate-900">${escapeHtml(receipt.receipt_number)}</strong> &bull; Tanggal: <strong>${escapeHtml(dateStr)}</strong>
                        </div>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-800 leading-relaxed">
                        Sekretariat Pengurus <strong>Wihara Mahabodhi Buddhist Centre Semarang</strong> menerangkan telah menerima penyerahan dengan rincian lengkap sebagai berikut:
                    </p>

                    <table class="w-full text-xs border border-slate-400 border-collapse">
                        <tr class="border-b border-slate-300">
                            <td class="p-2.5 font-bold bg-slate-100 w-[34%] border-r border-slate-300">Telah Diterima Dari</td>
                            <td class="p-2.5 font-bold text-slate-900 text-sm">${escapeHtml(receipt.donor_name)}</td>
                        </tr>
                        <tr class="border-b border-slate-300">
                            <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Instansi / Alamat Domisili</td>
                            <td class="p-2.5 text-slate-800">${escapeHtml(receipt.donor_institution || '—')}</td>
                        </tr>
                        <tr class="border-b border-slate-300">
                            <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Nomor Telepon / Kontak</td>
                            <td class="p-2.5 text-slate-800">${escapeHtml(receipt.donor_contact || '—')}</td>
                        </tr>
                        <tr class="border-b border-slate-300">
                            <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Kategori Penerimaan</td>
                            <td class="p-2.5 font-semibold text-slate-900">${escapeHtml(receipt.receipt_type)}</td>
                        </tr>
                        <tr class="border-b border-slate-300">
                            <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Uraian Bantuan / Barang / Surat</td>
                            <td class="p-2.5 font-bold text-slate-900">${escapeHtml(receipt.item_name)}</td>
                        </tr>
                        <tr class="border-b border-slate-300">
                            <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Jumlah / Kuantitas</td>
                            <td class="p-2.5 font-semibold text-slate-900">${escapeHtml(receipt.quantity)}</td>
                        </tr>
                        <tr class="border-b border-slate-300">
                            <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Petugas Penerima Sekretariat</td>
                            <td class="p-2.5 text-slate-800">${escapeHtml(receipt.receiver_name)}</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Catatan Pemeriksaan Sekretariat</td>
                            <td class="p-2.5 text-slate-800">${escapeHtml(receipt.notes || 'Telah diterima dalam keadaan baik, lengkap, dan dicatat dalam buku induk sekretariat vihara.')}</td>
                        </tr>
                    </table>

                    <!-- Highlighted Official Nominal Box -->
                    <div class="p-3.5 rounded-lg border-2 border-slate-900 bg-slate-50 flex items-center justify-between gap-4">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-700">Jumlah Nominal Tercatat:</div>
                        <div class="text-base sm:text-lg font-extrabold text-slate-900 font-mono">${formatRupiah(receipt.nominal)}</div>
                    </div>

                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-300 text-xs italic text-slate-700 leading-relaxed">
                        <strong>Ucapan Anumodana:</strong> "${escapeHtml(receipt.thank_you_message || 'Sabbadanam Dhammadanam Jinati — Anumodana atas kebajikan Bapak/Ibu. Semoga semua makhluk hidup berbahagia.')}"
                    </div>
                `;
            }

            wrapper.classList.remove('hidden');
        }

        function openDocumentPrintModal(doc) {
            const wrapper = document.getElementById('a4PrintWrapper');
            const content = document.getElementById('a4DocumentContent');
            const dateStr = (doc.document_date || '').substring(0, 10);

            document.getElementById('a4SignDate').textContent = dateStr;
            document.getElementById('a4LeftSignTitle').textContent = 'Pihak Pengirim / Pembuat Naskah';
            document.getElementById('a4LeftSignName').textContent = doc.sender || 'Pengirim';
            document.getElementById('a4RightSignTitle').textContent = 'Penerima / Arsiparis Sekretariat';
            document.getElementById('a4RightSignName').textContent = doc.recipient || 'Sekretariat Wihara Mahabodhi';

            content.innerHTML = `
                <!-- Formal Master Document Sheet Title -->
                <div class="text-center border-b border-slate-300 pb-3 space-y-1">
                    <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Lembar Kendali &amp; Salinan Arsip Naskah Dinas (${escapeHtml(doc.paper_size || 'A4')})</div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 underline tracking-wide">
                        ${escapeHtml((doc.category || 'DOKUMEN RESMI').toUpperCase())}
                    </h3>
                    <div class="text-xs text-slate-700">
                        Nomor Dokumen: <strong class="font-mono text-slate-900">${escapeHtml(doc.document_number)}</strong> &bull; Kode Klasifikasi Arsip: <strong class="font-mono text-slate-900">${escapeHtml(doc.archive_code || 'ARS-A4')}</strong>
                    </div>
                </div>

                <!-- Structured Metadata Table -->
                <table class="w-full text-xs border border-slate-400 border-collapse">
                    <tr class="border-b border-slate-300">
                        <td class="p-2.5 font-bold bg-slate-100 w-[30%] border-r border-slate-300">Perihal / Judul Dokumen</td>
                        <td class="p-2.5 font-bold text-slate-900 text-sm" colspan="3">${escapeHtml(doc.title)}</td>
                    </tr>
                    <tr class="border-b border-slate-300">
                        <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Tanggal Surat / Naskah</td>
                        <td class="p-2.5 text-slate-800 w-[25%] border-r border-slate-300">${escapeHtml(dateStr)}</td>
                        <td class="p-2.5 font-bold bg-slate-100 w-[20%] border-r border-slate-300">Standar Kertas</td>
                        <td class="p-2.5 font-semibold text-slate-900">${escapeHtml(doc.paper_size || 'A4')}</td>
                    </tr>
                    <tr class="border-b border-slate-300">
                        <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Asal / Pihak Pengirim</td>
                        <td class="p-2.5 font-semibold text-slate-900" colspan="3">${escapeHtml(doc.sender)}</td>
                    </tr>
                    <tr>
                        <td class="p-2.5 font-bold bg-slate-100 border-r border-slate-300">Tujuan / Pihak Penerima</td>
                        <td class="p-2.5 font-semibold text-slate-900" colspan="3">${escapeHtml(doc.recipient)}</td>
                    </tr>
                </table>

                <!-- Official Document Body Content Box -->
                <div class="space-y-2 pt-1">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-700">Isi Pokok / Uraian Lengkap Naskah Dokumen:</div>
                    <div class="p-5 rounded-lg bg-white border border-slate-400 text-xs sm:text-sm text-slate-900 leading-relaxed whitespace-pre-line min-h-[160px] text-justify">${escapeHtml(doc.summary)}</div>
                </div>

                <div class="p-3 rounded-lg bg-slate-50 border border-slate-300 text-[11px] text-slate-600 flex items-center justify-between">
                    <span>Status Registrasi: <strong>Tercatat dalam Buku Agenda Sekretariat Vihara</strong></span>
                    <span>Lampiran Digital: <strong>${escapeHtml(doc.attachment_name || 'Arsip Fisik Sekretariat')}</strong></span>
                </div>
            `;

            wrapper.classList.remove('hidden');
        }

        function openMarriagePrintModal(marriage, mode) {
            const wrapper = document.getElementById('a4PrintWrapper');
            const content = document.getElementById('a4DocumentContent');
            const weddingDateStr = (marriage.wedding_date || '').substring(0, 10);
            const regDateStr = (marriage.registration_date || '').substring(0, 10);

            document.getElementById('a4SignDate').textContent = weddingDateStr;
            document.getElementById('a4LeftSignTitle').textContent = 'Rohaniwan / Pandita Pemimpin Pemberkatan';
            document.getElementById('a4LeftSignName').textContent = marriage.officiant_name || 'Romo Pandita';
            document.getElementById('a4RightSignTitle').textContent = 'Ketua / Pengurus Sekretariat Wihara';
            document.getElementById('a4RightSignName').textContent = 'Sekretariat Wihara Mahabodhi';

            if (mode === 'checklist') {
                content.innerHTML = `
                    <!-- Official Berita Acara & Verification Header -->
                    <div class="text-center border-b border-slate-300 pb-3 space-y-1">
                        <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Formulir Kendali Sekretariat Perkawinan Agama Buddha</div>
                        <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 underline tracking-wide">
                            BERITA ACARA &amp; VERIFIKASI BERKAS PERKAWINAN BUDDHIS
                        </h3>
                        <div class="text-xs text-slate-700">
                            Nomor Registrasi: <strong class="font-mono text-slate-900">${escapeHtml(marriage.certificate_number)}</strong> &bull; Status Administrasi: <strong>${escapeHtml(marriage.status)}</strong>
                        </div>
                    </div>

                    <p class="text-xs text-slate-800 leading-relaxed text-justify">
                        Pada hari ini telah dilakukan pemeriksaan kelengkapan administrasi dan pelaksanaan upacara perkawinan secara Agama Buddha di <strong>Wihara Mahabodhi Buddhist Centre Semarang</strong> dengan data pokok sebagai berikut:
                    </p>

                    <table class="w-full text-xs border border-slate-400 border-collapse">
                        <tr class="border-b border-slate-300">
                            <td class="p-2 font-bold bg-slate-100 w-[34%] border-r border-slate-300">Mempelai Pria (Upasaka)</td>
                            <td class="p-2 font-bold text-slate-900">${escapeHtml(marriage.groom_name)} ${marriage.groom_buddhist_name ? '(' + escapeHtml(marriage.groom_buddhist_name) + ')' : ''}</td>
                        </tr>
                        <tr class="border-b border-slate-300">
                            <td class="p-2 font-bold bg-slate-100 border-r border-slate-300">Orang Tua Mempelai Pria</td>
                            <td class="p-2 text-slate-800">Ayah: <strong>${escapeHtml(marriage.groom_father_name)}</strong> &bull; Ibu: <strong>${escapeHtml(marriage.groom_mother_name)}</strong></td>
                        </tr>
                        <tr class="border-b border-slate-300">
                            <td class="p-2 font-bold bg-slate-100 border-r border-slate-300">Mempelai Wanita (Upasika)</td>
                            <td class="p-2 font-bold text-slate-900">${escapeHtml(marriage.bride_name)} ${marriage.bride_buddhist_name ? '(' + escapeHtml(marriage.bride_buddhist_name) + ')' : ''}</td>
                        </tr>
                        <tr class="border-b border-slate-300">
                            <td class="p-2 font-bold bg-slate-100 border-r border-slate-300">Orang Tua Mempelai Wanita</td>
                            <td class="p-2 text-slate-800">Ayah: <strong>${escapeHtml(marriage.bride_father_name)}</strong> &bull; Ibu: <strong>${escapeHtml(marriage.bride_mother_name)}</strong></td>
                        </tr>
                        <tr class="border-b border-slate-300">
                            <td class="p-2 font-bold bg-slate-100 border-r border-slate-300">Jadwal &amp; Tempat Pemberkatan</td>
                            <td class="p-2 font-semibold text-slate-900">${escapeHtml(weddingDateStr)} (${escapeHtml(marriage.wedding_time)}) &bull; ${escapeHtml(marriage.wedding_location)}</td>
                        </tr>
                        <tr class="border-b border-slate-300">
                            <td class="p-2 font-bold bg-slate-100 border-r border-slate-300">Pandita / Pemimpin Upacara</td>
                            <td class="p-2 font-semibold text-slate-900">${escapeHtml(marriage.officiant_name)}</td>
                        </tr>
                        <tr>
                            <td class="p-2 font-bold bg-slate-100 border-r border-slate-300">Saksi Resmi Pernikahan</td>
                            <td class="p-2 text-slate-800">Saksi I: <strong>${escapeHtml(marriage.witness_one)}</strong> &nbsp;|&nbsp; Saksi II: <strong>${escapeHtml(marriage.witness_two)}</strong></td>
                        </tr>
                    </table>

                    <!-- Checklist Kelengkapan Berkas Persyaratan -->
                    <div class="space-y-1.5">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-800">Daftar Pemeriksaan Persyaratan Administrasi Perkawinan:</div>
                        <table class="w-full text-xs border border-slate-400 border-collapse">
                            <thead class="bg-slate-100 font-bold text-slate-800 border-b border-slate-300">
                                <tr>
                                    <th class="p-2 border-r border-slate-300 w-10 text-center">No</th>
                                    <th class="p-2 border-r border-slate-300">Dokumen Persyaratan Administrasi Vihara &amp; Dukcapil</th>
                                    <th class="p-2 w-44 text-center">Status Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-300">
                                <tr>
                                    <td class="p-2 text-center border-r border-slate-300">1</td>
                                    <td class="p-2 border-r border-slate-300">Fotokopi KTP Elektronik &amp; Kartu Keluarga (KK) Kedua Mempelai</td>
                                    <td class="p-2 text-center font-bold text-emerald-800">[&#10003;] Lengkap</td>
                                </tr>
                                <tr>
                                    <td class="p-2 text-center border-r border-slate-300">2</td>
                                    <td class="p-2 border-r border-slate-300">Fotokopi Akta Kelahiran &amp; Surat Pengantar Kelurahan (N1 – N4)</td>
                                    <td class="p-2 text-center font-bold text-emerald-800">[&#10003;] Lengkap</td>
                                </tr>
                                <tr>
                                    <td class="p-2 text-center border-r border-slate-300">3</td>
                                    <td class="p-2 border-r border-slate-300">Sertifikat / Bukti Bimbingan Pra-Nikah Agama Buddha</td>
                                    <td class="p-2 text-center font-bold ${marriage.premarital_course_done ? 'text-emerald-800' : 'text-amber-700'}">${marriage.premarital_course_done ? '[&#10003;] Telah Lulus' : '[Proses Bimbingan]'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2 text-center border-r border-slate-300">4</td>
                                    <td class="p-2 border-r border-slate-300">Surat Pernyataan Belum Pernah Menikah &amp; Persetujuan Orang Tua</td>
                                    <td class="p-2 text-center font-bold text-emerald-800">[&#10003;] Lengkap</td>
                                </tr>
                                <tr>
                                    <td class="p-2 text-center border-r border-slate-300">5</td>
                                    <td class="p-2 border-r border-slate-300">Pas Foto Gandeng Berwarna Ukuran 4x6 cm &amp; Data KTP Saksi I &amp; II</td>
                                    <td class="p-2 text-center font-bold text-emerald-800">[&#10003;] Lengkap</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-300 text-xs leading-relaxed">
                        <strong>Catatan Sekretariat:</strong> ${escapeHtml(marriage.notes || 'Seluruh dokumen persyaratan administrasi pemberkatan perkawinan agama Buddha telah diperiksa dan dinyatakan lengkap.')}
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <!-- Official Buddhist Marriage Certificate Title -->
                    <div class="text-center border-b border-slate-300 pb-3 space-y-1">
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-600">Namo Sanghyang Adi Buddhaya &bull; Namo Buddhaya</div>
                        <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 underline tracking-wide">
                            SURAT KETERANGAN PEMBERKATAN PERKAWINAN BUDDHIS
                        </h3>
                        <div class="text-xs text-slate-700">
                            Nomor Akta / Surat: <strong class="font-mono text-slate-900">${escapeHtml(marriage.certificate_number)}</strong> &bull; Tanggal Registrasi: <strong>${escapeHtml(regDateStr || weddingDateStr)}</strong>
                        </div>
                    </div>

                    <p class="text-xs text-slate-900 leading-relaxed text-justify">
                        Pengurus <strong>Wihara Mahabodhi Buddhist Centre Semarang</strong> dengan ini menerangkan dengan sesungguhnya bahwa pada tanggal <strong>${escapeHtml(weddingDateStr)}</strong> pukul <strong>${escapeHtml(marriage.wedding_time)}</strong> bertempat di <strong>${escapeHtml(marriage.wedding_location)}</strong>, telah dilangsungkan Upacara Pemberkatan Perkawinan yang sah menurut tata cara Agama Buddha di hadapan Tiratana (Buddha, Dhamma, dan Sangha) antara:
                    </p>

                    <!-- Clean Aligned Biodata Tables for Groom & Bride -->
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="rounded-lg border border-slate-400 overflow-hidden">
                            <div class="bg-slate-100 px-2.5 py-1.5 font-bold text-slate-900 uppercase tracking-wider text-[10px] border-b border-slate-300">
                                I. Mempelai Pria (Upasaka)
                            </div>
                            <table class="w-full text-[11px] border-collapse">
                                <tr class="border-b border-slate-200">
                                    <td class="p-1.5 font-semibold text-slate-600 w-[38%]">Nama Lengkap</td>
                                    <td class="p-1.5 font-bold text-slate-900">: ${escapeHtml(marriage.groom_name)}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="p-1.5 font-semibold text-slate-600">Nama Buddhis</td>
                                    <td class="p-1.5 text-slate-800">: ${escapeHtml(marriage.groom_buddhist_name || '—')}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="p-1.5 font-semibold text-slate-600">NIK KTP</td>
                                    <td class="p-1.5 font-mono text-slate-800">: ${escapeHtml(marriage.groom_nik || '—')}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="p-1.5 font-semibold text-slate-600">Tempat, Tgl Lahir</td>
                                    <td class="p-1.5 text-slate-800">: ${escapeHtml(marriage.groom_birth_info)}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="p-1.5 font-semibold text-slate-600">Nama Ayah</td>
                                    <td class="p-1.5 text-slate-800">: Bpk. ${escapeHtml(marriage.groom_father_name)}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="p-1.5 font-semibold text-slate-600">Nama Ibu</td>
                                    <td class="p-1.5 text-slate-800">: Ibu ${escapeHtml(marriage.groom_mother_name)}</td>
                                </tr>
                                <tr>
                                    <td class="p-1.5 font-semibold text-slate-600 align-top">Alamat</td>
                                    <td class="p-1.5 text-slate-800">: ${escapeHtml(marriage.groom_address)}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="rounded-lg border border-slate-400 overflow-hidden">
                            <div class="bg-slate-100 px-2.5 py-1.5 font-bold text-slate-900 uppercase tracking-wider text-[10px] border-b border-slate-300">
                                II. Mempelai Wanita (Upasika)
                            </div>
                            <table class="w-full text-[11px] border-collapse">
                                <tr class="border-b border-slate-200">
                                    <td class="p-1.5 font-semibold text-slate-600 w-[38%]">Nama Lengkap</td>
                                    <td class="p-1.5 font-bold text-slate-900">: ${escapeHtml(marriage.bride_name)}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="p-1.5 font-semibold text-slate-600">Nama Buddhis</td>
                                    <td class="p-1.5 text-slate-800">: ${escapeHtml(marriage.bride_buddhist_name || '—')}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="p-1.5 font-semibold text-slate-600">NIK KTP</td>
                                    <td class="p-1.5 font-mono text-slate-800">: ${escapeHtml(marriage.bride_nik || '—')}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="p-1.5 font-semibold text-slate-600">Tempat, Tgl Lahir</td>
                                    <td class="p-1.5 text-slate-800">: ${escapeHtml(marriage.bride_birth_info)}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="p-1.5 font-semibold text-slate-600">Nama Ayah</td>
                                    <td class="p-1.5 text-slate-800">: Bpk. ${escapeHtml(marriage.bride_father_name)}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="p-1.5 font-semibold text-slate-600">Nama Ibu</td>
                                    <td class="p-1.5 text-slate-800">: Ibu ${escapeHtml(marriage.bride_mother_name)}</td>
                                </tr>
                                <tr>
                                    <td class="p-1.5 font-semibold text-slate-600 align-top">Alamat</td>
                                    <td class="p-1.5 text-slate-800">: ${escapeHtml(marriage.bride_address)}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Officiant & Witnesses Official Box -->
                    <table class="w-full text-xs border border-slate-400 border-collapse">
                        <tr class="border-b border-slate-300">
                            <td class="p-1.5 font-bold bg-slate-100 w-[34%] border-r border-slate-300">Rohaniwan / Pandita Pemimpin</td>
                            <td class="p-1.5 font-bold text-slate-900">${escapeHtml(marriage.officiant_name)}</td>
                        </tr>
                        <tr>
                            <td class="p-1.5 font-bold bg-slate-100 border-r border-slate-300">Saksi Upacara Pemberkatan</td>
                            <td class="p-1.5 text-slate-800">1. <strong>${escapeHtml(marriage.witness_one)}</strong> (Saksi I) &nbsp;&nbsp;&bull;&nbsp;&nbsp; 2. <strong>${escapeHtml(marriage.witness_two)}</strong> (Saksi II)</td>
                        </tr>
                    </table>

                    <p class="text-[11px] text-slate-800 leading-snug text-justify">
                        Demikian Surat Keterangan Pemberkatan Perkawinan Buddhis ini diterbitkan dengan sebenarnya sebagai bukti sah perkawinan secara Agama Buddha dan untuk dipergunakan dalam pencatatan perkawinan pada Dinas Kependudukan dan Pencatatan Sipil (Disdukcapil). Semoga Tiratana senantiasa melindungi keluarga bahagia (Hitaya Sukhaya).
                    </p>

                    <!-- Bride, Groom & Witnesses Signature Row -->
                    <div class="grid grid-cols-4 gap-2.5 pt-1 text-[10px] text-center">
                        <div class="border border-slate-300 rounded-lg p-2 bg-slate-50/40">
                            <div class="font-semibold text-slate-600">Mempelai Pria</div>
                            <div class="pt-7 font-bold underline text-slate-900">${escapeHtml(marriage.groom_name)}</div>
                        </div>
                        <div class="border border-slate-300 rounded-lg p-2 bg-slate-50/40">
                            <div class="font-semibold text-slate-600">Mempelai Wanita</div>
                            <div class="pt-7 font-bold underline text-slate-900">${escapeHtml(marriage.bride_name)}</div>
                        </div>
                        <div class="border border-slate-300 rounded-lg p-2 bg-slate-50/40">
                            <div class="font-semibold text-slate-600">Saksi I</div>
                            <div class="pt-7 font-bold underline text-slate-900">${escapeHtml(marriage.witness_one)}</div>
                        </div>
                        <div class="border border-slate-300 rounded-lg p-2 bg-slate-50/40">
                            <div class="font-semibold text-slate-600">Saksi II</div>
                            <div class="pt-7 font-bold underline text-slate-900">${escapeHtml(marriage.witness_two)}</div>
                        </div>
                    </div>
                `;
            }

            wrapper.classList.remove('hidden');
        }

        /* ========================================================= */
        /* INTERACTIVE DASHBOARD CALENDAR (JADWAL IBADAH & KEGIATAN) */
        /* ========================================================= */
        const dashboardSchedulesData = @json($schedules ?? []);
        const indoMonths = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        const indoDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        let currentCalYear = new Date().getFullYear();
        let currentCalMonth = new Date().getMonth();
        let selectedCalDateStr = new Date().toISOString().substring(0, 10);

        function getSchedulesForDate(dateObj) {
            const dayName = indoDays[dateObj.getDay()];
            const y = dateObj.getFullYear();
            const m = String(dateObj.getMonth() + 1).padStart(2, '0');
            const d = String(dateObj.getDate()).padStart(2, '0');
            const isoStr = `${y}-${m}-${d}`;

            return dashboardSchedulesData.filter(function (sch) {
                if (!sch.is_active) return false;
                if (sch.event_date && String(sch.event_date).substring(0, 10) === isoStr) {
                    return true;
                }
                const dow = String(sch.day_of_week || '').toLowerCase();
                if (dow.includes('setiap hari') || dow.includes('harian')) {
                    return true;
                }
                return dow.includes(dayName.toLowerCase());
            });
        }

        function renderDashboardCalendar() {
            const grid = document.getElementById('dashboardCalendarGrid');
            const titleEl = document.getElementById('dashboardCalendarMonthTitle');
            if (!grid || !titleEl) return;

            titleEl.textContent = `${indoMonths[currentCalMonth]} ${currentCalYear}`;

            const firstDay = new Date(currentCalYear, currentCalMonth, 1).getDay();
            const daysInMonth = new Date(currentCalYear, currentCalMonth + 1, 0).getDate();
            const todayIso = new Date().toISOString().substring(0, 10);

            let html = '';

            for (let emptyIdx = 0; emptyIdx < firstDay; emptyIdx++) {
                html += `<div class="min-h-[60px] sm:min-h-[82px] p-1 sm:p-1.5 bg-slate-50/60"></div>`;
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dateObj = new Date(currentCalYear, currentCalMonth, day);
                const y = dateObj.getFullYear();
                const m = String(dateObj.getMonth() + 1).padStart(2, '0');
                const d = String(day).padStart(2, '0');
                const isoStr = `${y}-${m}-${d}`;

                const dayEvents = getSchedulesForDate(dateObj);
                const isToday = (isoStr === todayIso);
                const isSelected = (isoStr === selectedCalDateStr);
                const isSunday = (dateObj.getDay() === 0);

                let cellBg = 'bg-white hover:bg-slate-50';
                if (isSelected) {
                    cellBg = 'bg-emerald-50/80 ring-2 ring-inset ring-emerald-600';
                } else if (isToday) {
                    cellBg = 'bg-amber-50/60';
                }

                let badgeNumberClass = 'text-slate-800';
                if (isToday) {
                    badgeNumberClass = 'bg-amber-500 text-slate-950 px-1.5 py-0.5 rounded-md font-extrabold';
                } else if (isSunday) {
                    badgeNumberClass = 'text-rose-600 font-bold';
                }

                let eventsHtml = '';
                dayEvents.slice(0, 2).forEach(function (ev) {
                    eventsHtml += `
                        <div class="px-1 sm:px-1.5 py-0.5 rounded bg-emerald-100/90 text-emerald-900 font-semibold text-[9px] sm:text-[10px] leading-tight truncate sm:whitespace-normal sm:break-words border border-emerald-200/80">
                            ${escapeHtml(ev.start_time)} ${escapeHtml(ev.title)}
                        </div>
                    `;
                });
                if (dayEvents.length > 2) {
                    eventsHtml += `<div class="text-[9px] sm:text-[10px] font-bold text-emerald-700 pl-0.5 sm:pl-1 truncate">+${dayEvents.length - 2} kegiatan</div>`;
                }

                html += `
                    <button
                        type="button"
                        onclick="selectDashboardCalendarDate('${isoStr}')"
                        class="min-h-[60px] sm:min-h-[82px] p-1 sm:p-1.5 text-left flex flex-col justify-between transition cursor-pointer overflow-hidden min-w-0 ${cellBg}"
                    >
                        <div class="flex items-center justify-between w-full">
                            <span class="text-xs font-bold ${badgeNumberClass}">${day}</span>
                            ${dayEvents.length > 0 ? `<span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-500 shrink-0"></span>` : ''}
                        </div>
                        <div class="space-y-1 mt-1 w-full min-w-0">
                            ${eventsHtml}
                        </div>
                    </button>
                `;
            }

            grid.innerHTML = html;
            renderSelectedDayAgenda(selectedCalDateStr);
        }

        function renderSelectedDayAgenda(isoStr) {
            const labelEl = document.getElementById('dashboardSelectedDateLabel');
            const listEl = document.getElementById('dashboardSelectedDayEvents');
            if (!labelEl || !listEl) return;

            const parts = isoStr.split('-');
            const dateObj = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
            const dayName = indoDays[dateObj.getDay()];
            labelEl.textContent = `${dayName}, ${Number(parts[2])} ${indoMonths[dateObj.getMonth()]} ${dateObj.getFullYear()}`;

            const events = getSchedulesForDate(dateObj);
            if (events.length === 0) {
                listEl.innerHTML = `
                    <div class="p-4 rounded-xl bg-white border border-slate-200 text-xs text-slate-500 text-center space-y-1">
                        <i class="fa-regular fa-calendar text-lg text-slate-400 block mb-1"></i>
                        <div class="font-semibold text-slate-700">Tidak ada jadwal khusus pada hari ${escapeHtml(dayName)}</div>
                        <div>Pilih hari Minggu, Rabu, atau Sabtu untuk melihat jadwal rutin ibadah vihara.</div>
                    </div>
                `;
                return;
            }

            let html = '';
            events.forEach(function (ev) {
                html += `
                    <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs space-y-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[11px]">${escapeHtml(ev.category)}</span>
                            <span class="text-xs font-bold text-slate-700"><i class="fa-regular fa-clock mr-1 text-emerald-600"></i>${escapeHtml(ev.start_time)} – ${escapeHtml(ev.end_time)} WIB</span>
                        </div>
                        <div class="font-bold text-slate-900 text-sm leading-snug">${escapeHtml(ev.title)}</div>
                        <div class="text-xs text-slate-600 flex items-center gap-1.5">
                            <i class="fa-solid fa-location-dot text-slate-400"></i>
                            <span>${escapeHtml(ev.location)}</span>
                        </div>
                        ${ev.speaker ? `<div class="text-xs text-slate-500 flex items-center gap-1.5"><i class="fa-solid fa-user-tie text-slate-400"></i><span>${escapeHtml(ev.speaker)}</span></div>` : ''}
                    </div>
                `;
            });
            listEl.innerHTML = html;
        }

        function selectDashboardCalendarDate(isoStr) {
            selectedCalDateStr = isoStr;
            renderDashboardCalendar();
        }

        function changeDashboardCalendarMonth(delta) {
            currentCalMonth += delta;
            if (currentCalMonth > 11) {
                currentCalMonth = 0;
                currentCalYear++;
            } else if (currentCalMonth < 0) {
                currentCalMonth = 11;
                currentCalYear--;
            }
            const firstMatch = `${currentCalYear}-${String(currentCalMonth + 1).padStart(2, '0')}-01`;
            selectedCalDateStr = firstMatch;
            renderDashboardCalendar();
        }

        function resetDashboardCalendarToday() {
            const now = new Date();
            currentCalYear = now.getFullYear();
            currentCalMonth = now.getMonth();
            selectedCalDateStr = now.toISOString().substring(0, 10);
            renderDashboardCalendar();
        }

        function openImportExcelModal() {
            const modal = document.getElementById('importExcelModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeImportExcelModal() {
            const modal = document.getElementById('importExcelModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        function updateModalFileName(input) {
            const display = document.getElementById('modalFileName');
            if (display && input.files && input.files[0]) {
                const f = input.files[0];
                const sizeKb = (f.size / 1024).toFixed(1);
                display.innerHTML = `<span class="text-emerald-700 font-bold flex items-center justify-center gap-2"><i class="fa-solid fa-file-excel text-lg text-emerald-600"></i> ${escapeHtml(f.name)} (${sizeKb} KB)</span>`;
            }
        }

        function closeA4PrintModal() {
            document.getElementById('a4PrintWrapper').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Close modals on Escape key or backdrop click
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImportExcelModal();
                closeA4PrintModal();
                closeProfileDropdown();
            }
        });

        document.addEventListener('click', function(e) {
            const importModal = document.getElementById('importExcelModal');
            if (importModal && e.target === importModal) {
                closeImportExcelModal();
            }
            const a4Modal = document.getElementById('a4PrintWrapper');
            if (a4Modal && e.target === a4Modal) {
                closeA4PrintModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const initialTab = @json($currentTab);
            const targetPanel = document.getElementById('tab-' + initialTab);
            if (targetPanel) {
                switchManagementTab(initialTab);
            } else if (document.getElementById('tab-overview')) {
                switchManagementTab('overview');
            }

            document.querySelectorAll('.mgmt-tab-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    switchManagementTab(btn.getAttribute('data-tab-target'));
                });
            });

            // 1. Overview Receipts Paginator
            new ClientTablePaginator({
                container: '#overviewReceiptsContainer',
                rowSelector: '.overview-receipt-row',
                searchInput: '#overviewReceiptsSearch',
                infoContainer: '#overviewReceiptsInfo',
                paginationContainer: '#overviewReceiptsPagination',
                pageSize: 5
            });

            // 2. Overview Documents Paginator
            new ClientTablePaginator({
                container: '#overviewDocsContainer',
                rowSelector: '.overview-doc-row',
                searchInput: '#overviewDocsSearch',
                infoContainer: '#overviewDocsInfo',
                paginationContainer: '#overviewDocsPagination',
                pageSize: 5
            });

            // 3. Receipts Tab Paginator
            new ClientTablePaginator({
                container: '#receiptsTableBody',
                rowSelector: '.receipt-row',
                searchInput: '#receiptSearchInput',
                filterSelect: '#receiptFilterType',
                infoContainer: '#receiptsCountInfo',
                paginationContainer: '#receiptsPaginationContainer',
                pageSize: 8
            });

            // 4. Documents Tab Paginator
            new ClientTablePaginator({
                container: '#documentsTableBody',
                rowSelector: '.doc-row',
                searchInput: '#docSearchInput',
                filterPills: '.doc-filter-pill',
                infoContainer: '#docsCountInfo',
                paginationContainer: '#documentsPaginationContainer',
                pageSize: 8
            });

            // 4B. Marriages Tab Paginator
            new ClientTablePaginator({
                container: '#marriagesTableBody',
                rowSelector: '.marriage-row',
                searchInput: '#marriageSearchInput',
                filterSelect: '#marriageFilterStatus',
                infoContainer: '#marriagesCountInfo',
                paginationContainer: '#marriagesPaginationContainer',
                pageSize: 8
            });

            // 5. Schedules Tab Paginator
            new ClientTablePaginator({
                container: '#schedulesTableBody',
                rowSelector: '.schedule-row',
                searchInput: '#scheduleSearchInput',
                infoContainer: '#schedulesCountInfo',
                paginationContainer: '#schedulesPaginationContainer',
                pageSize: 8
            });

            // 5B. Overview Interactive Worship & Activity Calendar
            renderDashboardCalendar();

            // 6. Galleries Tab Paginator (Table View)
            new ClientTablePaginator({
                container: '#galleriesTableBody',
                rowSelector: '.gallery-row',
                searchInput: '#gallerySearchInput',
                filterSelect: '#galleryFilterType',
                infoContainer: '#galleriesCountInfo',
                paginationContainer: '#galleriesPaginationContainer',
                pageSize: 8
            });

            // 7. Articles Tab Paginator
            new ClientTablePaginator({
                container: '#articlesTableBody',
                rowSelector: '.article-row',
                searchInput: '#articleSearchInput',
                infoContainer: '#articlesCountInfo',
                paginationContainer: '#articlesPaginationContainer',
                pageSize: 8
            });

            // 8. Roles & Users Paginators
            new ClientTablePaginator({
                container: '#rolesListContainer',
                rowSelector: '.role-item-row',
                searchInput: '#roleSearchInput',
                infoContainer: '#rolesCountInfo',
                paginationContainer: '#rolesPaginationContainer',
                pageSize: 5
            });

            new ClientTablePaginator({
                container: '#usersListContainer',
                rowSelector: '.user-item-row',
                searchInput: '#userSearchInput',
                infoContainer: '#usersCountInfo',
                paginationContainer: '#usersPaginationContainer',
                pageSize: 5
            });

            @if (!empty($printRecord) && !empty($printType))
                @if (in_array($printType, ['receipt', 'thank_you'], true))
                    openReceiptPrintModal(@js($printRecord), @json($printType));
                @elseif ($printType === 'document')
                    openDocumentPrintModal(@js($printRecord));
                @elseif ($printType === 'marriage')
                    openMarriagePrintModal(@js($printRecord), 'certificate');
                @endif
            @endif
        });
    </script>
@endif
</body>
</html>
