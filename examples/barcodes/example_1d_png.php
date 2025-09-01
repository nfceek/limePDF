<?php 
//============================================================+
// File name: example_1d_png.php
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

use LimePDF\Barcodes\Barcodes1D;
require_once __DIR__ . '/../../src/Barcodes/Barcodes1D.php';

// set the barcode content and type
$barcodeobj = new Barcodes1D('https://limepdf.com', 'C128');

// output the barcode as PNG image
$barcodeobj->getBarcodePNG(2, 30, array(0,0,0));

//============================================================+
// END OF FILE
//============================================================+
