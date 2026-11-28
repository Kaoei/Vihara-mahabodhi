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
     * Parsed styles from word/styles.xml.
     *
     * @var array{
     *     defaultRun: array<string, mixed>,
     *     defaultPara: array<string, mixed>,
     *     styles: array<string, array<string, mixed>>
     * }
     */
    private array $stylesMap = [
        'defaultRun' => [],
        'defaultPara' => [],
        'styles' => [],
    ];

    /**
     * Parsed numbering formats from word/numbering.xml (numId => [ilvl => fmt]).
     *
     * @var array<string, array<string, string>>
     */
    private array $numberingMap = [];

    /**
     * Extract full-fidelity, editable HTML from an uploaded Microsoft Word (.docx) file,
     * preserving Word headers, footers, embedded images/logos, fonts, sizes, colors,
     * borders (including borderless Kop/signature tables), column widths, merges, and spacing.
     */
    public function parseDocxToHtml(UploadedFile $file): string
    {
        $realPath = $file->getRealPath() ?: $file->path();

        $zip = new ZipArchive;
        if ($zip->open($realPath) !== true) {
            return '';
        }

        try {
            $documentXml = $zip->getFromName('word/document.xml');
            if (! is_string($documentXml) || trim($documentXml) === '') {
                return '';
            }

            $this->stylesMap = $this->parseStylesXml($zip->getFromName('word/styles.xml') ?: '');
            $this->numberingMap = $this->parseNumberingXml($zip->getFromName('word/numbering.xml') ?: '');

            $docDom = $this->loadXmlSafely($documentXml);
            if (! $docDom instanceof DOMDocument) {
                return '';
            }

            $bodies = $docDom->getElementsByTagName('body');
            if ($bodies->length === 0) {
                return '';
            }

            $body = $bodies->item(0);
            if (! $body instanceof DOMElement) {
                return '';
            }

            $docRels = $this->parseRelationships($zip->getFromName('word/_rels/document.xml.rels') ?: '');
            $sectionInfo = $this->extractSectionInfo($body, $docRels);

            // 1. Render Word Header (Kop Surat) if present in header*.xml
            $headerHtml = '';
            if ($sectionInfo['headerTarget'] !== null) {
                $headerPath = $this->normalizeWordTarget($sectionInfo['headerTarget']);
                $headerXml = $zip->getFromName($headerPath);
                if (is_string($headerXml) && trim($headerXml) !== '') {
                    $headerRelsPath = $this->relsPathForPart($headerPath);
                    $headerRels = $this->parseRelationships($zip->getFromName($headerRelsPath) ?: '');
                    $headerDom = $this->loadXmlSafely($headerXml);
                    if ($headerDom instanceof DOMDocument && $headerDom->documentElement instanceof DOMElement) {
                        $renderedHeader = $this->renderBlockContainer($headerDom->documentElement, $zip, $headerRels);
                        if (trim($renderedHeader) !== '') {
                            $headerHtml = '<div class="docx-header" style="margin-bottom: 12px;">'.$renderedHeader.'</div>';
                        }
                    }
                }
            }

            // 2. Render Main Word Document Body
            $bodyHtml = $this->renderBlockContainer($body, $zip, $docRels);

            // 3. Render Word Footer if present in footer*.xml
            $footerHtml = '';
            if ($sectionInfo['footerTarget'] !== null) {
                $footerPath = $this->normalizeWordTarget($sectionInfo['footerTarget']);
                $footerXml = $zip->getFromName($footerPath);
                if (is_string($footerXml) && trim($footerXml) !== '') {
                    $footerRelsPath = $this->relsPathForPart($footerPath);
                    $footerRels = $this->parseRelationships($zip->getFromName($footerRelsPath) ?: '');
                    $footerDom = $this->loadXmlSafely($footerXml);
                    if ($footerDom instanceof DOMDocument && $footerDom->documentElement instanceof DOMElement) {
                        $renderedFooter = $this->renderBlockContainer($footerDom->documentElement, $zip, $footerRels);
                        if (trim($renderedFooter) !== '') {
                            $footerHtml = '<div class="docx-footer" style="margin-top: 16px;">'.$renderedFooter.'</div>';
                        }
                    }
                }
            }

            $combined = trim($headerHtml."\n".$bodyHtml."\n".$footerHtml);
            if ($combined === '') {
                return '';
            }

            $defaultFont = $this->stylesMap['defaultRun']['fontFamily'] ?? "'Times New Roman', Times, serif";
            $defaultSize = $this->stylesMap['defaultRun']['fontSize'] ?? '11pt';
            $margins = $sectionInfo['margins'];

            $wrapperStyle = sprintf(
                'font-family: %s; font-size: %s; color: #0f172a; line-height: 1.35; padding: %smm %smm %smm %smm; box-sizing: border-box; width: 100%%;',
                $defaultFont,
                $defaultSize,
                $margins['top'],
                $margins['right'],
                $margins['bottom'],
                $margins['left']
            );

            $fullHtml = '<div class="docx-page-root" style="'.$this->escapeAttr($wrapperStyle).'">'."\n".$combined."\n".'</div>';

            return $this->sanitizeHtml($fullHtml);
        } finally {
            $zip->close();
        }
    }

    /**
     * Sanitize user-edited or imported template HTML so safe rich formatting and embedded images remain intact.
     */
    public function sanitizeHtml(string $html): string
    {
        // Remove executable or dangerous blocks with their inner contents first
        $clean = preg_replace('#<(script|style|iframe|object|embed|applet|form)[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $clean = preg_replace('#<(script|style|iframe|object|embed|applet|form)[^>]*/>#is', '', $clean) ?? $clean;

        $allowedTags = '<p><div><span><strong><b><em><i><u><s><del><sub><sup><br><h1><h2><h3><h4><h5><h6><ul><ol><li><table><thead><tbody><tfoot><colgroup><col><tr><th><td><hr><blockquote><img><section><header><footer>';
        $clean = strip_tags($clean, $allowedTags);

        // Strip inline JS event handlers (onclick=, onerror=, onload=, etc.)
        $clean = preg_replace('/\son[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;

        // Strip javascript: / vbscript: / non-image data: URIs
        $clean = preg_replace('/(?:javascript|vbscript)\s*:/i', '', $clean) ?? $clean;
        $clean = preg_replace('/data\s*:\s*(?!image\/(?:png|jpe?g|gif|webp|bmp|svg\+xml)\s*;base64,)/i', '', $clean) ?? $clean;

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
            '{{logo}}' => 'Logo Resmi Wihara',
            '{{nama_wihara}}' => 'Nama Resmi Wihara',
            '{{alamat_wihara}}' => 'Alamat Lengkap Wihara',
            '{{telepon_wihara}}' => 'Telepon / Kontak Wihara',
            '{{email_wihara}}' => 'Email Sekretariat Wihara',
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
     * Default full-page editable HTML template for Bukti Tanda Terima (A4: Header + Body + Signature).
     */
    public static function defaultReceiptTemplateHtml(): string
    {
        return <<<'HTML'
<div class="docx-page-root" style="font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #0f172a; line-height: 1.4; padding: 12mm 15mm; box-sizing: border-box; width: 100%;">
    <!-- HEADER / KOP SURAT (DAPAT DIUBAH LANGSUNG) -->
    <table style="width: 100%; border-collapse: collapse; border-bottom: 3px double #0f172a; margin-bottom: 14px;">
        <tbody>
            <tr>
                <td style="width: 18%; border: none; padding: 0 10px 10px 0; vertical-align: middle; text-align: center;">{{logo}}</td>
                <td style="width: 82%; border: none; padding: 0 0 10px 0; vertical-align: middle; text-align: center;">
                    <p style="margin: 0; font-size: 9pt; font-weight: bold; letter-spacing: 0.14em; text-transform: uppercase; color: #475569;">Majelis Agama Buddha &bull; Sekretariat Pengurus Wihara</p>
                    <p style="margin: 2px 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; color: #0f172a;">{{nama_wihara}}</p>
                    <p style="margin: 0; font-size: 9.5pt; color: #334155;">{{alamat_wihara}}</p>
                    <p style="margin: 2px 0 0 0; font-size: 9pt; color: #475569;">Telp: {{telepon_wihara}} &bull; Email: {{email_wihara}}</p>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- JUDUL SURAT -->
    <div style="text-align: center; border-bottom: 1px solid #cbd5e1; padding-bottom: 8px; margin-bottom: 12px;">
        <p style="font-size: 9.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.12em; color: #64748b; margin: 0;">Bukti Resmi Penerimaan Sekretariat Wihara</p>
        <h3 style="font-size: 14pt; font-weight: bold; text-decoration: underline; margin: 4px 0;">BUKTI TANDA TERIMA {{jenis}}</h3>
        <p style="font-size: 10.5pt; color: #334155; margin: 0;">Nomor Registrasi: <strong>{{nomor}}</strong> &bull; Tanggal: <strong>{{tanggal}}</strong></p>
    </div>

    <!-- ISI SURAT -->
    <p style="margin: 0 0 10px 0;">Sekretariat Pengurus <strong>{{nama_wihara}}</strong> menerangkan telah menerima penyerahan dengan rincian lengkap sebagai berikut:</p>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10.5pt;">
        <tbody>
            <tr>
                <td style="border: 1px solid #94a3b8; padding: 6px 8px; background-color: #f1f5f9; font-weight: bold; width: 34%;">Telah Diterima Dari</td>
                <td style="border: 1px solid #94a3b8; padding: 6px 8px; font-weight: bold;">{{nama_donatur}}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #94a3b8; padding: 6px 8px; background-color: #f1f5f9; font-weight: bold;">Instansi / Alamat Domisili</td>
                <td style="border: 1px solid #94a3b8; padding: 6px 8px;">{{instansi}}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #94a3b8; padding: 6px 8px; background-color: #f1f5f9; font-weight: bold;">Nomor Telepon / Kontak</td>
                <td style="border: 1px solid #94a3b8; padding: 6px 8px;">{{kontak}}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #94a3b8; padding: 6px 8px; background-color: #f1f5f9; font-weight: bold;">Kategori Penerimaan</td>
                <td style="border: 1px solid #94a3b8; padding: 6px 8px;">{{jenis}}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #94a3b8; padding: 6px 8px; background-color: #f1f5f9; font-weight: bold;">Petugas Penerima</td>
                <td style="border: 1px solid #94a3b8; padding: 6px 8px;">{{penerima}}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #94a3b8; padding: 6px 8px; background-color: #f1f5f9; font-weight: bold;">Catatan Sekretariat</td>
                <td style="border: 1px solid #94a3b8; padding: 6px 8px;">{{catatan}}</td>
            </tr>
        </tbody>
    </table>

    <p style="font-weight: bold; margin: 0 0 6px 0;">Rincian Barang / Bantuan yang Diterima:</p>
    <div style="margin-bottom: 12px;">{{daftar_barang}}</div>

    <div style="padding: 8px 12px; border: 2px solid #0f172a; background-color: #f8fafc; margin-bottom: 10px;">
        <strong>Total Nominal Tercatat:</strong> <span style="font-size: 13pt; font-weight: bold;">{{nominal}}</span>
    </div>

    <p style="padding: 8px 12px; border: 1px solid #cbd5e1; background-color: #f8fafc; font-style: italic; margin: 0 0 18px 0;"><strong>Ucapan Anumodana:</strong> "{{ucapan}}"</p>

    <!-- BAGIAN TANDA TANGAN (DAPAT DIUBAH LANGSUNG) -->
    <table style="width: 100%; border-collapse: collapse; border-top: 1px solid #cbd5e1; margin-top: 18px;">
        <tbody>
            <tr>
                <td style="width: 50%; border: none; padding: 14px 10px 0 10px; text-align: center; vertical-align: top;">
                    <p style="margin: 0; font-size: 10pt; color: #475569;">Mengetahui / Menyerahkan,</p>
                    <p style="margin: 2px 0 0 0; font-weight: bold; font-size: 10.5pt;">Pihak Penyerah / Donatur</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{nama_donatur}}</p>
                    <p style="margin: 2px 0 0 0; font-size: 9pt; color: #64748b;">Tanda Tangan &amp; Nama Terang</p>
                </td>
                <td style="width: 50%; border: none; padding: 14px 10px 0 10px; text-align: center; vertical-align: top;">
                    <p style="margin: 0; font-size: 10pt; color: #334155;">Semarang, <strong>{{tanggal}}</strong></p>
                    <p style="margin: 2px 0 0 0; font-weight: bold; font-size: 10.5pt;">Petugas Penerima Sekretariat</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{penerima}}</p>
                    <p style="margin: 2px 0 0 0; font-size: 9pt; color: #64748b;">Cap &amp; Stempel Resmi Sekretariat Wihara</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
HTML;
    }

    /**
     * Default full-page editable HTML template for Surat Ucapan Terima Kasih (A4: Header + Body + Signature).
     */
    public static function defaultThankYouTemplateHtml(): string
    {
        return <<<'HTML'
<div class="docx-page-root" style="font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #0f172a; line-height: 1.45; padding: 12mm 15mm; box-sizing: border-box; width: 100%;">
    <!-- HEADER / KOP SURAT (DAPAT DIUBAH LANGSUNG) -->
    <table style="width: 100%; border-collapse: collapse; border-bottom: 3px double #0f172a; margin-bottom: 14px;">
        <tbody>
            <tr>
                <td style="width: 18%; border: none; padding: 0 10px 10px 0; vertical-align: middle; text-align: center;">{{logo}}</td>
                <td style="width: 82%; border: none; padding: 0 0 10px 0; vertical-align: middle; text-align: center;">
                    <p style="margin: 0; font-size: 9pt; font-weight: bold; letter-spacing: 0.14em; text-transform: uppercase; color: #475569;">Majelis Agama Buddha &bull; Sekretariat Pengurus Wihara</p>
                    <p style="margin: 2px 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; color: #0f172a;">{{nama_wihara}}</p>
                    <p style="margin: 0; font-size: 9.5pt; color: #334155;">{{alamat_wihara}}</p>
                    <p style="margin: 2px 0 0 0; font-size: 9pt; color: #475569;">Telp: {{telepon_wihara}} &bull; Email: {{email_wihara}}</p>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- METADATA SURAT -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10.5pt;">
        <tbody>
            <tr>
                <td style="width: 15%; border: none; font-weight: bold; padding: 2px 0;">Nomor</td>
                <td style="width: 3%; border: none; padding: 2px 0;">:</td>
                <td style="width: 52%; border: none; font-weight: bold; padding: 2px 0;">{{nomor}}</td>
                <td style="width: 30%; border: none; text-align: right; padding: 2px 0;">Semarang, <strong>{{tanggal}}</strong></td>
            </tr>
            <tr>
                <td style="border: none; font-weight: bold; padding: 2px 0;">Lampiran</td>
                <td style="border: none; padding: 2px 0;">:</td>
                <td colspan="2" style="border: none; padding: 2px 0;">1 (Satu) Lembar Salinan Tanda Terima</td>
            </tr>
            <tr>
                <td style="border: none; font-weight: bold; padding: 2px 0;">Perihal</td>
                <td style="border: none; padding: 2px 0;">:</td>
                <td colspan="2" style="border: none; font-weight: bold; text-decoration: underline; padding: 2px 0;">Surat Ucapan Terima Kasih (Anumodana)</td>
            </tr>
        </tbody>
    </table>

    <div style="padding: 8px 12px; border: 1px solid #cbd5e1; background-color: #f8fafc; margin-bottom: 12px; width: 68%;">
        <p style="margin: 0; font-size: 9.5pt; color: #64748b;">Kepada Yth.</p>
        <p style="margin: 2px 0; font-weight: bold; font-size: 11.5pt;">Bapak / Ibu / Dermawan {{nama_donatur}}</p>
        <p style="margin: 0; font-size: 10.5pt;">{{instansi}}</p>
    </div>

    <p style="font-weight: bold; font-style: italic; margin: 0 0 8px 0;">Namo Sanghyang Adi Buddhaya, Namo Buddhaya.</p>

    <p style="text-align: justify; margin: 0 0 10px 0;">Semoga pancaran cinta kasih (Metta) dan berkah Tiratana senantiasa melimpah kepada Bapak/Ibu/Saudara/i beserta seluruh keluarga. Melalui surat resmi ini, Pengurus <strong>{{nama_wihara}}</strong> menyampaikan apresiasi serta ucapan terima kasih yang sebesar-besarnya (Anumodana) atas partisipasi kebajikan ({{jenis}}) berupa:</p>

    <div style="margin-bottom: 10px;">{{daftar_barang}}</div>

    <p style="margin: 0 0 10px 0;"><strong>Total Nominal Tercatat:</strong> {{nominal}} &bull; <strong>Diterima Oleh:</strong> {{penerima}} ({{tanggal}})</p>

    <blockquote style="padding: 8px 12px; border-left: 4px solid #0f172a; background-color: #f8fafc; margin: 0 0 10px 0; text-align: justify;">{{ucapan}}</blockquote>

    <p style="text-align: justify; margin: 0 0 8px 0;">Demikian Surat Ucapan Terima Kasih ini kami sampaikan sebagai bukti pencatatan resmi Sekretariat {{nama_wihara}}.</p>
    <p style="font-weight: bold; font-style: italic; margin: 0 0 18px 0;">Sabbe Satta Bhavantu Sukhitatta. Sadhu, Sadhu, Sadhu.</p>

    <!-- BAGIAN TANDA TANGAN (DAPAT DIUBAH LANGSUNG) -->
    <table style="width: 100%; border-collapse: collapse; border-top: 1px solid #cbd5e1; margin-top: 18px;">
        <tbody>
            <tr>
                <td style="width: 50%; border: none; padding: 14px 10px 0 10px; text-align: center; vertical-align: top;">
                    <p style="margin: 0; font-size: 10pt; color: #475569;">Mengetahui,</p>
                    <p style="margin: 2px 0 0 0; font-weight: bold; font-size: 10.5pt;">Ketua / Pengurus Wihara</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">( ........................................ )</p>
                    <p style="margin: 2px 0 0 0; font-size: 9pt; color: #64748b;">Tanda Tangan &amp; Nama Terang</p>
                </td>
                <td style="width: 50%; border: none; padding: 14px 10px 0 10px; text-align: center; vertical-align: top;">
                    <p style="margin: 0; font-size: 10pt; color: #334155;">Semarang, <strong>{{tanggal}}</strong></p>
                    <p style="margin: 2px 0 0 0; font-weight: bold; font-size: 10.5pt;">Sekretariat / Penerima</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{penerima}}</p>
                    <p style="margin: 2px 0 0 0; font-size: 9pt; color: #64748b;">Cap &amp; Stempel Resmi Sekretariat Wihara</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
HTML;
    }

    /**
     * Load XML safely suppressing libxml warnings.
     */
    private function loadXmlSafely(string $xml): ?DOMDocument
    {
        if (trim($xml) === '') {
            return null;
        }

        $dom = new DOMDocument;
        $prev = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        return $loaded ? $dom : null;
    }

    /**
     * Parse relationships XML (rId => Target).
     *
     * @return array<string, array{target: string, type: string}>
     */
    private function parseRelationships(string $xml): array
    {
        $rels = [];
        $dom = $this->loadXmlSafely($xml);
        if (! $dom instanceof DOMDocument) {
            return $rels;
        }

        foreach ($dom->getElementsByTagName('Relationship') as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $id = $node->getAttribute('Id');
            $target = $node->getAttribute('Target');
            $type = $node->getAttribute('Type');
            if ($id !== '' && $target !== '') {
                $rels[$id] = [
                    'target' => $target,
                    'type' => $type,
                ];
            }
        }

        return $rels;
    }

    /**
     * Extract section page margins and header/footer targets from body's sectPr.
     *
     * @param  array<string, array{target: string, type: string}>  $docRels
     * @return array{
     *     margins: array{top: string, right: string, bottom: string, left: string},
     *     headerTarget: ?string,
     *     footerTarget: ?string
     * }
     */
    private function extractSectionInfo(DOMElement $body, array $docRels): array
    {
        $margins = [
            'top' => '15',
            'right' => '18',
            'bottom' => '15',
            'left' => '18',
        ];
        $headerCandidates = [];
        $footerCandidates = [];

        $sectPrNodes = $body->getElementsByTagName('sectPr');
        $sectPr = $sectPrNodes->length > 0 ? $sectPrNodes->item($sectPrNodes->length - 1) : null;

        if ($sectPr instanceof DOMElement) {
            foreach ($sectPr->childNodes as $child) {
                if (! $child instanceof DOMElement) {
                    continue;
                }
                if ($child->localName === 'pgMar') {
                    foreach (['top', 'right', 'bottom', 'left'] as $side) {
                        $twips = $this->attr($child, $side);
                        if ($twips !== '' && is_numeric($twips)) {
                            $mm = round(((float) $twips) / 56.6929, 1);
                            $mm = max(6.0, min(35.0, $mm));
                            $margins[$side] = (string) $mm;
                        }
                    }
                } elseif ($child->localName === 'headerReference') {
                    $type = strtolower($this->attr($child, 'type') ?: 'default');
                    $rId = $this->attr($child, 'id');
                    if ($rId !== '' && isset($docRels[$rId])) {
                        $headerCandidates[$type] = $docRels[$rId]['target'];
                    }
                } elseif ($child->localName === 'footerReference') {
                    $type = strtolower($this->attr($child, 'type') ?: 'default');
                    $rId = $this->attr($child, 'id');
                    if ($rId !== '' && isset($docRels[$rId])) {
                        $footerCandidates[$type] = $docRels[$rId]['target'];
                    }
                }
            }
        }

        $headerTarget = $headerCandidates['first'] ?? $headerCandidates['default'] ?? null;
        $footerTarget = $footerCandidates['first'] ?? $footerCandidates['default'] ?? null;

        // Fallback if header/footer exists in relationships even without explicit sectPr reference
        if ($headerTarget === null) {
            foreach ($docRels as $rel) {
                if (str_contains(strtolower($rel['type']), '/header') || preg_match('/^header\d+\.xml$/i', basename($rel['target']))) {
                    $headerTarget = $rel['target'];
                    break;
                }
            }
        }

        if ($footerTarget === null) {
            foreach ($docRels as $rel) {
                if (str_contains(strtolower($rel['type']), '/footer') || preg_match('/^footer\d+\.xml$/i', basename($rel['target']))) {
                    $footerTarget = $rel['target'];
                    break;
                }
            }
        }

        return [
            'margins' => $margins,
            'headerTarget' => $headerTarget,
            'footerTarget' => $footerTarget,
        ];
    }

    /**
     * Parse word/styles.xml for docDefaults and named paragraph/run/table styles.
     *
     * @return array{
     *     defaultRun: array<string, mixed>,
     *     defaultPara: array<string, mixed>,
     *     styles: array<string, array<string, mixed>>
     * }
     */
    private function parseStylesXml(string $xml): array
    {
        $result = [
            'defaultRun' => [],
            'defaultPara' => [],
            'styles' => [],
        ];

        $dom = $this->loadXmlSafely($xml);
        if (! $dom instanceof DOMDocument) {
            return $result;
        }

        $docDefaultsNodes = $dom->getElementsByTagName('docDefaults');
        if ($docDefaultsNodes->length > 0 && $docDefaultsNodes->item(0) instanceof DOMElement) {
            $dd = $docDefaultsNodes->item(0);
            foreach ($dd->getElementsByTagName('rPr') as $rPr) {
                if ($rPr instanceof DOMElement) {
                    $result['defaultRun'] = array_merge($result['defaultRun'], $this->extractRunProps($rPr));
                }
            }
            foreach ($dd->getElementsByTagName('pPr') as $pPr) {
                if ($pPr instanceof DOMElement) {
                    $result['defaultPara'] = array_merge($result['defaultPara'], $this->extractParaProps($pPr));
                }
            }
        }

        foreach ($dom->getElementsByTagName('style') as $styleEl) {
            if (! $styleEl instanceof DOMElement) {
                continue;
            }
            $styleId = $this->attr($styleEl, 'styleId');
            if ($styleId === '') {
                continue;
            }

            $entry = [
                'type' => strtolower($this->attr($styleEl, 'type')),
                'basedOn' => null,
                'run' => [],
                'para' => [],
                'tblBorders' => [],
            ];

            foreach ($styleEl->childNodes as $child) {
                if (! $child instanceof DOMElement) {
                    continue;
                }
                if ($child->localName === 'basedOn') {
                    $entry['basedOn'] = $this->attr($child, 'val');
                } elseif ($child->localName === 'rPr') {
                    $entry['run'] = $this->extractRunProps($child);
                } elseif ($child->localName === 'pPr') {
                    $entry['para'] = $this->extractParaProps($child);
                } elseif ($child->localName === 'tblPr') {
                    foreach ($child->childNodes as $tblChild) {
                        if ($tblChild instanceof DOMElement && $tblChild->localName === 'tblBorders') {
                            $entry['tblBorders'] = $this->extractBorders($tblChild);
                        }
                    }
                }
            }

            $result['styles'][$styleId] = $entry;
        }

        return $result;
    }

    /**
     * Parse word/numbering.xml to determine bullet vs decimal list formats.
     *
     * @return array<string, array<string, string>>
     */
    private function parseNumberingXml(string $xml): array
    {
        $dom = $this->loadXmlSafely($xml);
        if (! $dom instanceof DOMDocument) {
            return [];
        }

        $abstract = [];
        foreach ($dom->getElementsByTagName('abstractNum') as $absNode) {
            if (! $absNode instanceof DOMElement) {
                continue;
            }
            $absId = $this->attr($absNode, 'abstractNumId');
            if ($absId === '') {
                continue;
            }
            foreach ($absNode->childNodes as $lvl) {
                if (! $lvl instanceof DOMElement || $lvl->localName !== 'lvl') {
                    continue;
                }
                $ilvl = $this->attr($lvl, 'ilvl') ?: '0';
                $fmt = 'bullet';
                foreach ($lvl->childNodes as $lProp) {
                    if ($lProp instanceof DOMElement && $lProp->localName === 'numFmt') {
                        $fmt = strtolower($this->attr($lProp, 'val') ?: 'bullet');
                    }
                }
                $abstract[$absId][$ilvl] = $fmt;
            }
        }

        $map = [];
        foreach ($dom->getElementsByTagName('num') as $numNode) {
            if (! $numNode instanceof DOMElement) {
                continue;
            }
            $numId = $this->attr($numNode, 'numId');
            foreach ($numNode->childNodes as $nChild) {
                if ($nChild instanceof DOMElement && $nChild->localName === 'abstractNumId') {
                    $absId = $this->attr($nChild, 'val');
                    if ($numId !== '' && isset($abstract[$absId])) {
                        $map[$numId] = $abstract[$absId];
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Resolve inherited style properties from styles.xml.
     *
     * @return array{run: array<string, mixed>, para: array<string, mixed>, tblBorders: array<string, mixed>}
     */
    private function resolveStyle(?string $styleId, int $depth = 0): array
    {
        if ($styleId === null || $styleId === '' || $depth > 6 || ! isset($this->stylesMap['styles'][$styleId])) {
            return ['run' => [], 'para' => [], 'tblBorders' => []];
        }

        $style = $this->stylesMap['styles'][$styleId];
        $parent = $this->resolveStyle($style['basedOn'] ?? null, $depth + 1);

        return [
            'run' => array_merge($parent['run'], $style['run'] ?? []),
            'para' => array_merge($parent['para'], $style['para'] ?? []),
            'tblBorders' => array_merge($parent['tblBorders'], $style['tblBorders'] ?? []),
        ];
    }

    /**
     * Render all block children (<w:p>, <w:tbl>, <w:sdt>) inside a container (body, header, footer, table cell, textbox).
     *
     * @param  array<string, array{target: string, type: string}>  $rels
     */
    private function renderBlockContainer(DOMElement $container, ZipArchive $zip, array $rels, bool $insideCell = false): string
    {
        $parts = [];
        $openListTag = null;

        foreach ($container->childNodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            if ($node->localName === 'sdt') {
                // Structured document tag (e.g. content controls in modern Word templates)
                foreach ($node->childNodes as $sdtChild) {
                    if ($sdtChild instanceof DOMElement && $sdtChild->localName === 'sdtContent') {
                        if ($openListTag !== null) {
                            $parts[] = '</'.$openListTag.'>';
                            $openListTag = null;
                        }
                        $parts[] = $this->renderBlockContainer($sdtChild, $zip, $rels, $insideCell);
                    }
                }

                continue;
            }

            if ($node->localName === 'p') {
                $listTag = $this->detectListTag($node);
                if ($listTag !== null) {
                    if ($openListTag !== $listTag) {
                        if ($openListTag !== null) {
                            $parts[] = '</'.$openListTag.'>';
                        }
                        $listStyle = $listTag === 'ol'
                            ? 'margin: 4px 0 8px 0; padding-left: 22px; list-style-type: decimal;'
                            : 'margin: 4px 0 8px 0; padding-left: 22px; list-style-type: disc;';
                        $parts[] = '<'.$listTag.' style="'.$listStyle.'">';
                        $openListTag = $listTag;
                    }
                    $parts[] = $this->renderParagraph($node, $zip, $rels, true, $insideCell);
                } else {
                    if ($openListTag !== null) {
                        $parts[] = '</'.$openListTag.'>';
                        $openListTag = null;
                    }
                    $parts[] = $this->renderParagraph($node, $zip, $rels, false, $insideCell);
                }
            } elseif ($node->localName === 'tbl') {
                if ($openListTag !== null) {
                    $parts[] = '</'.$openListTag.'>';
                    $openListTag = null;
                }
                $tableHtml = $this->renderTable($node, $zip, $rels);
                if ($tableHtml !== '') {
                    $parts[] = $tableHtml;
                }
            }
        }

        if ($openListTag !== null) {
            $parts[] = '</'.$openListTag.'>';
        }

        return implode("\n", array_filter($parts, fn (string $part): bool => $part !== ''));
    }

    /**
     * Detect if a paragraph belongs to a bulleted (ul) or numbered (ol) list.
     */
    private function detectListTag(DOMElement $paragraph): ?string
    {
        foreach ($paragraph->childNodes as $child) {
            if (! $child instanceof DOMElement || $child->localName !== 'pPr') {
                continue;
            }
            foreach ($child->childNodes as $prop) {
                if (! $prop instanceof DOMElement || $prop->localName !== 'numPr') {
                    continue;
                }
                $numId = '';
                $ilvl = '0';
                foreach ($prop->childNodes as $nProp) {
                    if ($nProp instanceof DOMElement && $nProp->localName === 'numId') {
                        $numId = $this->attr($nProp, 'val');
                    } elseif ($nProp instanceof DOMElement && $nProp->localName === 'ilvl') {
                        $ilvl = $this->attr($nProp, 'val') ?: '0';
                    }
                }
                if ($numId === '' || $numId === '0') {
                    return null;
                }
                $fmt = $this->numberingMap[$numId][$ilvl] ?? 'bullet';

                return in_array($fmt, ['bullet', 'none'], true) ? 'ul' : 'ol';
            }
        }

        return null;
    }

    /**
     * Render a Word <w:p> paragraph element to HTML, preserving empty spacing lines,
     * alignment, margins, borders (e.g. Kop Surat double lines), shading, and inline runs/images.
     *
     * @param  array<string, array{target: string, type: string}>  $rels
     */
    private function renderParagraph(DOMElement $paragraph, ZipArchive $zip, array $rels, bool $asListItem = false, bool $insideCell = false): string
    {
        $pPrElement = null;
        foreach ($paragraph->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'pPr') {
                $pPrElement = $child;
                break;
            }
        }

        $directParaProps = $pPrElement instanceof DOMElement ? $this->extractParaProps($pPrElement) : [];
        $styleId = $directParaProps['styleId'] ?? null;
        $inherited = $this->resolveStyle($styleId);

        $paraProps = array_merge($this->stylesMap['defaultPara'], $inherited['para'], $directParaProps);
        $baseRunProps = array_merge($this->stylesMap['defaultRun'], $inherited['run']);

        // Also check paragraph mark rPr inside pPr
        if ($pPrElement instanceof DOMElement) {
            foreach ($pPrElement->childNodes as $pChild) {
                if ($pChild instanceof DOMElement && $pChild->localName === 'rPr') {
                    $baseRunProps = array_merge($baseRunProps, $this->extractRunProps($pChild));
                }
            }
        }

        $innerHtml = $this->renderInlineChildren($paragraph, $zip, $rels, $baseRunProps);
        $hasMedia = str_contains($innerHtml, '<img') || str_contains($innerHtml, '<div') || str_contains($innerHtml, '<table');
        $isVisuallyEmpty = ! $hasMedia && trim(str_replace('&nbsp;', '', strip_tags($innerHtml))) === '';

        // Preserve empty paragraphs (used for vertical spacing & signature gaps in Word)
        if ($isVisuallyEmpty) {
            $innerHtml = '&nbsp;';
        }

        $css = [];

        if (! empty($paraProps['align'])) {
            $css[] = 'text-align: '.$paraProps['align'];
        }

        $marginTop = $paraProps['spaceBefore'] ?? '0';
        $marginBottom = $paraProps['spaceAfter'] ?? ($insideCell ? '2pt' : '4pt');
        $css[] = "margin-top: {$marginTop}; margin-bottom: {$marginBottom}";

        if (! empty($paraProps['lineHeight'])) {
            $css[] = 'line-height: '.$paraProps['lineHeight'];
        }
        if (! empty($paraProps['marginLeft'])) {
            $css[] = 'margin-left: '.$paraProps['marginLeft'];
        }
        if (! empty($paraProps['marginRight'])) {
            $css[] = 'margin-right: '.$paraProps['marginRight'];
        }
        if (! empty($paraProps['textIndent'])) {
            $css[] = 'text-indent: '.$paraProps['textIndent'];
        }
        if (! empty($paraProps['background'])) {
            $css[] = 'background-color: '.$paraProps['background'];
            $css[] = 'padding: 3px 6px';
        }

        // Paragraph borders (very commonly used for Kop Surat bottom double lines!)
        if (! empty($paraProps['borders']) && is_array($paraProps['borders'])) {
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                if (! empty($paraProps['borders'][$side])) {
                    $css[] = "border-{$side}: ".$paraProps['borders'][$side];
                    if ($side === 'bottom') {
                        $css[] = 'padding-bottom: 4px';
                    } elseif ($side === 'top') {
                        $css[] = 'padding-top: 4px';
                    }
                }
            }
        }

        // Apply paragraph-level font size if empty line so blank signature lines keep accurate height
        if ($isVisuallyEmpty && ! empty($baseRunProps['fontSize'])) {
            $css[] = 'font-size: '.$baseRunProps['fontSize'];
        }

        $tag = $asListItem ? 'li' : 'p';
        $styleAttr = $css !== [] ? ' style="'.$this->escapeAttr(implode('; ', $css).';').'"' : '';

        return "<{$tag}{$styleAttr}>{$innerHtml}</{$tag}>";
    }

    /**
     * Render inline runs (<w:r>), hyperlinks, drawings/images, VML shapes, and textboxes inside a node.
     *
     * @param  array<string, array{target: string, type: string}>  $rels
     * @param  array<string, mixed>  $baseRunProps
     */
    private function renderInlineChildren(DOMNode $parent, ZipArchive $zip, array $rels, array $baseRunProps = []): string
    {
        $result = '';

        foreach ($parent->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            if ($child->localName === 'AlternateContent') {
                // Only render mc:Choice (skip mc:Fallback to prevent duplicated shapes/textboxes!)
                $choiceRendered = false;
                foreach ($child->childNodes as $acChild) {
                    if ($acChild instanceof DOMElement && $acChild->localName === 'Choice') {
                        $result .= $this->renderInlineChildren($acChild, $zip, $rels, $baseRunProps);
                        $choiceRendered = true;
                        break;
                    }
                }
                if (! $choiceRendered) {
                    foreach ($child->childNodes as $acChild) {
                        if ($acChild instanceof DOMElement && $acChild->localName === 'Fallback') {
                            $result .= $this->renderInlineChildren($acChild, $zip, $rels, $baseRunProps);
                            break;
                        }
                    }
                }

                continue;
            }

            if ($child->localName === 'r') {
                $result .= $this->renderRun($child, $zip, $rels, $baseRunProps);
            } elseif (in_array($child->localName, ['hyperlink', 'ins', 'smartTag', 'sdtContent'], true)) {
                $result .= $this->renderInlineChildren($child, $zip, $rels, $baseRunProps);
            } elseif ($child->localName === 'sdt') {
                foreach ($child->childNodes as $sdtChild) {
                    if ($sdtChild instanceof DOMElement && $sdtChild->localName === 'sdtContent') {
                        $result .= $this->renderInlineChildren($sdtChild, $zip, $rels, $baseRunProps);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Render a single <w:r> run element with all typography, embedded images, and textboxes.
     *
     * @param  array<string, array{target: string, type: string}>  $rels
     * @param  array<string, mixed>  $baseRunProps
     */
    private function renderRun(DOMElement $run, ZipArchive $zip, array $rels, array $baseRunProps): string
    {
        $directRunProps = [];
        $runContent = '';

        foreach ($run->childNodes as $part) {
            if (! $part instanceof DOMElement) {
                continue;
            }

            if ($part->localName === 'rPr') {
                $directRunProps = $this->extractRunProps($part);
            } elseif ($part->localName === 't' || $part->localName === 'instrText') {
                if ($part->localName === 't') {
                    $runContent .= htmlspecialchars($part->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
            } elseif ($part->localName === 'br') {
                $runContent .= '<br>';
            } elseif ($part->localName === 'cr') {
                $runContent .= '<br>';
            } elseif ($part->localName === 'tab') {
                $runContent .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            } elseif ($part->localName === 'noBreakHyphen') {
                $runContent .= '&#8209;';
            } elseif ($part->localName === 'drawing') {
                $runContent .= $this->renderDrawing($part, $zip, $rels);
            } elseif ($part->localName === 'pict') {
                $runContent .= $this->renderLegacyPict($part, $zip, $rels);
            } elseif ($part->localName === 'AlternateContent') {
                foreach ($part->childNodes as $acChild) {
                    if ($acChild instanceof DOMElement && $acChild->localName === 'Choice') {
                        $runContent .= $this->renderRun($acChild, $zip, $rels, $baseRunProps);
                        break;
                    }
                }
            }
        }

        if ($runContent === '') {
            return '';
        }

        // Merge style inheritance: base -> rStyle -> direct
        $rStyleProps = [];
        if (! empty($directRunProps['styleId'])) {
            $rStyleProps = $this->resolveStyle($directRunProps['styleId'])['run'];
        }
        $props = array_merge($baseRunProps, $rStyleProps, $directRunProps);

        $isOnlyHtmlBlocks = str_contains($runContent, '<img') || str_contains($runContent, '<div') || str_contains($runContent, '<table');
        if ($isOnlyHtmlBlocks && trim(strip_tags($runContent)) === '') {
            return $runContent;
        }

        $spanCss = [];
        if (! empty($props['fontFamily'])) {
            $spanCss[] = 'font-family: '.$props['fontFamily'];
        }
        if (! empty($props['fontSize'])) {
            $spanCss[] = 'font-size: '.$props['fontSize'];
        }
        if (! empty($props['color'])) {
            $spanCss[] = 'color: '.$props['color'];
        }
        if (! empty($props['background'])) {
            $spanCss[] = 'background-color: '.$props['background'];
        }
        if (! empty($props['caps'])) {
            $spanCss[] = 'text-transform: uppercase';
        }
        if (! empty($props['smallCaps'])) {
            $spanCss[] = 'font-variant: small-caps';
        }
        if (! empty($props['strike'])) {
            $spanCss[] = 'text-decoration: line-through';
        }

        $html = $runContent;

        if (! empty($props['underline'])) {
            $html = '<u>'.$html.'</u>';
        }
        if (! empty($props['italic'])) {
            $html = '<em>'.$html.'</em>';
        }
        if (! empty($props['bold'])) {
            $html = '<strong>'.$html.'</strong>';
        }
        if (($props['vertAlign'] ?? '') === 'superscript') {
            $html = '<sup>'.$html.'</sup>';
        } elseif (($props['vertAlign'] ?? '') === 'subscript') {
            $html = '<sub>'.$html.'</sub>';
        }

        if ($spanCss !== []) {
            $html = '<span style="'.$this->escapeAttr(implode('; ', $spanCss).';').'">'.$html.'</span>';
        }

        return $html;
    }

    /**
     * Render modern Word <w:drawing> (embedded image or Word text box).
     *
     * @param  array<string, array{target: string, type: string}>  $rels
     */
    private function renderDrawing(DOMElement $drawing, ZipArchive $zip, array $rels): string
    {
        $output = '';

        // 1. Check for embedded image (<a:blip r:embed="rId...">)
        $blips = $drawing->getElementsByTagName('blip');
        if ($blips->length > 0) {
            $blip = $blips->item(0);
            if ($blip instanceof DOMElement) {
                $rId = $this->attr($blip, 'embed') ?: $this->attr($blip, 'link');
                if ($rId !== '' && isset($rels[$rId])) {
                    $dataUri = $this->extractImageDataUri($zip, $rels[$rId]['target']);
                    if ($dataUri !== null) {
                        $widthPx = null;
                        $heightPx = null;
                        $extents = $drawing->getElementsByTagName('extent');
                        if ($extents->length > 0 && $extents->item(0) instanceof DOMElement) {
                            $cx = $extents->item(0)->getAttribute('cx');
                            $cy = $extents->item(0)->getAttribute('cy');
                            if (is_numeric($cx) && (float) $cx > 0) {
                                $widthPx = max(16, min(680, (int) round(((float) $cx) / 9525)));
                            }
                            if (is_numeric($cy) && (float) $cy > 0) {
                                $heightPx = max(16, min(900, (int) round(((float) $cy) / 9525)));
                            }
                        }

                        $imgCss = ['display: inline-block', 'max-width: 100%', 'height: auto', 'vertical-align: middle'];
                        if ($widthPx !== null) {
                            $imgCss[] = "width: {$widthPx}px";
                        }
                        if ($heightPx !== null && $widthPx === null) {
                            $imgCss[] = "height: {$heightPx}px";
                        }

                        // Check anchor alignment (floating left/right logo next to letterhead text)
                        $anchors = $drawing->getElementsByTagName('anchor');
                        if ($anchors->length > 0 && $anchors->item(0) instanceof DOMElement) {
                            $anchor = $anchors->item(0);
                            $wrapNone = $anchor->getElementsByTagName('wrapNone')->length > 0;
                            $posH = $anchor->getElementsByTagName('positionH');
                            if (! $wrapNone && $posH->length > 0 && $posH->item(0) instanceof DOMElement) {
                                $alignNodes = $posH->item(0)->getElementsByTagName('align');
                                if ($alignNodes->length > 0) {
                                    $alignVal = strtolower(trim($alignNodes->item(0)?->textContent ?? ''));
                                    if ($alignVal === 'left') {
                                        $imgCss[] = 'float: left; margin-right: 12px; margin-bottom: 6px';
                                    } elseif ($alignVal === 'right') {
                                        $imgCss[] = 'float: right; margin-left: 12px; margin-bottom: 6px';
                                    }
                                }
                            }
                        }

                        $output .= '<img src="'.$dataUri.'" alt="Gambar Dokumen" style="'.$this->escapeAttr(implode('; ', $imgCss).';').'">';
                    }
                }
            }
        }

        // 2. Check for text box content (<w:txbxContent>)
        $txbxNodes = $drawing->getElementsByTagName('txbxContent');
        if ($txbxNodes->length > 0 && $txbxNodes->item(0) instanceof DOMElement) {
            $boxHtml = $this->renderBlockContainer($txbxNodes->item(0), $zip, $rels, true);
            if (trim($boxHtml) !== '') {
                $output .= '<div style="margin: 4px 0;">'.$boxHtml.'</div>';
            }
        }

        return $output;
    }

    /**
     * Render legacy VML <w:pict> elements (images & legacy text boxes).
     *
     * @param  array<string, array{target: string, type: string}>  $rels
     */
    private function renderLegacyPict(DOMElement $pict, ZipArchive $zip, array $rels): string
    {
        $output = '';

        $imageDataNodes = $pict->getElementsByTagName('imagedata');
        if ($imageDataNodes->length > 0 && $imageDataNodes->item(0) instanceof DOMElement) {
            $imgNode = $imageDataNodes->item(0);
            $rId = $this->attr($imgNode, 'id') ?: $this->attr($imgNode, 'relid');
            if ($rId !== '' && isset($rels[$rId])) {
                $dataUri = $this->extractImageDataUri($zip, $rels[$rId]['target']);
                if ($dataUri !== null) {
                    $styleStr = '';
                    $shapes = $pict->getElementsByTagName('shape');
                    if ($shapes->length > 0 && $shapes->item(0) instanceof DOMElement) {
                        $rawStyle = $shapes->item(0)->getAttribute('style');
                        if (preg_match('/width\s*:\s*([0-9.]+(?:pt|px|in|cm|mm))/i', $rawStyle, $wm)) {
                            $styleStr .= 'width: '.$wm[1].'; ';
                        }
                    }
                    $output .= '<img src="'.$dataUri.'" alt="Gambar Dokumen" style="display: inline-block; max-width: 100%; height: auto; vertical-align: middle; '.$this->escapeAttr($styleStr).'">';
                }
            }
        }

        $txbxNodes = $pict->getElementsByTagName('txbxContent');
        if ($txbxNodes->length > 0 && $txbxNodes->item(0) instanceof DOMElement) {
            $boxHtml = $this->renderBlockContainer($txbxNodes->item(0), $zip, $rels, true);
            if (trim($boxHtml) !== '') {
                $output .= '<div style="margin: 4px 0;">'.$boxHtml.'</div>';
            }
        }

        return $output;
    }

    /**
     * Extract an embedded image from word/media/* into a safe inline base64 data URI.
     */
    private function extractImageDataUri(ZipArchive $zip, string $target): ?string
    {
        $zipPath = $this->normalizeWordTarget($target);
        $bytes = $zip->getFromName($zipPath);
        if (! is_string($bytes) || $bytes === '' || strlen($bytes) > 4 * 1024 * 1024) {
            return null;
        }

        $ext = strtolower(pathinfo($zipPath, PATHINFO_EXTENSION));
        $mimeMap = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
        ];

        $mime = $mimeMap[$ext] ?? null;
        if ($mime === null) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($bytes);
    }

    /**
     * Render a Word <w:tbl> table to HTML preserving exact border styles (including borderless Kop/Signature tables),
     * column widths, colspan (gridSpan), rowspan (vMerge), cell shading, vertical alignment, and inner paragraphs/images.
     *
     * @param  array<string, array{target: string, type: string}>  $rels
     */
    private function renderTable(DOMElement $table, ZipArchive $zip, array $rels): string
    {
        $tblStyleId = null;
        $tblBorders = [];
        $tblAlign = '';
        $tblWidthCss = 'width: 100%';
        $defaultCellPad = '4px 6px';
        $gridCols = [];

        foreach ($table->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }
            if ($child->localName === 'tblPr') {
                foreach ($child->childNodes as $prop) {
                    if (! $prop instanceof DOMElement) {
                        continue;
                    }
                    if ($prop->localName === 'tblStyle') {
                        $tblStyleId = $this->attr($prop, 'val');
                    } elseif ($prop->localName === 'tblBorders') {
                        $tblBorders = $this->extractBorders($prop);
                    } elseif ($prop->localName === 'jc') {
                        $jc = strtolower($this->attr($prop, 'val'));
                        if ($jc === 'center') {
                            $tblAlign = 'margin-left: auto; margin-right: auto';
                        } elseif ($jc === 'right') {
                            $tblAlign = 'margin-left: auto; margin-right: 0';
                        }
                    } elseif ($prop->localName === 'tblW') {
                        $w = $this->attr($prop, 'w');
                        $type = strtolower($this->attr($prop, 'type'));
                        if ($type === 'pct' && is_numeric($w) && (float) $w > 0) {
                            $pct = min(100, round(((float) $w) / 50, 1));
                            $tblWidthCss = "width: {$pct}%";
                        }
                    } elseif ($prop->localName === 'tblCellMar') {
                        $defaultCellPad = $this->extractCellPadding($prop, $defaultCellPad);
                    }
                }
            } elseif ($child->localName === 'tblGrid') {
                foreach ($child->childNodes as $gc) {
                    if ($gc instanceof DOMElement && $gc->localName === 'gridCol') {
                        $colW = $this->attr($gc, 'w');
                        if (is_numeric($colW) && (float) $colW > 0) {
                            $gridCols[] = (float) $colW;
                        }
                    }
                }
            }
        }

        $inheritedBorders = $this->resolveStyle($tblStyleId)['tblBorders'];
        // If table references TableGrid (Word's default bordered table style) and styles.xml wasn't included, give default 1px grid
        if ($inheritedBorders === [] && $tblBorders === [] && $tblStyleId !== null && stripos($tblStyleId, 'grid') !== false) {
            $inheritedBorders = [
                'top' => '1px solid #000000',
                'bottom' => '1px solid #000000',
                'left' => '1px solid #000000',
                'right' => '1px solid #000000',
                'insideH' => '1px solid #000000',
                'insideV' => '1px solid #000000',
            ];
        }
        $effectiveTblBorders = array_merge($inheritedBorders, $tblBorders);

        $totalGridWidth = array_sum($gridCols);

        // First pass: collect rows & cells metadata to accurately resolve vMerge (rowspan)
        $rowElements = [];
        foreach ($table->childNodes as $tr) {
            if ($tr instanceof DOMElement && $tr->localName === 'tr') {
                $rowElements[] = $tr;
            }
        }

        if ($rowElements === []) {
            return '';
        }

        $matrix = [];
        $rowCount = count($rowElements);

        foreach ($rowElements as $rIdx => $tr) {
            $colCursor = 0;
            $rowCells = [];

            foreach ($tr->childNodes as $tc) {
                if (! $tc instanceof DOMElement || $tc->localName !== 'tc') {
                    continue;
                }

                $colspan = 1;
                $vMerge = null; // null | 'restart' | 'continue'
                $cellBorders = [];
                $cellShading = null;
                $vAlign = 'top';
                $cellWidthCss = '';
                $cellPad = $defaultCellPad;

                foreach ($tc->childNodes as $tcChild) {
                    if (! $tcChild instanceof DOMElement || $tcChild->localName !== 'tcPr') {
                        continue;
                    }
                    foreach ($tcChild->childNodes as $cProp) {
                        if (! $cProp instanceof DOMElement) {
                            continue;
                        }
                        if ($cProp->localName === 'gridSpan') {
                            $span = (int) $this->attr($cProp, 'val');
                            if ($span > 1) {
                                $colspan = $span;
                            }
                        } elseif ($cProp->localName === 'vMerge') {
                            $val = strtolower($this->attr($cProp, 'val'));
                            $vMerge = $val === 'restart' ? 'restart' : 'continue';
                        } elseif ($cProp->localName === 'tcBorders') {
                            $cellBorders = $this->extractBorders($cProp);
                        } elseif ($cProp->localName === 'shd') {
                            $fill = $this->normalizeHexColor($this->attr($cProp, 'fill'));
                            if ($fill !== null) {
                                $cellShading = $fill;
                            }
                        } elseif ($cProp->localName === 'vAlign') {
                            $va = strtolower($this->attr($cProp, 'val'));
                            if ($va === 'center') {
                                $vAlign = 'middle';
                            } elseif (in_array($va, ['top', 'bottom'], true)) {
                                $vAlign = $va;
                            }
                        } elseif ($cProp->localName === 'tcW') {
                            $cw = $this->attr($cProp, 'w');
                            $ct = strtolower($this->attr($cProp, 'type'));
                            if ($ct === 'pct' && is_numeric($cw) && (float) $cw > 0) {
                                $cellWidthCss = 'width: '.round(((float) $cw) / 50, 1).'%';
                            } elseif ($ct === 'dxa' && is_numeric($cw) && (float) $cw > 0 && $totalGridWidth > 0) {
                                $cellWidthCss = 'width: '.round((((float) $cw) / $totalGridWidth) * 100, 1).'%';
                            }
                        } elseif ($cProp->localName === 'tcMar') {
                            $cellPad = $this->extractCellPadding($cProp, $cellPad);
                        }
                    }
                }

                if ($cellWidthCss === '' && $totalGridWidth > 0) {
                    $spanWidth = 0.0;
                    for ($c = $colCursor; $c < $colCursor + $colspan; $c++) {
                        $spanWidth += $gridCols[$c] ?? 0.0;
                    }
                    if ($spanWidth > 0) {
                        $cellWidthCss = 'width: '.round(($spanWidth / $totalGridWidth) * 100, 1).'%';
                    }
                }

                $rowCells[] = [
                    'element' => $tc,
                    'colStart' => $colCursor,
                    'colspan' => $colspan,
                    'rowspan' => 1,
                    'vMerge' => $vMerge,
                    'skip' => false,
                    'borders' => $cellBorders,
                    'shading' => $cellShading,
                    'vAlign' => $vAlign,
                    'widthCss' => $cellWidthCss,
                    'padding' => $cellPad,
                ];

                $colCursor += $colspan;
            }

            $matrix[$rIdx] = $rowCells;
        }

        // Resolve vertical merges (rowspan)
        for ($rIdx = 0; $rIdx < $rowCount; $rIdx++) {
            foreach ($matrix[$rIdx] as $cIdx => &$cell) {
                if ($cell['vMerge'] === 'continue') {
                    // Find owning cell above in the same colStart
                    for ($prevR = $rIdx - 1; $prevR >= 0; $prevR--) {
                        foreach ($matrix[$prevR] as &$prevCell) {
                            if ($prevCell['colStart'] === $cell['colStart'] && ! $prevCell['skip']) {
                                $prevCell['rowspan']++;
                                $cell['skip'] = true;
                                break 2;
                            }
                        }
                        unset($prevCell);
                    }
                }
            }
            unset($cell);
        }

        $rowsHtml = [];
        foreach ($matrix as $rIdx => $rowCells) {
            $cellsHtml = [];
            $cellCount = count($rowCells);

            foreach ($rowCells as $cIdx => $cell) {
                if ($cell['skip']) {
                    continue;
                }

                $isFirstRow = $rIdx === 0;
                $isLastRow = ($rIdx + $cell['rowspan']) >= $rowCount;
                $isFirstCol = $cIdx === 0;
                $isLastCol = $cIdx === $cellCount - 1;

                // Determine each border side (cell override > table outer/inner borders > none!)
                $sideDefaults = [
                    'top' => $isFirstRow ? ($effectiveTblBorders['top'] ?? 'none') : ($effectiveTblBorders['insideH'] ?? 'none'),
                    'bottom' => $isLastRow ? ($effectiveTblBorders['bottom'] ?? 'none') : ($effectiveTblBorders['insideH'] ?? 'none'),
                    'left' => $isFirstCol ? ($effectiveTblBorders['left'] ?? 'none') : ($effectiveTblBorders['insideV'] ?? 'none'),
                    'right' => $isLastCol ? ($effectiveTblBorders['right'] ?? 'none') : ($effectiveTblBorders['insideV'] ?? 'none'),
                ];

                $tdCss = [];
                foreach (['top', 'right', 'bottom', 'left'] as $side) {
                    $bVal = $cell['borders'][$side] ?? $sideDefaults[$side];
                    $tdCss[] = "border-{$side}: ".($bVal !== '' ? $bVal : 'none');
                }

                $tdCss[] = 'padding: '.$cell['padding'];
                $tdCss[] = 'vertical-align: '.$cell['vAlign'];

                if ($cell['widthCss'] !== '') {
                    $tdCss[] = $cell['widthCss'];
                }
                if ($cell['shading'] !== null) {
                    $tdCss[] = 'background-color: '.$cell['shading'];
                }

                $attrs = '';
                if ($cell['colspan'] > 1) {
                    $attrs .= ' colspan="'.$cell['colspan'].'"';
                }
                if ($cell['rowspan'] > 1) {
                    $attrs .= ' rowspan="'.$cell['rowspan'].'"';
                }

                $innerHtml = $this->renderBlockContainer($cell['element'], $zip, $rels, true);
                if (trim($innerHtml) === '') {
                    $innerHtml = '&nbsp;';
                }

                $cellsHtml[] = '<td'.$attrs.' style="'.$this->escapeAttr(implode('; ', $tdCss).';').'">'.$innerHtml.'</td>';
            }

            if ($cellsHtml !== []) {
                $rowsHtml[] = '<tr>'.implode('', $cellsHtml).'</tr>';
            }
        }

        if ($rowsHtml === []) {
            return '';
        }

        $tableCss = [$tblWidthCss, 'border-collapse: collapse', 'margin-bottom: 8px'];
        if ($tblAlign !== '') {
            $tableCss[] = $tblAlign;
        }

        return '<table style="'.$this->escapeAttr(implode('; ', $tableCss).';').'"><tbody>'.implode('', $rowsHtml).'</tbody></table>';
    }

    /**
     * Extract paragraph properties from <w:pPr>.
     *
     * @return array<string, mixed>
     */
    private function extractParaProps(DOMElement $pPr): array
    {
        $props = [];

        foreach ($pPr->childNodes as $prop) {
            if (! $prop instanceof DOMElement) {
                continue;
            }

            if ($prop->localName === 'pStyle') {
                $props['styleId'] = $this->attr($prop, 'val');
            } elseif ($prop->localName === 'jc') {
                $val = strtolower($this->attr($prop, 'val'));
                if (in_array($val, ['center', 'right', 'left'], true)) {
                    $props['align'] = $val;
                } elseif (in_array($val, ['both', 'distribute'], true)) {
                    $props['align'] = 'justify';
                }
            } elseif ($prop->localName === 'spacing') {
                $before = $this->attr($prop, 'before');
                $after = $this->attr($prop, 'after');
                $line = $this->attr($prop, 'line');
                $lineRule = strtolower($this->attr($prop, 'lineRule'));

                if ($before !== '' && is_numeric($before)) {
                    $props['spaceBefore'] = round(((float) $before) / 20, 1).'pt';
                }
                if ($after !== '' && is_numeric($after)) {
                    $props['spaceAfter'] = round(((float) $after) / 20, 1).'pt';
                }
                if ($line !== '' && is_numeric($line) && (float) $line > 0) {
                    if ($lineRule === 'exact' || $lineRule === 'atleast') {
                        $props['lineHeight'] = round(((float) $line) / 20, 1).'pt';
                    } else {
                        $ratio = round(((float) $line) / 240, 2);
                        if ($ratio >= 0.9 && $ratio <= 3.0) {
                            $props['lineHeight'] = (string) $ratio;
                        }
                    }
                }
            } elseif ($prop->localName === 'ind') {
                $left = $this->attr($prop, 'left') ?: $this->attr($prop, 'start');
                $right = $this->attr($prop, 'right') ?: $this->attr($prop, 'end');
                $firstLine = $this->attr($prop, 'firstLine');
                $hanging = $this->attr($prop, 'hanging');

                if ($left !== '' && is_numeric($left) && (float) $left > 0) {
                    $props['marginLeft'] = round(((float) $left) / 20, 1).'pt';
                }
                if ($right !== '' && is_numeric($right) && (float) $right > 0) {
                    $props['marginRight'] = round(((float) $right) / 20, 1).'pt';
                }
                if ($firstLine !== '' && is_numeric($firstLine) && (float) $firstLine > 0) {
                    $props['textIndent'] = round(((float) $firstLine) / 20, 1).'pt';
                } elseif ($hanging !== '' && is_numeric($hanging) && (float) $hanging > 0) {
                    $props['textIndent'] = '-'.round(((float) $hanging) / 20, 1).'pt';
                }
            } elseif ($prop->localName === 'pBdr') {
                $props['borders'] = $this->extractBorders($prop);
            } elseif ($prop->localName === 'shd') {
                $fill = $this->normalizeHexColor($this->attr($prop, 'fill'));
                if ($fill !== null) {
                    $props['background'] = $fill;
                }
            }
        }

        return $props;
    }

    /**
     * Extract run typography properties from <w:rPr>.
     *
     * @return array<string, mixed>
     */
    private function extractRunProps(DOMElement $rPr): array
    {
        $props = [];

        foreach ($rPr->childNodes as $rProp) {
            if (! $rProp instanceof DOMElement) {
                continue;
            }

            if ($rProp->localName === 'rStyle') {
                $props['styleId'] = $this->attr($rProp, 'val');
            } elseif ($rProp->localName === 'b') {
                $val = strtolower($this->attr($rProp, 'val'));
                $props['bold'] = $val !== '0' && $val !== 'false';
            } elseif ($rProp->localName === 'i') {
                $val = strtolower($this->attr($rProp, 'val'));
                $props['italic'] = $val !== '0' && $val !== 'false';
            } elseif ($rProp->localName === 'u') {
                $val = strtolower($this->attr($rProp, 'val'));
                $props['underline'] = $val !== 'none' && $val !== '0' && $val !== 'false';
            } elseif ($rProp->localName === 'strike' || $rProp->localName === 'dstrike') {
                $val = strtolower($this->attr($rProp, 'val'));
                $props['strike'] = $val !== '0' && $val !== 'false';
            } elseif ($rProp->localName === 'caps') {
                $val = strtolower($this->attr($rProp, 'val'));
                $props['caps'] = $val !== '0' && $val !== 'false';
            } elseif ($rProp->localName === 'smallCaps') {
                $val = strtolower($this->attr($rProp, 'val'));
                $props['smallCaps'] = $val !== '0' && $val !== 'false';
            } elseif ($rProp->localName === 'sz') {
                $val = $this->attr($rProp, 'val');
                if ($val !== '' && is_numeric($val) && (float) $val > 0) {
                    $props['fontSize'] = round(((float) $val) / 2, 1).'pt';
                }
            } elseif ($rProp->localName === 'rFonts') {
                $font = $this->attr($rProp, 'ascii') ?: $this->attr($rProp, 'hAnsi') ?: $this->attr($rProp, 'cs');
                if ($font !== '' && preg_match('/^[A-Za-z0-9\s\-]+$/', $font)) {
                    $props['fontFamily'] = "'{$font}', serif";
                }
            } elseif ($rProp->localName === 'color') {
                $hex = $this->normalizeHexColor($this->attr($rProp, 'val'));
                if ($hex !== null) {
                    $props['color'] = $hex;
                }
            } elseif ($rProp->localName === 'shd') {
                $fill = $this->normalizeHexColor($this->attr($rProp, 'fill'));
                if ($fill !== null) {
                    $props['background'] = $fill;
                }
            } elseif ($rProp->localName === 'highlight') {
                $hl = $this->mapHighlightColor($this->attr($rProp, 'val'));
                if ($hl !== null) {
                    $props['background'] = $hl;
                }
            } elseif ($rProp->localName === 'vertAlign') {
                $va = strtolower($this->attr($rProp, 'val'));
                if (in_array($va, ['superscript', 'subscript'], true)) {
                    $props['vertAlign'] = $va;
                }
            }
        }

        return $props;
    }

    /**
     * Extract CSS border definitions from <w:tblBorders>, <w:tcBorders>, or <w:pBdr>.
     *
     * @return array<string, string>
     */
    private function extractBorders(DOMElement $bordersEl): array
    {
        $result = [];

        foreach ($bordersEl->childNodes as $sideEl) {
            if (! $sideEl instanceof DOMElement) {
                continue;
            }
            $side = $sideEl->localName;
            if (! in_array($side, ['top', 'bottom', 'left', 'right', 'start', 'end', 'insideH', 'insideV'], true)) {
                continue;
            }
            if ($side === 'start') {
                $side = 'left';
            } elseif ($side === 'end') {
                $side = 'right';
            }

            $val = strtolower($this->attr($sideEl, 'val'));
            if ($val === '' || $val === 'none' || $val === 'nil') {
                $result[$side] = 'none';

                continue;
            }

            $sz = $this->attr($sideEl, 'sz');
            // Word sz is in eighths of a point (e.g. 4 = 0.5pt, 8 = 1pt, 12 = 1.5pt)
            $px = is_numeric($sz) && (float) $sz > 0 ? max(1.0, round(((float) $sz) / 6, 1)) : 1.0;

            $cssStyle = 'solid';
            if (in_array($val, ['double', 'triple', 'thickthinsmallgap', 'thinthicksmallgap', 'thickthinmediumgap', 'thinthickmediumgap', 'thickthinlargegap', 'thinthicklargegap'], true)) {
                $cssStyle = 'double';
                $px = max(3.0, $px);
            } elseif (str_contains($val, 'dash')) {
                $cssStyle = 'dashed';
            } elseif (str_contains($val, 'dot')) {
                $cssStyle = 'dotted';
            }

            $color = $this->normalizeHexColor($this->attr($sideEl, 'color')) ?? '#0f172a';
            $result[$side] = "{$px}px {$cssStyle} {$color}";
        }

        return $result;
    }

    /**
     * Extract cell padding from <w:tcMar> or <w:tblCellMar>.
     */
    private function extractCellPadding(DOMElement $marEl, string $fallback): string
    {
        $sides = ['top' => null, 'right' => null, 'bottom' => null, 'left' => null];
        $found = false;

        foreach ($marEl->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }
            $name = $child->localName;
            if ($name === 'start') {
                $name = 'left';
            } elseif ($name === 'end') {
                $name = 'right';
            }
            if (array_key_exists($name, $sides)) {
                $w = $this->attr($child, 'w');
                if ($w !== '' && is_numeric($w)) {
                    $sides[$name] = round(((float) $w) / 20, 1).'pt';
                    $found = true;
                }
            }
        }

        if (! $found) {
            return $fallback;
        }

        return sprintf(
            '%s %s %s %s',
            $sides['top'] ?? '3pt',
            $sides['right'] ?? '5pt',
            $sides['bottom'] ?? '3pt',
            $sides['left'] ?? '5pt'
        );
    }

    /**
     * Read an attribute supporting both namespaced (w:val / r:embed) and unprefixed forms.
     */
    private function attr(DOMElement $element, string $localName): string
    {
        foreach (['w:'.$localName, 'r:'.$localName, $localName] as $candidate) {
            if ($element->hasAttribute($candidate)) {
                return (string) $element->getAttribute($candidate);
            }
        }

        if ($element->attributes !== null) {
            foreach ($element->attributes as $attr) {
                if ($attr->localName === $localName) {
                    return (string) $attr->nodeValue;
                }
            }
        }

        return '';
    }

    /**
     * Normalize a Word hex color string (e.g. "1F4E79" -> "#1F4E79").
     */
    private function normalizeHexColor(string $raw): ?string
    {
        $trimmed = ltrim(trim($raw), '#');
        if ($trimmed === '' || strtolower($trimmed) === 'auto' || strtolower($trimmed) === 'none') {
            return null;
        }

        if (preg_match('/^[0-9A-Fa-f]{6}$/', $trimmed)) {
            return '#'.strtoupper($trimmed);
        }

        return null;
    }

    /**
     * Map Word highlight color names to CSS colors.
     */
    private function mapHighlightColor(string $raw): ?string
    {
        $map = [
            'yellow' => '#fef08a',
            'green' => '#bbf7d0',
            'cyan' => '#a5f3fc',
            'magenta' => '#f5d0fe',
            'blue' => '#bfdbfe',
            'red' => '#fecaca',
            'lightgray' => '#e2e8f0',
            'darkgray' => '#cbd5e1',
        ];

        return $map[strtolower(trim($raw))] ?? null;
    }

    /**
     * Normalize an internal Word relationship target to a zip path inside word/.
     */
    private function normalizeWordTarget(string $target): string
    {
        $clean = str_replace('\\', '/', ltrim($target, '/'));
        if (str_starts_with($clean, 'word/')) {
            return $clean;
        }
        $clean = preg_replace('#^\.\./#', '', $clean) ?? $clean;

        return 'word/'.$clean;
    }

    /**
     * Compute the .rels path for any Word XML part (e.g. word/header1.xml -> word/_rels/header1.xml.rels).
     */
    private function relsPathForPart(string $zipPath): string
    {
        $dir = dirname($zipPath);
        $base = basename($zipPath);

        return ($dir !== '.' ? $dir.'/' : '').'_rels/'.$base.'.rels';
    }

    /**
     * Safely escape HTML attribute values.
     */
    private function escapeAttr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
