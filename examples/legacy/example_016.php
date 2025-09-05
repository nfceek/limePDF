<?php 
//============================================================+
// File name   : example_016.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Document Encryption / Security
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_016.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Document Encryption / Security';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'Encryption Example Consult the source code documentation for the SetProtection() method.';

		$user_pass='1234';

		$certPath = '../data/cert/tcpdf.crt';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 


// *** Set PDF protection (encryption) *********************

/*
  The permission array is composed of values taken from the following ones (specify the ones you want to block):
	- print : Print the document;
	- modify : Modify the contents of the document by operations other than those controlled by 'fill-forms', 'extract' and 'assemble';
	- copy : Copy or otherwise extract text and graphics from the document;
	- annot-forms : Add or modify text annotations, fill in interactive form fields, and, if 'modify' is also set, create or modify interactive form fields (including signature fields);
	- fill-forms : Fill in existing interactive form fields (including signature fields), even if 'annot-forms' is not specified;
	- extract : Extract text and graphics (in support of accessibility to users with disabilities or for other purposes);
	- assemble : Assemble the document (insert, rotate, or delete pages and create bookmarks or thumbnail images), even if 'modify' is not set;
	- print-high : Print the document to a representation from which a faithful digital copy of the PDF content could be generated. When this is not set, printing is limited to a low-level representation of the appearance, possibly of degraded quality.
	- owner : (inverted logic - only for public-key) when set permits change of encryption and enables all other permissions.

 If you don't set any password, the document will open as usual.
 If you set a user password, the PDF viewer will ask for it before displaying the document.
 The master (owner) password, if different from the user one, can be used to get full document access.

 Possible encryption modes are:
 	0 = RSA 40 bit
 	1 = RSA 128 bit
 	2 = AES 128 bit
 	3 = AES 256 bit

 NOTES:
 - To create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
 - To export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
 - To convert pfx certificate to pem: openssl pkcs12 -in tcpdf.pfx -out tcpdf.crt -nodes

*/

$pdf->setProtection(array('print', 'copy'), '', null, 0, null);

// Example with public-key
// To open the document you need to install the private key (tcpdf.p12) on the Acrobat Reader. The password is: 1234
//$pdf->setProtection($permissions=array('print', 'copy'), $user_pass, $owner_pass=null, $mode=1, $pubkeys=array(array('c' => $certPath, 'p' => array('print'))));

// *********************************************************



// set font
$pdf->setFont('times', '', 16);

// add a page
$pdf->AddPage();

// print a block of text using Write()
$pdf->Write(0, $pdfText, '', 0, 'L', true, 0, false, false, 0);


// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
