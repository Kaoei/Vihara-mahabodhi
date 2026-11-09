<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @if (!empty($isArticleDetail) && isset($article))
            {{ $article->title }} — {{ $vihara['name'] ?? 'Vihara Mahabodhi Buddhist Centre' }}
        @else
            {{ $vihara['name'] ?? 'Vihara Mahabodhi Buddhist Centre' }} — Semarang
        @endif
    </title>
    <meta name="description" content="{{ $vihara['about'] ?? 'Vihara Mahabodhi Buddhist Centre, Jl. Seroja Timur No. 9-11 Semarang.' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        gold: {
                            50: '#fbf8f1',
                            100: '#f5eedc',
                            200: '#eadab8',
                            300: '#dfc48d',
                            400: '#d4af63',
                            500: '#c49a45',
                            600: '#a37b2c',
                            700: '#85621f',
                            800: '#6b4e18',
                            900: '#523b12',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        html, body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        .font-display {
            font-family: 'Playfair Display', Georgia, serif;
        }

        /* Hero background showing both the Buddha statues at top and the Bhikkhus & congregation below */
        .hero-banner {
            background-image:
                linear-gradient(to bottom, rgba(15, 23, 42, 0.45) 0%, rgba(15, 23, 42, 0.55) 50%, rgba(15, 23, 42, 0.72) 100%),
                url('/Assets/Img/hero.png');
            background-size: 100% 112%;
            background-position: center 48%;
            background-repeat: no-repeat;
        }

        @media (max-width: 768px) {
            .hero-banner {
                background-size: cover;
                background-position: center center;
            }
        }

        /* Smooth Scroll Reveal Effects */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }

        .reveal-on-scroll.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-delay-100 { transition-delay: 100ms; }
        .reveal-delay-200 { transition-delay: 200ms; }
        .reveal-delay-300 { transition-delay: 300ms; }
    </style>
</head>
<body class="antialiased selection:bg-gold-500 selection:text-white w-full max-w-full overflow-x-hidden">
<div class="w-full max-w-full overflow-x-hidden relative">

    @if (empty($isArticleDetail))
    <!-- 3-Second Initial Buddhist Dhamma Loading Screen (White Mode) -->
    <div id="dhammaLoadingScreen" class="fixed inset-0 z-[100] bg-white text-slate-900 flex flex-col items-center justify-center px-5 text-center transition-opacity duration-700 overflow-hidden">
        <div class="w-full max-w-xl mx-auto space-y-5 sm:space-y-6 flex flex-col items-center">
            <!-- Buddha / Dharma Icon (No Background Box) -->
            <div class="text-gold-500 text-4xl sm:text-6xl">
                <i class="fa-solid fa-dharmachakra fa-spin" style="animation-duration: 10s;"></i>
            </div>

            <div class="space-y-1.5 px-2">
                <span class="text-[10px] sm:text-xs font-bold uppercase tracking-widest sm:tracking-[0.2em] text-gold-600 block leading-relaxed">
                    Namo Tassa Bhagavato Arahato Sammāsambuddhassa
                </span>
                <div class="text-xs text-slate-500 font-medium">
                    Vihara Mahabodhi Buddhist Centre &bull; Semarang
                </div>
            </div>

            <!-- Buddhist Dhamma Message -->
            <div class="p-5 sm:p-6 rounded-2xl bg-gold-50/70 border border-gold-200/80 shadow-xs space-y-3 w-full max-w-lg">
                <p id="loadingDhammaPali" class="font-display italic text-sm sm:text-lg text-slate-900 leading-relaxed break-words">
                    "Sabbapāpassa akaraṇaṃ, kusalassa upasampadā, sacittapariyodapanaṃ, etaṃ buddhāna sāsanaṃ."
                </p>
                <p id="loadingDhammaTranslation" class="text-xs sm:text-sm text-slate-600 leading-relaxed break-words">
                    Tidak melakukan segala bentuk kejahatan, senantiasa mengembangkan kebajikan, dan memurnikan pikiran sendiri; inilah ajaran para Buddha.
                </p>
                <span id="loadingDhammaSource" class="inline-block text-[11px] font-bold uppercase tracking-wider text-gold-600 pt-1">
                    — Dhammapada 183
                </span>
            </div>

            <!-- 3-Second Progress Bar -->
            <div class="w-48 sm:w-64 space-y-2 pt-1">
                <div class="h-1.5 w-full rounded-full bg-slate-100 border border-slate-200/60 overflow-hidden">
                    <div id="dhammaLoadingBar" class="h-full w-0 bg-gold-500 rounded-full transition-all ease-linear" style="transition-duration: 3000ms;"></div>
                </div>
                <div class="text-[10px] sm:text-[11px] text-slate-500 tracking-wide leading-relaxed">
                    Sabbe Sattā Bhavantu Sukhitattā &bull; Semoga Semua Makhluk Berbahagia
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Sticky Navigation Header with Spacious Y-Padding & Margin (No Icons in Menu Items, No Background on Buddha Icon) -->
    <header id="mainNavbar" class="sticky top-0 z-40 w-full bg-white/95 backdrop-blur-md border-b border-slate-200/80 py-3.5 sm:py-5 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-0.5 flex items-center justify-between gap-2 sm:gap-4">
            <!-- Brand (Buddha Icon Only — No Background Box) -->
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 sm:gap-3 min-w-0 group">
                <i class="fa-solid fa-dharmachakra text-gold-500 text-2xl sm:text-3xl shrink-0 transition-transform duration-500 group-hover:rotate-45"></i>
                <div class="min-w-0">
                    <span class="block text-sm sm:text-base font-bold text-slate-900 tracking-tight leading-none truncate">Vihara Mahabodhi</span>
                    <span class="block text-[11px] sm:text-xs text-slate-500 mt-1 truncate">Buddhist Centre &bull; Semarang</span>
                </div>
            </a>

            <!-- Desktop Navigation (Clean text links without icons, Kontak moved to Floating Button) -->
            <nav class="hidden lg:flex items-center gap-8 text-xs sm:text-sm font-semibold text-slate-600 whitespace-nowrap">
                <a href="{{ !empty($isArticleDetail) ? route('landing').'#profil' : '#profil' }}" class="hover:text-gold-600 transition">Profil Vihara</a>
                <a href="{{ !empty($isArticleDetail) ? route('landing').'#jadwal' : '#jadwal' }}" class="hover:text-gold-600 transition">Jadwal Ibadah</a>
                <a href="{{ !empty($isArticleDetail) ? route('landing').'#galeri' : '#galeri' }}" class="hover:text-gold-600 transition">Galeri Kegiatan</a>
                <a href="{{ !empty($isArticleDetail) ? route('landing').'#berita' : '#berita' }}" class="hover:text-gold-600 transition">Berita &amp; Renungan</a>
            </nav>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2.5 shrink-0 whitespace-nowrap">
                <a href="{{ !empty($isArticleDetail) ? route('landing').'#jadwal' : '#jadwal' }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gold-500 hover:bg-gold-600 text-white text-xs sm:text-sm font-semibold shadow-2xs transition">
                    <i class="fa-regular fa-calendar-check"></i>
                    <span>Jadwal Kegiatan</span>
                </a>
                <button type="button" id="mobileMenuBtn" class="lg:hidden p-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100" aria-label="Buka Menu">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu (Clean text links without icons) -->
        <div id="mobileMenuPanel" class="hidden lg:hidden bg-white border-b border-slate-200 px-4 pt-3 pb-4 mt-3 space-y-1.5 text-xs sm:text-sm font-semibold">
            <a href="{{ !empty($isArticleDetail) ? route('landing').'#profil' : '#profil' }}" class="block px-3 py-2.5 rounded-lg text-slate-700 hover:bg-gold-50 hover:text-gold-700">Profil Vihara</a>
            <a href="{{ !empty($isArticleDetail) ? route('landing').'#jadwal' : '#jadwal' }}" class="block px-3 py-2.5 rounded-lg text-slate-700 hover:bg-gold-50 hover:text-gold-700">Jadwal Ibadah &amp; Meditasi</a>
            <a href="{{ !empty($isArticleDetail) ? route('landing').'#galeri' : '#galeri' }}" class="block px-3 py-2.5 rounded-lg text-slate-700 hover:bg-gold-50 hover:text-gold-700">Galeri Kegiatan</a>
            <a href="{{ !empty($isArticleDetail) ? route('landing').'#berita' : '#berita' }}" class="block px-3 py-2.5 rounded-lg text-slate-700 hover:bg-gold-50 hover:text-gold-700">Berita &amp; Renungan Dharma</a>
        </div>
    </header>

