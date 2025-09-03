<?php
//============================================================+
// File name   : example_003.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Custom page header and footer
//               
//
// Last Update : 8-30-2025
//============================================================+

use LimePDF\PDF;
use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_002.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = false;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = '';
	//  Set the Header logo
		$pdfHeaderImage = '';	
	//  Set Footer output
		$outputFooter = false;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

//  Set Text
	$pdfText = "LimePDF Example 003\n\n";
	$pdfText .= "Custom page header and footer are defined by extending the PDF class\n\n and overriding the Header() and Footer() methods.";


// ----- Dont Edit below here ---------------------------------------------------------

    class CustomPdf extends PDF
    {
        // Override the header
        public function Header()
        {
            // Example: put a logo
            $pdfHeaderImage =  dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png'; // adjust path
            if (file_exists($pdfHeaderImage)) {
                $this->Image($pdfHeaderImage, 10, 10, 30); // x, y, width
            }

            // Set font for header text
            $this->SetFont('helvetica', 'B', 12);
            $this->Cell(0, 0, 'Custom Header Example',0 , false, 'C', 0, '', 0, false, 'M', 'M');
        }

        // Override the footer
        public function Footer()
        {
            // Position footer at 15 mm from bottom
            $this->SetY(-15);

            // Set font
            $this->SetFont('helvetica', 'I', 8);

            // Page number
            $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(),
                0, false, 'C');
        }
    }

// Now use CustomPdf
$pdf = new CustomPdf();

$pdf->SetFont('times', '', 12);

// Add Page + Content
$pdf->AddPage();
$pdf->Write(0, $pdfText, '', false, 'C', true, 0, false, false, 0);

// Output
//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
