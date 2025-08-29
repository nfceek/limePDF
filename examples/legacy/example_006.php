<?php
//============================================================+
// File name   : example_006.php
//
// Author: Brad Smith
// (c) Copyright 2025, Brad Smith - LimePDF.com
//
//  * Original TCPDF Copyright (c) 2002-2023:
//  * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
//
//
// Description : WriteHTML and RTL support
//               Updated for PHP 7/8 compatibility
//               
//
// Last Update : 8-27-2025
//============================================================+


require_once __DIR__ . '/../../src/PDF.php';
require_once '../../vendor/autoload.php'; 

use LimePDF\Pdf;

$pdf = new Pdf();

use LimePDF\Config\ConfigManager;

// Instantiate and load ConfigManager
$config = new ConfigManager();
$config->loadFromArray([
]);

// ---------- ONLY EDIT THIS AREA --------------------------------

// 1) set Output File Name
	$outputFile = 'example_006.pdf';

// 2) set Output type ( I = In Browser & D = Download )
	$outputType = 'I';

// 3) set text for cell(s)
	$pdfText1 = '';

// 4) Set the doc Title 
    $pdfTitle = 'limePDF Example 006';

// 5) Set the Header logo
    $imgHeader = dirname(__DIR__) . '/images/limePDF_logo.png';

// 6) Set a Logo   
    $pdfLogo = dirname(__DIR__) . '/images/limePDF_logo.png';

// ---------- Dont Edit below here -----------------------------

$cfgArray = $config->toArray();
$pdfConfig = [
    'author' => $cfgArray['author'],
    'creator' => $cfgArray['creator'],
	'title' => $cfgArray['title'],
    'font' => [
        'main' => [$cfgArray['fontNameMain'], $cfgArray['fontSizeMain']],
        'data' => [$cfgArray['fontNameData'], $cfgArray['fontSizeData']],
        'mono' => $cfgArray['fontMonospaced'],
    ],  
	'headerString' => $cfgArray['headerString'],
    'headerLogoWidth' => $cfgArray['headerLogoWidth'],  
    'margins' => [
        'header' => $cfgArray['marginHeader'],
        'footer' => $cfgArray['marginFooter'],
        'top'    => $cfgArray['marginTop'],
        'bottom' => $cfgArray['marginBottom'],
        'left'   => $cfgArray['marginLeft'],
        'right'  => $cfgArray['marginRight'],
    ],
    'layout' => [
        'pageFormat' => $cfgArray['pageFormat'],
        'orientation' => $cfgArray['pageOrientation'],
        'unit' => $cfgArray['unit'],
        'imageScale' => $cfgArray['imageScaleRatio'],
    ],
    'meta' => [
        'subject' => $cfgArray['subject'],
        'keywords' => $cfgArray['keywords'],
    ]
];

// create new PDF document
$pdf = new PDF($pdfConfig['layout']['orientation'], $pdfConfig['layout']['unit'], $pdfConfig['layout']['pageFormat'], true, 'UTF-8', false);

// set document information
$pdf->setCreator( $pdfConfig['creator']);
$pdf->setAuthor($pdfConfig['author']);
$pdf->setTitle($pdfConfig['title']);
$pdf->setSubject($pdfConfig['meta']['subject']);
$pdf->setKeywords($pdfConfig['meta']['keywords']);

// remove default header/footer
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// set default header data
//$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 004', PDF_HEADER_STRING);
$pdf->setHeaderData(
	$imgHeader,
	$pdfConfig['headerLogoWidth'],
	$pdfTitle,
	$pdfConfig['headerString'],
	array(0,64,255),
	array(0,64,128)
);

// set header and footer fonts
$pdf->setHeaderFont([
	$pdfConfig['font']['main'][0],
	'',
	$pdfConfig['font']['main'][1]]
);

$pdf->setFooterFont([
	$pdfConfig['font']['data'][0],
	'',
	$pdfConfig['font']['data'][1]]
);

// set default monospaced font
$pdf->setDefaultMonospacedFont($pdfConfig['font']['mono']);

// set margins
$pdf->setMargins(
	$pdfConfig['margins']['left'], 
	$pdfConfig['margins']['top'], 
	$pdfConfig['margins']['right']
);

