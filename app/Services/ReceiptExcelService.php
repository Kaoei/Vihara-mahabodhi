<?php

namespace App\Services;

use App\Models\Document;
use App\Models\MarriageCertificate;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use SimpleXMLElement;
use Throwable;
use ZipArchive;

class ReceiptExcelService
{
    /**
     * Parse an uploaded Excel (.xlsx) or CSV (.csv / .txt) file into normalized receipt rows.
     *
     * @return array<int, array{
     *     receipt_number: string,
     *     receipt_type: string,
     *     receipt_date: string,
     *     donor_name: string,
     *     donor_institution: ?string,
     *     donor_contact: ?string,
     *     item_name: string,
     *     quantity: string,
     *     nominal: ?float,
     *     receiver_name: string,
     *     thank_you_message: ?string,
     *     notes: ?string
     * }>
     */
    public function parseUploadedFile(UploadedFile $file, string $defaultReceiver = 'Sekretariat Wihara Mahabodhi'): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $realPath = $file->getRealPath() ?: $file->path();

        if ($extension === 'xlsx' || $this->isZipArchive($realPath)) {
            $rawRows = $this->parseXlsx($realPath);
        } else {
            $rawRows = $this->parseCsv($realPath);
        }

        return $this->normalizeRows($rawRows, $defaultReceiver);
    }

    /**
     * Generate a valid OpenXML .xlsx binary string from an array of rows.
     *
     * @param  array<int, array<int, string|int|float|null>>  $rows
     */
    public function generateXlsxBinary(array $rows, string $sheetName = 'Tanda Terima'): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'wmbc_xlsx_');
        if ($tempFile === false) {
            return '';
        }

        $zip = new ZipArchive;
        $zip->open($tempFile, ZipArchive::OVERWRITE | ZipArchive::CREATE);

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>');

        $safeSheetName = htmlspecialchars($sheetName, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.$safeSheetName.'" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>');

        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'</Relationships>');

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $excelRowNumber = $rowIndex + 1;
            $sheetXml .= '<row r="'.$excelRowNumber.'">';
            foreach (array_values($row) as $colIndex => $cellValue) {
                $colLetter = $this->columnIndexToLetter($colIndex);
                $cellRef = $colLetter.$excelRowNumber;
                $escaped = htmlspecialchars((string) ($cellValue ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $sheetXml .= '<c r="'.$cellRef.'" t="inlineStr"><is><t>'.$escaped.'</t></is></c>';
            }
            $sheetXml .= '</row>';
        }

        $sheetXml .= '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $binary = (string) file_get_contents($tempFile);
        @unlink($tempFile);

        return $binary;
    }

    /**
     * Build template rows for Excel download.
     *
     * @return array<int, array<int, string|int|float|null>>
     */
    public function getTemplateRows(): array
    {
        return [
            [
                'No Tanda Terima',
                'Jenis (Bantuan Dana / Barang / Surat / Dokumen)',
                'Tanggal (YYYY-MM-DD)',
                'Nama Penyerah / Donatur',
                'Instansi / Alamat',
                'Kontak / Telepon',
                'Uraian Bantuan / Barang / Perihal Surat',
                'Jumlah / Kuantitas',
                'Nominal (Rp)',
                'Petugas Penerima',
                'Ucapan Terima Kasih (Anumodana)',
                'Catatan',
            ],
            [
                '',
                'Bantuan Dana',
                now()->toDateString(),
                'Bpk. Suryadi Kusuma',
                'Umat Wihara Mahabodhi Semarang',
                '081234567890',
                'Dana Paramita Operasional Puja Bakti & Pelita Altar',
                '1 Paket Dana',
                1000000,
                'Upasika Metta Kusuma',
                'Anumodana atas kebajikan Bapak Suryadi Kusuma beserta keluarga. Semoga semua makhluk berbahagia.',
                'Diterima via Sekretariat Wihara',
            ],
            [
                '',
                'Barang',
                now()->toDateString(),
                'Ibu Liana Wijaya',
                'Donatur Bakti Sosial',
                '081398765432',
                'Beras 100 Kg & Minyak Goreng 20 Liter untuk Baksos Wihara',
                '10 Karung & 2 Dus',
                2200000,
                'Upasaka Dharmadi Wijaya',
                'Terima kasih dan Anumodana atas bantuan sembako untuk kegiatan bakti sosial Wihara Mahabodhi.',
                'Barang disimpan di ruang logistik',
            ],
        ];
    }

    /**
     * Check whether a file starts with the PK zip signature.
     */
    private function isZipArchive(string $filePath): bool
    {
        if (! is_readable($filePath)) {
            return false;
        }

        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            return false;
        }

        $signature = fread($handle, 4);
        fclose($handle);

        return $signature === "PK\x03\x04";
    }

    /**
     * Parse an .xlsx file into a 2D array of string values.
     *
     * @return array<int, array<int, string>>
     */
    private function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return [];
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if (is_string($sharedStringsXml) && $sharedStringsXml !== '') {
            try {
                $xml = new SimpleXMLElement($sharedStringsXml);
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } else {
                        $text = '';
                        foreach ($si->r as $run) {
                            $text .= (string) $run->t;
                        }
                        $sharedStrings[] = $text;
                    }
                }
            } catch (Throwable) {
                $sharedStrings = [];
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! is_string($sheetXml) || $sheetXml === '') {
            return [];
        }

        $rows = [];
        try {
            $xml = new SimpleXMLElement($sheetXml);
            if (! isset($xml->sheetData->row)) {
                return [];
            }

            foreach ($xml->sheetData->row as $rowElement) {
                $rowCells = [];
                $nextColIndex = 0;

                foreach ($rowElement->c as $cell) {
                    $cellRef = (string) $cell['r'];
                    $colIndex = $cellRef !== '' ? $this->cellReferenceToColumnIndex($cellRef) : $nextColIndex;

                    while ($nextColIndex < $colIndex) {
                        $rowCells[$nextColIndex] = '';
                        $nextColIndex++;
                    }

                    $type = (string) $cell['t'];
                    $value = '';

                    if ($type === 's') {
                        $idx = (int) $cell->v;
                        $value = $sharedStrings[$idx] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $value = isset($cell->is->t) ? (string) $cell->is->t : '';
                    } else {
                        $value = isset($cell->v) ? (string) $cell->v : '';
                    }

                    $rowCells[$colIndex] = trim($value);
                    $nextColIndex = $colIndex + 1;
                }

                $rows[] = $rowCells;
            }
        } catch (Throwable) {
            return [];
        }

        return $rows;
    }

    /**
     * Parse a CSV file (comma or semicolon separated) into a 2D array of string values.
     *
     * @return array<int, array<int, string>>
     */
    private function parseCsv(string $filePath): array
    {
        if (! is_readable($filePath)) {
            return [];
        }

        $content = (string) file_get_contents($filePath);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;

        $lines = preg_split('/\r\n|\r|\n/', trim($content)) ?: [];
        if ($lines === []) {
            return [];
        }

        $firstLine = $lines[0];
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $parsed = str_getcsv($line, $delimiter);
            $rows[] = array_map(fn (?string $val): string => trim((string) $val), $parsed);
        }

        return $rows;
    }

    /**
     * Normalize raw spreadsheet rows into receipt attribute arrays.
     *
     * @param  array<int, array<int, string>>  $rawRows
     * @return array<int, array{
     *     receipt_number: string,
     *     receipt_type: string,
     *     receipt_date: string,
     *     donor_name: string,
     *     donor_institution: ?string,
     *     donor_contact: ?string,
     *     item_name: string,
     *     quantity: string,
     *     nominal: ?float,
     *     receiver_name: string,
     *     thank_you_message: ?string,
     *     notes: ?string
     * }>
     */
    private function normalizeRows(array $rawRows, string $defaultReceiver): array
    {
        if ($rawRows === []) {
            return [];
        }

        $normalized = [];
        $sequenceOffset = 0;

        foreach ($rawRows as $index => $row) {
            $nonEmpty = array_filter($row, fn (string $cell): bool => $cell !== '');
            if ($nonEmpty === []) {
                continue;
            }

            if ($index === 0 && $this->looksLikeHeaderRow($row)) {
                continue;
            }

            $donorName = $row[3] ?? ($row[1] ?? '');
            $itemName = $row[6] ?? ($row[2] ?? '');

            if ($donorName === '' && $itemName === '') {
                continue;
            }

            $rawType = $row[1] ?? 'Bantuan Dana';
            $receiptType = $this->normalizeReceiptType($rawType);
            $rawDate = $row[2] ?? now()->toDateString();
            $receiptDate = $this->normalizeDate($rawDate);

            $rawNumber = trim($row[0] ?? '');
            if ($rawNumber === '' || Receipt::where('receipt_number', $rawNumber)->exists()) {
                $sequenceOffset++;
                $rawNumber = $this->generateUniqueReceiptNumber($sequenceOffset);
            }

            $rawNominal = preg_replace('/[^0-9.]/', '', (string) ($row[8] ?? ''));
            $nominal = ($rawNominal !== null && $rawNominal !== '' && is_numeric($rawNominal))
                ? (float) $rawNominal
                : null;

            $donorFinal = $donorName !== '' ? $donorName : 'Donatur / Simpatisan Wihara';
            $itemFinal = $itemName !== '' ? $itemName : 'Bantuan Operasional Wihara';

            $thankYou = trim($row[10] ?? '');
            if ($thankYou === '') {
                $thankYou = sprintf(
                    'Anumodana dan terima kasih yang tulus kepada %s atas penyerahan %s (%s). Semoga kebajikan ini membawa berkah kebahagiaan, kesehatan, dan kedamaian bagi Bapak/Ibu beserta keluarga.',
                    $donorFinal,
                    strtolower($receiptType),
                    $itemFinal
                );
            }

            $normalized[] = [
                'receipt_number' => $rawNumber,
                'receipt_type' => $receiptType,
                'receipt_date' => $receiptDate,
                'donor_name' => $donorFinal,
                'donor_institution' => ($row[4] ?? '') !== '' ? $row[4] : null,
                'donor_contact' => ($row[5] ?? '') !== '' ? $row[5] : null,
                'item_name' => $itemFinal,
                'quantity' => ($row[7] ?? '') !== '' ? $row[7] : '1 Paket',
                'nominal' => $nominal,
                'receiver_name' => ($row[9] ?? '') !== '' ? $row[9] : $defaultReceiver,
                'thank_you_message' => $thankYou,
                'notes' => ($row[11] ?? '') !== '' ? $row[11] : null,
            ];
        }

        return $normalized;
    }

    /**
     * Determine if a spreadsheet row is a column header row.
     *
     * @param  array<int, string>  $row
     */
    private function looksLikeHeaderRow(array $row): bool
    {
        $joined = strtolower(implode(' ', $row));

        return str_contains($joined, 'tanda terima')
            || str_contains($joined, 'donatur')
            || str_contains($joined, 'penyerah')
            || str_contains($joined, 'jenis')
            || str_contains($joined, 'uraian');
    }

    /**
     * Normalize receipt type string into one of the standard categories.
     */
    private function normalizeReceiptType(string $raw): string
    {
        $lower = strtolower(trim($raw));

        if (str_contains($lower, 'barang') || str_contains($lower, 'sembako') || str_contains($lower, 'logistik')) {
            return 'Barang';
        }

        if (str_contains($lower, 'surat') || str_contains($lower, 'dokumen') || str_contains($lower, 'berkas')) {
            return 'Surat / Dokumen';
        }

        return 'Bantuan Dana';
    }

    /**
     * Normalize date string into YYYY-MM-DD format.
     */
    private function normalizeDate(string $rawDate): string
    {
        $trimmed = trim($rawDate);
        if ($trimmed === '') {
            return now()->toDateString();
        }

        if (is_numeric($trimmed) && (int) $trimmed > 30000) {
            return Carbon::create(1899, 12, 30)->addDays((int) $trimmed)->toDateString();
        }

        try {
            return Carbon::parse($trimmed)->toDateString();
        } catch (Throwable) {
            return now()->toDateString();
        }
    }

    /**
     * Generate a unique receipt number with an offset for bulk imports.
     */
    private function generateUniqueReceiptNumber(int $offset): string
    {
        $year = now()->format('Y');
        $monthRoman = Receipt::monthToRoman((int) now()->format('n'));
        $seq = Receipt::count() + $offset;

        do {
            $candidate = sprintf('TT/WMBC/%s/%s/%03d', $monthRoman, $year, $seq);
            $seq++;
        } while (Receipt::where('receipt_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * Parse raw rows from an uploaded Excel (.xlsx) or CSV (.csv / .txt) file.
     *
     * @return array<int, array<int, string>>
     */
    public function parseRawSpreadsheet(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $realPath = $file->getRealPath() ?: $file->path();

        if ($extension === 'xlsx' || $this->isZipArchive($realPath)) {
            return $this->parseXlsx($realPath);
        }

        return $this->parseCsv($realPath);
    }

    /**
     * Build template rows for Arsip Persuratan & Dokumen Excel import.
     *
     * @return array<int, array<int, string|int|float|null>>
     */
    public function getDocumentTemplateRows(): array
    {
        return [
            [
                'Nomor Surat / Dokumen',
                'Kategori (Surat Masuk / Surat Keluar / SK / Piagam)',
                'Judul / Perihal Dokumen',
                'Tanggal Surat (YYYY-MM-DD)',
                'Tanggal Diterima (YYYY-MM-DD)',
                'Pengirim',
                'Penerima / Tujuan',
                'Ukuran Kertas',
                'Kode Arsip',
                'Ringkasan / Catatan',
            ],
            [
                '010/SM/WMBC/XI/2026',
                'Surat Masuk',
                'Undangan Rapat Koordinasi Perayaan Waisak Kota Semarang',
                now()->toDateString(),
                now()->toDateString(),
                'Majelis Agama Buddha Semarang',
                'Pengurus Wihara Mahabodhi Buddhist Centre',
                'A4',
                'ARS-A4-SM-10',
                'Surat undangan koordinasi kegiatan bersama lintas wihara.',
            ],
            [
                '011/SK/WMBC/XI/2026',
                'Surat Keluar',
                'Surat Pemberitahuan Bakti Sosial Pengobatan Gratis',
                now()->toDateString(),
                now()->toDateString(),
                'Sekretariat Wihara Mahabodhi Buddhist Centre',
                'Kelurahan Karangkidul Semarang',
                'A4',
                'ARS-A4-SK-11',
                'Pemberitahuan pelaksanaan kegiatan bakti sosial rutin.',
            ],
        ];
    }

    /**
     * Parse uploaded Excel/CSV into normalized Document attribute rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parseDocumentsFile(UploadedFile $file): array
    {
        $rawRows = $this->parseRawSpreadsheet($file);
        if ($rawRows === []) {
            return [];
        }

        $firstRowText = strtolower(implode(' ', $rawRows[0] ?? []));
        $hasHeader = str_contains($firstRowText, 'nomor')
            || str_contains($firstRowText, 'kategori')
            || str_contains($firstRowText, 'judul')
            || str_contains($firstRowText, 'pengirim');

        $dataRows = $hasHeader ? array_slice($rawRows, 1) : $rawRows;
        $normalized = [];
        $seqOffset = 1;

        foreach ($dataRows as $row) {
            if (trim(implode('', $row)) === '') {
                continue;
            }

            $docNumber = trim((string) ($row[0] ?? ''));
            $category = trim((string) ($row[1] ?? '')) ?: 'Surat Masuk';
            $title = trim((string) ($row[2] ?? ''));
            $docDateRaw = trim((string) ($row[3] ?? ''));
            $recvDateRaw = trim((string) ($row[4] ?? ''));
            $sender = trim((string) ($row[5] ?? '')) ?: 'Pengirim Tidak Tercantum';
            $recipient = trim((string) ($row[6] ?? '')) ?: 'Pengurus Wihara Mahabodhi Buddhist Centre';
            $paperSize = trim((string) ($row[7] ?? '')) ?: 'A4';
            $archiveCode = trim((string) ($row[8] ?? '')) ?: 'ARS-A4-01';
            $summary = trim((string) ($row[9] ?? ''));

            if ($title === '' && $docNumber === '') {
                continue;
            }

            if ($docNumber === '' || Document::where('document_number', $docNumber)->exists()) {
                $docNumber = $this->generateUniqueDocumentNumber($seqOffset);
                $seqOffset++;
            }

            $docDate = $this->normalizeDate($docDateRaw);
            $recvDate = $recvDateRaw !== '' ? $this->normalizeDate($recvDateRaw) : $docDate;

            $normalized[] = [
                'document_number' => $docNumber,
                'category' => $category,
                'title' => $title !== '' ? $title : 'Dokumen Arsip '.$docNumber,
                'document_date' => $docDate,
                'received_date' => $recvDate,
                'sender' => $sender,
                'recipient' => $recipient,
                'paper_size' => $paperSize,
                'archive_code' => $archiveCode,
                'summary' => $summary,
            ];
        }

        return $normalized;
    }

    /**
     * Build template rows for Arsip Surat Nikah & Pemberkatan Excel import.
     *
     * @return array<int, array<int, string|int|float|null>>
     */
    public function getMarriageTemplateRows(): array
    {
        return [
            [
                'Nomor Arsip / Surat (Kosongkan untuk otomatis)',
                'Jenis Dokumen Nikah',
                'Tanggal Pemberkatan (YYYY-MM-DD)',
                'Nama Mempelai Pria',
                'Nama Mempelai Wanita',
                'Status Arsip (Terdaftar & Verifikasi Berkas / Selesai Pemberkatan / Tercatat di Dukcapil)',
                'Catatan / Keterangan Arsip',
            ],
            [
                '',
                'Surat Pemberkatan Perkawinan',
                now()->toDateString(),
                'Andi Pratama Wijaya',
                'Evelyn Natalia Tan',
                'Selesai Pemberkatan',
                'Arsip akta pemberkatan perkawinan Buddhis.',
            ],
            [
                '',
                'Berita Acara Pemberkatan',
                now()->toDateString(),
                'Michael Santoso',
                'Jessica Lim',
                'Tercatat di Dukcapil',
                'Salinan berita acara dan pengantar Dukcapil telah lengkap.',
            ],
        ];
    }

    /**
     * Parse uploaded Excel/CSV into normalized MarriageCertificate attribute rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parseMarriagesFile(UploadedFile $file): array
    {
        $rawRows = $this->parseRawSpreadsheet($file);
        if ($rawRows === []) {
            return [];
        }

        $firstRowText = strtolower(implode(' ', $rawRows[0] ?? []));
        $hasHeader = str_contains($firstRowText, 'nomor')
            || str_contains($firstRowText, 'mempelai')
            || str_contains($firstRowText, 'jenis')
            || str_contains($firstRowText, 'tanggal');

        $dataRows = $hasHeader ? array_slice($rawRows, 1) : $rawRows;
        $normalized = [];
        $seqOffset = 1;

        foreach ($dataRows as $row) {
            if (trim(implode('', $row)) === '') {
                continue;
            }

            $certNumber = trim((string) ($row[0] ?? ''));
            $docType = trim((string) ($row[1] ?? '')) ?: 'Surat Pemberkatan Perkawinan';
            $weddingDateRaw = trim((string) ($row[2] ?? ''));
            $groomName = trim((string) ($row[3] ?? ''));
            $brideName = trim((string) ($row[4] ?? ''));
            $statusRaw = trim((string) ($row[5] ?? ''));
            $notes = trim((string) ($row[6] ?? ''));

            if ($groomName === '' && $brideName === '') {
                continue;
            }

            if ($certNumber === '' || MarriageCertificate::where('certificate_number', $certNumber)->exists()) {
                $certNumber = $this->generateUniqueMarriageNumber($seqOffset);
                $seqOffset++;
            }

            $validStatuses = [
                'Terdaftar & Verifikasi Berkas',
                'Selesai Pemberkatan',
                'Tercatat di Dukcapil',
            ];
            $status = in_array($statusRaw, $validStatuses, true) ? $statusRaw : 'Selesai Pemberkatan';
            $weddingDate = $this->normalizeDate($weddingDateRaw);

            $normalized[] = [
                'certificate_number' => $certNumber,
                'document_type' => $docType,
                'registration_date' => $weddingDate,
                'wedding_date' => $weddingDate,
                'groom_name' => $groomName !== '' ? $groomName : 'Mempelai Pria',
                'bride_name' => $brideName !== '' ? $brideName : 'Mempelai Wanita',
                'status' => $status,
                'premarital_course_done' => true,
                'notes' => $notes !== '' ? $notes : null,
            ];
        }

        return $normalized;
    }

    /**
     * Generate a unique document number for bulk imports.
     */
    private function generateUniqueDocumentNumber(int $offset): string
    {
        $year = now()->format('Y');
        $monthRoman = Receipt::monthToRoman((int) now()->format('n'));
        $seq = Document::count() + $offset;

        do {
            $candidate = sprintf('%03d/ARS/WMBC/%s/%s', $seq, $monthRoman, $year);
            $seq++;
        } while (Document::where('document_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * Generate a unique marriage certificate number for bulk imports.
     */
    private function generateUniqueMarriageNumber(int $offset): string
    {
        $year = now()->format('Y');
        $monthRoman = Receipt::monthToRoman((int) now()->format('n'));
        $seq = MarriageCertificate::count() + $offset;

        do {
            $candidate = sprintf('%03d/SPP-BDH/WMBC/%s/%s', $seq, $monthRoman, $year);
            $seq++;
        } while (MarriageCertificate::where('certificate_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * Convert 0-based column index to Excel column letters (A, B, ..., Z, AA).
     */
    private function columnIndexToLetter(int $index): string
    {
        $letter = '';
        $num = $index + 1;

        while ($num > 0) {
            $modulo = ($num - 1) % 26;
            $letter = chr(65 + $modulo).$letter;
            $num = (int) (($num - $modulo) / 26);
        }

        return $letter;
    }

    /**
     * Convert an Excel cell reference (e.g. "C5") into a 0-based column index.
     */
    private function cellReferenceToColumnIndex(string $cellRef): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($cellRef)) ?? 'A';
        $length = strlen($letters);
        $index = 0;

        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }
}
