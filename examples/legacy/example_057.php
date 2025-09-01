<?php 
//============================================================+
// File name   : example_057.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Cell vertical alignments
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_057.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Cell vertical alignments';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'Example of alignment options for Cell()';

		$pdfImage = dirname(__DIR__, 2) . '/examples/images/tcpdf_cell.png';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('helvetica', 'B', 20);

// add a page
$pdf->AddPage();

$pdf->Write(0, $pdfText, '', 0, 'L', true, 0, false, false, 0);

$pdf->setFont('helvetica', '', 11);

// set border width
$pdf->setLineWidth(0.7);

// set color for cell border
$pdf->setDrawColor(0,128,255);

$pdf->setCellHeightRatio(3);

$pdf->setXY(15, 60);

// text on center
$pdf->Cell(30, 0, 'Top-Center', 1, $ln=0, 'C', 0, '', 0, false, 'T', 'C');
$pdf->Cell(30, 0, 'Center-Center', 1, $ln=0, 'C', 0, '', 0, false, 'C', 'C');
$pdf->Cell(30, 0, 'Bottom-Center', 1, $ln=0, 'C', 0, '', 0, false, 'B', 'C');
$pdf->Cell(30, 0, 'Ascent-Center', 1, $ln=0, 'C', 0, '', 0, false, 'A', 'C');
$pdf->Cell(30, 0, 'Baseline-Center', 1, $ln=0, 'C', 0, '', 0, false, 'L', 'C');
$pdf->Cell(30, 0, 'Descent-Center', 1, $ln=0, 'C', 0, '', 0, false, 'D', 'C');


$pdf->setXY(15, 90);

// text on top
$pdf->Cell(30, 0, 'Top-Top', 1, $ln=0, 'C', 0, '', 0, false, 'T', 'T');
$pdf->Cell(30, 0, 'Center-Top', 1, $ln=0, 'C', 0, '', 0, false, 'C', 'T');
$pdf->Cell(30, 0, 'Bottom-Top', 1, $ln=0, 'C', 0, '', 0, false, 'B', 'T');
$pdf->Cell(30, 0, 'Ascent-Top', 1, $ln=0, 'C', 0, '', 0, false, 'A', 'T');
$pdf->Cell(30, 0, 'Baseline-Top', 1, $ln=0, 'C', 0, '', 0, false, 'L', 'T');
$pdf->Cell(30, 0, 'Descent-Top', 1, $ln=0, 'C', 0, '', 0, false, 'D', 'T');


$pdf->setXY(15, 120);

// text on bottom
$pdf->Cell(30, 0, 'Top-Bottom', 1, $ln=0, 'C', 0, '', 0, false, 'T', 'B');
$pdf->Cell(30, 0, 'Center-Bottom', 1, $ln=0, 'C', 0, '', 0, false, 'C', 'B');
$pdf->Cell(30, 0, 'Bottom-Bottom', 1, $ln=0, 'C', 0, '', 0, false, 'B', 'B');
$pdf->Cell(30, 0, 'Ascent-Bottom', 1, $ln=0, 'C', 0, '', 0, false, 'A', 'B');
$pdf->Cell(30, 0, 'Baseline-Bottom', 1, $ln=0, 'C', 0, '', 0, false, 'L', 'B');
$pdf->Cell(30, 0, 'Descent-Bottom', 1, $ln=0, 'C', 0, '', 0, false, 'D', 'B');


// draw some reference lines
$linestyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '', 'phase' => 0, 'color' => array(255, 0, 0));
$pdf->Line(15, 60, 195, 60, $linestyle);
$pdf->Line(15, 90, 195, 90, $linestyle);
$pdf->Line(15, 120, 195, 120, $linestyle);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// Print an image to explain cell measures

$pdf->Image($pdfImage, 15, 160, 100, 100, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);
$legend = 'LEGEND:

X: cell x top-left origin (top-right for RTL)
Y: cell y top-left origin (top-right for RTL)
CW: cell width
CH: cell height
LW: line width
NRL: normal line position
EXT: external line position
INT: internal line position
ML: margin left
MR: margin right
MT: margin top
MB: margin bottom
PL: padding left
PR: padding right
PT: padding top
PB: padding bottom
TW: text width
FA: font ascent
FB: font baseline
FD: font descent';
$pdf->setFont('helvetica', '', 10);
$pdf->setCellHeightRatio(1.25);
$pdf->MultiCell(0, 0, $legend, 0, 'L', false, 1, 125, 160, true, 0, false, true, 0, 'T', false);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// CELL BORDERS

// add a page
$pdf->AddPage();

$pdf->setFont('helvetica', 'B', 20);

$pdf->Write(0, 'Example of borders for Cell()', '', 0, 'L', true, 0, false, false, 0);

$pdf->setFont('helvetica', '', 11);

// set border width
$pdf->setLineWidth(0.508);

// set color for cell border
$pdf->setDrawColor(0,128,255);

// set filling color
$pdf->setFillColor(255,255,128);

// set cell height ratio
$pdf->setCellHeightRatio(3);

$pdf->Cell(30, 0, '1', 1, 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'LTRB', 'LTRB', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'LTR', 'LTR', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'TRB', 'TRB', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'LRB', 'LRB', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'LTB', 'LTB', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'LT', 'LT', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'TR', 'TR', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'RB', 'RB', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'LB', 'LB', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'LR', 'LR', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'TB', 'TB', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'L', 'L', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'T', 'T', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'R', 'R', 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(2);
$pdf->Cell(30, 0, 'B', 'B', 1, 'C', 1, '', 0, false, 'T', 'C');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// ADVANCED SETTINGS FOR CELL BORDERS

// add a page
$pdf->AddPage();

$pdf->setFont('helvetica', 'B', 20);

$pdf->Write(0, 'Example of advanced border settings for Cell()', '', 0, 'L', true, 0, false, false, 0);

$pdf->setFont('helvetica', '', 11);

// set border width
$pdf->setLineWidth(1);

// set color for cell border
$pdf->setDrawColor(0,128,255);

// set filling color
$pdf->setFillColor(255,255,128);

$border = array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0)));
$pdf->Cell(30, 0, 'LTRB', $border, 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(5);

$border = array(
'L' => array('width' => 2, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0)),
'R' => array('width' => 2, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 255)),
'T' => array('width' => 2, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 255, 0)),
'B' => array('width' => 2, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 255)));
$pdf->Cell(30, 0, 'LTRB', $border, 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(5);

$border = array('mode' => 'ext', 'LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0)));
$pdf->Cell(30, 0, 'LTRB EXT', $border, 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(5);

$border = array('mode' => 'int', 'LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0)));
$pdf->Cell(30, 0, 'LTRB INT', $border, 1, 'C', 1, '', 0, false, 'T', 'C');
$pdf->Ln(5);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
