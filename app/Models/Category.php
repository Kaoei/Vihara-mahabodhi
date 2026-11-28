<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['module', 'name'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * Supported modules and their default category lists.
     *
     * @return array<string, array<int, string>>
     */
    public static function defaultsByModule(): array
    {
        return [
            'receipts' => ['Bantuan Dana', 'Barang', 'Surat / Dokumen'],
            'documents' => ['Surat Masuk', 'Surat Keluar', 'SK / Piagam'],
            'marriages' => [
                'Surat Pemberkatan Perkawinan',
                'Berita Acara Pemberkatan',
                'Sertifikat Bimbingan Pra-Nikah',
                'Surat Pengantar Dukcapil',
            ],
            'schedules' => ['Puja Bakti', 'Meditasi', 'Kelas Dharma', 'Perayaan Hari Besar', 'Kegiatan Sosial'],
            'galleries' => ['Puja Bakti', 'Perayaan Hari Besar', 'Meditasi', 'Bakti Sosial', 'Dhamma Talk', 'Kegiatan Umum'],
            'articles' => ['Berita Kegiatan', 'Renungan Dharma', 'Pengumuman', 'Artikel Buddhis'],
        ];
    }

    /**
     * Map module keys to human-readable labels.
     *
     * @return array<string, string>
     */
    public static function moduleLabels(): array
    {
        return [
            'receipts' => 'Jenis Tanda Terima',
            'documents' => 'Kategori Arsip Dokumen',
            'marriages' => 'Jenis Dokumen Pernikahan',
            'schedules' => 'Kategori Jadwal Ibadah',
            'galleries' => 'Kategori Galeri Dokumentasi',
            'articles' => 'Kategori Berita & Artikel',
        ];
    }

    /**
     * Ensure default categories exist for a given module.
     */
    public static function ensureDefaults(string $module): void
    {
        $defaults = self::defaultsByModule()[$module] ?? [];
        if ($defaults === []) {
            return;
        }

        if (self::query()->where('module', $module)->exists()) {
            return;
        }

        foreach ($defaults as $name) {
            self::query()->firstOrCreate([
                'module' => $module,
                'name' => $name,
            ]);
        }
    }

    /**
     * Ensure a specific category name is stored for the given module.
     */
    public static function ensureExists(string $module, string $name): self
    {
        self::ensureDefaults($module);

        return self::query()->firstOrCreate([
            'module' => $module,
            'name' => trim($name),
        ]);
    }

    /**
     * Get all Category models for a specific module.
     *
     * @return Collection<int, self>
     */
    public static function forModule(string $module): Collection
    {
        self::ensureDefaults($module);

        return self::query()
            ->where('module', $module)
            ->orderBy('id')
            ->get();
    }

    /**
     * Get all Category models grouped by module.
     *
     * @return array<string, Collection<int, self>>
     */
    public static function allGrouped(): array
    {
        $grouped = [];

        foreach (array_keys(self::defaultsByModule()) as $module) {
            $grouped[$module] = self::forModule($module);
        }

        return $grouped;
    }

    /**
     * Get all category names grouped by module.
     *
     * @return array<string, array<int, string>>
     */
    public static function namesGrouped(): array
    {
        $names = [];

        foreach (self::allGrouped() as $module => $collection) {
            $names[$module] = $collection->pluck('name')->all();
        }

        return $names;
    }
}
