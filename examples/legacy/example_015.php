<?php 
//============================================================+
// File name   : example_015.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Bookmarks (Table of Content) and Named Destinations
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_015.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Bookmarks (Table of Content) and Named Destinations.';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'TEST CELL STRETCH:';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// Bookmark($txt, $level=0, $y=-1, $page='', $style='', $color=array(0,0,0))

// set font
$pdf->setFont('times', 'B', 20);

// add a page
$pdf->AddPage();

// set a bookmark for the current position
$pdf->Bookmark('Chapter 1', 0, 0, '', 'B', array(0,64,128));

// print a line using Cell()
$pdf->Cell(0, 10, 'Chapter 1', 0, 1, 'L');

$pdf->setFont('times', 'I', 14);
$pdf->Write(0, 'You can set PDF Bookmarks using the Bookmark() method.
You can set PDF Named Destinations using the setDestination() method.');

$pdf->setFont('times', 'B', 20);

// add other pages and bookmarks

$pdf->AddPage();
$pdf->Bookmark('Paragraph 1.1', 1, 0, '', '', array(0,0,0));
$pdf->Cell(0, 10, 'Paragraph 1.1', 0, 1, 'L');

$pdf->AddPage();
$pdf->Bookmark('Paragraph 1.2', 1, 0, '', '', array(0,0,0));
$pdf->Cell(0, 10, 'Paragraph 1.2', 0, 1, 'L');

$pdf->AddPage();
$pdf->Bookmark('Sub-Paragraph 1.2.1', 2, 0, '', 'I', array(0,0,0));
$pdf->Cell(0, 10, 'Sub-Paragraph 1.2.1', 0, 1, 'L');

$pdf->AddPage();
$pdf->Bookmark('Paragraph 1.3', 1, 0, '', '', array(0,0,0));
$pdf->Cell(0, 10, 'Paragraph 1.3', 0, 1, 'L');

$pdf->AddPage();
// add a named destination so you can open this document at this page using the link: "example_015.pdf#chapter2"
$pdf->setDestination('chapter2', 0, '');
// add a bookmark that points to a named destination
$pdf->Bookmark('Chapter 2', 0, 0, '', 'BI', array(128,0,0), -1, '#chapter2');
$pdf->Cell(0, 10, 'Chapter 2', 0, 1, 'L');
$pdf->setFont('times', 'I', 14);
$pdf->Write(0, 'Once saved, you can open this document at this page using the link: "example_015.pdf#chapter2".');

$pdf->AddPage();
$pdf->setDestination('chapter3', 0, '');
$pdf->setFont('times', 'B', 20);
$pdf->Bookmark('Chapter 3', 0, 0, '', 'B', array(0,64,128));
$pdf->Cell(0, 10, 'Chapter 3', 0, 1, 'L');

$pdf->AddPage();
$pdf->setDestination('chapter4', 0, '');
$pdf->setFont('times', 'B', 20);
$pdf->Bookmark('Chapter 4', 0, 0, '', 'B', array(0,64,128));
$pdf->Cell(0, 10, 'Chapter 4', 0, 1, 'L');

$pdf->AddPage();
$pdf->Bookmark('Chapter 5', 0, 0, '', 'B', array(0,128,0));
$pdf->Cell(0, 10, 'Chapter 5', 0, 1, 'L');
$txt = 'Example of File Attachment.
Double click on the icon to open the attached file.';
$pdf->setFont('helvetica', '', 10);
$pdf->Write(0, $txt, '', 0, 'L', true, 0, false, false, 0);

// attach an external file TXT file
$pdf->Annotation(20, 50, 5, 5, 'TXT file', array('Subtype'=>'FileAttachment', 'Name' => 'PushPin', 'FS' => 'data/utf8test.txt'));

// attach an external file
$pdf->Annotation(50, 50, 5, 5, 'PDF file', array('Subtype'=>'FileAttachment', 'Name' => 'PushPin', 'FS' => 'example_012.pdf'));

// add a bookmark that points to an embedded file
// NOTE: prefix the file name with the * character for generic file and with % character for PDF file
$pdf->Bookmark('TXT file', 0, 0, '', 'B', array(128,0,255), -1, '*utf8test.txt');

// add a bookmark that points to an embedded file
// NOTE: prefix the file name with the * character for generic file and with % character for PDF file
$pdf->Bookmark('PDF file', 0, 0, '', 'B', array(128,0,255), -1, '%example_012.pdf');

// add a bookmark that points to an external URL
$pdf->Bookmark('External URL', 0, 0, '', 'B', array(0,0,255), -1, 'http://www.tcpdf.org');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
