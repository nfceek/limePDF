<?php 
//============================================================+
// File name   : example_062.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : XObject Template
//               
//
// Last Update : 9-1-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_062.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'XObject Template';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'XObject Templates';

		$pdfImage = dirname(__DIR__, 2) . '/examples/images/image_demo.jpg';
// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 


// set font
$pdf->setFont('helvetica', 'B', 20);

// add a page
$pdf->AddPage();

$pdf->Write(0, $pdfText, '', 0, 'C', 1, 0, false, false, 0);

/*
 * An XObject Template is a PDF block that is a self-contained
 * description of any sequence of graphics objects (including path
 * objects, text objects, and sampled images).
 * An XObject Template may be painted multiple times, either on
 * several pages or at several locations on the same page and produces
 * the same results each time, subject only to the graphics state at
 * the time it is invoked.
 */


// start a new XObject Template and set transparency group option
$template_id = $pdf->startTemplate(60, 60, true);

// create Template content
// ...................................................................
//Start Graphic Transformation
$pdf->StartTransform();

// set clipping mask
$pdf->StarPolygon(30, 30, 29, 10, 3, 0, 1, 'CNZ');

// draw jpeg image to be clipped
$pdf->Image($pdfImage, 0, 0, 60, 60, '', '', '', true, 72, '', false, false, 0, false, false, false);

//Stop Graphic Transformation
$pdf->StopTransform();

$pdf->setXY(0, 0);

$pdf->setFont('times', '', 40);

$pdf->setTextColor(255, 0, 0);

// print a text
$pdf->Cell(60, 60, 'Template', 0, 0, 'C', false, '', 0, false, 'T', 'M');
// ...................................................................

// end the current Template
$pdf->endTemplate();


// print the selected Template various times using various transparencies

$pdf->setAlpha(0.4);
$pdf->printTemplate($template_id, 15, 50, 20, 20, '', '', false);

$pdf->setAlpha(0.6);
$pdf->printTemplate($template_id, 27, 62, 40, 40, '', '', false);

$pdf->setAlpha(0.8);
$pdf->printTemplate($template_id, 55, 85, 60, 60, '', '', false);

$pdf->setAlpha(1);
$pdf->printTemplate($template_id, 95, 125, 80, 80, '', '', false);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
