<?php
//============================================================+
// File name   : example_004.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Cell stretching
//               
//
// Last Update : 8-30-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_004.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Cell stretching';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'TEST CELL STRETCH:';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('times', '', 11);

// add a page
$pdf->AddPage();

//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')

// test Cell stretching
$pdf->Cell(0, 0, $pdfText . ' no stretch', 1, 1, 'C', 0, '', 0);
$pdf->Cell(0, 0, $pdfText . ' scaling', 1, 1, 'C', 0, '', 1);
$pdf->Cell(0, 0, $pdfText . ' force scaling', 1, 1, 'C', 0, '', 2);
$pdf->Cell(0, 0, $pdfText . ' spacing', 1, 1, 'C', 0, '', 3);
$pdf->Cell(0, 0, $pdfText . ' force spacing', 1, 1, 'C', 0, '', 4);

$pdf->Ln(5);

$pdf->Cell(45, 0, $pdfText . ' scaling', 1, 1, 'C', 0, '', 1);
$pdf->Cell(45, 0, $pdfText . ' force scaling', 1, 1, 'C', 0, '', 2);
$pdf->Cell(45, 0, $pdfText . ' spacing', 1, 1, 'C', 0, '', 3);
$pdf->Cell(45, 0, $pdfText . ' force spacing', 1, 1, 'C', 0, '', 4);

$pdf->AddPage();

// example using general stretching and spacing

for ($stretching = 90; $stretching <= 110; $stretching += 10) {
	for ($spacing = -0.254; $spacing <= 0.254; $spacing += 0.254) {

		// set general stretching (scaling) value
		$pdf->setFontStretching($stretching);

		// set general spacing value
		$pdf->setFontSpacing($spacing);

		$pdf->Cell(0, 0, 'Stretching '.$stretching.'%, Spacing '.sprintf('%+.3F', $spacing).'mm, no stretch', 1, 1, 'C', 0, '', 0);
		$pdf->Cell(0, 0, 'Stretching '.$stretching.'%, Spacing '.sprintf('%+.3F', $spacing).'mm, scaling', 1, 1, 'C', 0, '', 1);
		$pdf->Cell(0, 0, 'Stretching '.$stretching.'%, Spacing '.sprintf('%+.3F', $spacing).'mm, force scaling', 1, 1, 'C', 0, '', 2);
		$pdf->Cell(0, 0, 'Stretching '.$stretching.'%, Spacing '.sprintf('%+.3F', $spacing).'mm, spacing', 1, 1, 'C', 0, '', 3);
		$pdf->Cell(0, 0, 'Stretching '.$stretching.'%, Spacing '.sprintf('%+.3F', $spacing).'mm, force spacing', 1, 1, 'C', 0, '', 4);

		$pdf->Ln(2);
	}
}

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
