<?php 
//============================================================+
// File name: example_2d_pdf417_png.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : example_2d_pdf417_svgi.php
//               
//
// Last Update : 9-1-2025
//============================================================+

use LimePDF\Barcodes\Barcodes2D;
require_once __DIR__ . '/../../src/Barcodes/Barcodes2D.php';

// set the barcode content and type
$barcodeobj = new Barcodes2D('https://limepdf.com', 'PDF417');

// output the barcode as SVG inline code
echo $barcodeobj->getBarcodeSVGcode(4, 4, 'black');

//============================================================+
// END OF FILE
//============================================================+
