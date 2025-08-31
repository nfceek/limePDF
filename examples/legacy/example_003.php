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
// Last Update : 8-30-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
use LimePDF\Pdf;

$pdf = new Pdf();
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_002.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = false;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Custom page header and footer';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = false;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

//  Set Text
	$pdfText = "LimePDF Example 003\n\n";
	$pdfText .= "Custom page header and footer are defined by extending the PDF class\n\n and overriding the Header() and Footer() methods.";

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

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
