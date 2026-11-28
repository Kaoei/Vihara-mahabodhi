<?php

namespace App\Models;

use Database\Factories\ReceiptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'receipt_number',
    'receipt_type',
    'receipt_date',
    'donor_name',
    'donor_institution',
    'donor_contact',
    'item_name',
    'quantity',
    'items',
    'nominal',
    'receiver_name',
    'thank_you_message',
    'notes',
    'created_by',
])]
class Receipt extends Model
{
    /** @use HasFactory<ReceiptFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'receipt_date' => 'date',
            'nominal' => 'decimal:2',
            'items' => 'array',
        ];
    }

    /**
     * Return a normalized list of goods/items for this receipt (supports multi-item and legacy single-item).
     *
     * @return array<int, array{name: string, quantity: string, nominal: ?float}>
     */
    public function normalizedItems(): array
    {
        $raw = is_array($this->items) ? $this->items : [];
        $normalized = [];

        foreach ($raw as $entry) {
            if (! is_array($entry)) {
                continue;
            }
            $name = trim((string) ($entry['name'] ?? ''));
            $qty = trim((string) ($entry['quantity'] ?? ''));
            if ($name === '') {
                continue;
            }
            $nom = isset($entry['nominal']) && $entry['nominal'] !== '' && $entry['nominal'] !== null
                ? (float) $entry['nominal']
                : null;
            $normalized[] = [
                'name' => $name,
                'quantity' => $qty !== '' ? $qty : '1 Paket',
                'nominal' => $nom,
            ];
        }

        if ($normalized === [] && trim((string) $this->item_name) !== '') {
            $normalized[] = [
                'name' => (string) $this->item_name,
                'quantity' => (string) ($this->quantity ?: '1 Paket'),
                'nominal' => $this->nominal !== null ? (float) $this->nominal : null,
            ];
        }

        return $normalized;
    }

    /**
     * Get the user who created the receipt.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate the next sequential receipt number.
     */
    public static function generateNextNumber(): string
    {
        $year = now()->format('Y');
        $monthRoman = self::monthToRoman((int) now()->format('n'));
        $count = self::count() + 1;

        do {
            $candidate = sprintf('TT/WMBC/%s/%s/%03d', $monthRoman, $year, $count);
            $count++;
        } while (self::where('receipt_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * Convert month number (1-12) to Roman numeral.
     */
    public static function monthToRoman(int $month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $map[$month] ?? 'X';
    }
}
