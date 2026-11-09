<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Gallery;
use App\Models\Schedule;
use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    /**
     * Get the shared Vihara Mahabodhi profile configuration.
     *
     * @return array<string, mixed>
     */
    private function getViharaProfile(): array
    {
        return [
            'name' => 'Vihara Mahabodhi Buddhist Centre',
            'short_name' => 'Vihara Mahabodhi',
            'address' => 'Jl. Seroja Timur No. 9-11, Karangkidul, Kec. Semarang Tengah, Kota Semarang, Jawa Tengah 50136',
            'address_short' => 'Jl. Seroja Timur No. 9-11 Semarang',
            'phone' => '(024) 841-5509 / 0812-2500-8899',
            'email' => 'sekretariat@mahabodhi.or.id',
            'operational_hours' => 'Setiap Hari: 07.00 – 21.00 WIB (Sekretariat: 08.00 – 17.00 WIB)',
            'tagline' => 'Harmoni Kesadaran, Welas Asih, dan Kebijaksanaan di Jantung Kota Semarang',
            'about' => 'Vihara Mahabodhi Buddhist Centre merupakan pusat pembinaan spiritual, Puja Bakti, latihan meditasi, dan pelayanan sosial kemasyarakatan yang berlokasi di Jl. Seroja Timur No. 9-11 Semarang. Dengan semangat cinta kasih universal (Metta) dan welas asih (Karuna), vihara ini terbuka bagi seluruh umat dan masyarakat yang ingin mempelajari serta mempraktikkan Dhamma dalam keheningan yang teduh.',
            'facilities' => [
                [
                    'title' => 'Dharmasala Agung',
                    'description' => 'Ruang puja bakti utama yang tenang dan khidmat untuk pelaksanaan ibadah rutin, Uposatha, serta perayaan hari besar Buddhis.',
                ],
                [
                    'title' => 'Ruang Meditasi Bodhi',
                    'description' => 'Ruang hening khusus latihan Samatha dan Vipassana Bhavana dengan suasana sejuk dan kondusif bagi ketenangan batin.',
                ],
                [
                    'title' => 'Perpustakaan Dharma',
                    'description' => 'Koleksi kitab suci Tipitaka, buku-buku Sutra, Paritta, serta literatur Buddhis kontemporer untuk segala usia.',
                ],
                [
                    'title' => 'Sekretariat & Layanan Umat',
                    'description' => 'Pusat administrasi surat-menyurat, tanda terima dana/bantuan, konsultasi kegiatan keagamaan, dan pelayanan bakti sosial.',
                ],
            ],
            'dhammapada_quotes' => [
                [
                    'verse' => 'Dhammapada 183',
                    'pali' => 'Sabbapāpassa akaraṇaṃ, kusalassa upasampadā, sacittapariyodapanaṃ, etaṃ buddhāna sāsanaṃ.',
                    'translation' => 'Tidak melakukan segala bentuk kejahatan, senantiasa mengembangkan kebajikan, dan memurnikan pikiran sendiri; inilah ajaran para Buddha.',
                ],
                [
                    'verse' => 'Dhammapada 223',
                    'pali' => 'Akkodhena jine kodhaṃ, asādhuṃ sādhunā jine, jine kadariyaṃ dānena, saccenālikavādinaṃ.',
                    'translation' => 'Kalahkan kemarahan dengan cinta kasih, kalahkan kejahatan dengan kebaikan, kalahkan kekikiran dengan kemurahan hati, dan kalahkan kebohongan dengan kejujuran.',
                ],
                [
                    'verse' => 'Dhammapada 204',
                    'pali' => 'Ārogya paramā lābhā, santuṭṭhī paramaṃ dhanaṃ, vissāsā paramā ñāti, nibbānaṃ paramaṃ sukhaṃ.',
                    'translation' => 'Kesehatan adalah keuntungan terbesar, kepuasan batin adalah kekayaan paling berharga, kepercayaan adalah sanak saudara terbaik, dan Nibbana adalah kebahagiaan tertinggi.',
                ],
            ],
        ];
    }

    /**
     * Display the public landing page for Wihara Mahabodhi Buddhist Centre.
     */
    public function index(): View
    {
        $viharaProfile = $this->getViharaProfile();

        $schedules = Schedule::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $scheduleCategories = $schedules->pluck('category')->unique()->values();

        $galleries = Gallery::query()
            ->orderByDesc('is_featured')
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->get();

        $articles = Article::query()
            ->where('is_published', true)
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('landing', [
            'isArticleDetail' => false,
            'vihara' => $viharaProfile,
            'schedules' => $schedules,
            'scheduleCategories' => $scheduleCategories,
            'galleries' => $galleries,
            'articles' => $articles,
        ]);
    }

    /**
     * Display a dedicated page for a single Article / Berita / Renungan Dharma.
     */
    public function showArticle(Article $article): View
    {
        $relatedArticles = Article::query()
            ->where('is_published', true)
            ->where('id', '!=', $article->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('landing', [
            'isArticleDetail' => true,
            'vihara' => $this->getViharaProfile(),
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }
}
