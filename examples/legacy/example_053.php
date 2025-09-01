<?php 
//============================================================+
// File name   : example_053.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Javascript example
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_053.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'D';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Javascript example.';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText ='This is an example of <strong>JavaScript</strong> usage on PDF 
		documents.<br /><br />For more information check the source code of this 
		example, the source code documentation for the <i>IncludeJS()</i> method 
		and the <i>JavaScript for Acrobat API Reference</i> guide.<br /><br />
		<a href="https://limePDF.com">LimePDF.com</a>';


// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('times', '', 14);

// add a page
$pdf->AddPage();

// print a some of text
$pdf->writeHTML($pdfText, true, 0, true, 0);

// write some JavaScript code
$js = <<<EOD
app.alert('JavaScript Popup Example', 3, 0, 'Welcome');
var cResponse = app.response({
	cQuestion: 'How are you today?',
	cTitle: 'Your Health Status',
	cDefault: 'Fine',
	cLabel: 'Response:'
});
if (cResponse == null) {
	app.alert('Thanks for trying anyway.', 3, 0, 'Result');
} else {
	app.alert('You responded, "'+cResponse+'", to the health question.', 3, 0, 'Result');
}
EOD;

// force print dialog
$js .= 'print(true);';

// set javascript
$pdf->IncludeJS($js);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
