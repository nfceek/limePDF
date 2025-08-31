<?php 
//============================================================+
// File name   : example_024.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Object Visibility and Layers
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_024.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Object Visibility and Layers';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'You can limit the visibility of PDF objects to screen or printer by using the setVisibility() method.' .
					'Check the print preview of this document to display the alternative text.';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 


// set font
$pdf->setFont('times', '', 18);

// add a page
$pdf->AddPage();

/*
 * setVisibility() allows to restrict the rendering of some
 * elements to screen or printout. This can be useful, for
 * instance, to put a background image or color that will
 * show on screen but won't print.
 */

$pdf->Write(0, $pdfText, '', 0, '', true, 0, false, false, 0);

// change font size
$pdf->setFontSize(40);

// change text color
$pdf->setTextColor(0,63,127);

// set visibility only for screen
$pdf->setVisibility('screen');

// write something only for screen
$pdf->Write(0, '[This line is for display]', '', 0, 'C', true, 0, false, false, 0);

// set visibility only for print
$pdf->setVisibility('print');

// change text color
$pdf->setTextColor(127,0,0);

// write something only for print
$pdf->Write(0, '[This line is for printout]', '', 0, 'C', true, 0, false, false, 0);

// restore visibility
$pdf->setVisibility('all');

// ---------------------------------------------------------

// LAYERS

// start a new layer
$pdf->startLayer('layer1', true, true);

// change font size
$pdf->setFontSize(18);

// change text color
$pdf->setTextColor(0,127,0);

$txt = 'Using the startLayer() method you can group PDF objects into layers.
This text is on "layer1".';

// write something
$pdf->Write(0, $txt, '', 0, 'L', true, 0, false, false, 0);

// close the current layer
$pdf->endLayer();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