$pdf->setHeaderMargin($pdfConfig['margins']['header']);
$pdf->setFooterMargin($pdfConfig['margins']['footer']);

// set auto page breaks
$pdf->setAutoPageBreak(TRUE, $pdfConfig['margins']['bottom']);

// set image scale factor
$pdf->setImageScale($pdfConfig['layout']['imageScale']);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->setFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();

// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

// create some HTML content
$html = '<h1>HTML Example</h1>
Some special characters: &lt; € &euro; &#8364; &amp; è &egrave; &copy; &gt; \\slash \\\\double-slash \\\\\\triple-slash
<h2>List</h2>
List example:
<ol>
	<li><img src="images/logo_example.png" alt="test alt attribute" width="30" height="30" border="0" /> test image</li>
	<li><b>bold text</b></li>
	<li><i>italic text</i></li>
	<li><u>underlined text</u></li>
	<li><b>b<i>bi<u>biu</u>bi</i>b</b></li>
	<li><a href="http://www.limePDF.com" dir="ltr">link to http://www.limepdf.com</a></li>
	<li>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.<br />Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</li>
	<li>SUBLIST
		<ol>
			<li>row one
				<ul>
					<li>sublist</li>
				</ul>
			</li>
			<li>row two</li>
		</ol>
	</li>
	<li><b>T</b>E<i>S</i><u>T</u> <del>line through</del></li>
	<li><font size="+3">font + 3</font></li>
	<li><small>small text</small> normal <small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal</li>
</ol>
<dl>
	<dt>Coffee</dt>
	<dd>Black hot drink</dd>
	<dt>Milk</dt>
	<dd>White cold drink</dd>
</dl>
<div style="text-align:center">IMAGES<br />
<img src="images/logo_example.png" alt="test alt attribute" width="100" height="100" border="0" /><img src="../images/tcpdf_box.svg" alt="test alt attribute" width="100" height="100" border="0" /><img src="images/logo_example.jpg" alt="test alt attribute" width="100" height="100" border="0" />
</div>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');


// output some RTL HTML content
$html = '<div style="text-align:center">The words &#8220;<span dir="rtl">&#1502;&#1494;&#1500; [mazel] &#1496;&#1493;&#1489; [tov]</span>&#8221; mean &#8220;Congratulations!&#8221;</div>';
$pdf->writeHTML($html, true, false, true, false, '');

// test some inline CSS
$html = '<p>This is just an example of html code to demonstrate some supported CSS inline styles.
<span style="font-weight: bold;">bold text</span>
<span style="text-decoration: line-through;">line-trough</span>
<span style="text-decoration: underline line-through;">underline and line-trough</span>
<span style="color: rgb(0, 128, 64);">color</span>
<span style="background-color: rgb(255, 0, 0); color: rgb(255, 255, 255);">background color</span>
<span style="font-weight: bold;">bold</span>
<span style="font-size: xx-small;">xx-small</span>
<span style="font-size: x-small;">x-small</span>
<span style="font-size: small;">small</span>
<span style="font-size: medium;">medium</span>
<span style="font-size: large;">large</span>
<span style="font-size: x-large;">x-large</span>
<span style="font-size: xx-large;">xx-large</span>
</p>';

$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Print a table

// add a page
$pdf->AddPage();

// create some HTML content
$subtable = '<table border="1" cellspacing="6" cellpadding="4"><tr><td>a</td><td>b</td></tr><tr><td>c</td><td>d</td></tr></table>';

