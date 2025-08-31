<?php 
//============================================================+
// File name   : example_030.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Colour gradients
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_030.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Colour gradients';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'PDF Gradients';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 


// set font
$pdf->setFont('helvetica', 'B', 20);

// --- first page ------------------------------------------

// add a page
$pdf->AddPage();

$pdf->Cell(0, 0, $pdfText, 0, 1, 'C', 0, '', 0, false, 'T', 'M');

// set colors for gradients (r,g,b) or (grey 0-255)
$red = array(255, 0, 0);
$blue = array(0, 0, 200);
$yellow = array(255, 255, 0);
$green = array(0, 255, 0);
$white = array(255);
$black = array(0);

// set the coordinates x1,y1,x2,y2 of the gradient (see linear_gradient_coords.jpg)
$coords = array(0, 0, 1, 0);

// paint a linear gradient
$pdf->LinearGradient(20, 45, 80, 80, $red, $blue, $coords);

// write label
$pdf->Text(20, 130, 'LinearGradient()');

// set the coordinates fx,fy,cx,cy,r of the gradient (see radial_gradient_coords.jpg)
$coords = array(0.5, 0.5, 1, 1, 1.2);

// paint a radial gradient
$pdf->RadialGradient(110, 45, 80, 80, $white, $black, $coords);

// write label
$pdf->Text(110, 130, 'RadialGradient()');

// paint a coons patch mesh with default coordinates
$pdf->CoonsPatchMesh(20, 155, 80, 80, $yellow, $blue, $green, $red);

// write label
$pdf->Text(20, 240, 'CoonsPatchMesh()');

// set the coordinates for the cubic Bézier points x1,y1 ... x12, y12 of the patch (see coons_patch_mesh_coords.jpg)
$coords = array(
	0.00,0.00, 0.33,0.20,             //lower left
	0.67,0.00, 1.00,0.00, 0.80,0.33,  //lower right
	0.80,0.67, 1.00,1.00, 0.67,0.80,  //upper right
	0.33,1.00, 0.00,1.00, 0.20,0.67,  //upper left
	0.00,0.33);                       //lower left
$coords_min = 0;   //minimum value of the coordinates
$coords_max = 1;   //maximum value of the coordinates

// paint a coons patch gradient with the above coordinates
$pdf->CoonsPatchMesh(110, 155, 80, 80, $yellow, $blue, $green, $red, $coords, $coords_min, $coords_max);

// write label
$pdf->Text(110, 240, 'CoonsPatchMesh()');

// --- second page -----------------------------------------
$pdf->AddPage();

// first patch: f = 0
$patch_array[0]['f'] = 0;
$patch_array[0]['points'] = array(
	0.00,0.00, 0.33,0.00,
	0.67,0.00, 1.00,0.00, 1.00,0.33,
	0.8,0.67, 1.00,1.00, 0.67,0.8,
	0.33,1.80, 0.00,1.00, 0.00,0.67,
	0.00,0.33);
$patch_array[0]['colors'][0] = array('r' => 255, 'g' => 255, 'b' => 0);
$patch_array[0]['colors'][1] = array('r' => 0, 'g' => 0, 'b' => 255);
$patch_array[0]['colors'][2] = array('r' => 0, 'g' => 255,'b' => 0);
$patch_array[0]['colors'][3] = array('r' => 255, 'g' => 0,'b' => 0);

// second patch - above the other: f = 2
$patch_array[1]['f'] = 2;
$patch_array[1]['points'] = array(
	0.00,1.33,
	0.00,1.67, 0.00,2.00, 0.33,2.00,
	0.67,2.00, 1.00,2.00, 1.00,1.67,
	1.5,1.33);
$patch_array[1]['colors'][0]=array('r' => 0, 'g' => 0, 'b' => 0);
$patch_array[1]['colors'][1]=array('r' => 255, 'g' => 0, 'b' => 255);

// third patch - right of the above: f = 3
$patch_array[2]['f'] = 3;
$patch_array[2]['points'] = array(
	1.33,0.80,
	1.67,1.50, 2.00,1.00, 2.00,1.33,
	2.00,1.67, 2.00,2.00, 1.67,2.00,
	1.33,2.00);
$patch_array[2]['colors'][0] = array('r' => 0, 'g' => 255, 'b' => 255);
$patch_array[2]['colors'][1] = array('r' => 0, 'g' => 0, 'b' => 0);

// fourth patch - below the above, which means left(?) of the above: f = 1
$patch_array[3]['f'] = 1;
$patch_array[3]['points'] = array(
	2.00,0.67,
	2.00,0.33, 2.00,0.00, 1.67,0.00,
	1.33,0.00, 1.00,0.00, 1.00,0.33,
	0.8,0.67);
$patch_array[3]['colors'][0] = array('r' => 0, 'g' => 0, 'b' => 0);
$patch_array[3]['colors'][1] = array('r' => 0, 'g' => 0, 'b' => 255);

$coords_min = 0;
$coords_max = 2;

$pdf->CoonsPatchMesh(10, 45, 190, 200, '', '', '', '', $patch_array, $coords_min, $coords_max);

// write label
$pdf->Text(10, 250, 'CoonsPatchMesh()');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
