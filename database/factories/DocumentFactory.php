<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_number' => fake()->unique()->numerify('###').'/SM/WMBC/X/2026',
            'category' => fake()->randomElement(['Surat Masuk', 'Surat Keluar', 'Dokumen Umum A4']),
            'title' => fake()->sentence(5),
            'document_date' => now()->toDateString(),
            'received_date' => now()->toDateString(),
            'sender' => fake()->company(),
            'recipient' => 'Pengurus Wihara Mahabodhi Buddhist Centre',
            'paper_size' => 'A4',
            'archive_code' => 'ARS-A4-'.fake()->numerify('##'),
            'summary' => fake()->paragraph(),
        ];
    }
}
