<?php 
//============================================================+
// File name   : example_022.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : CMYK colors
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_022.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'CMYK colors';
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


// check also the following methods:
// setDrawColorArray()
// setFillColorArray()
// setTextColorArray()

// set font
$pdf->setFont('helvetica', 'B', 18);

// add a page
$pdf->AddPage();

$pdf->Write(0, 'Example of CMYK, RGB and Grayscale colours', '', 0, 'L', true, 0, false, false, 0);

// define style for border
$border_style = array('all' => array('width' => 2, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'phase' => 0));

// --- CMYK ------------------------------------------------

$pdf->setDrawColor(50, 0, 0, 0);
$pdf->setFillColor(100, 0, 0, 0);
$pdf->setTextColor(100, 0, 0, 0);
$pdf->Rect(30, 60, 30, 30, 'DF', $border_style);
$pdf->Text(30, 92, 'Cyan');

$pdf->setDrawColor(0, 50, 0, 0);
$pdf->setFillColor(0, 100, 0, 0);
$pdf->setTextColor(0, 100, 0, 0);
$pdf->Rect(70, 60, 30, 30, 'DF', $border_style);
$pdf->Text(70, 92, 'Magenta');

$pdf->setDrawColor(0, 0, 50, 0);
$pdf->setFillColor(0, 0, 100, 0);
$pdf->setTextColor(0, 0, 100, 0);
$pdf->Rect(110, 60, 30, 30, 'DF', $border_style);
$pdf->Text(110, 92, 'Yellow');

$pdf->setDrawColor(0, 0, 0, 50);
$pdf->setFillColor(0, 0, 0, 100);
$pdf->setTextColor(0, 0, 0, 100);
$pdf->Rect(150, 60, 30, 30, 'DF', $border_style);
$pdf->Text(150, 92, 'Black');

// --- RGB -------------------------------------------------

$pdf->setDrawColor(255, 127, 127);
$pdf->setFillColor(255, 0, 0);
$pdf->setTextColor(255, 0, 0);
$pdf->Rect(30, 110, 30, 30, 'DF', $border_style);
$pdf->Text(30, 142, 'Red');

$pdf->setDrawColor(127, 255, 127);
$pdf->setFillColor(0, 255, 0);
$pdf->setTextColor(0, 255, 0);
$pdf->Rect(70, 110, 30, 30, 'DF', $border_style);
$pdf->Text(70, 142, 'Green');

$pdf->setDrawColor(127, 127, 255);
$pdf->setFillColor(0, 0, 255);
$pdf->setTextColor(0, 0, 255);
$pdf->Rect(110, 110, 30, 30, 'DF', $border_style);
$pdf->Text(110, 142, 'Blue');

// --- GRAY ------------------------------------------------

$pdf->setDrawColor(191);
$pdf->setFillColor(127);
$pdf->setTextColor(127);
$pdf->Rect(30, 160, 30, 30, 'DF', $border_style);
$pdf->Text(30, 192, 'Gray');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
