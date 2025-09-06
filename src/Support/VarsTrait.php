<?php

namespace LimePDF\Support;

trait VarsTrait {
    
	protected $utils;    
	protected $utilsPut;    
	protected $utilsMisc;    
	protected $page;    
	protected $n;    
	protected $offsets = array();    
	protected $pageobjects = array();    
	protected $buffer;    
	protected $pages = array();    
	protected $state;
    protected $compress;    
	protected $CurOrientation;    
	protected $pagedim = array();    
	protected $k;    
	protected $fwPt;    
	protected $fhPt;    
	protected $wPt;    
	protected $hPt;    
	protected $w;    
	protected $h;    
	protected $lMargin;    
	protected $rMargin;    
	protected $clMargin;    
	protected $crMargin;    
	protected $tMargin;    
	protected $bMargin;    
	protected $cell_padding = array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0);    
	protected $cell_margin = array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0);    
	protected $x;    
	protected $y;    
	protected $lasth;    
	protected $LineWidth;    
	protected $CoreFonts;    
	protected $fonts = array();
    protected $FontFiles = array();
    protected $diffs = array();    
	protected $images = array();    
	protected $svg_tag_depth = 0;
    protected $PageAnnots = array();    
	protected $links = array();    
	protected $FontFamily;    
	protected $FontStyle;    
	protected $FontAscent;    
	protected $FontDescent;    
	protected $underline;    
	protected $overline;    
	protected $CurrentFont;    
	protected $FontSizePt;    
	protected $FontSize;    
	protected $DrawColor;    
	protected $FillColor;    
	protected $TextColor;    
	protected $ColorFlag;    
	protected $AutoPageBreak;    
	protected $PageBreakTrigger;    
	protected $InHeader = false;    
	protected $InFooter = false;    
	protected $ZoomMode;    
	protected $LayoutMode;    
	protected $docinfounicode = true;    
	protected $title = '';    
	protected $subject = '';    
	protected $author = '';    
	protected $keywords = '';    
	protected $creator = '';    
	protected $starting_page_number = 1;
	protected $img_rb_x;    
	protected $img_rb_y;    
	protected $imgscale = 1;    
	protected $isunicode = false;    
	protected $PDFVersion = '1.7';    
	protected $header_xobjid = false;    
	protected $header_xobj_autoreset = false;    
	protected $header_margin;    
	protected $footer_margin;    
	protected $original_lMargin;    
	protected $original_rMargin;    
	protected $header_font;    
	protected $footer_font;    
	protected $l;    
	protected $barcode = false;    
	protected $print_header = true;    
	protected $print_footer = true;    
	protected $header_logo = '';    
	protected $header_logo_width = 30;    
	protected $header_title = '';    
	protected $header_string = '';    
	protected $header_text_color = array(0,0,0);    
	protected $header_line_color = array(0,0,0);    
	protected $footer_text_color = array(0,0,0);    
	protected $footer_line_color = array(0,0,0);    
	protected $txtshadow = array('enabled'=>false, 'depth_w'=>0, 'depth_h'=>0, 'color'=>false, 'opacity'=>1, 'blend_mode'=>'Normal');    
	protected $default_table_columns = 4;    
	protected $HREF = array();    
	protected $fontlist = array();    
	protected $fgcolor;    
	protected $listordered = array();    
	protected $listcount = array();    
	protected $listnum = 0;    
	protected $listindent = 0;    
	protected $listindentlevel = 0;    
	protected $bgcolor;    
	protected $tempfontsize = 10;    
	protected $lispacer = '';    
	protected $encoding = 'UTF-8';    
	protected $rtl = false;    
	protected $tmprtl = false;    
	protected $encrypted;    
	protected $encryptdata = array();    
	protected $last_enc_key;    
	protected $last_enc_key_c;    
	protected $file_id;    
	protected $hash_key;    
	protected $outlines = array();    
	protected $OutlineRoot;    
	protected $javascript = '';    
	protected $n_js;    
	protected $linethrough;    
	protected $ur = array();    
	protected $dpi = 72;    
	protected $newpagegroup = array();    
	protected $pagegroups = array();    
	protected $currpagegroup = 0;
	protected $extgstates;    
	protected $jpeg_quality;    
	protected $cell_height_ratio = 1.25;    
	protected $viewer_preferences;    
	protected $PageMode;    
	protected $gradients = array();    
	protected $intmrk = array();    
	protected $bordermrk = array();    
	protected $emptypagemrk = array();    
	protected $cntmrk = array();    
	protected $footerpos = array();    
	protected $footerlen = array();    
	protected $newline = true;    
	protected $endlinex = 0;    
	protected $linestyleWidth = '';    
	protected $linestyleCap = '0 J';    
	protected $linestyleJoin = '0 j';    
	protected $linestyleDash = '[] 0 d';    
	protected $openMarkedContent = false;    
	protected $htmlvspace = 0;
    protected $spot_colors = array();    
	protected $lisymbol = '';    
	protected $epsmarker = 'x#!#EPS#!#x';    
	protected $transfmatrix = array();    
	protected $transfmatrix_key = 0;    
	protected $booklet = false;    
	protected $feps = 0.005;    
	protected $tagvspaces = array();    
	protected $customlistindent = -1;    
	protected $opencell = true;    
	protected $embeddedfiles = array();    
	protected $premode = false;    
	protected $transfmrk = array();    
	protected $htmlLinkColorArray = array(0, 0, 255);    
	protected $htmlLinkFontStyle = 'U';    
	protected $numpages = 0;    
	protected $pagelen = array();    
	protected $numimages = 0;
    protected $imagekeys = array();
    
	//public $imagekeys = array();
    
	protected $bufferlen = 0;    
	protected $numfonts = 0;
    protected $fontkeys = array();
    protected $font_obj_ids = array();    
	protected $pageopen = array();    
	protected $default_monospaced_font = 'courier';    
	protected $objcopy;    
	protected $cache_file_length = array();    
	protected $thead = '';    
	protected $theadMargins = array();    
	protected $sign = false;    
	protected $signature_data = array();    
	protected $signature_max_length = 11742;    
	protected $signature_appearance = array('page' => 1, 'rect' => '0 0 0 0');    
	protected $empty_signature_appearance = array();    
	protected $tsa_timestamp = false;    
	protected $tsa_data = array();    
	protected $re_spaces = '/[^\S\xa0]/';    
	protected $re_space = array('p' => '[^\S\xa0]', 'm' => '');    
	protected $sig_obj_id = 0;    
	protected $page_obj_id = array();    
	protected $form_obj_id = array();    
	protected $default_form_prop = array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 255), 'strokeColor'=>array(128, 128, 128));    
	protected $js_objects = array();    
	protected $form_action = '';    
	protected $form_enctype = 'application/x-www-form-urlencoded';    
	protected $form_mode = 'post';
    protected $annotation_fonts = array();    
	protected $radiobutton_groups = array();    
	protected $radio_groups = array();    
	protected $textindent = 0;    
	protected $start_transaction_page = 0;    
	protected $start_transaction_y = 0;    
	protected $inthead = false;    
	protected $columns = array();    
	protected $num_columns = 1;    
	protected $current_column = 0;    
	protected $column_start_page = 0;    
	protected $maxselcol = array('page' => 0, 'column' => 0);    
	protected $colxshift = array('x' => 0, 's' => array('H' => 0, 'V' => 0), 'p' => array('L' => 0, 'T' => 0, 'R' => 0, 'B' => 0));    
	protected $textrendermode = 0;
	protected $textstrokewidth = 0;
	protected $strokecolor;
	protected $pdfunit = 'mm';
	protected $tocpage = false;
	protected $rasterize_vector_images = false;
	protected $font_subsetting = true;
	protected $default_graphic_vars = array();
    protected $xobjects = array();
    
	//public $xobjects = array();
    
	protected $inxobj = false;
	protected $xobjid = '';
	protected $font_stretching = 100;
	protected $font_spacing = 0;
	protected $page_regions = array();
	protected $check_page_regions = true;
	protected $pdflayers = array();
	protected $dests = array();
	protected $n_dests;
	protected $efnames = array();
	protected $svgdir = '';
	protected $svgunit = 'px';
	protected $svggradients = array();
	protected $svggradientid = 0;
	protected $svgdefsmode = false;
	protected $svgdefs = array();
	protected $svgclipmode = false;
	protected $svgclippaths = array();
	protected $svgcliptm = array();
	protected $svgclipid = 0;
	protected $svgtext = '';
	protected $svgtextmode = array();
	protected $svgstyles = array(array(
		'alignment-baseline' => 'auto',
		'baseline-shift' => 'baseline',
		'clip' => 'auto',
		'clip-path' => 'none',
		'clip-rule' => 'nonzero',
		'color' => 'black',
		'color-interpolation' => 'sRGB',
		'color-interpolation-filters' => 'linearRGB',
		'color-profile' => 'auto',
		'color-rendering' => 'auto',
		'cursor' => 'auto',
		'direction' => 'ltr',
		'display' => 'inline',
		'dominant-baseline' => 'auto',
		'enable-background' => 'accumulate',
		'fill' => 'black',
		'fill-opacity' => 1,
		'fill-rule' => 'nonzero',
		'filter' => 'none',
		'flood-color' => 'black',
		'flood-opacity' => 1,
		'font' => '',
		'font-family' => 'helvetica',
		'font-size' => 'medium',
		'font-size-adjust' => 'none',
		'font-stretch' => 'normal',
		'font-style' => 'normal',
		'font-variant' => 'normal',
		'font-weight' => 'normal',
		'glyph-orientation-horizontal' => '0deg',
		'glyph-orientation-vertical' => 'auto',
		'image-rendering' => 'auto',
		'kerning' => 'auto',
		'letter-spacing' => 'normal',
		'lighting-color' => 'white',
		'marker' => '',
		'marker-end' => 'none',
		'marker-mid' => 'none',
		'marker-start' => 'none',
		'mask' => 'none',
		'opacity' => 1,
		'overflow' => 'auto',
		'pointer-events' => 'visiblePainted',
		'shape-rendering' => 'auto',
		'stop-color' => 'black',
		'stop-opacity' => 1,
		'stroke' => 'none',
		'stroke-dasharray' => 'none',
		'stroke-dashoffset' => 0,
		'stroke-linecap' => 'butt',
		'stroke-linejoin' => 'miter',
		'stroke-miterlimit' => 4,
		'stroke-opacity' => 1,
		'stroke-width' => 1,
		'text-anchor' => 'start',
		'text-decoration' => 'none',
		'text-rendering' => 'auto',
		'unicode-bidi' => 'normal',
		'visibility' => 'visible',
		'word-spacing' => 'normal',
		'writing-mode' => 'lr-tb',
		'text-color' => 'black',
		'transfmatrix' => array(1, 0, 0, 1, 0, 0)
		));
    
	protected $force_srgb = false;
    protected $pdfa_mode = false;
    
	//public $pdfa_mode = false;
    
	protected $pdfa_version = 1;
	protected $doc_creation_timestamp;
	protected $doc_modification_timestamp;
	protected $custom_xmp = '';
	protected $custom_xmp_rdf = '';
	protected $custom_xmp_rdf_pdfaExtension = '';
	protected $overprint = array('OP' => false, 'op' => false, 'OPM' => 0);
	protected $alpha = array('CA' => 1, 'ca' => 1, 'BM' => '/Normal', 'AIS' => false);
	protected $page_boxes = array('MediaBox', 'CropBox', 'BleedBox', 'TrimBox', 'ArtBox');
	protected $tcpdflink = true;
	protected $gdgammacache = array();
	protected $fileContentCache = array();
	protected $allowLocalFiles = false;
}
