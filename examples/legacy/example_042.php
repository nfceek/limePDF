<?php 
//============================================================+
// File name   : example_042.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Test Image with alpha channel
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_042.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Test Image with alpha channel';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'LimePDF test PNG Alpha Channel ';
		$pdfImages1 = dirname(__DIR__, 2) . 'images/image_with_alpha.png';
		$pdfImages2 = dirname(__DIR__, 2) . 'images/alpha.png';
		$pdfImages3 = dirname(__DIR__, 2) . 'images/img.png';
		$pdfHook = 'https://www.limepdf.com';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set JPEG quality
//$pdf->setJPEGQuality(75);

$pdf->setFont('helvetica', '', 18);

// add a page
$pdf->AddPage();

// create background text
$background_text = str_repeat($pdfText, 50);
$pdf->MultiCell(0, 5, $background_text, 0, 'J', 0, 2, '', '', true, 0, false);

// --- Method (A) ------------------------------------------
// the Image() method recognizes the alpha channel embedded on the image:

$pdf->Image($pdfImages1, 50, 50, 100, '', '', $pdfHook, '', false, 300);

// --- Method (B) ------------------------------------------
// provide image + separate 8-bit mask

// first embed mask image (w, h, x and y will be ignored, the image will be scaled to the target image's size)
$mask = $pdf->Image($pdfImages2, 50, 140, 100, '', '', '', '', false, 300, '', true);

// embed image, masked with previously embedded mask
$pdf->Image($pdfImages1, 50, 140, 100, '', '', $pdfHook, '', false, 300, '', false, $mask);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
