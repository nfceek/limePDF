<?php 
//============================================================+
// File name   : example_044.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Move, copy and delete pages
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_044.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Move, copy and delete pages';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'LimePdf allows you to Copy, Move and Delete pages.';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 
// set font
$pdf->setFont('helvetica', 'B', 40);

// print a line using Cell()
$pdf->AddPage();
$pdf->Cell(0, 10, 'PAGE: A', 0, 1, 'L');

// add some vertical space
$pdf->Ln(10);

// print some text
$pdf->setFont('times', 'I', 16);

$pdf->Write(0, $pdfText, '', 0, 'L', true, 0, false, false, 0);

$pdf->setFont('helvetica', 'B', 40);

$pdf->AddPage();
$pdf->Cell(0, 10, 'PAGE: B', 0, 1, 'L');

$pdf->AddPage();
$pdf->Cell(0, 10, 'PAGE: D', 0, 1, 'L');

$pdf->AddPage();
$pdf->Cell(0, 10, 'PAGE: E', 0, 1, 'L');

$pdf->AddPage();
$pdf->Cell(0, 10, 'PAGE: E-2', 0, 1, 'L');

$pdf->AddPage();
$pdf->Cell(0, 10, 'PAGE: F', 0, 1, 'L');

$pdf->AddPage();
$pdf->Cell(0, 10, 'PAGE: C', 0, 1, 'L');

$pdf->AddPage();
$pdf->Cell(0, 10, 'PAGE: G', 0, 1, 'L');

// Move page 7 to page 3
$pdf->movePage(7, 3);

// Delete page 6
$pdf->deletePage(6);

$pdf->AddPage();
$pdf->Cell(0, 10, 'PAGE: H', 0, 1, 'L');

// copy the second page
$pdf->copyPage(2);

// NOTE: to insert a page to a previous position, you can add a new page to the end of document and then move it using movePage().

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
