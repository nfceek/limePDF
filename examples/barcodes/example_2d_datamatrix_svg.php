<?php 
//============================================================+
// File name: example_2d_datamatrix_svg.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : Example for 1DBarcodes class
//               
//
// Last Update : 9-1-2025
//============================================================+

use LimePDF\Barcodes\Barcodes2D;
require_once __DIR__ . '/../../src/Barcodes/Barcodes2D.php';

// set the barcode content and type
$barcodeobj = new Barcodes2D('https://limepdf.com', 'DATAMATRIX');

// output the barcode as SVG image
$barcodeobj->getBarcodeSVG(6, 6, 'black');

//============================================================+
// END OF FILE
//============================================================+
