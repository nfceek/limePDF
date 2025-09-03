<?php 
//============================================================+
// File name   : example_039.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : HTML justification
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_039.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'HTML justification';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'Example of HTML Justification';

		$imageFile  = realpath(dirname(__DIR__, 2) . '/examples/images/logo_example.png');
		$imageFile2 = realpath(dirname(__DIR__, 2) . '/examples/images/logo_example.tcpdf_box.svg');

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); //============================================================+

// add a page
$pdf->AddPage();

// set font
$pdf->setFont('helvetica', 'B', 20);

$pdf->Write(0, $pdfText, '', 0, 'L', true, 0, false, false, 0);

// create some HTML content
$html = '<span style="text-align:justify;">a <u>abc</u> abcdefghijkl (abcdef) abcdefg <b>abcdefghi</b> a ((abc)) abcd ';
$html .= '<img src="' . $imageFile . '" border="0" height="41" width="41" />  ';
$html .= '<img src="' . $imageFile2 . '" alt="test alt attribute" width="80" height="60" border="0" /> ';
$html .= 'abcdef abcdefg <b>abcdefghi</b> a abc abcd abcdef abcdefg <b>abcdefghi</b> a abc abcd abcdef abcdefg ';
$html .= '<b>abcdefghi</b> a <u>abc</u> abcd abcdef abcdefg <b>abcdefghi</b> a abc \(abcd\) abcdef abcdefg ';
$html .= '<b>abcdefghi</b> a abc \\\(abcd\\\) abcdef abcdefg <b>abcdefghi</b> a abc abcd abcdef abcdefg ';
$html .= '<b>abcdefghi</b> a abc abcd abcdef abcdefg <b>abcdefghi</b> a abc abcd abcdef abcdefg abcdefghi a '; 
$html .= 'abc abcd <a href="http://limepdf.com">abcdef abcdefg</a> start a abc before ';
$html .= '<span style="background-color:yellow">yellow color</span> after a abc abcd abcdef abcdefg abcdefghi a abc abcd end ';
$html .= 'abcdefg abcdefghi a abc abcd abcdef abcdefg abcdefghi a abc abcd abcdef abcdefg abcdefghi a abc abcd abcdef abcdefg '; 
$html .= 'abcdefghi a abc abcd abcdef abcdefg abcdefghi a abc abcd abcdef abcdefg abcdefghi a abc abcd abcdef abcdefg abcdefghi ';
$html .= 'a abc abcd abcdef abcdefg abcdefghi<br />abcd abcdef abcdefg abcdefghi<br />abcd abcde abcdef</span>';

// set core font
$pdf->setFont('helvetica', '', 10);

// output the HTML content
$pdf->writeHTML($html, true, 0, true, true);

$pdf->Ln();

// set UTF-8 Unicode font
$pdf->setFont('dejavusans', '', 10);

// output the HTML content
$pdf->writeHTML($html, true, 0, true, true);

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
