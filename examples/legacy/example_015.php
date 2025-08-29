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
// Description : Bookmarks (Table of Content) and Named Destinations.
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
	$outputFile = 'example_015.pdf';

// 2) set Output type ( I = In Browser & D = Download )
	$outputType = 'D';

// 3) Set the doc Title 
    $pdfTitle = 'limePDF Example 015';

// 4) Set the Header logo
    $imgHeader = dirname(__DIR__) . '/images/limePDF_logo.png';

// 5) Set a Logo   
    $pdfLogo = dirname(__DIR__) . '/images/limePDF_logo.png'; 

// 6) Set the Text    
    $pdfText1 = 'You can set PDF Bookmarks using the Bookmark() method. You can set PDF Named Destinations using the setDestination() method.';

	$pdfText2 = 'Once saved, you can open this document at this page using the link: "example_015.pdf#chapter2".';

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
$pdf->Write(0, $pdfText1);

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
$pdf->Write(0, $pdfText2);

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
$pdf->Bookmark('External URL', 0, 0, '', 'B', array(0,0,255), -1, 'https://limepdf.com');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
