<?php 
//============================================================+
// File name   : example_040.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Booklet mode (double-sided pages)
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_040.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Booklet mode (double-sided pages)';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'Example of booklet mode';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set display mode
$pdf->setDisplayMode($zoom='fullpage', $layout='TwoColumnRight', $mode='UseNone');

// set pdf viewer preferences
$pdf->setViewerPreferences(array('Duplex' => 'DuplexFlipLongEdge'));

// set booklet mode
$pdf->setBooklet(true, 10, 30);

// set core font
$pdf->setFont('helvetica', '', 18);

// add a page (left page)
$pdf->AddPage();

$pdf->Write(0, $pdfText, '', 0, 'L', true, 0, false, false, 0);

// print a line using Cell()
$pdf->Cell(0, 0, 'PAGE 1', 1, 1, 'C');


// add a page (right page)
$pdf->AddPage();

// print a line using Cell()
$pdf->Cell(0, 0, 'PAGE 2', 1, 1, 'C');


// add a page (left page)
$pdf->AddPage();

// print a line using Cell()
$pdf->Cell(0, 0, 'PAGE 3', 1, 1, 'C');

// add a page (right page)
$pdf->AddPage();

// print a line using Cell()
$pdf->Cell(0, 0, 'PAGE 4', 1, 1, 'C');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
