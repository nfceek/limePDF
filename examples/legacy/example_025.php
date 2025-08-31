<?php 
//============================================================+
// File name   : example_025.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Object Transparency
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_025.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Object Transparency';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfImage = dirname(__DIR__, 2) . 'You can set the transparency of PDF objects using the setAlpha() method.';
		$pdfText = 'You can set the transparency of PDF objects using the setAlpha() method.';
// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 


// set font
$pdf->setFont('helvetica', '', 12);

// add a page
$pdf->AddPage();

$pdf->Write(0, $pdfText, '', 0, '', true, 0, false, false, 0);

/*
 * setAlpha() gives transparency support. You can set the
 * alpha channel from 0 (fully transparent) to 1 (fully
 * opaque). It applies to all elements (text, drawings,
 * images).
 */

$pdf->setLineWidth(2);

// draw opaque red square
$pdf->setFillColor(255, 0, 0);
$pdf->setDrawColor(127, 0, 0);
$pdf->Rect(30, 40, 60, 60, 'DF');

// set alpha to semi-transparency
$pdf->setAlpha(0.5);

// draw green square
$pdf->setFillColor(0, 255, 0);
$pdf->setDrawColor(0, 127, 0);
$pdf->Rect(50, 60, 60, 60, 'DF');

// draw blue square
$pdf->setFillColor(0, 0, 255);
$pdf->setDrawColor(0, 0, 127);
$pdf->Rect(70, 80, 60, 60, 'DF');

// draw jpeg image
$pdf->Image($pdfImage, 90, 100, 60, 60, '', 'http://www.limePDF.com', '', true, 72);

// restore full opacity
$pdf->setAlpha(1);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
