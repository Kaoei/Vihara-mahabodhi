<?php

namespace Database\Factories;

use App\Models\Receipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receipt>
 */
class ReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'receipt_number' => 'TT/WMBC/X/2026/'.fake()->unique()->numerify('###'),
            'receipt_type' => fake()->randomElement(['Bantuan Dana', 'Barang', 'Surat / Dokumen']),
            'receipt_date' => now()->toDateString(),
            'donor_name' => fake()->name(),
            'donor_institution' => fake()->company(),
            'donor_contact' => '0812'.fake()->numerify('########'),
            'item_name' => 'Bantuan Perlengkapan Puja Bakti & Operasional Dharmasala',
            'quantity' => '1 Paket',
            'nominal' => 1500000,
            'receiver_name' => 'Sekretariat Wihara Mahabodhi',
            'thank_you_message' => 'Anumodana atas kebajikan dan kemurahan hati Bapak/Ibu. Semoga semua makhluk berbahagia.',
            'notes' => 'Diterima dalam keadaan baik di Sekretariat Vihara.',
        ];
    }
}
