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

// ---------- ONLY EDIT THIS AREA --------------------------------

// 1) set the doc title 
    $pdfTitle = 'limePDF Example 001';

// 2) set the Logo   
    $pdfLogo = '../images/limePDF_logo.png';

// 3) set the Text    
    $pdfText = '<br /><br />Example # 001<br /><br /><img src="http://limepdf/examples/images/limePDF_logo.png"><br /><h1>Welcome to <a href="http://www.limePDF.org" style="text-decoration:none;"><span style=";color:#527201">lime</span><span style="color:black;">PDF</span>&nbsp;</a>!</h1><i>This is the first Sample file for the limePDF library.</i><p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p><p>Please check the source code documentation and other examples for further information.</p><br />here<img src="' . $pdfLogo . '">';

// 4) Set Output File Name
$OutputFile = 'example_001.pdf';

//-------- do not edit below (make changes in ConfigManager file) ------------------------------------------------

require_once __DIR__ . '/../../src/PDF.php';
require_once '../../vendor/autoload.php'; 

use LimePDF\Pdf;

$pdf = new Pdf();

use LimePDF\Config\ConfigManager;

// Instantiate and load ConfigManager
$config = new ConfigManager();
$config->loadFromArray([
]);

// Change the $config array vars to be injected or used as necessary
$cfgArray = $config->toArray();
$pdfConfig = [
    'author' => $cfgArray['author'],
    'creator' => $cfgArray['creator'],
    'font' => [
        'main' => [$cfgArray['fontNameMain'], $cfgArray['fontSizeMain']],
        'data' => [$cfgArray['fontNameData'], $cfgArray['fontSizeData']],
        'mono' => $cfgArray['fontMonospaced'],
    ],
    'logo' => [
		'file' => 'test.png',
        //'file' => __DIR__ . '/images/limePDF_logo.png',
		//'file' => $pdfLogo,
        'width' => $cfgArray['headerLogoWidth'],
    ],
    'headerString' => $cfgArray['headerString'],
	'headerLogo' => $cfgArray['headerLogo'],
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
$pdf = new PDF(
    $pdfConfig['layout']['orientation'],
    $pdfConfig['layout']['unit'],
    $pdfConfig['layout']['pageFormat'],
    true,
    'UTF-8',
    false
);

// set document information
$pdf->setCreator( $pdfConfig['creator']);
$pdf->setAuthor($pdfConfig['author']);
$pdf->setTitle($pdfTitle);
$pdf->setSubject($pdfConfig['meta']['subject']);
$pdf->setKeywords($pdfConfig['meta']['keywords']);


// set default header data
if (!file_exists($pdfConfig['headerString'])) {
	//throw new Exception("Logo file not found: " . $pdfConfig['logo']['file']);
}

$pdf->setHeaderData(
	$pdfConfig['headerLogo'],
	$pdfConfig['logo']['width'],
	$pdfTitle,
	$pdfConfig['headerString'],
	array(0,64,255),
	array(0,64,128)
);

$pdf->setFooterData(array(0,64,0), array(0,64,128));

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

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
//$pdf->setFont('helvetica', '', 14, '', true);
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
$pdf->Output($OutputFile, 'I');

//============================================================+
// END OF FILE
//============================================================+
