<?php 
//============================================================+
// File name   : example_017.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Two independent columns with MultiCell
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_017.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Two independent columns with MultiCell';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
	$LeftColumn = 'left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column left column left column left column left column' . 
	' left column left column left column left column left column left column left column';

	$RightColumn = 'right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column right column right column right column right column' . 
	' right column right column right column right column right column right column right column';


// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('helvetica', '', 20);

// add a page
$pdf->AddPage();

$pdf->Write(0, 'Example of independent Multicell() columns', '', 0, 'L', true, 0, false, false, 0);

$pdf->Ln(5);

$pdf->setFont('times', '', 12);

// create columns content
// create columns content
$left_column = '[LEFT COLUMN]' . $LeftColumn . "\n";

$right_column = '[RIGHT COLUMN]'. $RightColumn  . "\n";

// set color for background
$pdf->setFillColor(255, 255, 200);

// set color for text
$pdf->setTextColor(0, 63, 127);

// write the first column
$pdf->MultiCell(80, 0, $left_column, 1, 'J', 1, 0, '', '', true, 0, false, true, 0);

// set color for background
$pdf->setFillColor(215, 235, 255);

// set color for text
$pdf->setTextColor(127, 31, 0);

// write the second column
$pdf->MultiCell(80, 0, $right_column, 1, 'J', 1, 1, '', '', true, 0, false, true, 0);

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
