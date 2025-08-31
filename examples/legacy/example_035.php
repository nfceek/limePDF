<?php 
//============================================================+
// File name   : example_035.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Line styles with cells and multicells
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_035.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Line styles with cells and multicells';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'Example of SetLineStyle() method';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('times', 'BI', 16);

// add a page
$pdf->AddPage();

$pdf->Write(0, $pdfText, '', 0, 'L', true, 0, false, false, 0);

$pdf->Ln();

$pdf->setLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 4, 'color' => array(255, 0, 0)));
$pdf->setFillColor(255,255,128);
$pdf->setTextColor(0,0,128);

$text="DUMMY";

$pdf->Cell(0, 0, $text, 1, 1, 'L', 1, 0);

$pdf->Ln();

$pdf->setLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 255)));
$pdf->setFillColor(255,255,0);
$pdf->setTextColor(0,0,255);
$pdf->MultiCell(60, 4, $text, 1, 'C', 1, 0);

$pdf->setLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 255, 0)));
$pdf->setFillColor(0,0,255);
$pdf->setTextColor(255,255,0);
$pdf->MultiCell(60, 4, $text, 'TB', 'C', 1, 0);

$pdf->setLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 255)));
$pdf->setFillColor(0,255,0);
$pdf->setTextColor(255,0,255);
$pdf->MultiCell(60, 4, $text, 1, 'C', 1, 1);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
