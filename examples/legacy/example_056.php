<?php 
//============================================================+
// File name   : example_056.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Crop marks and color registration bars
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_056.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Crop marks and color registration bars';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'Example of Registration Marks, Crop Marks and Color Bars';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('helvetica', '', 18);

// add a page
$pdf->AddPage();

$pdf->Write(0, $pdfText, '', 0, 'L', true, 0, false, false, 0);

$pdf->Ln(5);

// color registration bars

// A,W,R,G,B,C,M,Y,K,RGB,CMYK,ALL,ALLSPOT,<SPOT_COLOR_NAME>
$pdf->colorRegistrationBar(50, 70, 40, 40, true, false, 'A,R,G,B,C,M,Y,K');
$pdf->colorRegistrationBar(90, 70, 40, 40, true, true, 'A,R,G,B,C,M,Y,K');
$pdf->colorRegistrationBar(50, 115, 80, 5, false, true, 'A,W,R,G,B,C,M,Y,K,ALL');
$pdf->colorRegistrationBar(135, 70, 5, 50, false, false, 'A,W,R,G,B,C,M,Y,K,ALL');

// corner crop marks

$pdf->cropMark(50, 70, 10, 10, 'TL');
$pdf->cropMark(140, 70, 10, 10, 'TR');
$pdf->cropMark(50, 120, 10, 10, 'BL');
$pdf->cropMark(140, 120, 10, 10, 'BR');

// various crop marks

$pdf->cropMark(95, 65, 5, 5, 'LEFT,TOP,RIGHT', array(255,0,0));
$pdf->cropMark(95, 125, 5, 5, 'LEFT,BOTTOM,RIGHT', array(255,0,0));

$pdf->cropMark(45, 95, 5, 5, 'TL,BL', array(0,255,0));
$pdf->cropMark(145, 95, 5, 5, 'TR,BR', array(0,255,0));

$pdf->cropMark(95, 140, 5, 5, 'A,D', array(0,0,255));

// registration marks

$pdf->registrationMark(40, 60, 5, false);
$pdf->registrationMark(150, 60, 5, true, array(0,0,0), array(255,255,0));
$pdf->registrationMark(40, 130, 5, true, array(0,0,0), array(255,255,0));
$pdf->registrationMark(150, 130, 5, false, array(100,100,100,100,'All'), array(0,0,0,0,'None'));

// test registration bar with spot colors

$pdf->AddSpotColor('My TCPDF Dark Green', 100, 50, 80, 45);
$pdf->AddSpotColor('My TCPDF Light Yellow', 0, 0, 55, 0);
$pdf->AddSpotColor('My TCPDF Black', 0, 0, 0, 100);
$pdf->AddSpotColor('My TCPDF Red', 30, 100, 90, 10);
$pdf->AddSpotColor('My TCPDF Green', 100, 30, 100, 0);
$pdf->AddSpotColor('My TCPDF Blue', 100, 60, 10, 5);
$pdf->AddSpotColor('My TCPDF Yellow', 0, 20, 100, 0);

$pdf->colorRegistrationBar(50, 150, 80, 10, false, true, 'ALLSPOT');

// CMYK registration mark
$pdf->registrationMarkCMYK(150, 155, 8);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
