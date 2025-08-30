<?php
//============================================================+
// File name   : example_003.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Custom page header and footer
//               
//
// Last Update : 8-26-2025
//============================================================+

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use LimePDF\Pdf;
use LimePDF\Config\ConfigManager;

//-------- do not edit above (make changes in ConfigManager file) ------------------------------------------------

// 1) set Output File Name
	$outputFile = 'example_003.pdf';

// 2) set Output type ( I = In Browser & D = Download )
	$outputType = 'I';

// 3) set Text
	$pdfText = "LimePDF Example 003\n\n";
	$pdfText .= "Custom page header and footer are defined by extending the PDF class\n\n and overriding the Header() and Footer() methods.";

// 4) Edit the loadFromArray vars as needed	
$config = new ConfigManager();
$config->loadFromArray([
    'headerLogo'      => __DIR__ . '/../images/limePDF_logo.png',
	'headerLogoType'  => 'PNG',	// PNG | JPG
    'headerLogoWidth' => 20,
    'headerTitle'     => 'LimePDF Example 003',
]);

// 5) Set the Header logo
    $imgHeader = dirname(__DIR__) . '/images/limePDF_logo.png';

// 6) Set a Logo   
    $pdfLogo = dirname(__DIR__) . '/images/limePDF_logo.png';

// ---------- Dont Edit below here ----------------------------------------------------------------------------
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

class MyPdf extends Pdf
{
    protected ConfigManager $config;

    public function __construct(ConfigManager $config)
    {
        parent::__construct();
        $this->config = $config;
    }

    // Page header
    public function Header(): void
    {
        $logo = $this->config->get('headerLogo');
        $logoWidth = (float) $this->config->get('headerLogoWidth', 20);
		$logoType = $this->config->get('headerLogoType');

        if ($logo && file_exists($logo)) {
            $this->Image(
                $logo,
                10, 10,
                $logoWidth,
                '', $logoType,
                '', 'T',
                false, 300,
                '', false, false, 0, false, false, false
            );
        }

        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 15, $this->config->get('headerTitle', ''), 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(
            0, 10,
            'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(),
            0, false, 'C', 0, '', 0, false, 'T', 'M'
        );
    }
}

// -----------------------------------------------------------------------------
$pdf = new MyPdf($config);

// set document information
$pdf->setCreator( $pdfConfig['creator']);
$pdf->setAuthor($pdfConfig['author']);
$pdf->setTitle($pdfConfig['title']);
$pdf->setSubject($pdfConfig['meta']['subject']);
$pdf->setKeywords($pdfConfig['meta']['keywords']);

// remove default header/footer
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

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


$pdf->SetFont('times', '', 12);

// Add Page + Content
$pdf->AddPage();
$pdf->Write(0, $pdfText, '', false, 'C', true, 0, false, false, 0);

// Output
//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
