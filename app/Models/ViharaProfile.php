<?php

namespace App\Models;

use Database\Factories\ViharaProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'short_name',
    'address',
    'address_short',
    'phone',
    'email',
    'operational_hours',
    'tagline',
    'about',
    'hero_image',
    'default_signer_title',
    'default_signer_name',
    'footer_description',
    'footer_services_title',
    'footer_services',
    'footer_copyright',
    'facilities',
    'dhammapada_quotes',
])]
class ViharaProfile extends Model
{
    /** @use HasFactory<ViharaProfileFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'dhammapada_quotes' => 'array',
            'footer_services' => 'array',
        ];
    }

    /**
     * Default Wihara Mahabodhi profile configuration.
     *
     * @return array<string, mixed>
     */
    public static function defaultData(): array
    {
        return [
            'name' => 'Wihara Mahabodhi Buddhist Centre',
            'short_name' => 'Wihara Mahabodhi',
            'address' => 'Jl. Seroja Timur No. 9-11, Karangkidul, Kec. Semarang Tengah, Kota Semarang, Jawa Tengah 50136',
            'address_short' => 'Jl. Seroja Timur No. 9-11 Semarang',
            'phone' => '(024) 841-5509 / 0812-2500-8899',
            'email' => 'sekretariat@mahabodhi.or.id',
            'operational_hours' => 'Setiap Hari: 07.00 – 21.00 WIB (Sekretariat: 08.00 – 17.00 WIB)',
            'tagline' => 'Harmoni Kesadaran, Welas Asih, dan Kebijaksanaan di Jantung Kota Semarang',
            'about' => 'Wihara Mahabodhi Buddhist Centre merupakan pusat pembinaan spiritual, Puja Bakti, latihan meditasi, dan pelayanan sosial kemasyarakatan yang berlokasi di Jl. Seroja Timur No. 9-11 Semarang. Dengan semangat cinta kasih universal (Metta) dan welas asih (Karuna), wihara ini terbuka bagi seluruh umat dan masyarakat yang ingin mempelajari serta mempraktikkan Dhamma dalam keheningan yang teduh.',
            'hero_image' => '/Assets/Img/hero.png',
            'default_signer_title' => null,
            'default_signer_name' => null,
            'footer_description' => 'Pusat pembinaan spiritual Buddhis, pelaksanaan Puja Bakti, latihan meditasi Samatha-Vipassana, serta pelayanan sosial dan administrasi umat di Kota Semarang.',
            'footer_services_title' => 'Layanan Sekretariat',
            'footer_services' => [
                'Tanda Terima Dana & Barang',
                'Administrasi Pemberkatan Nikah',
                'Persuratan & Dokumen Resmi',
                'Jadwal Puja Bakti & Meditasi',
            ],
            'footer_copyright' => 'Sabbe Satta Bhavantu Sukhitatta.',
            'facilities' => [
                [
                    'id' => 'dharmasala-agung',
                    'title' => 'Dharmasala Agung',
                    'description' => 'Ruang puja bakti utama yang tenang dan khidmat untuk pelaksanaan ibadah rutin, Uposatha, serta perayaan hari besar Buddhis.',
                    'icon' => 'fa-hands-praying',
                ],
                [
                    'id' => 'ruang-meditasi-bodhi',
                    'title' => 'Ruang Meditasi Bodhi',
                    'description' => 'Ruang hening khusus latihan Samatha dan Vipassana Bhavana dengan suasana sejuk dan kondusif bagi ketenangan batin.',
                    'icon' => 'fa-spa',
                ],
                [
                    'id' => 'perpustakaan-dharma',
                    'title' => 'Perpustakaan Dharma',
                    'description' => 'Koleksi kitab suci Tipitaka, buku-buku Sutra, Paritta, serta literatur Buddhis kontemporer untuk segala usia.',
                    'icon' => 'fa-book-open-reader',
                ],
                [
                    'id' => 'sekretariat-layanan-umat',
                    'title' => 'Sekretariat & Layanan Umat',
                    'description' => 'Pusat administrasi surat-menyurat, tanda terima dana/bantuan, konsultasi kegiatan keagamaan, dan pelayanan bakti sosial.',
                    'icon' => 'fa-hand-holding-heart',
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
     * Available FontAwesome icons for facility / information cards.
     *
     * @return array<string, string>
     */
    public static function availableIcons(): array
    {
        return [
            'fa-hands-praying' => 'Puja Bakti / Ibadah',
            'fa-spa' => 'Meditasi / Ketenangan',
            'fa-book-open-reader' => 'Perpustakaan / Dharma',
            'fa-hand-holding-heart' => 'Layanan Umat / Baksos',
            'fa-dharmachakra' => 'Roda Dharma',
            'fa-building-columns' => 'Gedung / Fasilitas Wihara',
            'fa-users' => 'Komunitas / Muda-Mudi',
            'fa-graduation-cap' => 'Sekolah Minggu / Pendidikan',
            'fa-utensils' => 'Dapur / Konsumsi Umat',
            'fa-circle-info' => 'Informasi Umum',
        ];
    }

    /**
     * Retrieve the active WiharaProfile record, creating defaults if none exists yet.
     */
    public static function current(): self
    {
        $profile = self::query()->first();

        if (! $profile) {
            $profile = self::query()->create(self::defaultData());
        }

        return $profile;
    }

    /**
     * Convert the profile model into the array shape consumed by landing.blade.php.
     *
     * @return array<string, mixed>
     */
    public function toLandingArray(): array
    {
        $defaults = self::defaultData();

        return [
            'name' => $this->name ?: $defaults['name'],
            'short_name' => $this->short_name ?: $defaults['short_name'],
            'address' => $this->address ?: $defaults['address'],
            'address_short' => $this->address_short ?: $defaults['address_short'],
            'phone' => $this->phone ?: $defaults['phone'],
            'email' => $this->email ?: $defaults['email'],
            'operational_hours' => $this->operational_hours ?: $defaults['operational_hours'],
            'tagline' => $this->tagline ?: $defaults['tagline'],
            'about' => $this->about ?: $defaults['about'],
            'hero_image' => $this->hero_image ?: $defaults['hero_image'],
            'footer_description' => $this->footer_description ?: $defaults['footer_description'],
            'footer_services_title' => $this->footer_services_title ?: $defaults['footer_services_title'],
            'footer_services' => is_array($this->footer_services) && count($this->footer_services) > 0
                ? $this->footer_services
                : $defaults['footer_services'],
            'footer_copyright' => $this->footer_copyright ?: $defaults['footer_copyright'],
            'facilities' => is_array($this->facilities) ? $this->facilities : $defaults['facilities'],
            'dhammapada_quotes' => is_array($this->dhammapada_quotes) && count($this->dhammapada_quotes) > 0
                ? $this->dhammapada_quotes
                : $defaults['dhammapada_quotes'],
        ];
    }
}
