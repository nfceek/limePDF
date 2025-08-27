<?php
//============================================================+
// File name   : example_004.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Cell stretching
//               
//
// Last Update : 8-27-2025
//============================================================+


require_once __DIR__ . '/../../src/PDF.php';
require_once '../../vendor/autoload.php'; 

use LimePDF\Pdf;

$pdf = new Pdf();

use LimePDF\Config\ConfigManager;

// Instantiate and load ConfigManager
$config = new ConfigManager();
$config->loadFromArray([
]);

//-------- do not edit above (make changes in ConfigManager file) ------------------------------------------------

// 1) set Output File Name
	$outputFile = 'example_004.pdf';

// 2) set Output type ( I = In Browser & D = Download )
	$outputType = 'I';

// 3) set text for cell(s)
	$pdfText = 'TEST CELL STRETCH:';

// 4) Set the doc Title 
    $pdfTitle = 'limePDF Example 001';

// 5) Set the Header logo
    $imgHeader = dirname(__DIR__) . '/images/limePDF_logo.png';

// 56) Set a Logo   
    $pdfLogo = dirname(__DIR__) . '/images/limePDF_logo.png';

// ---------- Dont Edit below here -----------------------------

$cfgArray = $config->toArray();
$pdfConfig = [
    'author' => $cfgArray['author'],
    'creator' => $cfgArray['creator'],
	'title' => $cfgArray['title'],
    'font' => [
        'main' => [$cfgArray['fontNameMain'], $cfgArray['fontSizeMain']],
        'data' => [$cfgArray['fontNameData'], $cfgArray['fontSizeData']],
        'mono' => $cfgArray['fontMonospaced'],
    ],  
	'headerString' => $cfgArray['headerString'],
    'headerLogoWidth' => $cfgArray['headerLogoWidth'],  
    'margins' => [
        'header' => $cfgArray['marginHeader'],
        'footer' => $cfgArray['marginFooter'],
        'top'    => $cfgArray['marginTop'],
        'bottom' => $cfgArray['marginBottom'],
        'left'   => $cfgArray['marginLeft'],
        'right'  => $cfgArray['marginRight'],
    ],
    'layout' => [
        'pageFormat' => $cfgArray['pageFormat'],
        'orientation' => $cfgArray['pageOrientation'],
        'unit' => $cfgArray['unit'],
        'imageScale' => $cfgArray['imageScaleRatio'],
    ],
    'meta' => [
        'subject' => $cfgArray['subject'],
        'keywords' => $cfgArray['keywords'],
    ]
];

// create new PDF document
$pdf = new PDF($pdfConfig['layout']['orientation'], $pdfConfig['layout']['unit'], $pdfConfig['layout']['pageFormat'], true, 'UTF-8', false);

// set document information
$pdf->setCreator( $pdfConfig['creator']);
$pdf->setAuthor($pdfConfig['author']);
$pdf->setTitle($pdfConfig['title']);
$pdf->setSubject($pdfConfig['meta']['subject']);
$pdf->setKeywords($pdfConfig['meta']['keywords']);

// set default header data
//$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 004', PDF_HEADER_STRING);
$pdf->setHeaderData(
	$imgHeader,
	$pdfConfig['headerLogoWidth'],
	$pdfTitle,
	$pdfConfig['headerString'],
	array(0,64,255),
	array(0,64,128)
);

// set header and footer fonts
$pdf->setHeaderFont([
	$pdfConfig['font']['main'][0],
	'',
	$pdfConfig['font']['main'][1]]
);

$pdf->setFooterFont([
	$pdfConfig['font']['data'][0],
	'',
	$pdfConfig['font']['data'][1]]
);

// set default monospaced font
$pdf->setDefaultMonospacedFont($pdfConfig['font']['mono']);

// set margins
$pdf->setMargins(
	$pdfConfig['margins']['left'], 
	$pdfConfig['margins']['top'], 
	$pdfConfig['margins']['right']
);

$pdf->setHeaderMargin($pdfConfig['margins']['header']);
$pdf->setFooterMargin($pdfConfig['margins']['footer']);

// set auto page breaks
$pdf->setAutoPageBreak(TRUE, $pdfConfig['margins']['bottom']);

// set image scale factor
$pdf->setImageScale($pdfConfig['layout']['imageScale']);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->setFont('times', '', 11);

// add a page
$pdf->AddPage();

//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')

// test Cell stretching
$pdf->Cell(0, 0, $pdfText . ' no stretch', 1, 1, 'C', 0, '', 0);
$pdf->Cell(0, 0, $pdfText . ' scaling', 1, 1, 'C', 0, '', 1);
$pdf->Cell(0, 0, $pdfText . ' force scaling', 1, 1, 'C', 0, '', 2);
$pdf->Cell(0, 0, $pdfText . ' spacing', 1, 1, 'C', 0, '', 3);
$pdf->Cell(0, 0, $pdfText . ' force spacing', 1, 1, 'C', 0, '', 4);

$pdf->Ln(5);

$pdf->Cell(45, 0, $pdfText . ' scaling', 1, 1, 'C', 0, '', 1);
$pdf->Cell(45, 0, $pdfText . ' force scaling', 1, 1, 'C', 0, '', 2);
$pdf->Cell(45, 0, $pdfText . ' spacing', 1, 1, 'C', 0, '', 3);
$pdf->Cell(45, 0, $pdfText . ' force spacing', 1, 1, 'C', 0, '', 4);

$pdf->AddPage();

// example using general stretching and spacing

for ($stretching = 90; $stretching <= 110; $stretching += 10) {
	for ($spacing = -0.254; $spacing <= 0.254; $spacing += 0.254) {

		// set general stretching (scaling) value
		$pdf->setFontStretching($stretching);

		// set general spacing value
		$pdf->setFontSpacing($spacing);

		$pdf->Cell(0, 0, 'Stretching '.$stretching.'%, Spacing '.sprintf('%+.3F', $spacing).'mm, no stretch', 1, 1, 'C', 0, '', 0);
		$pdf->Cell(0, 0, 'Stretching '.$stretching.'%, Spacing '.sprintf('%+.3F', $spacing).'mm, scaling', 1, 1, 'C', 0, '', 1);
		$pdf->Cell(0, 0, 'Stretching '.$stretching.'%, Spacing '.sprintf('%+.3F', $spacing).'mm, force scaling', 1, 1, 'C', 0, '', 2);
		$pdf->Cell(0, 0, 'Stretching '.$stretching.'%, Spacing '.sprintf('%+.3F', $spacing).'mm, spacing', 1, 1, 'C', 0, '', 3);
		$pdf->Cell(0, 0, 'Stretching '.$stretching.'%, Spacing '.sprintf('%+.3F', $spacing).'mm, force spacing', 1, 1, 'C', 0, '', 4);

		$pdf->Ln(2);
	}
}

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
