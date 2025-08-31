<?php 
//============================================================+
// File name   : example_047.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Transactions
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_047.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Transactions';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'Example of Transactions. LimePDF allows you to undo some operations using the Transactions. Check the source code for further information.';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set some language-dependent strings (optional)
if (@file_exists(dirname(__DIR__, 2) . '/examples/lang/eng.php')) {
	require_once(dirname(__DIR__, 2) . '/examples/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->setFont('helvetica', '', 16);

// add a page
$pdf->AddPage();

$pdf->Write(0, $pdfText, '', 0, 'L', true, 0, false, false, 0);

$pdf->Ln(5);

$pdf->setFont('times', '', 12);

// start transaction
$pdf->startTransaction();

$pdf->Write(0, "LINE 1\n");
$pdf->Write(0, "LINE 2\n");

// restarts transaction
$pdf->startTransaction();

$pdf->Write(0, "LINE 3\n");
$pdf->Write(0, "LINE 4\n");

// rolls back to the last (re)start
$pdf = $pdf->rollbackTransaction();

$pdf->Write(0, "LINE 5\n");
$pdf->Write(0, "LINE 6\n");

// start transaction
$pdf->startTransaction();

$pdf->Write(0, "LINE 7\n");

// commit transaction (actually just frees memory)
$pdf->commitTransaction();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
