<?php 
//============================================================+
// File name   : example_051.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original LimePDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Full page background
//               
//
// Last Update : 9-1-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_051.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Full page background';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = '<span style="background-color:yellow;color:blue;">&nbsp;PAGE 1&nbsp;</span>' .
		'<p stroke="0.2" fill="true" strokecolor="yellow" color="blue" style="font-family:helvetica;font-weight:bold;font-size:26pt;">You can set a full page background.</p>';
		
		$pdfText2 = '<span style="background-color:yellow;color:blue;">&nbsp;PAGE 2&nbsp;</span>';

		$pdfImage = dirname(__DIR__, 2) . '/examples/images/image_demo.jpg';

		$pdfText3 = '<span style="color:white;text-align:center;font-weight:bold;font-size:80pt;">PAGE 3</span>';
// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('times', '', 48);

// add a page
$pdf->AddPage();

// Print a text
$pdf->writeHTML($pdfText, true, false, true, false, '');


// add a page
$pdf->AddPage();

// Print a text
$pdf->writeHTML($pdfText2, true, false, true, false, '');

// --- example with background set on page ---

// remove default header
$pdf->setPrintHeader(false);

// add a page
$pdf->AddPage();


// -- set new background ---

// get the current page break margin
$bMargin = $pdf->getBreakMargin();
// get current auto-page-break mode
$auto_page_break = $pdf->getAutoPageBreak();
// disable auto-page-break
$pdf->setAutoPageBreak(false, 0);
// set bacground image
$pdf->Image($pdfImage, null, 0, 210, 297, '', '', '', false, 300, 'C', false, false, 0);
// restore auto-page-break status
$pdf->setAutoPageBreak($auto_page_break, $bMargin);
// set the starting point for the page content
$pdf->setPageMark();


// Print a text
$pdf->writeHTML($pdfText3, true, false, true, false, '');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