$html = '<h2>HTML TABLE:</h2>
<table border="1" cellspacing="3" cellpadding="4">
	<tr>
		<th>#</th>
		<th align="right">RIGHT align</th>
		<th align="left">LEFT align</th>
		<th>4A</th>
	</tr>
	<tr>
		<td>1</td>
		<td bgcolor="#cccccc" align="center" colspan="2">A1 ex<i>amp</i>le <a href="https://limepdf.com">link</a> column span. One two tree four five six seven eight nine ten.<br />line after br<br /><small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal  bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla<ol><li>first<ol><li>sublist</li><li>sublist</li></ol></li><li>second</li></ol><small color="#FF0000" bgcolor="#FFFF00">small small small small small small small small small small small small small small small small small small small small</small></td>
		<td>4B</td>
	</tr>
	<tr>
		<td>'.$subtable.'</td>
		<td bgcolor="#0000FF" color="yellow" align="center">A2 € &euro; &#8364; &amp; è &egrave;<br/>A2 € &euro; &#8364; &amp; è &egrave;</td>
		<td bgcolor="#FFFF00" align="left"><font color="#FF0000">Red</font> Yellow BG</td>
		<td>4C</td>
	</tr>
	<tr>
		<td>1A</td>
		<td rowspan="2" colspan="2" bgcolor="#FFFFCC">2AA<br />2AB<br />2AC</td>
		<td bgcolor="#FF0000">4D</td>
	</tr>
	<tr>
		<td>1B</td>
		<td>4E</td>
	</tr>
	<tr>
		<td>1C</td>
		<td>2C</td>
		<td>3C</td>
		<td>4F</td>
	</tr>
</table>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Print some HTML Cells

$html = '<span color="red">red</span> <span color="green">green</span> <span color="blue">blue</span><br /><span color="red">red</span> <span color="green">green</span> <span color="blue">blue</span>';

$pdf->setFillColor(255,255,0);

$pdf->writeHTMLCell(0, 0, '', '', $html, 'LRTB', 1, 0, true, 'L', true);
$pdf->writeHTMLCell(0, 0, '', '', $html, 'LRTB', 1, 1, true, 'C', true);
$pdf->writeHTMLCell(0, 0, '', '', $html, 'LRTB', 1, 0, true, 'R', true);

// reset pointer to the last page
$pdf->lastPage();

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Print a table

// add a page
$pdf->AddPage();

// create some HTML content
$html = '<h1>Image alignments on HTML table</h1>
<table cellpadding="1" cellspacing="1" border="1" style="text-align:center;">
<tr><td><img src="../../images/logo_example.png" border="0" height="41" width="41" /></td></tr>
<tr style="text-align:left;"><td><img src="images/logo_example.png" border="0" height="41" width="41" align="top" /></td></tr>
<tr style="text-align:center;"><td><img src="http://limepdf/examples/images/logo_example.png" border="0" height="41" width="41" align="middle" /></td></tr>
<tr style="text-align:right;"><td><img src="images/logo_example.png" border="0" height="41" width="41" align="bottom" /></td></tr>
<tr><td style="text-align:left;"><img src="images/logo_example.png" border="0" height="41" width="41" align="top" /></td></tr>
<tr><td style="text-align:center;"><img src="images/logo_example.png" border="0" height="41" width="41" align="middle" /></td></tr>
<tr><td style="text-align:right;"><img src="images/logo_example.png" border="0" height="41" width="41" align="bottom" /></td></tr>
</table>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// create some HTML content
$html = '<h1>Embedded Images</h1>
<table cellpadding="1" cellspacing="1" border="1" style="text-align:center;">
<tr><td>src="@..." </td><td><img src="@DATA1@" border="0" height="41" width="41" /></td></tr>
<tr><td>src="data..."</td><td><img src="@DATA2@" border="0" height="41" width="41" /></td></tr>
</table>';

$imageFile = "images/logo_example.png";
if (file_exists($imageFile)) {
    $data = base64_encode(file_get_contents($imageFile));
    $html = str_replace("@DATA1@", "@" . $data, $html);
    $html = str_replace("@DATA2@", "data:image/png;base64," . $data, $html);
} else {
    // Fallback if image doesn't exist
    $html = str_replace("@DATA1@", "", $html);
    $html = str_replace("@DATA2@", "", $html);
}

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Print all HTML colors

// add a page
$pdf->AddPage();

$textcolors = '<h1>HTML Text Colors</h1>';
$bgcolors = '<hr /><h1>HTML Background Colors</h1>';

/**
 * Validates if a string is a valid hexadecimal color code
 * @param string $hex The hex string to validate
 * @return bool True if valid hex color, false otherwise
 */
function isValidHexColor($hex) {
    // Remove # if present
    $hex = ltrim($hex, '#');
    // Check if it's exactly 6 characters and contains only valid hex characters
    return strlen($hex) === 6 && ctype_xdigit($hex);
}

