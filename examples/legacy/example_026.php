<?php 
//============================================================+
// File name   : example_026.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Text Rendering Modes and Text Clipping
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_026.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Text Rendering Modes and Text Clipping';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'Fill Text';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 


// set font
$pdf->setFont('helvetica', '', 22);

// add a page
$pdf->AddPage();

// set color for text stroke
$pdf->setDrawColor(255,0,0);


$pdf->setTextRenderingMode($stroke=0, $fill=true, $clip=false);
$pdf->Write(0, $pdfText, '', 0, '', true, 0, false, false, 0);

$pdf->setTextRenderingMode($stroke=0.2, $fill=false, $clip=false);
$pdf->Write(0, 'Stroke text', '', 0, '', true, 0, false, false, 0);

$pdf->setTextRenderingMode($stroke=0.2, $fill=true, $clip=false);
$pdf->Write(0, 'Fill, then stroke text', '', 0, '', true, 0, false, false, 0);

$pdf->setTextRenderingMode($stroke=0, $fill=false, $clip=false);
$pdf->Write(0, 'Neither fill nor stroke text (invisible)', '', 0, '', true, 0, false, false, 0);


// * * * CLIPPING MODES  * * * * * * * * * * * * * * * * * *

$pdf->StartTransform();
$pdf->setTextRenderingMode($stroke=0, $fill=true, $clip=true);
$pdf->Write(0, 'Fill text and add to path for clipping', '', 0, '', true, 0, false, false, 0);
$pdf->Image('images/image_demo.jpg', 15, 65, 170, 10, '', '', '', true, 72);
$pdf->StopTransform();

$pdf->StartTransform();
$pdf->setTextRenderingMode($stroke=0.3, $fill=false, $clip=true);
$pdf->Write(0, 'Stroke text and add to path for clipping', '', 0, '', true, 0, false, false, 0);
$pdf->Image('images/image_demo.jpg', 15, 75, 170, 10, '', '', '', true, 72);
$pdf->StopTransform();

$pdf->StartTransform();
$pdf->setTextRenderingMode($stroke=0.3, $fill=true, $clip=true);
$pdf->Write(0, 'Fill, then stroke text and add to path for clipping', '', 0, '', true, 0, false, false, 0);
$pdf->Image('images/image_demo.jpg', 15, 85, 170, 10, '', '', '', true, 72);
$pdf->StopTransform();

$pdf->StartTransform();
$pdf->setTextRenderingMode($stroke=0, $fill=false, $clip=true);
$pdf->Write(0, 'Add text to path for clipping', '', 0, '', true, 0, false, false, 0);
$pdf->Image('images/image_demo.jpg', 15, 95, 170, 10, '', '', '', true, 72);
$pdf->StopTransform();

// reset text rendering mode
$pdf->setTextRenderingMode($stroke=0, $fill=true, $clip=false);

// * * * HTML MODE * * * * * * * * * * * * * * * * * * * * *

// The following attributes were added to HTML:
// stroke : stroke width
// strokecolor : stroke color
// fill : true (default) to fill the font, false otherwise


// create some HTML content with text rendering modes
$html  = '<span stroke="0" fill="true">HTML Fill text</span><br />';
$html .= '<span stroke="0.2" fill="false">HTML Stroke text</span><br />';
$html .= '<span stroke="0.2" fill="true" strokecolor="#FF0000" color="#FFFF00">HTML Fill, then stroke text</span><br />';
$html .= '<span stroke="0" fill="false">HTML Neither fill nor stroke text (invisible)</span><br />';

// output the HTML content
$pdf->writeHTML($html, true, 0, true, 0);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
