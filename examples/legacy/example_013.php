<?php  
//============================================================+
// File name   : example_013.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Graphic Transformations
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_013.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Graphic Transformations';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------

	//  Set text for cell(s)
		$pdfText = 'TEST CELL STRETCH:';

// ----- Dont Edit below here ---------------------------------------------------------

// send form parameters 
$pdf = PdfBootstrap::create($outputFile, $outputType, $outputHeader, $outputFooter, $pdfHeader, $pdfSubHeader, $pdfHeaderImage); 

// set font
$pdf->setFont('helvetica', 'B', 20);

// add a page
$pdf->AddPage();

$pdf->Write(0, 'Graphic Transformations', '', 0, 'C', 1, 0, false, false, 0);

// set font
$pdf->setFont('helvetica', '', 10);

// --- Scaling ---------------------------------------------
$pdf->setDrawColor(200);
$pdf->setTextColor(200);
$pdf->Rect(50, 70, 40, 10, 'D');
$pdf->Text(50, 66, 'Scale');
$pdf->setDrawColor(0);
$pdf->setTextColor(0);
// Start Transformation
$pdf->StartTransform();
// Scale by 150% centered by (50,80) which is the lower left corner of the rectangle
$pdf->ScaleXY(150, 50, 80);
$pdf->Rect(50, 70, 40, 10, 'D');
$pdf->Text(50, 66, 'Scale');
// Stop Transformation
$pdf->StopTransform();

// --- Translation -----------------------------------------
$pdf->setDrawColor(200);
$pdf->setTextColor(200);
$pdf->Rect(125, 70, 40, 10, 'D');
$pdf->Text(125, 66, 'Translate');
$pdf->setDrawColor(0);
$pdf->setTextColor(0);
// Start Transformation
$pdf->StartTransform();
// Translate 7 to the right, 5 to the bottom
$pdf->Translate(7, 5);
$pdf->Rect(125, 70, 40, 10, 'D');
$pdf->Text(125, 66, 'Translate');
// Stop Transformation
$pdf->StopTransform();

// --- Rotation --------------------------------------------
$pdf->setDrawColor(200);
$pdf->setTextColor(200);
$pdf->Rect(70, 100, 40, 10, 'D');
$pdf->Text(70, 96, 'Rotate');
$pdf->setDrawColor(0);
$pdf->setTextColor(0);
// Start Transformation
$pdf->StartTransform();
// Rotate 20 degrees counter-clockwise centered by (70,110) which is the lower left corner of the rectangle
$pdf->Rotate(20, 70, 110);
$pdf->Rect(70, 100, 40, 10, 'D');
$pdf->Text(70, 96, 'Rotate');
// Stop Transformation
$pdf->StopTransform();

// --- Skewing ---------------------------------------------
$pdf->setDrawColor(200);
$pdf->setTextColor(200);
$pdf->Rect(125, 100, 40, 10, 'D');
$pdf->Text(125, 96, 'Skew');
$pdf->setDrawColor(0);
$pdf->setTextColor(0);
// Start Transformation
$pdf->StartTransform();
// skew 30 degrees along the x-axis centered by (125,110) which is the lower left corner of the rectangle
$pdf->SkewX(30, 125, 110);
$pdf->Rect(125, 100, 40, 10, 'D');
$pdf->Text(125, 96, 'Skew');
// Stop Transformation
$pdf->StopTransform();

// --- Mirroring horizontally ------------------------------
$pdf->setDrawColor(200);
$pdf->setTextColor(200);
$pdf->Rect(70, 130, 40, 10, 'D');
$pdf->Text(70, 126, 'MirrorH');
$pdf->setDrawColor(0);
$pdf->setTextColor(0);
// Start Transformation
$pdf->StartTransform();
// mirror horizontally with axis of reflection at x-position 70 (left side of the rectangle)
$pdf->MirrorH(70);
$pdf->Rect(70, 130, 40, 10, 'D');
$pdf->Text(70, 126, 'MirrorH');
// Stop Transformation
$pdf->StopTransform();

// --- Mirroring vertically --------------------------------
$pdf->setDrawColor(200);
$pdf->setTextColor(200);
$pdf->Rect(125, 130, 40, 10, 'D');
$pdf->Text(125, 126, 'MirrorV');
$pdf->setDrawColor(0);
$pdf->setTextColor(0);
// Start Transformation
$pdf->StartTransform();
// mirror vertically with axis of reflection at y-position 140 (bottom side of the rectangle)
$pdf->MirrorV(140);
$pdf->Rect(125, 130, 40, 10, 'D');
$pdf->Text(125, 126, 'MirrorV');
// Stop Transformation
$pdf->StopTransform();

// --- Point reflection ------------------------------------
$pdf->setDrawColor(200);
$pdf->setTextColor(200);
$pdf->Rect(70, 160, 40, 10, 'D');
$pdf->Text(70, 156, 'MirrorP');
$pdf->setDrawColor(0);
$pdf->setTextColor(0);
// Start Transformation
$pdf->StartTransform();
// point reflection at the lower left point of rectangle
$pdf->MirrorP(70,170);
$pdf->Rect(70, 160, 40, 10, 'D');
$pdf->Text(70, 156, 'MirrorP');
// Stop Transformation
$pdf->StopTransform();

// --- Mirroring against a straigth line described by a point (120, 120) and an angle -20°
$angle=-20;
$px=120;
$py=170;

// just for visualisation: the straight line to mirror against

$pdf->setDrawColor(200);
$pdf->Line($px-1,$py-1,$px+1,$py+1);
$pdf->Line($px-1,$py+1,$px+1,$py-1);
$pdf->StartTransform();
$pdf->Rotate($angle, $px, $py);
$pdf->Line($px-5, $py, $px+60, $py);
$pdf->StopTransform();

$pdf->setDrawColor(200);
$pdf->setTextColor(200);
$pdf->Rect(125, 160, 40, 10, 'D');
$pdf->Text(125, 156, 'MirrorL');
$pdf->setDrawColor(0);
$pdf->setTextColor(0);
//Start Transformation
$pdf->StartTransform();
//mirror against the straight line
$pdf->MirrorL($angle, $px, $py);
$pdf->Rect(125, 160, 40, 10, 'D');
$pdf->Text(125, 156, 'MirrorL');
//Stop Transformation
$pdf->StopTransform();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