// Check if LIMEPDF_COLORS class and webcolor property exist
if (class_exists('LIMEPDF_COLORS') && isset(LIMEPDF_COLORS::$webcolor)) {
    foreach(LIMEPDF_COLORS::$webcolor as $k => $v) {
        // Validate hex color before using it
        if (isValidHexColor($v)) {
            $textcolors .= '<span color="#'.$v.'">'.$v.'</span> ';
            $bgcolors .= '<span bgcolor="#'.$v.'" color="#333333">'.$v.'</span> ';
        }
    }
} else {
    // Fallback color array if LIMEPDF_COLORS is not available
    $webcolors = [
        'red' => 'FF0000',
        'green' => '008000',
        'blue' => '0000FF',
        'yellow' => 'FFFF00',
        'cyan' => '00FFFF',
        'magenta' => 'FF00FF',
        'black' => '000000',
        'white' => 'FFFFFF',
        'orange' => 'FFA500',
        'purple' => '800080',
        'brown' => 'A52A2A',
        'pink' => 'FFC0CB',
        'gray' => '808080',
        'silver' => 'C0C0C0',
        'gold' => 'FFD700',
        'navy' => '000080',
        'teal' => '008080',
        'lime' => '00FF00',
        'maroon' => '800000',
        'olive' => '808000'
    ];
    
    foreach($webcolors as $k => $v) {
        // Double-check validation even for our fallback colors
        if (isValidHexColor($v)) {
            $textcolors .= '<span color="#'.$v.'">'.$v.'</span> ';
            $bgcolors .= '<span bgcolor="#'.$v.'" color="#333333">'.$v.'</span> ';
        }
    }
}

// output the HTML content
$pdf->writeHTML($textcolors, true, false, true, false, '');
$pdf->writeHTML($bgcolors, true, false, true, false, '');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// Test word-wrap

// create some HTML content
$html = '<hr />
<h1>Various tests</h1>
<a href="#2">link to page 2</a><br />
<font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font> <font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font> <font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font> <font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font> <font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Test fonts nesting
$html1 = 'Default <font face="courier">Courier <font face="helvetica">Helvetica <font face="times">Times <font face="dejavusans">dejavusans </font>Times </font>Helvetica </font>Courier </font>Default';
$html2 = '<small>small text</small> normal <small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal';
// Fixed invalid hex color "#4169el" to "#4169e1" (corrected typo)
$html3 = '<font size="10" color="#ff7f50">The</font> <font size="10" color="#6495ed">quick</font> <font size="14" color="#dc143c">brown</font> <font size="18" color="#008000">fox</font> <font size="22"><a href="https://limepdf.com">jumps</a></font> <font size="22" color="#a0522d">over</font> <font size="18" color="#da70d6">the</font> <font size="14" color="#9400d3">lazy</font> <font size="10" color="#4169e1">dog</font>.';

$html = $html1.'<br />'.$html2.'<br />'.$html3.'<br />'.$html3.'<br />'.$html2;

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// test pre tag

// add a page
$pdf->AddPage();

$html = <<<EOF
<div style="background-color:#880000;color:white;">
Hello World!<br />
Hello
</div>
<pre style="background-color:#336699;color:white;">
int main() {
    printf("HelloWorld");
    return 0;
}
</pre>
<tt>Monospace font</tt>, normal font, <tt>monospace font</tt>, normal font.
<br />
<div style="background-color:#880000;color:white;">DIV LEVEL 1<div style="background-color:#008800;color:white;">DIV LEVEL 2</div>DIV LEVEL 1</div>
<br />
<span style="background-color:#880000;color:white;">SPAN LEVEL 1 <span style="background-color:#008800;color:white;">SPAN LEVEL 2</span> SPAN LEVEL 1</span>
EOF;

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// test custom bullet points for list

// add a page
$pdf->AddPage();

$html = <<<EOF
<h1>Test custom bullet image for list items</h1>
<ul style="font-size:14pt;list-style-type:img|png|4|4|images/logo_example.png">
	<li>test custom bullet image</li>
	<li>test custom bullet image</li>
	<li>test custom bullet image</li>
	<li>test custom bullet image</li>
</ul>
EOF;

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($outputFile, $outputType);

//============================================================+
// END OF FILE
//============================================================+