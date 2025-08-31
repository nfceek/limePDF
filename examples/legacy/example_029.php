<?php 
//============================================================+
// File name   : example_029.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Set PDF viewer display preferences
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_029.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Set PDF viewer display preferences';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'DISPLAY PREFERENCES - PAGE 1';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 


// set array for viewer preferences
$preferences = array(
	'HideToolbar' => true,
	'HideMenubar' => true,
	'HideWindowUI' => true,
	'FitWindow' => true,
	'CenterWindow' => true,
	'DisplayDocTitle' => true,
	'NonFullScreenPageMode' => 'UseNone', // UseNone, UseOutlines, UseThumbs, UseOC
	'ViewArea' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
	'ViewClip' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
	'PrintArea' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
	'PrintClip' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
	'PrintScaling' => 'AppDefault', // None, AppDefault
	'Duplex' => 'DuplexFlipLongEdge', // Simplex, DuplexFlipShortEdge, DuplexFlipLongEdge
	'PickTrayByPDFSize' => true,
	'PrintPageRange' => array(1,1,2,3),
	'NumCopies' => 2
);

// Check the example n. 60 for advanced page settings

// set pdf viewer preferences
$pdf->setViewerPreferences($preferences);

// set font
$pdf->setFont('times', '', 14);

// add a page
$pdf->AddPage();

// print a line
$pdf->Cell(0, 12, $pdfText, 1, 1, 'C');

$pdf->Ln(5);

$pdf->Write(0, 'You can use the setViewerPreferences() method to change viewer preferences.', '', 0, 'L', true, 0, false, false, 0);

// add a page
$pdf->AddPage();
// print a line
$pdf->Cell(0, 12, 'DISPLAY PREFERENCES - PAGE 2', 0, 0, 'C');

// add a page
$pdf->AddPage();
// print a line
$pdf->Cell(0, 12, 'DISPLAY PREFERENCES - PAGE 3', 0, 0, 'C');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
