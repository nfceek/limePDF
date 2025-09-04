<?php 
//============================================================+
// File name   : example_055.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Display all characters available on core fonts
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Include\FontTrait;
use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_055.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Display all characters available on core fonts.';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'The quick brown fox jumps over the lazy dog';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('helvetica', '', 14);

// array of font names
$core_fonts = array('courier', 'courierB', 'courierI', 'courierBI', 'helvetica', 'helveticaB', 'helveticaI', 'helveticaBI', 'times', 'timesB', 'timesI', 'timesBI', 'symbol', 'zapfdingbats');

// set fill color
$pdf->setFillColor(221,238,255);

// create one HTML table for each core font
foreach($core_fonts as $font) {
	// add a page
	$pdf->AddPage();

	// Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')

	// set font for title
	$pdf->setFont('helvetica', 'B', 16);

	// print font name
	$pdf->Cell(0, 10, 'FONT: '.$font, 1, 1, 'C', true, '', 0, false, 'T', 'M');

	// set font for chars
	$pdf->setFont($font, '', 16);

	// print each character
	for ($i = 0; $i < 256; ++$i) {
		if (($i > 0) AND (($i % 16) == 0)) {
			$pdf->Ln();
		}
		$pdf->Cell(11.25, 11.25, $pdf::unichr($i), 1, 0, 'C', false, '', 0, false, 'T', 'M');
	}

	$pdf->Ln(20);

	// print a pangram
	$pdf->Cell(0, 0, $pdfText, 0, 1, 'C', false, '', 0, false, 'T', 'M');
}

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);
//============================================================+
// END OF FILE
//============================================================+
