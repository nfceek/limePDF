<?php 
//============================================================+
// File name   : example_023.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Page Groups
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_023.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Page Groups';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'Example of page groups. Check the page numbers on the page footer. \nThis is the first page of group 1.';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 


// set font
$pdf->setFont('times', 'BI', 14);

// Start First Page Group
$pdf->startPageGroup();

// add a page
$pdf->AddPage();

// print a block of text using Write()
$pdf->Write(0, $pdfText, '', 0, 'L', true, 0, false, false, 0);

// add second page
$pdf->AddPage();
$pdf->Cell(0, 10, 'This is the second page of group 1', 0, 1, 'L');

// Start Second Page Group
$pdf->startPageGroup();

// add some pages
$pdf->AddPage();
$pdf->Cell(0, 10, 'This is the first page of group 2', 0, 1, 'L');
$pdf->AddPage();
$pdf->Cell(0, 10, 'This is the second page of group 2', 0, 1, 'L');
$pdf->AddPage();
$pdf->Cell(0, 10, 'This is the third page of group 2', 0, 1, 'L');
$pdf->AddPage();
$pdf->Cell(0, 10, 'This is the fourth page of group 2', 0, 1, 'L');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
