<?php

namespace LimePDF;

	// includes Vars
	use LimePDF\LIMEPDF_STATIC;
	use LimePDF\LIMEPDF_FONT;
	use LimePDF\LIMEPDF_IMAGES;
	use LimePDF\LIMEPDF_FONT_DATA;
	use LimePDF\LIMEPDF_COLORS;
	//use LimePDF\Graphics\IMAGES;

	// limePDF configuration
	require_once(dirname(__FILE__).'/limepdf_autoconfig.php');



	// src files
	require_once(dirname(__FILE__).'/src/Encryption/limePDF_Encryption.php');

	require_once(dirname(__FILE__).'/src/Fonts/limePDF_FontManager.php');	
	require_once(dirname(__FILE__).'/src/Fonts/limePDF_Fonts.php');

	require_once(dirname(__FILE__).'/src/Graphics/limePDF_Barcode.php');
	require_once(dirname(__FILE__).'/src/Graphics/limePDF_Columns.php');
	require_once(dirname(__FILE__).'/src/Graphics/limePDF_Draw.php');
	require_once(dirname(__FILE__).'/src/Graphics/limePDF_Graphics.php');
	require_once(dirname(__FILE__).'/src/Graphics/limePDF_Images.php');	
	require_once(dirname(__FILE__).'/src/Graphics/limePDF_SVG.php');
	require_once(dirname(__FILE__).'/src/Graphics/limePDF_Transformations.php');
	require_once(dirname(__FILE__).'/src/Graphics/limePDF_XObjects_Templates.php');

	// Include files
	require_once(dirname(__FILE__).'/src//include/limePDF_Vars.php');
	require_once(dirname(__FILE__).'/src//include/limePDF_Static.php');	

	require_once(dirname(__FILE__).'/src/Pages/limePDF_Annotations.php');
	require_once(dirname(__FILE__).'/src/Pages/limePDF_Bookmarks.php');	
	require_once(dirname(__FILE__).'/src/Pages/limePDF_Pages.php');
	require_once(dirname(__FILE__).'/src/Pages/limePDF_PageManager.php');	
	require_once(dirname(__FILE__).'/src/Pages/limePDF_Margins.php');
	require_once(dirname(__FILE__).'/src/Pages/limePDF_PageColors.php');
	require_once(dirname(__FILE__).'/src/Pages/limePDF_Sections.php');	

	require_once(dirname(__FILE__).'/src/Utils/limePDF_Javascript.php');
	require_once(dirname(__FILE__).'/src/Utils/limePDF_Forms.php');
	require_once(dirname(__FILE__).'/src/Utils/limePDF_Environment.php');
	require_once(dirname(__FILE__).'/src/Utils/limePDF_Misc.php');	
	require_once(dirname(__FILE__).'/src/Utils/limePDF_Put.php');
	require_once(dirname(__FILE__).'/src/Utils/limePDF_Signature.php');	

	require_once(dirname(__FILE__).'/src/Model/limePDF_Barcode_GetterSetter.php');
	require_once(dirname(__FILE__).'/src/Model/limePDF_Font_GetterSetter.php');	
	require_once(dirname(__FILE__).'/src/Model/limePDF_Image_GetterSetter.php');		
	require_once(dirname(__FILE__).'/src/Model/limePDF_Page_GetterSetter.php');
	require_once(dirname(__FILE__).'/src/Model/limePDF_Text_GetterSetter.php');	
	require_once(dirname(__FILE__).'/src/Model/limePDF_Util_GetterSetter.php');
	require_once(dirname(__FILE__).'/src/Model/limePDF_Vars_GetterSetter.php');
	require_once(dirname(__FILE__).'/src/Model/limePDF_Web_GetterSetter.php');

	require_once(dirname(__FILE__).'/src/Text/limePDF_text.php');

	require_once(dirname(__FILE__).'/src/Web/limePDF_Web.php');
	
class TCPDF {
	
	use LIMEPDF_IMAGE;

	use LIMEPDF_ANNOTATIONS;

	use LIMEPDF_BOOKMARKS;
	use LIMEPDF_BARCODE;

	use LIMEPDF_COLUMNS;

	use LIMEPDF_DRAW;

	use LIMEPDF_ENCRYPTION;
	use LIMEPDF_ENVIRONMENT;

	use LIMEPDF_FONTMANAGER;	
	use LIMEPDF_FONTS;
	use LIMEPDF_FORMS;

	use LIMEPDF_GRAPHICS;



	use LIMEPDF_JAVASCRIPT;

	use LIMEPDF_MARGINS;
	use LIMEPDF_MISC;

	use LIMEPDF_PAGES;
	use LIMEPDF_PAGEMANAGER;	
	use LIMEPDF_PAGECOLORS;	
	use LIMEPDF_PUT;

	use LIMEPDF_SECTIONS;
	use LIMEPDF_SIGNATURE;
	use LIMEPDF_SVG;

	use LIMEPDF_TEXT;
	use LIMEPDF_TRANSFORMATIONS;

	use LIMEPDF_VARS;

	use LIMEPDF_WEB;


	use LIMEPDF_XOTEMPLATES;

	// Load Getter Setters
	use LIMEPDF_BARCODE_GETTERSETTER;	
	use LIMEPDF_FONT_GETTERSETTER;
	use LIMEPDF_IMAGE_GETTERSETTER;	
	use LIMEPDF_PAGE_GETTERSETTER;	
	use LIMEPDF_TEXT_GETTERSETTER;
	use LIMEPDF_UTIL_GETTERSETTER;	
	use LIMEPDF_VARS_GETTERSETTER;
	use LIMEPDF_WEB_GETTERSETTER;

	public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
    
		// set file ID for trailer
		$serformat = (is_array($format) ? json_encode($format) : $format);
		$this->file_id = md5(LIMEPDF_STATIC::getRandomSeed('TCPDF'.$orientation.$unit.$serformat.$encoding));
		$this->hash_key = hash_hmac('sha256', LIMEPDF_STATIC::getRandomSeed($this->file_id), LIMEPDF_STATIC::getRandomSeed('TCPDF'), false);
		$this->font_obj_ids = array();
		$this->page_obj_id = array();
		$this->form_obj_id = array();
		// set pdf/a mode
		if ($pdfa != false) {
			$this->pdfa_mode = true;
			$this->pdfa_version = $pdfa;  // 1 or 3
		} else
			$this->pdfa_mode = false;

