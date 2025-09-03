<?php 
//============================================================+
// File name   : example_010.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Text on multiple columns
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\PDF;
use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_010.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Text on multiple columns';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	// Set Text
		$PrintText = '../data/chapter_demo_1.txt';

	// Set HTML
		$PrintHtml = '../data/chapter_demo_2.txt';

// ---------- Dont Edit below here -----------------------------

    class CustomPdf extends PDF
    {
	/**
	 * Print chapter
	 * @param int $num chapter number
	 * @param string $title chapter title
	 * @param string $file name of the file containing the chapter body
	 * @param boolean $mode if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function PrintChapter($num, $title, $file, $mode=false) {
		// add a new page
		$this->AddPage();
		// disable existing columns
		$this->resetColumns();
		// print chapter title
		$this->ChapterTitle($num, $title);
		// set columns
		$this->setEqualColumns(3, 57);
		// print chapter body
		$this->ChapterBody($file, $mode);
	}

	/**
	 * Set chapter title
	 * @param int $num chapter number
	 * @param string $title chapter title
	 * @public
	 */
	public function ChapterTitle($num, $title) {
		$this->setFont('helvetica', '', 14);
		$this->setFillColor(200, 220, 255);
		$this->Cell(180, 6, 'Chapter '.$num.' : '.$title, 0, 1, '', 1);
		$this->Ln(4);
	}

	/**
	 * Print chapter body
	 * @param string $file name of the file containing the chapter body
	 * @param boolean $mode if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function ChapterBody($file, $mode=false) {
		$this->selectColumn();
		// get esternal file content
		$content = file_get_contents($file, false);
		// set font
		$this->setFont('times', '', 9);
		$this->setTextColor(50, 50, 50);
		// print content
		if ($mode) {
			// ------ HTML MODE ------
			$this->writeHTML($content, true, false, true, false, 'J');
		} else {
			// ------ TEXT MODE ------
			$this->Write(0, $content, '', 0, 'J', true, 0, false, true, 0);
		}
		$this->Ln();
	}
} // end of extended class

// ---------------------------------------------------------
// EXAMPLE
// ---------------------------------------------------------
// create new PDF document
$pdf = new CustomPdf('P', 'mm', 'A4', true, 'UTF-8', false);

// send form parameters 
//$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// ---------------------------------------------------------

// print TEXT
$pdf->PrintChapter(1, 'LOREM IPSUM [TEXT]', $PrintText, false);

// print HTML
$pdf->PrintChapter(2, 'LOREM IPSUM [HTML]', $PrintHtml, true);

// ---------------------------------------------------------

$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
