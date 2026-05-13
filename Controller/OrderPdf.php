<?php

namespace App\Service;

class OrderPdf
{
    private const PAGE_WIDTH = 612;
    private const PAGE_HEIGHT = 792;
    private const MARGIN_X = 42;
    private const HEADER_TOP = 730;

    public function build(array $order): string
    {
        $lines = $this->buildInvoiceLines($order);
        $contentStream = $this->buildContentStream($order, $lines);
        $contentLength = strlen($contentStream);

        $objects = [];
        $objects[1] = '<< /Title (' . $this->pdfEscape('Order #' . (int) ($order['id'] ?? 0)) . ') /Author (Smart Nutrition) /Creator (ProjetWeb1) >>';
        $objects[2] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
        $objects[4] = '<< /Type /Pages /Kids [6 0 R] /Count 1 >>';
        $objects[5] = '<< /Type /Catalog /Pages 4 0 R >>';
        $objects[6] = '<< /Type /Page /Parent 4 0 R /MediaBox [0 0 ' . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . '] /Resources << /Font << /F1 2 0 R /F2 3 0 R >> >> /Contents 7 0 R >>';
        $objects[7] = '<< /Length ' . $contentLength . " >>\nstream\n" . $contentStream . "\nendstream";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        for ($objectId = 1; $objectId <= 7; $objectId++) {
            $offsets[$objectId] = strlen($pdf);
            $pdf .= $objectId . " 0 obj\n" . $objects[$objectId] . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= "0 8\n";
        $pdf .= "0000000000 65535 f \n";

        for ($objectId = 1; $objectId <= 7; $objectId++) {
            $pdf .= sprintf('%010d 00000 n \n', $offsets[$objectId]);
        }

        $pdf .= "trailer\n";
        $pdf .= "<< /Size 8 /Root 5 0 R /Info 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    public function filenameForOrder(array $order): string
    {
        return 'order-' . (int) ($order['id'] ?? 0) . '.pdf';
    }

    private function buildInvoiceLines(array $order): array
    {
        $items = $order['items'] ?? [];
        $lines = [];

        foreach ($items as $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $subtotal = $quantity * $unitPrice;
            $lines[] = [
                'name' => (string) ($item['product_name'] ?? 'Unknown'),
                'qty' => $quantity,
                'unit' => $unitPrice,
                'subtotal' => $subtotal,
            ];
        }

        return $lines;
    }

    private function buildContentStream(array $order, array $lines): string
    {
        $buyerName = (string) ($order['buyer_name'] ?? '');
        $buyerPhone = (string) ($order['buyer_phone'] ?? '');
        $buyerEmail = (string) ($order['buyer_email'] ?? '');
        $buyerAddress = (string) ($order['buyer_address'] ?? '');
        $orderId = (int) ($order['id'] ?? 0);
        $orderDate = (string) ($order['created_at'] ?? date('Y-m-d H:i:s'));
        $totalPrice = (float) ($order['total_price'] ?? 0);

        $left = 42;
        $width = 528;
        $right = $left + $width;

        $stream = [];
        $stream[] = 'q';
        $stream[] = '0.99 0.99 0.99 rg';
        $stream[] = '0 0 ' . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . ' re f';
        $stream[] = 'Q';

        // Header area
        $stream[] = '0.09 0.13 0.19 rg';
        $stream[] = $left . ' 706 ' . $width . ' 54 re f';
        $stream[] = '0.17 0.60 0.36 rg';
        $stream[] = $left . ' 706 8 54 re f';

        $stream[] = 'BT';
        $stream[] = '/F2 19 Tf';
        $stream[] = '1 1 1 rg';
        $stream[] = '1 0 0 1 58 736 Tm';
        $stream[] = '(' . $this->pdfEscape('Smart Nutrition') . ') Tj';
        $stream[] = '/F1 9 Tf';
        $stream[] = '0.82 0.86 0.92 rg';
        $stream[] = '1 0 0 1 58 720 Tm';
        $stream[] = '(' . $this->pdfEscape('Order Invoice') . ') Tj';
        $stream[] = '/F2 12 Tf';
        $stream[] = '1 1 1 rg';
        $stream[] = '1 0 0 1 444 734 Tm';
        $stream[] = '(#' . $this->pdfEscape((string) $orderId) . ') Tj';
        $stream[] = '/F1 9 Tf';
        $stream[] = '0.84 0.88 0.93 rg';
        $stream[] = '1 0 0 1 444 719 Tm';
        $stream[] = '(' . $this->pdfEscape($orderDate) . ') Tj';
        $stream[] = 'ET';

        // Customer summary card
        $stream[] = '0.97 0.98 0.99 rg';
        $stream[] = $left . ' 614 ' . $width . ' 78 re f';
        $stream[] = '0.85 0.88 0.92 RG';
        $stream[] = '1 w';
        $stream[] = $left . ' 614 ' . $width . ' 78 re S';

        $stream[] = 'BT';
        $stream[] = '/F2 10 Tf';
        $stream[] = '0.14 0.19 0.26 rg';
        $stream[] = '1 0 0 1 56 678 Tm';
        $stream[] = '(Bill To) Tj';
        $stream[] = '/F1 10 Tf';
        $stream[] = '0.20 0.24 0.30 rg';
        $stream[] = '1 0 0 1 56 662 Tm';
        $stream[] = '(Name: ' . $this->pdfEscape($buyerName !== '' ? $buyerName : 'N/A') . ') Tj';
        $stream[] = '1 0 0 1 56 648 Tm';
        $stream[] = '(Phone: ' . $this->pdfEscape($buyerPhone !== '' ? $buyerPhone : 'N/A') . ') Tj';
        $stream[] = '1 0 0 1 56 634 Tm';
        $stream[] = '(Email: ' . $this->pdfEscape($buyerEmail !== '' ? $buyerEmail : 'N/A') . ') Tj';
        $stream[] = '1 0 0 1 300 662 Tm';
        $stream[] = '(Delivery Address:) Tj';
        $stream[] = '1 0 0 1 300 648 Tm';
        $stream[] = '(' . $this->pdfEscape($this->truncateText($this->singleLine($buyerAddress), 44)) . ') Tj';
        $stream[] = 'ET';

        // Table frame and header
        $tableTop = 572;
        $rowHeight = 22;
        $colProduct = 52;
        $colQtyRight = 365;
        $colUnitRight = 455;
        $colSubtotalRight = 560;

        $stream[] = '0.15 0.18 0.24 rg';
        $stream[] = $left . ' ' . $tableTop . ' ' . $width . ' 24 re f';
        $stream[] = 'BT';
        $stream[] = '/F2 10 Tf';
        $stream[] = '1 1 1 rg';
        $stream[] = '1 0 0 1 52 ' . ($tableTop + 8) . ' Tm';
        $stream[] = '(Product) Tj';
        $stream[] = '1 0 0 1 325 ' . ($tableTop + 8) . ' Tm';
        $stream[] = '(Qty) Tj';
        $stream[] = '1 0 0 1 390 ' . ($tableTop + 8) . ' Tm';
        $stream[] = '(Unit Price) Tj';
        $stream[] = '1 0 0 1 488 ' . ($tableTop + 8) . ' Tm';
        $stream[] = '(Subtotal) Tj';
        $stream[] = 'ET';

        $y = $tableTop - $rowHeight;
        foreach ($lines as $index => $item) {
            $isAlt = $index % 2 === 0;
            $stream[] = $isAlt ? '0.98 0.99 1 rg' : '0.95 0.96 0.98 rg';
            $stream[] = $left . ' ' . $y . ' ' . $width . ' ' . $rowHeight . ' re f';
            $stream[] = '0.88 0.90 0.93 RG';
            $stream[] = '0.5 w';
            $stream[] = $left . ' ' . $y . ' ' . $width . ' ' . $rowHeight . ' re S';

            $productName = $this->truncateText((string) $item['name'], 38);
            $qtyText = (string) $item['qty'];
            $unitText = $this->formatMoney((float) $item['unit']);
            $subtotalText = $this->formatMoney((float) $item['subtotal']);

            $stream[] = 'BT';
            $stream[] = '/F1 9.5 Tf';
            $stream[] = '0.17 0.20 0.25 rg';
            $stream[] = '1 0 0 1 ' . $colProduct . ' ' . ($y + 7) . ' Tm';
            $stream[] = '(' . $this->pdfEscape($productName) . ') Tj';
            $stream[] = '1 0 0 1 ' . $this->rightAlignX($colQtyRight, $qtyText, 9.5) . ' ' . ($y + 7) . ' Tm';
            $stream[] = '(' . $this->pdfEscape($qtyText) . ') Tj';
            $stream[] = '1 0 0 1 ' . $this->rightAlignX($colUnitRight, $unitText, 9.5) . ' ' . ($y + 7) . ' Tm';
            $stream[] = '(' . $this->pdfEscape($unitText) . ') Tj';
            $stream[] = '1 0 0 1 ' . $this->rightAlignX($colSubtotalRight, $subtotalText, 9.5) . ' ' . ($y + 7) . ' Tm';
            $stream[] = '(' . $this->pdfEscape($subtotalText) . ') Tj';
            $stream[] = 'ET';

            $y -= $rowHeight;
        }

        if ($lines === []) {
            $stream[] = '0.98 0.99 1 rg';
            $stream[] = $left . ' ' . $y . ' ' . $width . ' ' . $rowHeight . ' re f';
            $stream[] = '0.88 0.90 0.93 RG';
            $stream[] = '0.5 w';
            $stream[] = $left . ' ' . $y . ' ' . $width . ' ' . $rowHeight . ' re S';
            $stream[] = 'BT';
            $stream[] = '/F1 9.5 Tf';
            $stream[] = '0.30 0.33 0.38 rg';
            $stream[] = '1 0 0 1 52 ' . ($y + 7) . ' Tm';
            $stream[] = '(No line items in this order.) Tj';
            $stream[] = 'ET';
            $y -= $rowHeight;
        }

        // Totals panel (always below the items table)
        $totalsHeight = 94;
        $totalsGap = 12;
        $tableBottom = $y + $rowHeight;
        $totalsTop = max(96, (int) ($tableBottom - $totalsGap - $totalsHeight));
        $panelX = 332;
        $panelW = 238;
        $stream[] = '0.10 0.14 0.20 rg';
        $stream[] = $panelX . ' ' . $totalsTop . ' ' . $panelW . ' ' . $totalsHeight . ' re f';
        $stream[] = '0.17 0.60 0.36 rg';
        $stream[] = $panelX . ' ' . ($totalsTop + $totalsHeight - 12) . ' ' . $panelW . ' 12 re f';

        $itemsCountText = (string) count($lines);
        $totalText = $this->formatMoney($totalPrice);

        $stream[] = 'BT';
        $stream[] = '/F2 12 Tf';
        $stream[] = '1 1 1 rg';
        $stream[] = '1 0 0 1 346 ' . ($totalsTop + 64) . ' Tm';
        $stream[] = '(Invoice Totals) Tj';
        $stream[] = '/F1 10.5 Tf';
        $stream[] = '0.90 0.94 0.97 rg';
        $stream[] = '1 0 0 1 346 ' . ($totalsTop + 42) . ' Tm';
        $stream[] = '(Items) Tj';
        $stream[] = '1 0 0 1 ' . $this->rightAlignX(556, $itemsCountText, 10.5) . ' ' . ($totalsTop + 42) . ' Tm';
        $stream[] = '(' . $this->pdfEscape($itemsCountText) . ') Tj';
        $stream[] = '/F2 11 Tf';
        $stream[] = '1 1 1 rg';
        $stream[] = '1 0 0 1 346 ' . ($totalsTop + 24) . ' Tm';
        $stream[] = '(Total Due) Tj';
        $stream[] = '1 0 0 1 ' . $this->rightAlignX(556, $totalText, 11) . ' ' . ($totalsTop + 24) . ' Tm';
        $stream[] = '(' . $this->pdfEscape($totalText) . ') Tj';
        $stream[] = '/F1 8.5 Tf';
        $stream[] = '0.86 0.90 0.95 rg';
        $stream[] = '1 0 0 1 346 ' . ($totalsTop + 8) . ' Tm';
        $stream[] = '(Payment on delivery) Tj';
        $stream[] = 'ET';

        // Footer note
        $stream[] = 'BT';
        $stream[] = '/F1 8.5 Tf';
        $stream[] = '0.35 0.40 0.48 rg';
        $stream[] = '1 0 0 1 42 50 Tm';
        $stream[] = '(Thank you for choosing Smart Nutrition.) Tj';
        $stream[] = '1 0 0 1 42 36 Tm';
        $stream[] = '(Automatically generated by the back office.) Tj';
        $stream[] = 'ET';

        return implode("\n", $stream);
    }

    private function truncateText(string $text, int $maxChars): string
    {
        $text = trim($text);
        if ($text === '') {
            return 'N/A';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text) <= $maxChars) {
                return $text;
            }
            return mb_substr($text, 0, $maxChars - 1) . '...';
        }

        if (strlen($text) <= $maxChars) {
            return $text;
        }

        return substr($text, 0, $maxChars - 1) . '...';
    }

    private function rightAlignX(float $rightX, string $text, float $fontSize): float
    {
        // Basic width approximation for Helvetica to keep numeric columns aligned.
        $charCount = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
        $estimatedWidth = $charCount * $fontSize * 0.50;
        return max(42.0, $rightX - $estimatedWidth);
    }

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 2) . ' DH';
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
