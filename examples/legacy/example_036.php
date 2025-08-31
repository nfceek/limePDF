<?php 
//============================================================+
// File name   : example_036.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Annotations
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_036.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Annotations';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'Example of Text Annotation. Move your mouse over the yellow box or double click on it to display the annotation text.';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('times', '', 16);

// add a page
$pdf->AddPage();

$pdf->Write(0, $pdfText, '', 0, 'L', true, 0, false, false, 0);

// text annotation
$pdf->Annotation(83, 27, 10, 10, "Text annotation example\naccented letters test: àèéìòù", array('Subtype'=>'Text', 'Name' => 'Comment', 'T' => 'title example', 'Subj' => 'example', 'C' => array(255, 255, 0)));

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
