<?php 
//============================================================+
// File name   : example_020.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Two columns composed by MultiCell of different heights
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_020.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Two columns composed by MultiCell of different heights';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText =  'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In sed imperdiet lectus. Phasellus quis velit velit, non condimentum quam. Sed neque urna, ultrices ac volutpat vel, laoreet vitae augue. Sed vel velit erat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras eget velit nulla, eu sagittis elit. Nunc ac arcu est, in lobortis tellus. Praesent condimentum rhoncus sodales. In hac habitasse platea dictumst. Proin porta eros pharetra enim tincidunt dignissim nec vel dolor. Cras sapien elit, ornare ac dignissim eu, ultricies ac eros. Maecenas augue magna, ultrices a congue in, mollis eu nulla. Nunc venenatis massa at est eleifend faucibus. Vivamus sed risus lectus, nec interdum nunc.' .
					' ' .
					'Fusce et felis vitae diam lobortis sollicitudin. Aenean tincidunt accumsan nisi, id vehicula quam laoreet elementum. Phasellus egestas interdum erat, et viverra ipsum ultricies ac. Praesent sagittis augue at augue volutpat eleifend. Cras nec orci neque. Mauris bibendum posuere blandit. Donec feugiat mollis dui sit amet pellentesque. Sed a enim justo. Donec tincidunt, nisl eget elementum aliquam, odio ipsum ultrices quam, eu porttitor ligula urna at lorem. Donec varius, eros et convallis laoreet, ligula tellus consequat felis, ut ornare metus tellus sodales velit. Duis sed diam ante. Ut rutrum malesuada massa, vitae consectetur ipsum rhoncus sed. Suspendisse potenti. Pellentesque a congue massa.';


// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('helvetica', '', 20);
// add a page
$pdf->AddPage();

// $pdf->Write(0, 'Example of text layout using Multicell()', '', 0, 'L', true, 0, false, false, 0);

// $pdf->Ln(5);

// $pdf->setFont('times', '', 9);

// //$pdf->setCellPadding(0);
// //$pdf->setLineWidth(2);

// // set color for background
// $pdf->setFillColor(255, 255, 200);

// // print some rows just as example
// for ($i = 0; $i < 10; ++$i) {
// 	$pdf->MultiRow('Row '.($i+1), $pdfText."\n");
// }


	// set font
	$pdf->SetFont('helvetica', '', 10);

	// table header
	$pdf->SetFillColor(200, 220, 255);
	$pdf->Cell(90, 10, 'Column 1', 1, 0, 'C', 1);
	$pdf->Cell(90, 10, 'Column 2', 1, 1, 'C', 1);

	// table body rows
	$data = [
		['This is a long paragraph that would normally go into column 1', 'This is another block of text for column 2'],
		['Second row, column 1 text goes here', 'Second row, column 2 text goes here'],
	];

	foreach ($data as [$col1, $col2]) {
		// Get starting X/Y
		$x = $pdf->GetX();
		$y = $pdf->GetY();

		// Output col1
		$pdf->MultiCell(90, 0, $col1, 1, 'L', 0, 0);

		// Reset position for col2
		$pdf->SetXY($x + 90, $y);

		// Output col2
		$pdf->MultiCell(90, 0, $col2, 1, 'L', 0, 1);
	}
// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