		$this->force_srgb = false;
		// set language direction
		$this->rtl = false;
		$this->tmprtl = false;
		// some checks
		$this->_doChecks();
		// initialization of properties
		$this->isunicode = $unicode;
		$this->page = 0;
		$this->transfmrk[0] = array();
		$this->pagedim = array();
		$this->n = 2;
		$this->buffer = '';
		$this->pages = array();
		$this->state = 0;
		$this->fonts = array();
		$this->FontFiles = array();
		$this->diffs = array();
		$this->images = array();
		$this->links = array();
		$this->gradients = array();
		$this->InFooter = false;
		$this->lasth = 0;
		$this->FontFamily = defined('PDF_FONT_NAME_MAIN')?PDF_FONT_NAME_MAIN:'helvetica';
		$this->FontStyle = '';
		$this->FontSizePt = 12;
		$this->underline = false;
		$this->overline = false;
		$this->linethrough = false;
		$this->DrawColor = '0 G';
		$this->FillColor = '0 g';
		$this->TextColor = '0 g';
		$this->ColorFlag = false;
		$this->pdflayers = array();
		// encryption values
		$this->encrypted = false;
		$this->last_enc_key = '';
		// standard Unicode fonts
		$this->CoreFonts = array(
			'courier'=>'Courier',
			'courierB'=>'Courier-Bold',
			'courierI'=>'Courier-Oblique',
			'courierBI'=>'Courier-BoldOblique',
			'helvetica'=>'Helvetica',
			'helveticaB'=>'Helvetica-Bold',
			'helveticaI'=>'Helvetica-Oblique',
			'helveticaBI'=>'Helvetica-BoldOblique',
			'times'=>'Times-Roman',
			'timesB'=>'Times-Bold',
			'timesI'=>'Times-Italic',
			'timesBI'=>'Times-BoldItalic',
			'symbol'=>'Symbol',
			'zapfdingbats'=>'ZapfDingbats'
		);
		// set scale factor
		$this->setPageUnit($unit);
		// set page format and orientation
		$this->setPageFormat($format, $orientation);
		// page margins (1 cm)
		$margin = 28.35 / $this->k;
		$this->setMargins($margin, $margin);
		$this->clMargin = $this->lMargin;
		$this->crMargin = $this->rMargin;
		// internal cell padding
		$cpadding = $margin / 10;
		$this->setCellPaddings($cpadding, 0, $cpadding, 0);
		// cell margins
		$this->setCellMargins(0, 0, 0, 0);
		// line width (0.2 mm)
		$this->LineWidth = 0.57 / $this->k;
		$this->linestyleWidth = sprintf('%F w', ($this->LineWidth * $this->k));
		$this->linestyleCap = '0 J';
		$this->linestyleJoin = '0 j';
		$this->linestyleDash = '[] 0 d';
		// automatic page break
		$this->setAutoPageBreak(true, (2 * $margin));
		// full width display mode
		$this->setDisplayMode('fullwidth');
		// compression
		$this->setCompression();
		// set default PDF version number
		$this->setPDFVersion();
		$this->tcpdflink = true;
		$this->encoding = $encoding;
		$this->HREF = array();
		$this->getFontsList();
		$this->fgcolor = array('R' => 0, 'G' => 0, 'B' => 0);
		$this->strokecolor = array('R' => 0, 'G' => 0, 'B' => 0);
		$this->bgcolor = array('R' => 255, 'G' => 255, 'B' => 255);
		$this->extgstates = array();
		$this->setTextShadow();
		// signature
		$this->sign = false;
		$this->tsa_timestamp = false;
		$this->tsa_data = array();
		$this->signature_appearance = array('page' => 1, 'rect' => '0 0 0 0', 'name' => 'Signature');
		$this->empty_signature_appearance = array();
		// user's rights
		$this->ur['enabled'] = false;
		$this->ur['document'] = '/FullSave';
		$this->ur['annots'] = '/Create/Delete/Modify/Copy/Import/Export';
		$this->ur['form'] = '/Add/Delete/FillIn/Import/Export/SubmitStandalone/SpawnTemplate';
		$this->ur['signature'] = '/Modify';
		$this->ur['ef'] = '/Create/Delete/Modify/Import';
		$this->ur['formex'] = '';
		// set default JPEG quality
		$this->jpeg_quality = 75;
		// initialize some settings
		LIMEPDF_FONT::utf8Bidi(array(), '', false, $this->isunicode, $this->CurrentFont);
		// set default font
		$this->setFont($this->FontFamily, $this->FontStyle, $this->FontSizePt);
		$this->setHeaderFont(array($this->FontFamily, $this->FontStyle, $this->FontSizePt));
		$this->setFooterFont(array($this->FontFamily, $this->FontStyle, $this->FontSizePt));
		// check if PCRE Unicode support is enabled
		if ($this->isunicode AND (@preg_match('/\pL/u', 'a') == 1)) {
			// PCRE unicode support is turned ON
			// \s     : any whitespace character
			// \p{Z}  : any separator
			// \p{Lo} : Unicode letter or ideograph that does not have lowercase and uppercase variants. Is used to chunk chinese words.
			// \xa0   : Unicode Character 'NO-BREAK SPACE' (U+00A0)
			//$this->setSpacesRE('/(?!\xa0)[\s\p{Z}\p{Lo}]/u');
			$this->setSpacesRE('/(?!\xa0)[\s\p{Z}]/u');
		} else {
			// PCRE unicode support is turned OFF
			$this->setSpacesRE('/[^\S\xa0]/');
		}
		$this->default_form_prop = array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 255), 'strokeColor'=>array(128, 128, 128));
		// set document creation and modification timestamp
		$this->doc_creation_timestamp = time();
		$this->doc_modification_timestamp = $this->doc_creation_timestamp;
		// get default graphic vars
		$this->default_graphic_vars = $this->getGraphicVars();
		$this->header_xobj_autoreset = false;
		$this->custom_xmp = '';
		$this->custom_xmp_rdf = '';
	}

	/**
	 * Default destructor.
	 * @public
	 * @since 1.53.0.TC016
	 */
	public function __destruct() {
		// cleanup
		$this->_destroy(true);
	}

	/**
	 * Reset the last cell height.
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 */
	public function resetLastH() {
		$this->lasth = $this->getCellHeight($this->FontSize);
	}

	/**
	 * Throw an exception or print an error message and die if the K_TCPDF_PARSER_THROW_EXCEPTION_ERROR constant is set to true.
	 * @param string $msg The error message
	 * @public
	 * @since 1.0
	 */
	// public function Error($msg) {
	// 	// unset all class variables
	// 	$this->_destroy(true);
	// 	$msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
	// 	if (defined('K_TCPDF_THROW_EXCEPTION_ERROR') AND !K_TCPDF_THROW_EXCEPTION_ERROR) {
	// 		die('<strong>TCPDF ERROR: </strong>'.$msg);
	// 	} else {
	// 		throw new Exception('TCPDF ERROR: '.$msg);
	// 	}
	// }
	// 8+ -- error improvement
	public function Error(string $msg): never 
	{
		$this->_destroy(true);
		$msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
		
		if ($this->shouldThrowExceptions()) {
			throw new TCPDFException($msg);
		}
		
		$this->outputError($msg);
		exit(1);
	}

	private function shouldThrowExceptions(): bool 
	{
		return !defined('K_TCPDF_THROW_EXCEPTION_ERROR') || K_TCPDF_THROW_EXCEPTION_ERROR;
}

	/**
	 * This method begins the generation of the PDF document.
	 * It is not necessary to call it explicitly because AddPage() does it automatically.
	 * Note: no page is created by this method
	 * @public
	 * @since 1.0
	 * @see AddPage(), Close()
	 */
	public function Open() {
		$this->state = 1;
	}

	/**
	 * Terminates the PDF document.
	 * It is not necessary to call this method explicitly because Output() does it automatically.
	 * If the document contains no page, AddPage() is called to prevent from getting an invalid document.
	 * @public
	 * @since 1.0
	 * @see Open(), Output()
	 */
	public function Close() {
		if ($this->state == 3) {
			return;
		}
		if ($this->page == 0) {
			$this->AddPage();
		}
		$this->endLayer();
		if ($this->tcpdflink) {
			// save current graphic settings
			$gvars = $this->getGraphicVars();
			$this->setEqualColumns();
			$this->lastpage(true);
			$this->setAutoPageBreak(false);
			$this->x = 0;
			$this->y = $this->h - (1 / $this->k);
			$this->lMargin = 0;
			$this->_outSaveGraphicsState();
			$font = defined('PDF_FONT_NAME_MAIN')?PDF_FONT_NAME_MAIN:'helvetica';
			$this->setFont($font, '', 1);
			$this->setTextRenderingMode(0, false, false);
			$msg = "\x50\x6f\x77\x65\x72\x65\x64\x20\x62\x79\x20\x54\x43\x50\x44\x46\x20\x28\x77\x77\x77\x2e\x74\x63\x70\x64\x66\x2e\x6f\x72\x67\x29";
			$lnk = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x74\x63\x70\x64\x66\x2e\x6f\x72\x67";
			$this->Cell(0, 0, $msg, 0, 0, 'L', 0, $lnk, 0, false, 'D', 'B');
			$this->_outRestoreGraphicsState();
			// restore graphic settings
			$this->setGraphicVars($gvars);
		}
		// close page
		$this->endPage();
		// close document
		$this->_enddoc();
		// unset all class variables (except critical ones)
		$this->_destroy(false);
	}

	

	/**
	 * Prints a text cell at the specified position.
	 * This method allows to place a string precisely on the page.
	 * @param float $x Abscissa of the cell origin
	 * @param float $y Ordinate of the cell origin
	 * @param string $txt String to print
	 * @param int $fstroke outline size in user units (0 = disable)
	 * @param boolean $fclip if true activate clipping mode (you must call StartTransform() before this function and StopTransform() to stop the clipping tranformation).
	 * @param boolean $ffill if true fills the text
	 * @param mixed $border Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param int $ln Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right (or left for RTL languages)</li><li>1: to the beginning of the next line</li><li>2: below</li></ul>Putting 1 is equivalent to putting 0 and calling Ln() just after. Default value: 0.
	 * @param string $align Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align (default value)</li><li>C: center</li><li>R: right align</li><li>J: justify</li></ul>
	 * @param boolean $fill Indicates if the cell background must be painted (true) or transparent (false).
	 * @param mixed $link URL or identifier returned by AddLink().
	 * @param int $stretch font stretch mode: <ul><li>0 = disabled</li><li>1 = horizontal scaling only if text is larger than cell width</li><li>2 = forced horizontal scaling to fit cell width</li><li>3 = character spacing only if text is larger than cell width</li><li>4 = forced character spacing to fit cell width</li></ul> General font stretching and scaling values will be preserved when possible.
	 * @param boolean $ignore_min_height if true ignore automatic minimum height value.
	 * @param string $calign cell vertical alignment relative to the specified Y value. Possible values are:<ul><li>T : cell top</li><li>A : font top</li><li>L : font baseline</li><li>D : font bottom</li><li>B : cell bottom</li></ul>
	 * @param string $valign text vertical alignment inside the cell. Possible values are:<ul><li>T : top</li><li>C : center</li><li>B : bottom</li></ul>
	 * @param boolean $rtloff if true uses the page top-left corner as origin of axis for $x and $y initial position.
	 * @public
	 * @since 1.0
	 * @see Cell(), Write(), MultiCell(), WriteHTML(), WriteHTMLCell()
	 */
	public function Text($x, $y, $txt, $fstroke=0, $fclip=false, $ffill=true, $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M', $rtloff=false) {
		$textrendermode = $this->textrendermode;
		$textstrokewidth = $this->textstrokewidth;
		$this->setTextRenderingMode($fstroke, $ffill, $fclip);
		$this->setXY($x, $y, $rtloff);
		$this->Cell(0, 0, $txt, $border, $ln, $align, $fill, $link, $stretch, $ignore_min_height, $calign, $valign);
		// restore previous rendering mode
		$this->textrendermode = $textrendermode;
		$this->textstrokewidth = $textstrokewidth;
	}

	/**
	 * Whenever a page break condition is met, the method is called, and the break is issued or not depending on the returned value.
	 * The default implementation returns a value according to the mode selected by SetAutoPageBreak().<br />
	 * This method is called automatically and should not be called directly by the application.
	 * @return bool
	 * @public
	 * @since 1.4
	 * @see SetAutoPageBreak()
	 */
	public function AcceptPageBreak() {
		if ($this->num_columns > 1) {
			// multi column mode
			if ($this->current_column < ($this->num_columns - 1)) {
				// go to next column
				$this->selectColumn($this->current_column + 1);
			} elseif ($this->AutoPageBreak) {
				// add a new page
				$this->AddPage();
				// set first column
				$this->selectColumn(0);
			}
			// avoid page breaking from checkPageBreak()
			return false;
		}
		return $this->AutoPageBreak;
	}

	/**
	 * Add page if needed.
	 * @param float $h Cell height. Default value: 0.
	 * @param float|null $y starting y position, leave empty for current position.
	 * @param bool  $addpage if true add a page, otherwise only return the true/false state
	 * @return bool true in case of page break, false otherwise.
	 * @since 3.2.000 (2008-07-01)
	 * @protected
	 */
	protected function checkPageBreak($h=0, $y=null, $addpage=true) {
		if (LIMEPDF_STATIC::empty_string($y)) {
			$y = $this->y;
		}
		$current_page = $this->page;
		if ((($y + $h) > $this->PageBreakTrigger) AND ($this->inPageBody()) AND ($this->AcceptPageBreak())) {
			if ($addpage) {
				//Automatic page break
				$x = $this->x;
				$this->AddPage($this->CurOrientation);
				$this->y = $this->tMargin;
				$oldpage = $this->page - 1;
				if ($this->rtl) {
					if ($this->pagedim[$this->page]['orm'] != $this->pagedim[$oldpage]['orm']) {
						$this->x = $x - ($this->pagedim[$this->page]['orm'] - $this->pagedim[$oldpage]['orm']);
					} else {
						$this->x = $x;
					}
				} else {
					if ($this->pagedim[$this->page]['olm'] != $this->pagedim[$oldpage]['olm']) {
						$this->x = $x + ($this->pagedim[$this->page]['olm'] - $this->pagedim[$oldpage]['olm']);
					} else {
						$this->x = $x;
					}
				}
			}
			return true;
		}
		if ($current_page != $this->page) {
			// account for columns mode
			return true;
		}
		return false;
	}

	/**
	 * Replace a char if is defined on the current font.
	 * @param int $oldchar Integer code (unicode) of the character to replace.
	 * @param int $newchar Integer code (unicode) of the new character.
	 * @return int the replaced char or the old char in case the new char i not defined
	 * @protected
	 * @since 5.9.167 (2012-06-22)
	 */
	protected function replaceChar($oldchar, $newchar) {
		if ($this->isCharDefined($newchar)) {
			// add the new char on the subset list
			$this->CurrentFont['subsetchars'][$newchar] = true;
			// return the new character
			return $newchar;
		}
		// return the old char
		return $oldchar;
	}

	/**
	 * This method prints text from the current position.<br />
	 * @param float $h Line height
	 * @param string $txt String to print
	 * @param mixed $link URL or identifier returned by AddLink()
	 * @param boolean $fill Indicates if the cell background must be painted (true) or transparent (false).
	 * @param string $align Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align (default value)</li><li>C: center</li><li>R: right align</li><li>J: justify</li></ul>
	 * @param boolean $ln if true set cursor at the bottom of the line, otherwise set cursor at the top of the line.
	 * @param int $stretch font stretch mode: <ul><li>0 = disabled</li><li>1 = horizontal scaling only if text is larger than cell width</li><li>2 = forced horizontal scaling to fit cell width</li><li>3 = character spacing only if text is larger than cell width</li><li>4 = forced character spacing to fit cell width</li></ul> General font stretching and scaling values will be preserved when possible.
	 * @param boolean $firstline if true prints only the first line and return the remaining string.
	 * @param boolean $firstblock if true the string is the starting of a line.
	 * @param float $maxh maximum height. It should be >= $h and less then remaining space to the bottom of the page, or 0 for disable this feature.
	 * @param float $wadj first line width will be reduced by this amount (used in HTML mode).
	 * @param array|null $margin margin array of the parent container
	 * @return mixed Return the number of cells or the remaining string if $firstline = true.
	 * @public
	 * @since 1.5
	 */
	public function Write($h, $txt, $link='', $fill=false, $align='', $ln=false, $stretch=0, $firstline=false, $firstblock=false, $maxh=0, $wadj=0, $margin=null) {
		// check page for no-write regions and adapt page margins if necessary
		list($this->x, $this->y) = $this->checkPageRegions($h, $this->x, $this->y);
		if (strlen($txt) == 0) {
			// fix empty text
			$txt = ' ';
		}
		if (!is_array($margin)) {
			// set default margins
			$margin = $this->cell_margin;
		}
		// remove carriage returns
		$s = str_replace("\r", '', $txt);
		// check if string contains arabic text
		if (preg_match(LIMEPDF_FONT_DATA::$uni_RE_PATTERN_ARABIC, $s)) {
			$arabic = true;
		} else {
			$arabic = false;
		}
		// check if string contains RTL text
		if ($arabic OR ($this->tmprtl == 'R') OR preg_match(LIMEPDF_FONT_DATA::$uni_RE_PATTERN_RTL, $s)) {
			$rtlmode = true;
		} else {
			$rtlmode = false;
		}
		// get a char width
		$chrwidth = $this->GetCharWidth(46); // dot character
		// get array of unicode values
		$chars = LIMEPDF_FONT::UTF8StringToArray($s, $this->isunicode, $this->CurrentFont);
		// calculate maximum width for a single character on string
		$chrw = $this->GetArrStringWidth($chars, '', '', 0, true);
		array_walk($chrw, array($this, 'getRawCharWidth'));
		$maxchwidth = ((is_array($chrw) || $chrw instanceof Countable) && count($chrw) > 0) ? max($chrw) : 0;
		// get array of chars
		$uchars = LIMEPDF_FONT::UTF8ArrayToUniArray($chars, $this->isunicode);
		// get the number of characters
		$nb = count($chars);
		// replacement for SHY character (minus symbol)
		$shy_replacement = 45;
		$shy_replacement_char = LIMEPDF_FONT::unichr($shy_replacement, $this->isunicode);
		// widht for SHY replacement
		$shy_replacement_width = $this->GetCharWidth($shy_replacement);
		// page width
		$pw = $w = $this->w - $this->lMargin - $this->rMargin;
		// calculate remaining line width ($w)
		if ($this->rtl) {
			$w = $this->x - $this->lMargin;
		} else {
			$w = $this->w - $this->rMargin - $this->x;
		}
		// max column width
		$wmax = ($w - $wadj);
		if (!$firstline) {
			$wmax -= ($this->cell_padding['L'] + $this->cell_padding['R']);
		}
		if ((!$firstline) AND (($chrwidth > $wmax) OR ($maxchwidth > $wmax))) {
			// the maximum width character do not fit on column
			return '';
		}
		// minimum row height
		$row_height = max($h, $this->getCellHeight($this->FontSize));
		// max Y
		$maxy = $this->y + $maxh - max($row_height, $h);
		$start_page = $this->page;
		$i = 0; // character position
		$j = 0; // current starting position
		$sep = -1; // position of the last blank space
		$prevsep = $sep; // previous separator
		$shy = false; // true if the last blank is a soft hypen (SHY)
		$prevshy = $shy; // previous shy mode
		$l = 0; // current string length
		$nl = 0; //number of lines
		$linebreak = false;
		$pc = 0; // previous character
		// for each character
		while ($i < $nb) {
			if (($maxh > 0) AND ($this->y > $maxy) ) {
				break;
			}
			//Get the current character
			$c = $chars[$i];
			if ($c == 10) { // 10 = "\n" = new line
				//Explicit line break
				if ($align == 'J') {
					if ($this->rtl) {
						$talign = 'R';
					} else {
						$talign = 'L';
					}
				} else {
					$talign = $align;
				}
				$tmpstr = LIMEPDF_FONT::UniArrSubString($uchars, $j, $i);
				if ($firstline) {
					$startx = $this->x;
					$tmparr = array_slice($chars, $j, ($i - $j));
					if ($rtlmode) {
						$tmparr = LIMEPDF_FONT::utf8Bidi($tmparr, $tmpstr, $this->tmprtl, $this->isunicode, $this->CurrentFont);
					}
					$linew = $this->GetArrStringWidth($tmparr);
					unset($tmparr);
					if ($this->rtl) {
						$this->endlinex = $startx - $linew;
					} else {
						$this->endlinex = $startx + $linew;
					}
					$w = $linew;
					$tmpcellpadding = $this->cell_padding;
					if ($maxh == 0) {
						$this->setCellPadding(0);
					}
				}
				if ($firstblock AND $this->isRTLTextDir()) {
					$tmpstr = $this->stringRightTrim($tmpstr);
				}
				// Skip newlines at the beginning of a page or column
				if (!empty($tmpstr) OR ($this->y < ($this->PageBreakTrigger - $row_height))) {
					$this->Cell($w, $h, $tmpstr, 0, 1, $talign, $fill, $link, $stretch);
				}
				unset($tmpstr);
				if ($firstline) {
					$this->cell_padding = $tmpcellpadding;
					return (LIMEPDF_FONT::UniArrSubString($uchars, $i));
				}
				++$nl;
				$j = $i + 1;
				$l = 0;
				$sep = -1;
				$prevsep = $sep;
				$shy = false;
				// account for margin changes
				if ((($this->y + $this->lasth) > $this->PageBreakTrigger) AND ($this->inPageBody())) {
					if ($this->AcceptPageBreak())
					{
						if ($this->rtl) {
							$this->x -= $margin['R'];
						} else {
							$this->x += $margin['L'];
						}
						$this->lMargin += $margin['L'];
						$this->rMargin += $margin['R'];
					}
				}
				$w = $this->getRemainingWidth();
				$wmax = ($w - $this->cell_padding['L'] - $this->cell_padding['R']);
			} else {
				// 160 is the non-breaking space.
				// 173 is SHY (Soft Hypen).
				// \p{Z} or \p{Separator}: any kind of Unicode whitespace or invisible separator.
				// \p{Lo} or \p{Other_Letter}: a Unicode letter or ideograph that does not have lowercase and uppercase variants.
				// \p{Lo} is needed because Chinese characters are packed next to each other without spaces in between.
				if (($c != 160)
					AND (($c == 173)
						OR preg_match($this->re_spaces, LIMEPDF_FONT::unichr($c, $this->isunicode))
						OR (($c == 45)
							AND ($i < ($nb - 1))
							AND @preg_match('/[\p{L}]/'.$this->re_space['m'], LIMEPDF_FONT::unichr($pc, $this->isunicode))
							AND @preg_match('/[\p{L}]/'.$this->re_space['m'], LIMEPDF_FONT::unichr($chars[($i + 1)], $this->isunicode))
						)
					)
				) {
					// update last blank space position
					$prevsep = $sep;
					$sep = $i;
					// check if is a SHY
					if (($c == 173) OR ($c == 45)) {
						$prevshy = $shy;
						$shy = true;
						if ($pc == 45) {
							$tmp_shy_replacement_width = 0;
							$tmp_shy_replacement_char = '';
						} else {
							$tmp_shy_replacement_width = $shy_replacement_width;
							$tmp_shy_replacement_char = $shy_replacement_char;
						}
					} else {
						$shy = false;
					}
				}
				// update string length
				if ($this->isUnicodeFont() AND ($arabic)) {
					// with bidirectional algorithm some chars may be changed affecting the line length
					// *** very slow ***
					$l = $this->GetArrStringWidth(LIMEPDF_FONT::utf8Bidi(array_slice($chars, $j, ($i - $j)), '', $this->tmprtl, $this->isunicode, $this->CurrentFont));
				} else {
					$l += $this->GetCharWidth($c, ($i+1 < $nb));
				}
				if (($l > $wmax) OR (($c == 173) AND (($l + $tmp_shy_replacement_width) >= $wmax))) {
					if (($c == 173) AND (($l + $tmp_shy_replacement_width) > $wmax)) {
						$sep = $prevsep;
						$shy = $prevshy;
					}
					// we have reached the end of column
					if ($sep == -1) {
						// check if the line was already started
						if (($this->rtl AND ($this->x <= ($this->w - $this->rMargin - $this->cell_padding['R'] - $margin['R'] - $chrwidth)))
							OR ((!$this->rtl) AND ($this->x >= ($this->lMargin + $this->cell_padding['L'] + $margin['L'] + $chrwidth)))) {
							// print a void cell and go to next line
							$this->Cell($w, $h, '', 0, 1);
							$linebreak = true;
							if ($firstline) {
								return (LIMEPDF_FONT::UniArrSubString($uchars, $j));
							}
						} else {
							// truncate the word because do not fit on column
							$tmpstr = LIMEPDF_FONT::UniArrSubString($uchars, $j, $i);
							if ($firstline) {
								$startx = $this->x;
								$tmparr = array_slice($chars, $j, ($i - $j));
								if ($rtlmode) {
									$tmparr = LIMEPDF_FONT::utf8Bidi($tmparr, $tmpstr, $this->tmprtl, $this->isunicode, $this->CurrentFont);
								}
								$linew = $this->GetArrStringWidth($tmparr);
								unset($tmparr);
								if ($this->rtl) {
									$this->endlinex = $startx - $linew;
								} else {
									$this->endlinex = $startx + $linew;
								}
								$w = $linew;
								$tmpcellpadding = $this->cell_padding;
								if ($maxh == 0) {
									$this->setCellPadding(0);
								}
							}
							if ($firstblock AND $this->isRTLTextDir()) {
								$tmpstr = $this->stringRightTrim($tmpstr);
							}
							$this->Cell($w, $h, $tmpstr, 0, 1, $align, $fill, $link, $stretch);
							unset($tmpstr);
							if ($firstline) {
								$this->cell_padding = $tmpcellpadding;
								return (LIMEPDF_FONT::UniArrSubString($uchars, $i));
							}
							$j = $i;
							--$i;
						}
					} else {
						// word wrapping
						if ($this->rtl AND (!$firstblock) AND ($sep < $i)) {
							$endspace = 1;
						} else {
							$endspace = 0;
						}
						// check the length of the next string
						$strrest = LIMEPDF_FONT::UniArrSubString($uchars, ($sep + $endspace));
						$nextstr = LIMEPDF_STATIC::pregSplit('/'.$this->re_space['p'].'/', $this->re_space['m'], $this->stringTrim($strrest));
						// PHP 8+ -- throws error
						//if ($nextstr?.[0] !== null && $this->GetStringWidth($nextstr[0]) > $pw) {
						if (isset($nextstr[0]) AND ($this->GetStringWidth($nextstr[0]) > $pw)) {
							// truncate the word because do not fit on a full page width
							$tmpstr = LIMEPDF_FONT::UniArrSubString($uchars, $j, $i);
							if ($firstline) {
								$startx = $this->x;
								$tmparr = array_slice($chars, $j, ($i - $j));
								if ($rtlmode) {
									$tmparr = LIMEPDF_FONT::utf8Bidi($tmparr, $tmpstr, $this->tmprtl, $this->isunicode, $this->CurrentFont);
								}
								$linew = $this->GetArrStringWidth($tmparr);
								unset($tmparr);
								if ($this->rtl) {
									$this->endlinex = ($startx - $linew);
								} else {
									$this->endlinex = ($startx + $linew);
								}
								$w = $linew;
								$tmpcellpadding = $this->cell_padding;
								if ($maxh == 0) {
									$this->setCellPadding(0);
								}
							}
							if ($firstblock AND $this->isRTLTextDir()) {
								$tmpstr = $this->stringRightTrim($tmpstr);
							}
							$this->Cell($w, $h, $tmpstr, 0, 1, $align, $fill, $link, $stretch);
							unset($tmpstr);
							if ($firstline) {
								$this->cell_padding = $tmpcellpadding;
								return (LIMEPDF_FONT::UniArrSubString($uchars, $i));
							}
							$j = $i;
							--$i;
						} else {
							// word wrapping
							if ($shy) {
								// add hypen (minus symbol) at the end of the line
								$shy_width = $tmp_shy_replacement_width;
								if ($this->rtl) {
									$shy_char_left = $tmp_shy_replacement_char;
									$shy_char_right = '';
								} else {
									$shy_char_left = '';
									$shy_char_right = $tmp_shy_replacement_char;
								}
							} else {
								$shy_width = 0;
								$shy_char_left = '';
								$shy_char_right = '';
							}
							$tmpstr = LIMEPDF_FONT::UniArrSubString($uchars, $j, ($sep + $endspace));
							if ($firstline) {
								$startx = $this->x;
								$tmparr = array_slice($chars, $j, (($sep + $endspace) - $j));
								if ($rtlmode) {
									$tmparr = LIMEPDF_FONT::utf8Bidi($tmparr, $tmpstr, $this->tmprtl, $this->isunicode, $this->CurrentFont);
								}
								$linew = $this->GetArrStringWidth($tmparr);
								unset($tmparr);
								if ($this->rtl) {
									$this->endlinex = $startx - $linew - $shy_width;
								} else {
									$this->endlinex = $startx + $linew + $shy_width;
								}
								$w = $linew;
								$tmpcellpadding = $this->cell_padding;
								if ($maxh == 0) {
									$this->setCellPadding(0);
								}
							}
							// print the line
							if ($firstblock AND $this->isRTLTextDir()) {
								$tmpstr = $this->stringRightTrim($tmpstr);
							}
							$this->Cell($w, $h, $shy_char_left.$tmpstr.$shy_char_right, 0, 1, $align, $fill, $link, $stretch);
							unset($tmpstr);
							if ($firstline) {
								if ($chars[$sep] == 45) {
									$endspace += 1;
								}
								// return the remaining text
								$this->cell_padding = $tmpcellpadding;
								return (LIMEPDF_FONT::UniArrSubString($uchars, ($sep + $endspace)));
							}
							$i = $sep;
							$sep = -1;
							$shy = false;
							$j = ($i + 1);
						}
					}
					// account for margin changes
					if ((($this->y + $this->lasth) > $this->PageBreakTrigger) AND ($this->inPageBody())) {
						if ($this->AcceptPageBreak())
						{
							if ($this->rtl) {
								$this->x -= $margin['R'];
							} else {
								$this->x += $margin['L'];
							}
							$this->lMargin += $margin['L'];
							$this->rMargin += $margin['R'];
						}
					}
					$w = $this->getRemainingWidth();
					$wmax = $w - $this->cell_padding['L'] - $this->cell_padding['R'];
					if ($linebreak) {
						$linebreak = false;
					} else {
						++$nl;
						$l = 0;
					}
				}
			}
			// save last character
			$pc = $c;
			++$i;
		} // end while i < nb
		// print last substring (if any)
		if ($l > 0) {
			// PHP 8+ Match Expression
			$w = match($align) {
				'J', 'C' => $w, // keep current width
				'L' => !$this->rtl ? $l : $w,
				'R' => $this->rtl ? $l : $w,
				default => $l
			};

			// switch ($align) {
			// 	case 'J':
			// 	case 'C': {
			// 		break;
			// 	}
			// 	case 'L': {
			// 		if (!$this->rtl) {
			// 			$w = $l;
			// 		}
			// 		break;
			// 	}
			// 	case 'R': {
			// 		if ($this->rtl) {
			// 			$w = $l;
			// 		}
			// 		break;
			// 	}
			// 	default: {
			// 		$w = $l;
			// 		break;
			// 	}
			// }
			$tmpstr = LIMEPDF_FONT::UniArrSubString($uchars, $j, $nb);
			if ($firstline) {
				$startx = $this->x;
				$tmparr = array_slice($chars, $j, ($nb - $j));
				if ($rtlmode) {
					$tmparr = LIMEPDF_FONT::utf8Bidi($tmparr, $tmpstr, $this->tmprtl, $this->isunicode, $this->CurrentFont);
				}
				$linew = $this->GetArrStringWidth($tmparr);
				unset($tmparr);
				if ($this->rtl) {
					$this->endlinex = $startx - $linew;
				} else {
					$this->endlinex = $startx + $linew;
				}
				$w = $linew;
				$tmpcellpadding = $this->cell_padding;
				if ($maxh == 0) {
					$this->setCellPadding(0);
				}
			}
			if ($firstblock AND $this->isRTLTextDir()) {
				$tmpstr = $this->stringRightTrim($tmpstr);
			}
			$this->Cell($w, $h, $tmpstr, 0, $ln, $align, $fill, $link, $stretch);
			unset($tmpstr);
			if ($firstline) {
				$this->cell_padding = $tmpcellpadding;
				return (LIMEPDF_FONT::UniArrSubString($uchars, $nb));
			}
			++$nl;
		}
		if ($firstline) {
			return '';
		}
		return $nl;
	}



	/**
	 * Send the document to a given destination: string, local file or browser.
	 * In the last case, the plug-in may be used (if present) or a download ("Save as" dialog box) may be forced.<br />
	 * The method first calls Close() if necessary to terminate the document.
	 * @param string $name The name of the file when saved
	 * @param string $dest Destination where to send the document. It can take one of the following values:<ul><li>I: send the file inline to the browser (default). The plug-in is used if available. The name given by name is used when one selects the "Save as" option on the link generating the PDF.</li><li>D: send to the browser and force a file download with the name given by name.</li><li>F: save to a local server file with the name given by name.</li><li>S: return the document as a string (name is ignored).</li><li>FI: equivalent to F + I option</li><li>FD: equivalent to F + D option</li><li>E: return the document as base64 mime multi-part email attachment (RFC 2045)</li></ul>
	 * @return string
	 * @public
	 * @since 1.0
	 * @see Close()
	 */
	public function Output($name='doc.pdf', $dest='I') {
		//Output PDF to some destination
		//Finish document if necessary
		if ($this->state < 3) {
			$this->Close();
		}
		//Normalize parameters
		if (is_bool($dest)) {
			$dest = $dest ? 'D' : 'F';
		}
		$dest = strtoupper($dest);

		if ($this->sign) {
			// *** apply digital signature to the document ***
			// get the document content
			$pdfdoc = $this->getBuffer();
			// remove last newline
			$pdfdoc = substr($pdfdoc, 0, -1);
			// remove filler space
			$byterange_string_len = strlen(LIMEPDF_STATIC::$byterange_string);
			// define the ByteRange
			$byte_range = array();
			$byte_range[0] = 0;
			$byte_range[1] = strpos($pdfdoc, LIMEPDF_STATIC::$byterange_string) + $byterange_string_len + 10;
			$byte_range[2] = $byte_range[1] + $this->signature_max_length + 2;
			$byte_range[3] = strlen($pdfdoc) - $byte_range[2];
			$pdfdoc = substr($pdfdoc, 0, $byte_range[1]).substr($pdfdoc, $byte_range[2]);
			// replace the ByteRange
			$byterange = sprintf('/ByteRange[0 %u %u %u]', $byte_range[1], $byte_range[2], $byte_range[3]);
			$byterange .= str_repeat(' ', ($byterange_string_len - strlen($byterange)));
			$pdfdoc = str_replace(LIMEPDF_STATIC::$byterange_string, $byterange, $pdfdoc);
			// write the document to a temporary folder
			$tempdoc = LIMEPDF_STATIC::getObjFilename('doc', $this->file_id);
			$f = LIMEPDF_STATIC::fopenLocal($tempdoc, 'wb');
			if (!$f) {
				$this->Error('Unable to create temporary file: '.$tempdoc);
			}
			$pdfdoc_length = strlen($pdfdoc);
			fwrite($f, $pdfdoc, $pdfdoc_length);
			fclose($f);
			// get digital signature via openssl library
			$tempsign = LIMEPDF_STATIC::getObjFilename('sig', $this->file_id);
			if (empty($this->signature_data['extracerts'])) {
				openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED);
			} else {
				openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED, $this->signature_data['extracerts']);
			}
			// read signature
			$signature = file_get_contents($tempsign);
			// extract signature
			$signature = substr($signature, $pdfdoc_length);
			$signature = substr($signature, (strpos($signature, "%%EOF\n\n------") + 13));
			$tmparr = explode("\n\n", $signature);
			$signature = $tmparr[1];
			// decode signature
			$signature = base64_decode(trim($signature));
			// add TSA timestamp to signature
			$signature = $this->applyTSA($signature);
			// convert signature to hex
			$signature = current(unpack('H*', $signature));
			$signature = str_pad($signature, $this->signature_max_length, '0');
			// Add signature to the document
			$this->buffer = substr($pdfdoc, 0, $byte_range[1]).'<'.$signature.'>'.substr($pdfdoc, $byte_range[1]);
			$this->bufferlen = strlen($this->buffer);
		}
		switch($dest) {
			case 'I': {
				// Send PDF to the standard output
				if (ob_get_contents()) {
					$this->Error('Some data has already been output, can\'t send PDF file');
				}
				if (php_sapi_name() != 'cli') {
					// send output to a browser
					header('Content-Type: application/pdf');
					if (headers_sent()) {
						$this->Error('Some data has already been output to browser, can\'t send PDF file');
					}
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					header('Content-Disposition: inline; filename="' . rawurlencode(basename($name)) . '"; ' .
						'filename*=UTF-8\'\'' . rawurlencode(basename($name)));
					LIMEPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
				} else {
					echo $this->getBuffer();
				}
				break;
			}
			case 'D': {
				// download PDF as file
				if (ob_get_contents()) {
					$this->Error('Some data has already been output, can\'t send PDF file');
				}
				header('Content-Description: File Transfer');
				if (headers_sent()) {
					$this->Error('Some data has already been output to browser, can\'t send PDF file');
				}
				header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
				//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
				header('Pragma: public');
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
				// force download dialog
				if (strpos(php_sapi_name(), 'cgi') === false) {
					header('Content-Type: application/force-download');
					header('Content-Type: application/octet-stream', false);
					header('Content-Type: application/download', false);
					header('Content-Type: application/pdf', false);
				} else {
					header('Content-Type: application/pdf');
				}
				// use the Content-Disposition header to supply a recommended filename
				header('Content-Disposition: attachment; filename="' . rawurlencode(basename($name)) . '"; ' .
					'filename*=UTF-8\'\'' . rawurlencode(basename($name)));
				header('Content-Transfer-Encoding: binary');
				LIMEPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
				break;
			}
			case 'F':
			case 'FI':
			case 'FD': {
				// save PDF to a local file
				$f = LIMEPDF_STATIC::fopenLocal($name, 'wb');
				if (!$f) {
					$this->Error('Unable to create output file: '.$name);
				}
				fwrite($f, $this->getBuffer(), $this->bufferlen);
				fclose($f);
				if ($dest == 'FI') {
					// send headers to browser
					header('Content-Type: application/pdf');
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					header('Content-Disposition: inline; filename="'.basename($name).'"');
					LIMEPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
				} elseif ($dest == 'FD') {
					// send headers to browser
					if (ob_get_contents()) {
						$this->Error('Some data has already been output, can\'t send PDF file');
					}
					header('Content-Description: File Transfer');
					if (headers_sent()) {
						$this->Error('Some data has already been output to browser, can\'t send PDF file');
					}
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					// force download dialog
					if (strpos(php_sapi_name(), 'cgi') === false) {
						header('Content-Type: application/force-download');
						header('Content-Type: application/octet-stream', false);
						header('Content-Type: application/download', false);
						header('Content-Type: application/pdf', false);
					} else {
						header('Content-Type: application/pdf');
					}
					// use the Content-Disposition header to supply a recommended filename
					header('Content-Disposition: attachment; filename="'.basename($name).'"');
					header('Content-Transfer-Encoding: binary');
					LIMEPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
				}
				break;
			}
			case 'E': {
				// return PDF as base64 mime multi-part email attachment (RFC 2045)
				$retval = 'Content-Type: application/pdf;'."\r\n";
				$retval .= ' name="'.$name.'"'."\r\n";
				$retval .= 'Content-Transfer-Encoding: base64'."\r\n";
				$retval .= 'Content-Disposition: attachment;'."\r\n";
				$retval .= ' filename="'.$name.'"'."\r\n\r\n";
				$retval .= chunk_split(base64_encode($this->getBuffer()), 76, "\r\n");
				return $retval;
			}
			case 'S': {
				// returns PDF as a string
				return $this->getBuffer();
			}
			default: {
				$this->Error('Incorrect output destination: '.$dest);
			}
		}
		return '';
	}

	protected static $cleaned_ids = array();

		/**
	 * Check for locale-related bug
	 * @protected
	 */
	protected function _doChecks() {
		//Check for locale-related bug
		if (1.1 == 1) {
			$this->Error('Don\'t alter the locale before including class file');
		}
		//Check for decimal separator
		if (sprintf('%.1F', 1.0) != '1.0') {
			setlocale(LC_NUMERIC, 'C');
		}
	}

	/**
	 * Unset all class variables except the following critical variables.
	 * @param boolean $destroyall if true destroys all class variables, otherwise preserves critical variables.
	 * @param boolean $preserve_objcopy if true preserves the objcopy variable
	 * @public
	 * @since 4.5.016 (2009-02-24)
	 */
	public function _destroy($destroyall=false, $preserve_objcopy=false) {
		if (isset(self::$cleaned_ids[$this->file_id])) {
			$destroyall = false;
		}
		if ($destroyall AND !$preserve_objcopy && isset($this->file_id)) {
			self::$cleaned_ids[$this->file_id] = true;
			// remove all temporary files
			if ($handle = @opendir(K_PATH_CACHE)) {
				while ( false !== ( $file_name = readdir( $handle ) ) ) {
					if (strpos($file_name, '__tcpdf_'.$this->file_id.'_') === 0) {
						$this->_unlink(K_PATH_CACHE.$file_name);
					}
				}
				closedir($handle);
			}
			if (isset($this->imagekeys)) {
				foreach($this->imagekeys as $file) {
					if ((strpos($file,  K_PATH_CACHE.'__tcpdf_'.$this->file_id.'_') === 0)
						&& LIMEPDF_STATIC::file_exists($file)) {
							$this->_unlink($file);
					}
				}
			}
		}
		$preserve = array(
			'file_id',
			'state',
			'bufferlen',
			'buffer',
			'cached_files',
			'imagekeys',
			'sign',
			'signature_data',
			'signature_max_length',
			'byterange_string',
			'tsa_timestamp',
			'tsa_data'
		);
		foreach (array_keys(get_object_vars($this)) as $val) {
			if ($destroyall OR !in_array($val, $preserve)) {
				if ((!$preserve_objcopy OR ($val != 'objcopy')) AND ($val != 'file_id') AND isset($this->$val)) {
					unset($this->$val);
				}
			}
		}
	}

	/**
	 * Output Resources.
	 * @protected
	 * 
	 */
	public function _putresources() {
		$this->_putextgstates();
		$this->_putocg();
		$this->_putfonts();
		$this->_putimages();
		$this->_putspotcolors();
		$this->_putshaders();
		$this->_putxobjects();
		$this->_putresourcedict();
		$this->_putdests();
		$this->_putEmbeddedFiles();
		//$this->_putannotsobjs();
		//this->_putjavascript();
		$this->_putbookmarks();
		$this->_putencryption();

	}

	
	/**
	 * Begin a new object and return the object number.
	 * @return int object number
	 * @protected
	 */
	//protected function _newobj() {
	public function _newobj() {
		$this->_out($this->_getobj());
		return $this->n;
	}

	/**
	 * Return the starting object string for the selected object ID.
	 * @param int|null $objid Object ID (leave empty to get a new ID).
	 * @return string the starting object string
	 * @protected
	 * @since 5.8.009 (2010-08-20)
	 */
	public function _getobj($objid=null) {
		if (LIMEPDF_STATIC::empty_string($objid)) {
			++$this->n;
			$objid = $this->n;
		}
		$this->offsets[$objid] = $this->bufferlen;
		$this->pageobjects[$this->page][] = $objid;
		return $objid.' 0 obj';
	}

	/**
	 * Output a string to the document.
	 * @param string $s string to output.
	 * @protected
	 */
	//protected function _out($s) {
	public function _out($s) {
		if ($this->state == 2) {
			if ($this->inxobj) {
				// we are inside an XObject template
				$this->xobjects[$this->xobjid]['outdata'] .= $s."\n";
			} elseif ((!$this->InFooter) AND isset($this->footerlen[$this->page]) AND ($this->footerlen[$this->page] > 0)) {
				// puts data before page footer
				$pagebuff = $this->getPageBuffer($this->page);
				$page = substr($pagebuff, 0, -$this->footerlen[$this->page]);
				$footer = substr($pagebuff, -$this->footerlen[$this->page]);
				$this->setPageBuffer($this->page, $page.$s."\n".$footer);
				// update footer position
				$this->footerpos[$this->page] += strlen($s."\n");
			} else {
				// set page data
				$this->setPageBuffer($this->page, $s."\n", true);
			}
		} elseif ($this->state > 0) {
			// set general data
			$this->setBuffer($s."\n");
		}
	}
	



	/**
	 * Calculates the hash value of the given data.
	 *
	 * @param string $data The data to be hashed.
	 * @return string The hashed value of the data.
	 */
	protected function hashTCPDFtag($data) {
		return hash_hmac('sha256', $data, $this->hash_key, false);
	}

	/**
	 * Serialize data to be used with TCPDF tag in HTML code.
	 * @param string $method TCPDF method name
	 * @param array $params Method parameters
	 * @return string Serialized data
	 * @public static
	 */
	public function serializeTCPDFtag($method, $params=array()) {
		$data = array('m' => $method, 'p' => $params);
		$encoded = urlencode(json_encode($data));
		$hash = $this->hashTCPDFtag($encoded);
		return strlen($hash).'+'.$hash.'+'.$encoded;
	}

	/**
	 * Unserialize data to be used with TCPDF tag in HTML code.
	 * @param string $data serialized data
	 * @return array containing unserialized data
	 * @protected static
	 */
	protected function unserializeTCPDFtag($data) {
		$hpos = strpos($data, '+');
		$hlen = intval(substr($data, 0, $hpos));
		$hash = substr($data, $hpos + 1, $hlen);
		$encoded = substr($data, $hpos + 2 + $hlen);
		if (!hash_equals( $this->hashTCPDFtag($encoded), $hash)) {
			$this->Error('Invalid parameters');
		}
		return json_decode(urldecode($encoded), true);
	}

	/**
	 * Check if a TCPDF tag is allowed
	 * @param string $method TCPDF method name
	 * @return boolean
	 * @protected
	 */
	protected function allowedTCPDFtag($method) {
		if (defined('K_ALLOWED_TCPDF_TAGS')) {
			return (strpos(K_ALLOWED_TCPDF_TAGS, '|'.$method.'|') !== false);
		}
		return false;
	}

	/**
	 * Swap the left and right margins.
	 * @param boolean $reverse if true swap left and right margins.
	 * @protected
	 * @since 4.2.000 (2008-10-29)
	 */
	protected function swapMargins($reverse=true) {
		if ($reverse) {
			// swap left and right margins
			$mtemp = $this->original_lMargin;
			$this->original_lMargin = $this->original_rMargin;
			$this->original_rMargin = $mtemp;
			$deltam = $this->original_lMargin - $this->original_rMargin;
			$this->lMargin += $deltam;
			$this->rMargin -= $deltam;
		}
	}




} // END OF TCPDF CLASS

//============================================================+
// END OF FILE
//============================================================+
