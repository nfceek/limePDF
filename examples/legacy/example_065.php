<?php 
//============================================================+
// File name   : example_065.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Creates an example PDF/A-3b document using LimePDF
//               
//
// Last Update : 9-1-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_065.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Creates an example PDF/A-3b document using LimePDF';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = '<h1>Example of <a href="limepdf.com" style="text-decoration:none;background-color:#fff;color:black;">
		&nbsp;<span style="color:#527201">Lime</span><span style="color:black;">PDF</span>&nbsp;</a> document in <span style="background-color:#99ccff;color:black;"> 
		PDF/A-3b </span> mode.</h1><i>This document conforms to the standard <b>PDF/A-3b (ISO 19005-3:2012)</b>.</i> <p>Please check the source code documentation 
		and other examples for further information (<a href="limepdf.com">limepdf.com</a>).</p> <p style="color:#CC0000;"></a></p>';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 


// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
$pdf->setFont('helvetica', '', 14, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// Set some content to print


// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $pdfText, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
