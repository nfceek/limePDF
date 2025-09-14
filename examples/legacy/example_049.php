<?php 
//============================================================+
// File name   : example_049.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original LimePDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : WriteHTML with LimePDF callback functions
//               
//
// Last Update : 9-1-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_049.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'WriteHTML with LimePDF callback functions';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = '<h1>Test LimePDF Methods in HTML</h1><h2 style="color:red;">IMPORTANT:</h2>
		<span style="color:red;">If you are using user-generated content, the limepdf tag should be considered unsafe.<br />
		Please use this feature only if you are in control of the HTML content and you are sure that it does not contain any harmful code.<br />
		This feature is disabled by default by the <b>K_LimePDF_CALLS_IN_HTML</b> constant on LimePDF configuration file.</span>
		<h2>write1DBarcode method in HTML</h2>';
		
// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('helvetica', '', 10);

// add a page
$pdf->AddPage();


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

IMPORTANT:
If you are printing user-generated content, the limepdf tag should be considered unsafe.
This tag is disabled by default by the limePDF_CALLS_IN_HTML constant on LimePDF configuration file.
Please use this feature only if you are in control of the HTML content and you are sure that it does not contain any harmful code.

For security reasons, the content of the LimePDF tag must be prepared and encoded with the serializeLimePDFtag() method (see the example below).

 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */


$data = $pdf->serializeLimePDFtag('write1DBarcode', array('CODE 39', 'C39', '', '', 80, 30, 0.4, array('position'=>'S', 'border'=>true, 'padding'=>4, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4), 'N'));
$pdfText .= '<limepdf data="'.$data.'" />';

$data = $pdf->serializeLimePDFtag('write1DBarcode', array('CODE 128', 'C128', '', '', 80, 30, 0.4, array('position'=>'S', 'border'=>true, 'padding'=>4, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4), 'N'));
$pdfText .= '<limepdf data="'.$data.'" />';

$data = $pdf->serializeLimePDFtag('AddPage');
$pdfText .= '<limepdf data="'.$data.'" /><h2>Graphic Functions</h2>';

$data = $pdf->serializeLimePDFtag('SetDrawColor', array(0));
$pdfText .= '<limepdf data="'.$data.'" />';

$data = $pdf->serializeLimePDFtag('Rect', array(50, 50, 40, 10, 'DF', array(), array(0,128,255)));
$pdfText .= '<limepdf data="'.$data.'" />';


// output the HTML content
$pdf->writeHTML($pdfText, true, 0, true, 0);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
