<?php
//============================================================+
// File name   : example_017.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 017 for TCPDF class
//               Two independent columns with MultiCell
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Two independent columns with MultiCell
 * @author Nicola Asuni
 * @since 2008-03-04
 * @group column
 * @group cell
 * @group pdf
 */
// ---------- ONLY EDIT THIS AREA --------------------------------

// set Output File Name
$OutputFile = 'example_017.pdf';

$LeftColumn = 'left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column left column left column left column left column' . 
' left column left column left column left column left column left column left column';

$RightColumn = 'right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column right column right column right column right column' . 
' right column right column right column right column right column right column right column';


// Include the main TCPDF library (search for installation path).
require_once __DIR__ . '/../../tcpdf.php';
require_once '../../vendor/autoload.php'; 

use LimePDF\TCPDF;
use LimePDF\Config\ConfigManager;

// Instantiate and load ConfigManager
$config = new ConfigManager();
$config->loadFromArray([
]);

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('Nicola Asuni');
$pdf->setTitle('TCPDF Example 017');
$pdf->setSubject('TCPDF Tutorial');
$pdf->setKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 017', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->setFont('helvetica', '', 20);

// add a page
$pdf->AddPage();

$pdf->Write(0, 'Example of independent Multicell() columns', '', 0, 'L', true, 0, false, false, 0);

$pdf->Ln(5);

$pdf->setFont('times', '', 12);

// create columns content
// create columns content
$left_column = '[LEFT COLUMN]' . $LeftColumn . "\n";

$right_column = '[RIGHT COLUMN]'. $RightColumn  . "\n";

// set color for background
$pdf->setFillColor(255, 255, 200);

// set color for text
$pdf->setTextColor(0, 63, 127);

// write the first column
$pdf->MultiCell(80, 0, $left_column, 1, 'J', 1, 0, '', '', true, 0, false, true, 0);

// set color for background
$pdf->setFillColor(215, 235, 255);

// set color for text
$pdf->setTextColor(127, 31, 0);

// write the second column
$pdf->MultiCell(80, 0, $right_column, 1, 'J', 1, 1, '', '', true, 0, false, true, 0);

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($OutputFile, 'I');

//============================================================+
// END OF FILE
//============================================================+
