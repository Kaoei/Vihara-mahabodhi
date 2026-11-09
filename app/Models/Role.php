<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'permissions', 'is_system'])]
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    /**
     * All available permission definitions in the internal system.
     *
     * @return array<string, string>
     */
    public static function availablePermissions(): array
    {
        return [
            'manage_receipts' => 'Kelola Tanda Terima & Ucapan Terima Kasih (Print & Excel)',
            'manage_documents' => 'Kelola Master Dokumen (Surat Masuk/Keluar & Arsip A4)',
            'manage_marriages' => 'Kelola Administrasi Surat Nikah & Pemberkatan Perkawinan Buddhis',
            'manage_schedules' => 'Kelola Jadwal Ibadah, Puja Bakti & Meditasi',
            'manage_galleries' => 'Kelola Galeri Foto & Video Kegiatan',
            'manage_articles' => 'Kelola Berita, Pengumuman & Renungan Dharma',
            'manage_roles' => 'Kelola Role & Hak Akses Pengguna',
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Users assigned to this role.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if the role grants a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        if ($this->is_system || in_array('*', $permissions, true)) {
            return true;
        }

        if ($permission === 'manage_marriages' && in_array('manage_documents', $permissions, true)) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }
}