@if (!empty($isArticleDetail) && isset($article))
    <!-- ========================================================= -->
    <!-- HALAMAN DETAIL BERITA / ARTIKEL / RENUNGAN DHARMA         -->
    <!-- ========================================================= -->
    <main class="py-12 sm:py-16">
        <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 reveal-on-scroll is-visible">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <a href="{{ route('landing') }}#berita" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold whitespace-nowrap transition">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Kembali ke Berita &amp; Renungan</span>
                </a>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gold-50 text-gold-600 border border-gold-200 whitespace-nowrap inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-tag text-[10px]"></i>
                    <span>{{ $article->category }}</span>
                </span>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden shadow-xs">
                @if ($article->cover_image)
                    <div class="aspect-[16/8] w-full overflow-hidden bg-slate-100">
                        <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="p-6 sm:p-10 space-y-6">
                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar text-gold-600"></i>
                            <span>Diterbitkan: <strong class="text-slate-800">{{ $article->published_at ? $article->published_at->format('d F Y') : now()->format('d F Y') }}</strong></span>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fa-regular fa-user text-gold-600"></i>
                            <span>Penulis: <strong class="text-slate-800">{{ $article->author }}</strong></span>
                        </span>
                    </div>

                    <h1 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 leading-snug">
                        {{ $article->title }}
                    </h1>

                    <div class="p-4 sm:p-5 rounded-2xl bg-gold-50/70 border-l-4 border-gold-500 text-sm text-slate-700 italic leading-relaxed">
                        {{ $article->excerpt }}
                    </div>

                    <div class="pt-2 text-sm sm:text-base text-slate-800 leading-relaxed whitespace-pre-line">
                        {{ $article->content }}
                    </div>
                </div>
            </div>

            @if (isset($relatedArticles) && $relatedArticles->isNotEmpty())
                <div class="pt-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-slate-900 whitespace-nowrap">Berita &amp; Renungan Lainnya</h2>
                        <a href="{{ route('landing') }}#berita" class="text-xs font-semibold text-gold-600 hover:underline whitespace-nowrap inline-flex items-center gap-1.5">
                            <span>Lihat Semua</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        @foreach ($relatedArticles as $rel)
                            <a href="{{ route('landing.article', $rel->slug) }}" class="group bg-white rounded-2xl border border-slate-200/80 p-5 hover:border-gold-300 transition flex flex-col justify-between space-y-3">
                                <div class="space-y-1.5">
                                    <span class="text-[11px] font-semibold text-gold-600">{{ $rel->category }}</span>
                                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-gold-600 truncate">
                                        {{ $rel->title }}
                                    </h3>
                                    <p class="text-xs text-slate-500 line-clamp-2">{{ $rel->excerpt }}</p>
                                </div>
                                <span class="text-xs font-semibold text-gold-600 inline-flex items-center gap-1.5 whitespace-nowrap">
                                    <span>Baca Artikel</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </article>
    </main>
