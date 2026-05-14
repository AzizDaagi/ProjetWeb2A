<?php

class OrderPdf
{
    private const PAGE_WIDTH = 612;
    private const PAGE_HEIGHT = 792;

    public function build(array $order): string
    {
        $lines = $this->buildInvoiceLines($order);
        $contentStream = $this->buildContentStream($order, $lines);
        $contentLength = strlen($contentStream);

        $objects = [
            1 => '<< /Title (' . $this->pdfEscape('Order #' . (int) ($order['id'] ?? 0)) . ') /Author (Smart Nutrition) /Creator (Smart Nutrition) >>',
            2 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
            4 => '<< /Type /Pages /Kids [6 0 R] /Count 1 >>',
            5 => '<< /Type /Catalog /Pages 4 0 R >>',
            6 => '<< /Type /Page /Parent 4 0 R /MediaBox [0 0 ' . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . '] /Resources << /Font << /F1 2 0 R /F2 3 0 R >> >> /Contents 7 0 R >>',
            7 => '<< /Length ' . $contentLength . " >>\nstream\n" . $contentStream . "\nendstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        for ($objectId = 1; $objectId <= 7; $objectId++) {
            $offsets[$objectId] = strlen($pdf);
            $pdf .= $objectId . " 0 obj\n" . $objects[$objectId] . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 8\n0000000000 65535 f \n";

        for ($objectId = 1; $objectId <= 7; $objectId++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$objectId]) . "\n";
        }

        $pdf .= "trailer\n<< /Size 8 /Root 5 0 R /Info 1 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    public function filenameForOrder(array $order): string
    {
        return 'order-' . (int) ($order['id'] ?? 0) . '.pdf';
    }

    private function buildInvoiceLines(array $order): array
    {
        $lines = [];
        foreach (($order['items'] ?? []) as $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $lines[] = [
                'name' => (string) ($item['product_name'] ?? 'Produit'),
                'qty' => $quantity,
                'unit' => $unitPrice,
                'subtotal' => $quantity * $unitPrice,
            ];
        }

        return $lines;
    }

