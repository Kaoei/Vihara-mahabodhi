<?php

namespace App\Models;

use Database\Factories\MarriageCertificateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'certificate_number',
    'document_type',
    'registration_date',
    'wedding_date',
    'wedding_time',
    'wedding_location',
    'groom_name',
    'groom_buddhist_name',
    'groom_nik',
    'groom_birth_info',
    'groom_father_name',
    'groom_mother_name',
    'groom_address',
    'bride_name',
    'bride_buddhist_name',
    'bride_nik',
    'bride_birth_info',
    'bride_father_name',
    'bride_mother_name',
    'bride_address',
    'officiant_name',
    'witness_one',
    'witness_two',
    'status',
    'premarital_course_done',
    'attachment_path',
    'attachment_name',
    'notes',
    'created_by',
])]
class MarriageCertificate extends Model
{
    /** @use HasFactory<MarriageCertificateFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'wedding_date' => 'date',
            'premarital_course_done' => 'boolean',
        ];
    }

    /**
     * Get the user who registered the marriage certificate.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate the next sequential Buddhist marriage certificate number.
     */
    public static function generateNextNumber(): string
    {
        $year = now()->format('Y');
        $monthRoman = Receipt::monthToRoman((int) now()->format('n'));
        $count = self::count() + 1;

        do {
            $candidate = sprintf('%03d/SPP-BDH/WMBC/%s/%s', $count, $monthRoman, $year);
            $count++;
        } while (self::where('certificate_number', $candidate)->exists());

        return $candidate;
    }
}