@else
    <!-- ========================================================= -->
    <!-- HALAMAN UTAMA PUBLIC LANDING PAGE                         -->
    <!-- ========================================================= -->

    <!-- Hero Section with Full Device Height (min-h-screen), Centered Content & Public Hero Image -->
    <section id="beranda" class="relative hero-banner text-white py-20 sm:py-24 lg:py-32 min-h-screen flex items-center justify-center w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
            <div class="max-w-3xl mx-auto text-center flex flex-col items-center space-y-5 sm:space-y-6 reveal-on-scroll is-visible">
                <div class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-1.5 rounded-2xl sm:rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-white text-[11px] sm:text-xs font-medium max-w-full leading-snug">
                    <i class="fa-solid fa-dharmachakra text-gold-400 shrink-0"></i>
                    <span class="break-words">{{ $vihara['name'] }} &bull; {{ $vihara['address_short'] }}</span>
                </div>

                <h1 class="font-display text-3xl sm:text-5xl lg:text-6xl font-bold leading-tight tracking-tight text-white drop-shadow-sm px-1">
                    Harmoni Kesadaran &amp; Kebajikan Dhamma
                </h1>

                <p class="text-sm sm:text-lg text-slate-100/95 leading-relaxed max-w-2xl mx-auto drop-shadow-xs px-1">
                    Pusat pembinaan spiritual, Puja Bakti, latihan meditasi, dan pelayanan sosial kemasyarakatan di <strong>{{ $vihara['address_short'] }}</strong>.
                </p>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3 sm:gap-3.5 pt-2 w-full sm:w-auto px-2 sm:px-0">
                    <a href="#jadwal" class="px-6 py-3.5 rounded-xl bg-gold-500 hover:bg-gold-600 text-white font-semibold text-xs sm:text-sm shadow-md transition inline-flex items-center justify-center gap-2">
                        <span>Lihat Jadwal Ibadah</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                    <a href="#berita" class="px-6 py-3.5 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur-md text-white border border-white/25 font-semibold text-xs sm:text-sm transition inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-book-open text-xs"></i>
                        <span>Baca Renungan Dharma</span>
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-2 sm:gap-10 pt-6 sm:pt-8 border-t border-white/20 w-full max-w-xl mx-auto text-center">
                    <div class="min-w-0">
                        <div class="text-base sm:text-2xl font-bold text-gold-400">{{ $schedules->count() }} Agenda</div>
                        <div class="text-[11px] sm:text-xs text-slate-200 mt-0.5">Jadwal Aktif</div>
                    </div>
                    <div class="min-w-0">
                        <div class="text-base sm:text-2xl font-bold text-gold-400">{{ $galleries->count() }} Media</div>
                        <div class="text-[11px] sm:text-xs text-slate-200 mt-0.5">Galeri Vihara</div>
                    </div>
                    <div class="min-w-0">
                        <div class="text-base sm:text-2xl font-bold text-gold-400">07.00–21.00</div>
                        <div class="text-[11px] sm:text-xs text-slate-200 mt-0.5">Buka Setiap Hari</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 1: Profil & Fasilitas Vihara -->
    <section id="profil" class="py-14 sm:py-20 bg-white border-b border-slate-200/80 w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                <div class="lg:col-span-5 space-y-4 reveal-on-scroll min-w-0">
                    <span class="inline-block text-xs font-bold uppercase tracking-wider text-gold-600">
                        Profil &amp; Informasi Vihara
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-snug">
                        Vihara Mahabodhi Semarang
                    </h2>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        {{ $vihara['about'] }}
                    </p>

                    <div class="rounded-2xl bg-gold-50/50 border border-gold-200/70 p-4 sm:p-5 space-y-2.5 text-xs">
                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-4 py-1.5 border-b border-gold-200/50">
                            <span class="text-slate-500 inline-flex items-center gap-1.5 shrink-0">
                                <i class="fa-solid fa-location-dot text-gold-600 w-3.5 text-center"></i>
                                <span>Lokasi Vihara</span>
                            </span>
                            <span class="font-semibold text-slate-900 sm:text-right break-words">{{ $vihara['address_short'] }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-4 py-1.5 border-b border-gold-200/50">
                            <span class="text-slate-500 inline-flex items-center gap-1.5 shrink-0">
                                <i class="fa-regular fa-clock text-gold-600 w-3.5 text-center"></i>
                                <span>Jam Operasional</span>
                            </span>
                            <span class="font-semibold text-slate-900 sm:text-right">07.00 – 21.00 WIB</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-4 py-1.5">
                            <span class="text-slate-500 inline-flex items-center gap-1.5 shrink-0">
                                <i class="fa-solid fa-phone text-gold-600 w-3.5 text-center"></i>
                                <span>Kontak Sekretariat</span>
                            </span>
                            <span class="font-semibold text-slate-900 sm:text-right break-words">{{ $vihara['phone'] }}</span>
                        </div>
                    </div>
                </div>

                @php($facilityIcons = ['fa-hands-praying', 'fa-spa', 'fa-book-open-reader', 'fa-hand-holding-heart'])
                <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4 min-w-0">
                    @foreach ($vihara['facilities'] as $index => $facility)
                        <div class="reveal-on-scroll p-5 sm:p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-gold-300 hover:shadow-md transition duration-300 space-y-2.5 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gold-500 text-white flex items-center justify-center text-sm shadow-2xs shrink-0">
                                <i class="fa-solid {{ $facilityIcons[$index % count($facilityIcons)] }}"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 leading-snug break-words">{{ $facility['title'] }}</h3>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $facility['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Section 2: Jadwal Ibadah, Puja Bakti & Meditasi -->
    <section id="jadwal" class="py-14 sm:py-20 bg-slate-50 border-b border-slate-200/80 w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 reveal-on-scroll">
                <div class="space-y-1.5 min-w-0">
                    <span class="text-xs font-bold uppercase tracking-wider text-gold-600">Agenda Kegiatan Vihara</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-snug">
                        Jadwal Ibadah &amp; Meditasi
                    </h2>
                </div>

                <div class="flex flex-wrap gap-2" id="scheduleFilters">
                    <button type="button" data-filter="all" class="schedule-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-gold-500 text-white whitespace-nowrap transition">
                        Semua Kegiatan
                    </button>
                    @foreach ($scheduleCategories as $cat)
                        <button type="button" data-filter="{{ $cat }}" class="schedule-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-gold-50 border border-slate-200 whitespace-nowrap transition">
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="scheduleGrid">
                @forelse ($schedules as $schedule)
                    <article data-category="{{ $schedule->category }}" class="schedule-card reveal-on-scroll bg-white rounded-2xl border border-slate-200/80 p-6 flex flex-col justify-between space-y-4 hover:border-gold-300 hover:-translate-y-1 shadow-2xs hover:shadow-md transition duration-300">
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between gap-2 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-gold-50 text-gold-600 border border-gold-200/70">
                                    {{ $schedule->category }}
                                </span>
                                <span class="text-[11px] font-medium text-slate-400">
                                    {{ $schedule->is_recurring ? 'Rutin Mingguan' : 'Kegiatan Khusus' }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-slate-900 truncate" title="{{ $schedule->title }}">
                                {{ $schedule->title }}
                            </h3>

                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed line-clamp-2">
                                {{ $schedule->description }}
                            </p>
                        </div>

                        <div class="pt-4 border-t border-slate-100 space-y-1.5 text-xs">
                            <div class="flex justify-between gap-2 whitespace-nowrap">
                                <span class="text-slate-400 inline-flex items-center gap-1.5"><i class="fa-regular fa-calendar text-gold-600 w-3.5 text-center"></i> Hari</span>
                                <span class="font-semibold text-slate-900">{{ $schedule->day_of_week }}</span>
                            </div>
                            <div class="flex justify-between gap-2 whitespace-nowrap">
                                <span class="text-slate-400 inline-flex items-center gap-1.5"><i class="fa-regular fa-clock text-gold-600 w-3.5 text-center"></i> Waktu</span>
                                <span class="font-semibold text-gold-600">{{ $schedule->start_time }} – {{ $schedule->end_time }} WIB</span>
                            </div>
                            <div class="flex justify-between gap-2">
                                <span class="text-slate-400 whitespace-nowrap inline-flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-gold-600 w-3.5 text-center"></i> Lokasi</span>
                                <span class="font-medium text-slate-700 truncate">{{ $schedule->location }}</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-12 bg-white rounded-2xl border border-slate-200">
                        <p class="text-sm text-slate-500">Belum ada jadwal kegiatan yang ditampilkan.</p>
                    </div>
                @endforelse
            </div>

            <!-- Schedule Pagination Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                <div id="scheduleCountInfo" class="text-xs font-medium text-slate-500"></div>
                <div id="schedulePaginationContainer" class="flex items-center flex-wrap gap-1.5"></div>
            </div>
        </div>
    </section>

    <!-- Section 3: Galeri Foto & Video Kegiatan -->
    <section id="galeri" class="py-14 sm:py-20 bg-white border-b border-slate-200/80 w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 reveal-on-scroll">
                <div class="space-y-1.5 min-w-0">
                    <span class="text-xs font-bold uppercase tracking-wider text-gold-600">Dokumentasi Visual</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-snug">
                        Galeri Kegiatan Vihara
                    </h2>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
                        <input
                            type="search"
                            id="gallerySearchInput"
                            placeholder="Cari dokumentasi kegiatan..."
                            class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:bg-white focus:border-gold-400 transition"
                        >
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-media-filter="all" class="gallery-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-gold-500 text-white whitespace-nowrap transition inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-layer-group"></i>
                            <span>Semua Media</span>
                        </button>
                        <button type="button" data-media-filter="photo" class="gallery-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-gold-50 whitespace-nowrap transition inline-flex items-center gap-1.5">
                            <i class="fa-regular fa-image"></i>
                            <span>Foto</span>
                        </button>
                        <button type="button" data-media-filter="video" class="gallery-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-gold-50 whitespace-nowrap transition inline-flex items-center gap-1.5">
                            <i class="fa-brands fa-youtube"></i>
                            <span>Video</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" id="galleryGrid">
                @forelse ($galleries as $item)
                    <div
                        data-media-type="{{ $item->media_type }}"
                        data-title="{{ $item->title }}"
                        data-category="{{ $item->category }}"
                        data-description="{{ $item->description }}"
                        data-url="{{ $item->media_url }}"
                        data-date="{{ $item->event_date ? $item->event_date->format('d M Y') : 'Dokumentasi Vihara' }}"
                        class="gallery-card reveal-on-scroll group cursor-pointer bg-white rounded-2xl border border-slate-200/80 overflow-hidden hover:border-gold-300 hover:-translate-y-1 shadow-2xs hover:shadow-md transition duration-300 flex flex-col justify-between"
                    >
                        <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                            <img
                                src="{{ $item->thumbnail_url ?: $item->media_url }}"
                                alt="{{ $item->title }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                loading="lazy"
                            >
                            @if ($item->media_type === 'video')
                                <div class="absolute inset-0 bg-slate-900/25 flex items-center justify-center">
                                    <span class="w-10 h-10 rounded-full bg-gold-500 text-white flex items-center justify-center shadow-md">
                                        <i class="fa-solid fa-play ml-0.5 text-sm"></i>
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="p-4 space-y-1">
                            <div class="text-[11px] font-semibold text-gold-600 whitespace-nowrap">
                                {{ $item->category }} &bull; {{ $item->media_type === 'video' ? 'Video' : 'Foto' }}
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 truncate" title="{{ $item->title }}">
                                {{ $item->title }}
                            </h3>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 bg-slate-50 rounded-2xl border border-slate-200">
                        <p class="text-sm text-slate-500">Belum ada dokumentasi galeri.</p>
                    </div>
                @endforelse
            </div>

            <div id="galleryEmptyState" class="hidden text-center py-12 bg-slate-50 rounded-2xl border border-slate-200">
                <p class="text-sm text-slate-500">Tidak ada dokumentasi yang sesuai dengan pencarian/filter.</p>
            </div>

            <!-- Gallery Pagination Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-slate-100">
                <div id="galleryCountInfo" class="text-xs font-medium text-slate-500"></div>
                <div id="galleryPaginationContainer" class="flex items-center flex-wrap gap-1.5"></div>
            </div>
        </div>
    </section>

    <!-- Section 4: Berita Kegiatan & Renungan Dharma (Navigates to Separate Page!) -->
    <section id="berita" class="py-14 sm:py-20 bg-slate-50 border-b border-slate-200/80 w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 reveal-on-scroll">
                <div class="space-y-1.5 min-w-0">
                    <span class="text-xs font-bold uppercase tracking-wider text-gold-600">Publikasi &amp; Warta Vihara</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-snug">
                        Berita &amp; Renungan Dharma
                    </h2>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
                        <input
                            type="search"
                            id="articleSearchInput"
                            placeholder="Cari berita atau renungan..."
                            class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs focus:outline-none focus:border-gold-400 transition"
                        >
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-article-filter="all" class="article-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-gold-500 text-white whitespace-nowrap transition">
                            Semua
                        </button>
                        <button type="button" data-article-filter="Berita Kegiatan" class="article-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-gold-50 border border-slate-200 whitespace-nowrap transition">
                            Berita Kegiatan
                        </button>
                        <button type="button" data-article-filter="Renungan Dharma" class="article-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-gold-50 border border-slate-200 whitespace-nowrap transition">
                            Renungan Dharma
                        </button>
                        <button type="button" data-article-filter="Pengumuman" class="article-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-gold-50 border border-slate-200 whitespace-nowrap transition">
                            Pengumuman
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="articleGrid">
                @forelse ($articles as $article)
                    <article
                        data-article-category="{{ $article->category }}"
                        class="article-card reveal-on-scroll bg-white rounded-2xl border border-slate-200/80 overflow-hidden flex flex-col justify-between hover:border-gold-300 hover:-translate-y-1 shadow-2xs hover:shadow-md transition duration-300 group"
                    >
                        <div>
                            @if ($article->cover_image)
                                <a href="{{ route('landing.article', $article->slug) }}" class="block h-44 overflow-hidden bg-slate-100">
                                    <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                                </a>
                            @endif

                            <div class="p-5 space-y-2">
                                <div class="flex items-center gap-2 text-[11px] text-slate-400 whitespace-nowrap">
                                    <span class="font-semibold text-gold-600">{{ $article->category }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $article->published_at ? $article->published_at->format('d M Y') : 'Terbaru' }}</span>
                                </div>

                                <h3 class="text-base font-bold text-slate-900 group-hover:text-gold-600 truncate" title="{{ $article->title }}">
                                    <a href="{{ route('landing.article', $article->slug) }}">
                                        {{ $article->title }}
                                    </a>
                                </h3>

                                <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">
                                    {{ $article->excerpt }}
                                </p>
                            </div>
                        </div>

                        <div class="px-5 pb-5">
                            <a href="{{ route('landing.article', $article->slug) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gold-600 hover:text-gold-700 whitespace-nowrap transition">
                                <span>Baca Selengkapnya</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-12 bg-white rounded-2xl border border-slate-200">
                        <p class="text-sm text-slate-500">Belum ada berita atau artikel yang diterbitkan.</p>
                    </div>
                @endforelse
            </div>

            <div id="articleEmptyState" class="hidden text-center py-12 bg-white rounded-2xl border border-slate-200">
                <p class="text-sm text-slate-500">Tidak ada artikel atau berita yang sesuai dengan pencarian/filter.</p>
            </div>

            <!-- Article Pagination Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-slate-200/70">
                <div id="articleCountInfo" class="text-xs font-medium text-slate-500"></div>
                <div id="articlePaginationContainer" class="flex items-center flex-wrap gap-1.5"></div>
            </div>
        </div>
    </section>

    <!-- Gallery Lightbox Modal -->
    <div id="galleryModal" class="fixed inset-0 z-50 hidden bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white border border-slate-200 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl p-6 space-y-4">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3">
                <div class="min-w-0">
                    <span id="modalCategory" class="text-xs font-semibold text-gold-600"></span>
                    <h3 id="modalTitle" class="text-lg font-bold text-slate-900 truncate mt-0.5"></h3>
                    <p id="modalMeta" class="text-xs text-slate-400"></p>
                </div>
                <button type="button" id="closeModalBtn" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div id="modalMediaContainer" class="rounded-xl overflow-hidden bg-slate-100"></div>
            <div id="modalBody" class="text-sm text-slate-600 leading-relaxed"></div>
        </div>
    </div>
@endif

    <!-- Floating Circular Contact Button & Popover (Always follows scroll at bottom-right) -->
    <div class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-50 flex flex-col items-end gap-3 max-w-[calc(100vw-2rem)]">
        <!-- Popover Contact Card -->
        <div id="floatingContactCard" class="hidden w-[calc(100vw-2rem)] max-w-xs sm:w-80 bg-white rounded-2xl border border-gold-200 shadow-2xl p-5 space-y-3.5 text-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-gold-500 text-white flex items-center justify-center">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 text-sm">Kontak Sekretariat</div>
                        <div class="text-[11px] text-gold-600 font-medium">Vihara Mahabodhi Semarang</div>
                    </div>
                </div>
                <button type="button" id="closeFloatingContactBtn" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-2.5 text-slate-600">
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-location-dot text-gold-600 mt-0.5 w-4 text-center shrink-0"></i>
                    <span class="leading-snug">{{ $vihara['address'] ?? $vihara['address_short'] }}</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-phone text-gold-600 w-4 text-center shrink-0"></i>
                    <span class="font-semibold text-slate-800">{{ $vihara['phone'] }}</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <i class="fa-regular fa-envelope text-gold-600 w-4 text-center shrink-0"></i>
                    <span>{{ $vihara['email'] ?? 'sekretariat@mahabodhi.or.id' }}</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <i class="fa-regular fa-clock text-gold-600 mt-0.5 w-4 text-center shrink-0"></i>
                    <span>{{ $vihara['operational_hours'] ?? 'Setiap Hari: 07.00 – 21.00 WIB' }}</span>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center gap-2">
                <a href="tel:0248415509" class="flex-1 py-2 px-3 rounded-xl bg-gold-500 hover:bg-gold-600 text-white font-semibold text-center transition inline-flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-phone-volume text-[11px]"></i>
                    <span>Hubungi Sekretariat</span>
                </a>
                <a href="#kontak" onclick="document.getElementById('floatingContactCard').classList.add('hidden')" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition">
                    Info Lengkap
                </a>
            </div>
        </div>

        <!-- Circular Floating Button -->
        <button
            type="button"
            id="floatingContactBtn"
            aria-label="Kontak Vihara"
            title="Kontak Sekretariat Vihara"
            class="w-14 h-14 rounded-full bg-gold-500 hover:bg-gold-600 text-white shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300 flex items-center justify-center ring-4 ring-gold-400/30"
        >
            <i class="fa-solid fa-comments text-xl"></i>
        </button>
    </div>

    <!-- Footer (Contains Sekretariat & Pelayanan Umat Information) -->
    <footer id="kontak" class="bg-slate-950 text-slate-300 pt-14 pb-10 border-t border-slate-800 w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-10 border-b border-slate-800/80">
                <!-- Col 1: Vihara Brand & About -->
                <div class="md:col-span-5 space-y-4">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-dharmachakra text-gold-400 text-2xl shrink-0"></i>
                        <div>
                            <div class="text-base font-bold text-white leading-tight">{{ $vihara['name'] }}</div>
                            <div class="text-xs text-gold-400 mt-0.5">{{ $vihara['address_short'] }}</div>
                        </div>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-md">
                        Pusat pembinaan spiritual Buddhis, pelaksanaan Puja Bakti, latihan meditasi Samatha-Vipassana, serta pelayanan sosial dan administrasi umat di Kota Semarang.
                    </p>
                </div>

                <!-- Col 2: Sekretariat & Pelayanan Umat -->
                <div class="md:col-span-4 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gold-400 flex items-center gap-2">
                        <i class="fa-solid fa-building-columns"></i>
                        <span>Sekretariat &amp; Pelayanan Umat</span>
                    </h3>
                    <ul class="space-y-2.5 text-xs sm:text-sm text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <i class="fa-solid fa-location-dot text-gold-400 mt-1 w-4 text-center shrink-0"></i>
                            <span>{{ $vihara['address'] ?? $vihara['address_short'] }}</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="fa-solid fa-phone text-gold-400 w-4 text-center shrink-0"></i>
                            <span>{{ $vihara['phone'] }}</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="fa-regular fa-envelope text-gold-400 w-4 text-center shrink-0"></i>
                            <span>{{ $vihara['email'] ?? 'sekretariat@mahabodhi.or.id' }}</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i class="fa-regular fa-clock text-gold-400 mt-1 w-4 text-center shrink-0"></i>
                            <span>{{ $vihara['operational_hours'] ?? 'Buka Setiap Hari: 07.00 – 21.00 WIB' }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Layanan Administrasi & Navigasi -->
                <div class="md:col-span-3 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gold-400 flex items-center gap-2">
                        <i class="fa-solid fa-hands-holding-circle"></i>
                        <span>Layanan Sekretariat</span>
                    </h3>
                    <ul class="space-y-2 text-xs sm:text-sm text-slate-400">
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-gold-400 text-xs"></i>
                            <span>Tanda Terima Dana &amp; Barang</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-gold-400 text-xs"></i>
                            <span>Administrasi Pemberkatan Nikah</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-gold-400 text-xs"></i>
                            <span>Persuratan &amp; Dokumen Resmi</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-gold-400 text-xs"></i>
                            <span>Jadwal Puja Bakti &amp; Meditasi</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400 text-center sm:text-left">
                <div class="flex items-center justify-center sm:justify-start gap-2">
                    <i class="fa-solid fa-dharmachakra text-gold-400 shrink-0"></i>
                    <span class="break-words">&copy; {{ date('Y') }} {{ $vihara['name'] }}. Sabbe Satta Bhavantu Sukhitatta.</span>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-5">
                    <a href="{{ !empty($isArticleDetail) ? route('landing').'#profil' : '#profil' }}" class="hover:text-white transition">Profil</a>
                    <a href="{{ !empty($isArticleDetail) ? route('landing').'#jadwal' : '#jadwal' }}" class="hover:text-white transition">Jadwal</a>
                    <a href="{{ !empty($isArticleDetail) ? route('landing').'#galeri' : '#galeri' }}" class="hover:text-white transition">Galeri</a>
                    <a href="{{ !empty($isArticleDetail) ? route('landing').'#berita' : '#berita' }}" class="hover:text-white transition">Berita</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 3-Second Initial Buddhist Dhamma Loading Screen
            const loadingScreen = document.getElementById('dhammaLoadingScreen');
            const loadingBar = document.getElementById('dhammaLoadingBar');
            if (loadingScreen) {
                const dhammaMessages = @json($vihara['dhammapada_quotes'] ?? []);
                if (dhammaMessages.length > 0) {
                    const chosen = dhammaMessages[Math.floor(Math.random() * dhammaMessages.length)];
                    const paliEl = document.getElementById('loadingDhammaPali');
                    const transEl = document.getElementById('loadingDhammaTranslation');
                    const sourceEl = document.getElementById('loadingDhammaSource');
                    if (paliEl && chosen.pali) {
                        paliEl.textContent = '"' + chosen.pali + '"';
                    }
                    if (transEl && chosen.translation) {
                        transEl.textContent = chosen.translation;
                    }
                    if (sourceEl && chosen.verse) {
                        sourceEl.textContent = '— ' + chosen.verse;
                    }
                }

                requestAnimationFrame(function () {
                    if (loadingBar) {
                        loadingBar.style.width = '100%';
                    }
                });

                setTimeout(function () {
                    loadingScreen.classList.add('opacity-0', 'pointer-events-none');
                    setTimeout(function () {
                        loadingScreen.classList.add('hidden');
                    }, 700);
                }, 3000);
            }

            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenuPanel = document.getElementById('mobileMenuPanel');
            if (mobileMenuBtn && mobileMenuPanel) {
                mobileMenuBtn.addEventListener('click', function () {
                    mobileMenuPanel.classList.toggle('hidden');
                });
            }

            // Floating Contact Button Popover Toggle
            const floatingContactBtn = document.getElementById('floatingContactBtn');
            const floatingContactCard = document.getElementById('floatingContactCard');
            const closeFloatingContactBtn = document.getElementById('closeFloatingContactBtn');
            if (floatingContactBtn && floatingContactCard) {
                floatingContactBtn.addEventListener('click', function () {
                    floatingContactCard.classList.toggle('hidden');
                });
                if (closeFloatingContactBtn) {
                    closeFloatingContactBtn.addEventListener('click', function () {
                        floatingContactCard.classList.add('hidden');
                    });
                }
            }

            // Scroll Effects: Navbar shadow & IntersectionObserver reveal-on-scroll
            const mainNavbar = document.getElementById('mainNavbar');
            window.addEventListener('scroll', function () {
                if (!mainNavbar) {
                    return;
                }
                if (window.scrollY > 24) {
                    mainNavbar.classList.add('shadow-md');
                } else {
                    mainNavbar.classList.remove('shadow-md');
                }
            }, { passive: true });

            const revealElements = document.querySelectorAll('.reveal-on-scroll');
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });

                revealElements.forEach(function (el) {
                    observer.observe(el);
                });
            } else {
                revealElements.forEach(function (el) {
                    el.classList.add('is-visible');
                });
            }

            // Reusable Client-Side Filter + Search + Pagination Helper for Landing Sections
            function initLandingSectionPaginator(config) {
                const cards = Array.from(document.querySelectorAll(config.cardSelector));
                const filterButtons = Array.from(document.querySelectorAll(config.filterBtnSelector));
                const searchInput = config.searchInputId ? document.getElementById(config.searchInputId) : null;
                const infoEl = config.infoId ? document.getElementById(config.infoId) : null;
                const paginationEl = config.paginationId ? document.getElementById(config.paginationId) : null;
                const emptyStateEl = config.emptyStateId ? document.getElementById(config.emptyStateId) : null;
                const perPage = config.perPage || 6;

                if (cards.length === 0) {
                    return;
                }

                let activeFilter = 'all';
                let searchQuery = '';
                let currentPage = 1;

                function render() {
                    const matched = cards.filter(function (card) {
                        const catVal = card.getAttribute(config.filterAttr) || '';
                        const matchesFilter = activeFilter === 'all' || catVal === activeFilter;
                        const matchesSearch = !searchQuery || (card.textContent || '').toLowerCase().indexOf(searchQuery) !== -1;
                        return matchesFilter && matchesSearch;
                    });

                    const total = matched.length;
                    const totalPages = Math.max(1, Math.ceil(total / perPage));
                    if (currentPage > totalPages) {
                        currentPage = totalPages;
                    }

                    const startIdx = (currentPage - 1) * perPage;
                    const endIdx = startIdx + perPage;

                    cards.forEach(function (card) {
                        card.classList.add('hidden');
                    });

                    matched.slice(startIdx, endIdx).forEach(function (card) {
                        card.classList.remove('hidden');
                        card.classList.add('is-visible');
                    });

                    if (emptyStateEl) {
                        if (total === 0) {
                            emptyStateEl.classList.remove('hidden');
                        } else {
                            emptyStateEl.classList.add('hidden');
                        }
                    }

                    if (infoEl) {
                        if (total === 0) {
                            infoEl.textContent = 'Menampilkan 0 ' + config.unitLabel;
                        } else {
                            infoEl.textContent = 'Menampilkan ' + (startIdx + 1) + '–' + Math.min(endIdx, total) + ' dari ' + total + ' ' + config.unitLabel;
                        }
                    }

                    if (paginationEl) {
                        paginationEl.innerHTML = '';
                        if (totalPages <= 1) {
                            return;
                        }

                        const prevBtn = document.createElement('button');
                        prevBtn.type = 'button';
                        prevBtn.disabled = currentPage === 1;
                        prevBtn.className = 'px-3 py-1.5 rounded-xl text-xs font-semibold border transition ' + (currentPage === 1 ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed' : 'bg-white text-slate-700 border-slate-200 hover:bg-gold-50 hover:border-gold-300');
                        prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left text-[10px] mr-1"></i> Sebelumnya';
                        prevBtn.addEventListener('click', function () {
                            if (currentPage > 1) {
                                currentPage--;
                                render();
                            }
                        });
                        paginationEl.appendChild(prevBtn);

                        for (let p = 1; p <= totalPages; p++) {
                            const pageBtn = document.createElement('button');
                            pageBtn.type = 'button';
                            pageBtn.textContent = String(p);
                            pageBtn.className = p === currentPage
                                ? 'w-8 h-8 rounded-xl text-xs font-bold bg-gold-500 text-white shadow-2xs transition'
                                : 'w-8 h-8 rounded-xl text-xs font-semibold bg-white text-slate-700 border border-slate-200 hover:bg-gold-50 hover:border-gold-300 transition';
                            pageBtn.addEventListener('click', function () {
                                currentPage = p;
                                render();
                            });
                            paginationEl.appendChild(pageBtn);
                        }

                        const nextBtn = document.createElement('button');
                        nextBtn.type = 'button';
                        nextBtn.disabled = currentPage === totalPages;
                        nextBtn.className = 'px-3 py-1.5 rounded-xl text-xs font-semibold border transition ' + (currentPage === totalPages ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed' : 'bg-white text-slate-700 border-slate-200 hover:bg-gold-50 hover:border-gold-300');
                        nextBtn.innerHTML = 'Berikutnya <i class="fa-solid fa-chevron-right text-[10px] ml-1"></i>';
                        nextBtn.addEventListener('click', function () {
                            if (currentPage < totalPages) {
                                currentPage++;
                                render();
                            }
                        });
                        paginationEl.appendChild(nextBtn);
                    }
                }

                filterButtons.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        activeFilter = btn.getAttribute(config.btnFilterAttr) || 'all';
                        currentPage = 1;
                        filterButtons.forEach(function (b) {
                            b.className = config.inactiveBtnClass;
                        });
                        btn.className = config.activeBtnClass;
                        render();
                    });
                });

                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        searchQuery = (searchInput.value || '').trim().toLowerCase();
                        currentPage = 1;
                        render();
                    });
                }

                render();
            }

            // Initialize Pagination + Filter for Schedules (6 per page)
            initLandingSectionPaginator({
                cardSelector: '.schedule-card',
                filterBtnSelector: '.schedule-filter-btn',
                filterAttr: 'data-category',
                btnFilterAttr: 'data-filter',
                infoId: 'scheduleCountInfo',
                paginationId: 'schedulePaginationContainer',
                perPage: 6,
                unitLabel: 'jadwal kegiatan',
                activeBtnClass: 'schedule-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-gold-500 text-white whitespace-nowrap transition',
                inactiveBtnClass: 'schedule-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-gold-50 border border-slate-200 whitespace-nowrap transition'
            });

            // Initialize Pagination + Search + Filter for Galleries (8 per page)
            initLandingSectionPaginator({
                cardSelector: '.gallery-card',
                filterBtnSelector: '.gallery-filter-btn',
                filterAttr: 'data-media-type',
                btnFilterAttr: 'data-media-filter',
                searchInputId: 'gallerySearchInput',
                infoId: 'galleryCountInfo',
                paginationId: 'galleryPaginationContainer',
                emptyStateId: 'galleryEmptyState',
                perPage: 8,
                unitLabel: 'dokumentasi galeri',
                activeBtnClass: 'gallery-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-gold-500 text-white whitespace-nowrap transition inline-flex items-center gap-1.5',
                inactiveBtnClass: 'gallery-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-gold-50 whitespace-nowrap transition inline-flex items-center gap-1.5'
            });

            // Initialize Pagination + Search + Filter for Articles (6 per page)
            initLandingSectionPaginator({
                cardSelector: '.article-card',
                filterBtnSelector: '.article-filter-btn',
                filterAttr: 'data-article-category',
                btnFilterAttr: 'data-article-filter',
                searchInputId: 'articleSearchInput',
                infoId: 'articleCountInfo',
                paginationId: 'articlePaginationContainer',
                emptyStateId: 'articleEmptyState',
                perPage: 6,
                unitLabel: 'artikel & renungan',
                activeBtnClass: 'article-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-gold-500 text-white whitespace-nowrap transition',
                inactiveBtnClass: 'article-filter-btn px-3.5 py-2 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-gold-50 border border-slate-200 whitespace-nowrap transition'
            });

            const galleryCards = document.querySelectorAll('.gallery-card');

            // Gallery Modal
            const modal = document.getElementById('galleryModal');
            const closeModalBtn = document.getElementById('closeModalBtn');
            if (modal) {
                galleryCards.forEach(function (card) {
                    card.addEventListener('click', function () {
                        document.getElementById('modalTitle').textContent = card.getAttribute('data-title') || '';
                        document.getElementById('modalCategory').textContent = card.getAttribute('data-category') || '';
                        document.getElementById('modalMeta').textContent = card.getAttribute('data-date') || '';
                        document.getElementById('modalBody').textContent = card.getAttribute('data-description') || '';
                        const mediaContainer = document.getElementById('modalMediaContainer');
                        const url = card.getAttribute('data-url');
                        if (card.getAttribute('data-media-type') === 'video' && url) {
                            mediaContainer.innerHTML = '<div class="aspect-video"><iframe src="' + url + '" class="w-full h-full" allowfullscreen></iframe></div>';
                        } else if (url) {
                            mediaContainer.innerHTML = '<img src="' + url + '" class="w-full max-h-96 object-cover">';
                        }
                        modal.classList.remove('hidden');
                    });
                });

                if (closeModalBtn) {
                    closeModalBtn.addEventListener('click', function () {
                        modal.classList.add('hidden');
                        document.getElementById('modalMediaContainer').innerHTML = '';
                    });
                }
            }
        });
    </script>
</div>
</body>
</html>
