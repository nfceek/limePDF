<?php 
//============================================================+
// File name   : example_011.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Colored Table (very simple table)
//               
//
// Last Update : 8-31-2025
//============================================================+

use LimePDF\PDF;
use LimePDF\Config\PdfBootstrap;
require_once __DIR__ . '/../../src/config/PdfBootstrap.php';

// ----- Standard Form Parameters---------------------------------------------------------
	//  Set File name
		$outputFile = 'Example_011.pdf';
	//  Set Output type ( I = In Browser & D = Download )
		$outputType = 'I';
	// Header output ( true / false)
		$outputHeader = true;
	//  Set the header Title 
		$pdfHeader = $outputFile;
	// Set the sub Title
		$pdfSubHeader = 'Colored Table (very simple table)';
	//  Set the Header logo
		$pdfHeaderImage = dirname(__DIR__, 2) . '/examples/images/limePDF_logo.png';	
	//  Set Footer output
		$outputFooter = true;
//--------------------------------------------------------------------------------------

// ----- Form Specific Parameters-------------------------------------------------------
	//  Load your table Data
		$PrintText = '../data/table_data_demo.txt';

	//  Add Column Titles
		$ColumnTitles = ['Country', 'Capitals', 'Area (sq km)', 'Pop. (thousands)'];

// ----- Dont Edit below here ---------------------------------------------------------

	// extend TCPF with custom functions
    class CustomPdf extends PDF
    {
		// Load table data from file
		public function LoadData($file) {
			// Read file lines
			$lines = file($file);
			$data = array();
			foreach($lines as $line) {
				$data[] = explode(';', chop($line));
			}
			return $data;
		}

		// Colored table
		public function ColoredTable($header,$data) {
			// Colors, line width and bold font
			$this->setFillColor(255, 0, 0);
			$this->setTextColor(255);
			$this->setDrawColor(128, 0, 0);
			$this->setLineWidth(0.3);
			$this->setFont('', 'B');
			// Header
			$w = array(40, 35, 40, 45);
			$num_headers = count($header);
			for($i = 0; $i < $num_headers; ++$i) {
				$this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
			}
			$this->Ln();
			// Color and font restoration
			$this->setFillColor(224, 235, 255);
			$this->setTextColor(0);
			$this->setFont('');
			// Data
			$fill = 0;
			foreach($data as $row) {
				$this->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill);
				$this->Cell($w[1], 6, $row[1], 'LR', 0, 'L', $fill);
				$this->Cell($w[2], 6, number_format($row[2]), 'LR', 0, 'R', $fill);
				$this->Cell($w[3], 6, number_format($row[3]), 'LR', 0, 'R', $fill);
				$this->Ln();
				$fill=!$fill;
			}
			$this->Cell(array_sum($w), 0, '', 'T');
		}
	}

	// Now use CustomPdf
$pdf = new CustomPdf();

// set font
$pdf->setFont('helvetica', '', 12);

// add a page
$pdf->AddPage();

// column titles
$header = $ColumnTitles;;

// data loading
$data = $pdf->LoadData($PrintText);

// print colored table
$pdf->ColoredTable($header, $data);

// ---------------------------------------------------------

// close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+
