<?php

namespace App\Models;

use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'document_number',
    'category',
    'title',
    'document_date',
    'received_date',
    'sender',
    'recipient',
    'paper_size',
    'archive_code',
    'summary',
    'attachment_path',
    'attachment_name',
    'attachments',
    'created_by',
])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'received_date' => 'date',
            'attachments' => 'array',
        ];
    }

    /**
     * Return all uploaded files in a normalized list (supports both legacy single file and multi-file arrays).
     *
     * @return array<int, array{path: string, name: string}>
     */
    public function allAttachments(): array
    {
        $items = is_array($this->attachments) ? $this->attachments : [];
        $normalized = [];

        foreach ($items as $item) {
            if (is_array($item) && ! empty($item['path'])) {
                $normalized[] = [
                    'path' => (string) $item['path'],
                    'name' => (string) ($item['name'] ?? basename((string) $item['path'])),
                ];
            }
        }

        if (count($normalized) === 0 && ! empty($this->attachment_path)) {
            $normalized[] = [
                'path' => (string) $this->attachment_path,
                'name' => (string) ($this->attachment_name ?: basename((string) $this->attachment_path)),
            ];
        }

        return $normalized;
    }

    /**
     * Get the user who registered the document.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
