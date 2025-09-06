<?php 
//============================================================+
// File name   : example_052.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original LimePDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Certification Signature (experimental)
//               
//
// Last Update : 9-1-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_052.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'WCertification Signature (experimental)';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'Next This is a <b color="#FF0000">digitally signed document</b> 
		using the default (example) <b>tcpdf.crt</b> certificate.<br />To validate this 
		signature you have to load the <b color="#006600">tcpdf.fdf</b> on the Arobat 
		Reader to add the certificate to <i>List of Trusted Identities</i>.<br /><br />
		For more information check the source code of this example and the source code 
		documentation for the <i>setSignature()</i> method.<br /><br />
		<a href="https://limePDF.com">limePDF.com</a>';


	$pdfImage = dirname(__DIR__, 2) . '/examples/images/limepdf_signature.png';
	
// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('helvetica', '', 10);

// add a page
$pdf->AddPage();

/*
NOTES:
 - To create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
 - To export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
 - To convert pfx certificate to pem: openssl pkcs12 -in tcpdf.pfx -out tcpdf.crt -nodes
*/

// set certificate file
$certificate = 'file://data/cert/limepdf.crt';

// set additional information
$info = array(
	'Name' => 'LIMEPDF',
	'Location' => 'Office',
	'Reason' => 'Testing LimePDF-Next',
	'ContactInfo' => 'http://limePDF.com',
	);

// set document signature
$pdf->setSignature($certificate, $certificate, 'limepdfdemo', '', 2, $info);

// set font. 'helvetica' MUST be used to avoid a PHP notice from PHP 7.4+
$pdf->setFont('helvetica', '', 12);

// add a page
$pdf->AddPage();

// print a line of text
$pdf->writeHTML($pdfText, true, 0, true, 0);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// *** set signature appearance ***

// create content for signature (image and/or text)
$pdf->Image($pdfImage, 180, 60, 15, 15, 'PNG');

// define active area for signature appearance
$pdf->setSignatureAppearance(180, 60, 15, 15);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// *** set an empty signature appearance ***
$pdf->addEmptySignatureAppearance(180, 80, 15, 15);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
