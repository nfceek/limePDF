<?php 
//============================================================+
// File name   : example_028.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Changing page formats
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_028.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'JChanging page formats';
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


$pdf->setDisplayMode('fullpage', 'SinglePage', 'UseNone');

// set font
$pdf->setFont('times', 'B', 20);

$pdf->AddPage('P', 'A4');
$pdf->Cell(0, 0, 'A4 PORTRAIT', 1, 1, 'C');

$pdf->AddPage('L', 'A4');
$pdf->Cell(0, 0, 'A4 LANDSCAPE', 1, 1, 'C');

$pdf->AddPage('P', 'A5');
$pdf->Cell(0, 0, 'A5 PORTRAIT', 1, 1, 'C');

$pdf->AddPage('L', 'A5');
$pdf->Cell(0, 0, 'A5 LANDSCAPE', 1, 1, 'C');

$pdf->AddPage('P', 'A6');
$pdf->Cell(0, 0, 'A6 PORTRAIT', 1, 1, 'C');

$pdf->AddPage('L', 'A6');
$pdf->Cell(0, 0, 'A6 LANDSCAPE', 1, 1, 'C');

$pdf->AddPage('P', 'A7');
$pdf->Cell(0, 0, 'A7 PORTRAIT', 1, 1, 'C');

$pdf->AddPage('L', 'A7');
$pdf->Cell(0, 0, 'A7 LANDSCAPE', 1, 1, 'C');


// --- test backward editing ---


$pdf->setPage(1, true);
$pdf->setY(50);
$pdf->Cell(0, 0, 'A4 test', 1, 1, 'C');

$pdf->setPage(2, true);
$pdf->setY(50);
$pdf->Cell(0, 0, 'A4 test', 1, 1, 'C');

$pdf->setPage(3, true);
$pdf->setY(50);
$pdf->Cell(0, 0, 'A5 test', 1, 1, 'C');

$pdf->setPage(4, true);
$pdf->setY(50);
$pdf->Cell(0, 0, 'A5 test', 1, 1, 'C');

$pdf->setPage(5, true);
$pdf->setY(50);
$pdf->Cell(0, 0, 'A6 test', 1, 1, 'C');

$pdf->setPage(6, true);
$pdf->setY(50);
$pdf->Cell(0, 0, 'A6 test', 1, 1, 'C');

$pdf->setPage(7, true);
$pdf->setY(40);
$pdf->Cell(0, 0, 'A7 test', 1, 1, 'C');

$pdf->setPage(8, true);
$pdf->setY(40);
$pdf->Cell(0, 0, 'A7 test', 1, 1, 'C');

$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
