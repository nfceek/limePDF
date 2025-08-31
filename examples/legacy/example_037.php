<?php 
//============================================================+
// File name   : example_037.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original limePDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Spot colors
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_037.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Spot colors';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = '<h1>Example of Spot Colors</h1>Spot colors are single ink colors, rather than colors produced by four (CMYK), '.
		'six (CMYKOG) or more inks in the printing process (process colors). They can be obtained by special vendors, but often '.
		'the printers have found their own way of mixing inks to match defined colors.<br /><br />As long as no open standard '.
		'for spot colours exists, limePDF users will have to buy a colour book by one of the colour manufacturers and insert the '.
		'values and names of spot colours directly into the $spotcolor array in <b><em>include/limePDF_colors.php</em></b> file, '.
		'or define them using the <b><em>AddSpotColor()</em></b> method.<br /><br />Common industry standard spot colors are: '.
		'<br /><span color="#008800">ANPA-COLOR, DIC, FOCOLTONE, GCMI, HKS, PANTONE, TOYO, TRUMATCH</span>.';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('helvetica', '', 11);

// add a page
$pdf->AddPage();

// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $pdfText, 0, 1, 0, true, 'J', true);

$pdf->setFont('helvetica', '', 10);

// Define some new spot colors
// $c, $m, $y and $k (2nd, 3rd, 4th and 5th parameter) are the CMYK color components.
// AddSpotColor($name, $c, $m, $y, $k)

$pdf->AddSpotColor('My limePDF Dark Green', 100, 50, 80, 45);
$pdf->AddSpotColor('My limePDF Light Yellow', 0, 0, 55, 0);
$pdf->AddSpotColor('My limePDF Black', 0, 0, 0, 100);
$pdf->AddSpotColor('My limePDF Red', 30, 100, 90, 10);
$pdf->AddSpotColor('My limePDF Green', 100, 30, 100, 0);
$pdf->AddSpotColor('My limePDF Blue', 100, 60, 10, 5);
$pdf->AddSpotColor('My limePDF Yellow', 0, 20, 100, 0);

// Select the spot color
// $tint (the second parameter) is the intensity of the color (0-100).
// setTextSpotColor($name, $tint=100)
// setDrawSpotColor($name, $tint=100)
// setFillSpotColor($name, $tint=100)

$pdf->setTextSpotColor('My limePDF Black', 100);
$pdf->setDrawSpotColor('My limePDF Black', 100);

$starty = 100;

// print some spot colors

$pdf->setFillSpotColor('My limePDF Dark Green', 100);
$pdf->Rect(30, $starty, 40, 20, 'DF');
$pdf->Text(73, $starty + 8, 'My limePDF Dark Green');

$starty += 24;
$pdf->setFillSpotColor('My limePDF Light Yellow', 100);
$pdf->Rect(30, $starty, 40, 20, 'DF');
$pdf->Text(73, $starty + 8, 'My limePDF Light Yellow');


// --- default values defined on spotcolors.php ---

$starty += 24;
$pdf->setFillSpotColor('My limePDF Red', 100);
$pdf->Rect(30, $starty, 40, 20, 'DF');
$pdf->Text(73, $starty + 8, 'My limePDF Red');

$starty += 24;
$pdf->setFillSpotColor('My limePDF Green', 100);
$pdf->Rect(30, $starty, 40, 20, 'DF');
$pdf->Text(73, $starty + 8, 'My limePDF Green');

$starty += 24;
$pdf->setFillSpotColor('My limePDF Blue', 100);
$pdf->Rect(30, $starty, 40, 20, 'DF');
$pdf->Text(73, $starty + 8, 'My limePDF Blue');

$starty += 24;
$pdf->setFillSpotColor('My limePDF Yellow', 100);
$pdf->Rect(30, $starty, 40, 20, 'DF');
$pdf->Text(73, $starty + 8, 'My limePDF Yellow');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