    private function buildContentStream(array $order, array $lines): string
    {
        $orderId = (int) ($order['id'] ?? 0);
        $orderDate = (string) ($order['created_at'] ?? date('Y-m-d H:i:s'));
        $buyerName = (string) ($order['buyer_name'] ?? '');
        $buyerPhone = (string) ($order['buyer_phone'] ?? '');
        $buyerEmail = (string) ($order['buyer_email'] ?? '');
        $buyerAddress = $this->singleLine((string) ($order['buyer_address'] ?? ''));
        $totalPrice = (float) ($order['total_price'] ?? 0);

        $stream = [
            'q',
            '0.99 0.99 0.99 rg',
            '0 0 612 792 re f',
            'Q',
            '0.09 0.13 0.19 rg',
            '42 706 528 54 re f',
            '0.17 0.60 0.36 rg',
            '42 706 8 54 re f',
            'BT',
            '/F2 19 Tf',
            '1 1 1 rg',
            '1 0 0 1 58 736 Tm',
            '(' . $this->pdfEscape('Smart Nutrition') . ') Tj',
            '/F1 9 Tf',
            '0.82 0.86 0.92 rg',
            '1 0 0 1 58 720 Tm',
            '(' . $this->pdfEscape('Order Invoice') . ') Tj',
            '/F2 12 Tf',
            '1 1 1 rg',
            '1 0 0 1 444 734 Tm',
            '(#' . $this->pdfEscape((string) $orderId) . ') Tj',
            '/F1 9 Tf',
            '0.84 0.88 0.93 rg',
            '1 0 0 1 444 719 Tm',
            '(' . $this->pdfEscape($orderDate) . ') Tj',
            'ET',
            '0.97 0.98 0.99 rg',
            '42 614 528 78 re f',
            '0.85 0.88 0.92 RG',
            '1 w',
            '42 614 528 78 re S',
            'BT',
            '/F2 10 Tf',
            '0.14 0.19 0.26 rg',
            '1 0 0 1 56 678 Tm',
            '(Client) Tj',
            '/F1 10 Tf',
            '1 0 0 1 56 662 Tm',
            '(Nom: ' . $this->pdfEscape($buyerName !== '' ? $buyerName : 'N/A') . ') Tj',
            '1 0 0 1 56 648 Tm',
            '(Telephone: ' . $this->pdfEscape($buyerPhone !== '' ? $buyerPhone : 'N/A') . ') Tj',
            '1 0 0 1 56 634 Tm',
            '(Email: ' . $this->pdfEscape($buyerEmail !== '' ? $buyerEmail : 'N/A') . ') Tj',
            '1 0 0 1 300 662 Tm',
            '(Adresse:) Tj',
            '1 0 0 1 300 648 Tm',
            '(' . $this->pdfEscape($this->truncateText($buyerAddress, 44)) . ') Tj',
            'ET',
            '0.15 0.18 0.24 rg',
            '42 572 528 24 re f',
            'BT',
            '/F2 10 Tf',
            '1 1 1 rg',
            '1 0 0 1 52 580 Tm',
            '(Produit) Tj',
            '1 0 0 1 325 580 Tm',
            '(Qte) Tj',
            '1 0 0 1 390 580 Tm',
            '(Prix) Tj',
            '1 0 0 1 488 580 Tm',
            '(Sous-total) Tj',
            'ET',
        ];

        $y = 550;
        foreach ($lines as $index => $item) {
            $stream[] = $index % 2 === 0 ? '0.98 0.99 1 rg' : '0.95 0.96 0.98 rg';
            $stream[] = '42 ' . $y . ' 528 22 re f';
            $stream[] = 'BT';
            $stream[] = '/F1 9.5 Tf';
            $stream[] = '0.17 0.20 0.25 rg';
            $stream[] = '1 0 0 1 52 ' . ($y + 7) . ' Tm';
            $stream[] = '(' . $this->pdfEscape($this->truncateText((string) $item['name'], 38)) . ') Tj';
            $stream[] = '1 0 0 1 325 ' . ($y + 7) . ' Tm';
            $stream[] = '(' . $this->pdfEscape((string) $item['qty']) . ') Tj';
            $stream[] = '1 0 0 1 390 ' . ($y + 7) . ' Tm';
            $stream[] = '(' . $this->pdfEscape($this->formatMoney((float) $item['unit'])) . ') Tj';
            $stream[] = '1 0 0 1 488 ' . ($y + 7) . ' Tm';
            $stream[] = '(' . $this->pdfEscape($this->formatMoney((float) $item['subtotal'])) . ') Tj';
            $stream[] = 'ET';
            $y -= 22;
        }

        if ($lines === []) {
            $stream[] = 'BT';
            $stream[] = '/F1 10 Tf';
            $stream[] = '0.30 0.33 0.38 rg';
            $stream[] = '1 0 0 1 52 550 Tm';
            $stream[] = '(' . $this->pdfEscape('Aucun article dans cette commande.') . ') Tj';
            $stream[] = 'ET';
        }

        $stream[] = '0.10 0.14 0.20 rg';
        $stream[] = '332 96 238 94 re f';
        $stream[] = 'BT';
        $stream[] = '/F2 12 Tf';
        $stream[] = '1 1 1 rg';
        $stream[] = '1 0 0 1 346 160 Tm';
        $stream[] = '(' . $this->pdfEscape('Total facture') . ') Tj';
        $stream[] = '/F2 14 Tf';
        $stream[] = '1 0 0 1 346 130 Tm';
        $stream[] = '(' . $this->pdfEscape($this->formatMoney($totalPrice)) . ') Tj';
        $stream[] = '/F1 8.5 Tf';
        $stream[] = '1 0 0 1 42 50 Tm';
        $stream[] = '(' . $this->pdfEscape('Merci pour votre commande.') . ') Tj';
        $stream[] = 'ET';

        return implode("\n", $stream);
    }

    private function truncateText(string $text, int $maxChars): string
    {
        $text = trim($text);
        if ($text === '') {
            return 'N/A';
        }

        return mb_strlen($text) <= $maxChars ? $text : mb_substr($text, 0, $maxChars - 1) . '...';
    }

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 2) . ' DT';
    }

    private function singleLine(string $text): string
    {
        return str_replace(["\r", "\n"], ' ', $text);
    }

    private function pdfEscape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
