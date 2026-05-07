<?php
require_once __DIR__ . '/fpdf.php';

class SmartPDF extends FPDF {
    private $reportTitle;

    public function setReportTitle($title) {
        $this->reportTitle = $title;
    }

    // Page header
    function Header() {
        // Colors: Green (#10b981) and Dark Blue (#0f172a)
        $this->SetFillColor(15, 23, 42); // Dark Blue
        $this->Rect(0, 0, 210, 40, 'F');
        
        // Logo-like text
        $this->SetFont('Arial', 'B', 22);
        $this->SetTextColor(16, 185, 129); // Green
        $this->Text(15, 20, 'SMART');
        $this->SetTextColor(255, 255, 255);
        $this->Text(46, 20, 'NUTRITION');
        
        // Report Title
        $this->SetFont('Arial', 'B', 15);
        $this->SetXY(15, 28);
        $this->Cell(0, 10, utf8_decode($this->reportTitle), 0, 0, 'L');
        
        // Date
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(200, 200, 200);
        $this->SetXY(150, 28);
        $this->Cell(45, 10, 'G' . utf8_decode('é') . 'n' . utf8_decode('é') . 'r' . utf8_decode('é') . ' le: ' . date('d/m/Y'), 0, 0, 'R');
        
        // Line break
        $this->Ln(20);
    }

    // Page footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->Cell(0, 10, 'Smart Nutrition Platform - Document Officiel', 0, 0, 'R');
    }

    // Fancy Table Header
    function StyledHeader($header, $widths) {
        $this->SetFillColor(16, 185, 129); // Green
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(16, 185, 129);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', 'B', 12);
        
        for($i=0; $i<count($header); $i++) {
            $this->Cell($widths[$i], 12, utf8_decode($header[$i]), 1, 0, 'C', true);
        }
        $this->Ln();
        
        // Restoration for body
        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 11);
    }
}
