<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Http\UploadedFile;
use ZipArchive;

class DocxTemplateService
{
    /**
     * Extract clean, editable HTML from an uploaded Microsoft Word (.docx) file.
     */
    public function parseDocxToHtml(UploadedFile $file): string
    {
        $realPath = $file->getRealPath() ?: $file->path();

        $zip = new ZipArchive;
        if ($zip->open($realPath) !== true) {
            return '';
        }

        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! is_string($documentXml) || trim($documentXml) === '') {
            return '';
        }

        $dom = new DOMDocument;
        $previousLibxml = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($documentXml);
        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxml);

        if (! $loaded) {
            return '';
        }

        $bodies = $dom->getElementsByTagName('body');
        if ($bodies->length === 0) {
            return '';
        }

        $body = $bodies->item(0);
        if (! $body instanceof DOMElement) {
            return '';
        }

        $htmlParts = [];
        foreach ($body->childNodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            if ($node->localName === 'p') {
                $paragraphHtml = $this->renderParagraph($node);
                if ($paragraphHtml !== '') {
                    $htmlParts[] = $paragraphHtml;
                }
            } elseif ($node->localName === 'tbl') {
                $tableHtml = $this->renderTable($node);
                if ($tableHtml !== '') {
                    $htmlParts[] = $tableHtml;
                }
            }
        }

        return $this->sanitizeHtml(implode("\n", $htmlParts));
    }

    /**
     * Sanitize user-edited or imported template HTML so only safe formatting markup remains.
     */
    public function sanitizeHtml(string $html): string
    {
        $allowedTags = '<p><div><span><strong><b><em><i><u><br><h1><h2><h3><h4><ul><ol><li><table><thead><tbody><tr><th><td><hr><blockquote>';
        $clean = strip_tags($html, $allowedTags);

        // Strip inline JS event handlers and javascript: URLs
        $clean = preg_replace('/\son[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/javascript\s*:/i', '', $clean) ?? $clean;

        return trim($clean);
    }

    /**
     * Available template variables / placeholders for receipt and thank-you letter templates.
     *
     * @return array<string, string>
     */
    public static function availablePlaceholders(): array
    {
        return [
            '{{nomor}}' => 'Nomor Tanda Terima',
            '{{tanggal}}' => 'Tanggal Penerimaan',
            '{{jenis}}' => 'Jenis Penerimaan / Kategori',
            '{{nama_donatur}}' => 'Nama Penyerah / Donatur',
            '{{instansi}}' => 'Instansi / Alamat Donatur',
            '{{kontak}}' => 'Kontak / Telepon Donatur',
            '{{daftar_barang}}' => 'Tabel Daftar Barang / Bantuan (Multi-Item)',
            '{{uraian}}' => 'Ringkasan Uraian Barang / Bantuan',
            '{{jumlah}}' => 'Jumlah / Kuantitas',
            '{{nominal}}' => 'Total Nominal (Rp)',
            '{{penerima}}' => 'Petugas Penerima Sekretariat',
            '{{ucapan}}' => 'Pesan Ucapan Terima Kasih (Anumodana)',
            '{{catatan}}' => 'Catatan Pemeriksaan Sekretariat',
        ];
    }

    /**
     * Default editable HTML template for Bukti Tanda Terima (A4).
     */
    public static function defaultReceiptTemplateHtml(): string
    {
        return <<<'HTML'
<div style="text-align: center; border-bottom: 1px solid #cbd5e1; padding-bottom: 10px; margin-bottom: 12px;">
    <p style="font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.14em; color: #64748b; margin: 0;">Bukti Resmi Penerimaan Sekretariat Wihara</p>
    <h3 style="font-size: 18px; font-weight: 800; text-decoration: underline; margin: 4px 0;">BUKTI TANDA TERIMA {{jenis}}</h3>
    <p style="font-size: 12px; color: #334155; margin: 0;">Nomor Registrasi: <strong>{{nomor}}</strong> &bull; Tanggal: <strong>{{tanggal}}</strong></p>
</div>
<p style="margin-bottom: 10px;">Sekretariat Pengurus <strong>Wihara Mahabodhi Buddhist Centre Semarang</strong> menerangkan telah menerima penyerahan dengan rincian lengkap sebagai berikut:</p>
<table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 12px;">
    <tbody>
        <tr>
            <td style="border: 1px solid #94a3b8; padding: 7px; background-color: #f1f5f9; font-weight: bold; width: 34%;">Telah Diterima Dari</td>
            <td style="border: 1px solid #94a3b8; padding: 7px; font-weight: bold;">{{nama_donatur}}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #94a3b8; padding: 7px; background-color: #f1f5f9; font-weight: bold;">Instansi / Alamat Domisili</td>
            <td style="border: 1px solid #94a3b8; padding: 7px;">{{instansi}}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #94a3b8; padding: 7px; background-color: #f1f5f9; font-weight: bold;">Nomor Telepon / Kontak</td>
            <td style="border: 1px solid #94a3b8; padding: 7px;">{{kontak}}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #94a3b8; padding: 7px; background-color: #f1f5f9; font-weight: bold;">Kategori Penerimaan</td>
            <td style="border: 1px solid #94a3b8; padding: 7px;">{{jenis}}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #94a3b8; padding: 7px; background-color: #f1f5f9; font-weight: bold;">Petugas Penerima</td>
            <td style="border: 1px solid #94a3b8; padding: 7px;">{{penerima}}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #94a3b8; padding: 7px; background-color: #f1f5f9; font-weight: bold;">Catatan Sekretariat</td>
            <td style="border: 1px solid #94a3b8; padding: 7px;">{{catatan}}</td>
        </tr>
    </tbody>
</table>
<p style="font-weight: bold; margin-bottom: 6px;">Rincian Barang / Bantuan yang Diterima:</p>
<div style="margin-bottom: 12px;">{{daftar_barang}}</div>
<div style="padding: 10px 14px; border: 2px solid #0f172a; border-radius: 8px; background-color: #f8fafc; margin-bottom: 10px;">
    <strong>Jumlah Nominal Tercatat:</strong> <span style="font-size: 15px; font-weight: 800;">{{nominal}}</span>
</div>
<p style="padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; background-color: #f8fafc; font-style: italic;"><strong>Ucapan Anumodana:</strong> "{{ucapan}}"</p>
HTML;
    }

    /**
     * Default editable HTML template for Surat Ucapan Terima Kasih (Anumodana).
     */
    public static function defaultThankYouTemplateHtml(): string
    {
        return <<<'HTML'
<table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 12px;">
    <tbody>
        <tr>
            <td style="width: 16%; font-weight: bold; padding: 2px 0;">Nomor</td>
            <td style="width: 3%; padding: 2px 0;">:</td>
            <td style="font-weight: bold; padding: 2px 0;">{{nomor}}</td>
            <td style="text-align: right; padding: 2px 0;">Semarang, <strong>{{tanggal}}</strong></td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 2px 0;">Lampiran</td>
            <td style="padding: 2px 0;">:</td>
            <td colspan="2" style="padding: 2px 0;">1 (Satu) Lembar Salinan Tanda Terima</td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 2px 0;">Perihal</td>
            <td style="padding: 2px 0;">:</td>
            <td colspan="2" style="font-weight: bold; text-decoration: underline; padding: 2px 0;">Surat Ucapan Terima Kasih (Anumodana)</td>
        </tr>
    </tbody>
</table>
<div style="padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; background-color: #f8fafc; margin-bottom: 12px;">
    <p style="margin: 0; font-size: 11px; color: #64748b;">Kepada Yth.</p>
    <p style="margin: 2px 0; font-weight: bold; font-size: 13px;">Bapak / Ibu / Dermawan {{nama_donatur}}</p>
    <p style="margin: 0; font-size: 12px;">{{instansi}}</p>
</div>
<p style="font-weight: bold; font-style: italic; margin-bottom: 8px;">Namo Sanghyang Adi Buddhaya, Namo Buddhaya.</p>
<p style="text-align: justify; margin-bottom: 10px;">Semoga pancaran cinta kasih (Metta) dan berkah Tiratana senantiasa melimpah kepada Bapak/Ibu/Saudara/i beserta seluruh keluarga. Melalui surat resmi ini, Pengurus <strong>Wihara Mahabodhi Buddhist Centre Semarang</strong> menyampaikan apresiasi serta ucapan terima kasih yang sebesar-besarnya (Anumodana) atas partisipasi kebajikan ({{jenis}}) berupa:</p>
<div style="margin-bottom: 12px;">{{daftar_barang}}</div>
<p style="margin-bottom: 10px;"><strong>Total Nominal Tercatat:</strong> {{nominal}} &bull; <strong>Diterima Oleh:</strong> {{penerima}} ({{tanggal}})</p>
<blockquote style="padding: 10px 14px; border-left: 4px solid #0f172a; background-color: #f8fafc; margin: 0 0 10px 0; text-align: justify;">{{ucapan}}</blockquote>
<p style="text-align: justify; margin-bottom: 8px;">Demikian Surat Ucapan Terima Kasih ini kami sampaikan sebagai bukti pencatatan resmi Sekretariat Wihara Mahabodhi Buddhist Centre Semarang.</p>
<p style="font-weight: bold; font-style: italic;">Sabbe Satta Bhavantu Sukhitatta. Sadhu, Sadhu, Sadhu.</p>
HTML;
    }

    /**
     * Render a Word <w:p> paragraph element to HTML.
     */
    private function renderParagraph(DOMElement $paragraph): string
    {
        $align = '';
        foreach ($paragraph->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'pPr') {
                foreach ($child->childNodes as $prop) {
                    if ($prop instanceof DOMElement && $prop->localName === 'jc') {
                        $val = strtolower((string) $prop->getAttribute('w:val') ?: (string) $prop->getAttribute('val'));
                        if (in_array($val, ['center', 'right', 'left'], true)) {
                            $align = $val;
                        } elseif ($val === 'both') {
                            $align = 'justify';
                        }
                    }
                }
            }
        }

        $innerHtml = $this->renderInlineChildren($paragraph);
        if (trim(strip_tags($innerHtml)) === '') {
            return '';
        }

        $style = $align !== '' ? ' style="text-align: '.$align.'; margin-bottom: 8px;"' : ' style="margin-bottom: 8px;"';

        return '<p'.$style.'>'.$innerHtml.'</p>';
    }

    /**
     * Render inline runs (<w:r>) and text inside a Word node.
     */
    private function renderInlineChildren(DOMNode $parent): string
    {
        $result = '';

        foreach ($parent->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            if ($child->localName === 'r') {
                $isBold = false;
                $isItalic = false;
                $isUnderline = false;
                $runContent = '';

                foreach ($child->childNodes as $part) {
                    if (! $part instanceof DOMElement) {
                        continue;
                    }

                    if ($part->localName === 'rPr') {
                        foreach ($part->childNodes as $rProp) {
                            if (! $rProp instanceof DOMElement) {
                                continue;
                            }
                            if ($rProp->localName === 'b') {
                                $val = $rProp->getAttribute('w:val') ?: $rProp->getAttribute('val');
                                $isBold = $val !== '0' && $val !== 'false';
                            } elseif ($rProp->localName === 'i') {
                                $val = $rProp->getAttribute('w:val') ?: $rProp->getAttribute('val');
                                $isItalic = $val !== '0' && $val !== 'false';
                            } elseif ($rProp->localName === 'u') {
                                $val = $rProp->getAttribute('w:val') ?: $rProp->getAttribute('val');
                                $isUnderline = $val !== 'none';
                            }
                        }
                    } elseif ($part->localName === 't') {
                        $runContent .= htmlspecialchars($part->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    } elseif ($part->localName === 'br') {
                        $runContent .= '<br>';
                    } elseif ($part->localName === 'tab') {
                        $runContent .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                }

                if ($runContent !== '') {
                    if ($isUnderline) {
                        $runContent = '<u>'.$runContent.'</u>';
                    }
                    if ($isItalic) {
                        $runContent = '<em>'.$runContent.'</em>';
                    }
                    if ($isBold) {
                        $runContent = '<strong>'.$runContent.'</strong>';
                    }
                    $result .= $runContent;
                }
            } elseif ($child->localName === 'hyperlink') {
                $result .= $this->renderInlineChildren($child);
            }
        }

        return $result;
    }

    /**
     * Render a Word <w:tbl> table element to HTML.
     */
    private function renderTable(DOMElement $table): string
    {
        $rowsHtml = [];

        foreach ($table->childNodes as $tr) {
            if (! $tr instanceof DOMElement || $tr->localName !== 'tr') {
                continue;
            }

            $cellsHtml = [];
            foreach ($tr->childNodes as $tc) {
                if (! $tc instanceof DOMElement || $tc->localName !== 'tc') {
                    continue;
                }

                $cellParagraphs = [];
                foreach ($tc->childNodes as $p) {
                    if ($p instanceof DOMElement && $p->localName === 'p') {
                        $text = $this->renderInlineChildren($p);
                        if (trim(strip_tags($text)) !== '') {
                            $cellParagraphs[] = $text;
                        }
                    }
                }

                $cellsHtml[] = '<td style="border: 1px solid #94a3b8; padding: 6px; vertical-align: top;">'.implode('<br>', $cellParagraphs).'</td>';
            }

            if ($cellsHtml !== []) {
                $rowsHtml[] = '<tr>'.implode('', $cellsHtml).'</tr>';
            }
        }

        if ($rowsHtml === []) {
            return '';
        }

        return '<table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 12px;"><tbody>'.implode('', $rowsHtml).'</tbody></table>';
    }
}
