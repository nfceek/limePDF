<?php
//============================================================+
// File name   : example_001.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Default Header and Footer
//               
//
// Last Update : 8-30-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_001.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Default Header and Footer';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

    // Set a Logo   
        $pdfLogo = dirname(__DIR__) . '/images/limePDF_logo.png';

    // Set the Text    
        $pdfText = '<h1>Example # 001</h1>';
        $pdfText .= '<br /><h1>Welcome to <a href="http://www.limePDF.com" style="text-decoration:none;">';
		$pdfText .= '<span style=";color:#527201">lime</span><span style="color:black;">PDF</span>&nbsp;</a>!</h1><i>This is the first Example file for the limePDF library.</i>';
        $pdfText .= '<p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.';
        $pdfText .= '</p><p>Please check the source code documentation and other examples for further information.</p>';
        $pdfText .= '1) Here is the Logo as an inline HTML Img:<br /><img src="http://limepdf/examples/images/limePDF_logo.png"><br /><br />';
        $pdfText .= '2) Here is an embedded Logo File in the config file:<br /><img src="' . $pdfLogo . '">';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

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
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
