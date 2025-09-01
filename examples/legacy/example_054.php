<?php 
//============================================================+
// File name   : example_054.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : XHTML Forms
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_054.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'XHTML Forms';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'XHTML Forms';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// IMPORTANT: disable font subsetting to allow users editing the document
$pdf->setFontSubsetting(false);

// set font. 'helvetica' MUST be used to avoid a PHP notice from PHP 7.4+
$pdf->setFont('helvetica', '', 10, '', false);

// add a page
$pdf->AddPage();

// create some HTML content
$html = <<<EOD
<h1>XHTML Form Example</h1>
<form method="post" action="http://localhost/printvars.php" enctype="multipart/form-data">
<label for="name">name:</label> <input type="text" name="name" value="" size="20" maxlength="30" /><br />
<label for="password">password:</label> <input type="password" name="password" value="" size="20" maxlength="30" /><br /><br />
<label for="infile">file:</label> <input type="file" name="userfile" size="20" /><br /><br />
<input type="checkbox" name="agree" value="1" checked="checked" /> <label for="agree">I agree </label><br /><br />
<input type="radio" name="radioquestion" id="rqa" value="1" /> <label for="rqa">one</label><br />
<input type="radio" name="radioquestion" id="rqb" value="2" checked="checked"/> <label for="rqb">two</label><br />
<input type="radio" name="radioquestion" id="rqc" value="3" /> <label for="rqc">three</label><br /><br />
<label for="selection">select:</label>
<select name="selection" size="0">
	<option value="0">zero</option>
	<option value="1">one</option>
	<option value="2">two</option>
	<option value="3">three</option>
</select><br /><br />
<label for="selection">select:</label>
<select name="multiselection" size="2" multiple="multiple">
	<option value="0">zero</option>
	<option value="1">one</option>
	<option value="2">two</option>
	<option value="3">three</option>
</select><br /><br /><br />
<label for="text">text area:</label><br />
<textarea cols="40" rows="3" name="text">line one
line two</textarea><br />
<br /><br /><br />
<input type="reset" name="reset" value="Reset" />
<input type="submit" name="submit" value="Submit" />
<input type="button" name="print" value="Print" onclick="print()" />
<input type="hidden" name="hiddenfield" value="OK" />
<br />
</form>
EOD;

// output the HTML content
$pdf->writeHTML($html, true, 0, true, 0);

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
