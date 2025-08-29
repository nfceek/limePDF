<?php
//============================================================+
// File name   : example_017.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Two independent columns with MultiCell
//               
//               
//
// Last Update : 8-29-2025
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

// ---------- ONLY EDIT THIS AREA --------------------------------

// 1) set Output File Name
	$outputFile = 'example_017.pdf';

// 2) set Output type ( I = In Browser & D = Download )
	$outputType = 'I';

// 3) Set the doc Title 
    $pdfTitle = 'limePDF Example 012';

// 4) Set the Header logo
    $imgHeader = dirname(__DIR__) . '/images/limePDF_logo.png';

// 5) Set a Logo   
    $pdfLogo = dirname(__DIR__) . '/images/limePDF_logo.png'; 

// 6) column data
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

// remove default header/footer
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

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
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
