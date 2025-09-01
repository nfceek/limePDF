<?php 
//============================================================+
// File name   : example_063.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Text stretching and spacing (tracking)
//               
//
// Last Update : 9-1-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_063.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Text stretching and spacing (tracking)';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'Example of Text Stretching and Spacing (tracking)';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 


// set font
$pdf->setFont('helvetica', 'B', 16);

// add a page
$pdf->AddPage();

$pdf->Write(0, $pdfText, '', 0, 'L', true, 0, false, false, 0);
$pdf->Ln(5);

// create several cells to display all cases of stretching and spacing combinations.

$fonts = array('times', 'dejavuserif');
$alignments = array('L' => 'LEFT', 'C' => 'CENTER', 'R' => 'RIGHT', 'J' => 'JUSTIFY');


// Test all cases using direct stretching/spacing methods
foreach ($fonts as $fkey => $font) {
	$pdf->setFont($font, '', 14);
	foreach ($alignments as $align_mode => $align_name) {
		for ($stretching = 90; $stretching <= 110; $stretching += 10) {
			for ($spacing = -0.254; $spacing <= 0.254; $spacing += 0.254) {
				$pdf->setFontStretching($stretching);
				$pdf->setFontSpacing($spacing);
				$txt = $align_name.' | Stretching = '.$stretching.'% | Spacing = '.sprintf('%+.3F', $spacing).'mm';
				$pdf->Cell(0, 0, $txt, 1, 1, $align_mode);
			}
		}
	}
	$pdf->AddPage();
}


// Test all cases using CSS stretching/spacing properties
foreach ($fonts as $fkey => $font) {
	$pdf->setFont($font, '', 11);
	foreach ($alignments as $align_mode => $align_name) {
		for ($stretching = 90; $stretching <= 110; $stretching += 10) {
			for ($spacing = -0.254; $spacing <= 0.254; $spacing += 0.254) {
				$html = '<span style="font-stretch:'.$stretching.'%;letter-spacing:'.$spacing.'mm;"><span style="color:red;">'.$align_name.'</span> | <span style="color:green;">Stretching = '.$stretching.'%</span> | <span style="color:blue;">Spacing = '.sprintf('%+.3F', $spacing).'mm</span><br />Lorem ipsum dolor sit amet, consectetur adipiscing elit. In sed imperdiet lectus. Phasellus quis velit velit, non condimentum quam. Sed neque urna, ultrices ac volutpat vel, laoreet vitae augue. Sed vel velit erat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.</span>';
				$pdf->writeHTMLCell(0, 0, '', '', $html, 1, 1, false, true, $align_mode, false);
			}
		}
		if (!(($fkey == 1) AND ($align_mode == 'J'))) {
			$pdf->AddPage();
		}
	}
}


// reset font stretching
$pdf->setFontStretching(100);

// reset font spacing
$pdf->setFontSpacing(0);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
