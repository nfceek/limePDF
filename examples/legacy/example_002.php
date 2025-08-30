<?php
//============================================================+
// File name   : example_002.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Default page header and footer are disabled
//               
//
// Last Update : 8-26-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

//--Do Not Edit Above - make changes in ConfigManager or Bootstrap file) --

// 1) What is the File name to create:
    $outputFile = 'example_001.pdf';

// 2) set Output type ( I = In Browser & D = Download )
    $outputType = 'I';

// 3) set Text
	$pdfText = "LimePDF Example 002\n\n";
	$pdfText .= "Default page header and footer are disabled using\nsetPrintHeader()\nand\nsetPrintFooter()\nmethods.";

// ---------- Dont Edit below here ---------------------------------------

// filename + type
$pdf = PdfBootstrap::create($outputFile, $outputType ); 

// set font
$pdf->setFont('times', 'BI', 20);

// add a page
$pdf->AddPage();

// set some text to print
$txt = $pdfText;

// print a block of text using Write()
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
