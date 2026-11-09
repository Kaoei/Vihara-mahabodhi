<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Document;
use App\Models\Gallery;
use App\Models\MarriageCertificate;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator Utama',
                'description' => 'Memiliki hak akses penuh terhadap seluruh modul administrasi internal, tanda terima, master dokumen A4, jadwal kegiatan, galeri, berita, dan manajemen role.',
                'permissions' => array_keys(Role::availablePermissions()),
                'is_system' => true,
            ]
        );

        $sekretarisRole = Role::updateOrCreate(
            ['slug' => 'sekretaris'],
            [
                'name' => 'Sekretaris & Administrasi',
                'description' => 'Mengelola pembuatan Tanda Terima bantuan/barang/surat, Surat Ucapan Terima Kasih (cetak A4 & import/export Excel), Master Dokumen A4, serta Administrasi Surat Nikah & Pemberkatan Perkawinan Buddhis.',
                'permissions' => ['manage_receipts', 'manage_documents', 'manage_marriages'],
                'is_system' => false,
            ]
        );

        $pengurusRole = Role::updateOrCreate(
            ['slug' => 'pengurus'],
            [
                'name' => 'Pengurus Kegiatan & Publikasi',
                'description' => 'Mengelola pembaruan Jadwal Ibadah/Puja Bakti/Meditasi, Galeri Foto & Video, serta Artikel, Berita & Renungan Dharma pada halaman publik.',
                'permissions' => ['manage_schedules', 'manage_galleries', 'manage_articles'],
                'is_system' => false,
            ]
        );

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@mahabodhi.or.id'],
            [
                'name' => 'Upasaka Dharmadi Wijaya',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'position' => 'Ketua Sekretariat & Admin Utama',
                'phone' => '0812-2500-8899',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $sekretarisUser = User::updateOrCreate(
            ['email' => 'sekretaris@mahabodhi.or.id'],
            [
                'name' => 'Upasika Metta Kusuma',
                'password' => Hash::make('password'),
                'role_id' => $sekretarisRole->id,
                'position' => 'Sekretaris Administrasi & Arsip A4',
                'phone' => '0813-2600-7788',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'pengurus@mahabodhi.or.id'],
            [
                'name' => 'Upasaka Bodhi Santoso',
                'password' => Hash::make('password'),
                'role_id' => $pengurusRole->id,
                'position' => 'Koordinator Puja Bakti & Media Dharma',
                'phone' => '0815-4200-6655',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $receipts = [
            [
                'receipt_number' => 'TT/WMBC/X/2026/001',
                'receipt_type' => 'Bantuan Dana',
                'receipt_date' => '2026-10-18',
                'donor_name' => 'Bpk. Hendra Tanuwijaya & Keluarga',
                'donor_institution' => 'Umat Wihara Mahabodhi Semarang',
                'donor_contact' => '0811-2788-901',
                'item_name' => 'Dana Paramita Pemeliharaan Dharmasala & Pelita Altar Buddha',
                'quantity' => '1 Paket Dana',
                'nominal' => 5000000,
                'receiver_name' => 'Upasika Metta Kusuma',
                'thank_you_message' => 'Sabbadanam Dhammadanam Jinati. Pengurus Wihara Mahabodhi Buddhist Centre Semarang mengucapkan Anumodana dan terima kasih yang tulus atas dukungan Dana Paramita Bapak Hendra Tanuwijaya beserta keluarga. Semoga timbunan kebajikan ini membawa berkah kesehatan, kebahagiaan, dan kemajuan di dalam Dhamma.',
                'notes' => 'Diterima melalui Sekretariat Wihara untuk operasional pelita dan perawatan ruang ibadah utama.',
                'created_by' => $adminUser->id,
            ],
            [
                'receipt_number' => 'TT/WMBC/X/2026/002',
                'receipt_type' => 'Barang',
                'receipt_date' => '2026-10-21',
                'donor_name' => 'Ibu Ratna Dewi Halim',
                'donor_institution' => 'Komunitas Kasih Metta Semarang',
                'donor_contact' => '0812-2901-3344',
                'item_name' => 'Beras Premium 250 Kg & 50 Kitab Paritta Suci Edisi Lengkap',
                'quantity' => '25 Karung & 50 Buku',
                'nominal' => 4750000,
                'receiver_name' => 'Upasaka Dharmadi Wijaya',
                'thank_you_message' => 'Anumodana atas penyerahan bantuan sembako beras dan buku Paritta Suci dari Ibu Ratna Dewi Halim. Bantuan ini sangat bermanfaat bagi kegiatan bakti sosial vihara serta menunjang kelancaran Puja Bakti umat di Dharmasala.',
                'notes' => 'Barang telah disimpan di ruang logistik lantai 1 dan perpustakaan Dharma.',
                'created_by' => $sekretarisUser->id,
            ],
            [
                'receipt_number' => 'TT/WMBC/X/2026/003',
                'receipt_type' => 'Surat / Dokumen',
                'receipt_date' => '2026-10-25',
                'donor_name' => 'Sekretariat WALUBI / Majelis Buddhis Jawa Tengah',
                'donor_institution' => 'Majelis Agama Buddha Kota Semarang',
                'donor_contact' => '024-8415522',
                'item_name' => 'Berkas Undangan & Panduan Pelaksanaan Kathina Puja Bersama 2570 BE',
                'quantity' => '1 Bundel Dokumen A4',
                'nominal' => null,
                'receiver_name' => 'Upasika Metta Kusuma',
                'thank_you_message' => 'Terima kasih atas penyampaian berkas surat resmi dan panduan pelaksanaan Kathina Puja 2570 BE. Dokumen telah kami terima dengan lengkap dan diteruskan kepada jajaran pengurus Wihara Mahabodhi Buddhist Centre.',
                'notes' => 'Diterima di meja resepsionis sekretariat, fisik dokumen A4 lengkap beserta lampiran jadwal.',
                'created_by' => $sekretarisUser->id,
            ],
        ];

        foreach ($receipts as $receiptData) {
            Receipt::updateOrCreate(
                ['receipt_number' => $receiptData['receipt_number']],
                $receiptData
            );
        }

        $documents = [
            [
                'document_number' => '042/SM/WMBC/X/2026',
                'category' => 'Surat Masuk',
                'title' => 'Undangan Koordinasi Kegiatan Keagamaan Buddha Kota Semarang',
                'document_date' => '2026-10-15',
                'received_date' => '2026-10-16',
                'sender' => 'Pembimas Buddha Kanwil Kemenag Provinsi Jawa Tengah',
                'recipient' => 'Ketua Pengurus Wihara Mahabodhi Buddhist Centre',
                'paper_size' => 'A4',
                'archive_code' => 'ARS-A4-SM-01',
                'summary' => 'Surat undangan rapat koordinasi pembinaan umat Buddha dan penyelarasan jadwal kegiatan keagamaan lintas vihara di wilayah Kota Semarang. Berkas asli disimpan dalam map arsip A4 Sekretariat.',
                'created_by' => $sekretarisUser->id,
            ],
            [
                'document_number' => '018/SK/WMBC/X/2026',
                'category' => 'Surat Keluar',
                'title' => 'Permohonan Kesediaan Bhikkhu Sangha untuk Dhammadesana Puja Bakti & Kathina',
                'document_date' => '2026-10-20',
                'received_date' => '2026-10-20',
                'sender' => 'Pengurus Wihara Mahabodhi Buddhist Centre Semarang',
                'recipient' => 'Sangha Theravada / Mahayana Indonesia Wilayah Jawa Tengah',
                'paper_size' => 'A4',
                'archive_code' => 'ARS-A4-SK-01',
                'summary' => 'Permohonan resmi kehadiran anggota Bhikkhu Sangha untuk memimpin Puja Bakti Agung dan memberikan wejangan Dharma (Dhammadesana) di Wihara Mahabodhi Buddhist Centre, Jl. Seroja Timur No. 9-11 Semarang.',
                'created_by' => $adminUser->id,
            ],
            [
                'document_number' => '019/SK/WMBC/X/2026',
                'category' => 'Surat Keluar',
                'title' => 'Pemberitahuan Kegiatan Bakti Sosial & Donor Darah Wihara Mahabodhi',
                'document_date' => '2026-10-23',
                'received_date' => '2026-10-23',
                'sender' => 'Sekretariat Wihara Mahabodhi Buddhist Centre',
                'recipient' => 'Kelurahan Karangkidul & PMI Kota Semarang',
                'paper_size' => 'A4',
                'archive_code' => 'ARS-A4-SK-02',
                'summary' => 'Surat pemberitahuan dan kerja sama penyelenggaraan bakti sosial pembagian sembako serta donor darah rutin di halaman aula Wihara Mahabodhi Buddhist Centre.',
                'created_by' => $sekretarisUser->id,
            ],
            [
                'document_number' => '005/DOK-A4/WMBC/X/2026',
                'category' => 'Dokumen Umum A4',
                'title' => 'Pedoman Tata Tertib Dharmasala, Ruang Meditasi & Standar Administrasi Surat A4',
                'document_date' => '2026-10-01',
                'received_date' => '2026-10-01',
                'sender' => 'Dewan Pengurus Wihara Mahabodhi Buddhist Centre',
                'recipient' => 'Seluruh Pengurus, Petugas Sekretariat, dan Umat Vihara',
                'paper_size' => 'A4',
                'archive_code' => 'ARS-A4-UM-01',
                'summary' => 'Dokumen standar operasional vihara ukuran A4 yang memuat etika memasuki Dharmasala, ketenangan ruang meditasi, serta format baku penomoran surat masuk, surat keluar, dan tanda terima bantuan.',
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($documents as $docData) {
            Document::updateOrCreate(
                ['document_number' => $docData['document_number']],
                $docData
            );
        }

        $marriages = [
            [
                'certificate_number' => '001/SPP-BDH/WMBC/X/2026',
                'document_type' => 'Surat Pemberkatan Perkawinan',
                'registration_date' => '2026-10-01',
                'wedding_date' => '2026-10-18',
                'wedding_time' => '09:00 - 11:00 WIB',
                'wedding_location' => 'Dharmasala Utama Wihara Mahabodhi Buddhist Centre Semarang',
                'groom_name' => 'Kevin Wijaya Kusuma, S.Kom.',
                'groom_buddhist_name' => 'Upasaka Dhammapala',
                'groom_nik' => '3374011405960001',
                'groom_birth_info' => 'Semarang, 14 Mei 1996',
                'groom_father_name' => 'Hendra Wijaya Kusuma',
                'groom_mother_name' => 'Lina Susanti',
                'groom_address' => 'Jl. Gajahmada No. 88, Kec. Semarang Tengah, Kota Semarang',
                'bride_name' => 'Clara Anastasia Tan, S.E.',
                'bride_buddhist_name' => 'Upasika Metta Devi',
                'bride_nik' => '3374022208980002',
                'bride_birth_info' => 'Semarang, 22 Agustus 1998',
                'bride_father_name' => 'Budianto Tan',
                'bride_mother_name' => 'Siska Halim',
                'bride_address' => 'Jl. Pandanaran No. 45, Kec. Semarang Selatan, Kota Semarang',
                'officiant_name' => 'Romo Pandita Dharmaputra, S.Ag., M.Pd.B.',
                'witness_one' => 'Drs. Suryanto Gunawan',
                'witness_two' => 'Ir. Hartono Salim',
                'status' => 'Tercatat di Dukcapil',
                'premarital_course_done' => true,
                'notes' => 'Berkas KTP, KK, Akta Kelahiran, Surat N1-N4 Kelurahan, Pas Foto Gandeng 4x6, dan Sertifikat Pra-Nikah Buddhis lengkap. Telah dicatatkan di Disdukcapil Kota Semarang.',
                'created_by' => $sekretarisUser->id,
            ],
            [
                'certificate_number' => '002/SPP-BDH/WMBC/X/2026',
                'document_type' => 'Berita Acara Pemberkatan',
                'registration_date' => '2026-10-10',
                'wedding_date' => '2026-10-25',
                'wedding_time' => '10:00 - 12:00 WIB',
                'wedding_location' => 'Dharmasala Utama Wihara Mahabodhi Buddhist Centre Semarang',
                'groom_name' => 'Daniel Setiawan Halim',
                'groom_buddhist_name' => 'Upasaka Saddhavira',
                'groom_nik' => '3374060911950003',
                'groom_birth_info' => 'Surakarta, 09 November 1995',
                'groom_father_name' => 'Gunawan Halim',
                'groom_mother_name' => 'Ratna Kumala',
                'groom_address' => 'Jl. Puri Anjasmoro Blok E1 No. 12, Semarang Barat',
                'bride_name' => 'Felicia Nathania Ong',
                'bride_buddhist_name' => 'Upasika Karuna Ratna',
                'bride_nik' => '3374051903970004',
                'bride_birth_info' => 'Semarang, 19 Maret 1997',
                'bride_father_name' => 'Chandra Ongkowijoyo',
                'bride_mother_name' => 'Melani Sutanto',
                'bride_address' => 'Jl. Sultan Agung No. 104, Gajahmungkur, Kota Semarang',
                'officiant_name' => 'Romo Pandita Bodhi Santoso, S.Ag.',
                'witness_one' => 'Bpk. Hendra Tanuwijaya',
                'witness_two' => 'Bpk. Antonius Wibowo',
                'status' => 'Selesai Pemberkatan',
                'premarital_course_done' => true,
                'notes' => 'Upacara pemberkatan berjalan khidmat dihadiri keluarga kedua mempelai. Surat Keterangan Pemberkatan telah diserahkan untuk pengurusan Akta Perkawinan.',
                'created_by' => $adminUser->id,
            ],
            [
                'certificate_number' => '003/SPP-BDH/WMBC/XI/2026',
                'document_type' => 'Sertifikat Bimbingan Pra-Nikah',
                'registration_date' => '2026-11-02',
                'wedding_date' => '2026-11-22',
                'wedding_time' => '09:00 - 11:00 WIB',
                'wedding_location' => 'Dharmasala Utama Wihara Mahabodhi Buddhist Centre Semarang',
                'groom_name' => 'Raymond Kurniawan Tirtadjaja',
                'groom_buddhist_name' => 'Upasaka Viriya Putra',
                'groom_nik' => '3374030502970005',
                'groom_birth_info' => 'Semarang, 05 Februari 1997',
                'groom_father_name' => 'Tirtadjaja Kurniawan',
                'groom_mother_name' => 'Yenny Kusumawati',
                'groom_address' => 'Jl. Seroja Dalam No. 15, Karangkidul, Semarang Tengah',
                'bride_name' => 'Michelle Valerie Sie',
                'bride_buddhist_name' => 'Upasika Panna Devi',
                'bride_nik' => '3374031207990006',
                'bride_birth_info' => 'Semarang, 12 Juli 1999',
                'bride_father_name' => 'Eddy Sie',
                'bride_mother_name' => 'Diana Pangestu',
                'bride_address' => 'Jl. Thamrin No. 62, Sekayu, Semarang Tengah',
                'officiant_name' => 'Romo Pandita Dharmaputra, S.Ag., M.Pd.B.',
                'witness_one' => 'Bpk. Lukas Tirtadjaja',
                'witness_two' => 'Bpk. Herman Pangestu',
                'status' => 'Terdaftar & Verifikasi Berkas',
                'premarital_course_done' => true,
                'notes' => 'Telah menyelesaikan 4 sesi Bimbingan Pra-Nikah Buddhis di Wihara Mahabodhi. Menunggu hari pelaksanaan pemberkatan perkawinan.',
                'created_by' => $sekretarisUser->id,
            ],
        ];

        foreach ($marriages as $marriageData) {
            MarriageCertificate::updateOrCreate(
                ['certificate_number' => $marriageData['certificate_number']],
                $marriageData
            );
        }

        $schedules = [
            [
                'title' => 'Puja Bakti Umum Minggu Pagi & Dhammadesana',
                'category' => 'Puja Bakti',
                'day_of_week' => 'Minggu',
                'event_date' => null,
                'start_time' => '09:00',
                'end_time' => '11:00',
                'speaker' => 'Bhikkhu Sangha & Pandita Lokapalasraya',
                'location' => 'Dharmasala Utama Lt. 2',
                'description' => 'Puja bakti bersama umat dewasa dan keluarga, lantunan Paritta/Sutra suci, meditasi penenangan batin, serta ceramah Dharma mingguan.',
                'is_recurring' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Latihan Meditasi Vipassana & Metta Bhavana',
                'category' => 'Meditasi',
                'day_of_week' => 'Rabu & Jumat',
                'event_date' => null,
                'start_time' => '19:00',
                'end_time' => '20:45',
                'speaker' => 'YM. Bhikkhu Kheminda / Pembimbing Meditasi',
                'location' => 'Ruang Meditasi Bodhi Lt. 3',
                'description' => 'Sesi latihan kesadaran penuh (Sati), meditasi duduk dan jalan, serta pengembangan cinta kasih universal (Metta) bagi pemula maupun praktisi lanjut.',
                'is_recurring' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Sekolah Minggu Buddhis (SMB) Anak & Remaja',
                'category' => 'Kelas Dharma',
                'day_of_week' => 'Minggu',
                'event_date' => null,
                'start_time' => '09:00',
                'end_time' => '10:45',
                'speaker' => 'Tim Guru Sekolah Minggu Mahabodhi',
                'location' => 'Ruang Kelas Karuna Lt. 1',
                'description' => 'Pembelajaran nilai-nilai moral Buddhis, kisah Jataka, lagu-lagu Buddhis, dan pembentukan karakter berbakti bagi anak-anak serta remaja.',
                'is_recurring' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Puja Bakti Uposatha (Purnama & Tilem) & Atthasila',
                'category' => 'Puja Bakti',
                'day_of_week' => 'Tanggal 1 & 15 Lunar',
                'event_date' => null,
                'start_time' => '19:00',
                'end_time' => '21:00',
                'speaker' => 'Pandita & Dhammaduta Wihara Mahabodhi',
                'location' => 'Dharmasala Utama Lt. 2',
                'description' => 'Pembacaan Paritta Uposatha, perenungan kemurnian sila, dan pelimpahan jasa (Pattidana) bagi leluhur serta semua makhluk.',
                'is_recurring' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Perayaan Agung Kathina Puja & Bakti Sosial Kasih',
                'category' => 'Perayaan Hari Besar',
                'day_of_week' => 'Minggu Khusus',
                'event_date' => '2026-11-08',
                'start_time' => '08:30',
                'end_time' => '13:00',
                'speaker' => 'Anggota Bhikkhu Sangha',
                'location' => 'Aula Agung & Dharmasala Wihara Mahabodhi',
                'description' => 'Persembahan jubah Kathina dan kebutuhan pokok kepada Bhikkhu Sangha pada masa akhir Vassa, dilanjutkan ramah tamah dan pembagian paket kasih.',
                'is_recurring' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($schedules as $scheduleData) {
            Schedule::updateOrCreate(
                ['title' => $scheduleData['title']],
                $scheduleData
            );
        }

        $galleries = [
            [
                'title' => 'Suasana Khidmat Puja Bakti Agung di Dharmasala Utama',
                'category' => 'Puja Bakti',
                'media_type' => 'photo',
                'media_url' => 'https://images.unsplash.com/photo-1545569341-9eb8b30979d9?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1545569341-9eb8b30979d9?auto=format&fit=crop&w=800&q=80',
                'event_date' => '2026-10-12',
                'description' => 'Umat secara khusyuk melantunkan paritta suci dan menyalakan pelita kebijaksanaan di altar utama Wihara Mahabodhi Buddhist Centre Semarang.',
                'is_featured' => true,
            ],
            [
                'title' => 'Sesi Meditasi Malam & Pengembangan Kesadaran (Mindfulness)',
                'category' => 'Meditasi',
                'media_type' => 'photo',
                'media_url' => 'https://images.unsplash.com/photo-1508672019048-805c876b67e2?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1508672019048-805c876b67e2?auto=format&fit=crop&w=800&q=80',
                'event_date' => '2026-10-16',
                'description' => 'Latihan hening bersama di Ruang Meditasi Bodhi untuk menumbuhkan ketenangan batin (Samatha) dan pandangan terang (Vipassana).',
                'is_featured' => true,
            ],
            [
                'title' => 'Dokumentasi Video: Pelita Waisak & Untaian Doa Metta Bhavana',
                'category' => 'Hari Besar Buddha',
                'media_type' => 'video',
                'media_url' => 'https://www.youtube.com/embed/inpok4MKVLM',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=800&q=80',
                'event_date' => '2026-09-28',
                'description' => 'Rekaman suasana penyalaan seribu pelita Dharma dan pemancaran cinta kasih universal bagi perdamaian dunia di Wihara Mahabodhi.',
                'is_featured' => true,
            ],
            [
                'title' => 'Bakti Sosial Pembagian Paket Sembako kepada Warga Sekitar Seroja',
                'category' => 'Bakti Sosial',
                'media_type' => 'photo',
                'media_url' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=800&q=80',
                'event_date' => '2026-10-20',
                'description' => 'Wujud nyata praktik Karuna (welas asih) pengurus dan donatur Wihara Mahabodhi bersama masyarakat di sekitar Jl. Seroja Timur Semarang.',
                'is_featured' => false,
            ],
        ];

        foreach ($galleries as $galleryData) {
            Gallery::updateOrCreate(
                ['title' => $galleryData['title']],
                $galleryData
            );
        }

        $articles = [
            [
                'title' => 'Menemukan Kedamaian Batin di Tengah Kesibukan Melalui Praktik Sati',
                'slug' => 'menemukan-kedamaian-batin-melalui-praktik-sati',
                'category' => 'Renungan Dharma',
                'author' => 'Tim Dharma Wihara Mahabodhi',
                'excerpt' => 'Di tengah derasnya arus informasi dan rutinitas harian, perhatian murni (Sati) menjadi jangkar batin yang menjaga kita tetap tenang, jernih, dan penuh kasih.',
                'content' => "Dalam Dhammapada Bab Pikiran (Cittavagga), Sang Buddha membabarkan bahwa pikiran yang terjaga dan terlatih dengan baik akan membawa kebahagiaan sejati.\n\nSering kali ketegangan batin bukan disebabkan oleh banyaknya pekerjaan, melainkan oleh pikiran yang mengembara ke masa lalu dengan penyesalan atau ke masa depan dengan kecemasan. Saat kita melatih Sati—perhatian penuh pada napas masuk dan napas keluar di saat ini—kita memberi ruang bagi kebijaksanaan untuk tumbuh.\n\nMari luangkan waktu setiap pagi dan malam selama 15 menit untuk duduk hening, menyadari napas, dan memancarkan pikiran cinta kasih: 'Semoga semua makhluk hidup berbahagia, bebas dari derita, dan damai sejahtera.'",
                'cover_image' => 'https://images.unsplash.com/photo-1508672019048-805c876b67e2?auto=format&fit=crop&w=1000&q=80',
                'published_at' => now()->subDays(5),
                'is_published' => true,
                'is_featured' => true,
            ],
            [
                'title' => 'Persiapan Perayaan Agung Kathina Puja & Dana Makan Bhikkhu Sangha',
                'slug' => 'persiapan-perayaan-agung-kathina-puja-wihara-mahabodhi',
                'category' => 'Berita Kegiatan',
                'author' => 'Sekretariat Wihara Mahabodhi',
                'excerpt' => 'Wihara Mahabodhi Buddhist Centre Semarang mengundang seluruh umat untuk hadir dalam rangkaian Puja Bakti Kathina dan persembahan dana empat kebutuhan pokok Sangha.',
                'content' => "Namo Buddhaya,\n\nBerakhirnya masa Vassa selama tiga bulan merupakan momen suci bagi umat Buddha untuk mempraktikkan Sanghadana pada perayaan Kathina Puja. Wihara Mahabodhi Buddhist Centre (Jl. Seroja Timur No. 9-11 Semarang) akan menyelenggarakan Puja Bakti Kathina yang dihadiri oleh anggota Bhikkhu Sangha.\n\nRangkaian acara meliputi Pindapatta pagi di halaman vihara, prosesi persembahan jubah dan kebutuhan pokok (Cattari Paccaya), Dhammadesana, serta pelimpahan jasa bagi para leluhur. Umat yang ingin berpartisipasi dalam dana barang maupun dana operasional dapat menghubungi Sekretariat Vihara.",
                'cover_image' => 'https://images.unsplash.com/photo-1545569341-9eb8b30979d9?auto=format&fit=crop&w=1000&q=80',
                'published_at' => now()->subDays(2),
                'is_published' => true,
                'is_featured' => true,
            ],
            [
                'title' => 'Pengumuman Layanan Sekretariat Digital & Standarisasi Tanda Terima Bantuan',
                'slug' => 'pengumuman-layanan-sekretariat-digital-tanda-terima',
                'category' => 'Pengumuman',
                'author' => 'Upasika Metta Kusuma',
                'excerpt' => 'Untuk meningkatkan transparansi dan kerapian arsip, setiap penerimaan bantuan dana, barang, maupun surat kini dilengkapi Tanda Terima dan Surat Ucapan Terima Kasih resmi.',
                'content' => "Dalam rangka menata administrasi Wihara Mahabodhi Buddhist Centre secara tertib, akuntabel, dan modern, Sekretariat Vihara kini menerapkan sistem pencatatan digital terpadu.\n\nSetiap penyerahan bantuan dana, barang bakti sosial, perlengkapan altar, maupun surat menyurat (format A4) akan langsung dicatat dan diterbitkan Tanda Terima resmi beserta Surat Ucapan Terima Kasih (Anumodana). Para donatur juga dapat meminta salinan cetak A4 langsung melalui petugas sekretariat yang bertugas.",
                'cover_image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1000&q=80',
                'published_at' => now()->subDay(),
                'is_published' => true,
                'is_featured' => false,
            ],
        ];

        foreach ($articles as $articleData) {
            Article::updateOrCreate(
                ['slug' => $articleData['slug']],
                $articleData
            );
        }
    }
}
