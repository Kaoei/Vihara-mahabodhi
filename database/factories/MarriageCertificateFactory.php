<?php

namespace Database\Factories;

use App\Models\MarriageCertificate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarriageCertificate>
 */
class MarriageCertificateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'certificate_number' => sprintf('%03d/SPP-BDH/WMBC/XI/2026', fake()->unique()->numberBetween(10, 999)),
            'document_type' => fake()->randomElement([
                'Surat Pemberkatan Perkawinan',
                'Berita Acara Pemberkatan',
                'Sertifikat Bimbingan Pra-Nikah',
                'Surat Pengantar Dukcapil',
            ]),
            'registration_date' => now()->subDays(14)->toDateString(),
            'wedding_date' => now()->toDateString(),
            'wedding_time' => '09:00 - 11:00 WIB',
            'wedding_location' => 'Dharmasala Utama Wihara Mahabodhi Buddhist Centre Semarang',
            'groom_name' => 'Kevin Wijaya Kusuma',
            'groom_buddhist_name' => 'Upasaka Dhammapala',
            'groom_nik' => '3374011405960001',
            'groom_birth_info' => 'Semarang, 14 Mei 1996',
            'groom_father_name' => 'Hendra Wijaya',
            'groom_mother_name' => 'Lina Susanti',
            'groom_address' => 'Jl. Gajahmada No. 88, Kec. Semarang Tengah, Kota Semarang',
            'bride_name' => 'Clara Anastasia Tan',
            'bride_buddhist_name' => 'Upasika Metta Devi',
            'bride_nik' => '3374022208980002',
            'bride_birth_info' => 'Semarang, 22 Agustus 1998',
            'bride_father_name' => 'Budianto Tan',
            'bride_mother_name' => 'Siska Halim',
            'bride_address' => 'Jl. Pandanaran No. 45, Kec. Semarang Selatan, Kota Semarang',
            'officiant_name' => 'Romo Pandita Dharmaputra, S.Ag., M.Pd.B.',
            'witness_one' => 'Drs. Suryanto Gunawan',
            'witness_two' => 'Ir. Hartono Salim',
            'status' => 'Selesai Pemberkatan',
            'premarital_course_done' => true,
            'notes' => 'Berkas persyaratan KTP, KK, Akta Lahir, Surat Pengantar N1-N4, dan Pas Foto Gandeng 4x6 telah lengkap.',
        ];
    }
}
