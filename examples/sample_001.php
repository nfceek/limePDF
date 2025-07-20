<?php
//============================================================+
// File name   : sample_001.php
// Begin       : 2025-07-19
// Last Update : 2025-07-19
//
// Description : Sample 001 for limePDF class
//               Default Header and Footer
//
// Author: Brad Smith
//
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//============================================================+

use LimePDF\Config\ConfigManager;

require_once '../vendor/autoload.php'; 



// Instantiate and load ConfigManager
$config = new ConfigManager();
$config->loadFromArray([
]);

/* Dump the internal config array for verification
echo "<pre>";
print_r($config->toArray()); // Assumes you have a toArray() method in ConfigManager
echo "</pre>";
*/

// set the doc title & test text
$pdfTitle = 'limePDF Sample 001';
$pdfLogo = './images/limePDF_logo.png';
$pdfText = 'Sample # 001<br><h1>Welcome to <a href="http://www.limePDF.org" style="text-decoration:none;"><span style=";color:#527201">lime</span><span style="color:black;">PDF</span>&nbsp;</a>!</h1><i>This is the first Sample file for the limePDF library.</i><p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p><p>Please check the source code documentation and other examples for further information.</p>';


// Change the $config array ars to be injected or used as necessary
$cfgArray = $config->toArray();
	$author = $cfgArray['author'];

	$creator = $cfgArray['creator'];

	$headerLogoWidth = $cfgArray['headerlogowidth'];
	$headerString = $cfgArray['headerstring'];
	$keywords = $cfgArray['keywords'];

	$pageFormat = $cfgArray['pageformat'];
	$pageOrientation = $cfgArray['pageorientation'];


	$subject = $cfgArray['subject'];
	$unit = $cfgArray['unit'];

//-------- do not edit below ------------------------------------------------

// create new PDF document
$pdf = new TCPDF($pageOrientation, $unit, $pageFormat, true, 'UTF-8', false);

// set document information
$pdf->setCreator($creator);
$pdf->setAuthor($author);
$pdf->setTitle($pdfTitle);
$pdf->setSubject($subject);
$pdf->setKeywords($keywords);

// set default header data
$pdf->setHeaderData($pdfLogo, $headerLogoWidth, $pdfTitle, $headerString, array(0,64,255), array(0,64,128));

$pdf->setFooterData(array(0,64,0), array(0,64,128));

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

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->setFont('dejavusans', '', 14, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// set text shadow effect
$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

// Set some content to print
$html = $pdfText;

// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('sample_001.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
