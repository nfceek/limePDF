<?php

namespace LimePDF;

	use LimePDF\LIMEPDF_STATIC;
	use LimePDF\LIMEPDF_FONT;
	use LimePDF\LIMEPDF_IMAGES;
	use LimePDF\LIMEPDF_FONT_DATA;
	use LimePDF\LIMEPDF_COLORS;

	// limePDF configuration
	require_once(dirname(__FILE__).'/limepdf_autoconfig.php');

	// limePDF vars
	require_once(dirname(__FILE__).'/include/limePDF_Vars.php');
	require_once(dirname(__FILE__).'/include/limePDF_Static.php');

	require_once(dirname(__FILE__).'/src/Encryption/limePDF_Encryption.php');

	require_once(dirname(__FILE__).'/src/Fonts/limePDF_FontManager.php');	
	require_once(dirname(__FILE__).'/src/Fonts/limePDF_Fonts.php');

	require_once(dirname(__FILE__).'/src/Graphics/limePDF_Columns.php');
	require_once(dirname(__FILE__).'/src/Graphics/limePDF_Draw.php');
	require_once(dirname(__FILE__).'/src/Graphics/limePDF_Graphics.php');
	require_once(dirname(__FILE__).'/src/Graphics/limePDF_SVG.php');
	require_once(dirname(__FILE__).'/src/Graphics/limePDF_Transformations.php');

	require_once(dirname(__FILE__).'/src/Graphics/limePDF_XObjects_Templates.php');
	
	require_once(dirname(__FILE__).'/src/Pages/limePDF_Pages.php');
	require_once(dirname(__FILE__).'/src/Pages/limePDF_Margins.php');
	require_once(dirname(__FILE__).'/src/Pages/limePDF_PageColors.php');
	require_once(dirname(__FILE__).'/src/Pages/limePDF_Sections.php');	


	require_once(dirname(__FILE__).'/src/Utils/limePDF_Misc.php');
	require_once(dirname(__FILE__).'/src/Utils/limePDF_Javascript.php');
	require_once(dirname(__FILE__).'/src/Utils/limePDF_Forms.php');
	require_once(dirname(__FILE__).'/src/Utils/limePDF_Environment.php');


	require_once(dirname(__FILE__).'/src/Model/limePDF_Page_GetterSetter.php');
	require_once(dirname(__FILE__).'/src/Model/limePDF_Font_GetterSetter.php');	

	
class TCPDF {

	use LIMEPDF_VARS;
	use LIMEPDF_GRAPHICS;
	use LIMEPDF_TRANSFORMATIONS;
	use LIMEPDF_COLUMNS;
	use LIMEPDF_SVG;
	use LIMEPDF_ENCRYPTION;
	use LIMEPDF_ENVIRONMENT;
	use LIMEPDF_FORMS;
	use LIMEPDF_JAVASCRIPT;

	use LIMEPDF_XOTEMPLATES;
	use LIMEPDF_PAGES;
	use LIMEPDF_PAGECOLORS;	
	use LIMEPDF_MARGINS;
	use LIMEPDF_SECTIONS;
	use LIMEPDF_FONTMANAGER;	
	use LIMEPDF_FONTS;
	use LIMEPDF_DRAW;


	// Load Getter Setters
	USE LIMEPDF_FONT_GETTERSETTER;
	USE LIMEPDF_PAGE_GETTERSETTER;	

	public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {

        //$this->utilsPut = new limePDF_Put();
	   //$this->utilsMisc = new limePDF_Misc();
    
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
	 * Set regular expression to detect withespaces or word separators.
	 * The pattern delimiter must be the forward-slash character "/".
	 * Some example patterns are:
	 * <pre>
	 * Non-Unicode or missing PCRE unicode support: "/[^\S\xa0]/"
	 * Unicode and PCRE unicode support: "/(?!\xa0)[\s\p{Z}]/u"
	 * Unicode and PCRE unicode support in Chinese mode: "/(?!\xa0)[\s\p{Z}\p{Lo}]/u"
	 * if PCRE unicode support is turned ON ("\P" is the negate class of "\p"):
	 *      \s     : any whitespace character
	 *      \p{Z}  : any separator
	 *      \p{Lo} : Unicode letter or ideograph that does not have lowercase and uppercase variants. Is used to chunk chinese words.
	 *      \xa0   : Unicode Character 'NO-BREAK SPACE' (U+00A0)
	 * </pre>
	 * @param string $re regular expression (leave empty for default).
	 * @public
	 * @since 4.6.016 (2009-06-15)
	 */
	public function setSpacesRE($re='/[^\S\xa0]/') {
		$this->re_spaces = $re;
		$re_parts = explode('/', $re);
		// get pattern parts
		$this->re_space = array();
		if (isset($re_parts[1]) AND !empty($re_parts[1])) {
			$this->re_space['p'] = $re_parts[1];
		} else {
			$this->re_space['p'] = '[\s]';
		}
		// set pattern modifiers
		if (isset($re_parts[2]) AND !empty($re_parts[2])) {
			$this->re_space['m'] = $re_parts[2];
		} else {
			$this->re_space['m'] = '';
		}
	}

	/**
	 * Enable or disable Right-To-Left language mode
	 * @param boolean $enable if true enable Right-To-Left language mode.
	 * @param boolean $resetx if true reset the X position on direction change.
	 * @public
	 * @since 2.0.000 (2008-01-03)
	 */
	public function setRTL($enable, $resetx=true) {
		$enable = $enable ? true : false;
		$resetx = ($resetx AND ($enable != $this->rtl));
		$this->rtl = $enable;
		$this->tmprtl = false;
		if ($resetx) {
			$this->Ln(0);
		}
	}

	/**
	 * Return the RTL status
	 * @return bool
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getRTL() {
		return $this->rtl;
	}

	/**
	 * Force temporary RTL language direction
	 * @param false|string $mode can be false, 'L' for LTR or 'R' for RTL
	 * @public
	 * @since 2.1.000 (2008-01-09)
	 */
	public function setTempRTL($mode) {
		$newmode = false;
		switch (strtoupper($mode)) {
			case 'LTR':
			case 'L': {
				if ($this->rtl) {
					$newmode = 'L';
				}
				break;
			}
			case 'RTL':
			case 'R': {
				if (!$this->rtl) {
					$newmode = 'R';
				}
				break;
			}
			case false:
			default: {
				$newmode = false;
				break;
			}
		}
		$this->tmprtl = $newmode;
	}

	/**
	 * Return the current temporary RTL status
	 * @return bool
	 * @public
	 * @since 4.8.014 (2009-11-04)
	 */
	public function isRTLTextDir() {
		return ($this->rtl OR ($this->tmprtl == 'R'));
	}

	/**
	 * Set the last cell height.
	 * @param float $h cell height.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.53.0.TC034
	 */
	public function setLastH($h) {
		$this->lasth = $h;
	}

	/**
	 * Return the cell height
	 * @param int $fontsize Font size in internal units
	 * @param boolean $padding If true add cell padding
	 * @public
	 * @return float
	 */
	public function getCellHeight($fontsize, $padding=TRUE) {
		$height = ($fontsize * $this->cell_height_ratio);
		if ($padding && !empty($this->cell_padding)) {
			$height += ($this->cell_padding['T'] + $this->cell_padding['B']);
		}
		return round($height, 6);
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
	 * Get the last cell height.
	 * @return float last cell height
	 * @public
	 * @since 4.0.017 (2008-08-05)
	 */
	public function getLastH() {
		return $this->lasth;
	}

	/**
	 * Set the adjusting factor to convert pixels to user units.
	 * @param float $scale adjusting factor to convert pixels to user units.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 */
	public function setImageScale($scale) {
		$this->imgscale = $scale;
	}

	/**
	 * Returns the adjusting factor to convert pixels to user units.
	 * @return float adjusting factor to convert pixels to user units.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 */
	public function getImageScale() {
		return $this->imgscale;
	}

	/**
	 * Returns an array of page dimensions:
	 * <ul><li>$this->pagedim[$this->page]['w'] = page width in points</li><li>$this->pagedim[$this->page]['h'] = height in points</li><li>$this->pagedim[$this->page]['wk'] = page width in user units</li><li>$this->pagedim[$this->page]['hk'] = page height in user units</li><li>$this->pagedim[$this->page]['tm'] = top margin</li><li>$this->pagedim[$this->page]['bm'] = bottom margin</li><li>$this->pagedim[$this->page]['lm'] = left margin</li><li>$this->pagedim[$this->page]['rm'] = right margin</li><li>$this->pagedim[$this->page]['pb'] = auto page break</li><li>$this->pagedim[$this->page]['or'] = page orientation</li><li>$this->pagedim[$this->page]['olm'] = original left margin</li><li>$this->pagedim[$this->page]['orm'] = original right margin</li><li>$this->pagedim[$this->page]['Rotate'] = The number of degrees by which the page shall be rotated clockwise when displayed or printed. The value shall be a multiple of 90.</li><li>$this->pagedim[$this->page]['PZ'] = The page's preferred zoom (magnification) factor.</li><li>$this->pagedim[$this->page]['trans'] : the style and duration of the visual transition to use when moving from another page to the given page during a presentation<ul><li>$this->pagedim[$this->page]['trans']['Dur'] = The page's display duration (also called its advance timing): the maximum length of time, in seconds, that the page shall be displayed during presentations before the viewer application shall automatically advance to the next page.</li><li>$this->pagedim[$this->page]['trans']['S'] = transition style : Split, Blinds, Box, Wipe, Dissolve, Glitter, R, Fly, Push, Cover, Uncover, Fade</li><li>$this->pagedim[$this->page]['trans']['D'] = The duration of the transition effect, in seconds.</li><li>$this->pagedim[$this->page]['trans']['Dm'] = (Split and Blinds transition styles only) The dimension in which the specified transition effect shall occur: H = Horizontal, V = Vertical. Default value: H.</li><li>$this->pagedim[$this->page]['trans']['M'] = (Split, Box and Fly transition styles only) The direction of motion for the specified transition effect: I = Inward from the edges of the page, O = Outward from the center of the pageDefault value: I.</li><li>$this->pagedim[$this->page]['trans']['Di'] = (Wipe, Glitter, Fly, Cover, Uncover and Push transition styles only) The direction in which the specified transition effect shall moves, expressed in degrees counterclockwise starting from a left-to-right direction. If the value is a number, it shall be one of: 0 = Left to right, 90 = Bottom to top (Wipe only), 180 = Right to left (Wipe only), 270 = Top to bottom, 315 = Top-left to bottom-right (Glitter only). If the value is a name, it shall be None, which is relevant only for the Fly transition when the value of SS is not 1.0. Default value: 0.</li><li>$this->pagedim[$this->page]['trans']['SS'] = (Fly transition style only) The starting or ending scale at which the changes shall be drawn. If M specifies an inward transition, the scale of the changes drawn shall progress from SS to 1.0 over the course of the transition. If M specifies an outward transition, the scale of the changes drawn shall progress from 1.0 to SS over the course of the transition. Default: 1.0. </li><li>$this->pagedim[$this->page]['trans']['B'] = (Fly transition style only) If true, the area that shall be flown in is rectangular and opaque. Default: false.</li></ul></li><li>$this->pagedim[$this->page]['MediaBox'] : the boundaries of the physical medium on which the page shall be displayed or printed<ul><li>$this->pagedim[$this->page]['MediaBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['MediaBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['MediaBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['MediaBox']['ury'] = upper-right y coordinate in points</li></ul></li><li>$this->pagedim[$this->page]['CropBox'] : the visible region of default user space<ul><li>$this->pagedim[$this->page]['CropBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['CropBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['CropBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['CropBox']['ury'] = upper-right y coordinate in points</li></ul></li><li>$this->pagedim[$this->page]['BleedBox'] : the region to which the contents of the page shall be clipped when output in a production environment<ul><li>$this->pagedim[$this->page]['BleedBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['BleedBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['BleedBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['BleedBox']['ury'] = upper-right y coordinate in points</li></ul></li><li>$this->pagedim[$this->page]['TrimBox'] : the intended dimensions of the finished page after trimming<ul><li>$this->pagedim[$this->page]['TrimBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['TrimBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['TrimBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['TrimBox']['ury'] = upper-right y coordinate in points</li></ul></li><li>$this->pagedim[$this->page]['ArtBox'] : the extent of the page's meaningful content<ul><li>$this->pagedim[$this->page]['ArtBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['ArtBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['ArtBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['ArtBox']['ury'] = upper-right y coordinate in points</li></ul></li></ul>
	 * @param int|null $pagenum page number (empty = current page)
	 * @return array of page dimensions.
	 * @author Nicola Asuni
	 * @public
	 * @since 4.5.027 (2009-03-16)
	 */
	public function getPageDimensions($pagenum=null) {
		if (empty($pagenum)) {
			$pagenum = $this->page;
		}
		return $this->pagedim[$pagenum];
	}

	/**
	 * Returns the page width in units.
	 * @param int|null $pagenum page number (empty = current page)
	 * @return int|float page width.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 * @see getPageDimensions()
	 */
	public function getPageWidth($pagenum=null) {
		if (empty($pagenum)) {
			return $this->w;
		}
		return $this->pagedim[$pagenum]['w'];
	}

	/**
	 * Returns the page height in units.
	 * @param int|null $pagenum page number (empty = current page)
	 * @return int|float page height.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 * @see getPageDimensions()
	 */
	public function getPageHeight($pagenum=null) {
		if (empty($pagenum)) {
			return $this->h;
		}
		return $this->pagedim[$pagenum]['h'];
	}

	/**
	 * Returns the page break margin.
	 * @param int|null $pagenum page number (empty = current page)
	 * @return int|float page break margin.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 * @see getPageDimensions()
	 */
	public function getBreakMargin($pagenum=null) {
		if (empty($pagenum)) {
			return $this->bMargin;
		}
		return $this->pagedim[$pagenum]['bm'];
	}

	/**
	 * Returns the scale factor (number of points in user unit).
	 * @return int scale factor.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 */
	public function getScaleFactor() {
		return $this->k;
	}


	/**
	 * Set the same internal Cell padding for top, right, bottom, left-
	 * @param int|float $pad internal padding.
	 * @public
	 * @since 2.1.000 (2008-01-09)
	 * @see getCellPaddings(), setCellPaddings()
	 */
	public function setCellPadding($pad) {
		if ($pad >= 0) {
			$this->cell_padding['L'] = $pad;
			$this->cell_padding['T'] = $pad;
			$this->cell_padding['R'] = $pad;
			$this->cell_padding['B'] = $pad;
		}
	}

	/**
	 * Set the internal Cell paddings.
	 * @param int|float|null $left left padding
	 * @param int|float|null $top top padding
	 * @param int|float|null $right right padding
	 * @param int|float|null $bottom bottom padding
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 * @see getCellPaddings(), SetCellPadding()
	 */
	public function setCellPaddings($left=null, $top=null, $right=null, $bottom=null) {
		if (!LIMEPDF_STATIC::empty_string($left) AND ($left >= 0)) {
			$this->cell_padding['L'] = $left;
		}
		if (!LIMEPDF_STATIC::empty_string($top) AND ($top >= 0)) {
			$this->cell_padding['T'] = $top;
		}
		if (!LIMEPDF_STATIC::empty_string($right) AND ($right >= 0)) {
			$this->cell_padding['R'] = $right;
		}
		if (!LIMEPDF_STATIC::empty_string($bottom) AND ($bottom >= 0)) {
			$this->cell_padding['B'] = $bottom;
		}
	}

	/**
	 * Get the internal Cell padding array.
	 * @return array of padding values
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 * @see setCellPaddings(), SetCellPadding()
	 */
	public function getCellPaddings() {
		return $this->cell_padding;
	}

	/**
	 * Set the internal Cell margins.
	 * @param int|float|null $left left margin
	 * @param int|float|null $top top margin
	 * @param int|float|null $right right margin
	 * @param int|float|null $bottom bottom margin
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 * @see getCellMargins()
	 */
	public function setCellMargins($left=null, $top=null, $right=null, $bottom=null) {
		if (!LIMEPDF_STATIC::empty_string($left) AND ($left >= 0)) {
			$this->cell_margin['L'] = $left;
		}
		if (!LIMEPDF_STATIC::empty_string($top) AND ($top >= 0)) {
			$this->cell_margin['T'] = $top;
		}
		if (!LIMEPDF_STATIC::empty_string($right) AND ($right >= 0)) {
			$this->cell_margin['R'] = $right;
		}
		if (!LIMEPDF_STATIC::empty_string($bottom) AND ($bottom >= 0)) {
			$this->cell_margin['B'] = $bottom;
		}
	}

	/**
	 * Get the internal Cell margin array.
	 * @return array of margin values
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 * @see setCellMargins()
	 */
	public function getCellMargins() {
		return $this->cell_margin;
	}

	/**
	 * Adjust the internal Cell padding array to take account of the line width.
	 * @param string|array|int|bool $brd Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @return void|array array of adjustments
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 */
	protected function adjustCellPadding($brd=0) {
		if (empty($brd)) {
			return;
		}
		if (is_string($brd)) {
			// convert string to array
			$slen = strlen($brd);
			$newbrd = array();
			for ($i = 0; $i < $slen; ++$i) {
				$newbrd[$brd[$i]] = true;
			}
			$brd = $newbrd;
		} elseif (
			($brd === 1)
			|| ($brd === true)
			|| (is_numeric($brd) && ((int)$brd > 0))
		) {
			$brd = array('LRTB' => true);
		}
		if (!is_array($brd)) {
			return;
		}
		// store current cell padding
		$cp = $this->cell_padding;
		// select border mode
		if (isset($brd['mode'])) {
			$mode = $brd['mode'];
			unset($brd['mode']);
		} else {
			$mode = 'normal';
		}
		// process borders
		foreach ($brd as $border => $style) {
			$line_width = $this->LineWidth;
			if (is_array($style) && isset($style['width'])) {
				// get border width
				$line_width = $style['width'];
			}
			$adj = 0; // line width inside the cell
			switch ($mode) {
				case 'ext': {
					$adj = 0;
					break;
				}
				case 'int': {
					$adj = $line_width;
					break;
				}
				case 'normal':
				default: {
					$adj = ($line_width / 2);
					break;
				}
			}
			// correct internal cell padding if required to avoid overlap between text and lines
			if (
				is_numeric($this->cell_padding['T'])
				&& ($this->cell_padding['T'] < $adj)
				&& (strpos($border, 'T') !== false)
			) {
				$this->cell_padding['T'] = $adj;
			}
			if (
				is_numeric($this->cell_padding['R'])
				&& ($this->cell_padding['R'] < $adj)
				&& (strpos($border, 'R') !== false)
			) {
				$this->cell_padding['R'] = $adj;
			}
			if (
				is_numeric($this->cell_padding['B'])
				&& ($this->cell_padding['B'] < $adj)
				&& (strpos($border, 'B') !== false)
			) {
				$this->cell_padding['B'] = $adj;
			}
			if (
				is_numeric($this->cell_padding['L'])
				&& ($this->cell_padding['L'] < $adj)
				&& (strpos($border, 'L') !== false)
			) {
				$this->cell_padding['L'] = $adj;
			}

		}

		return array(
			'T' => ($this->cell_padding['T'] - $cp['T']),
			'R' => ($this->cell_padding['R'] - $cp['R']),
			'B' => ($this->cell_padding['B'] - $cp['B']),
			'L' => ($this->cell_padding['L'] - $cp['L']),
		);
	}

	/**
	 * Enables or disables the automatic page breaking mode. When enabling, the second parameter is the distance from the bottom of the page that defines the triggering limit. By default, the mode is on and the margin is 2 cm.
	 * @param boolean $auto Boolean indicating if mode should be on or off.
	 * @param float $margin Distance from the bottom of the page.
	 * @public
	 * @since 1.0
	 * @see Cell(), MultiCell(), AcceptPageBreak()
	 */
	public function setAutoPageBreak($auto, $margin=0) {
		$this->AutoPageBreak = $auto ? true : false;
		$this->bMargin = $margin;
		$this->PageBreakTrigger = $this->h - $margin;
	}

	/**
	 * Return the auto-page-break mode (true or false).
	 * @return bool auto-page-break mode
	 * @public
	 * @since 5.9.088
	 */
	public function getAutoPageBreak() {
		return $this->AutoPageBreak;
	}

	/**
	 * Defines the way the document is to be displayed by the viewer.
	 * @param mixed $zoom The zoom to use. It can be one of the following string values or a number indicating the zooming factor to use. <ul><li>fullpage: displays the entire page on screen </li><li>fullwidth: uses maximum width of window</li><li>real: uses real size (equivalent to 100% zoom)</li><li>default: uses viewer default mode</li></ul>
	 * @param string $layout The page layout. Possible values are:<ul><li>SinglePage Display one page at a time</li><li>OneColumn Display the pages in one column</li><li>TwoColumnLeft Display the pages in two columns, with odd-numbered pages on the left</li><li>TwoColumnRight Display the pages in two columns, with odd-numbered pages on the right</li><li>TwoPageLeft (PDF 1.5) Display the pages two at a time, with odd-numbered pages on the left</li><li>TwoPageRight (PDF 1.5) Display the pages two at a time, with odd-numbered pages on the right</li></ul>
	 * @param string $mode A name object specifying how the document should be displayed when opened:<ul><li>UseNone Neither document outline nor thumbnail images visible</li><li>UseOutlines Document outline visible</li><li>UseThumbs Thumbnail images visible</li><li>FullScreen Full-screen mode, with no menu bar, window controls, or any other window visible</li><li>UseOC (PDF 1.5) Optional content group panel visible</li><li>UseAttachments (PDF 1.6) Attachments panel visible</li></ul>
	 * @public
	 * @since 1.2
	 */
	public function setDisplayMode($zoom, $layout='SinglePage', $mode='UseNone') {
		if (($zoom == 'fullpage') OR ($zoom == 'fullwidth') OR ($zoom == 'real') OR ($zoom == 'default') OR (!is_string($zoom))) {
			$this->ZoomMode = $zoom;
		} else {
			$this->Error('Incorrect zoom display mode: '.$zoom);
		}
		$this->LayoutMode = LIMEPDF_STATIC::getPageLayoutMode($layout);
		$this->PageMode = LIMEPDF_STATIC::getPageMode($mode);
	}

	/**
	 * Activates or deactivates page compression. When activated, the internal representation of each page is compressed, which leads to a compression ratio of about 2 for the resulting document. Compression is on by default.
	 * Note: the Zlib extension is required for this feature. If not present, compression will be turned off.
	 * @param boolean $compress Boolean indicating if compression must be enabled.
	 * @public
	 * @since 1.4
	 */
	public function setCompression($compress=true) {
		$this->compress = false;
		if (function_exists('gzcompress')) {
			if ($compress) {
				if ( !$this->pdfa_mode) {
					$this->compress = true;
				}
			}
		}
	}

	/**
	 * Set flag to force sRGB_IEC61966-2.1 black scaled ICC color profile for the whole document.
	 * @param boolean $mode If true force sRGB output intent.
	 * @public
	 * @since 5.9.121 (2011-09-28)
	 */
	public function setSRGBmode($mode=false) {
		$this->force_srgb = $mode ? true : false;
	}

	/**
	 * Turn on/off Unicode mode for document information dictionary (meta tags).
	 * This has effect only when unicode mode is set to false.
	 * @param boolean $unicode if true set the meta information in Unicode
	 * @since 5.9.027 (2010-12-01)
	 * @public
	 */
	public function setDocInfoUnicode($unicode=true) {
		$this->docinfounicode = $unicode ? true : false;
	}

	/**
	 * Defines the title of the document.
	 * @param string $title The title.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetCreator(), SetKeywords(), SetSubject()
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Defines the subject of the document.
	 * @param string $subject The subject.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetCreator(), SetKeywords(), SetTitle()
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
	}

	/**
	 * Defines the author of the document.
	 * @param string $author The name of the author.
	 * @public
	 * @since 1.2
	 * @see SetCreator(), SetKeywords(), SetSubject(), SetTitle()
	 */
	public function setAuthor($author) {
		$this->author = $author;
	}

	/**
	 * Associates keywords with the document, generally in the form 'keyword1 keyword2 ...'.
	 * @param string $keywords The list of keywords.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetCreator(), SetSubject(), SetTitle()
	 */
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}

	/**
	 * Defines the creator of the document. This is typically the name of the application that generates the PDF.
	 * @param string $creator The name of the creator.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetKeywords(), SetSubject(), SetTitle()
	 */
	public function setCreator($creator) {
		$this->creator = $creator;
	}

	/**
	 * Whether to allow local file path in image html tags, when prefixed with file://
	 *
	 * @param bool $allowLocalFiles true, when local files should be allowed. Otherwise false.
	 * @public
	 * @since 6.4
	 */
	public function setAllowLocalFiles($allowLocalFiles) {
		$this->allowLocalFiles = (bool) $allowLocalFiles;
	}


	/**
	 * Throw an exception or print an error message and die if the K_TCPDF_PARSER_THROW_EXCEPTION_ERROR constant is set to true.
	 * @param string $msg The error message
	 * @public
	 * @since 1.0
	 */
	public function Error($msg) {
		// unset all class variables
		$this->_destroy(true);
		$msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
		if (defined('K_TCPDF_THROW_EXCEPTION_ERROR') AND !K_TCPDF_THROW_EXCEPTION_ERROR) {
			die('<strong>TCPDF ERROR: </strong>'.$msg);
		} else {
			throw new Exception('TCPDF ERROR: '.$msg);
		}
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
	 * Set start-writing mark on selected page.
	 * Borders and fills are always created after content and inserted on the position marked by this method.
	 * @param int $page page number (default is the current page)
	 * @protected
	 * @since 4.6.021 (2009-07-20)
	 */	
	protected function setContentMark($page=0) {
		if ($page <= 0) {
			$page = $this->page;
		}
		if (isset($this->footerlen[$page])) {
			$this->cntmrk[$page] = $this->pagelen[$page] - $this->footerlen[$page];
		} else {
			$this->cntmrk[$page] = $this->pagelen[$page];
		}
	}




	/**
	 * Convert a relative font measure into absolute value.
	 * @param int $s Font measure.
	 * @return float Absolute measure.
	 * @since 5.9.186 (2012-09-13)
	 */
	public function getAbsFontMeasure($s) {
		return ($s * $this->FontSize / 1000);
	}

	/**
	 * Returns the glyph bounding box of the specified character in the current font in user units.
	 * @param int $char Input character code.
	 * @return false|array array(xMin, yMin, xMax, yMax) or FALSE if not defined.
	 * @since 5.9.186 (2012-09-13)
	 */
	public function getCharBBox($char) {
		$c = intval($char);
		if (isset($this->CurrentFont['cw'][$c])) {
			// glyph is defined ... use zero width & height for glyphs without outlines
			$result = array(0,0,0,0);
			if (isset($this->CurrentFont['cbbox'][$c])) {
				$result = $this->CurrentFont['cbbox'][$c];
			}
			return array_map(array($this,'getAbsFontMeasure'), $result);
		}
		return false;
	}

	/**
	 * Return true in the character is present in the specified font.
	 * @param mixed $char Character to check (integer value or string)
	 * @param string $font Font name (family name).
	 * @param string $style Font style.
	 * @return bool true if the char is defined, false otherwise.
	 * @public
	 * @since 5.9.153 (2012-03-28)
	 */
	public function isCharDefined($char, $font='', $style='') {
		if (is_string($char)) {
			// get character code
			$char = LIMEPDF_FONT::UTF8StringToArray($char, $this->isunicode, $this->CurrentFont);
			$char = $char[0];
		}
		if (LIMEPDF_STATIC::empty_string($font)) {
			if (LIMEPDF_STATIC::empty_string($style)) {
				return (isset($this->CurrentFont['cw'][intval($char)]));
			}
			$font = $this->FontFamily;
		}
		$fontdata = $this->AddFont($font, $style);
		$fontinfo = $this->getFontBuffer($fontdata['fontkey']);
		return (isset($fontinfo['cw'][intval($char)]));
	}

	/**
	 * Replace missing font characters on selected font with specified substitutions.
	 * @param string $text Text to process.
	 * @param string $font Font name (family name).
	 * @param string $style Font style.
	 * @param array $subs Array of possible character substitutions. The key is the character to check (integer value) and the value is a single intege value or an array of possible substitutes.
	 * @return string Processed text.
	 * @public
	 * @since 5.9.153 (2012-03-28)
	 */
	public function replaceMissingChars($text, $font='', $style='', $subs=array()) {
		if (empty($subs)) {
			return $text;
		}
		if (LIMEPDF_STATIC::empty_string($font)) {
			$font = $this->FontFamily;
		}
		$fontdata = $this->AddFont($font, $style);
		$fontinfo = $this->getFontBuffer($fontdata['fontkey']);
		$uniarr = LIMEPDF_FONT::UTF8StringToArray($text, $this->isunicode, $this->CurrentFont);
		foreach ($uniarr as $k => $chr) {
			if (!isset($fontinfo['cw'][$chr])) {
				// this character is missing on the selected font
				if (isset($subs[$chr])) {
					// we have available substitutions
					if (is_array($subs[$chr])) {
						foreach($subs[$chr] as $s) {
							if (isset($fontinfo['cw'][$s])) {
								$uniarr[$k] = $s;
								break;
							}
						}
					} elseif (isset($fontinfo['cw'][$subs[$chr]])) {
						$uniarr[$k] = $subs[$chr];
					}
				}
			}
		}
		return LIMEPDF_FONT::UniArrSubString(LIMEPDF_FONT::UTF8ArrayToUniArray($uniarr, $this->isunicode));
	}

	/**
	 * Defines the default monospaced font.
	 * @param string $font Font name.
	 * @public
	 * @since 4.5.025
	 */
	public function setDefaultMonospacedFont($font) {
		$this->default_monospaced_font = $font;
	}

	/**
	 * Creates a new internal link and returns its identifier. An internal link is a clickable area which directs to another place within the document.<br />
	 * The identifier can then be passed to Cell(), Write(), Image() or Link(). The destination is defined with SetLink().
	 * @public
	 * @since 1.5
	 * @see Cell(), Write(), Image(), Link(), SetLink()
	 */
	public function AddLink() {
		// create a new internal link
		$n = count($this->links) + 1;
		$this->links[$n] = array('p' => 0, 'y' => 0, 'f' => false);
		return $n;
	}

	/**
	 * Defines the page and position a link points to.
	 * @param int $link The link identifier returned by AddLink()
	 * @param float $y Ordinate of target position; -1 indicates the current position. The default value is 0 (top of page)
	 * @param int|string $page Number of target page; -1 indicates the current page (default value). If you prefix a page number with the * character, then this page will not be changed when adding/deleting/moving pages.
	 * @public
	 * @since 1.5
	 * @see AddLink()
	 */
	public function setLink($link, $y=0, $page=-1) {
		$fixed = false;
		if (!empty($page) AND (substr($page, 0, 1) == '*')) {
			$page = intval(substr($page, 1));
			// this page number will not be changed when moving/add/deleting pages
			$fixed = true;
		}
		if ($page < 0) {
			$page = $this->page;
		}
		if ($y == -1) {
			$y = $this->y;
		}
		$this->links[$link] = array('p' => $page, 'y' => $y, 'f' => $fixed);
	}

	/**
	 * Puts a link on a rectangular area of the page.
	 * Text or image links are generally put via Cell(), Write() or Image(), but this method can be useful for instance to define a clickable area inside an image.
	 * @param float $x Abscissa of the upper-left corner of the rectangle
	 * @param float $y Ordinate of the upper-left corner of the rectangle
	 * @param float $w Width of the rectangle
	 * @param float $h Height of the rectangle
	 * @param mixed $link URL or identifier returned by AddLink()
	 * @param int $spaces number of spaces on the text to link
	 * @public
	 * @since 1.5
	 * @see AddLink(), Annotation(), Cell(), Write(), Image()
	 */
	public function Link($x, $y, $w, $h, $link, $spaces=0) {
		$this->Annotation($x, $y, $w, $h, $link, array('Subtype'=>'Link'), $spaces);
	}

	/**
	 * Puts a markup annotation on a rectangular area of the page.
	 * !!!!THE ANNOTATION SUPPORT IS NOT YET FULLY IMPLEMENTED !!!!
	 * @param float $x Abscissa of the upper-left corner of the rectangle
	 * @param float $y Ordinate of the upper-left corner of the rectangle
	 * @param float $w Width of the rectangle
	 * @param float $h Height of the rectangle
	 * @param string $text annotation text or alternate content
	 * @param array $opt array of options (see section 8.4 of PDF reference 1.7).
	 * @param int $spaces number of spaces on the text to link
	 * @public
	 * @since 4.0.018 (2008-08-06)
	 */
	public function Annotation($x, $y, $w, $h, $text, $opt=array('Subtype'=>'Text'), $spaces=0) {
		if ($this->inxobj) {
			// store parameters for later use on template
			$this->xobjects[$this->xobjid]['annotations'][] = array('x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'text' => $text, 'opt' => $opt, 'spaces' => $spaces);
			return;
		}
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		// recalculate coordinates to account for graphic transformations
		if (isset($this->transfmatrix) AND !empty($this->transfmatrix)) {
			for ($i=$this->transfmatrix_key; $i > 0; --$i) {
				$maxid = count($this->transfmatrix[$i]) - 1;
				for ($j=$maxid; $j >= 0; --$j) {
					$ctm = $this->transfmatrix[$i][$j];
					if (isset($ctm['a'])) {
						$x = $x * $this->k;
						$y = ($this->h - $y) * $this->k;
						$w = $w * $this->k;
						$h = $h * $this->k;
						// top left
						$xt = $x;
						$yt = $y;
						$x1 = ($ctm['a'] * $xt) + ($ctm['c'] * $yt) + $ctm['e'];
						$y1 = ($ctm['b'] * $xt) + ($ctm['d'] * $yt) + $ctm['f'];
						// top right
						$xt = $x + $w;
						$yt = $y;
						$x2 = ($ctm['a'] * $xt) + ($ctm['c'] * $yt) + $ctm['e'];
						$y2 = ($ctm['b'] * $xt) + ($ctm['d'] * $yt) + $ctm['f'];
						// bottom left
						$xt = $x;
						$yt = $y - $h;
						$x3 = ($ctm['a'] * $xt) + ($ctm['c'] * $yt) + $ctm['e'];
						$y3 = ($ctm['b'] * $xt) + ($ctm['d'] * $yt) + $ctm['f'];
						// bottom right
						$xt = $x + $w;
						$yt = $y - $h;
						$x4 = ($ctm['a'] * $xt) + ($ctm['c'] * $yt) + $ctm['e'];
						$y4 = ($ctm['b'] * $xt) + ($ctm['d'] * $yt) + $ctm['f'];
						// new coordinates (rectangle area)
						$x = min($x1, $x2, $x3, $x4);
						$y = max($y1, $y2, $y3, $y4);
						$w = (max($x1, $x2, $x3, $x4) - $x) / $this->k;
						$h = ($y - min($y1, $y2, $y3, $y4)) / $this->k;
						$x = $x / $this->k;
						$y = $this->h - ($y / $this->k);
					}
				}
			}
		}
		if ($this->page <= 0) {
			$page = 1;
		} else {
			$page = $this->page;
		}
		if (!isset($this->PageAnnots[$page])) {
			$this->PageAnnots[$page] = array();
		}
		$this->PageAnnots[$page][] = array('n' => ++$this->n, 'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'txt' => $text, 'opt' => $opt, 'numspaces' => $spaces);
		if (!$this->pdfa_mode || ($this->pdfa_mode && $this->pdfa_version == 3)) {
			if ((($opt['Subtype'] == 'FileAttachment') OR ($opt['Subtype'] == 'Sound')) AND (!LIMEPDF_STATIC::empty_string($opt['FS']))
				AND (@LIMEPDF_STATIC::file_exists($opt['FS']) OR LIMEPDF_STATIC::isValidURL($opt['FS']))
				AND (!isset($this->embeddedfiles[basename($opt['FS'])]))) {
				$this->embeddedfiles[basename($opt['FS'])] = array('f' => ++$this->n, 'n' => ++$this->n, 'file' => $opt['FS']);
			}
		}
		// Add widgets annotation's icons
		if (isset($opt['mk']['i']) AND @LIMEPDF_STATIC::file_exists($opt['mk']['i'])) {
			$this->Image($opt['mk']['i'], '', '', 10, 10, '', '', '', false, 300, '', false, false, 0, false, true);
		}
		if (isset($opt['mk']['ri']) AND @LIMEPDF_STATIC::file_exists($opt['mk']['ri'])) {
			$this->Image($opt['mk']['ri'], '', '', 0, 0, '', '', '', false, 300, '', false, false, 0, false, true);
		}
		if (isset($opt['mk']['ix']) AND @LIMEPDF_STATIC::file_exists($opt['mk']['ix'])) {
			$this->Image($opt['mk']['ix'], '', '', 0, 0, '', '', '', false, 300, '', false, false, 0, false, true);
		}
	}

	/**
	 * Embed the attached files.
	 * @since 6.9.000 (2025-02-11)
	 * @public
	 */
	public function EmbedFile($opt) {
		if (!$this->pdfa_mode || ($this->pdfa_mode && $this->pdfa_version == 3)) {
			if ((($opt['Subtype'] == 'FileAttachment')) AND (!LIMEPDF_STATIC::empty_string($opt['FS']))
				AND (@LIMEPDF_STATIC::file_exists($opt['FS']) OR LIMEPDF_STATIC::isValidURL($opt['FS']))
				AND (!isset($this->embeddedfiles[basename($opt['FS'])]))) {
				$this->embeddedfiles[basename($opt['FS'])] = array('f' => ++$this->n, 'n' => ++$this->n, 'file' => $opt['FS']);
			}
		}
	}

	/**
	 * Embed the attached files.
	 * @since 6.9.000 (2025-02-11)
	 * @public
	 */
	public function EmbedFileFromString($filename, $content) {
		if (!$this->pdfa_mode || ($this->pdfa_mode && $this->pdfa_version == 3)) {
			$this->embeddedfiles[$filename] = array('f' => ++$this->n, 'n' => ++$this->n, 'content' => $content );
		}
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
	 * Prints a cell (rectangular area) with optional borders, background color and character string. The upper-left corner of the cell corresponds to the current position. The text can be aligned or centered. After the call, the current position moves to the right or to the next line. It is possible to put a link on the text.<br />
	 * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done before outputting.
	 * @param float $w Cell width. If 0, the cell extends up to the right margin.
	 * @param float $h Cell height. Default value: 0.
	 * @param string $txt String to print. Default value: empty string.
	 * @param mixed $border Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param int $ln Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right (or left for RTL languages)</li><li>1: to the beginning of the next line</li><li>2: below</li></ul> Putting 1 is equivalent to putting 0 and calling Ln() just after. Default value: 0.
	 * @param string $align Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align (default value)</li><li>C: center</li><li>R: right align</li><li>J: justify</li></ul>
	 * @param boolean $fill Indicates if the cell background must be painted (true) or transparent (false).
	 * @param mixed $link URL or identifier returned by AddLink().
	 * @param int $stretch font stretch mode: <ul><li>0 = disabled</li><li>1 = horizontal scaling only if text is larger than cell width</li><li>2 = forced horizontal scaling to fit cell width</li><li>3 = character spacing only if text is larger than cell width</li><li>4 = forced character spacing to fit cell width</li></ul> General font stretching and scaling values will be preserved when possible.
	 * @param boolean $ignore_min_height if true ignore automatic minimum height value.
	 * @param string $calign cell vertical alignment relative to the specified Y value. Possible values are:<ul><li>T : cell top</li><li>C : center</li><li>B : cell bottom</li><li>A : font top</li><li>L : font baseline</li><li>D : font bottom</li></ul>
	 * @param string $valign text vertical alignment inside the cell. Possible values are:<ul><li>T : top</li><li>C : center</li><li>B : bottom</li></ul>
	 * @public
	 * @since 1.0
	 * @see SetFont(), SetDrawColor(), SetFillColor(), SetTextColor(), SetLineWidth(), AddLink(), Ln(), MultiCell(), Write(), SetAutoPageBreak()
	 */
	public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M') {
		$prev_cell_margin = $this->cell_margin;
		$prev_cell_padding = $this->cell_padding;
		$this->adjustCellPadding($border);
		if (!$ignore_min_height) {
			$min_cell_height = $this->getCellHeight($this->FontSize);
			if ($h < $min_cell_height) {
				$h = $min_cell_height;
			}
		}
		$this->checkPageBreak($h + $this->cell_margin['T'] + $this->cell_margin['B']);
		// apply text shadow if enabled
		if ($this->txtshadow['enabled']) {
			// save data
			$x = $this->x;
			$y = $this->y;
			$bc = $this->bgcolor;
			$fc = $this->fgcolor;
			$sc = $this->strokecolor;
			$alpha = $this->alpha;
			// print shadow
			$this->x += $this->txtshadow['depth_w'];
			$this->y += $this->txtshadow['depth_h'];
			$this->setFillColorArray($this->txtshadow['color']);
			$this->setTextColorArray($this->txtshadow['color']);
			$this->setDrawColorArray($this->txtshadow['color']);
			if ($this->txtshadow['opacity'] != $alpha['CA']) {
				$this->setAlpha($this->txtshadow['opacity'], $this->txtshadow['blend_mode']);
			}
			if ($this->state == 2) {
				$this->_out($this->getCellCode($w, $h, $txt, $border, $ln, $align, $fill, $link, $stretch, true, $calign, $valign));
			}
			//restore data
			$this->x = $x;
			$this->y = $y;
			$this->setFillColorArray($bc);
			$this->setTextColorArray($fc);
			$this->setDrawColorArray($sc);
			if ($this->txtshadow['opacity'] != $alpha['CA']) {
				$this->setAlpha($alpha['CA'], $alpha['BM'], $alpha['ca'], $alpha['AIS']);
			}
		}
		if ($this->state == 2) {
			$this->_out($this->getCellCode($w, $h, $txt, $border, $ln, $align, $fill, $link, $stretch, true, $calign, $valign));
		}
		$this->cell_padding = $prev_cell_padding;
		$this->cell_margin = $prev_cell_margin;
	}

	/**
	 * Returns the PDF string code to print a cell (rectangular area) with optional borders, background color and character string. The upper-left corner of the cell corresponds to the current position. The text can be aligned or centered. After the call, the current position moves to the right or to the next line. It is possible to put a link on the text.<br />
	 * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done before outputting.
	 * @param float $w Cell width. If 0, the cell extends up to the right margin.
	 * @param float $h Cell height. Default value: 0.
	 * @param string $txt String to print. Default value: empty string.
	 * @param mixed $border Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param int $ln Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right (or left for RTL languages)</li><li>1: to the beginning of the next line</li><li>2: below</li></ul>Putting 1 is equivalent to putting 0 and calling Ln() just after. Default value: 0.
	 * @param string $align Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align (default value)</li><li>C: center</li><li>R: right align</li><li>J: justify</li></ul>
	 * @param boolean $fill Indicates if the cell background must be painted (true) or transparent (false).
	 * @param mixed $link URL or identifier returned by AddLink().
	 * @param int $stretch font stretch mode: <ul><li>0 = disabled</li><li>1 = horizontal scaling only if text is larger than cell width</li><li>2 = forced horizontal scaling to fit cell width</li><li>3 = character spacing only if text is larger than cell width</li><li>4 = forced character spacing to fit cell width</li></ul> General font stretching and scaling values will be preserved when possible.
	 * @param boolean $ignore_min_height if true ignore automatic minimum height value.
	 * @param string $calign cell vertical alignment relative to the specified Y value. Possible values are:<ul><li>T : cell top</li><li>C : center</li><li>B : cell bottom</li><li>A : font top</li><li>L : font baseline</li><li>D : font bottom</li></ul>
	 * @param string $valign text vertical alignment inside the cell. Possible values are:<ul><li>T : top</li><li>M : middle</li><li>B : bottom</li></ul>
	 * @return string containing cell code
	 * @protected
	 * @since 1.0
	 * @see Cell()
	 */
	protected function getCellCode($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M') {
		// replace 'NO-BREAK SPACE' (U+00A0) character with a simple space
		$txt = is_null($txt) ? '' : $txt;
		$txt = str_replace(LIMEPDF_FONT::unichr(160, $this->isunicode), ' ', $txt);
		$prev_cell_margin = $this->cell_margin;
		$prev_cell_padding = $this->cell_padding;
		$txt = LIMEPDF_STATIC::removeSHY($txt, $this->isunicode);
		$rs = ''; //string to be returned
		$this->adjustCellPadding($border);
		if (!$ignore_min_height) {
			$min_cell_height = $this->getCellHeight($this->FontSize);
			if ($h < $min_cell_height) {
				$h = $min_cell_height;
			}
		}
		$k = $this->k;
		// check page for no-write regions and adapt page margins if necessary
		list($this->x, $this->y) = $this->checkPageRegions($h, $this->x, $this->y);
		if ($this->rtl) {
			$x = $this->x - $this->cell_margin['R'];
		} else {
			$x = $this->x + $this->cell_margin['L'];
		}
		$y = $this->y + $this->cell_margin['T'];
		$prev_font_stretching = $this->font_stretching;
		$prev_font_spacing = $this->font_spacing;
		// cell vertical alignment
		switch ($calign) {
			case 'A': {
				// font top
				switch ($valign) {
					case 'T': {
						// top
						$y -= $this->cell_padding['T'];
						break;
					}
					case 'B': {
						// bottom
						$y -= ($h - $this->cell_padding['B'] - $this->FontAscent - $this->FontDescent);
						break;
					}
					default:
					case 'C':
					case 'M': {
						// center
						$y -= (($h - $this->FontAscent - $this->FontDescent) / 2);
						break;
					}
				}
				break;
			}
			case 'L': {
				// font baseline
				switch ($valign) {
					case 'T': {
						// top
						$y -= ($this->cell_padding['T'] + $this->FontAscent);
						break;
					}
					case 'B': {
						// bottom
						$y -= ($h - $this->cell_padding['B'] - $this->FontDescent);
						break;
					}
					default:
					case 'C':
					case 'M': {
						// center
						$y -= (($h + $this->FontAscent - $this->FontDescent) / 2);
						break;
					}
				}
				break;
			}
			case 'D': {
				// font bottom
				switch ($valign) {
					case 'T': {
						// top
						$y -= ($this->cell_padding['T'] + $this->FontAscent + $this->FontDescent);
						break;
					}
					case 'B': {
						// bottom
						$y -= ($h - $this->cell_padding['B']);
						break;
					}
					default:
					case 'C':
					case 'M': {
						// center
						$y -= (($h + $this->FontAscent + $this->FontDescent) / 2);
						break;
					}
				}
				break;
			}
			case 'B': {
				// cell bottom
				$y -= $h;
				break;
			}
			case 'C':
			case 'M': {
				// cell center
				$y -= ($h / 2);
				break;
			}
			default:
			case 'T': {
				// cell top
				break;
			}
		}
		// text vertical alignment
		switch ($valign) {
			case 'T': {
				// top
				$yt = $y + $this->cell_padding['T'];
				break;
			}
			case 'B': {
				// bottom
				$yt = $y + $h - $this->cell_padding['B'] - $this->FontAscent - $this->FontDescent;
				break;
			}
			default:
			case 'C':
			case 'M': {
				// center
				$yt = $y + (($h - $this->FontAscent - $this->FontDescent) / 2);
				break;
			}
		}
		$basefonty = $yt + $this->FontAscent;
		if (LIMEPDF_STATIC::empty_string($w) OR ($w <= 0)) {
			if ($this->rtl) {
				$w = $x - $this->lMargin;
			} else {
				$w = $this->w - $this->rMargin - $x;
			}
		}
		$s = '';
		// fill and borders
		if (is_string($border) AND (strlen($border) == 4)) {
			// full border
			$border = 1;
		}
		if ($fill OR ($border == 1)) {
			if ($fill) {
				$op = ($border == 1) ? 'B' : 'f';
			} else {
				$op = 'S';
			}
			if ($this->rtl) {
				$xk = (($x - $w) * $k);
			} else {
				$xk = ($x * $k);
			}
			$s .= sprintf('%F %F %F %F re %s ', $xk, (($this->h - $y) * $k), ($w * $k), (-$h * $k), $op);
		}
		// draw borders
		$s .= $this->getCellBorder($x, $y, $w, $h, $border);
		if ($txt != '') {
			$txt2 = $txt;
			if ($this->isunicode) {
				if (($this->CurrentFont['type'] == 'core') OR ($this->CurrentFont['type'] == 'TrueType') OR ($this->CurrentFont['type'] == 'Type1')) {
					$txt2 = LIMEPDF_FONT::UTF8ToLatin1($txt2, $this->isunicode, $this->CurrentFont);
				} else {
					$unicode = LIMEPDF_FONT::UTF8StringToArray($txt, $this->isunicode, $this->CurrentFont); // array of UTF-8 unicode values
					$unicode = LIMEPDF_FONT::utf8Bidi($unicode, '', $this->tmprtl, $this->isunicode, $this->CurrentFont);
					// replace thai chars (if any)
					if (defined('K_THAI_TOPCHARS') AND (K_THAI_TOPCHARS == true)) {
						// number of chars
						$numchars = count($unicode);
						// po pla, for far, for fan
						$longtail = array(0x0e1b, 0x0e1d, 0x0e1f);
						// do chada, to patak
						$lowtail = array(0x0e0e, 0x0e0f);
						// mai hun arkad, sara i, sara ii, sara ue, sara uee
						$upvowel = array(0x0e31, 0x0e34, 0x0e35, 0x0e36, 0x0e37);
						// mai ek, mai tho, mai tri, mai chattawa, karan
						$tonemark = array(0x0e48, 0x0e49, 0x0e4a, 0x0e4b, 0x0e4c);
						// sara u, sara uu, pinthu
						$lowvowel = array(0x0e38, 0x0e39, 0x0e3a);
						$output = array();
						for ($i = 0; $i < $numchars; $i++) {
							if (($unicode[$i] >= 0x0e00) && ($unicode[$i] <= 0x0e5b)) {
								$ch0 = $unicode[$i];
								$ch1 = ($i > 0) ? $unicode[($i - 1)] : 0;
								$ch2 = ($i > 1) ? $unicode[($i - 2)] : 0;
								$chn = ($i < ($numchars - 1)) ? $unicode[($i + 1)] : 0;
								if (in_array($ch0, $tonemark)) {
									if ($chn == 0x0e33) {
										// sara um
										if (in_array($ch1, $longtail)) {
											// tonemark at upper left
											$output[] = $this->replaceChar($ch0, (0xf713 + $ch0 - 0x0e48));
										} else {
											// tonemark at upper right (normal position)
											$output[] = $ch0;
										}
									} elseif (in_array($ch1, $longtail) OR (in_array($ch2, $longtail) AND in_array($ch1, $lowvowel))) {
										// tonemark at lower left
										$output[] = $this->replaceChar($ch0, (0xf705 + $ch0 - 0x0e48));
									} elseif (in_array($ch1, $upvowel)) {
										if (in_array($ch2, $longtail)) {
											// tonemark at upper left
											$output[] = $this->replaceChar($ch0, (0xf713 + $ch0 - 0x0e48));
										} else {
											// tonemark at upper right (normal position)
											$output[] = $ch0;
										}
									} else {
										// tonemark at lower right
										$output[] = $this->replaceChar($ch0, (0xf70a + $ch0 - 0x0e48));
									}
								} elseif (($ch0 == 0x0e33) AND (in_array($ch1, $longtail) OR (in_array($ch2, $longtail) AND in_array($ch1, $tonemark)))) {
									// add lower left nikhahit and sara aa
									if ($this->isCharDefined(0xf711) AND $this->isCharDefined(0x0e32)) {
										$output[] = 0xf711;
										$this->CurrentFont['subsetchars'][0xf711] = true;
										$output[] = 0x0e32;
										$this->CurrentFont['subsetchars'][0x0e32] = true;
									} else {
										$output[] = $ch0;
									}
								} elseif (in_array($ch1, $longtail)) {
									if ($ch0 == 0x0e31) {
										// lower left mai hun arkad
										$output[] = $this->replaceChar($ch0, 0xf710);
									} elseif (in_array($ch0, $upvowel)) {
										// lower left
										$output[] = $this->replaceChar($ch0, (0xf701 + $ch0 - 0x0e34));
									} elseif ($ch0 == 0x0e47) {
										// lower left mai tai koo
										$output[] = $this->replaceChar($ch0, 0xf712);
									} else {
										// normal character
										$output[] = $ch0;
									}
								} elseif (in_array($ch1, $lowtail) AND in_array($ch0, $lowvowel)) {
									// lower vowel
									$output[] = $this->replaceChar($ch0, (0xf718 + $ch0 - 0x0e38));
								} elseif (($ch0 == 0x0e0d) AND in_array($chn, $lowvowel)) {
									// yo ying without lower part
									$output[] = $this->replaceChar($ch0, 0xf70f);
								} elseif (($ch0 == 0x0e10) AND in_array($chn, $lowvowel)) {
									// tho santan without lower part
									$output[] = $this->replaceChar($ch0, 0xf700);
								} else {
									$output[] = $ch0;
								}
							} else {
								// non-thai character
								$output[] = $unicode[$i];
							}
						}
						$unicode = $output;
						// update font subsetchars
						$this->setFontSubBuffer($this->CurrentFont['fontkey'], 'subsetchars', $this->CurrentFont['subsetchars']);
					} // end of K_THAI_TOPCHARS
					$txt2 = LIMEPDF_FONT::arrUTF8ToUTF16BE($unicode, false);
				}
			}
			$txt2 = LIMEPDF_STATIC::_escape($txt2);
			// get current text width (considering general font stretching and spacing)
			$txwidth = $this->GetStringWidth($txt);
			$width = $txwidth;
			// check for stretch mode
			if ($stretch > 0) {
				// calculate ratio between cell width and text width
				if ($width <= 0) {
					$ratio = 1;
				} else {
					$ratio = (($w - $this->cell_padding['L'] - $this->cell_padding['R']) / $width);
				}
				// check if stretching is required
				if (($ratio < 1) OR (($ratio > 1) AND (($stretch % 2) == 0))) {
					// the text will be stretched to fit cell width
					if ($stretch > 2) {
						// set new character spacing
						$this->font_spacing += ($w - $this->cell_padding['L'] - $this->cell_padding['R'] - $width) / (max(($this->GetNumChars($txt) - 1), 1) * ($this->font_stretching / 100));
					} else {
						// set new horizontal stretching
						$this->font_stretching *= $ratio;
					}
					// recalculate text width (the text fills the entire cell)
					$width = $w - $this->cell_padding['L'] - $this->cell_padding['R'];
					// reset alignment
					$align = '';
				}
			}
			if ($this->font_stretching != 100) {
				// apply font stretching
				$rs .= sprintf('BT %F Tz ET ', $this->font_stretching);
			}
			if ($this->font_spacing != 0) {
				// increase/decrease font spacing
				$rs .= sprintf('BT %F Tc ET ', ($this->font_spacing * $this->k));
			}
			if ($this->ColorFlag AND ($this->textrendermode < 4)) {
				$s .= 'q '.$this->TextColor.' ';
			}
			// rendering mode
			$s .= sprintf('BT %d Tr %F w ET ', $this->textrendermode, ($this->textstrokewidth * $this->k));
			// count number of spaces
			$ns = substr_count($txt, chr(32));
			// Justification
			$spacewidth = 0;
			if (($align == 'J') AND ($ns > 0)) {
				if ($this->isUnicodeFont()) {
					// get string width without spaces
					$width = $this->GetStringWidth(str_replace(' ', '', $txt));
					// calculate average space width
					$spacewidth = -1000 * ($w - $width - $this->cell_padding['L'] - $this->cell_padding['R']) / ($ns?$ns:1) / ($this->FontSize?$this->FontSize:1);
					if ($this->font_stretching != 100) {
						// word spacing is affected by stretching
						$spacewidth /= ($this->font_stretching / 100);
					}
					// set word position to be used with TJ operator
					$txt2 = str_replace(chr(0).chr(32), ') '.sprintf('%F', $spacewidth).' (', $txt2);
					$unicode_justification = true;
				} else {
					// get string width
					$width = $txwidth;
					// new space width
					$spacewidth = (($w - $width - $this->cell_padding['L'] - $this->cell_padding['R']) / ($ns?$ns:1)) * $this->k;
					if ($this->font_stretching != 100) {
						// word spacing (Tw) is affected by stretching
						$spacewidth /= ($this->font_stretching / 100);
					}
					// set word spacing
					$rs .= sprintf('BT %F Tw ET ', $spacewidth);
				}
				$width = $w - $this->cell_padding['L'] - $this->cell_padding['R'];
			}
			// replace carriage return characters
			$txt2 = str_replace("\r", ' ', $txt2);
			switch ($align) {
				case 'C': {
					$dx = ($w - $width) / 2;
					break;
				}
				case 'R': {
					if ($this->rtl) {
						$dx = $this->cell_padding['R'];
					} else {
						$dx = $w - $width - $this->cell_padding['R'];
					}
					break;
				}
				case 'L': {
					if ($this->rtl) {
						$dx = $w - $width - $this->cell_padding['L'];
					} else {
						$dx = $this->cell_padding['L'];
					}
					break;
				}
				case 'J':
				default: {
					if ($this->rtl) {
						$dx = $this->cell_padding['R'];
					} else {
						$dx = $this->cell_padding['L'];
					}
					break;
				}
			}
			if ($this->rtl) {
				$xdx = $x - $dx - $width;
			} else {
				$xdx = $x + $dx;
			}
			$xdk = $xdx * $k;
			// print text
			$s .= sprintf('BT %F %F Td [(%s)] TJ ET', $xdk, (($this->h - $basefonty) * $k), $txt2);
			if (isset($uniblock)) { // @phpstan-ignore-line
				// print overlapping characters as separate string
				$xshift = 0; // horizontal shift
				$ty = (($this->h - $basefonty + (0.2 * $this->FontSize)) * $k);
				$spw = (($w - $txwidth - $this->cell_padding['L'] - $this->cell_padding['R']) / ($ns?$ns:1));
				foreach ($uniblock as $uk => $uniarr) { // @phpstan-ignore-line
					if (($uk % 2) == 0) {
						// x space to skip
						if ($spacewidth != 0) {
							// justification shift
							$xshift += (count(array_keys($uniarr, 32)) * $spw);
						}
						$xshift += $this->GetArrStringWidth($uniarr); // + shift justification
					} else {
						// character to print
						$topchr = LIMEPDF_FONT::arrUTF8ToUTF16BE($uniarr, false);
						$topchr = LIMEPDF_STATIC::_escape($topchr);
						$s .= sprintf(' BT %F %F Td [(%s)] TJ ET', ($xdk + ($xshift * $k)), $ty, $topchr);
					}
				}
			}
			if ($this->underline) {
				$s .= ' '.$this->_dounderlinew($xdx, $basefonty, $width);
			}
			if ($this->linethrough) {
				$s .= ' '.$this->_dolinethroughw($xdx, $basefonty, $width);
			}
			if ($this->overline) {
				$s .= ' '.$this->_dooverlinew($xdx, $basefonty, $width);
			}
			if ($this->ColorFlag AND ($this->textrendermode < 4)) {
				$s .= ' Q';
			}
			if ($link) {
				$this->Link($xdx, $yt, $width, ($this->FontAscent + $this->FontDescent), $link, $ns);
			}
		}
		// output cell
		if ($s) {
			// output cell
			$rs .= $s;
			if ($this->font_spacing != 0) {
				// reset font spacing mode
				$rs .= ' BT 0 Tc ET';
			}
			if ($this->font_stretching != 100) {
				// reset font stretching mode
				$rs .= ' BT 100 Tz ET';
			}
		}
		// reset word spacing
		if (!$this->isUnicodeFont() AND ($align == 'J')) {
			$rs .= ' BT 0 Tw ET';
		}
		// reset stretching and spacing
		$this->font_stretching = $prev_font_stretching;
		$this->font_spacing = $prev_font_spacing;
		$this->lasth = $h;
		if ($ln > 0) {
			//Go to the beginning of the next line
			$this->y = $y + $h + $this->cell_margin['B'];
			if ($ln == 1) {
				if ($this->rtl) {
					$this->x = $this->w - $this->rMargin;
				} else {
					$this->x = $this->lMargin;
				}
			}
		} else {
			// go left or right by case
			if ($this->rtl) {
				$this->x = $x - $w - $this->cell_margin['L'];
			} else {
				$this->x = $x + $w + $this->cell_margin['R'];
			}
		}
		$gstyles = ''.$this->linestyleWidth.' '.$this->linestyleCap.' '.$this->linestyleJoin.' '.$this->linestyleDash.' '.$this->DrawColor.' '.$this->FillColor."\n";
		$rs = $gstyles.$rs;
		$this->cell_padding = $prev_cell_padding;
		$this->cell_margin = $prev_cell_margin;
		return $rs;
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
	 * Returns the code to draw the cell border
	 * @param float $x X coordinate.
	 * @param float $y Y coordinate.
	 * @param float $w Cell width.
	 * @param float $h Cell height.
	 * @param string|array|int $brd Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @return string containing cell border code
	 * @protected
	 * @see SetLineStyle()
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCellBorder($x, $y, $w, $h, $brd) {
		$s = ''; // string to be returned
		if (empty($brd)) {
			return $s;
		}
		if ($brd == 1) {
			$brd = array('LRTB' => true);
		}
		// calculate coordinates for border
		$k = $this->k;
		if ($this->rtl) {
			$xeL = ($x - $w) * $k;
			$xeR = $x * $k;
		} else {
			$xeL = $x * $k;
			$xeR = ($x + $w) * $k;
		}
		$yeL = (($this->h - ($y + $h)) * $k);
		$yeT = (($this->h - $y) * $k);
		$xeT = $xeL;
		$xeB = $xeR;
		$yeR = $yeT;
		$yeB = $yeL;
		if (is_string($brd)) {
			// convert string to array
			$slen = strlen($brd);
			$newbrd = array();
			for ($i = 0; $i < $slen; ++$i) {
				$newbrd[$brd[$i]] = array('cap' => 'square', 'join' => 'miter');
			}
			$brd = $newbrd;
		}
		if (isset($brd['mode'])) {
			$mode = $brd['mode'];
			unset($brd['mode']);
		} else {
			$mode = 'normal';
		}
		foreach ($brd as $border => $style) {
			if (is_array($style) AND !empty($style)) {
				// apply border style
				$prev_style = $this->linestyleWidth.' '.$this->linestyleCap.' '.$this->linestyleJoin.' '.$this->linestyleDash.' '.$this->DrawColor.' ';
				$s .= $this->setLineStyle($style, true)."\n";
			}
			switch ($mode) {
				case 'ext': {
					$off = (($this->LineWidth / 2) * $k);
					$xL = $xeL - $off;
					$xR = $xeR + $off;
					$yT = $yeT + $off;
					$yL = $yeL - $off;
					$xT = $xL;
					$xB = $xR;
					$yR = $yT;
					$yB = $yL;
					$w += $this->LineWidth;
					$h += $this->LineWidth;
					break;
				}
				case 'int': {
					$off = ($this->LineWidth / 2) * $k;
					$xL = $xeL + $off;
					$xR = $xeR - $off;
					$yT = $yeT - $off;
					$yL = $yeL + $off;
					$xT = $xL;
					$xB = $xR;
					$yR = $yT;
					$yB = $yL;
					$w -= $this->LineWidth;
					$h -= $this->LineWidth;
					break;
				}
				case 'normal':
				default: {
					$xL = $xeL;
					$xT = $xeT;
					$xB = $xeB;
					$xR = $xeR;
					$yL = $yeL;
					$yT = $yeT;
					$yB = $yeB;
					$yR = $yeR;
					break;
				}
			}
			// draw borders by case
			if (strlen($border) == 4) {
				$s .= sprintf('%F %F %F %F re S ', $xT, $yT, ($w * $k), (-$h * $k));
			} elseif (strlen($border) == 3) {
				if (strpos($border,'B') === false) { // LTR
					$s .= sprintf('%F %F m ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= 'S ';
				} elseif (strpos($border,'L') === false) { // TRB
					$s .= sprintf('%F %F m ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= 'S ';
				} elseif (strpos($border,'T') === false) { // RBL
					$s .= sprintf('%F %F m ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= 'S ';
				} elseif (strpos($border,'R') === false) { // BLT
					$s .= sprintf('%F %F m ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= 'S ';
				}
			} elseif (strlen($border) == 2) {
				if ((strpos($border,'L') !== false) AND (strpos($border,'T') !== false)) { // LT
					$s .= sprintf('%F %F m ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= 'S ';
				} elseif ((strpos($border,'T') !== false) AND (strpos($border,'R') !== false)) { // TR
					$s .= sprintf('%F %F m ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= 'S ';
				} elseif ((strpos($border,'R') !== false) AND (strpos($border,'B') !== false)) { // RB
					$s .= sprintf('%F %F m ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= 'S ';
				} elseif ((strpos($border,'B') !== false) AND (strpos($border,'L') !== false)) { // BL
					$s .= sprintf('%F %F m ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= 'S ';
				} elseif ((strpos($border,'L') !== false) AND (strpos($border,'R') !== false)) { // LR
					$s .= sprintf('%F %F m ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= 'S ';
					$s .= sprintf('%F %F m ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= 'S ';
				} elseif ((strpos($border,'T') !== false) AND (strpos($border,'B') !== false)) { // TB
					$s .= sprintf('%F %F m ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= 'S ';
					$s .= sprintf('%F %F m ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= 'S ';
				}
			} else { // strlen($border) == 1
				if (strpos($border,'L') !== false) { // L
					$s .= sprintf('%F %F m ', $xL, $yL);
					$s .= sprintf('%F %F l ', $xT, $yT);
					$s .= 'S ';
				} elseif (strpos($border,'T') !== false) { // T
					$s .= sprintf('%F %F m ', $xT, $yT);
					$s .= sprintf('%F %F l ', $xR, $yR);
					$s .= 'S ';
				} elseif (strpos($border,'R') !== false) { // R
					$s .= sprintf('%F %F m ', $xR, $yR);
					$s .= sprintf('%F %F l ', $xB, $yB);
					$s .= 'S ';
				} elseif (strpos($border,'B') !== false) { // B
					$s .= sprintf('%F %F m ', $xB, $yB);
					$s .= sprintf('%F %F l ', $xL, $yL);
					$s .= 'S ';
				}
			}
			if (is_array($style) AND !empty($style)) {
				// reset border style to previous value
				$s .= "\n".$this->linestyleWidth.' '.$this->linestyleCap.' '.$this->linestyleJoin.' '.$this->linestyleDash.' '.$this->DrawColor."\n";
			}
		}
		return $s;
	}

	/**
	 * This method allows printing text with line breaks.
	 * They can be automatic (as soon as the text reaches the right border of the cell) or explicit (via the \n character). As many cells as necessary are output, one below the other.<br />
	 * Text can be aligned, centered or justified. The cell block can be framed and the background painted.
	 * @param float $w Width of cells. If 0, they extend up to the right margin of the page.
	 * @param float $h Cell minimum height. The cell extends automatically if needed.
	 * @param string $txt String to print
	 * @param mixed $border Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param string $align Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align</li><li>C: center</li><li>R: right align</li><li>J: justification (default value when $ishtml=false)</li></ul>
	 * @param boolean $fill Indicates if the cell background must be painted (true) or transparent (false).
	 * @param int $ln Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right</li><li>1: to the beginning of the next line [DEFAULT]</li><li>2: below</li></ul>
	 * @param float|null $x x position in user units
	 * @param float|null $y y position in user units
	 * @param boolean $reseth if true reset the last cell height (default true).
	 * @param int $stretch font stretch mode: <ul><li>0 = disabled</li><li>1 = horizontal scaling only if text is larger than cell width</li><li>2 = forced horizontal scaling to fit cell width</li><li>3 = character spacing only if text is larger than cell width</li><li>4 = forced character spacing to fit cell width</li></ul> General font stretching and scaling values will be preserved when possible.
	 * @param boolean $ishtml INTERNAL USE ONLY -- set to true if $txt is HTML content (default = false). Never set this parameter to true, use instead writeHTMLCell() or writeHTML() methods.
	 * @param boolean $autopadding if true, uses internal padding and automatically adjust it to account for line width.
	 * @param float $maxh maximum height. It should be >= $h and less then remaining space to the bottom of the page, or 0 for disable this feature. This feature works only when $ishtml=false.
	 * @param string $valign Vertical alignment of text (requires $maxh = $h > 0). Possible values are:<ul><li>T: TOP</li><li>M: middle</li><li>B: bottom</li></ul>. This feature works only when $ishtml=false and the cell must fit in a single page.
	 * @param boolean $fitcell if true attempt to fit all the text within the cell by reducing the font size (do not work in HTML mode). $maxh must be greater than 0 and equal to $h.
	 * @return int Return the number of cells or 1 for html mode.
	 * @public
	 * @since 1.3
	 * @see SetFont(), SetDrawColor(), SetFillColor(), SetTextColor(), SetLineWidth(), Cell(), Write(), SetAutoPageBreak()
	 */
	public function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false, $ln=1, $x=null, $y=null, $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false) {
		$prev_cell_margin = $this->cell_margin;
		$prev_cell_padding = $this->cell_padding;
		// adjust internal padding
		$this->adjustCellPadding($border);
		$mc_padding = $this->cell_padding;
		$mc_margin = $this->cell_margin;
		$this->cell_padding['T'] = 0;
		$this->cell_padding['B'] = 0;
		$this->setCellMargins(0, 0, 0, 0);
		if (LIMEPDF_STATIC::empty_string($this->lasth) OR $reseth) {
			// reset row height
			$this->resetLastH();
		}
		if (!LIMEPDF_STATIC::empty_string($y)) {
			$this->setY($y); // set y in order to convert negative y values to positive ones
		}
		$y = $this->GetY();
		$resth = 0;
		if (($h > 0) AND $this->inPageBody() AND (($y + $h + $mc_margin['T'] + $mc_margin['B']) > $this->PageBreakTrigger)) {
			// spit cell in more pages/columns
			$newh = ($this->PageBreakTrigger - $y);
			$resth = ($h - $newh); // cell to be printed on the next page/column
			$h = $newh;
		}
		// get current page number
		$startpage = $this->page;
		// get current column
		$startcolumn = $this->current_column;
		if (!LIMEPDF_STATIC::empty_string($x)) {
			$this->setX($x);
		} else {
			$x = $this->GetX();
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions(0, $x, $y);
		// apply margins
		$oy = $y + $mc_margin['T'];
		if ($this->rtl) {
			$ox = ($this->w - $x - $mc_margin['R']);
		} else {
			$ox = ($x + $mc_margin['L']);
		}
		$this->x = $ox;
		$this->y = $oy;
		// set width
		if (LIMEPDF_STATIC::empty_string($w) OR ($w <= 0)) {
			if ($this->rtl) {
				$w = ($this->x - $this->lMargin - $mc_margin['L']);
			} else {
				$w = ($this->w - $this->x - $this->rMargin - $mc_margin['R']);
			}
		}
		// store original margin values
		$lMargin = $this->lMargin;
		$rMargin = $this->rMargin;
		if ($this->rtl) {
			$this->rMargin = ($this->w - $this->x);
			$this->lMargin = ($this->x - $w);
		} else {
			$this->lMargin = ($this->x);
			$this->rMargin = ($this->w - $this->x - $w);
		}
		$this->clMargin = $this->lMargin;
		$this->crMargin = $this->rMargin;
		if ($autopadding) {
			// add top padding
			$this->y += $mc_padding['T'];
		}
		if ($ishtml) { // ******* Write HTML text
			$this->writeHTML($txt, true, false, $reseth, true, $align);
			$nl = 1;
		} else { // ******* Write simple text
			$prev_FontSizePt = $this->FontSizePt;
			if ($fitcell) {
				// ajust height values
				$tobottom = ($this->h - $this->y - $this->bMargin - $this->cell_padding['T'] - $this->cell_padding['B']);
				$h = $maxh = max(min($h, $tobottom), min($maxh, $tobottom));
			}
			// vertical alignment
			if ($maxh > 0) {
				// get text height
				$text_height = $this->getStringHeight($w, $txt, $reseth, $autopadding, $mc_padding, $border);
				if ($fitcell AND ($text_height > $maxh) AND ($this->FontSizePt > 1)) {
					// try to reduce font size to fit text on cell (use a quick search algorithm)
					$fmin = 1;
					$fmax = $this->FontSizePt;
					$diff_epsilon = (1 / $this->k); // one point (min resolution)
					$maxit = (2 * min(100, max(10, intval($fmax)))); // max number of iterations
					while ($maxit >= 0) {
						$fmid = (($fmax + $fmin) / 2);
						$this->setFontSize($fmid, false);
						$this->resetLastH();
						$text_height = $this->getStringHeight($w, $txt, $reseth, $autopadding, $mc_padding, $border);
						$diff = ($maxh - $text_height);
						if ($diff >= 0) {
							if ($diff <= $diff_epsilon) {
								break;
							}
							$fmin = $fmid;
						} else {
							$fmax = $fmid;
						}
						--$maxit;
					}
					if ($maxit < 0) {
						// premature exit, we get the minimum font value to fit the cell
						$this->setFontSize($fmin);
						$this->resetLastH();
						$text_height = $this->getStringHeight($w, $txt, $reseth, $autopadding, $mc_padding, $border);
					} else {
						$this->setFontSize($fmid);
						$this->resetLastH();
					}
				}
				if ($text_height < $maxh) {
					if ($valign == 'M') {
						// text vertically centered
						$this->y += (($maxh - $text_height) / 2);
					} elseif ($valign == 'B') {
						// text vertically aligned on bottom
						$this->y += ($maxh - $text_height);
					}
				}
			}
			$nl = $this->Write($this->lasth, $txt, '', 0, $align, true, $stretch, false, true, $maxh, 0, $mc_margin);
			if ($fitcell) {
				// restore font size
				$this->setFontSize($prev_FontSizePt);
			}
		}
		if ($autopadding) {
			// add bottom padding
			$this->y += $mc_padding['B'];
		}
		// Get end-of-text Y position
		$currentY = $this->y;
		// get latest page number
		$endpage = $this->page;
		if ($resth > 0) {
			$skip = ($endpage - $startpage);
			$tmpresth = $resth;
			while ($tmpresth > 0) {
				if ($skip <= 0) {
					// add a page (or trig AcceptPageBreak() for multicolumn mode)
					$this->checkPageBreak($this->PageBreakTrigger + 1);
				}
				if ($this->num_columns > 1) {
					$tmpresth -= ($this->h - $this->y - $this->bMargin);
				} else {
					$tmpresth -= ($this->h - $this->tMargin - $this->bMargin);
				}
				--$skip;
			}
			$currentY = $this->y;
			$endpage = $this->page;
		}
		// get latest column
		$endcolumn = $this->current_column;
		if ($this->num_columns == 0) {
			$this->num_columns = 1;
		}
		// disable page regions check
		$check_page_regions = $this->check_page_regions;
		$this->check_page_regions = false;
		// get border modes
		$border_start = LIMEPDF_STATIC::getBorderMode($border, $position='start', $this->opencell);
		$border_end = LIMEPDF_STATIC::getBorderMode($border, $position='end', $this->opencell);
		$border_middle = LIMEPDF_STATIC::getBorderMode($border, $position='middle', $this->opencell);
		// design borders around HTML cells.
		for ($page = $startpage; $page <= $endpage; ++$page) { // for each page
			$ccode = '';
			$this->setPage($page);
			if ($this->num_columns < 2) {
				// single-column mode
				$this->setX($x);
				$this->y = $this->tMargin;
			}
			// account for margin changes
			if ($page > $startpage) {
				if (($this->rtl) AND ($this->pagedim[$page]['orm'] != $this->pagedim[$startpage]['orm'])) {
					$this->x -= ($this->pagedim[$page]['orm'] - $this->pagedim[$startpage]['orm']);
				} elseif ((!$this->rtl) AND ($this->pagedim[$page]['olm'] != $this->pagedim[$startpage]['olm'])) {
					$this->x += ($this->pagedim[$page]['olm'] - $this->pagedim[$startpage]['olm']);
				}
			}
			if ($startpage == $endpage) {
				// single page
				for ($column = $startcolumn; $column <= $endcolumn; ++$column) { // for each column
					if ($column != $this->current_column) {
						$this->selectColumn($column);
					}
					if ($this->rtl) {
						$this->x -= $mc_margin['R'];
					} else {
						$this->x += $mc_margin['L'];
					}
					if ($startcolumn == $endcolumn) { // single column
						$cborder = $border;
						$h = max($h, ($currentY - $oy));
						$this->y = $oy;
					} elseif ($column == $startcolumn) { // first column
						$cborder = $border_start;
						$this->y = $oy;
						$h = $this->h - $this->y - $this->bMargin;
					} elseif ($column == $endcolumn) { // end column
						$cborder = $border_end;
						$h = $currentY - $this->y;
						if ($resth > $h) {
							$h = $resth;
						}
					} else { // middle column
						$cborder = $border_middle;
						$h = $this->h - $this->y - $this->bMargin;
						$resth -= $h;
					}
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			} elseif ($page == $startpage) { // first page
				for ($column = $startcolumn; $column < $this->num_columns; ++$column) { // for each column
					if ($column != $this->current_column) {
						$this->selectColumn($column);
					}
					if ($this->rtl) {
						$this->x -= $mc_margin['R'];
					} else {
						$this->x += $mc_margin['L'];
					}
					if ($column == $startcolumn) { // first column
						$cborder = $border_start;
						$this->y = $oy;
						$h = $this->h - $this->y - $this->bMargin;
					} else { // middle column
						$cborder = $border_middle;
						$h = $this->h - $this->y - $this->bMargin;
						$resth -= $h;
					}
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			} elseif ($page == $endpage) { // last page
				for ($column = 0; $column <= $endcolumn; ++$column) { // for each column
					if ($column != $this->current_column) {
						$this->selectColumn($column);
					}
					if ($this->rtl) {
						$this->x -= $mc_margin['R'];
					} else {
						$this->x += $mc_margin['L'];
					}
					if ($column == $endcolumn) {
						// end column
						$cborder = $border_end;
						$h = $currentY - $this->y;
						if ($resth > $h) {
							$h = $resth;
						}
					} else {
						// middle column
						$cborder = $border_middle;
						$h = $this->h - $this->y - $this->bMargin;
						$resth -= $h;
					}
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			} else { // middle page
				for ($column = 0; $column < $this->num_columns; ++$column) { // for each column
					$this->selectColumn($column);
					if ($this->rtl) {
						$this->x -= $mc_margin['R'];
					} else {
						$this->x += $mc_margin['L'];
					}
					$cborder = $border_middle;
					$h = $this->h - $this->y - $this->bMargin;
					$resth -= $h;
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			}
			if ($cborder OR $fill) {
				$offsetlen = strlen($ccode);
				// draw border and fill
				if ($this->inxobj) {
					// we are inside an XObject template
					if (end($this->xobjects[$this->xobjid]['transfmrk']) !== false) {
						$pagemarkkey = key($this->xobjects[$this->xobjid]['transfmrk']);
						$pagemark = $this->xobjects[$this->xobjid]['transfmrk'][$pagemarkkey];
						$this->xobjects[$this->xobjid]['transfmrk'][$pagemarkkey] += $offsetlen;
					} else {
						$pagemark = $this->xobjects[$this->xobjid]['intmrk'];
						$this->xobjects[$this->xobjid]['intmrk'] += $offsetlen;
					}
					$pagebuff = $this->xobjects[$this->xobjid]['outdata'];
					$pstart = substr($pagebuff, 0, $pagemark);
					$pend = substr($pagebuff, $pagemark);
					$this->xobjects[$this->xobjid]['outdata'] = $pstart.$ccode.$pend;
				} else {
					if (end($this->transfmrk[$this->page]) !== false) {
						$pagemarkkey = key($this->transfmrk[$this->page]);
						$pagemark = $this->transfmrk[$this->page][$pagemarkkey];
						$this->transfmrk[$this->page][$pagemarkkey] += $offsetlen;
					} elseif ($this->InFooter) {
						$pagemark = $this->footerpos[$this->page];
						$this->footerpos[$this->page] += $offsetlen;
					} else {
						$pagemark = $this->intmrk[$this->page];
						$this->intmrk[$this->page] += $offsetlen;
					}
					$pagebuff = $this->getPageBuffer($this->page);
					$pstart = substr($pagebuff, 0, $pagemark);
					$pend = substr($pagebuff, $pagemark);
					$this->setPageBuffer($this->page, $pstart.$ccode.$pend);
				}
			}
		} // end for each page
		// restore page regions check
		$this->check_page_regions = $check_page_regions;
		// Get end-of-cell Y position
		$currentY = $this->GetY();
		// restore previous values
		if ($this->num_columns > 1) {
			$this->selectColumn();
		} else {
			// restore original margins
			$this->lMargin = $lMargin;
			$this->rMargin = $rMargin;
			if ($this->page > $startpage) {
				// check for margin variations between pages (i.e. booklet mode)
				$dl = ($this->pagedim[$this->page]['olm'] - $this->pagedim[$startpage]['olm']);
				$dr = ($this->pagedim[$this->page]['orm'] - $this->pagedim[$startpage]['orm']);
				if (($dl != 0) OR ($dr != 0)) {
					$this->lMargin += $dl;
					$this->rMargin += $dr;
				}
			}
		}
		if ($ln > 0) {
			//Go to the beginning of the next line
			$this->setY($currentY + $mc_margin['B']);
			if ($ln == 2) {
				$this->setX($x + $w + $mc_margin['L'] + $mc_margin['R']);
			}
		} else {
			// go left or right by case
			$this->setPage($startpage);
			$this->y = $y;
			$this->setX($x + $w + $mc_margin['L'] + $mc_margin['R']);
		}
		$this->setContentMark();
		$this->cell_padding = $prev_cell_padding;
		$this->cell_margin = $prev_cell_margin;
		$this->clMargin = $this->lMargin;
		$this->crMargin = $this->rMargin;
		return $nl;
	}

	/**
	 * This method return the estimated number of lines for print a simple text string using Multicell() method.
	 * @param string $txt String for calculating his height
	 * @param float $w Width of cells. If 0, they extend up to the right margin of the page.
	 * @param boolean $reseth if true reset the last cell height (default false).
	 * @param boolean $autopadding if true, uses internal padding and automatically adjust it to account for line width (default true).
	 * @param array|null $cellpadding Internal cell padding, if empty uses default cell padding.
	 * @param mixed $border Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @return float Return the minimal height needed for multicell method for printing the $txt param.
	 * @author Alexander Escalona Fern\E1ndez, Nicola Asuni
	 * @public
	 * @since 4.5.011
	 */
	public function getNumLines($txt, $w=0, $reseth=false, $autopadding=true, $cellpadding=null, $border=0) {
		if ($txt === NULL) {
			return 0;
		}
		if ($txt === '') {
			// empty string
			return 1;
		}
		// adjust internal padding
		$prev_cell_padding = $this->cell_padding;
		$prev_lasth = $this->lasth;
		if (is_array($cellpadding)) {
			$this->cell_padding = $cellpadding;
		}
		$this->adjustCellPadding($border);
		if (LIMEPDF_STATIC::empty_string($w) OR ($w <= 0)) {
			if ($this->rtl) {
				$w = $this->x - $this->lMargin;
			} else {
				$w = $this->w - $this->rMargin - $this->x;
			}
		}
		$wmax = $w - $this->cell_padding['L'] - $this->cell_padding['R'];
		if ($reseth) {
			// reset row height
			$this->resetLastH();
		}
		$lines = 1;
		$sum = 0;
		$chars = LIMEPDF_FONT::utf8Bidi(LIMEPDF_FONT::UTF8StringToArray($txt, $this->isunicode, $this->CurrentFont), $txt, $this->tmprtl, $this->isunicode, $this->CurrentFont);
		$charsWidth = $this->GetArrStringWidth($chars, '', '', 0, true);
		$length = count($chars);
		$lastSeparator = -1;
		for ($i = 0; $i < $length; ++$i) {
			$c = $chars[$i];
			$charWidth = $charsWidth[$i];
			if (($c != 160)
					AND (($c == 173)
						OR preg_match($this->re_spaces, LIMEPDF_FONT::unichr($c, $this->isunicode))
						OR (($c == 45)
							AND ($i > 0) AND ($i < ($length - 1))
							AND @preg_match('/[\p{L}]/'.$this->re_space['m'], LIMEPDF_FONT::unichr($chars[($i - 1)], $this->isunicode))
							AND @preg_match('/[\p{L}]/'.$this->re_space['m'], LIMEPDF_FONT::unichr($chars[($i + 1)], $this->isunicode))
						)
					)
				) {
				$lastSeparator = $i;
			}
			if ((($sum + $charWidth) > $wmax) OR ($c == 10)) {
				++$lines;
				if ($c == 10) {
					$lastSeparator = -1;
					$sum = 0;
				} elseif ($lastSeparator != -1) {
					$i = $lastSeparator;
					$lastSeparator = -1;
					$sum = 0;
				} else {
					$sum = $charWidth;
				}
			} else {
				$sum += $charWidth;
			}
		}
		if ($chars[($length - 1)] == 10) {
			--$lines;
		}
		$this->cell_padding = $prev_cell_padding;
		$this->lasth = $prev_lasth;
		return $lines;
	}

	/**
	 * This method return the estimated height needed for printing a simple text string using the Multicell() method.
	 * Generally, if you want to know the exact height for a block of content you can use the following alternative technique:
	 * @pre
	 *  // store current object
	 *  $pdf->startTransaction();
	 *  // store starting values
	 *  $start_y = $pdf->GetY();
	 *  $start_page = $pdf->getPage();
	 *  // call your printing functions with your parameters
	 *  // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 *  $pdf->MultiCell($w=0, $h=0, $txt, $border=1, $align='L', $fill=false, $ln=1, $x=null, $y=null, $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0);
	 *  // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 *  // get the new Y
	 *  $end_y = $pdf->GetY();
	 *  $end_page = $pdf->getPage();
	 *  // calculate height
	 *  $height = 0;
	 *  if ($end_page == $start_page) {
	 *  	$height = $end_y - $start_y;
	 *  } else {
	 *  	for ($page=$start_page; $page <= $end_page; ++$page) {
	 *  		$this->setPage($page);
	 *  		if ($page == $start_page) {
	 *  			// first page
	 *  			$height += $this->h - $start_y - $this->bMargin;
	 *  		} elseif ($page == $end_page) {
	 *  			// last page
	 *  			$height += $end_y - $this->tMargin;
	 *  		} else {
	 *  			$height += $this->h - $this->tMargin - $this->bMargin;
	 *  		}
	 *  	}
	 *  }
	 *  // restore previous object
	 *  $pdf = $pdf->rollbackTransaction();
	 *
	 * @param float $w Width of cells. If 0, they extend up to the right margin of the page.
	 * @param string $txt String for calculating his height
	 * @param boolean $reseth if true reset the last cell height (default false).
	 * @param boolean $autopadding if true, uses internal padding and automatically adjust it to account for line width (default true).
	 * @param array|null $cellpadding Internal cell padding, if empty uses default cell padding.
	 * @param mixed $border Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @return float Return the minimal height needed for multicell method for printing the $txt param.
	 * @author Nicola Asuni, Alexander Escalona Fern\E1ndez
	 * @public
	 */
	public function getStringHeight($w, $txt, $reseth=false, $autopadding=true, $cellpadding=null, $border=0) {
		// adjust internal padding
		$prev_cell_padding = $this->cell_padding;
		$prev_lasth = $this->lasth;
		if (is_array($cellpadding)) {
			$this->cell_padding = $cellpadding;
		}
		$this->adjustCellPadding($border);
		$lines = $this->getNumLines($txt, $w, $reseth, $autopadding, $cellpadding, $border);
		$height = $this->getCellHeight(($lines * $this->FontSize), $autopadding);
		$this->cell_padding = $prev_cell_padding;
		$this->lasth = $prev_lasth;
		return $height;
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
			switch ($align) {
				case 'J':
				case 'C': {
					break;
				}
				case 'L': {
					if (!$this->rtl) {
						$w = $l;
					}
					break;
				}
				case 'R': {
					if ($this->rtl) {
						$w = $l;
					}
					break;
				}
				default: {
					$w = $l;
					break;
				}
			}
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
	 * Returns the remaining width between the current position and margins.
	 * @return float Return the remaining width
	 * @protected
	 */
	protected function getRemainingWidth() {
		list($this->x, $this->y) = $this->checkPageRegions(0, $this->x, $this->y);
		if ($this->rtl) {
			return ($this->x - $this->lMargin);
		} else {
			return ($this->w - $this->rMargin - $this->x);
		}
	}

	/**
	 * Set the block dimensions accounting for page breaks and page/column fitting
	 * @param float $w width
	 * @param float $h height
	 * @param float $x X coordinate
	 * @param float $y Y coodiante
	 * @param boolean $fitonpage if true the block is resized to not exceed page dimensions.
	 * @return array array($w, $h, $x, $y)
	 * @protected
	 * @since 5.5.009 (2010-07-05)
	 */
	protected function fitBlock($w, $h, $x, $y, $fitonpage=false) {
		if ($w <= 0) {
			// set maximum width
			$w = ($this->w - $this->lMargin - $this->rMargin);
			if ($w <= 0) {
				$w = 1;
			}
		}
		if ($h <= 0) {
			// set maximum height
			$h = ($this->PageBreakTrigger - $this->tMargin);
			if ($h <= 0) {
				$h = 1;
			}
		}
		// resize the block to be vertically contained on a single page or single column
		if ($fitonpage OR $this->AutoPageBreak) {
			$ratio_wh = ($w / $h);
			if ($h > ($this->PageBreakTrigger - $this->tMargin)) {
				$h = $this->PageBreakTrigger - $this->tMargin;
				$w = ($h * $ratio_wh);
			}
			// resize the block to be horizontally contained on a single page or single column
			if ($fitonpage) {
				$maxw = ($this->w - $this->lMargin - $this->rMargin);
				if ($w > $maxw) {
					$w = $maxw;
					$h = ($w / $ratio_wh);
				}
			}
		}
		// Check whether we need a new page or new column first as this does not fit
		$prev_x = $this->x;
		$prev_y = $this->y;
		if ($this->checkPageBreak($h, $y) OR ($this->y < $prev_y)) {
			$y = $this->y;
			if ($this->rtl) {
				$x += ($prev_x - $this->x);
			} else {
				$x += ($this->x - $prev_x);
			}
			$this->newline = true;
		}
		// resize the block to be contained on the remaining available page or column space
		if ($fitonpage) {
			// fallback to avoid division by zero
			$h = $h == 0 ? 1 : $h;
			$ratio_wh = ($w / $h);
			if (($y + $h) > $this->PageBreakTrigger) {
				$h = $this->PageBreakTrigger - $y;
				$w = ($h * $ratio_wh);
			}
			if ((!$this->rtl) AND (($x + $w) > ($this->w - $this->rMargin))) {
				$w = $this->w - $this->rMargin - $x;
				$h = ($w / $ratio_wh);
			} elseif (($this->rtl) AND (($x - $w) < ($this->lMargin))) {
				$w = $x - $this->lMargin;
				$h = ($w / $ratio_wh);
			}
		}
		return array($w, $h, $x, $y);
	}

	/**
	 * Puts an image in the page.
	 * The upper-left corner must be given.
	 * The dimensions can be specified in different ways:<ul>
	 * <li>explicit width and height (expressed in user unit)</li>
	 * <li>one explicit dimension, the other being calculated automatically in order to keep the original proportions</li>
	 * <li>no explicit dimension, in which case the image is put at 72 dpi</li></ul>
	 * Supported formats are JPEG and PNG images whitout GD library and all images supported by GD: GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM;
	 * The format can be specified explicitly or inferred from the file extension.<br />
	 * It is possible to put a link on the image.<br />
	 * Remark: if an image is used several times, only one copy will be embedded in the file.<br />
	 * @param string $file Name of the file containing the image or a '@' character followed by the image data string. To link an image without embedding it on the document, set an asterisk character before the URL (i.e.: '*http://www.example.com/image.jpg').
	 * @param float|null $x Abscissa of the upper-left corner (LTR) or upper-right corner (RTL).
	 * @param float|null $y Ordinate of the upper-left corner (LTR) or upper-right corner (RTL).
	 * @param float $w Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param float $h Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param string $type Image format. Possible values are (case insensitive): JPEG and PNG (whitout GD library) and all images supported by GD: GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM;. If not specified, the type is inferred from the file extension.
	 * @param mixed $link URL or identifier returned by AddLink().
	 * @param string $align Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param mixed $resize If true resize (reduce) the image to fit $w and $h (requires GD or ImageMagick library); if false do not resize; if 2 force resize in all cases (upscaling and downscaling).
	 * @param int $dpi dot-per-inch resolution used on resize
	 * @param string $palign Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param boolean $ismask true if this image is a mask, false otherwise
	 * @param mixed $imgmask image object returned by this function or false
	 * @param mixed $border Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param mixed $fitbox If not false scale image dimensions proportionally to fit within the ($w, $h) box. $fitbox can be true or a 2 characters string indicating the image alignment inside the box. The first character indicate the horizontal alignment (L = left, C = center, R = right) the second character indicate the vertical algnment (T = top, M = middle, B = bottom).
	 * @param boolean $hidden If true do not display the image.
	 * @param boolean $fitonpage If true the image is resized to not exceed page dimensions.
	 * @param boolean $alt If true the image will be added as alternative and not directly printed (the ID of the image will be returned).
	 * @param array $altimgs Array of alternate images IDs. Each alternative image must be an array with two values: an integer representing the image ID (the value returned by the Image method) and a boolean value to indicate if the image is the default for printing.
	 * @return mixed|false image information
	 * @public
	 * @since 1.1
	 */
	public function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false, $alt=false, $altimgs=array()) {
		if ($this->state != 2) {
			return false;
		}
		if (LIMEPDF_STATIC::empty_string($x)) {
			$x = $this->x;
		}
		if (LIMEPDF_STATIC::empty_string($y)) {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		$exurl = ''; // external streams
		$imsize = FALSE;

        // Make sure the file variable is not empty or null because accessing $file[0] later
        // results in error when running PHP 7.4
        if (empty($file)) {
            return false;
        }
		// check if we are passing an image as file or string
		if ($file[0] === '@') {
			// image from string
			$imgdata = substr($file, 1);
		} else { // image file
			if ($file[0] === '*') {
				// image as external stream
				$file = substr($file, 1);
				$exurl = $file;
			}
			// check if file exist and it is valid
			if (!@$this->fileExists($file)) {
				return false;
			}
            if (false !== $info = $this->getImageBuffer($file)) {
                $imsize = array($info['w'], $info['h']);
            } elseif (($imsize = @getimagesize($file)) === FALSE && strpos($file, '__tcpdf_'.$this->file_id.'_img') === FALSE){
                $imgdata = $this->getCachedFileContents($file);
            }
		}
		if (!empty($imgdata)) {
			// copy image to cache
			$original_file = $file;
			$file = LIMEPDF_STATIC::getObjFilename('img', $this->file_id);
			$fp = LIMEPDF_STATIC::fopenLocal($file, 'w');
			if (!$fp) {
				$this->Error('Unable to write file: '.$file);
			}
			fwrite($fp, $imgdata);
			fclose($fp);
			unset($imgdata);
			$imsize = @getimagesize($file);
			if ($imsize === FALSE) {
				$this->_unlink($file);
				$file = $original_file;
			}
		}
		if ($imsize === FALSE) {
			if (($w > 0) AND ($h > 0)) {
				// get measures from specified data
				$pw = $this->getHTMLUnitToUnits($w, 0, $this->pdfunit, true) * $this->imgscale * $this->k;
				$ph = $this->getHTMLUnitToUnits($h, 0, $this->pdfunit, true) * $this->imgscale * $this->k;
				$imsize = array($pw, $ph);
			} else {
				$this->Error('[Image] Unable to get the size of the image: '.$file);
			}
		}
		// file hash
		$filehash = md5($file);
		// get original image width and height in pixels
		list($pixw, $pixh) = $imsize;
		// calculate image width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			// convert image size to document unit
			$w = $this->pixelsToUnits($pixw);
			$h = $this->pixelsToUnits($pixh);
		} elseif ($w <= 0) {
			$w = $h * $pixw / $pixh;
		} elseif ($h <= 0) {
			$h = $w * $pixh / $pixw;
		} elseif (($fitbox !== false) AND ($w > 0) AND ($h > 0)) {
			if (strlen($fitbox) !== 2) {
				// set default alignment
				$fitbox = '--';
			}
			// scale image dimensions proportionally to fit within the ($w, $h) box
			if ((($w * $pixh) / ($h * $pixw)) < 1) {
				// store current height
				$oldh = $h;
				// calculate new height
				$h = $w * $pixh / $pixw;
				// height difference
				$hdiff = ($oldh - $h);
				// vertical alignment
				switch (strtoupper($fitbox[1])) {
					case 'T': {
						break;
					}
					case 'M': {
						$y += ($hdiff / 2);
						break;
					}
					case 'B': {
						$y += $hdiff;
						break;
					}
				}
			} else {
				// store current width
				$oldw = $w;
				// calculate new width
				$w = $h * $pixw / $pixh;
				// width difference
				$wdiff = ($oldw - $w);
				// horizontal alignment
				switch (strtoupper($fitbox[0])) {
					case 'L': {
						if ($this->rtl) {
							$x -= $wdiff;
						}
						break;
					}
					case 'C': {
						if ($this->rtl) {
							$x -= ($wdiff / 2);
						} else {
							$x += ($wdiff / 2);
						}
						break;
					}
					case 'R': {
						if (!$this->rtl) {
							$x += $wdiff;
						}
						break;
					}
				}
			}
		}
		// fit the image on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		// calculate new minimum dimensions in pixels
		$neww = round($w * $this->k * $dpi / $this->dpi);
		$newh = round($h * $this->k * $dpi / $this->dpi);
		// check if resize is necessary (resize is used only to reduce the image)
		$newsize = ($neww * $newh);
		$pixsize = ($pixw * $pixh);
		if (intval($resize) == 2) {
			$resize = true;
		} elseif ($newsize >= $pixsize) {
			$resize = false;
		}
		// check if image has been already added on document
		$newimage = true;
		if (in_array($file, $this->imagekeys)) {
			$newimage = false;
			// get existing image data
			$info = $this->getImageBuffer($file);
			if (strpos($file, '__tcpdf_'.$this->file_id.'_imgmask_') === FALSE) {
				// check if the newer image is larger
				$oldsize = ($info['w'] * $info['h']);
				if ((($oldsize < $newsize) AND ($resize)) OR (($oldsize < $pixsize) AND (!$resize))) {
					$newimage = true;
				}
			}
		} elseif (($ismask === false) AND ($imgmask === false) AND (strpos($file, '__tcpdf_'.$this->file_id.'_imgmask_') === FALSE)) {
			// create temp image file (without alpha channel)
			$tempfile_plain = K_PATH_CACHE.'__tcpdf_'.$this->file_id.'_imgmask_plain_'.$filehash;
			// create temp alpha file
			$tempfile_alpha = K_PATH_CACHE.'__tcpdf_'.$this->file_id.'_imgmask_alpha_'.$filehash;
			// check for cached images
			if (in_array($tempfile_plain, $this->imagekeys)) {
				// get existing image data
				$info = $this->getImageBuffer($tempfile_plain);
				// check if the newer image is larger
				$oldsize = ($info['w'] * $info['h']);
				if ((($oldsize < $newsize) AND ($resize)) OR (($oldsize < $pixsize) AND (!$resize))) {
					$newimage = true;
				} else {
					$newimage = false;
					// embed mask image
					$imgmask = $this->Image($tempfile_alpha, $x, $y, $w, $h, 'PNG', '', '', $resize, $dpi, '', true, false);
					// embed image, masked with previously embedded mask
					return $this->Image($tempfile_plain, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign, false, $imgmask);
				}
			}
		}
		if ($newimage) {
			//First use of image, get info
			$type = strtolower($type);
			if ($type == '') {
				$type = LIMEPDF_IMAGES::getImageFileType($file, $imsize);
			} elseif ($type == 'jpg') {
				$type = 'jpeg';
			}
			// Specific image handlers (defined on LIMEPDF_IMAGES CLASS)
			$mtd = '_parse'.$type;
			// GD image handler function
			$gdfunction = 'imagecreatefrom'.$type;
			$info = false;
			if ((method_exists('LIMEPDF_IMAGES', $mtd)) AND (!($resize AND (function_exists($gdfunction) OR extension_loaded('imagick'))))) {
				// TCPDF image functions
				$info = LIMEPDF_IMAGES::$mtd($file);
				if (($ismask === false) AND ($imgmask === false) AND (strpos($file, '__tcpdf_'.$this->file_id.'_imgmask_') === FALSE)
					AND (($info === 'pngalpha') OR (isset($info['trns']) AND !empty($info['trns'])))) {
					return $this->ImagePngAlpha($file, $x, $y, $pixw, $pixh, $w, $h, 'PNG', $link, $align, $resize, $dpi, $palign, $filehash);
				}
			}
			if (($info === false) AND function_exists($gdfunction)) {
				try {
					// GD library
					$img = $gdfunction($file);
					if ($img !== false) {
						if ($resize) {
							$imgr = imagecreatetruecolor($neww, $newh);
							if (($type == 'gif') OR ($type == 'png')) {
								$imgr = LIMEPDF_IMAGES::setGDImageTransparency($imgr, $img);
							}
							imagecopyresampled($imgr, $img, 0, 0, 0, 0, $neww, $newh, $pixw, $pixh);
							$img = $imgr;
						}
						if (($type == 'gif') OR ($type == 'png')) {
							$info = LIMEPDF_IMAGES::_toPNG($img, LIMEPDF_STATIC::getObjFilename('img', $this->file_id));
						} else {
							$info = LIMEPDF_IMAGES::_toJPEG($img, $this->jpeg_quality, LIMEPDF_STATIC::getObjFilename('img', $this->file_id));
						}
					}
				} catch(Exception $e) {
					$info = false;
				}
			}
			if (($info === false) AND extension_loaded('imagick')) {
				try {
					// ImageMagick library
					$img = new Imagick();
					if ($type == 'svg') {
						if ($file[0] === '@') {
							// image from string
							$svgimg = substr($file, 1);
						} else {
							// get SVG file content
                            $svgimg = $this->getCachedFileContents($file);
						}
						if ($svgimg !== FALSE) {
							// get width and height
							$regs = array();
							if (preg_match('/<svg([^\>]*)>/si', $svgimg, $regs)) {
								$svgtag = $regs[1];
								$tmp = array();
								if (preg_match('/[\s]+width[\s]*=[\s]*"([^"]*)"/si', $svgtag, $tmp)) {
									$ow = $this->getHTMLUnitToUnits($tmp[1], 1, $this->svgunit, false);
									$owu = sprintf('%F', ($ow * $dpi / 72)).$this->pdfunit;
									$svgtag = preg_replace('/[\s]+width[\s]*=[\s]*"[^"]*"/si', ' width="'.$owu.'"', $svgtag, 1);
								} else {
									$ow = $w;
								}
								$tmp = array();
								if (preg_match('/[\s]+height[\s]*=[\s]*"([^"]*)"/si', $svgtag, $tmp)) {
									$oh = $this->getHTMLUnitToUnits($tmp[1], 1, $this->svgunit, false);
									$ohu = sprintf('%F', ($oh * $dpi / 72)).$this->pdfunit;
									$svgtag = preg_replace('/[\s]+height[\s]*=[\s]*"[^"]*"/si', ' height="'.$ohu.'"', $svgtag, 1);
								} else {
									$oh = $h;
								}
								$tmp = array();
								if (!preg_match('/[\s]+viewBox[\s]*=[\s]*"[\s]*([0-9\.]+)[\s]+([0-9\.]+)[\s]+([0-9\.]+)[\s]+([0-9\.]+)[\s]*"/si', $svgtag, $tmp)) {
									$vbw = ($ow * $this->imgscale * $this->k);
									$vbh = ($oh * $this->imgscale * $this->k);
									$vbox = sprintf(' viewBox="0 0 %F %F" ', $vbw, $vbh);
									$svgtag = $vbox.$svgtag;
								}
								$svgimg = preg_replace('/<svg([^\>]*)>/si', '<svg'.$svgtag.'>', $svgimg, 1);
							}
							$img->readImageBlob($svgimg);
						}
					} else {
						$img->readImage($file);
					}
					if ($resize) {
						$img->resizeImage($neww, $newh, 10, 1, false);
					}
					$img->setCompressionQuality($this->jpeg_quality);
					$img->setImageFormat('jpeg');
					$tempname = LIMEPDF_STATIC::getObjFilename('img', $this->file_id);
					$img->writeImage($tempname);
					$info = LIMEPDF_IMAGES::_parsejpeg($tempname);
					$this->_unlink($tempname);
					$img->destroy();
				} catch(Exception $e) {
					$info = false;
				}
			}
			if ($info === false) {
				// unable to process image
				return false;
			}
			if ($ismask) {
				// force grayscale
				$info['cs'] = 'DeviceGray';
			}
			if ($imgmask !== false) {
				$info['masked'] = $imgmask;
			}
			if (!empty($exurl)) {
				$info['exurl'] = $exurl;
			}
			// array of alternative images
			$info['altimgs'] = $altimgs;
			// add image to document
			$info['i'] = $this->setImageBuffer($file, $info);
		}
		// set alignment
		$this->img_rb_x = $x + $w;
		$this->img_rb_y = $y + $h;

		// set alignment
		if ($palign == 'L') {
			$ximg = $this->lMargin;
		} elseif ($palign == 'C') {
			$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
		} elseif ($palign == 'R') {
			$ximg = $this->w - $this->rMargin - $w;
		} else {
			$ximg = $this->rtl ? $x - $w : $x;
		}

		if ($ismask OR $hidden) {
			// image is not displayed
			return $info['i'];
		}
		$xkimg = $ximg * $this->k;
		if (!$alt) {
			// only non-alternative immages will be set
			$this->_out(sprintf('q %F 0 0 %F %F %F cm /I%u Do Q', ($w * $this->k), ($h * $this->k), $xkimg, (($this->h - ($y + $h)) * $this->k), $info['i']));
		}
		if (!empty($border)) {
			$bx = $this->x;
			$by = $this->y;
			$this->x = $ximg;
			if ($this->rtl) {
				$this->x += $w;
			}
			$this->y = $y;
			$this->Cell($w, $h, '', $border, 0, '', 0, '', 0, true);
			$this->x = $bx;
			$this->y = $by;
		}
		if ($link) {
			$this->Link($ximg, $y, $w, $h, $link, 0);
		}
		// set pointer to align the next text/objects
		switch($align) {
			case 'T': {
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M': {
				$this->y = $y + round($h/2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B': {
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N': {
				$this->setY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['images'][] = $info['i'];
		}
		return $info['i'];
	}

	/**
	 * Extract info from a PNG image with alpha channel using the Imagick or GD library.
	 * @param string $file Name of the file containing the image.
	 * @param float $x Abscissa of the upper-left corner.
	 * @param float $y Ordinate of the upper-left corner.
	 * @param float $wpx Original width of the image in pixels.
	 * @param float $hpx original height of the image in pixels.
	 * @param float $w Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param float $h Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param string $type Image format. Possible values are (case insensitive): JPEG and PNG (whitout GD library) and all images supported by GD: GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM;. If not specified, the type is inferred from the file extension.
	 * @param mixed $link URL or identifier returned by AddLink().
	 * @param string $align Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param boolean $resize If true resize (reduce) the image to fit $w and $h (requires GD library).
	 * @param int $dpi dot-per-inch resolution used on resize
	 * @param string $palign Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param string $filehash File hash used to build unique file names.
	 * @author Nicola Asuni
	 * @protected
	 * @since 4.3.007 (2008-12-04)
	 * @see Image()
	 */
	protected function ImagePngAlpha($file, $x, $y, $wpx, $hpx, $w, $h, $type, $link, $align, $resize, $dpi, $palign, $filehash='') {
		// create temp images
		if (empty($filehash)) {
			$filehash = md5($file);
		}
		// create temp image file (without alpha channel)
		$tempfile_plain = K_PATH_CACHE.'__tcpdf_'.$this->file_id.'_imgmask_plain_'.$filehash;
		// create temp alpha file
		$tempfile_alpha = K_PATH_CACHE.'__tcpdf_'.$this->file_id.'_imgmask_alpha_'.$filehash;
		$parsed = false;
		$parse_error = '';
		// ImageMagick extension
		if (($parsed === false) AND extension_loaded('imagick')) {
			try {
				// ImageMagick library
				$img = new Imagick();
				$img->readImage($file);
				// clone image object
				$imga = LIMEPDF_STATIC::objclone($img);
				// extract alpha channel
				if (method_exists($img, 'setImageAlphaChannel') AND defined('Imagick::ALPHACHANNEL_EXTRACT')) {
					$img->setImageAlphaChannel(Imagick::ALPHACHANNEL_EXTRACT);
				} else {
					$img->separateImageChannel(8); // 8 = (imagick::CHANNEL_ALPHA | imagick::CHANNEL_OPACITY | imagick::CHANNEL_MATTE);
					$img->negateImage(true);
				}
				$img->setImageFormat('png');
				$img->writeImage($tempfile_alpha);
				// remove alpha channel
				if (method_exists($imga, 'setImageMatte')) {
					$imga->setImageMatte(false);
				} else {
					$imga->separateImageChannel(39); // 39 = (imagick::CHANNEL_ALL & ~(imagick::CHANNEL_ALPHA | imagick::CHANNEL_OPACITY | imagick::CHANNEL_MATTE));
				}
				$imga->setImageFormat('png');
				$imga->writeImage($tempfile_plain);
				$parsed = true;
			} catch (Exception $e) {
				// Imagemagick fails, try with GD
				$parse_error = 'Imagick library error: '.$e->getMessage();
			}
		}
		// GD extension
		if (($parsed === false) AND function_exists('imagecreatefrompng')) {
			try {
				// generate images
				$img = imagecreatefrompng($file);
				$imgalpha = imagecreate($wpx, $hpx);
				// generate gray scale palette (0 -> 255)
				for ($c = 0; $c < 256; ++$c) {
					ImageColorAllocate($imgalpha, $c, $c, $c);
				}
				// extract alpha channel
				for ($xpx = 0; $xpx < $wpx; ++$xpx) {
					for ($ypx = 0; $ypx < $hpx; ++$ypx) {
						$color = imagecolorat($img, $xpx, $ypx);
						// get and correct gamma color
						$alpha = $this->getGDgamma($img, $color);
						imagesetpixel($imgalpha, (int) $xpx, (int) $ypx, (int) $alpha);
					}
				}
				imagepng($imgalpha, $tempfile_alpha);
				imagedestroy($imgalpha);
				// extract image without alpha channel
				$imgplain = imagecreatetruecolor($wpx, $hpx);
				imagecopy($imgplain, $img, 0, 0, 0, 0, $wpx, $hpx);
				imagepng($imgplain, $tempfile_plain);
				imagedestroy($imgplain);
				$parsed = true;
			} catch (Exception $e) {
				// GD fails
				$parse_error = 'GD library error: '.$e->getMessage();
			}
		}
		if ($parsed === false) {
			if (empty($parse_error)) {
				$this->Error('TCPDF requires the Imagick or GD extension to handle PNG images with alpha channel.');
			} else {
				$this->Error($parse_error);
			}
		}
		// embed mask image
		$imgmask = $this->Image($tempfile_alpha, $x, $y, $w, $h, 'PNG', '', '', $resize, $dpi, '', true, false);
		// embed image, masked with previously embedded mask
		$this->Image($tempfile_plain, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign, false, $imgmask);
	}

	/**
	 * Get the GD-corrected PNG gamma value from alpha color
	 * @param resource $img GD image Resource ID.
	 * @param int $c alpha color
	 * @protected
	 * @since 4.3.007 (2008-12-04)
	 */
	protected function getGDgamma($img, $c) {
		if (!isset($this->gdgammacache['#'.$c])) {
			$colors = imagecolorsforindex($img, $c);
			// GD alpha is only 7 bit (0 -> 127)
			$this->gdgammacache['#'.$c] = (int) (((127 - $colors['alpha']) / 127) * 255);
			// correct gamma
			$this->gdgammacache['#'.$c] = (int) (pow(($this->gdgammacache['#'.$c] / 255), 2.2) * 255);
			// store the latest values on cache to improve performances
			if (count($this->gdgammacache) > 8) {
				// remove one element from the cache array
				array_shift($this->gdgammacache);
			}
		}
		return $this->gdgammacache['#'.$c];
	}

	/**
	 * Performs a line break.
	 * The current abscissa goes back to the left margin and the ordinate increases by the amount passed in parameter.
	 * @param float|null $h The height of the break. By default, the value equals the height of the last printed cell.
	 * @param boolean $cell if true add the current left (or right o for RTL) padding to the X coordinate
	 * @public
	 * @since 1.0
	 * @see Cell()
	 */
	public function Ln($h=null, $cell=false) {
		if (($this->num_columns > 1) AND ($this->y == $this->columns[$this->current_column]['y']) AND isset($this->columns[$this->current_column]['x']) AND ($this->x == $this->columns[$this->current_column]['x'])) {
			// revove vertical space from the top of the column
			return;
		}
		if ($cell) {
			if ($this->rtl) {
				$cellpadding = $this->cell_padding['R'];
			} else {
				$cellpadding = $this->cell_padding['L'];
			}
		} else {
			$cellpadding = 0;
		}
		if ($this->rtl) {
			$this->x = $this->w - $this->rMargin - $cellpadding;
		} else {
			$this->x = $this->lMargin + $cellpadding;
		}
		if (LIMEPDF_STATIC::empty_string($h)) {
			$h = $this->lasth;
		}
		$this->y += $h;
		$this->newline = true;
	}

	/**
	 * Returns the relative X value of current position.
	 * The value is relative to the left border for LTR languages and to the right border for RTL languages.
	 * @return float
	 * @public
	 * @since 1.2
	 * @see SetX(), GetY(), SetY()
	 */
	public function GetX() {
		//Get x position
		if ($this->rtl) {
			return ($this->w - $this->x);
		} else {
			return $this->x;
		}
	}

	/**
	 * Returns the absolute X value of current position.
	 * @return float
	 * @public
	 * @since 1.2
	 * @see SetX(), GetY(), SetY()
	 */
	public function GetAbsX() {
		return $this->x;
	}

	/**
	 * Returns the ordinate of the current position.
	 * @return float
	 * @public
	 * @since 1.0
	 * @see SetY(), GetX(), SetX()
	 */
	public function GetY() {
		return $this->y;
	}

	/**
	 * Defines the abscissa of the current position.
	 * If the passed value is negative, it is relative to the right of the page (or left if language is RTL).
	 * @param float $x The value of the abscissa in user units.
	 * @param boolean $rtloff if true always uses the page top-left corner as origin of axis.
	 * @public
	 * @since 1.2
	 * @see GetX(), GetY(), SetY(), SetXY()
	 */
	public function setX($x, $rtloff=false) {
		$x = floatval($x);
		if (!$rtloff AND $this->rtl) {
			if ($x >= 0) {
				$this->x = $this->w - $x;
			} else {
				$this->x = abs($x);
			}
		} else {
			if ($x >= 0) {
				$this->x = $x;
			} else {
				$this->x = $this->w + $x;
			}
		}
		if ($this->x < 0) {
			$this->x = 0;
		}
		if ($this->x > $this->w) {
			$this->x = $this->w;
		}
	}

	/**
	 * Moves the current abscissa back to the left margin and sets the ordinate.
	 * If the passed value is negative, it is relative to the bottom of the page.
	 * @param float $y The value of the ordinate in user units.
	 * @param bool $resetx if true (default) reset the X position.
	 * @param boolean $rtloff if true always uses the page top-left corner as origin of axis.
	 * @public
	 * @since 1.0
	 * @see GetX(), GetY(), SetY(), SetXY()
	 */
	public function setY($y, $resetx=true, $rtloff=false) {
		$y = floatval($y);
		if ($resetx) {
			//reset x
			if (!$rtloff AND $this->rtl) {
				$this->x = $this->w - $this->rMargin;
			} else {
				$this->x = $this->lMargin;
			}
		}
		if ($y >= 0) {
			$this->y = $y;
		} else {
			$this->y = $this->h + $y;
		}
		if ($this->y < 0) {
			$this->y = 0;
		}
		if ($this->y > $this->h) {
			$this->y = $this->h;
		}
	}

	/**
	 * Defines the abscissa and ordinate of the current position.
	 * If the passed values are negative, they are relative respectively to the right and bottom of the page.
	 * @param float $x The value of the abscissa.
	 * @param float $y The value of the ordinate.
	 * @param boolean $rtloff if true always uses the page top-left corner as origin of axis.
	 * @public
	 * @since 1.2
	 * @see SetX(), SetY()
	 */
	public function setXY($x, $y, $rtloff=false) {
		$this->setY($y, false, $rtloff);
		$this->setX($x, $rtloff);
	}

	/**
	 * Set the absolute X coordinate of the current pointer.
	 * @param float $x The value of the abscissa in user units.
	 * @public
	 * @since 5.9.186 (2012-09-13)
	 * @see setAbsX(), setAbsY(), SetAbsXY()
	 */
	public function setAbsX($x) {
		$this->x = floatval($x);
	}

	/**
	 * Set the absolute Y coordinate of the current pointer.
	 * @param float $y (float) The value of the ordinate in user units.
	 * @public
	 * @since 5.9.186 (2012-09-13)
	 * @see setAbsX(), setAbsY(), SetAbsXY()
	 */
	public function setAbsY($y) {
		$this->y = floatval($y);
	}

	/**
	 * Set the absolute X and Y coordinates of the current pointer.
	 * @param float $x The value of the abscissa in user units.
	 * @param float $y (float) The value of the ordinate in user units.
	 * @public
	 * @since 5.9.186 (2012-09-13)
	 * @see setAbsX(), setAbsY(), SetAbsXY()
	 */
	public function setAbsXY($x, $y) {
		$this->setAbsX($x);
		$this->setAbsY($y);
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
	 * Return an array containing variations for the basic page number alias.
	 * @param string $a Base alias.
	 * @return array of page number aliases
	 * @protected
	 */
	protected function getInternalPageNumberAliases($a= '') {
		$alias = array();
		// build array of Unicode + ASCII variants (the order is important)
		$alias = array('u' => array(), 'a' => array());
		$u = '{'.$a.'}';
		$alias['u'][] = LIMEPDF_STATIC::_escape($u);
		if ($this->isunicode) {
			$alias['u'][] = LIMEPDF_STATIC::_escape(LIMEPDF_FONT::UTF8ToLatin1($u, $this->isunicode, $this->CurrentFont));
			$alias['u'][] = LIMEPDF_STATIC::_escape(LIMEPDF_FONT::utf8StrRev($u, false, $this->tmprtl, $this->isunicode, $this->CurrentFont));
			$alias['a'][] = LIMEPDF_STATIC::_escape(LIMEPDF_FONT::UTF8ToLatin1($a, $this->isunicode, $this->CurrentFont));
			$alias['a'][] = LIMEPDF_STATIC::_escape(LIMEPDF_FONT::utf8StrRev($a, false, $this->tmprtl, $this->isunicode, $this->CurrentFont));
		}
		$alias['a'][] = LIMEPDF_STATIC::_escape($a);
		return $alias;
	}

	/**
	 * Return an array containing all internal page aliases.
	 * @return array of page number aliases
	 * @protected
	 */
	protected function getAllInternalPageNumberAliases() {
		$basic_alias = array(LIMEPDF_STATIC::$alias_tot_pages, LIMEPDF_STATIC::$alias_num_page, LIMEPDF_STATIC::$alias_group_tot_pages, LIMEPDF_STATIC::$alias_group_num_page, LIMEPDF_STATIC::$alias_right_shift);
		$pnalias = array();
		foreach($basic_alias as $k => $a) {
			$pnalias[$k] = $this->getInternalPageNumberAliases($a);
		}
		return $pnalias;
	}

	/**
	 * Replace right shift page number aliases with spaces to correct right alignment.
	 * This works perfectly only when using monospaced fonts.
	 * @param string $page Page content.
	 * @param array $aliases Array of page aliases.
	 * @param int $diff initial difference to add.
	 * @return string replaced page content.
	 * @protected
	 */
	protected function replaceRightShiftPageNumAliases($page, $aliases, $diff) {
		foreach ($aliases as $type => $alias) {
			foreach ($alias as $a) {
				// find position of compensation factor
				$startnum = (strpos($a, ':') + 1);
				$a = substr($a, 0, $startnum);
				if (($pos = strpos($page, $a)) !== false) {
					// end of alias
					$endnum = strpos($page, '}', $pos);
					// string to be replaced
					$aa = substr($page, $pos, ($endnum - $pos + 1));
					// get compensation factor
					$ratio = substr($page, ($pos + $startnum), ($endnum - $pos - $startnum));
					$ratio = preg_replace('/[^0-9\.]/', '', $ratio);
					$ratio = floatval($ratio);
					if ($type == 'u') {
						$chrdiff = floor(($diff + 12) * $ratio);
						$shift = str_repeat(' ', $chrdiff);
						$shift = LIMEPDF_FONT::UTF8ToUTF16BE($shift, false, $this->isunicode, $this->CurrentFont);
					} else {
						$chrdiff = floor(($diff + 11) * $ratio);
						$shift = str_repeat(' ', $chrdiff);
					}
					$page = str_replace($aa, $shift, $page);
				}
			}
		}
		return $page;
	}

	/**
	 * Set page boxes to be included on page descriptions.
	 * @param array $boxes Array of page boxes to set on document: ('MediaBox', 'CropBox', 'BleedBox', 'TrimBox', 'ArtBox').
	 * @protected
	 */
	protected function setPageBoxTypes($boxes) {
		$this->page_boxes = array();
		foreach ($boxes as $box) {
			if (in_array($box, LIMEPDF_STATIC::$pageboxes)) {
				$this->page_boxes[] = $box;
			}
		}
	}

	/**
	 * Output pages (and replace page number aliases).
	 * @protected
	 */
	protected function _putpages() {
		$filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
		// get internal aliases for page numbers
		$pnalias = $this->getAllInternalPageNumberAliases();
		$num_pages = $this->numpages;
		$ptpa = LIMEPDF_STATIC::formatPageNumber(($this->starting_page_number + $num_pages - 1));
		$ptpu = LIMEPDF_FONT::UTF8ToUTF16BE($ptpa, false, $this->isunicode, $this->CurrentFont);
		$ptp_num_chars = $this->GetNumChars($ptpa);
		$pagegroupnum = 0;
		$groupnum = 0;
		$ptgu = 1;
		$ptga = 1;
		$ptg_num_chars = 1;
		for ($n = 1; $n <= $num_pages; ++$n) {
			// get current page
			$temppage = $this->getPageBuffer($n);
			$pagelen = strlen($temppage);
			// set replacements for total pages number
			$pnpa = LIMEPDF_STATIC::formatPageNumber(($this->starting_page_number + $n - 1));
			$pnpu = LIMEPDF_FONT::UTF8ToUTF16BE($pnpa, false, $this->isunicode, $this->CurrentFont);
			$pnp_num_chars = $this->GetNumChars($pnpa);
			$pdiff = 0; // difference used for right shift alignment of page numbers
			$gdiff = 0; // difference used for right shift alignment of page group numbers
			if (!empty($this->pagegroups)) {
				if (isset($this->newpagegroup[$n])) {
					$pagegroupnum = 0;
					++$groupnum;
					$ptga = LIMEPDF_STATIC::formatPageNumber($this->pagegroups[$groupnum]);
					$ptgu = LIMEPDF_FONT::UTF8ToUTF16BE($ptga, false, $this->isunicode, $this->CurrentFont);
					$ptg_num_chars = $this->GetNumChars($ptga);
				}
				++$pagegroupnum;
				$pnga = LIMEPDF_STATIC::formatPageNumber($pagegroupnum);
				$pngu = LIMEPDF_FONT::UTF8ToUTF16BE($pnga, false, $this->isunicode, $this->CurrentFont);
				$png_num_chars = $this->GetNumChars($pnga);
				// replace page numbers
				$replace = array();
				$replace[] = array($ptgu, $ptg_num_chars, 9, $pnalias[2]['u']);
				$replace[] = array($ptga, $ptg_num_chars, 7, $pnalias[2]['a']);
				$replace[] = array($pngu, $png_num_chars, 9, $pnalias[3]['u']);
				$replace[] = array($pnga, $png_num_chars, 7, $pnalias[3]['a']);
				list($temppage, $gdiff) = LIMEPDF_STATIC::replacePageNumAliases($temppage, $replace, $gdiff);
			}
			// replace page numbers
			$replace = array();
			$replace[] = array($ptpu, $ptp_num_chars, 9, $pnalias[0]['u']);
			$replace[] = array($ptpa, $ptp_num_chars, 7, $pnalias[0]['a']);
			$replace[] = array($pnpu, $pnp_num_chars, 9, $pnalias[1]['u']);
			$replace[] = array($pnpa, $pnp_num_chars, 7, $pnalias[1]['a']);
			list($temppage, $pdiff) = LIMEPDF_STATIC::replacePageNumAliases($temppage, $replace, $pdiff);
			// replace right shift alias
			$temppage = $this->replaceRightShiftPageNumAliases($temppage, $pnalias[4], max($pdiff, $gdiff));
			// replace EPS marker
			$temppage = str_replace($this->epsmarker, '', $temppage);
			//Page
			$this->page_obj_id[$n] = $this->_newobj();
			$out = '<<';
			$out .= ' /Type /Page';
			$out .= ' /Parent 1 0 R';
			if (empty($this->signature_data['approval']) OR ($this->signature_data['approval'] != 'A')) {
				$out .= ' /LastModified '.$this->_datestring(0, $this->doc_modification_timestamp);
			}
			$out .= ' /Resources 2 0 R';
			foreach ($this->page_boxes as $box) {
				$out .= ' /'.$box;
				$out .= sprintf(' [%F %F %F %F]', $this->pagedim[$n][$box]['llx'], $this->pagedim[$n][$box]['lly'], $this->pagedim[$n][$box]['urx'], $this->pagedim[$n][$box]['ury']);
			}
			if (isset($this->pagedim[$n]['BoxColorInfo']) AND !empty($this->pagedim[$n]['BoxColorInfo'])) {
				$out .= ' /BoxColorInfo <<';
				foreach ($this->page_boxes as $box) {
					if (isset($this->pagedim[$n]['BoxColorInfo'][$box])) {
						$out .= ' /'.$box.' <<';
						if (isset($this->pagedim[$n]['BoxColorInfo'][$box]['C'])) {
							$color = $this->pagedim[$n]['BoxColorInfo'][$box]['C'];
							$out .= ' /C [';
							$out .= sprintf(' %F %F %F', ($color[0] / 255), ($color[1] / 255), ($color[2] / 255));
							$out .= ' ]';
						}
						if (isset($this->pagedim[$n]['BoxColorInfo'][$box]['W'])) {
							$out .= ' /W '.($this->pagedim[$n]['BoxColorInfo'][$box]['W'] * $this->k);
						}
						if (isset($this->pagedim[$n]['BoxColorInfo'][$box]['S'])) {
							$out .= ' /S /'.$this->pagedim[$n]['BoxColorInfo'][$box]['S'];
						}
						if (isset($this->pagedim[$n]['BoxColorInfo'][$box]['D'])) {
							$dashes = $this->pagedim[$n]['BoxColorInfo'][$box]['D'];
							$out .= ' /D [';
							foreach ($dashes as $dash) {
								$out .= sprintf(' %F', ($dash * $this->k));
							}
							$out .= ' ]';
						}
						$out .= ' >>';
					}
				}
				$out .= ' >>';
			}
			$out .= ' /Contents '.($this->n + 1).' 0 R';
			$out .= ' /Rotate '.$this->pagedim[$n]['Rotate'];
			if (!$this->pdfa_mode || $this->pdfa_version >= 2) {
				$out .= ' /Group << /Type /Group /S /Transparency /CS /DeviceRGB >>';
			}
			if (isset($this->pagedim[$n]['trans']) AND !empty($this->pagedim[$n]['trans'])) {
				// page transitions
				if (isset($this->pagedim[$n]['trans']['Dur'])) {
					$out .= ' /Dur '.$this->pagedim[$n]['trans']['Dur'];
				}
				$out .= ' /Trans <<';
				$out .= ' /Type /Trans';
				if (isset($this->pagedim[$n]['trans']['S'])) {
					$out .= ' /S /'.$this->pagedim[$n]['trans']['S'];
				}
				if (isset($this->pagedim[$n]['trans']['D'])) {
					$out .= ' /D '.$this->pagedim[$n]['trans']['D'];
				}
				if (isset($this->pagedim[$n]['trans']['Dm'])) {
					$out .= ' /Dm /'.$this->pagedim[$n]['trans']['Dm'];
				}
				if (isset($this->pagedim[$n]['trans']['M'])) {
					$out .= ' /M /'.$this->pagedim[$n]['trans']['M'];
				}
				if (isset($this->pagedim[$n]['trans']['Di'])) {
					$out .= ' /Di '.$this->pagedim[$n]['trans']['Di'];
				}
				if (isset($this->pagedim[$n]['trans']['SS'])) {
					$out .= ' /SS '.$this->pagedim[$n]['trans']['SS'];
				}
				if (isset($this->pagedim[$n]['trans']['B'])) {
					$out .= ' /B '.$this->pagedim[$n]['trans']['B'];
				}
				$out .= ' >>';
			}
			$out .= $this->_getannotsrefs($n);
			$out .= ' /PZ '.$this->pagedim[$n]['PZ'];
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
			//Page content
			$p = ($this->compress) ? gzcompress($temppage) : $temppage;
			$this->_newobj();
			$p = $this->_getrawstream($p);
			$this->_out('<<'.$filter.'/Length '.strlen($p).'>> stream'."\n".$p."\n".'endstream'."\n".'endobj');
		}
		//Pages root
		$out = $this->_getobj(1)."\n";
		$out .= '<< /Type /Pages /Kids [';
		foreach($this->page_obj_id as $page_obj) {
			$out .= ' '.$page_obj.' 0 R';
		}
		$out .= ' ] /Count '.$num_pages.' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
	}

	/**
	 * Get references to page annotations.
	 * @param int $n page number
	 * @return string
	 * @protected
	 * @author Nicola Asuni
	 * @since 5.0.010 (2010-05-17)
	 */
	protected function _getannotsrefs($n) {
		if (!(isset($this->PageAnnots[$n]) OR count($this->empty_signature_appearance)>0 OR ($this->sign AND isset($this->signature_data['cert_type'])))) {
			return '';
		}
		$out = ' /Annots [';
		if (isset($this->PageAnnots[$n])) {
			foreach ($this->PageAnnots[$n] as $key => $val) {
				if (!in_array($val['n'], $this->radio_groups)) {
					$out .= ' '.$val['n'].' 0 R';
				}
			}
			// add radiobutton groups
			if (isset($this->radiobutton_groups[$n])) {
				foreach ($this->radiobutton_groups[$n] as $key => $data) {
					if (isset($data['n'])) {
						$out .= ' '.$data['n'].' 0 R';
					}
				}
			}
		}
		if ($this->sign AND ($n == $this->signature_appearance['page']) AND isset($this->signature_data['cert_type'])) {
			// set reference for signature object
			$out .= ' '.$this->sig_obj_id.' 0 R';
		}
		if (!empty($this->empty_signature_appearance)) {
			foreach ($this->empty_signature_appearance as $esa) {
				if ($esa['page'] == $n) {
					// set reference for empty signature objects
					$out .= ' '.$esa['objid'].' 0 R';
				}
			}
		}
		$out .= ' ]';
		return $out;
	}

	// /**
	//  * Output annotations objects for all pages.
	//  * !!! THIS METHOD IS NOT YET COMPLETED !!!
	//  * See section 12.5 of PDF 32000_2008 reference.
	//  * @protected
	//  * @author Nicola Asuni
	//  * @since 4.0.018 (2008-08-06)
	//  */
	// protected function _putannotsobjs() {
	// 	// reset object counter
	// 	for ($n=1; $n <= $this->numpages; ++$n) {
	// 		if (isset($this->PageAnnots[$n])) {
	// 			// set page annotations
	// 			foreach ($this->PageAnnots[$n] as $key => $pl) {
	// 				$annot_obj_id = $this->PageAnnots[$n][$key]['n'];
	// 				// create annotation object for grouping radiobuttons
	// 				if (isset($this->radiobutton_groups[$n][$pl['txt']]) AND is_array($this->radiobutton_groups[$n][$pl['txt']])) {
	// 					$radio_button_obj_id = $this->radiobutton_groups[$n][$pl['txt']]['n'];
	// 					$annots = '<<';
	// 					$annots .= ' /Type /Annot';
	// 					$annots .= ' /Subtype /Widget';
	// 					$annots .= ' /Rect [0 0 0 0]';
	// 					if ($this->radiobutton_groups[$n][$pl['txt']]['#readonly#']) {
	// 						// read only
	// 						$annots .= ' /F 68';
	// 						$annots .= ' /Ff 49153';
	// 					} else {
	// 						$annots .= ' /F 4'; // default print for PDF/A
	// 						$annots .= ' /Ff 49152';
	// 					}
	// 					$annots .= ' /T '.$this->_datastring($pl['txt'], $radio_button_obj_id);
	// 					if (isset($pl['opt']['tu']) AND is_string($pl['opt']['tu'])) {
	// 						$annots .= ' /TU '.$this->_datastring($pl['opt']['tu'], $radio_button_obj_id);
	// 					}
	// 					$annots .= ' /FT /Btn';
	// 					$annots .= ' /Kids [';
	// 					$defval = '';
	// 					foreach ($this->radiobutton_groups[$n][$pl['txt']] as $key => $data) {
	// 						if (isset($data['kid'])) {
	// 							$annots .= ' '.$data['kid'].' 0 R';
	// 							if ($data['def'] !== 'Off') {
	// 								$defval = $data['def'];
	// 							}
	// 						}
	// 					}
	// 					$annots .= ' ]';
	// 					if (!empty($defval)) {
	// 						$annots .= ' /V /'.$defval;
	// 					}
	// 					$annots .= ' >>';
	// 					$this->_out($this->_getobj($radio_button_obj_id)."\n".$annots."\n".'endobj');
	// 					$this->form_obj_id[] = $radio_button_obj_id;
	// 					// store object id to be used on Parent entry of Kids
	// 					$this->radiobutton_groups[$n][$pl['txt']] = $radio_button_obj_id;
	// 				}
	// 				$formfield = false;
	// 				$pl['opt'] = array_change_key_case($pl['opt'], CASE_LOWER);
	// 				$a = $pl['x'] * $this->k;
	// 				$b = $this->pagedim[$n]['h'] - (($pl['y'] + $pl['h']) * $this->k);
	// 				$c = $pl['w'] * $this->k;
	// 				$d = $pl['h'] * $this->k;
	// 				$rect = sprintf('%F %F %F %F', $a, $b, $a+$c, $b+$d);
	// 				// create new annotation object
	// 				$annots = '<</Type /Annot';
	// 				$annots .= ' /Subtype /'.$pl['opt']['subtype'];
	// 				$annots .= ' /Rect ['.$rect.']';
	// 				$ft = array('Btn', 'Tx', 'Ch', 'Sig');
	// 				if (isset($pl['opt']['ft']) AND in_array($pl['opt']['ft'], $ft)) {
	// 					$annots .= ' /FT /'.$pl['opt']['ft'];
	// 					$formfield = true;
	// 				}
	// 				if ($pl['opt']['subtype'] !== 'Link') {
	// 					$annots .= ' /Contents '.$this->_textstring($pl['txt'], $annot_obj_id);
	// 				}
	// 				$annots .= ' /P '.$this->page_obj_id[$n].' 0 R';
	// 				$annots .= ' /NM '.$this->_datastring(sprintf('%04u-%04u', $n, $key), $annot_obj_id);
	// 				$annots .= ' /M '.$this->_datestring($annot_obj_id, $this->doc_modification_timestamp);
	// 				if (isset($pl['opt']['f'])) {
	// 					$fval = 0;
	// 					if (is_array($pl['opt']['f'])) {
	// 						foreach ($pl['opt']['f'] as $f) {
	// 							switch (strtolower($f)) {
	// 								case 'invisible': {
	// 									$fval += 1 << 0;
	// 									break;
	// 								}
	// 								case 'hidden': {
	// 									$fval += 1 << 1;
	// 									break;
	// 								}
	// 								case 'print': {
	// 									$fval += 1 << 2;
	// 									break;
	// 								}
	// 								case 'nozoom': {
	// 									$fval += 1 << 3;
	// 									break;
	// 								}
	// 								case 'norotate': {
	// 									$fval += 1 << 4;
	// 									break;
	// 								}
	// 								case 'noview': {
	// 									$fval += 1 << 5;
	// 									break;
	// 								}
	// 								case 'readonly': {
	// 									$fval += 1 << 6;
	// 									break;
	// 								}
	// 								case 'locked': {
	// 									$fval += 1 << 7;
	// 									break;
	// 								}
	// 								case 'togglenoview': {
	// 									$fval += 1 << 8;
	// 									break;
	// 								}
	// 								case 'lockedcontents': {
	// 									$fval += 1 << 9;
	// 									break;
	// 								}
	// 								default: {
	// 									break;
	// 								}
	// 							}
	// 						}
	// 					} else {
	// 						$fval = intval($pl['opt']['f']);
	// 					}
	// 				} else {
	// 					$fval = 4;
	// 				}
	// 				if ($this->pdfa_mode) {
	// 					// force print flag for PDF/A mode
	// 					$fval |= 4;
	// 				}
	// 				$annots .= ' /F '.intval($fval);
	// 				if (isset($pl['opt']['as']) AND is_string($pl['opt']['as'])) {
	// 					$annots .= ' /AS /'.$pl['opt']['as'];
	// 				}
	// 				if (isset($pl['opt']['ap'])) {
	// 					// appearance stream
	// 					$annots .= ' /AP <<';
	// 					if (is_array($pl['opt']['ap'])) {
	// 						foreach ($pl['opt']['ap'] as $apmode => $apdef) {
	// 							// $apmode can be: n = normal; r = rollover; d = down;
	// 							$annots .= ' /'.strtoupper($apmode);
	// 							if (is_array($apdef)) {
	// 								$annots .= ' <<';
	// 								foreach ($apdef as $apstate => $stream) {
	// 									// reference to XObject that define the appearance for this mode-state
	// 									$apsobjid = $this->_putAPXObject($c, $d, $stream);
	// 									$annots .= ' /'.$apstate.' '.$apsobjid.' 0 R';
	// 								}
	// 								$annots .= ' >>';
	// 							} else {
	// 								// reference to XObject that define the appearance for this mode
	// 								$apsobjid = $this->_putAPXObject($c, $d, $apdef);
	// 								$annots .= ' '.$apsobjid.' 0 R';
	// 							}
	// 						}
	// 					} else {
	// 						$annots .= $pl['opt']['ap'];
	// 					}
	// 					$annots .= ' >>';
	// 				}
	// 				if (isset($pl['opt']['bs']) AND (is_array($pl['opt']['bs']))) {
	// 					$annots .= ' /BS <<';
	// 					$annots .= ' /Type /Border';
	// 					if (isset($pl['opt']['bs']['w'])) {
	// 						$annots .= ' /W '.intval($pl['opt']['bs']['w']);
	// 					}
	// 					$bstyles = array('S', 'D', 'B', 'I', 'U');
	// 					if (isset($pl['opt']['bs']['s']) AND in_array($pl['opt']['bs']['s'], $bstyles)) {
	// 						$annots .= ' /S /'.$pl['opt']['bs']['s'];
	// 					}
	// 					if (isset($pl['opt']['bs']['d']) AND (is_array($pl['opt']['bs']['d']))) {
	// 						$annots .= ' /D [';
	// 						foreach ($pl['opt']['bs']['d'] as $cord) {
	// 							$annots .= ' '.intval($cord);
	// 						}
	// 						$annots .= ']';
	// 					}
	// 					$annots .= ' >>';
	// 				} else {
	// 					$annots .= ' /Border [';
	// 					if (isset($pl['opt']['border']) AND (count($pl['opt']['border']) >= 3)) {
	// 						$annots .= intval($pl['opt']['border'][0]).' ';
	// 						$annots .= intval($pl['opt']['border'][1]).' ';
	// 						$annots .= intval($pl['opt']['border'][2]);
	// 						if (isset($pl['opt']['border'][3]) AND is_array($pl['opt']['border'][3])) {
	// 							$annots .= ' [';
	// 							foreach ($pl['opt']['border'][3] as $dash) {
	// 								$annots .= intval($dash).' ';
	// 							}
	// 							$annots .= ']';
	// 						}
	// 					} else {
	// 						$annots .= '0 0 0';
	// 					}
	// 					$annots .= ']';
	// 				}
	// 				if (isset($pl['opt']['be']) AND (is_array($pl['opt']['be']))) {
	// 					$annots .= ' /BE <<';
	// 					$bstyles = array('S', 'C');
	// 					if (isset($pl['opt']['be']['s']) AND in_array($pl['opt']['be']['s'], $bstyles)) {
	// 						$annots .= ' /S /'.$pl['opt']['bs']['s'];
	// 					} else {
	// 						$annots .= ' /S /S';
	// 					}
	// 					if (isset($pl['opt']['be']['i']) AND ($pl['opt']['be']['i'] >= 0) AND ($pl['opt']['be']['i'] <= 2)) {
	// 						$annots .= ' /I '.sprintf(' %F', $pl['opt']['be']['i']);
	// 					}
	// 					$annots .= '>>';
	// 				}
	// 				if (isset($pl['opt']['c']) AND (is_array($pl['opt']['c'])) AND !empty($pl['opt']['c'])) {
	// 					$annots .= ' /C '.LIMEPDF_COLORS::getColorStringFromArray($pl['opt']['c']);
	// 				}
	// 				//$annots .= ' /StructParent ';
	// 				//$annots .= ' /OC ';
	// 				$markups = array('text', 'freetext', 'line', 'square', 'circle', 'polygon', 'polyline', 'highlight', 'underline', 'squiggly', 'strikeout', 'stamp', 'caret', 'ink', 'fileattachment', 'sound');
	// 				if (in_array(strtolower($pl['opt']['subtype']), $markups)) {
	// 					// this is a markup type
	// 					if (isset($pl['opt']['t']) AND is_string($pl['opt']['t'])) {
	// 						$annots .= ' /T '.$this->_textstring($pl['opt']['t'], $annot_obj_id);
	// 					}
	// 					//$annots .= ' /Popup ';
	// 					if (isset($pl['opt']['ca'])) {
	// 						$annots .= ' /CA '.sprintf('%F', floatval($pl['opt']['ca']));
	// 					}
	// 					if (isset($pl['opt']['rc'])) {
	// 						$annots .= ' /RC '.$this->_textstring($pl['opt']['rc'], $annot_obj_id);
	// 					}
	// 					$annots .= ' /CreationDate '.$this->_datestring($annot_obj_id, $this->doc_creation_timestamp);
	// 					//$annots .= ' /IRT ';
	// 					if (isset($pl['opt']['subj'])) {
	// 						$annots .= ' /Subj '.$this->_textstring($pl['opt']['subj'], $annot_obj_id);
	// 					}
	// 					//$annots .= ' /RT ';
	// 					//$annots .= ' /IT ';
	// 					//$annots .= ' /ExData ';
	// 				}
	// 				$lineendings = array('Square', 'Circle', 'Diamond', 'OpenArrow', 'ClosedArrow', 'None', 'Butt', 'ROpenArrow', 'RClosedArrow', 'Slash');
	// 				// Annotation types
	// 				switch (strtolower($pl['opt']['subtype'])) {
	// 					case 'text': {
	// 						if (isset($pl['opt']['open'])) {
	// 							$annots .= ' /Open '. (strtolower($pl['opt']['open']) == 'true' ? 'true' : 'false');
	// 						}
	// 						$iconsapp = array('Comment', 'Help', 'Insert', 'Key', 'NewParagraph', 'Note', 'Paragraph');
	// 						if (isset($pl['opt']['name']) AND in_array($pl['opt']['name'], $iconsapp)) {
	// 							$annots .= ' /Name /'.$pl['opt']['name'];
	// 						} else {
	// 							$annots .= ' /Name /Note';
	// 						}
	// 						$hasStateModel = isset($pl['opt']['statemodel']);
	// 						$hasState = isset($pl['opt']['state']);
	// 						$statemodels = array('Marked', 'Review');
	// 						if (!$hasStateModel && !$hasState) {
	// 							break;
	// 						}
	// 						if ($hasStateModel AND in_array($pl['opt']['statemodel'], $statemodels)) {
	// 							$annots .= ' /StateModel /'.$pl['opt']['statemodel'];
	// 						} else {
	// 							$pl['opt']['statemodel'] = 'Marked';
	// 							$annots .= ' /StateModel /'.$pl['opt']['statemodel'];
	// 						}
	// 						if ($pl['opt']['statemodel'] == 'Marked') {
	// 							$states = array('Accepted', 'Unmarked');
	// 						} else {
	// 							$states = array('Accepted', 'Rejected', 'Cancelled', 'Completed', 'None');
	// 						}
	// 						if ($hasState AND in_array($pl['opt']['state'], $states)) {
	// 							$annots .= ' /State /'.$pl['opt']['state'];
	// 						} else {
	// 							if ($pl['opt']['statemodel'] == 'Marked') {
	// 								$annots .= ' /State /Unmarked';
	// 							} else {
	// 								$annots .= ' /State /None';
	// 							}
	// 						}
	// 						break;
	// 					}
	// 					case 'link': {
	// 						if (is_string($pl['txt']) && !empty($pl['txt'])) {
	// 							if ($pl['txt'][0] == '#') {
	// 								// internal destination
	// 								$annots .= ' /A <</S /GoTo /D /'.LIMEPDF_STATIC::encodeNameObject(substr($pl['txt'], 1)).'>>';
	// 							} elseif ($pl['txt'][0] == '%') {
	// 								// embedded PDF file
	// 								$filename = basename(substr($pl['txt'], 1));
	// 								$annots .= ' /A << /S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P '.($n - 1).' /A '.$this->embeddedfiles[$filename]['a'].' >> >>';
	// 							} elseif ($pl['txt'][0] == '*') {
	// 								// embedded generic file
	// 								$filename = basename(substr($pl['txt'], 1));
	// 								$jsa = 'var D=event.target.doc;var MyData=D.dataObjects;for (var i in MyData) if (MyData[i].path=="'.$filename.'") D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
	// 								$annots .= ' /A << /S /JavaScript /JS '.$this->_textstring($jsa, $annot_obj_id).'>>';
	// 							} else {
	// 								$parsedUrl = parse_url($pl['txt']);
	// 								if (empty($parsedUrl['scheme']) AND (!empty($parsedUrl['path']) && strtolower(substr($parsedUrl['path'], -4)) == '.pdf')) {
	// 									// relative link to a PDF file
	// 									$dest = '[0 /Fit]'; // default page 0
	// 									if (!empty($parsedUrl['fragment'])) {
	// 										// check for named destination
	// 										$tmp = explode('=', $parsedUrl['fragment']);
	// 										$dest = '('.((count($tmp) == 2) ? $tmp[1] : $tmp[0]).')';
	// 									}
	// 									$annots .= ' /A <</S /GoToR /D '.$dest.' /F '.$this->_datastring($this->unhtmlentities($parsedUrl['path']), $annot_obj_id).' /NewWindow true>>';
	// 								} else {
	// 									// external URI link
	// 									$annots .= ' /A <</S /URI /URI '.$this->_datastring($this->unhtmlentities($pl['txt']), $annot_obj_id).'>>';
	// 								}
	// 							}
	// 						} elseif (isset($this->links[$pl['txt']])) {
	// 							// internal link ID
	// 							$l = $this->links[$pl['txt']];
	// 							if (isset($this->page_obj_id[($l['p'])])) {
	// 								$annots .= sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $this->page_obj_id[($l['p'])], ($this->pagedim[$l['p']]['h'] - ($l['y'] * $this->k)));
	// 							}
	// 						}
	// 						$hmodes = array('N', 'I', 'O', 'P');
	// 						if (isset($pl['opt']['h']) AND in_array($pl['opt']['h'], $hmodes)) {
	// 							$annots .= ' /H /'.$pl['opt']['h'];
	// 						} else {
	// 							$annots .= ' /H /I';
	// 						}
	// 						//$annots .= ' /PA ';
	// 						//$annots .= ' /Quadpoints ';
	// 						break;
	// 					}
	// 					case 'freetext': {
	// 						if (isset($pl['opt']['da']) AND !empty($pl['opt']['da'])) {
	// 							$annots .= ' /DA '.$this->_datastring($pl['opt']['da']);
	// 						}
	// 						if (isset($pl['opt']['q']) AND ($pl['opt']['q'] >= 0) AND ($pl['opt']['q'] <= 2)) {
	// 							$annots .= ' /Q '.intval($pl['opt']['q']);
	// 						}
	// 						if (isset($pl['opt']['rc'])) {
	// 							$annots .= ' /RC '.$this->_textstring($pl['opt']['rc'], $annot_obj_id);
	// 						}
	// 						if (isset($pl['opt']['ds'])) {
	// 							$annots .= ' /DS '.$this->_textstring($pl['opt']['ds'], $annot_obj_id);
	// 						}
	// 						if (isset($pl['opt']['cl']) AND is_array($pl['opt']['cl'])) {
	// 							$annots .= ' /CL [';
	// 							foreach ($pl['opt']['cl'] as $cl) {
	// 								$annots .= sprintf('%F ', $cl * $this->k);
	// 							}
	// 							$annots .= ']';
	// 						}
	// 						$tfit = array('FreeText', 'FreeTextCallout', 'FreeTextTypeWriter');
	// 						if (isset($pl['opt']['it']) AND in_array($pl['opt']['it'], $tfit)) {
	// 							$annots .= ' /IT /'.$pl['opt']['it'];
	// 						}
	// 						if (isset($pl['opt']['rd']) AND is_array($pl['opt']['rd'])) {
	// 							$l = $pl['opt']['rd'][0] * $this->k;
	// 							$r = $pl['opt']['rd'][1] * $this->k;
	// 							$t = $pl['opt']['rd'][2] * $this->k;
	// 							$b = $pl['opt']['rd'][3] * $this->k;
	// 							$annots .= ' /RD ['.sprintf('%F %F %F %F', $l, $r, $t, $b).']';
	// 						}
	// 						if (isset($pl['opt']['le']) AND in_array($pl['opt']['le'], $lineendings)) {
	// 							$annots .= ' /LE /'.$pl['opt']['le'];
	// 						}
	// 						break;
	// 					}
	// 					case 'line': {
	// 						break;
	// 					}
	// 					case 'square': {
	// 						break;
	// 					}
	// 					case 'circle': {
	// 						break;
	// 					}
	// 					case 'polygon': {
	// 						break;
	// 					}
	// 					case 'polyline': {
	// 						break;
	// 					}
	// 					case 'highlight': {
	// 						break;
	// 					}
	// 					case 'underline': {
	// 						break;
	// 					}
	// 					case 'squiggly': {
	// 						break;
	// 					}
	// 					case 'strikeout': {
	// 						break;
	// 					}
	// 					case 'stamp': {
	// 						break;
	// 					}
	// 					case 'caret': {
	// 						break;
	// 					}
	// 					case 'ink': {
	// 						break;
	// 					}
	// 					case 'popup': {
	// 						break;
	// 					}
	// 					case 'fileattachment': {
	// 						if ($this->pdfa_mode && $this->pdfa_version != 3) {
	// 							// embedded files are not allowed in PDF/A mode version 1 and 2
	// 							break;
	// 						}
	// 						if (!isset($pl['opt']['fs'])) {
	// 							break;
	// 						}
	// 						$filename = basename($pl['opt']['fs']);
	// 						if (isset($this->embeddedfiles[$filename]['f'])) {
	// 							$annots .= ' /FS '.$this->embeddedfiles[$filename]['f'].' 0 R';
	// 							$iconsapp = array('Graph', 'Paperclip', 'PushPin', 'Tag');
	// 							if (isset($pl['opt']['name']) AND in_array($pl['opt']['name'], $iconsapp)) {
	// 								$annots .= ' /Name /'.$pl['opt']['name'];
	// 							} else {
	// 								$annots .= ' /Name /PushPin';
	// 							}
	// 							// index (zero-based) of the annotation in the Annots array of this page
	// 							$this->embeddedfiles[$filename]['a'] = $key;
	// 						}
	// 						break;
	// 					}
	// 					case 'sound': {
	// 						if (!isset($pl['opt']['fs'])) {
	// 							break;
	// 						}
	// 						$filename = basename($pl['opt']['fs']);
	// 						if (isset($this->embeddedfiles[$filename]['f'])) {
	// 							// ... TO BE COMPLETED ...
	// 							// /R /C /B /E /CO /CP
	// 							$annots .= ' /Sound '.$this->embeddedfiles[$filename]['f'].' 0 R';
	// 							$iconsapp = array('Speaker', 'Mic');
	// 							if (isset($pl['opt']['name']) AND in_array($pl['opt']['name'], $iconsapp)) {
	// 								$annots .= ' /Name /'.$pl['opt']['name'];
	// 							} else {
	// 								$annots .= ' /Name /Speaker';
	// 							}
	// 						}
	// 						break;
	// 					}
	// 					case 'movie': {
	// 						break;
	// 					}
	// 					case 'widget': {
	// 						$hmode = array('N', 'I', 'O', 'P', 'T');
	// 						if (isset($pl['opt']['h']) AND in_array($pl['opt']['h'], $hmode)) {
	// 							$annots .= ' /H /'.$pl['opt']['h'];
	// 						}
	// 						if (isset($pl['opt']['mk']) AND (is_array($pl['opt']['mk'])) AND !empty($pl['opt']['mk'])) {
	// 							$annots .= ' /MK <<';
	// 							if (isset($pl['opt']['mk']['r'])) {
	// 								$annots .= ' /R '.$pl['opt']['mk']['r'];
	// 							}
	// 							if (isset($pl['opt']['mk']['bc']) AND (is_array($pl['opt']['mk']['bc']))) {
	// 								$annots .= ' /BC '.LIMEPDF_COLORS::getColorStringFromArray($pl['opt']['mk']['bc']);
	// 							}
	// 							if (isset($pl['opt']['mk']['bg']) AND (is_array($pl['opt']['mk']['bg']))) {
	// 								$annots .= ' /BG '.LIMEPDF_COLORS::getColorStringFromArray($pl['opt']['mk']['bg']);
	// 							}
	// 							if (isset($pl['opt']['mk']['ca'])) {
	// 								$annots .= ' /CA '.$pl['opt']['mk']['ca'];
	// 							}
	// 							if (isset($pl['opt']['mk']['rc'])) {
	// 								$annots .= ' /RC '.$pl['opt']['mk']['rc'];
	// 							}
	// 							if (isset($pl['opt']['mk']['ac'])) {
	// 								$annots .= ' /AC '.$pl['opt']['mk']['ac'];
	// 							}
	// 							if (isset($pl['opt']['mk']['i'])) {
	// 								$info = $this->getImageBuffer($pl['opt']['mk']['i']);
	// 								if ($info !== false) {
	// 									$annots .= ' /I '.$info['n'].' 0 R';
	// 								}
	// 							}
	// 							if (isset($pl['opt']['mk']['ri'])) {
	// 								$info = $this->getImageBuffer($pl['opt']['mk']['ri']);
	// 								if ($info !== false) {
	// 									$annots .= ' /RI '.$info['n'].' 0 R';
	// 								}
	// 							}
	// 							if (isset($pl['opt']['mk']['ix'])) {
	// 								$info = $this->getImageBuffer($pl['opt']['mk']['ix']);
	// 								if ($info !== false) {
	// 									$annots .= ' /IX '.$info['n'].' 0 R';
	// 								}
	// 							}
	// 							if (isset($pl['opt']['mk']['if']) AND (is_array($pl['opt']['mk']['if'])) AND !empty($pl['opt']['mk']['if'])) {
	// 								$annots .= ' /IF <<';
	// 								$if_sw = array('A', 'B', 'S', 'N');
	// 								if (isset($pl['opt']['mk']['if']['sw']) AND in_array($pl['opt']['mk']['if']['sw'], $if_sw)) {
	// 									$annots .= ' /SW /'.$pl['opt']['mk']['if']['sw'];
	// 								}
	// 								$if_s = array('A', 'P');
	// 								if (isset($pl['opt']['mk']['if']['s']) AND in_array($pl['opt']['mk']['if']['s'], $if_s)) {
	// 									$annots .= ' /S /'.$pl['opt']['mk']['if']['s'];
	// 								}
	// 								if (isset($pl['opt']['mk']['if']['a']) AND (is_array($pl['opt']['mk']['if']['a'])) AND !empty($pl['opt']['mk']['if']['a'])) {
	// 									$annots .= sprintf(' /A [%F %F]', $pl['opt']['mk']['if']['a'][0], $pl['opt']['mk']['if']['a'][1]);
	// 								}
	// 								if (isset($pl['opt']['mk']['if']['fb']) AND ($pl['opt']['mk']['if']['fb'])) {
	// 									$annots .= ' /FB true';
	// 								}
	// 								$annots .= '>>';
	// 							}
	// 							if (isset($pl['opt']['mk']['tp']) AND ($pl['opt']['mk']['tp'] >= 0) AND ($pl['opt']['mk']['tp'] <= 6)) {
	// 								$annots .= ' /TP '.intval($pl['opt']['mk']['tp']);
	// 							}
	// 							$annots .= '>>';
	// 						} // end MK
	// 						// --- Entries for field dictionaries ---
	// 						if (isset($this->radiobutton_groups[$n][$pl['txt']])) {
	// 							// set parent
	// 							$annots .= ' /Parent '.$this->radiobutton_groups[$n][$pl['txt']].' 0 R';
	// 						}
	// 						if (isset($pl['opt']['t']) AND is_string($pl['opt']['t'])) {
	// 							$annots .= ' /T '.$this->_datastring($pl['opt']['t'], $annot_obj_id);
	// 						}
	// 						if (isset($pl['opt']['tu']) AND is_string($pl['opt']['tu'])) {
	// 							$annots .= ' /TU '.$this->_datastring($pl['opt']['tu'], $annot_obj_id);
	// 						}
	// 						if (isset($pl['opt']['tm']) AND is_string($pl['opt']['tm'])) {
	// 							$annots .= ' /TM '.$this->_datastring($pl['opt']['tm'], $annot_obj_id);
	// 						}
	// 						if (isset($pl['opt']['ff'])) {
	// 							if (is_array($pl['opt']['ff'])) {
	// 								// array of bit settings
	// 								$flag = 0;
	// 								foreach($pl['opt']['ff'] as $val) {
	// 									$flag += 1 << ($val - 1);
	// 								}
	// 							} else {
	// 								$flag = intval($pl['opt']['ff']);
	// 							}
	// 							$annots .= ' /Ff '.$flag;
	// 						}
	// 						if (isset($pl['opt']['maxlen'])) {
	// 							$annots .= ' /MaxLen '.intval($pl['opt']['maxlen']);
	// 						}
	// 						if (isset($pl['opt']['v'])) {
	// 							$annots .= ' /V';
	// 							if (is_array($pl['opt']['v'])) {
	// 								foreach ($pl['opt']['v'] AS $optval) {
	// 									if (is_float($optval)) {
	// 										$optval = sprintf('%F', $optval);
	// 									}
	// 									$annots .= ' '.$optval;
	// 								}
	// 							} else {
	// 								$annots .= ' '.$this->_textstring($pl['opt']['v'], $annot_obj_id);
	// 							}
	// 						}
	// 						if (isset($pl['opt']['dv'])) {
	// 							$annots .= ' /DV';
	// 							if (is_array($pl['opt']['dv'])) {
	// 								foreach ($pl['opt']['dv'] AS $optval) {
	// 									if (is_float($optval)) {
	// 										$optval = sprintf('%F', $optval);
	// 									}
	// 									$annots .= ' '.$optval;
	// 								}
	// 							} else {
	// 								$annots .= ' '.$this->_textstring($pl['opt']['dv'], $annot_obj_id);
	// 							}
	// 						}
	// 						if (isset($pl['opt']['rv'])) {
	// 							$annots .= ' /RV';
	// 							if (is_array($pl['opt']['rv'])) {
	// 								foreach ($pl['opt']['rv'] AS $optval) {
	// 									if (is_float($optval)) {
	// 										$optval = sprintf('%F', $optval);
	// 									}
	// 									$annots .= ' '.$optval;
	// 								}
	// 							} else {
	// 								$annots .= ' '.$this->_textstring($pl['opt']['rv'], $annot_obj_id);
	// 							}
	// 						}
	// 						if (isset($pl['opt']['a']) AND !empty($pl['opt']['a'])) {
	// 							$annots .= ' /A << '.$pl['opt']['a'].' >>';
	// 						}
	// 						if (isset($pl['opt']['aa']) AND !empty($pl['opt']['aa'])) {
	// 							$annots .= ' /AA << '.$pl['opt']['aa'].' >>';
	// 						}
	// 						if (isset($pl['opt']['da']) AND !empty($pl['opt']['da'])) {
	// 							$annots .= ' /DA '.$this->_datastring($pl['opt']['da']);
	// 						}
	// 						if (isset($pl['opt']['q']) AND ($pl['opt']['q'] >= 0) AND ($pl['opt']['q'] <= 2)) {
	// 							$annots .= ' /Q '.intval($pl['opt']['q']);
	// 						}
	// 						if (isset($pl['opt']['opt']) AND (is_array($pl['opt']['opt'])) AND !empty($pl['opt']['opt'])) {
	// 							$annots .= ' /Opt [';
	// 							foreach($pl['opt']['opt'] AS $copt) {
	// 								if (is_array($copt)) {
	// 									$annots .= ' ['.$this->_textstring($copt[0], $annot_obj_id).' '.$this->_textstring($copt[1], $annot_obj_id).']';
	// 								} else {
	// 									$annots .= ' '.$this->_textstring($copt, $annot_obj_id);
	// 								}
	// 							}
	// 							$annots .= ']';
	// 						}
	// 						if (isset($pl['opt']['ti'])) {
	// 							$annots .= ' /TI '.intval($pl['opt']['ti']);
	// 						}
	// 						if (isset($pl['opt']['i']) AND (is_array($pl['opt']['i'])) AND !empty($pl['opt']['i'])) {
	// 							$annots .= ' /I [';
	// 							foreach($pl['opt']['i'] AS $copt) {
	// 								$annots .= intval($copt).' ';
	// 							}
	// 							$annots .= ']';
	// 						}
	// 						break;
	// 					}
	// 					case 'screen': {
	// 						break;
	// 					}
	// 					case 'printermark': {
	// 						break;
	// 					}
	// 					case 'trapnet': {
	// 						break;
	// 					}
	// 					case 'watermark': {
	// 						break;
	// 					}
	// 					case '3d': {
	// 						break;
	// 					}
	// 					default: {
	// 						break;
	// 					}
	// 				}
	// 				$annots .= '>>';
	// 				// create new annotation object
	// 				$this->_out($this->_getobj($annot_obj_id)."\n".$annots."\n".'endobj');
	// 				if ($formfield AND !isset($this->radiobutton_groups[$n][$pl['txt']])) {
	// 					// store reference of form object
	// 					$this->form_obj_id[] = $annot_obj_id;
	// 				}
	// 			}
	// 		}
	// 	} // end for each page
	// }

	/**
	 * Put appearance streams XObject used to define annotation's appearance states.
	 * @param int $w annotation width
	 * @param int $h annotation height
	 * @param string $stream appearance stream
	 * @return int object ID
	 * @protected
	 * @since 4.8.001 (2009-09-09)
	 */
	protected function _putAPXObject($w=0, $h=0, $stream='') {
		$stream = trim($stream);
		$out = $this->_getobj()."\n";
		$this->xobjects['AX'.$this->n] = array('n' => $this->n);
		$out .= '<<';
		$out .= ' /Type /XObject';
		$out .= ' /Subtype /Form';
		$out .= ' /FormType 1';
		if ($this->compress) {
			$stream = gzcompress($stream);
			$out .= ' /Filter /FlateDecode';
		}
		$rect = sprintf('%F %F', $w, $h);
		$out .= ' /BBox [0 0 '.$rect.']';
		$out .= ' /Matrix [1 0 0 1 0 0]';
		$out .= ' /Resources 2 0 R';
		$stream = $this->_getrawstream($stream);
		$out .= ' /Length '.strlen($stream);
		$out .= ' >>';
		$out .= ' stream'."\n".$stream."\n".'endstream';
		$out .= "\n".'endobj';
		$this->_out($out);
		return $this->n;
	}

	/**
	 * Adds unicode fonts.<br>
	 * Based on PDF Reference 1.3 (section 5)
	 * @param array $font font data
	 * @protected
	 * @author Nicola Asuni
	 * @since 1.52.0.TC005 (2005-01-05)
	 */
	public function _puttruetypeunicode($font) {
		$fontname = '';
		if ($font['subset']) {
			// change name for font subsetting
			$subtag = sprintf('%06u', $font['i']);
			$subtag = strtr($subtag, '0123456789', 'ABCDEFGHIJ');
			$fontname .= $subtag.'+';
		}
		$fontname .= $font['name'];
		// Type0 Font
		// A composite font composed of other fonts, organized hierarchically
		$out = $this->_getobj($this->font_obj_ids[$font['fontkey']])."\n";
		$out .= '<< /Type /Font';
		$out .= ' /Subtype /Type0';
		$out .= ' /BaseFont /'.$fontname;
		$out .= ' /Name /F'.$font['i'];
		$out .= ' /Encoding /'.$font['enc'];
		$out .= ' /ToUnicode '.($this->n + 1).' 0 R';
		$out .= ' /DescendantFonts ['.($this->n + 2).' 0 R]';
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		// ToUnicode map for Identity-H
		$stream = LIMEPDF_FONT_DATA::$uni_identity_h;
		// ToUnicode Object
		$this->_newobj();
		$stream = ($this->compress) ? gzcompress($stream) : $stream;
		$filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
		$stream = $this->_getrawstream($stream);
		$this->_out('<<'.$filter.'/Length '.strlen($stream).'>> stream'."\n".$stream."\n".'endstream'."\n".'endobj');
		// CIDFontType2
		// A CIDFont whose glyph descriptions are based on TrueType font technology
		$oid = $this->_newobj();
		$out = '<< /Type /Font';
		$out .= ' /Subtype /CIDFontType2';
		$out .= ' /BaseFont /'.$fontname;
		// A dictionary containing entries that define the character collection of the CIDFont.
		$cidinfo = '/Registry '.$this->_datastring($font['cidinfo']['Registry'], $oid);
		$cidinfo .= ' /Ordering '.$this->_datastring($font['cidinfo']['Ordering'], $oid);
		$cidinfo .= ' /Supplement '.$font['cidinfo']['Supplement'];
		$out .= ' /CIDSystemInfo << '.$cidinfo.' >>';
		$out .= ' /FontDescriptor '.($this->n + 1).' 0 R';
		$out .= ' /DW '.$font['dw']; // default width
		$out .= "\n".LIMEPDF_FONT::_putfontwidths($font, 0);
		if (isset($font['ctg']) AND (!LIMEPDF_STATIC::empty_string($font['ctg']))) {
			$out .= "\n".'/CIDToGIDMap '.($this->n + 2).' 0 R';
		}
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		// Font descriptor
		// A font descriptor describing the CIDFont default metrics other than its glyph widths
		$this->_newobj();
		$out = '<< /Type /FontDescriptor';
		$out .= ' /FontName /'.$fontname;
		foreach ($font['desc'] as $key => $value) {
			if (is_float($value)) {
				$value = sprintf('%F', $value);
			}
			$out .= ' /'.$key.' '.$value;
		}
		$fontdir = false;

		if (!LIMEPDF_STATIC::empty_string($font['file'])) {
			if (
				isset($font['file'], $this->FontFiles[$font['file']]) &&
				isset($this->FontFiles[$font['file']]['n'])
			) {
				$out .= ' /FontFile2 ' . $this->FontFiles[$font['file']]['n'] . ' 0 R';
			} else {
				echo '<pre>';
				echo "Missing font file or 'n' key\n";
				var_dump($font['file'], $font, $this->FontFiles);
				echo '</pre>';
				exit;
			}

		// echo '<pre>';
		// var_dump($font['file'], $this->FontFiles[$font['file']] ?? 'Not Found');
		// echo '</pre>';
		// exit;

		// echo '<pre>';
		// var_dump($font['file']);
		// echo '<br />';
		// var_dump($this->FontFiles[$font['file']]['n']);
		// echo '<br />';
		// var_dump($out);
		// echo '<br />';
		// var_dump(' /FontFile2 ' . $this->FontFiles[$font['file']]['n'] . ' 0 R');
		// echo '</pre>';				
		// exit;			
			// A stream containing a TrueType font
			$out .= ' /FontFile2 ' . $this->FontFiles[$font['file']]['n'] . ' 0 R';
			//$out .= ' /FontFile2 23 0 R';
			$fontdir = $this->FontFiles[$font['file']]['fontdir'];
		}
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		if (isset($font['ctg']) AND (!LIMEPDF_STATIC::empty_string($font['ctg']))) {
			$this->_newobj();
			// Embed CIDToGIDMap
			// A specification of the mapping from CIDs to glyph indices
			// search and get CTG font file to embedd
			$ctgfile = strtolower($font['ctg']);
			// search and get ctg font file to embedd
			$fontfile = LIMEPDF_FONT::getFontFullPath($ctgfile, $fontdir);
			if (LIMEPDF_STATIC::empty_string($fontfile)) {
				$this->Error('Font file not found: '.$ctgfile);
			}
			$stream = $this->_getrawstream(file_get_contents($fontfile));
			$out = '<< /Length '.strlen($stream).'';
			if (substr($fontfile, -2) == '.z') { // check file extension
				// Decompresses data encoded using the public-domain
				// zlib/deflate compression method, reproducing the
				// original text or binary data
				$out .= ' /Filter /FlateDecode';
			}
			$out .= ' >>';
			$out .= ' stream'."\n".$stream."\n".'endstream';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
	}

	/**
	 * Output CID-0 fonts.
	 * A Type 0 CIDFont contains glyph descriptions based on the Adobe Type 1 font format
	 * @param array $font font data
	 * @protected
	 * @author Andrew Whitehead, Nicola Asuni, Yukihiro Nakadaira
	 * @since 3.2.000 (2008-06-23)
	 */
	protected function _putcidfont0($font) {
		$cidoffset = 0;
		if (!isset($font['cw'][1])) {
			$cidoffset = 31;
		}
		if (isset($font['cidinfo']['uni2cid'])) {
			// convert unicode to cid.
			$uni2cid = $font['cidinfo']['uni2cid'];
			$cw = array();
			foreach ($font['cw'] as $uni => $width) {
				if (isset($uni2cid[$uni])) {
					$cw[($uni2cid[$uni] + $cidoffset)] = $width;
				} elseif ($uni < 256) {
					$cw[$uni] = $width;
				} // else unknown character
			}
			$font = array_merge($font, array('cw' => $cw));
		}
		$name = $font['name'];
		$enc = $font['enc'];
		if ($enc) {
			$longname = $name.'-'.$enc;
		} else {
			$longname = $name;
		}
		$out = $this->_getobj($this->font_obj_ids[$font['fontkey']])."\n";
		$out .= '<</Type /Font';
		$out .= ' /Subtype /Type0';
		$out .= ' /BaseFont /'.$longname;
		$out .= ' /Name /F'.$font['i'];
		if ($enc) {
			$out .= ' /Encoding /'.$enc;
		}
		$out .= ' /DescendantFonts ['.($this->n + 1).' 0 R]';
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		$oid = $this->_newobj();
		$out = '<</Type /Font';
		$out .= ' /Subtype /CIDFontType0';
		$out .= ' /BaseFont /'.$name;
		$cidinfo = '/Registry '.$this->_datastring($font['cidinfo']['Registry'], $oid);
		$cidinfo .= ' /Ordering '.$this->_datastring($font['cidinfo']['Ordering'], $oid);
		$cidinfo .= ' /Supplement '.$font['cidinfo']['Supplement'];
		$out .= ' /CIDSystemInfo <<'.$cidinfo.'>>';
		$out .= ' /FontDescriptor '.($this->n + 1).' 0 R';
		$out .= ' /DW '.$font['dw'];
		$out .= "\n".LIMEPDF_FONT::_putfontwidths($font, $cidoffset);
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		$this->_newobj();
		$s = '<</Type /FontDescriptor /FontName /'.$name;
		foreach ($font['desc'] as $k => $v) {
			if ($k != 'Style') {
				if (is_float($v)) {
					$v = sprintf('%F', $v);
				}
				$s .= ' /'.$k.' '.$v.'';
			}
		}
		$s .= '>>';
		$s .= "\n".'endobj';
		$this->_out($s);
	}




	/**
	 * Return XObjects Dictionary.
	 * @return string XObjects dictionary
	 * @protected
	 * @since 5.8.014 (2010-08-23)
	 */
	protected function _getxobjectdict() {
		$out = '';
		foreach ($this->xobjects as $id => $objid) {
			$out .= ' /'.$id.' '.$objid['n'].' 0 R';
		}
		return $out;
	}



	/**
	 * Output Resources.
	 * @protected
	 * 
	 */

	 public function getImagekeys() {
        return $this->imagekeys;
    }

    public function setImagekeys($value) {
        $this->imagekeys = $value;
        return $this;
    }

	public function getPdfa_mode() {
        return $this->pdfa_mode;
    }

    public function setPdfa_mode($value) {
        $this->pdfa_mode = $value;
        return $this;
    }

	public function getXobjects() {
        return $this->xobjects;
    }

    public function setXobjects($value) {
        $this->xobjects = $value;
        return $this;
    }

	// new	
	// 
	// // --- GETTERS --
	public function &getExtGStates() {
		return $this->extgstates;
	}


	public function &getPdfLayers() {
		return $this->pdflayers;
	}
	public function getCurrentObjectId(): int {
		return $this->n;
	}

	public function getObjectId(): int {
		return $this->n;
	}

	public function getRawStream(string $data): string {
		return $this->_getrawstream($data);
	}

	public function getDiffs(): array {
		return $this->diffs;
	}

	public function getFontFiles(): array {
		return $this->FontFiles;
	}

	public function getFontKeys(): array {
		return $this->fontkeys;
	}

	public function getAnnotationFonts(): array {
		return $this->annotation_fonts;
	}

	public function getN(): int {
		return $this->n;
	}

	public function getFontObjId(string $key): ?int {
		return $this->font_obj_ids[$key] ?? null;
	}

	public function getSpotColors(): array {
		return $this->spot_colors ?? [];
	}

	public function callGetObj($n) {
		return $this->_getobj($n); // wrapper for protected method
	}

	public function getXObjectDict(): string {
		return $this->_getxobjectdict();
	}

	public function getPdfAVersion(): bool {
		return $this->pdfa_version ?? false;
	}

	public function getCompress(): bool {
		return $this->compress;
	}


	// public function getPdfAVersion(): bool {
	// 	return $this->pdfa_version ?? false;
	// }	

	// --- SETTERS ---

	public function setSpotColors(array $colors): void {
		$this->spot_colors = $colors;
	}

	public function setPdfLayers($pdflayers) {
		$this->pdflayers = $pdflayers;
	}

	public function setExtGStates($extgstates) {
		$this->extgstates = $extgstates;
	}

	public function setDiffs(array $diffs): void {
		$this->diffs = $diffs;
	}

	public function setFontFiles(array $fontFiles): void {
		$this->FontFiles = $fontFiles;
	}

	public function addFontKey(string $key): void {
		if (!in_array($key, $this->fontkeys)) {
			$this->fontkeys[] = $key;
		}
	}

	public function setAnnotationFont(string $key, int $value): void {
		$this->annotation_fonts[$key] = $value;
	}

	public function setFontObjId(string $key, int $id): void {
		$this->font_obj_ids[$key] = $id;
	}

	public function incrementN(): int {
		return ++$this->n;
	}

	public function setFontFileN(string $file, int $n): void {
		if (!isset($this->FontFiles[$file])) {
			$this->FontFiles[$file] = [];
		}
		$this->FontFiles[$file]['n'] = $n;
	}

	public function isPdfaMode(): bool {
		return $this->pdfa_mode ?? false;
	}

	public function getEpsmarker() {
        return $this->epsmarker;
    }

    public function setEpsmarker($value) {
        $this->epsmarker = $value;
        return $this;
    }

	public function buildBBox($data) {
        return sprintf(
            ' /BBox [%F %F %F %F]',
            ($data['x'] * $this->k),
            (-$data['y'] * $this->k),
            (($data['w'] + $data['x']) * $this->k),
            (($data['h'] - $data['y']) * $this->k)
        );
    }

	public function getGradients() {
        return $this->gradients;
    }

    public function setGradients($value) {
        $this->gradients = $value;
        return $this;
    }

	public function getPdfa_version() {
        return $this->pdfa_version;
    }

    public function setPdfa_version($value) {
        $this->pdfa_version = $value;
        return $this;
    }

	//old
	public function _putresources() {
		$utilsPut = new \LimePDF\Utils\limePDF_Put();
			$utilsPut->putExtGStates($this);
			$utilsPut->putOcg($this);
			$utilsPut->putSpotColors($this);
			$utilsPut->putImages($this);    
			$utilsPut->putFonts($this);
			$utilsPut->putResourceDict($this);
			$utilsPut->putDests($this);		
			//$utils->putEncryption($this);		<-- TODO needs updated		
			//$utils->putAnnotsObjs($this);		<-- work in progress

		$utilsMisc = new \LimePDF\Utils\limePDF_Misc();  
			$utilsMisc->putXObjects($this);
			//$utilsMisc->putBookmarks($this);	<-- TODO needs updated
			//$utilsMisc->putShaders($this);	<-- TODO needs updated	
		
		//$utilsJavascript = new \limePDF_Javascript();
			//$utilsJavacript->putJavascript($this);	<-- TODO needs updated	

		// echo '<pre>';
		// var_dump($this);
		// echo '</pre>';
		// exit;
		
		//$this->_putextgstates();
		//$this->_putocg();
		//$this->_putfonts();
		//$this->_putimages();
		//$this->_putspotcolors();
		//$this->_putshaders();
		//$this->_putxobjects();
		//$this->_putresourcedict();
		//$this->_putdests();
		//$this->_putEmbeddedFiles();
		//$this->_putannotsobjs();
		//this->_putjavascript();
		//$this->_putbookmarks();
		//$this->_putencryption();

	}

	/**
	 * Adds some Metadata information (Document Information Dictionary)
	 * (see Chapter 14.3.3 Document Information Dictionary of PDF32000_2008.pdf Reference)
	 * @return int object id
	 * @protected
	 */
	protected function _putinfo() {
		$oid = $this->_newobj();
		$out = '<<';
		// store current isunicode value
		$prev_isunicode = $this->isunicode;
		if ($this->docinfounicode) {
			$this->isunicode = true;
		}
		if (!LIMEPDF_STATIC::empty_string($this->title)) {
			// The document's title.
			$out .= ' /Title '.$this->_textstring($this->title, $oid);
		}
		if (!LIMEPDF_STATIC::empty_string($this->author)) {
			// The name of the person who created the document.
			$out .= ' /Author '.$this->_textstring($this->author, $oid);
		}
		if (!LIMEPDF_STATIC::empty_string($this->subject)) {
			// The subject of the document.
			$out .= ' /Subject '.$this->_textstring($this->subject, $oid);
		}
		if (!LIMEPDF_STATIC::empty_string($this->keywords)) {
			// Keywords associated with the document.
			$out .= ' /Keywords '.$this->_textstring($this->keywords, $oid);
		}
		if (!LIMEPDF_STATIC::empty_string($this->creator)) {
			// If the document was converted to PDF from another format, the name of the conforming product that created the original document from which it was converted.
			$out .= ' /Creator '.$this->_textstring($this->creator, $oid);
		}
		// restore previous isunicode value
		$this->isunicode = $prev_isunicode;
		// default producer
		$out .= ' /Producer '.$this->_textstring(LIMEPDF_STATIC::getTCPDFProducer(), $oid);
		// The date and time the document was created, in human-readable form
		$out .= ' /CreationDate '.$this->_datestring(0, $this->doc_creation_timestamp);
		// The date and time the document was most recently modified, in human-readable form
		$out .= ' /ModDate '.$this->_datestring(0, $this->doc_modification_timestamp);
		// A name object indicating whether the document has been modified to include trapping information
		$out .= ' /Trapped /False';
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		return $oid;
	}

	/**
	 * Set additional XMP data to be added on the default XMP data just before the end of "x:xmpmeta" tag.
	 * IMPORTANT: This data is added as-is without controls, so you have to validate your data before using this method!
	 * @param string $xmp Custom XMP data.
	 * @since 5.9.128 (2011-10-06)
	 * @public
	 */
	public function setExtraXMP($xmp) {
		$this->custom_xmp = $xmp;
	}

	/**
	 * Set additional XMP data to be added on the default XMP data just before the end of "rdf:RDF" tag.
	 * IMPORTANT: This data is added as-is without controls, so you have to validate your data before using this method!
	 * @param string $xmp Custom XMP RDF data.
	 * @since 6.3.0 (2019-09-19)
	 * @public
	 */
	public function setExtraXMPRDF($xmp) {
		$this->custom_xmp_rdf = $xmp;
	}

	/**
	 * Set additional XMP data to be added to the default XMP data for PDF/A extensions.
	 * IMPORTANT: This data is added as-is without controls, so you have to validate your data before using this method!
	 * @param string $xmp Custom XMP RDF data.
	 * @since 6.9.0 (2025-02-14)
	 * @public
	 */
	public function setExtraXMPPdfaextension($xmp) {
		$this->custom_xmp_rdf_pdfaExtension = $xmp;
	}

	/**
	 * Put XMP data object and return ID.
	 * @return int The object ID.
	 * @since 5.9.121 (2011-09-28)
	 * @protected
	 */
	protected function _putXMP() {
		$oid = $this->_newobj();
		// store current isunicode value
		$prev_isunicode = $this->isunicode;
		$this->isunicode = true;
		$prev_encrypted = $this->encrypted;
		$this->encrypted = false;
		// set XMP data
		$xmp = '<?xpacket begin="'.LIMEPDF_FONT::unichr(0xfeff, $this->isunicode).'" id="W5M0MpCehiHzreSzNTczkc9d"?>'."\n";
		$xmp .= '<x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 4.2.1-c043 52.372728, 2009/01/18-15:08:04">'."\n";
		$xmp .= "\t".'<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'."\n";
		$xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
		$xmp .= "\t\t\t".'<dc:format>application/pdf</dc:format>'."\n";
		$xmp .= "\t\t\t".'<dc:title>'."\n";
		$xmp .= "\t\t\t\t".'<rdf:Alt>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li xml:lang="x-default">'.LIMEPDF_STATIC::_escapeXML($this->title).'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t".'</rdf:Alt>'."\n";
		$xmp .= "\t\t\t".'</dc:title>'."\n";
		$xmp .= "\t\t\t".'<dc:creator>'."\n";
		$xmp .= "\t\t\t\t".'<rdf:Seq>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li>'.LIMEPDF_STATIC::_escapeXML($this->author).'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t".'</rdf:Seq>'."\n";
		$xmp .= "\t\t\t".'</dc:creator>'."\n";
		$xmp .= "\t\t\t".'<dc:description>'."\n";
		$xmp .= "\t\t\t\t".'<rdf:Alt>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li xml:lang="x-default">'.LIMEPDF_STATIC::_escapeXML($this->subject).'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t".'</rdf:Alt>'."\n";
		$xmp .= "\t\t\t".'</dc:description>'."\n";
		$xmp .= "\t\t\t".'<dc:subject>'."\n";
		$xmp .= "\t\t\t\t".'<rdf:Bag>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li>'.LIMEPDF_STATIC::_escapeXML($this->keywords).'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t".'</rdf:Bag>'."\n";
		$xmp .= "\t\t\t".'</dc:subject>'."\n";
		$xmp .= "\t\t".'</rdf:Description>'."\n";
		// convert doc creation date format
		$dcdate = LIMEPDF_STATIC::getFormattedDate($this->doc_creation_timestamp);
		$doccreationdate = substr($dcdate, 0, 4).'-'.substr($dcdate, 4, 2).'-'.substr($dcdate, 6, 2);
		$doccreationdate .= 'T'.substr($dcdate, 8, 2).':'.substr($dcdate, 10, 2).':'.substr($dcdate, 12, 2);
		$doccreationdate .= substr($dcdate, 14, 3).':'.substr($dcdate, 18, 2);
		$doccreationdate = LIMEPDF_STATIC::_escapeXML($doccreationdate);
		// convert doc modification date format
		$dmdate = LIMEPDF_STATIC::getFormattedDate($this->doc_modification_timestamp);
		$docmoddate = substr($dmdate, 0, 4).'-'.substr($dmdate, 4, 2).'-'.substr($dmdate, 6, 2);
		$docmoddate .= 'T'.substr($dmdate, 8, 2).':'.substr($dmdate, 10, 2).':'.substr($dmdate, 12, 2);
		$docmoddate .= substr($dmdate, 14, 3).':'.substr($dmdate, 18, 2);
		$docmoddate = LIMEPDF_STATIC::_escapeXML($docmoddate);
		$xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:xmp="http://ns.adobe.com/xap/1.0/">'."\n";
		$xmp .= "\t\t\t".'<xmp:CreateDate>'.$doccreationdate.'</xmp:CreateDate>'."\n";
		$xmp .= "\t\t\t".'<xmp:CreatorTool>'.$this->creator.'</xmp:CreatorTool>'."\n";
		$xmp .= "\t\t\t".'<xmp:ModifyDate>'.$docmoddate.'</xmp:ModifyDate>'."\n";
		$xmp .= "\t\t\t".'<xmp:MetadataDate>'.$doccreationdate.'</xmp:MetadataDate>'."\n";
		$xmp .= "\t\t".'</rdf:Description>'."\n";
		$xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdf="http://ns.adobe.com/pdf/1.3/">'."\n";
		$xmp .= "\t\t\t".'<pdf:Keywords>'.LIMEPDF_STATIC::_escapeXML($this->keywords).'</pdf:Keywords>'."\n";
		$xmp .= "\t\t\t".'<pdf:Producer>'.LIMEPDF_STATIC::_escapeXML(LIMEPDF_STATIC::getTCPDFProducer()).'</pdf:Producer>'."\n";
		$xmp .= "\t\t".'</rdf:Description>'."\n";
		$xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:xmpMM="http://ns.adobe.com/xap/1.0/mm/">'."\n";
		$uuid = 'uuid:'.substr($this->file_id, 0, 8).'-'.substr($this->file_id, 8, 4).'-'.substr($this->file_id, 12, 4).'-'.substr($this->file_id, 16, 4).'-'.substr($this->file_id, 20, 12);
		$xmp .= "\t\t\t".'<xmpMM:DocumentID>'.$uuid.'</xmpMM:DocumentID>'."\n";
		$xmp .= "\t\t\t".'<xmpMM:InstanceID>'.$uuid.'</xmpMM:InstanceID>'."\n";
		$xmp .= "\t\t".'</rdf:Description>'."\n";
		if ($this->pdfa_mode) {
			$xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdfaid="http://www.aiim.org/pdfa/ns/id/">'."\n";
			$xmp .= "\t\t\t".'<pdfaid:part>'.$this->pdfa_version.'</pdfaid:part>'."\n";
			$xmp .= "\t\t\t".'<pdfaid:conformance>B</pdfaid:conformance>'."\n";
			$xmp .= "\t\t".'</rdf:Description>'."\n";
		}
		// XMP extension schemas
		$xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdfaExtension="http://www.aiim.org/pdfa/ns/extension/" xmlns:pdfaSchema="http://www.aiim.org/pdfa/ns/schema#" xmlns:pdfaProperty="http://www.aiim.org/pdfa/ns/property#">'."\n";
		$xmp .= "\t\t\t".'<pdfaExtension:schemas>'."\n";
		$xmp .= "\t\t\t\t".'<rdf:Bag>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://ns.adobe.com/pdf/1.3/</pdfaSchema:namespaceURI>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:prefix>pdf</pdfaSchema:prefix>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:schema>Adobe PDF Schema</pdfaSchema:schema>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:property>'."\n";
		$xmp .= "\t\t\t\t\t\t\t".'<rdf:Seq>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Adobe PDF Schema</pdfaProperty:description>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>InstanceID</pdfaProperty:name>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>URI</pdfaProperty:valueType>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t\t\t".'</rdf:Seq>'."\n";
		$xmp .= "\t\t\t\t\t\t".'</pdfaSchema:property>'."\n";
		$xmp .= "\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://ns.adobe.com/xap/1.0/mm/</pdfaSchema:namespaceURI>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:prefix>xmpMM</pdfaSchema:prefix>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:schema>XMP Media Management Schema</pdfaSchema:schema>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:property>'."\n";
		$xmp .= "\t\t\t\t\t\t\t".'<rdf:Seq>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>UUID based identifier for specific incarnation of a document</pdfaProperty:description>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>InstanceID</pdfaProperty:name>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>URI</pdfaProperty:valueType>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t\t\t".'</rdf:Seq>'."\n";
		$xmp .= "\t\t\t\t\t\t".'</pdfaSchema:property>'."\n";
		$xmp .= "\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://www.aiim.org/pdfa/ns/id/</pdfaSchema:namespaceURI>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:prefix>pdfaid</pdfaSchema:prefix>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:schema>PDF/A ID Schema</pdfaSchema:schema>'."\n";
		$xmp .= "\t\t\t\t\t\t".'<pdfaSchema:property>'."\n";
		$xmp .= "\t\t\t\t\t\t\t".'<rdf:Seq>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Part of PDF/A standard</pdfaProperty:description>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>part</pdfaProperty:name>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Integer</pdfaProperty:valueType>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Amendment of PDF/A standard</pdfaProperty:description>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>amd</pdfaProperty:name>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Text</pdfaProperty:valueType>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Conformance level of PDF/A standard</pdfaProperty:description>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>conformance</pdfaProperty:name>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Text</pdfaProperty:valueType>'."\n";
		$xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= "\t\t\t\t\t\t\t".'</rdf:Seq>'."\n";
		$xmp .= "\t\t\t\t\t\t".'</pdfaSchema:property>'."\n";
		$xmp .= "\t\t\t\t\t".'</rdf:li>'."\n";
		$xmp .= $this->custom_xmp_rdf_pdfaExtension;
		$xmp .= "\t\t\t\t".'</rdf:Bag>'."\n";
		$xmp .= "\t\t\t".'</pdfaExtension:schemas>'."\n";
		$xmp .= "\t\t".'</rdf:Description>'."\n";
		$xmp .= $this->custom_xmp_rdf;
		$xmp .= "\t".'</rdf:RDF>'."\n";
		$xmp .= $this->custom_xmp;
		$xmp .= '</x:xmpmeta>'."\n";
		$xmp .= '<?xpacket end="w"?>';
		$out = '<< /Type /Metadata /Subtype /XML /Length '.strlen($xmp).' >> stream'."\n".$xmp."\n".'endstream'."\n".'endobj';
		// restore previous isunicode value
		$this->isunicode = $prev_isunicode;
		$this->encrypted = $prev_encrypted;
		$this->_out($out);
		return $oid;
	}

	/**
	 * Output Catalog.
	 * @return int object id
	 * @protected
	 */
	protected function _putcatalog() {
		// put XMP
		$xmpobj = $this->_putXMP();
		// if required, add standard sRGB ICC colour profile
		if ($this->pdfa_mode OR $this->force_srgb) {
			$iccobj = $this->_newobj();
			$icc = file_get_contents(dirname(__FILE__).'/include/sRGB.icc');
			$filter = '';
			if ($this->compress) {
				$filter = ' /Filter /FlateDecode';
				$icc = gzcompress($icc);
			}
			$icc = $this->_getrawstream($icc);
			$this->_out('<</N 3 '.$filter.'/Length '.strlen($icc).'>> stream'."\n".$icc."\n".'endstream'."\n".'endobj');
		}
		// start catalog
		$oid = $this->_newobj();
		$out = '<< ';
		if (!empty($this->efnames)) {
			$out .= ' /AF [ '. implode(' ', $this->efnames) .' ]';
		}
		$out .= ' /Type /Catalog';
		$out .= ' /Version /'.$this->PDFVersion;
		//$out .= ' /Extensions <<>>';
		$out .= ' /Pages 1 0 R';
		//$out .= ' /PageLabels ' //...;
		$out .= ' /Names <<';
		if ((!$this->pdfa_mode) AND !empty($this->n_js)) {
			$out .= ' /JavaScript '.$this->n_js;
		}
		if (!empty($this->efnames)) {
			$out .= ' /EmbeddedFiles <</Names [';
			foreach ($this->efnames AS $fn => $fref) {
				$out .= ' '.$this->_datastring($fn).' '.$fref;
			}
			$out .= ' ]>>';
		}
		$out .= ' >>';
		if (!empty($this->dests)) {
			$out .= ' /Dests '.($this->n_dests).' 0 R';
		}
		$out .= $this->_putviewerpreferences();
		if (isset($this->LayoutMode) AND (!LIMEPDF_STATIC::empty_string($this->LayoutMode))) {
			$out .= ' /PageLayout /'.$this->LayoutMode;
		}
		if (isset($this->PageMode) AND (!LIMEPDF_STATIC::empty_string($this->PageMode))) {
			$out .= ' /PageMode /'.$this->PageMode;
		}
		if (count($this->outlines) > 0) {
			$out .= ' /Outlines '.$this->OutlineRoot.' 0 R';
			$out .= ' /PageMode /UseOutlines';
		}
		//$out .= ' /Threads []';
		if ($this->ZoomMode == 'fullpage') {
			$out .= ' /OpenAction ['.$this->page_obj_id[1].' 0 R /Fit]';
		} elseif ($this->ZoomMode == 'fullwidth') {
			$out .= ' /OpenAction ['.$this->page_obj_id[1].' 0 R /FitH null]';
		} elseif ($this->ZoomMode == 'real') {
			$out .= ' /OpenAction ['.$this->page_obj_id[1].' 0 R /XYZ null null 1]';
		} elseif (!is_string($this->ZoomMode)) {
			$out .= sprintf(' /OpenAction ['.$this->page_obj_id[1].' 0 R /XYZ null null %F]', ($this->ZoomMode / 100));
		}
		//$out .= ' /AA <<>>';
		//$out .= ' /URI <<>>';
		$out .= ' /Metadata '.$xmpobj.' 0 R';
		//$out .= ' /StructTreeRoot <<>>';
		//$out .= ' /MarkInfo <<>>';
		if (isset($this->l['a_meta_language'])) {
			$out .= ' /Lang '.$this->_textstring($this->l['a_meta_language'], $oid);
		}
		//$out .= ' /SpiderInfo <<>>';
		// set OutputIntent to sRGB IEC61966-2.1 if required
		if ($this->pdfa_mode OR $this->force_srgb) {
			$out .= ' /OutputIntents [<<';
			$out .= ' /Type /OutputIntent';
			$out .= ' /S /GTS_PDFA1';
			$out .= ' /OutputCondition '.$this->_textstring('sRGB IEC61966-2.1', $oid);
			$out .= ' /OutputConditionIdentifier '.$this->_textstring('sRGB IEC61966-2.1', $oid);
			$out .= ' /RegistryName '.$this->_textstring('http://www.color.org', $oid);
			$out .= ' /Info '.$this->_textstring('sRGB IEC61966-2.1', $oid);
			$out .= ' /DestOutputProfile '.$iccobj.' 0 R';
			$out .= ' >>]';
		}
		//$out .= ' /PieceInfo <<>>';
		if (!empty($this->pdflayers)) {
			$lyrobjs = '';
			$lyrobjs_off = '';
			$lyrobjs_lock = '';
			foreach ($this->pdflayers as $layer) {
				$layer_obj_ref = ' '.$layer['objid'].' 0 R';
				$lyrobjs .= $layer_obj_ref;
				if ($layer['view'] === false) {
					$lyrobjs_off .= $layer_obj_ref;
				}
				if ($layer['lock']) {
					$lyrobjs_lock .= $layer_obj_ref;
				}
			}
			$out .= ' /OCProperties << /OCGs ['.$lyrobjs.']';
			$out .= ' /D <<';
			$out .= ' /Name '.$this->_textstring('Layers', $oid);
			$out .= ' /Creator '.$this->_textstring('TCPDF', $oid);
			$out .= ' /BaseState /ON';
			$out .= ' /OFF ['.$lyrobjs_off.']';
			$out .= ' /Locked ['.$lyrobjs_lock.']';
			$out .= ' /Intent /View';
			$out .= ' /AS [';
			$out .= ' << /Event /Print /OCGs ['.$lyrobjs.'] /Category [/Print] >>';
			$out .= ' << /Event /View /OCGs ['.$lyrobjs.'] /Category [/View] >>';
			$out .= ' ]';
			$out .= ' /Order ['.$lyrobjs.']';
			$out .= ' /ListMode /AllPages';
			//$out .= ' /RBGroups ['..']';
			//$out .= ' /Locked ['..']';
			$out .= ' >>';
			$out .= ' >>';
		}
		// AcroForm
		if (!empty($this->form_obj_id)
			OR ($this->sign AND isset($this->signature_data['cert_type']))
			OR !empty($this->empty_signature_appearance)) {
			$out .= ' /AcroForm <<';
			$objrefs = '';
			if ($this->sign AND isset($this->signature_data['cert_type'])) {
				// set reference for signature object
				$objrefs .= $this->sig_obj_id.' 0 R';
			}
			if (!empty($this->empty_signature_appearance)) {
				foreach ($this->empty_signature_appearance as $esa) {
					// set reference for empty signature objects
					$objrefs .= ' '.$esa['objid'].' 0 R';
				}
			}
			if (!empty($this->form_obj_id)) {
				foreach($this->form_obj_id as $objid) {
					$objrefs .= ' '.$objid.' 0 R';
				}
			}
			$out .= ' /Fields ['.$objrefs.']';
			// It's better to turn off this value and set the appearance stream for each annotation (/AP) to avoid conflicts with signature fields.
			if (empty($this->signature_data['approval']) OR ($this->signature_data['approval'] != 'A')) {
				$out .= ' /NeedAppearances false';
			}
			if ($this->sign AND isset($this->signature_data['cert_type'])) {
				if ($this->signature_data['cert_type'] > 0) {
					$out .= ' /SigFlags 3';
				} else {
					$out .= ' /SigFlags 1';
				}
			}
			//$out .= ' /CO ';
			if (isset($this->annotation_fonts) AND !empty($this->annotation_fonts)) {
				$out .= ' /DR <<';
				$out .= ' /Font <<';
				foreach ($this->annotation_fonts as $fontkey => $fontid) {
					$out .= ' /F'.$fontid.' '.$this->font_obj_ids[$fontkey].' 0 R';
				}
				$out .= ' >> >>';
			}
			$font = $this->getFontBuffer((($this->pdfa_mode) ? 'pdfa' : '') .'helvetica');
			$out .= ' /DA ' . $this->_datastring('/F'.$font['i'].' 0 Tf 0 g');
			$out .= ' /Q '.(($this->rtl)?'2':'0');
			//$out .= ' /XFA ';
			$out .= ' >>';
			// signatures
			if ($this->sign AND isset($this->signature_data['cert_type'])
				AND (empty($this->signature_data['approval']) OR ($this->signature_data['approval'] != 'A'))) {
				if ($this->signature_data['cert_type'] > 0) {
					$out .= ' /Perms << /DocMDP '.($this->sig_obj_id + 1).' 0 R >>';
				} else {
					$out .= ' /Perms << /UR3 '.($this->sig_obj_id + 1).' 0 R >>';
				}
			}
		}
		//$out .= ' /Legal <<>>';
		//$out .= ' /Requirements []';
		//$out .= ' /Collection <<>>';
		//$out .= ' /NeedsRendering true';
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
		return $oid;
	}

	/**
	 * Output viewer preferences.
	 * @return string for viewer preferences
	 * @author Nicola asuni
	 * @since 3.1.000 (2008-06-09)
	 * @protected
	 */
	protected function _putviewerpreferences() {
		$vp = $this->viewer_preferences;
		$out = ' /ViewerPreferences <<';
		if ($this->rtl) {
			$out .= ' /Direction /R2L';
		} else {
			$out .= ' /Direction /L2R';
		}
		if (isset($vp['HideToolbar']) AND ($vp['HideToolbar'])) {
			$out .= ' /HideToolbar true';
		}
		if (isset($vp['HideMenubar']) AND ($vp['HideMenubar'])) {
			$out .= ' /HideMenubar true';
		}
		if (isset($vp['HideWindowUI']) AND ($vp['HideWindowUI'])) {
			$out .= ' /HideWindowUI true';
		}
		if (isset($vp['FitWindow']) AND ($vp['FitWindow'])) {
			$out .= ' /FitWindow true';
		}
		if (isset($vp['CenterWindow']) AND ($vp['CenterWindow'])) {
			$out .= ' /CenterWindow true';
		}
		if (isset($vp['DisplayDocTitle']) AND ($vp['DisplayDocTitle'])) {
			$out .= ' /DisplayDocTitle true';
		}
		if (isset($vp['NonFullScreenPageMode'])) {
			$out .= ' /NonFullScreenPageMode /'.$vp['NonFullScreenPageMode'];
		}
		if (isset($vp['ViewArea'])) {
			$out .= ' /ViewArea /'.$vp['ViewArea'];
		}
		if (isset($vp['ViewClip'])) {
			$out .= ' /ViewClip /'.$vp['ViewClip'];
		}
		if (isset($vp['PrintArea'])) {
			$out .= ' /PrintArea /'.$vp['PrintArea'];
		}
		if (isset($vp['PrintClip'])) {
			$out .= ' /PrintClip /'.$vp['PrintClip'];
		}
		if (isset($vp['PrintScaling'])) {
			$out .= ' /PrintScaling /'.$vp['PrintScaling'];
		}
		if (isset($vp['Duplex']) AND (!LIMEPDF_STATIC::empty_string($vp['Duplex']))) {
			$out .= ' /Duplex /'.$vp['Duplex'];
		}
		if (isset($vp['PickTrayByPDFSize'])) {
			if ($vp['PickTrayByPDFSize']) {
				$out .= ' /PickTrayByPDFSize true';
			} else {
				$out .= ' /PickTrayByPDFSize false';
			}
		}
		if (isset($vp['PrintPageRange'])) {
			$PrintPageRangeNum = '';
			foreach ($vp['PrintPageRange'] as $k => $v) {
				$PrintPageRangeNum .= ' '.($v - 1).'';
			}
			$out .= ' /PrintPageRange ['.substr($PrintPageRangeNum,1).']';
		}
		if (isset($vp['NumCopies'])) {
			$out .= ' /NumCopies '.intval($vp['NumCopies']);
		}
		$out .= ' >>';
		return $out;
	}

	/**
	 * Output PDF File Header (7.5.2).
	 * @protected
	 */
	protected function _putheader() {
		$this->_out('%PDF-'.$this->PDFVersion);
		$this->_out('%'.chr(0xe2).chr(0xe3).chr(0xcf).chr(0xd3));
	}

	/**
	 * Output end of document (EOF).
	 * @protected
	 */
	protected function _enddoc() {
		if (isset($this->CurrentFont['fontkey']) AND isset($this->CurrentFont['subsetchars'])) {
			// save subset chars of the previous font
			$this->setFontSubBuffer($this->CurrentFont['fontkey'], 'subsetchars', $this->CurrentFont['subsetchars']);
		}
		$this->state = 1;
		$this->_putheader();
		$this->_putpages();
		$this->_putresources();
		// empty signature fields
		if (!empty($this->empty_signature_appearance)) {
			foreach ($this->empty_signature_appearance as $key => $esa) {
				// widget annotation for empty signature
				$out = $this->_getobj($esa['objid'])."\n";
				$out .= '<< /Type /Annot';
				$out .= ' /Subtype /Widget';
				$out .= ' /Rect ['.$esa['rect'].']';
				$out .= ' /P '.$this->page_obj_id[($esa['page'])].' 0 R'; // link to signature appearance page
				$out .= ' /F 4';
				$out .= ' /FT /Sig';
				$signame = $esa['name'].sprintf(' [%03d]', ($key + 1));
				$out .= ' /T '.$this->_textstring($signame, $esa['objid']);
				$out .= ' /Ff 0';
				$out .= ' >>';
				$out .= "\n".'endobj';
				$this->_out($out);
			}
		}
		// Signature
		if ($this->sign AND isset($this->signature_data['cert_type'])) {
			// widget annotation for signature
			$out = $this->_getobj($this->sig_obj_id)."\n";
			$out .= '<< /Type /Annot';
			$out .= ' /Subtype /Widget';
			$out .= ' /Rect ['.$this->signature_appearance['rect'].']';
			$out .= ' /P '.$this->page_obj_id[($this->signature_appearance['page'])].' 0 R'; // link to signature appearance page
			$out .= ' /F 4';
			$out .= ' /FT /Sig';
			$out .= ' /T '.$this->_textstring($this->signature_appearance['name'], $this->sig_obj_id);
			$out .= ' /Ff 0';
			$out .= ' /V '.($this->sig_obj_id + 1).' 0 R';
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
			// signature
			$this->_putsignature();
		}
		// Info
		$objid_info = $this->_putinfo();
		// Catalog
		$objid_catalog = $this->_putcatalog();
		// Cross-ref
		$o = $this->bufferlen;
		// XREF section
		$this->_out('xref');
		$this->_out('0 '.($this->n + 1));
		$this->_out('0000000000 65535 f ');
		$freegen = ($this->n + 2);
		for ($i=1; $i <= $this->n; ++$i) {
			if (!isset($this->offsets[$i]) AND ($i > 1)) {
				$this->_out(sprintf('0000000000 %05d f ', $freegen));
				++$freegen;
			} else {
				$this->_out(sprintf('%010d 00000 n ', $this->offsets[$i]));
			}
		}
		// TRAILER
		$out = 'trailer'."\n";
		$out .= '<<';
		$out .= ' /Size '.($this->n + 1);
		$out .= ' /Root '.$objid_catalog.' 0 R';
		$out .= ' /Info '.$objid_info.' 0 R';
		if ($this->encrypted) {
			$out .= ' /Encrypt '.$this->encryptdata['objid'].' 0 R';
		}
		$out .= ' /ID [ <'.$this->file_id.'> <'.$this->file_id.'> ]';
		$out .= ' >>';
		$this->_out($out);
		$this->_out('startxref');
		$this->_out($o);
		$this->_out('%%EOF');
		$this->state = 3; // end-of-doc
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
	 * Set the document creation timestamp
	 * @param mixed $time Document creation timestamp in seconds or date-time string.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function setDocCreationTimestamp($time) {
		if (is_string($time)) {
			$time = LIMEPDF_STATIC::getTimestamp($time);
		}
		$this->doc_creation_timestamp = intval($time);
	}

	/**
	 * Set the document modification timestamp
	 * @param mixed $time Document modification timestamp in seconds or date-time string.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function setDocModificationTimestamp($time) {
		if (is_string($time)) {
			$time = LIMEPDF_STATIC::getTimestamp($time);
		}
		$this->doc_modification_timestamp = intval($time);
	}

	/**
	 * Returns document creation timestamp in seconds.
	 * @return int Creation timestamp in seconds.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getDocCreationTimestamp() {
		return $this->doc_creation_timestamp;
	}

	/**
	 * Returns document modification timestamp in seconds.
	 * @return int Modfication timestamp in seconds.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getDocModificationTimestamp() {
		return $this->doc_modification_timestamp;
	}

	/**
	 * Returns a formatted date for meta information
	 * @param int $n Object ID.
	 * @param int $timestamp Timestamp to convert.
	 * @return string escaped date string.
	 * @protected
	 * @since 4.6.028 (2009-08-25)
	 */
	protected function _datestring($n=0, $timestamp=0) {
		if ((empty($timestamp)) OR ($timestamp < 0)) {
			$timestamp = $this->doc_creation_timestamp;
		}
		return $this->_datastring('D:'.LIMEPDF_STATIC::getFormattedDate($timestamp), $n);
	}

	/**
	 * Format a text string for meta information
	 * @param string $s string to escape.
	 * @param int $n object ID
	 * @return string escaped string.
	 * @protected
	 */
	protected function _textstring($s, $n=0) {
		if ($this->isunicode) {
			//Convert string to UTF-16BE
			$s = LIMEPDF_FONT::UTF8ToUTF16BE($s, true, $this->isunicode, $this->CurrentFont);
		}
		return $this->_datastring($s, $n);
	}

	/**
	 * get raw output stream.
	 * @param string $s string to output.
	 * @param int $n object reference for encryption mode
	 * @protected
	 * @author Nicola Asuni
	 * @since 5.5.000 (2010-06-22)
	 */
	public function _getrawstream($s, $n=0) {
		if ($n <= 0) {
			// default to current object
			$n = $this->n;
		}
		return $this->_encrypt_data($n, $s);
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
	 * Set header font.
	 * @param array<int,string|float|null> $font Array describing the basic font parameters: (family, style, size).
	 * @phpstan-param array{0: string, 1: string, 2: float|null} $font
	 * @public
	 * @since 1.1
	 */
	public function setHeaderFont($font) {
		$this->header_font = $font;
	}

	/**
	 * Get header font.
	 * @return array<int,string|float|null> Array describing the basic font parameters: (family, style, size).
	 * @phpstan-return array{0: string, 1: string, 2: float|null}
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getHeaderFont() {
		return $this->header_font;
	}

	/**
	 * Set footer font.
	 * @param array<int,string|float|null> $font Array describing the basic font parameters: (family, style, size).
	 * @phpstan-param array{0: string, 1: string, 2: float|null} $font
	 * @public
	 * @since 1.1
	 */
	public function setFooterFont($font) {
		$this->footer_font = $font;
	}

	/**
	 * Get Footer font.
	 * @return array<int,string|float|null> Array describing the basic font parameters: (family, style, size).
	 * @phpstan-return array{0: string, 1: string, 2: float|null} $font
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getFooterFont() {
		return $this->footer_font;
	}

	/**
	 * Set language array.
	 * @param array $language
	 * @public
	 * @since 1.1
	 */
	public function setLanguageArray($language) {
		$this->l = $language;
		if (isset($this->l['a_meta_dir'])) {
			$this->rtl = $this->l['a_meta_dir']=='rtl' ? true : false;
		} else {
			$this->rtl = false;
		}
	}

	/**
	 * Returns the PDF data.
	 * @public
	 */
	public function getPDFData() {
		if ($this->state < 3) {
			$this->Close();
		}
		return $this->buffer;
	}

	/**
	 * Output anchor link.
	 * @param string $url link URL or internal link (i.e.: &lt;a href="#23,4.5"&gt;link to page 23 at 4.5 Y position&lt;/a&gt;)
	 * @param string $name link name
	 * @param boolean $fill Indicates if the cell background must be painted (true) or transparent (false).
	 * @param boolean $firstline if true prints only the first line and return the remaining string.
	 * @param array|null $color array of RGB text color
	 * @param string $style font style (U, D, B, I)
	 * @param boolean $firstblock if true the string is the starting of a line.
	 * @return int the number of cells used or the remaining text if $firstline = true;
	 * @public
	 */
	public function addHtmlLink($url, $name, $fill=false, $firstline=false, $color=null, $style=-1, $firstblock=false) {
		if (isset($url[1]) AND ($url[0] == '#') AND is_numeric($url[1])) {
			// convert url to internal link
			$lnkdata = explode(',', $url);
			if (isset($lnkdata[0]) ) {
				$page = substr($lnkdata[0], 1);
				if (isset($lnkdata[1]) AND (strlen($lnkdata[1]) > 0)) {
					$lnky = floatval($lnkdata[1]);
				} else {
					$lnky = 0;
				}
				$url = $this->AddLink();
				$this->setLink($url, $lnky, $page);
			}
		}
		// store current settings
		$prevcolor = $this->fgcolor;
		$prevstyle = $this->FontStyle;
		if (empty($color)) {
			$this->setTextColorArray($this->htmlLinkColorArray);
		} else {
			$this->setTextColorArray($color);
		}
		if ($style == -1) {
			$this->setFont('', $this->FontStyle.$this->htmlLinkFontStyle);
		} else {
			$this->setFont('', $this->FontStyle.$style);
		}
		$ret = $this->Write($this->lasth, $name, $url, $fill, '', false, 0, $firstline, $firstblock, 0);
		// restore settings
		$this->setFont('', $prevstyle);
		$this->setTextColorArray($prevcolor);
		return $ret;
	}

	/**
	 * Converts pixels to User's Units.
	 * @param int $px pixels
	 * @return float value in user's unit
	 * @public
	 * @see setImageScale(), getImageScale()
	 */
	public function pixelsToUnits($px) {
		return ($px / ($this->imgscale * $this->k));
	}

	/**
	 * Reverse function for htmlentities.
	 * Convert entities in UTF-8.
	 * @param string $text_to_convert Text to convert.
	 * @return string converted text string
	 * @public
	 */
	public function unhtmlentities($text_to_convert) {
		return @html_entity_decode($text_to_convert, ENT_QUOTES, $this->encoding);
	}



	/**
	 * Add a Named Destination.
	 * NOTE: destination names are unique, so only last entry will be saved.
	 * @param string $name Destination name.
	 * @param float $y Y position in user units of the destiantion on the selected page (default = -1 = current position; 0 = page start;).
	 * @param int|string $page Target page number (leave empty for current page). If you prefix a page number with the * character, then this page will not be changed when adding/deleting/moving pages.
	 * @param float $x X position in user units of the destiantion on the selected page (default = -1 = current position;).
	 * @return string|false Stripped named destination identifier or false in case of error.
	 * @public
	 * @author Christian Deligant, Nicola Asuni
	 * @since 5.9.097 (2011-06-23)
	 */
	public function setDestination($name, $y=-1, $page='', $x=-1) {
		// remove unsupported characters
		$name = LIMEPDF_STATIC::encodeNameObject($name);
		if (LIMEPDF_STATIC::empty_string($name)) {
			return false;
		}
		if ($y == -1) {
			$y = $this->GetY();
		} elseif ($y < 0) {
			$y = 0;
		} elseif ($y > $this->h) {
			$y = $this->h;
		}
		if ($x == -1) {
			$x = $this->GetX();
		} elseif ($x < 0) {
			$x = 0;
		} elseif ($x > $this->w) {
			$x = $this->w;
		}
		$fixed = false;
		if (!empty($page) AND (substr($page, 0, 1) == '*')) {
			$page = intval(substr($page, 1));
			// this page number will not be changed when moving/add/deleting pages
			$fixed = true;
		}
		if (empty($page)) {
			$page = $this->PageNo();
			if (empty($page)) {
				return;
			}
		}
		$this->dests[$name] = array('x' => $x, 'y' => $y, 'p' => $page, 'f' => $fixed);
		return $name;
	}

	/**
	 * Return the Named Destination array.
	 * @return array Named Destination array.
	 * @public
	 * @author Nicola Asuni
	 * @since 5.9.097 (2011-06-23)
	 */
	public function getDestination() {
		return $this->dests;
	}



	/**
	 * Adds a bookmark - alias for Bookmark().
	 * @param string $txt Bookmark description.
	 * @param int $level Bookmark level (minimum value is 0).
	 * @param float $y Y position in user units of the bookmark on the selected page (default = -1 = current position; 0 = page start;).
	 * @param int|string $page Target page number (leave empty for current page). If you prefix a page number with the * character, then this page will not be changed when adding/deleting/moving pages.
	 * @param string $style Font style: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param array $color RGB color array (values from 0 to 255).
	 * @param float $x X position in user units of the bookmark on the selected page (default = -1 = current position;).
	 * @param mixed $link URL, or numerical link ID, or named destination (# character followed by the destination name), or embedded file (* character followed by the file name).
	 * @public
	 */
	public function setBookmark($txt, $level=0, $y=-1, $page='', $style='', $color=array(0,0,0), $x=-1, $link='') {
		$this->Bookmark($txt, $level, $y, $page, $style, $color, $x, $link);
	}

	/**
	 * Adds a bookmark.
	 * @param string $txt Bookmark description.
	 * @param int $level Bookmark level (minimum value is 0).
	 * @param float $y Y position in user units of the bookmark on the selected page (default = -1 = current position; 0 = page start;).
	 * @param int|string $page Target page number (leave empty for current page). If you prefix a page number with the * character, then this page will not be changed when adding/deleting/moving pages.
	 * @param string $style Font style: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param array $color RGB color array (values from 0 to 255).
	 * @param float $x X position in user units of the bookmark on the selected page (default = -1 = current position;).
	 * @param mixed $link URL, or numerical link ID, or named destination (# character followed by the destination name), or embedded file (* character followed by the file name).
	 * @public
	 * @since 2.1.002 (2008-02-12)
	 */
	public function Bookmark($txt, $level=0, $y=-1, $page='', $style='', $color=array(0,0,0), $x=-1, $link='') {
		if ($level < 0) {
			$level = 0;
		}
		if (isset($this->outlines[0])) {
			$lastoutline = end($this->outlines);
			$maxlevel = $lastoutline['l'] + 1;
		} else {
			$maxlevel = 0;
		}
		if ($level > $maxlevel) {
			$level = $maxlevel;
		}
		if ($y == -1) {
			$y = $this->GetY();
		} elseif ($y < 0) {
			$y = 0;
		} elseif ($y > $this->h) {
			$y = $this->h;
		}
		if ($x == -1) {
			$x = $this->GetX();
		} elseif ($x < 0) {
			$x = 0;
		} elseif ($x > $this->w) {
			$x = $this->w;
		}
		$fixed = false;
		$pageAsString = (string) $page;
		if ($pageAsString && $pageAsString[0] == '*') {
			$page = intval(substr($page, 1));
			// this page number will not be changed when moving/add/deleting pages
			$fixed = true;
		}
		if (empty($page)) {
			$page = $this->PageNo();
			if (empty($page)) {
				return;
			}
		}
		$this->outlines[] = array('t' => $txt, 'l' => $level, 'x' => $x, 'y' => $y, 'p' => $page, 'f' => $fixed, 's' => strtoupper($style), 'c' => $color, 'u' => $link);
	}

	/**
	 * Sort bookmarks for page and key.
	 * @protected
	 * @since 5.9.119 (2011-09-19)
	 */
	protected function sortBookmarks() {
		// get sorting columns
		$outline_p = array();
		$outline_y = array();
		foreach ($this->outlines as $key => $row) {
			$outline_p[$key] = $row['p'];
			$outline_k[$key] = $key;
		}
		// sort outlines by page and original position
		array_multisort($outline_p, SORT_NUMERIC, SORT_ASC, $outline_k, SORT_NUMERIC, SORT_ASC, $this->outlines);
	}


	/**
	 * Set default properties for form fields.
	 * @param array $prop javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-06)
	 */
	public function setFormDefaultProp($prop=array()) {
		$this->default_form_prop = $prop;
	}

	/**
	 * Return the default properties for form fields.
	 * @return array $prop javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-06)
	 */
	public function getFormDefaultProp() {
		return $this->default_form_prop;
	}

	/**
	 * Add certification signature (DocMDP or UR3)
	 * You can set only one signature type
	 * @protected
	 * @author Nicola Asuni
	 * @since 4.6.008 (2009-05-07)
	 */
	protected function _putsignature() {
		if ((!$this->sign) OR (!isset($this->signature_data['cert_type']))) {
			return;
		}
		$sigobjid = ($this->sig_obj_id + 1);
		$out = $this->_getobj($sigobjid)."\n";
		$out .= '<< /Type /Sig';
		$out .= ' /Filter /Adobe.PPKLite';
		$out .= ' /SubFilter /adbe.pkcs7.detached';
		$out .= ' '.LIMEPDF_STATIC::$byterange_string;
		$out .= ' /Contents<'.str_repeat('0', $this->signature_max_length).'>';
		if (empty($this->signature_data['approval']) OR ($this->signature_data['approval'] != 'A')) {
			$out .= ' /Reference ['; // array of signature reference dictionaries
			$out .= ' << /Type /SigRef';
			if ($this->signature_data['cert_type'] > 0) {
				$out .= ' /TransformMethod /DocMDP';
				$out .= ' /TransformParams <<';
				$out .= ' /Type /TransformParams';
				$out .= ' /P '.$this->signature_data['cert_type'];
				$out .= ' /V /1.2';
			} else {
				$out .= ' /TransformMethod /UR3';
				$out .= ' /TransformParams <<';
				$out .= ' /Type /TransformParams';
				$out .= ' /V /2.2';
				if (!LIMEPDF_STATIC::empty_string($this->ur['document'])) {
					$out .= ' /Document['.$this->ur['document'].']';
				}
				if (!LIMEPDF_STATIC::empty_string($this->ur['form'])) {
					$out .= ' /Form['.$this->ur['form'].']';
				}
				if (!LIMEPDF_STATIC::empty_string($this->ur['signature'])) {
					$out .= ' /Signature['.$this->ur['signature'].']';
				}
				if (!LIMEPDF_STATIC::empty_string($this->ur['annots'])) {
					$out .= ' /Annots['.$this->ur['annots'].']';
				}
				if (!LIMEPDF_STATIC::empty_string($this->ur['ef'])) {
					$out .= ' /EF['.$this->ur['ef'].']';
				}
				if (!LIMEPDF_STATIC::empty_string($this->ur['formex'])) {
					$out .= ' /FormEX['.$this->ur['formex'].']';
				}
			}
			$out .= ' >>'; // close TransformParams
			// optional digest data (values must be calculated and replaced later)
			//$out .= ' /Data ********** 0 R';
			//$out .= ' /DigestMethod/MD5';
			//$out .= ' /DigestLocation[********** 34]';
			//$out .= ' /DigestValue<********************************>';
			$out .= ' >>';
			$out .= ' ]'; // end of reference
		}
		if (isset($this->signature_data['info']['Name']) AND !LIMEPDF_STATIC::empty_string($this->signature_data['info']['Name'])) {
			$out .= ' /Name '.$this->_textstring($this->signature_data['info']['Name'], $sigobjid);
		}
		if (isset($this->signature_data['info']['Location']) AND !LIMEPDF_STATIC::empty_string($this->signature_data['info']['Location'])) {
			$out .= ' /Location '.$this->_textstring($this->signature_data['info']['Location'], $sigobjid);
		}
		if (isset($this->signature_data['info']['Reason']) AND !LIMEPDF_STATIC::empty_string($this->signature_data['info']['Reason'])) {
			$out .= ' /Reason '.$this->_textstring($this->signature_data['info']['Reason'], $sigobjid);
		}
		if (isset($this->signature_data['info']['ContactInfo']) AND !LIMEPDF_STATIC::empty_string($this->signature_data['info']['ContactInfo'])) {
			$out .= ' /ContactInfo '.$this->_textstring($this->signature_data['info']['ContactInfo'], $sigobjid);
		}
		$out .= ' /M '.$this->_datestring($sigobjid, $this->doc_modification_timestamp);
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
	}

	/**
	 * Set User's Rights for PDF Reader
	 * WARNING: This is experimental and currently do not work.
	 * Check the PDF Reference 8.7.1 Transform Methods,
	 * Table 8.105 Entries in the UR transform parameters dictionary
	 * @param boolean $enable if true enable user's rights on PDF reader
	 * @param string $document Names specifying additional document-wide usage rights for the document. The only defined value is "/FullSave", which permits a user to save the document along with modified form and/or annotation data.
	 * @param string $annots Names specifying additional annotation-related usage rights for the document. Valid names in PDF 1.5 and later are /Create/Delete/Modify/Copy/Import/Export, which permit the user to perform the named operation on annotations.
	 * @param string $form Names specifying additional form-field-related usage rights for the document. Valid names are: /Add/Delete/FillIn/Import/Export/SubmitStandalone/SpawnTemplate
	 * @param string $signature Names specifying additional signature-related usage rights for the document. The only defined value is /Modify, which permits a user to apply a digital signature to an existing signature form field or clear a signed signature form field.
	 * @param string $ef Names specifying additional usage rights for named embedded files in the document. Valid names are /Create/Delete/Modify/Import, which permit the user to perform the named operation on named embedded files
	 * Names specifying additional embedded-files-related usage rights for the document.
	 * @param string $formex Names specifying additional form-field-related usage rights. The only valid name is BarcodePlaintext, which permits text form field data to be encoded as a plaintext two-dimensional barcode.
	 * @public
	 * @author Nicola Asuni
	 * @since 2.9.000 (2008-03-26)
	 */
	public function setUserRights(
			$enable=true,
			$document='/FullSave',
			$annots='/Create/Delete/Modify/Copy/Import/Export',
			$form='/Add/Delete/FillIn/Import/Export/SubmitStandalone/SpawnTemplate',
			$signature='/Modify',
			$ef='/Create/Delete/Modify/Import',
			$formex='') {
		$this->ur['enabled'] = $enable;
		$this->ur['document'] = $document;
		$this->ur['annots'] = $annots;
		$this->ur['form'] = $form;
		$this->ur['signature'] = $signature;
		$this->ur['ef'] = $ef;
		$this->ur['formex'] = $formex;
		if (!$this->sign) {
			$this->setSignature('', '', '', '', 0, array());
		}
	}

	/**
	 * Enable document signature (requires the OpenSSL Library).
	 * The digital signature improve document authenticity and integrity and allows o enable extra features on Acrobat Reader.
	 * To create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
	 * To export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
	 * To convert pfx certificate to pem: openssl pkcs12 -in tcpdf.pfx -out tcpdf.crt -nodes
	 * @param mixed $signing_cert signing certificate (string or filename prefixed with 'file://')
	 * @param mixed $private_key private key (string or filename prefixed with 'file://')
	 * @param string $private_key_password password
	 * @param string $extracerts specifies the name of a file containing a bunch of extra certificates to include in the signature which can for example be used to help the recipient to verify the certificate that you used.
	 * @param int $cert_type The access permissions granted for this document. Valid values shall be: 1 = No changes to the document shall be permitted; any change to the document shall invalidate the signature; 2 = Permitted changes shall be filling in forms, instantiating page templates, and signing; other changes shall invalidate the signature; 3 = Permitted changes shall be the same as for 2, as well as annotation creation, deletion, and modification; other changes shall invalidate the signature.
	 * @param array $info array of option information: Name, Location, Reason, ContactInfo.
	 * @param string $approval Enable approval signature eg. for PDF incremental update
	 * @public
	 * @author Nicola Asuni
	 * @since 4.6.005 (2009-04-24)
	 */
	public function setSignature($signing_cert='', $private_key='', $private_key_password='', $extracerts='', $cert_type=2, $info=array(), $approval='') {
		// to create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
		// to export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
		// to convert pfx certificate to pem: openssl
		//     OpenSSL> pkcs12 -in <cert.pfx> -out <cert.crt> -nodes
		$this->sign = true;
		++$this->n;
		$this->sig_obj_id = $this->n; // signature widget
		++$this->n; // signature object ($this->sig_obj_id + 1)
		$this->signature_data = array();
		if (strlen($signing_cert) == 0) {
			$this->Error('Please provide a certificate file and password!');
		}
		if (strlen($private_key) == 0) {
			$private_key = $signing_cert;
		}
		$this->signature_data['signcert'] = $signing_cert;
		$this->signature_data['privkey'] = $private_key;
		$this->signature_data['password'] = $private_key_password;
		$this->signature_data['extracerts'] = $extracerts;
		$this->signature_data['cert_type'] = $cert_type;
		$this->signature_data['info'] = $info;
		$this->signature_data['approval'] = $approval;
	}

	/**
	 * Set the digital signature appearance (a cliccable rectangle area to get signature properties)
	 * @param float $x Abscissa of the upper-left corner.
	 * @param float $y Ordinate of the upper-left corner.
	 * @param float $w Width of the signature area.
	 * @param float $h Height of the signature area.
	 * @param int $page option page number (if < 0 the current page is used).
	 * @param string $name Name of the signature.
	 * @public
	 * @author Nicola Asuni
	 * @since 5.3.011 (2010-06-17)
	 */
	public function setSignatureAppearance($x=0, $y=0, $w=0, $h=0, $page=-1, $name='') {
		$this->signature_appearance = $this->getSignatureAppearanceArray($x, $y, $w, $h, $page, $name);
	}

	/**
	 * Add an empty digital signature appearance (a cliccable rectangle area to get signature properties)
	 * @param float $x Abscissa of the upper-left corner.
	 * @param float $y Ordinate of the upper-left corner.
	 * @param float $w Width of the signature area.
	 * @param float $h Height of the signature area.
	 * @param int $page option page number (if < 0 the current page is used).
	 * @param string $name Name of the signature.
	 * @public
	 * @author Nicola Asuni
	 * @since 5.9.101 (2011-07-06)
	 */
	public function addEmptySignatureAppearance($x=0, $y=0, $w=0, $h=0, $page=-1, $name='') {
		++$this->n;
		$this->empty_signature_appearance[] = array('objid' => $this->n) + $this->getSignatureAppearanceArray($x, $y, $w, $h, $page, $name);
	}

	/**
	 * Get the array that defines the signature appearance (page and rectangle coordinates).
	 * @param float $x Abscissa of the upper-left corner.
	 * @param float $y Ordinate of the upper-left corner.
	 * @param float $w Width of the signature area.
	 * @param float $h Height of the signature area.
	 * @param int $page option page number (if < 0 the current page is used).
	 * @param string $name Name of the signature.
	 * @return array Array defining page and rectangle coordinates of signature appearance.
	 * @protected
	 * @author Nicola Asuni
	 * @since 5.9.101 (2011-07-06)
	 */
	protected function getSignatureAppearanceArray($x=0, $y=0, $w=0, $h=0, $page=-1, $name='') {
		$sigapp = array();
		if (($page < 1) OR ($page > $this->numpages)) {
			$sigapp['page'] = $this->page;
		} else {
			$sigapp['page'] = intval($page);
		}
		if (empty($name)) {
			$sigapp['name'] = 'Signature';
		} else {
			$sigapp['name'] = $name;
		}
		$a = $x * $this->k;
		$b = $this->pagedim[($sigapp['page'])]['h'] - (($y + $h) * $this->k);
		$c = $w * $this->k;
		$d = $h * $this->k;
		$sigapp['rect'] = sprintf('%F %F %F %F', $a, $b, ($a + $c), ($b + $d));
		return $sigapp;
	}

	/**
	 * Enable document timestamping (requires the OpenSSL Library).
	 * The trusted timestamping improve document security that means that no one should be able to change the document once it has been recorded.
	 * Use with digital signature only!
	 * @param string $tsa_host Time Stamping Authority (TSA) server (prefixed with 'https://')
	 * @param string $tsa_username Specifies the username for TSA authorization (optional) OR specifies the TSA authorization PEM file (see: example_66.php, optional)
	 * @param string $tsa_password Specifies the password for TSA authorization (optional)
	 * @param string $tsa_cert Specifies the location of TSA certificate for authorization (optional for cURL)
	 * @public
	 * @author Richard Stockinger
	 * @since 6.0.090 (2014-06-16)
	 */
	public function setTimeStamp($tsa_host='', $tsa_username='', $tsa_password='', $tsa_cert='') {
		$this->tsa_data = array();
		if (!function_exists('curl_init')) {
			$this->Error('Please enable cURL PHP extension!');
		}
		if (strlen($tsa_host) == 0) {
			$this->Error('Please specify the host of Time Stamping Authority (TSA)!');
		}
		$this->tsa_data['tsa_host'] = $tsa_host;
		if (is_file($tsa_username)) {
			$this->tsa_data['tsa_auth'] = $tsa_username;
		} else {
			$this->tsa_data['tsa_username'] = $tsa_username;
		}
		$this->tsa_data['tsa_password'] = $tsa_password;
		$this->tsa_data['tsa_cert'] = $tsa_cert;
		$this->tsa_timestamp = true;
	}

	/**
	 * NOT YET IMPLEMENTED
	 * Request TSA for a timestamp
	 * @param string $signature Digital signature as binary string
	 * @return string Timestamped digital signature
	 * @protected
	 * @author Richard Stockinger
	 * @since 6.0.090 (2014-06-16)
	 */
	protected function applyTSA($signature) {
		if (!$this->tsa_timestamp) {
			return $signature;
		}
		//@TODO: implement this feature
		return $signature;
	}

	/**
	 * Create a new page group.
	 * NOTE: call this function before calling AddPage()
	 * @param int|null $page starting group page (leave empty for next page).
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function startPageGroup($page=null) {
		if (empty($page)) {
			$page = $this->page + 1;
		}
		$this->newpagegroup[$page] = sizeof($this->newpagegroup) + 1;
	}

	/**
	 * Set the starting page number.
	 * @param int $num Starting page number.
	 * @since 5.9.093 (2011-06-16)
	 * @public
	 */
	public function setStartingPageNumber($num=1) {
		$this->starting_page_number = max(0, intval($num));
	}

	/**
	 * Returns the string alias used right align page numbers.
	 * If the current font is unicode type, the returned string wil contain an additional open curly brace.
	 * @return string
	 * @since 5.9.099 (2011-06-27)
	 * @public
	 */
	public function getAliasRightShift() {
		// calculate aproximatively the ratio between widths of aliases and replacements.
		$ref = '{'.LIMEPDF_STATIC::$alias_right_shift.'}{'.LIMEPDF_STATIC::$alias_tot_pages.'}{'.LIMEPDF_STATIC::$alias_num_page.'}';
		$rep = str_repeat(' ', $this->GetNumChars($ref));
		$wrep = $this->GetStringWidth($rep);
		if ($wrep > 0) {
			$wdiff = max(1, ($this->GetStringWidth($ref) / $wrep));
		} else {
			$wdiff = 1;
		}
		$sdiff = sprintf('%F', $wdiff);
		$alias = LIMEPDF_STATIC::$alias_right_shift.$sdiff.'}';
		if ($this->isUnicodeFont()) {
			$alias = '{'.$alias;
		}
		return $alias;
	}

	/**
	 * Returns the string alias used for the total number of pages.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the total number of pages in the document.
	 * @return string
	 * @since 4.0.018 (2008-08-08)
	 * @public
	 */
	public function getAliasNbPages() {
		if ($this->isUnicodeFont()) {
			return '{'.LIMEPDF_STATIC::$alias_tot_pages.'}';
		}
		return LIMEPDF_STATIC::$alias_tot_pages;
	}

	/**
	 * Returns the string alias used for the page number.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the page number.
	 * @return string
	 * @since 4.5.000 (2009-01-02)
	 * @public
	 */
	public function getAliasNumPage() {
		if ($this->isUnicodeFont()) {
			return '{'.LIMEPDF_STATIC::$alias_num_page.'}';
		}
		return LIMEPDF_STATIC::$alias_num_page;
	}

	/**
	 * Return the alias for the total number of pages in the current page group.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the total number of pages in this group.
	 * @return string alias of the current page group
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function getPageGroupAlias() {
		if ($this->isUnicodeFont()) {
			return '{'.LIMEPDF_STATIC::$alias_group_tot_pages.'}';
		}
		return LIMEPDF_STATIC::$alias_group_tot_pages;
	}

	/**
	 * Return the alias for the page number on the current page group.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the page number (relative to the belonging group).
	 * @return string alias of the current page group
	 * @public
	 * @since 4.5.000 (2009-01-02)
	 */
	public function getPageNumGroupAlias() {
		if ($this->isUnicodeFont()) {
			return '{'.LIMEPDF_STATIC::$alias_group_num_page.'}';
		}
		return LIMEPDF_STATIC::$alias_group_num_page;
	}

	/**
	 * Return the current page in the group.
	 * @return int current page in the group
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function getGroupPageNo() {
		return $this->pagegroups[$this->currpagegroup];
	}

	/**
	 * Returns the current group page number formatted as a string.
	 * @public
	 * @since 4.3.003 (2008-11-18)
	 * @see PaneNo(), formatPageNumber()
	 */
	public function getGroupPageNoFormatted() {
		return LIMEPDF_STATIC::formatPageNumber($this->getGroupPageNo());
	}

	/**
	 * Returns the current page number formatted as a string.
	 * @public
	 * @since 4.2.005 (2008-11-06)
	 * @see PaneNo(), formatPageNumber()
	 */
	public function PageNoFormatted() {
		return LIMEPDF_STATIC::formatPageNumber($this->PageNo());
	}


	/**
	 * Start a new pdf layer.
	 * @param string $name Layer name (only a-z letters and numbers). Leave empty for automatic name.
	 * @param boolean|null $print Set to TRUE to print this layer, FALSE to not print and NULL to not set this option
	 * @param boolean $view Set to true to view this layer.
	 * @param boolean $lock If true lock the layer
	 * @public
	 * @since 5.9.102 (2011-07-13)
	 */
	public function startLayer($name='', $print=true, $view=true, $lock=true) {
		if ($this->state != 2) {
			return;
		}
		$layer = sprintf('LYR%03d', (count($this->pdflayers) + 1));
		if (empty($name)) {
			$name = $layer;
		} else {
			$name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
		}
		$this->pdflayers[] = array('layer' => $layer, 'name' => $name, 'print' => $print, 'view' => $view, 'lock' => $lock);
		$this->openMarkedContent = true;
		$this->_out('/OC /'.$layer.' BDC');
	}

	/**
	 * End the current PDF layer.
	 * @public
	 * @since 5.9.102 (2011-07-13)
	 */
	public function endLayer() {
		if ($this->state != 2) {
			return;
		}
		if ($this->openMarkedContent) {
			// close existing open marked-content layer
			$this->_out('EMC');
			$this->openMarkedContent = false;
		}
	}

	/**
	 * Set the visibility of the successive elements.
	 * This can be useful, for instance, to put a background
	 * image or color that will show on screen but won't print.
	 * @param string $v visibility mode. Legal values are: all, print, screen or view.
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function setVisibility($v) {
		if ($this->state != 2) {
			return;
		}
		$this->endLayer();
		switch($v) {
			case 'print': {
				$this->startLayer('Print', true, false);
				break;
			}
			case 'view':
			case 'screen': {
				$this->startLayer('View', false, true);
				break;
			}
			case 'all': {
				$this->_out('');
				break;
			}
			default: {
				$this->Error('Incorrect visibility: '.$v);
				break;
			}
		}
	}

	/**
	 * Add transparency parameters to the current extgstate
	 * @param array $parms parameters
	 * @return int|void the number of extgstates
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function addExtGState($parms) {
		if (($this->pdfa_mode && $this->pdfa_version < 2) || ($this->state != 2)) {
			// transparency is not allowed in PDF/A-1 mode
			return;
		}
		// check if this ExtGState already exist
		foreach ($this->extgstates as $i => $ext) {
			if ($ext['parms'] == $parms) {
				if ($this->inxobj) {
					// we are inside an XObject template
					$this->xobjects[$this->xobjid]['extgstates'][$i] = $ext;
				}
				// return reference to existing ExtGState
				return $i;
			}
		}
		$n = (count($this->extgstates) + 1);
		$this->extgstates[$n] = array('parms' => $parms);
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['extgstates'][$n] = $this->extgstates[$n];
		}
		return $n;
	}

	/**
	 * Add an extgstate
	 * @param int $gs extgstate
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function setExtGState($gs) {
		if (($this->pdfa_mode && $this->pdfa_version < 2) OR ($this->state != 2)) {
			// transparency is not allowed in PDF/A-1 mode
			return;
		}
		$this->_out(sprintf('/GS%d gs', $gs));
	}

	/**
	 * Set overprint mode for stroking (OP) and non-stroking (op) painting operations.
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @param boolean $stroking If true apply overprint for stroking operations.
	 * @param boolean|null $nonstroking If true apply overprint for painting operations other than stroking.
	 * @param integer $mode Overprint mode: (0 = each source colour component value replaces the value previously painted for the corresponding device colorant; 1 = a tint value of 0.0 for a source colour component shall leave the corresponding component of the previously painted colour unchanged).
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function setOverprint($stroking=true, $nonstroking=null, $mode=0) {
		if ($this->state != 2) {
			return;
		}
		$stroking = $stroking ? true : false;
		if (LIMEPDF_STATIC::empty_string($nonstroking)) {
			// default value if not set
			$nonstroking = $stroking;
		} else {
			$nonstroking = $nonstroking ? true : false;
		}
		if (($mode != 0) AND ($mode != 1)) {
			$mode = 0;
		}
		$this->overprint = array('OP' => $stroking, 'op' => $nonstroking, 'OPM' => $mode);
		$gs = $this->addExtGState($this->overprint);
		$this->setExtGState($gs);
	}

	/**
	 * Get the overprint mode array (OP, op, OPM).
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @return array<string,bool|int>
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getOverprint() {
		return $this->overprint;
	}

	/**
	 * Set alpha for stroking (CA) and non-stroking (ca) operations.
	 * @param float $stroking Alpha value for stroking operations: real value from 0 (transparent) to 1 (opaque).
	 * @param string $bm blend mode, one of the following: Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn, HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
	 * @param float|null $nonstroking Alpha value for non-stroking operations: real value from 0 (transparent) to 1 (opaque).
	 * @param boolean $ais
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function setAlpha($stroking=1, $bm='Normal', $nonstroking=null, $ais=false) {
		if ($this->pdfa_mode && $this->pdfa_version < 2) {
			// transparency is not allowed in PDF/A-1 mode
			return;
		}
		$stroking = floatval($stroking);
		if (LIMEPDF_STATIC::empty_string($nonstroking)) {
			// default value if not set
			$nonstroking = $stroking;
		} else {
			$nonstroking = floatval($nonstroking);
		}
		if ($bm[0] == '/') {
			// remove trailing slash
			$bm = substr($bm, 1);
		}
		if (!in_array($bm, array('Normal', 'Multiply', 'Screen', 'Overlay', 'Darken', 'Lighten', 'ColorDodge', 'ColorBurn', 'HardLight', 'SoftLight', 'Difference', 'Exclusion', 'Hue', 'Saturation', 'Color', 'Luminosity'))) {
			$bm = 'Normal';
		}
		$ais = $ais ? true : false;
		$this->alpha = array('CA' => $stroking, 'ca' => $nonstroking, 'BM' => '/'.$bm, 'AIS' => $ais);
		$gs = $this->addExtGState($this->alpha);
		$this->setExtGState($gs);
	}

	/**
	 * Get the alpha mode array (CA, ca, BM, AIS).
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @return array<string,bool|string>
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getAlpha() {
		return $this->alpha;
	}

	/**
	 * Set the default JPEG compression quality (1-100)
	 * @param int $quality JPEG quality, integer between 1 and 100
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function setJPEGQuality($quality) {
		if (($quality < 1) OR ($quality > 100)) {
			$quality = 75;
		}
		$this->jpeg_quality = intval($quality);
	}

	/**
	 * Set the default number of columns in a row for HTML tables.
	 * @param int $cols number of columns
	 * @public
	 * @since 3.0.014 (2008-06-04)
	 */
	public function setDefaultTableColumns($cols=4) {
		$this->default_table_columns = intval($cols);
	}

	/**
	 * Set the height of the cell (line height) respect the font height.
	 * @param float $h cell proportion respect font height (typical value = 1.25).
	 * @public
	 * @since 3.0.014 (2008-06-04)
	 */
	public function setCellHeightRatio($h) {
		$this->cell_height_ratio = $h;
	}

	/**
	 * return the height of cell repect font height.
	 * @public
	 * @return float
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getCellHeightRatio() {
		return $this->cell_height_ratio;
	}

	/**
	 * Set the PDF version (check PDF reference for valid values).
	 * @param string $version PDF document version.
	 * @public
	 * @since 3.1.000 (2008-06-09)
	 */
	public function setPDFVersion($version='1.7') {
		if ($this->pdfa_mode && $this->pdfa_version == 1 ) {
			// PDF/A-1 mode
			$this->PDFVersion = '1.4';
		} elseif ($this->pdfa_mode && $this->pdfa_version >= 2 ) {
            // PDF/A-2 mode
            $this->PDFVersion = '1.7';
        } else {
			$this->PDFVersion = $version;
		}
	}

	/**
	 * Set the viewer preferences dictionary controlling the way the document is to be presented on the screen or in print.
	 * (see Section 8.1 of PDF reference, "Viewer Preferences").
	 * <ul><li>HideToolbar boolean (Optional) A flag specifying whether to hide the viewer application's tool bars when the document is active. Default value: false.</li><li>HideMenubar boolean (Optional) A flag specifying whether to hide the viewer application's menu bar when the document is active. Default value: false.</li><li>HideWindowUI boolean (Optional) A flag specifying whether to hide user interface elements in the document's window (such as scroll bars and navigation controls), leaving only the document's contents displayed. Default value: false.</li><li>FitWindow boolean (Optional) A flag specifying whether to resize the document's window to fit the size of the first displayed page. Default value: false.</li><li>CenterWindow boolean (Optional) A flag specifying whether to position the document's window in the center of the screen. Default value: false.</li><li>DisplayDocTitle boolean (Optional; PDF 1.4) A flag specifying whether the window's title bar should display the document title taken from the Title entry of the document information dictionary (see Section 10.2.1, "Document Information Dictionary"). If false, the title bar should instead display the name of the PDF file containing the document. Default value: false.</li><li>NonFullScreenPageMode name (Optional) The document's page mode, specifying how to display the document on exiting full-screen mode:<ul><li>UseNone Neither document outline nor thumbnail images visible</li><li>UseOutlines Document outline visible</li><li>UseThumbs Thumbnail images visible</li><li>UseOC Optional content group panel visible</li></ul>This entry is meaningful only if the value of the PageMode entry in the catalog dictionary (see Section 3.6.1, "Document Catalog") is FullScreen; it is ignored otherwise. Default value: UseNone.</li><li>ViewArea name (Optional; PDF 1.4) The name of the page boundary representing the area of a page to be displayed when viewing the document on the screen. Valid values are (see Section 10.10.1, "Page Boundaries").:<ul><li>MediaBox</li><li>CropBox (default)</li><li>BleedBox</li><li>TrimBox</li><li>ArtBox</li></ul></li><li>ViewClip name (Optional; PDF 1.4) The name of the page boundary to which the contents of a page are to be clipped when viewing the document on the screen. Valid values are (see Section 10.10.1, "Page Boundaries").:<ul><li>MediaBox</li><li>CropBox (default)</li><li>BleedBox</li><li>TrimBox</li><li>ArtBox</li></ul></li><li>PrintArea name (Optional; PDF 1.4) The name of the page boundary representing the area of a page to be rendered when printing the document. Valid values are (see Section 10.10.1, "Page Boundaries").:<ul><li>MediaBox</li><li>CropBox (default)</li><li>BleedBox</li><li>TrimBox</li><li>ArtBox</li></ul></li><li>PrintClip name (Optional; PDF 1.4) The name of the page boundary to which the contents of a page are to be clipped when printing the document. Valid values are (see Section 10.10.1, "Page Boundaries").:<ul><li>MediaBox</li><li>CropBox (default)</li><li>BleedBox</li><li>TrimBox</li><li>ArtBox</li></ul></li><li>PrintScaling name (Optional; PDF 1.6) The page scaling option to be selected when a print dialog is displayed for this document. Valid values are: <ul><li>None, which indicates that the print dialog should reflect no page scaling</li><li>AppDefault (default), which indicates that applications should use the current print scaling</li></ul></li><li>Duplex name (Optional; PDF 1.7) The paper handling option to use when printing the file from the print dialog. The following values are valid:<ul><li>Simplex - Print single-sided</li><li>DuplexFlipShortEdge - Duplex and flip on the short edge of the sheet</li><li>DuplexFlipLongEdge - Duplex and flip on the long edge of the sheet</li></ul>Default value: none</li><li>PickTrayByPDFSize boolean (Optional; PDF 1.7) A flag specifying whether the PDF page size is used to select the input paper tray. This setting influences only the preset values used to populate the print dialog presented by a PDF viewer application. If PickTrayByPDFSize is true, the check box in the print dialog associated with input paper tray is checked. Note: This setting has no effect on Mac OS systems, which do not provide the ability to pick the input tray by size.</li><li>PrintPageRange array (Optional; PDF 1.7) The page numbers used to initialize the print dialog box when the file is printed. The first page of the PDF file is denoted by 1. Each pair consists of the first and last pages in the sub-range. An odd number of integers causes this entry to be ignored. Negative numbers cause the entire array to be ignored. Default value: as defined by PDF viewer application</li><li>NumCopies integer (Optional; PDF 1.7) The number of copies to be printed when the print dialog is opened for this file. Supported values are the integers 2 through 5. Values outside this range are ignored. Default value: as defined by PDF viewer application, but typically 1</li></ul>
	 * @param array $preferences array of options.
	 * @author Nicola Asuni
	 * @public
	 * @since 3.1.000 (2008-06-09)
	 */
	public function setViewerPreferences($preferences) {
		$this->viewer_preferences = $preferences;
	}

	/**
	 * Paints color transition registration bars
	 * @param float $x abscissa of the top left corner of the rectangle.
	 * @param float $y ordinate of the top left corner of the rectangle.
	 * @param float $w width of the rectangle.
	 * @param float $h height of the rectangle.
	 * @param boolean $transition if true prints tcolor transitions to white.
	 * @param boolean $vertical if true prints bar vertically.
	 * @param string $colors colors to print separated by comma. Valid values are: A,W,R,G,B,C,M,Y,K,RGB,CMYK,ALL,ALLSPOT,<SPOT_COLOR_NAME>. Where: A = grayscale black, W = grayscale white, R = RGB red, G RGB green, B RGB blue, C = CMYK cyan, M = CMYK magenta, Y = CMYK yellow, K = CMYK key/black, RGB = RGB registration color, CMYK = CMYK registration color, ALL = Spot registration color, ALLSPOT = print all defined spot colors, <SPOT_COLOR_NAME> = name of the spot color to print.
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-26)
	 * @public
	 */
	public function colorRegistrationBar($x, $y, $w, $h, $transition=true, $vertical=false, $colors='A,R,G,B,C,M,Y,K') {
		if (strpos($colors, 'ALLSPOT') !== false) {
			// expand spot colors
			$spot_colors = '';
			foreach ($this->spot_colors as $spot_color_name => $v) {
				$spot_colors .= ','.$spot_color_name;
			}
			if (!empty($spot_colors)) {
				$spot_colors = substr($spot_colors, 1);
				$colors = str_replace('ALLSPOT', $spot_colors, $colors);
			} else {
				$colors = str_replace('ALLSPOT', 'NONE', $colors);
			}
		}
		$bars = explode(',', $colors);
		$numbars = count($bars); // number of bars to print
		if ($numbars <= 0) {
			return;
		}
		// set bar measures
		if ($vertical) {
			$coords = array(0, 0, 0, 1);
			$wb = $w / $numbars; // bar width
			$hb = $h; // bar height
			$xd = $wb; // delta x
			$yd = 0; // delta y
		} else {
			$coords = array(1, 0, 0, 0);
			$wb = $w; // bar width
			$hb = $h / $numbars; // bar height
			$xd = 0; // delta x
			$yd = $hb; // delta y
		}
		$xb = $x;
		$yb = $y;
		foreach ($bars as $col) {
			switch ($col) {
				// set transition colors
				case 'A': { // BLACK (GRAYSCALE)
					$col_a = array(255);
					$col_b = array(0);
					break;
				}
				case 'W': { // WHITE (GRAYSCALE)
					$col_a = array(0);
					$col_b = array(255);
					break;
				}
				case 'R': { // RED (RGB)
					$col_a = array(255,255,255);
					$col_b = array(255,0,0);
					break;
				}
				case 'G': { // GREEN (RGB)
					$col_a = array(255,255,255);
					$col_b = array(0,255,0);
					break;
				}
				case 'B': { // BLUE (RGB)
					$col_a = array(255,255,255);
					$col_b = array(0,0,255);
					break;
				}
				case 'C': { // CYAN (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(100,0,0,0);
					break;
				}
				case 'M': { // MAGENTA (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(0,100,0,0);
					break;
				}
				case 'Y': { // YELLOW (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(0,0,100,0);
					break;
				}
				case 'K': { // KEY - BLACK (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(0,0,0,100);
					break;
				}
				case 'RGB': { // BLACK REGISTRATION (RGB)
					$col_a = array(255,255,255);
					$col_b = array(0,0,0);
					break;
				}
				case 'CMYK': { // BLACK REGISTRATION (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(100,100,100,100);
					break;
				}
				case 'ALL': { // SPOT COLOR REGISTRATION
					$col_a = array(0,0,0,0,'None');
					$col_b = array(100,100,100,100,'All');
					break;
				}
				case 'NONE': { // SKIP THIS COLOR
					$col_a = array(0,0,0,0,'None');
					$col_b = array(0,0,0,0,'None');
					break;
				}
				default: { // SPECIFIC SPOT COLOR NAME
					$col_a = array(0,0,0,0,'None');
					$col_b = LIMEPDF_COLORS::getSpotColor($col, $this->spot_colors);
					if ($col_b === false) {
						// in case of error defaults to the registration color
						$col_b = array(100,100,100,100,'All');
					}
					break;
				}
			}
			if ($col != 'NONE') {
				if ($transition) {
					// color gradient
					$this->LinearGradient($xb, $yb, $wb, $hb, $col_a, $col_b, $coords);
				} else {
					$this->setFillColorArray($col_b);
					// colored rectangle
					$this->Rect($xb, $yb, $wb, $hb, 'F', array());
				}
				$xb += $xd;
				$yb += $yd;
			}
		}
	}

	/**
	 * Paints crop marks.
	 * @param float $x abscissa of the crop mark center.
	 * @param float $y ordinate of the crop mark center.
	 * @param float $w width of the crop mark.
	 * @param float $h height of the crop mark.
	 * @param string $type type of crop mark, one symbol per type separated by comma: T = TOP, F = BOTTOM, L = LEFT, R = RIGHT, TL = A = TOP-LEFT, TR = B = TOP-RIGHT, BL = C = BOTTOM-LEFT, BR = D = BOTTOM-RIGHT.
	 * @param array $color crop mark color (default spot registration color).
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-26)
	 * @public
	 */
	public function cropMark($x, $y, $w, $h, $type='T,R,B,L', $color=array(100,100,100,100,'All')) {
		$this->setLineStyle(array('width' => (0.5 / $this->k), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $color));
		$type = strtoupper($type);
		$type = preg_replace('/[^A-Z\-\,]*/', '', $type);
		// split type in single components
		$type = str_replace('-', ',', $type);
		$type = str_replace('TL', 'T,L', $type);
		$type = str_replace('TR', 'T,R', $type);
		$type = str_replace('BL', 'F,L', $type);
		$type = str_replace('BR', 'F,R', $type);
		$type = str_replace('A', 'T,L', $type);
		$type = str_replace('B', 'T,R', $type);
		$type = str_replace('T,RO', 'BO', $type);
		$type = str_replace('C', 'F,L', $type);
		$type = str_replace('D', 'F,R', $type);
		$crops = explode(',', strtoupper($type));
		// remove duplicates
		$crops = array_unique($crops);
		$dw = ($w / 4); // horizontal space to leave before the intersection point
		$dh = ($h / 4); // vertical space to leave before the intersection point
		foreach ($crops as $crop) {
			switch ($crop) {
				case 'T':
				case 'TOP': {
					$x1 = $x;
					$y1 = ($y - $h);
					$x2 = $x;
					$y2 = ($y - $dh);
					break;
				}
				case 'F':
				case 'BOTTOM': {
					$x1 = $x;
					$y1 = ($y + $dh);
					$x2 = $x;
					$y2 = ($y + $h);
					break;
				}
				case 'L':
				case 'LEFT': {
					$x1 = ($x - $w);
					$y1 = $y;
					$x2 = ($x - $dw);
					$y2 = $y;
					break;
				}
				case 'R':
				case 'RIGHT': {
					$x1 = ($x + $dw);
					$y1 = $y;
					$x2 = ($x + $w);
					$y2 = $y;
					break;
				}
			}
			$this->Line($x1, $y1, $x2, $y2);
		}
	}

	/**
	 * Paints a registration mark
	 * @param float $x abscissa of the registration mark center.
	 * @param float $y ordinate of the registration mark center.
	 * @param float $r radius of the crop mark.
	 * @param boolean $double if true print two concentric crop marks.
	 * @param array $cola crop mark color (default spot registration color 'All').
	 * @param array $colb second crop mark color (default spot registration color 'None').
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-26)
	 * @public
	 */
	public function registrationMark($x, $y, $r, $double=false, $cola=array(100,100,100,100,'All'), $colb=array(0,0,0,0,'None')) {
		$line_style = array('width' => max((0.5 / $this->k),($r / 30)), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $cola);
		$this->setFillColorArray($cola);
		$this->PieSector($x, $y, $r, 90, 180, 'F');
		$this->PieSector($x, $y, $r, 270, 360, 'F');
		$this->Circle($x, $y, $r, 0, 360, 'C', $line_style, array(), 8);
		if ($double) {
			$ri = $r * 0.5;
			$this->setFillColorArray($colb);
			$this->PieSector($x, $y, $ri, 90, 180, 'F');
			$this->PieSector($x, $y, $ri, 270, 360, 'F');
			$this->setFillColorArray($cola);
			$this->PieSector($x, $y, $ri, 0, 90, 'F');
			$this->PieSector($x, $y, $ri, 180, 270, 'F');
			$this->Circle($x, $y, $ri, 0, 360, 'C', $line_style, array(), 8);
		}
	}

	/**
	 * Paints a CMYK registration mark
	 * @param float $x abscissa of the registration mark center.
	 * @param float $y ordinate of the registration mark center.
	 * @param float $r radius of the crop mark.
	 * @author Nicola Asuni
	 * @since 6.0.038 (2013-09-30)
	 * @public
	 */
	public function registrationMarkCMYK($x, $y, $r) {
		// line width
		$lw = max((0.5 / $this->k),($r / 8));
		// internal radius
		$ri = ($r * 0.6);
		// external radius
		$re = ($r * 1.3);
		// Cyan
		$this->setFillColorArray(array(100,0,0,0));
		$this->PieSector($x, $y, $ri, 270, 360, 'F');
		// Magenta
		$this->setFillColorArray(array(0,100,0,0));
		$this->PieSector($x, $y, $ri, 0, 90, 'F');
		// Yellow
		$this->setFillColorArray(array(0,0,100,0));
		$this->PieSector($x, $y, $ri, 90, 180, 'F');
		// Key - black
		$this->setFillColorArray(array(0,0,0,100));
		$this->PieSector($x, $y, $ri, 180, 270, 'F');
		// registration color
		$line_style = array('width' => $lw, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(100,100,100,100,'All'));
		$this->setFillColorArray(array(100,100,100,100,'All'));
		// external circle
		$this->Circle($x, $y, $r, 0, 360, 'C', $line_style, array(), 8);
		// cross lines
		$this->Line($x, ($y - $re), $x, ($y - $ri));
		$this->Line($x, ($y + $ri), $x, ($y + $re));
		$this->Line(($x - $re), $y, ($x - $ri), $y);
		$this->Line(($x + $ri), $y, ($x + $re), $y);
	}

	/**
	 * Paints a linear colour gradient.
	 * @param float $x abscissa of the top left corner of the rectangle.
	 * @param float $y ordinate of the top left corner of the rectangle.
	 * @param float $w width of the rectangle.
	 * @param float $h height of the rectangle.
	 * @param array $col1 first color (Grayscale, RGB or CMYK components).
	 * @param array $col2 second color (Grayscale, RGB or CMYK components).
	 * @param array $coords array of the form (x1, y1, x2, y2) which defines the gradient vector (see linear_gradient_coords.jpg). The default value is from left to right (x1=0, y1=0, x2=1, y2=0).
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function LinearGradient($x, $y, $w, $h, $col1=array(), $col2=array(), $coords=array(0,0,1,0)) {
		$this->Clip($x, $y, $w, $h);
		$this->Gradient(2, $coords, array(array('color' => $col1, 'offset' => 0, 'exponent' => 1), array('color' => $col2, 'offset' => 1, 'exponent' => 1)), array(), false);
	}

	/**
	 * Paints a radial colour gradient.
	 * @param float $x abscissa of the top left corner of the rectangle.
	 * @param float $y ordinate of the top left corner of the rectangle.
	 * @param float $w width of the rectangle.
	 * @param float $h height of the rectangle.
	 * @param array $col1 first color (Grayscale, RGB or CMYK components).
	 * @param array $col2 second color (Grayscale, RGB or CMYK components).
	 * @param array $coords array of the form (fx, fy, cx, cy, r) where (fx, fy) is the starting point of the gradient with color1, (cx, cy) is the center of the circle with color2, and r is the radius of the circle (see radial_gradient_coords.jpg). (fx, fy) should be inside the circle, otherwise some areas will not be defined.
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function RadialGradient($x, $y, $w, $h, $col1=array(), $col2=array(), $coords=array(0.5,0.5,0.5,0.5,1)) {
		$this->Clip($x, $y, $w, $h);
		$this->Gradient(3, $coords, array(array('color' => $col1, 'offset' => 0, 'exponent' => 1), array('color' => $col2, 'offset' => 1, 'exponent' => 1)), array(), false);
	}

	/**
	 * Paints a coons patch mesh.
	 * @param float $x abscissa of the top left corner of the rectangle.
	 * @param float $y ordinate of the top left corner of the rectangle.
	 * @param float $w width of the rectangle.
	 * @param float $h height of the rectangle.
	 * @param array $col1 first color (lower left corner) (RGB components).
	 * @param array $col2 second color (lower right corner) (RGB components).
	 * @param array $col3 third color (upper right corner) (RGB components).
	 * @param array $col4 fourth color (upper left corner) (RGB components).
	 * @param array $coords <ul><li>for one patch mesh: array(float x1, float y1, .... float x12, float y12): 12 pairs of coordinates (normally from 0 to 1) which specify the Bezier control points that define the patch. First pair is the lower left edge point, next is its right control point (control point 2). Then the other points are defined in the order: control point 1, edge point, control point 2 going counter-clockwise around the patch. Last (x12, y12) is the first edge point's left control point (control point 1).</li><li>for two or more patch meshes: array[number of patches]: arrays with the following keys for each patch: f: where to put that patch (0 = first patch, 1, 2, 3 = right, top and left of precedent patch - I didn't figure this out completely - just try and error ;-) points: 12 pairs of coordinates of the Bezier control points as above for the first patch, 8 pairs of coordinates for the following patches, ignoring the coordinates already defined by the precedent patch (I also didn't figure out the order of these - also: try and see what's happening) colors: must be 4 colors for the first patch, 2 colors for the following patches</li></ul>
	 * @param array $coords_min minimum value used by the coordinates. If a coordinate's value is smaller than this it will be cut to coords_min. default: 0
	 * @param array $coords_max maximum value used by the coordinates. If a coordinate's value is greater than this it will be cut to coords_max. default: 1
	 * @param boolean $antialias A flag indicating whether to filter the shading function to prevent aliasing artifacts.
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function CoonsPatchMesh($x, $y, $w, $h, $col1=array(), $col2=array(), $col3=array(), $col4=array(), $coords=array(0.00,0.0,0.33,0.00,0.67,0.00,1.00,0.00,1.00,0.33,1.00,0.67,1.00,1.00,0.67,1.00,0.33,1.00,0.00,1.00,0.00,0.67,0.00,0.33), $coords_min=0, $coords_max=1, $antialias=false) {
		if (($this->pdfa_mode && $this->pdfa_version < 2) OR ($this->state != 2)) {
			return;
		}
		$this->Clip($x, $y, $w, $h);
		$n = count($this->gradients) + 1;
		$this->gradients[$n] = array();
		$this->gradients[$n]['type'] = 6; //coons patch mesh
		$this->gradients[$n]['coords'] = array();
		$this->gradients[$n]['antialias'] = $antialias;
		$this->gradients[$n]['colors'] = array();
		$this->gradients[$n]['transparency'] = false;
		//check the coords array if it is the simple array or the multi patch array
		if (!isset($coords[0]['f'])) {
			//simple array -> convert to multi patch array
			if (!isset($col1[1])) {
				$col1[1] = $col1[2] = $col1[0];
			}
			if (!isset($col2[1])) {
				$col2[1] = $col2[2] = $col2[0];
			}
			if (!isset($col3[1])) {
				$col3[1] = $col3[2] = $col3[0];
			}
			if (!isset($col4[1])) {
				$col4[1] = $col4[2] = $col4[0];
			}
			$patch_array[0]['f'] = 0;
			$patch_array[0]['points'] = $coords;
			$patch_array[0]['colors'][0]['r'] = $col1[0];
			$patch_array[0]['colors'][0]['g'] = $col1[1];
			$patch_array[0]['colors'][0]['b'] = $col1[2];
			$patch_array[0]['colors'][1]['r'] = $col2[0];
			$patch_array[0]['colors'][1]['g'] = $col2[1];
			$patch_array[0]['colors'][1]['b'] = $col2[2];
			$patch_array[0]['colors'][2]['r'] = $col3[0];
			$patch_array[0]['colors'][2]['g'] = $col3[1];
			$patch_array[0]['colors'][2]['b'] = $col3[2];
			$patch_array[0]['colors'][3]['r'] = $col4[0];
			$patch_array[0]['colors'][3]['g'] = $col4[1];
			$patch_array[0]['colors'][3]['b'] = $col4[2];
		} else {
			//multi patch array
			$patch_array = $coords;
		}
		$bpcd = 65535; //16 bits per coordinate
		//build the data stream
		$this->gradients[$n]['stream'] = '';
		$count_patch = count($patch_array);
		for ($i=0; $i < $count_patch; ++$i) {
			$this->gradients[$n]['stream'] .= chr($patch_array[$i]['f']); //start with the edge flag as 8 bit
			$count_points = count($patch_array[$i]['points']);
			for ($j=0; $j < $count_points; ++$j) {
				//each point as 16 bit
				$patch_array[$i]['points'][$j] = (($patch_array[$i]['points'][$j] - $coords_min) / ($coords_max - $coords_min)) * $bpcd;
				if ($patch_array[$i]['points'][$j] < 0) {
					$patch_array[$i]['points'][$j] = 0;
				}
				if ($patch_array[$i]['points'][$j] > $bpcd) {
					$patch_array[$i]['points'][$j] = $bpcd;
				}
				$this->gradients[$n]['stream'] .= chr((int) floor($patch_array[$i]['points'][$j] / 256));
				$this->gradients[$n]['stream'] .= chr((int) floor(intval($patch_array[$i]['points'][$j]) % 256));
			}
			$count_cols = count($patch_array[$i]['colors']);
			for ($j=0; $j < $count_cols; ++$j) {
				//each color component as 8 bit
				$this->gradients[$n]['stream'] .= chr($patch_array[$i]['colors'][$j]['r']);
				$this->gradients[$n]['stream'] .= chr($patch_array[$i]['colors'][$j]['g']);
				$this->gradients[$n]['stream'] .= chr($patch_array[$i]['colors'][$j]['b']);
			}
		}
		//paint the gradient
		$this->_out('/Sh'.$n.' sh');
		//restore previous Graphic State
		$this->_outRestoreGraphicsState();
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['gradients'][$n] = $this->gradients[$n];
		}
	}

	/**
	 * Set a rectangular clipping area.
	 * @param float $x abscissa of the top left corner of the rectangle (or top right corner for RTL mode).
	 * @param float $y ordinate of the top left corner of the rectangle.
	 * @param float $w width of the rectangle.
	 * @param float $h height of the rectangle.
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @protected
	 */
	protected function Clip($x, $y, $w, $h) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rtl) {
			$x = $this->w - $x - $w;
		}
		//save current Graphic State
		$s = 'q';
		//set clipping area
		$s .= sprintf(' %F %F %F %F re W n', $x*$this->k, ($this->h-$y)*$this->k, $w*$this->k, -$h*$this->k);
		//set up transformation matrix for gradient
		$s .= sprintf(' %F 0 0 %F %F %F cm', $w*$this->k, $h*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k);
		$this->_out($s);
	}

	/**
	 * Output gradient.
	 * @param int $type type of gradient (1 Function-based shading; 2 Axial shading; 3 Radial shading; 4 Free-form Gouraud-shaded triangle mesh; 5 Lattice-form Gouraud-shaded triangle mesh; 6 Coons patch mesh; 7 Tensor-product patch mesh). (Not all types are currently supported)
	 * @param array $coords array of coordinates.
	 * @param array $stops array gradient color components: color = array of GRAY, RGB or CMYK color components; offset = (0 to 1) represents a location along the gradient vector; exponent = exponent of the exponential interpolation function (default = 1).
	 * @param array $background An array of colour components appropriate to the colour space, specifying a single background colour value.
	 * @param boolean $antialias A flag indicating whether to filter the shading function to prevent aliasing artifacts.
	 * @author Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function Gradient($type, $coords, $stops, $background=array(), $antialias=false) {
		if (($this->pdfa_mode && $this->pdfa_version < 2) OR ($this->state != 2)) {
			return;
		}
		$n = count($this->gradients) + 1;
		$this->gradients[$n] = array();
		$this->gradients[$n]['type'] = $type;
		$this->gradients[$n]['coords'] = $coords;
		$this->gradients[$n]['antialias'] = $antialias;
		$this->gradients[$n]['colors'] = array();
		$this->gradients[$n]['transparency'] = false;
		// color space
		$numcolspace = count($stops[0]['color']);
		$bcolor = array_values($background);
		switch($numcolspace) {
			case 5:   // SPOT
			case 4: { // CMYK
				$this->gradients[$n]['colspace'] = 'DeviceCMYK';
				if (!empty($background)) {
					$this->gradients[$n]['background'] = sprintf('%F %F %F %F', $bcolor[0]/100, $bcolor[1]/100, $bcolor[2]/100, $bcolor[3]/100);
				}
				break;
			}
			case 3: { // RGB
				$this->gradients[$n]['colspace'] = 'DeviceRGB';
				if (!empty($background)) {
					$this->gradients[$n]['background'] = sprintf('%F %F %F', $bcolor[0]/255, $bcolor[1]/255, $bcolor[2]/255);
				}
				break;
			}
			case 1: { // GRAY SCALE
				$this->gradients[$n]['colspace'] = 'DeviceGray';
				if (!empty($background)) {
					$this->gradients[$n]['background'] = sprintf('%F', $bcolor[0]/255);
				}
				break;
			}
		}
		$num_stops = count($stops);
		$last_stop_id = $num_stops - 1;
		foreach ($stops as $key => $stop) {
			$this->gradients[$n]['colors'][$key] = array();
			// offset represents a location along the gradient vector
			if (isset($stop['offset'])) {
				$this->gradients[$n]['colors'][$key]['offset'] = $stop['offset'];
			} else {
				if ($key == 0) {
					$this->gradients[$n]['colors'][$key]['offset'] = 0;
				} elseif ($key == $last_stop_id) {
					$this->gradients[$n]['colors'][$key]['offset'] = 1;
				} else {
					$offsetstep = (1 - $this->gradients[$n]['colors'][($key - 1)]['offset']) / ($num_stops - $key);
					$this->gradients[$n]['colors'][$key]['offset'] = $this->gradients[$n]['colors'][($key - 1)]['offset'] + $offsetstep;
				}
			}
			if (isset($stop['opacity'])) {
				$this->gradients[$n]['colors'][$key]['opacity'] = $stop['opacity'];
				if ((!($this->pdfa_mode && $this->pdfa_version < 2)) AND ($stop['opacity'] < 1)) {
					$this->gradients[$n]['transparency'] = true;
				}
			} else {
				$this->gradients[$n]['colors'][$key]['opacity'] = 1;
			}
			// exponent for the exponential interpolation function
			if (isset($stop['exponent'])) {
				$this->gradients[$n]['colors'][$key]['exponent'] = $stop['exponent'];
			} else {
				$this->gradients[$n]['colors'][$key]['exponent'] = 1;
			}
			// set colors
			$color = array_values($stop['color']);
			switch($numcolspace) {
				case 5:   // SPOT
				case 4: { // CMYK
					$this->gradients[$n]['colors'][$key]['color'] = sprintf('%F %F %F %F', $color[0]/100, $color[1]/100, $color[2]/100, $color[3]/100);
					break;
				}
				case 3: { // RGB
					$this->gradients[$n]['colors'][$key]['color'] = sprintf('%F %F %F', $color[0]/255, $color[1]/255, $color[2]/255);
					break;
				}
				case 1: { // GRAY SCALE
					$this->gradients[$n]['colors'][$key]['color'] = sprintf('%F', $color[0]/255);
					break;
				}
			}
		}
		if ($this->gradients[$n]['transparency']) {
			// paint luminosity gradient
			$this->_out('/TGS'.$n.' gs');
		}
		//paint the gradient
		$this->_out('/Sh'.$n.' sh');
		//restore previous Graphic State
		$this->_outRestoreGraphicsState();
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['gradients'][$n] = $this->gradients[$n];
		}
	}


	/**
	 * Draw the sector of a circle.
	 * It can be used for instance to render pie charts.
	 * @param float $xc abscissa of the center.
	 * @param float $yc ordinate of the center.
	 * @param float $r radius.
	 * @param float $a start angle (in degrees).
	 * @param float $b end angle (in degrees).
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param float $cw indicates whether to go clockwise (default: true).
	 * @param float $o origin of angles (0 for 3 o'clock, 90 for noon, 180 for 9 o'clock, 270 for 6 o'clock). Default: 90.
	 * @author Maxime Delorme, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function PieSector($xc, $yc, $r, $a, $b, $style='FD', $cw=true, $o=90) {
		$this->PieSectorXY($xc, $yc, $r, $r, $a, $b, $style, $cw, $o);
	}

	/**
	 * Draw the sector of an ellipse.
	 * It can be used for instance to render pie charts.
	 * @param float $xc abscissa of the center.
	 * @param float $yc ordinate of the center.
	 * @param float $rx the x-axis radius.
	 * @param float $ry the y-axis radius.
	 * @param float $a start angle (in degrees).
	 * @param float $b end angle (in degrees).
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param float $cw indicates whether to go clockwise.
	 * @param float $o origin of angles (0 for 3 o'clock, 90 for noon, 180 for 9 o'clock, 270 for 6 o'clock).
	 * @param integer $nc Number of curves used to draw a 90 degrees portion of arc.
	 * @author Maxime Delorme, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function PieSectorXY($xc, $yc, $rx, $ry, $a, $b, $style='FD', $cw=false, $o=0, $nc=2) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rtl) {
			$xc = ($this->w - $xc);
		}
		$op = LIMEPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		if ($cw) {
			$d = $b;
			$b = (360 - $a + $o);
			$a = (360 - $d + $o);
		} else {
			$b += $o;
			$a += $o;
		}
		$this->_outellipticalarc($xc, $yc, $rx, $ry, 0, $a, $b, true, $nc);
		$this->_out($op);
	}

	/**
	 * Embed vector-based Adobe Illustrator (AI) or AI-compatible EPS files.
	 * NOTE: EPS is not yet fully implemented, use the setRasterizeVectorImages() method to enable/disable rasterization of vector images using ImageMagick library.
	 * Only vector drawing is supported, not text or bitmap.
	 * Although the script was successfully tested with various AI format versions, best results are probably achieved with files that were exported in the AI3 format (tested with Illustrator CS2, Freehand MX and Photoshop CS2).
	 * @param string $file Name of the file containing the image or a '@' character followed by the EPS/AI data string.
	 * @param float|null $x Abscissa of the upper-left corner.
	 * @param float|null $y Ordinate of the upper-left corner.
	 * @param float $w Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param float $h Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param mixed $link URL or identifier returned by AddLink().
	 * @param boolean $useBoundingBox specifies whether to position the bounding box (true) or the complete canvas (false) at location (x,y). Default value is true.
	 * @param string $align Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param string $palign Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param mixed $border Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param boolean $fitonpage if true the image is resized to not exceed page dimensions.
	 * @param boolean $fixoutvals if true remove values outside the bounding box.
	 * @author Valentin Schmidt, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function ImageEps($file, $x=null, $y=null, $w=0, $h=0, $link='', $useBoundingBox=true, $align='', $palign='', $border=0, $fitonpage=false, $fixoutvals=false) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rasterize_vector_images AND ($w > 0) AND ($h > 0)) {
			// convert EPS to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'EPS', $link, $align, true, 300, $palign, false, false, $border, false, false, $fitonpage);
		}
		if (LIMEPDF_STATIC::empty_string($x)) {
			$x = $this->x;
		}
		if (LIMEPDF_STATIC::empty_string($y)) {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		$k = $this->k;
		if ($file[0] === '@') { // image from string
			$data = substr($file, 1);
		} else { // EPS/AI file
            $data = $this->getCachedFileContents($file);
		}
		if ($data === FALSE) {
			$this->Error('EPS file not found: '.$file);
		}
		$regs = array();
		// EPS/AI compatibility check (only checks files created by Adobe Illustrator!)
		preg_match("/%%Creator:([^\r\n]+)/", $data, $regs); # find Creator
		if (count($regs) > 1) {
			$version_str = trim($regs[1]); # e.g. "Adobe Illustrator(R) 8.0"
			if (strpos($version_str, 'Adobe Illustrator') !== false) {
				$versexp = explode(' ', $version_str);
				$version = (float)array_pop($versexp);
				if ($version >= 9) {
					$this->Error('This version of Adobe Illustrator file is not supported: '.$file);
				}
			}
		}
		// strip binary bytes in front of PS-header
		$start = strpos($data, '%!PS-Adobe');
		if ($start > 0) {
			$data = substr($data, $start);
		}
		// find BoundingBox params
		preg_match("/%%BoundingBox:([^\r\n]+)/", $data, $regs);
		if (count($regs) > 1) {
			list($x1, $y1, $x2, $y2) = explode(' ', trim($regs[1]));
		} else {
			$this->Error('No BoundingBox found in EPS/AI file: '.$file);
		}
		$start = strpos($data, '%%EndSetup');
		if ($start === false) {
			$start = strpos($data, '%%EndProlog');
		}
		if ($start === false) {
			$start = strpos($data, '%%BoundingBox');
		}
		$data = substr($data, $start);
		$end = strpos($data, '%%PageTrailer');
		if ($end===false) {
			$end = strpos($data, 'showpage');
		}
		if ($end) {
			$data = substr($data, 0, $end);
		}
		// calculate image width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			$w = ($x2 - $x1) / $k;
			$h = ($y2 - $y1) / $k;
		} elseif ($w <= 0) {
			$w = ($x2-$x1) / $k * ($h / (($y2 - $y1) / $k));
		} elseif ($h <= 0) {
			$h = ($y2 - $y1) / $k * ($w / (($x2 - $x1) / $k));
		}
		// fit the image on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		if ($this->rasterize_vector_images) {
			// convert EPS to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'EPS', $link, $align, true, 300, $palign, false, false, $border, false, false, $fitonpage);
		}
		// set scaling factors
		$scale_x = $w / (($x2 - $x1) / $k);
		$scale_y = $h / (($y2 - $y1) / $k);
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x - $w;
			}
			$this->img_rb_x = $ximg;
		} else {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x;
			}
			$this->img_rb_x = $ximg + $w;
		}
		if ($useBoundingBox) {
			$dx = $ximg * $k - $x1;
			$dy = $y * $k - $y1;
		} else {
			$dx = $ximg * $k;
			$dy = $y * $k;
		}
		// save the current graphic state
		$this->_out('q'.$this->epsmarker);
		// translate
		$this->_out(sprintf('%F %F %F %F %F %F cm', 1, 0, 0, 1, $dx, $dy + ($this->hPt - (2 * $y * $k) - ($y2 - $y1))));
		// scale
		$this->_out(sprintf('%F %F %F %F %F %F cm', $scale_x, 0, 0, $scale_y, $x1 * (1 - $scale_x), $y2 * (1 - $scale_y)));
		// handle pc/unix/mac line endings
		$lines = preg_split('/[\r\n]+/si', $data, -1, PREG_SPLIT_NO_EMPTY);
		$u=0;
		$cnt = count($lines);
		for ($i=0; $i < $cnt; ++$i) {
			$line = $lines[$i];
			if (($line == '') OR ($line[0] == '%')) {
				continue;
			}
			$len = strlen($line);
			// check for spot color names
			$color_name = '';
			if (strcasecmp('x', substr(trim($line), -1)) == 0) {
				if (preg_match('/\([^\)]*\)/', $line, $matches) > 0) {
					// extract spot color name
					$color_name = $matches[0];
					// remove color name from string
					$line = str_replace(' '.$color_name, '', $line);
					// remove pharentesis from color name
					$color_name = substr($color_name, 1, -1);
				}
			}
			$chunks = explode(' ', $line);
			$cmd = trim(array_pop($chunks));
			// RGB
			if (($cmd == 'Xa') OR ($cmd == 'XA')) {
				$b = array_pop($chunks);
				$g = array_pop($chunks);
				$r = array_pop($chunks);
				$this->_out(''.$r.' '.$g.' '.$b.' '.($cmd=='Xa'?'rg':'RG')); //substr($line, 0, -2).'rg' -> in EPS (AI8): c m y k r g b rg!
				continue;
			}
			$skip = false;
			if ($fixoutvals) {
				// check for values outside the bounding box
				switch ($cmd) {
					case 'm':
					case 'l':
					case 'L': {
						// skip values outside bounding box
						foreach ($chunks as $key => $val) {
							if ((($key % 2) == 0) AND (($val < $x1) OR ($val > $x2))) {
								$skip = true;
							} elseif ((($key % 2) != 0) AND (($val < $y1) OR ($val > $y2))) {
								$skip = true;
							}
						}
					}
				}
			}
			switch ($cmd) {
				case 'm':
				case 'l':
				case 'v':
				case 'y':
				case 'c':
				case 'k':
				case 'K':
				case 'g':
				case 'G':
				case 's':
				case 'S':
				case 'J':
				case 'j':
				case 'w':
				case 'M':
				case 'd':
				case 'n': {
					if ($skip) {
						break;
					}
					$this->_out($line);
					break;
				}
				case 'x': {// custom fill color
					if (empty($color_name)) {
						// CMYK color
						list($col_c, $col_m, $col_y, $col_k) = $chunks;
						$this->_out(''.$col_c.' '.$col_m.' '.$col_y.' '.$col_k.' k');
					} else {
						// Spot Color (CMYK + tint)
						list($col_c, $col_m, $col_y, $col_k, $col_t) = $chunks;
						$this->AddSpotColor($color_name, ($col_c * 100), ($col_m * 100), ($col_y * 100), ($col_k * 100));
						$color_cmd = sprintf('/CS%d cs %F scn', $this->spot_colors[$color_name]['i'], (1 - $col_t));
						$this->_out($color_cmd);
					}
					break;
				}
				case 'X': { // custom stroke color
					if (empty($color_name)) {
						// CMYK color
						list($col_c, $col_m, $col_y, $col_k) = $chunks;
						$this->_out(''.$col_c.' '.$col_m.' '.$col_y.' '.$col_k.' K');
					} else {
						// Spot Color (CMYK + tint)
						list($col_c, $col_m, $col_y, $col_k, $col_t) = $chunks;
						$this->AddSpotColor($color_name, ($col_c * 100), ($col_m * 100), ($col_y * 100), ($col_k * 100));
						$color_cmd = sprintf('/CS%d CS %F SCN', $this->spot_colors[$color_name]['i'], (1 - $col_t));
						$this->_out($color_cmd);
					}
					break;
				}
				case 'Y':
				case 'N':
				case 'V':
				case 'L':
				case 'C': {
					if ($skip) {
						break;
					}
					$line[($len - 1)] = strtolower($cmd);
					$this->_out($line);
					break;
				}
				case 'b':
				case 'B': {
					$this->_out($cmd . '*');
					break;
				}
				case 'f':
				case 'F': {
					if ($u > 0) {
						$isU = false;
						$max = min(($i + 5), $cnt);
						for ($j = ($i + 1); $j < $max; ++$j) {
							$isU = ($isU OR (($lines[$j] == 'U') OR ($lines[$j] == '*U')));
						}
						if ($isU) {
							$this->_out('f*');
						}
					} else {
						$this->_out('f*');
					}
					break;
				}
				case '*u': {
					++$u;
					break;
				}
				case '*U': {
					--$u;
					break;
				}
			}
		}
		// restore previous graphic state
		$this->_out($this->epsmarker.'Q');
		if (!empty($border)) {
			$bx = $this->x;
			$by = $this->y;
			$this->x = $ximg;
			if ($this->rtl) {
				$this->x += $w;
			}
			$this->y = $y;
			$this->Cell($w, $h, '', $border, 0, '', 0, '', 0, true);
			$this->x = $bx;
			$this->y = $by;
		}
		if ($link) {
			$this->Link($ximg, $y, $w, $h, $link, 0);
		}
		// set pointer to align the next text/objects
		switch($align) {
			case 'T':{
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M':{
				$this->y = $y + round($h/2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B':{
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N':{
				$this->setY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
	}

	/**
	 * Set document barcode.
	 * @param string $bc barcode
	 * @public
	 */
	public function setBarcode($bc='') {
		$this->barcode = $bc;
	}

	/**
	 * Get current barcode.
	 * @return string
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getBarcode() {
		return $this->barcode;
	}

	/**
	 * Print a Linear Barcode.
	 * @param string $code code to print
	 * @param string $type type of barcode (see tcpdf_barcodes_1d.php for supported formats).
	 * @param float|null $x x position in user units (null = current x position)
	 * @param float|null $y y position in user units (null = current y position)
	 * @param float|null $w width in user units (null = remaining page width)
	 * @param float|null $h height in user units (null = remaining page height)
	 * @param float|null $xres width of the smallest bar in user units (null = default value = 0.4mm)
	 * @param array $style array of options:<ul>
	 * <li>boolean $style['border'] if true prints a border</li>
	 * <li>int $style['padding'] padding to leave around the barcode in user units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['hpadding'] horizontal padding in user units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['vpadding'] vertical padding in user units (set to 'auto' for automatic padding)</li>
	 * <li>array $style['fgcolor'] color array for bars and text</li>
	 * <li>mixed $style['bgcolor'] color array for background (set to false for transparent)</li>
	 * <li>boolean $style['text'] if true prints text below the barcode</li>
	 * <li>string $style['label'] override default label</li>
	 * <li>string $style['font'] font name for text</li><li>int $style['fontsize'] font size for text</li>
	 * <li>int $style['stretchtext']: 0 = disabled; 1 = horizontal scaling only if necessary; 2 = forced horizontal scaling; 3 = character spacing only if necessary; 4 = forced character spacing.</li>
	 * <li>string $style['position'] horizontal position of the containing barcode cell on the page: L = left margin; C = center; R = right margin.</li>
	 * <li>string $style['align'] horizontal position of the barcode on the containing rectangle: L = left; C = center; R = right.</li>
	 * <li>string $style['stretch'] if true stretch the barcode to best fit the available width, otherwise uses $xres resolution for a single bar.</li>
	 * <li>string $style['fitwidth'] if true reduce the width to fit the barcode width + padding. When this option is enabled the 'stretch' option is automatically disabled.</li>
	 * <li>string $style['cellfitalign'] this option works only when 'fitwidth' is true and 'position' is unset or empty. Set the horizontal position of the containing barcode cell inside the specified rectangle: L = left; C = center; R = right.</li></ul>
	 * @param string $align Indicates the alignment of the pointer next to barcode insertion relative to barcode height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @author Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function write1DBarcode($code, $type, $x=null, $y=null, $w=null, $h=null, $xres=null, $style=array(), $align='') {
		if (LIMEPDF_STATIC::empty_string(trim($code))) {
			return;
		}
		require_once(dirname(__FILE__).'/tcpdf_barcodes_1d.php');
		// save current graphic settings
		$gvars = $this->getGraphicVars();
		// create new barcode object
		$barcodeobj = new TCPDFBarcode($code, $type);
		$arrcode = $barcodeobj->getBarcodeArray();
		if (empty($arrcode) OR ($arrcode['maxw'] <= 0)) {
			$this->Error('Error in 1D barcode string');
		}
		if ($arrcode['maxh'] <= 0) {
			$arrcode['maxh'] = 1;
		}
		// set default values
		if (!isset($style['position'])) {
			$style['position'] = '';
		} elseif ($style['position'] == 'S') {
			// keep this for backward compatibility
			$style['position'] = '';
			$style['stretch'] = true;
		}
		if (!isset($style['fitwidth'])) {
			if (!isset($style['stretch'])) {
				$style['fitwidth'] = true;
			} else {
				$style['fitwidth'] = false;
			}
		}
		if ($style['fitwidth']) {
			// disable stretch
			$style['stretch'] = false;
		}
		if (!isset($style['stretch'])) {
			if (($w === '') OR ($w <= 0)) {
				$style['stretch'] = false;
			} else {
				$style['stretch'] = true;
			}
		}
		if (!isset($style['fgcolor'])) {
			$style['fgcolor'] = array(0,0,0); // default black
		}
		if (!isset($style['bgcolor'])) {
			$style['bgcolor'] = false; // default transparent
		}
		if (!isset($style['border'])) {
			$style['border'] = false;
		}
		$fontsize = 0;
		if (!isset($style['text'])) {
			$style['text'] = false;
		}
		if ($style['text'] AND isset($style['font'])) {
			if (isset($style['fontsize'])) {
				$fontsize = $style['fontsize'];
			}
			$this->setFont($style['font'], '', $fontsize);
		}
		if (!isset($style['stretchtext'])) {
			$style['stretchtext'] = 4;
		}
		if (LIMEPDF_STATIC::empty_string($x)) {
			$x = $this->x;
		}
		if (LIMEPDF_STATIC::empty_string($y)) {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		if (LIMEPDF_STATIC::empty_string($w) OR ($w <= 0)) {
			if ($this->rtl) {
				$w = $x - $this->lMargin;
			} else {
				$w = $this->w - $this->rMargin - $x;
			}
		}
		// padding
		if (!isset($style['padding'])) {
			$padding = 0;
		} elseif ($style['padding'] === 'auto') {
			$padding = 10 * ($w / ($arrcode['maxw'] + 20));
		} else {
			$padding = floatval($style['padding']);
		}
		// horizontal padding
		if (!isset($style['hpadding'])) {
			$hpadding = $padding;
		} elseif ($style['hpadding'] === 'auto') {
			$hpadding = 10 * ($w / ($arrcode['maxw'] + 20));
		} else {
			$hpadding = floatval($style['hpadding']);
		}
		// vertical padding
		if (!isset($style['vpadding'])) {
			$vpadding = $padding;
		} elseif ($style['vpadding'] === 'auto') {
			$vpadding = ($hpadding / 2);
		} else {
			$vpadding = floatval($style['vpadding']);
		}
		// calculate xres (single bar width)
		$max_xres = ($w - (2 * $hpadding)) / $arrcode['maxw'];
		if ($style['stretch']) {
			$xres = $max_xres;
		} else {
			if (LIMEPDF_STATIC::empty_string($xres)) {
				$xres = (0.141 * $this->k); // default bar width = 0.4 mm
			}
			if ($xres > $max_xres) {
				// correct xres to fit on $w
				$xres = $max_xres;
			}
			if ((isset($style['padding']) AND ($style['padding'] === 'auto'))
				OR (isset($style['hpadding']) AND ($style['hpadding'] === 'auto'))) {
				$hpadding = 10 * $xres;
				if (isset($style['vpadding']) AND ($style['vpadding'] === 'auto')) {
					$vpadding = ($hpadding / 2);
				}
			}
		}
		if ($style['fitwidth']) {
			$wold = $w;
			$w = (($arrcode['maxw'] * $xres) + (2 * $hpadding));
			if (isset($style['cellfitalign'])) {
				switch ($style['cellfitalign']) {
					case 'L': {
						if ($this->rtl) {
							$x -= ($wold - $w);
						}
						break;
					}
					case 'R': {
						if (!$this->rtl) {
							$x += ($wold - $w);
						}
						break;
					}
					case 'C': {
						if ($this->rtl) {
							$x -= (($wold - $w) / 2);
						} else {
							$x += (($wold - $w) / 2);
						}
						break;
					}
					default : {
						break;
					}
				}
			}
		}
		$text_height = $this->getCellHeight($fontsize / $this->k);
		// height
		if (LIMEPDF_STATIC::empty_string($h) OR ($h <= 0)) {
			// set default height
			$h = (($arrcode['maxw'] * $xres) / 3) + (2 * $vpadding) + $text_height;
		}
		$barh = $h - $text_height - (2 * $vpadding);
		if ($barh <=0) {
			// try to reduce font or padding to fit barcode on available height
			if ($text_height > $h) {
				$fontsize = (($h * $this->k) / (4 * $this->cell_height_ratio));
				$text_height = $this->getCellHeight($fontsize / $this->k);
				$this->setFont($style['font'], '', $fontsize);
			}
			if ($vpadding > 0) {
				$vpadding = (($h - $text_height) / 4);
			}
			$barh = $h - $text_height - (2 * $vpadding);
		}
		// fit the barcode on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, false);
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($style['position'] == 'L') {
				$xpos = $this->lMargin;
			} elseif ($style['position'] == 'C') {
				$xpos = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($style['position'] == 'R') {
				$xpos = $this->w - $this->rMargin - $w;
			} else {
				$xpos = $x - $w;
			}
			$this->img_rb_x = $xpos;
		} else {
			if ($style['position'] == 'L') {
				$xpos = $this->lMargin;
			} elseif ($style['position'] == 'C') {
				$xpos = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($style['position'] == 'R') {
				$xpos = $this->w - $this->rMargin - $w;
			} else {
				$xpos = $x;
			}
			$this->img_rb_x = $xpos + $w;
		}
		$xpos_rect = $xpos;
		if (!isset($style['align'])) {
			$style['align'] = 'C';
		}
		switch ($style['align']) {
			case 'L': {
				$xpos = $xpos_rect + $hpadding;
				break;
			}
			case 'R': {
				$xpos = $xpos_rect + ($w - ($arrcode['maxw'] * $xres)) - $hpadding;
				break;
			}
			case 'C':
			default : {
				$xpos = $xpos_rect + (($w - ($arrcode['maxw'] * $xres)) / 2);
				break;
			}
		}
		$xpos_text = $xpos;
		// barcode is always printed in LTR direction
		$tempRTL = $this->rtl;
		$this->rtl = false;
		// print background color
		if ($style['bgcolor']) {
			$this->Rect($xpos_rect, $y, $w, $h, $style['border'] ? 'DF' : 'F', '', $style['bgcolor']);
		} elseif ($style['border']) {
			$this->Rect($xpos_rect, $y, $w, $h, 'D');
		}
		// set foreground color
		$this->setDrawColorArray($style['fgcolor']);
		$this->setTextColorArray($style['fgcolor']);
		// print bars
		foreach ($arrcode['bcode'] as $k => $v) {
			$bw = ($v['w'] * $xres);
			if ($v['t']) {
				// draw a vertical bar
				$ypos = $y + $vpadding + ($v['p'] * $barh / $arrcode['maxh']);
				$this->Rect($xpos, $ypos, $bw, ($v['h'] * $barh / $arrcode['maxh']), 'F', array(), $style['fgcolor']);
			}
			$xpos += $bw;
		}
		// print text
		if ($style['text']) {
			if (isset($style['label']) AND !LIMEPDF_STATIC::empty_string($style['label'])) {
				$label = $style['label'];
			} else {
				$label = $code;
			}
			$txtwidth = ($arrcode['maxw'] * $xres);
			if ($this->GetStringWidth($label) > $txtwidth) {
				$style['stretchtext'] = 2;
			}
			// print text
			$this->x = $xpos_text;
			$this->y = $y + $vpadding + $barh;
			$cellpadding = $this->cell_padding;
			$this->setCellPadding(0);
			$this->Cell($txtwidth, 0, $label, 0, 0, 'C', false, '', $style['stretchtext'], false, 'T', 'T');
			$this->cell_padding = $cellpadding;
		}
		// restore original direction
		$this->rtl = $tempRTL;
		// restore previous settings
		$this->setGraphicVars($gvars);
		// set pointer to align the next text/objects
		switch($align) {
			case 'T':{
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M':{
				$this->y = $y + round($h / 2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B':{
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N':{
				$this->setY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
	}

	/**
	 * Print 2D Barcode.
	 * @param string $code code to print
	 * @param string $type type of barcode (see tcpdf_barcodes_2d.php for supported formats).
	 * @param float|null $x x position in user units
	 * @param float|null $y y position in user units
	 * @param float|null $w width in user units
	 * @param float|null $h height in user units
	 * @param array $style array of options:<ul>
	 * <li>boolean $style['border'] if true prints a border around the barcode</li>
	 * <li>int $style['padding'] padding to leave around the barcode in barcode units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['hpadding'] horizontal padding in barcode units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['vpadding'] vertical padding in barcode units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['module_width'] width of a single module in points</li>
	 * <li>int $style['module_height'] height of a single module in points</li>
	 * <li>array $style['fgcolor'] color array for bars and text</li>
	 * <li>mixed $style['bgcolor'] color array for background or false for transparent</li>
	 * <li>string $style['position'] barcode position on the page: L = left margin; C = center; R = right margin; S = stretch</li>
	 * @param string $align Indicates the alignment of the pointer next to barcode insertion relative to barcode height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param boolean $distort if true distort the barcode to fit width and height, otherwise preserve aspect ratio
	 * @author Nicola Asuni
	 * @since 4.5.037 (2009-04-07)
	 * @public
	 */
	public function write2DBarcode($code, $type, $x=null, $y=null, $w=null, $h=null, $style=array(), $align='', $distort=false) {
		if (LIMEPDF_STATIC::empty_string(trim($code))) {
			return;
		}
		require_once(dirname(__FILE__).'/tcpdf_barcodes_2d.php');
		// save current graphic settings
		$gvars = $this->getGraphicVars();
		// create new barcode object
		$barcodeobj = new TCPDF2DBarcode($code, $type);
		$arrcode = $barcodeobj->getBarcodeArray();
		if (empty($arrcode) OR !isset($arrcode['num_rows']) OR ($arrcode['num_rows'] == 0) OR !isset($arrcode['num_cols']) OR ($arrcode['num_cols'] == 0)) {
			$this->Error('Error in 2D barcode string');
		}
		// set default values
		if (!isset($style['position'])) {
			$style['position'] = '';
		}
		if (!isset($style['fgcolor'])) {
			$style['fgcolor'] = array(0,0,0); // default black
		}
		if (!isset($style['bgcolor'])) {
			$style['bgcolor'] = false; // default transparent
		}
		if (!isset($style['border'])) {
			$style['border'] = false;
		}
		// padding
		if (!isset($style['padding'])) {
			$style['padding'] = 0;
		} elseif ($style['padding'] === 'auto') {
			$style['padding'] = 4;
		}
		if (!isset($style['hpadding'])) {
			$style['hpadding'] = $style['padding'];
		} elseif ($style['hpadding'] === 'auto') {
			$style['hpadding'] = 4;
		}
		if (!isset($style['vpadding'])) {
			$style['vpadding'] = $style['padding'];
		} elseif ($style['vpadding'] === 'auto') {
			$style['vpadding'] = 4;
		}
		$hpad = (2 * $style['hpadding']);
		$vpad = (2 * $style['vpadding']);
		// cell (module) dimension
		if (!isset($style['module_width'])) {
			$style['module_width'] = 1; // width of a single module in points
		}
		if (!isset($style['module_height'])) {
			$style['module_height'] = 1; // height of a single module in points
		}
		if (LIMEPDF_STATIC::empty_string($x)) {
			$x = $this->x;
		}
		if (LIMEPDF_STATIC::empty_string($y)) {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		// number of barcode columns and rows
		$rows = $arrcode['num_rows'];
		$cols = $arrcode['num_cols'];
		if (($rows <= 0) || ($cols <= 0)){
			$this->Error('Error in 2D barcode string');
		}
		// module width and height
		$mw = $style['module_width'];
		$mh = $style['module_height'];
		if (($mw <= 0) OR ($mh <= 0)) {
			$this->Error('Error in 2D barcode string');
		}
		// get max dimensions
		if ($this->rtl) {
			$maxw = $x - $this->lMargin;
		} else {
			$maxw = $this->w - $this->rMargin - $x;
		}
		$maxh = ($this->h - $this->tMargin - $this->bMargin);
		$ratioHW = ((($rows * $mh) + $hpad) / (($cols * $mw) + $vpad));
		$ratioWH = ((($cols * $mw) + $vpad) / (($rows * $mh) + $hpad));
		if (!$distort) {
			if (($maxw * $ratioHW) > $maxh) {
				$maxw = $maxh * $ratioWH;
			}
			if (($maxh * $ratioWH) > $maxw) {
				$maxh = $maxw * $ratioHW;
			}
		}
		// set maximum dimensions
		if ($w > $maxw) {
			$w = $maxw;
		}
		if ($h > $maxh) {
			$h = $maxh;
		}
		// set dimensions
		if ((LIMEPDF_STATIC::empty_string($w) OR ($w <= 0)) AND (LIMEPDF_STATIC::empty_string($h) OR ($h <= 0))) {
			$w = ($cols + $hpad) * ($mw / $this->k);
			$h = ($rows + $vpad) * ($mh / $this->k);
		} elseif (($w === '') OR ($w <= 0)) {
			$w = $h * $ratioWH;
		} elseif (($h === '') OR ($h <= 0)) {
			$h = $w * $ratioHW;
		}
		// barcode size (excluding padding)
		$bw = ($w * $cols) / ($cols + $hpad);
		$bh = ($h * $rows) / ($rows + $vpad);
		// dimension of single barcode cell unit
		$cw = $bw / $cols;
		$ch = $bh / $rows;
		if (!$distort) {
			if (($cw / $ch) > ($mw / $mh)) {
				// correct horizontal distortion
				$cw = $ch * $mw / $mh;
				$bw = $cw * $cols;
				$style['hpadding'] = ($w - $bw) / (2 * $cw);
			} else {
				// correct vertical distortion
				$ch = $cw * $mh / $mw;
				$bh = $ch * $rows;
				$style['vpadding'] = ($h - $bh) / (2 * $ch);
			}
		}
		// fit the barcode on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, false);
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($style['position'] == 'L') {
				$xpos = $this->lMargin;
			} elseif ($style['position'] == 'C') {
				$xpos = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($style['position'] == 'R') {
				$xpos = $this->w - $this->rMargin - $w;
			} else {
				$xpos = $x - $w;
			}
			$this->img_rb_x = $xpos;
		} else {
			if ($style['position'] == 'L') {
				$xpos = $this->lMargin;
			} elseif ($style['position'] == 'C') {
				$xpos = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($style['position'] == 'R') {
				$xpos = $this->w - $this->rMargin - $w;
			} else {
				$xpos = $x;
			}
			$this->img_rb_x = $xpos + $w;
		}
		$xstart = $xpos + ($style['hpadding'] * $cw);
		$ystart = $y + ($style['vpadding'] * $ch);
		// barcode is always printed in LTR direction
		$tempRTL = $this->rtl;
		$this->rtl = false;
		// print background color
		if ($style['bgcolor']) {
			$this->Rect($xpos, $y, $w, $h, $style['border'] ? 'DF' : 'F', '', $style['bgcolor']);
		} elseif ($style['border']) {
			$this->Rect($xpos, $y, $w, $h, 'D');
		}
		// set foreground color
		$this->setDrawColorArray($style['fgcolor']);
		// print barcode cells
		// for each row
		for ($r = 0; $r < $rows; ++$r) {
			$xr = $xstart;
			// for each column
			for ($c = 0; $c < $cols; ++$c) {
				if ($arrcode['bcode'][$r][$c] == 1) {
					// draw a single barcode cell
					$this->Rect($xr, $ystart, $cw, $ch, 'F', array(), $style['fgcolor']);
				}
				$xr += $cw;
			}
			$ystart += $ch;
		}
		// restore original direction
		$this->rtl = $tempRTL;
		// restore previous settings
		$this->setGraphicVars($gvars);
		// set pointer to align the next text/objects
		switch($align) {
			case 'T':{
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M':{
				$this->y = $y + round($h/2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B':{
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N':{
				$this->setY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
	}

	/**
	 * Returns an array containing current margins:
	 * <ul>
			<li>$ret['left'] = left margin</li>
			<li>$ret['right'] = right margin</li>
			<li>$ret['top'] = top margin</li>
			<li>$ret['bottom'] = bottom margin</li>
			<li>$ret['header'] = header margin</li>
			<li>$ret['footer'] = footer margin</li>
			<li>$ret['cell'] = cell padding array</li>
			<li>$ret['padding_left'] = cell left padding</li>
			<li>$ret['padding_top'] = cell top padding</li>
			<li>$ret['padding_right'] = cell right padding</li>
			<li>$ret['padding_bottom'] = cell bottom padding</li>
	 * </ul>
	 * @return array containing all margins measures
	 * @public
	 * @since 3.2.000 (2008-06-23)
	 */
	public function getMargins() {
		$ret = array(
			'left' => $this->lMargin,
			'right' => $this->rMargin,
			'top' => $this->tMargin,
			'bottom' => $this->bMargin,
			'header' => $this->header_margin,
			'footer' => $this->footer_margin,
			'cell' => $this->cell_padding,
			'padding_left' => $this->cell_padding['L'],
			'padding_top' => $this->cell_padding['T'],
			'padding_right' => $this->cell_padding['R'],
			'padding_bottom' => $this->cell_padding['B']
		);
		return $ret;
	}

	/**
	 * Returns an array containing original margins:
	 * <ul>
			<li>$ret['left'] = left margin</li>
			<li>$ret['right'] = right margin</li>
	 * </ul>
	 * @return array containing all margins measures
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getOriginalMargins() {
		$ret = array(
			'left' => $this->original_lMargin,
			'right' => $this->original_rMargin
		);
		return $ret;
	}

	/**
	 * Cleanup HTML code (requires HTML Tidy library).
	 * @param string $html htmlcode to fix
	 * @param string $default_css CSS commands to add
	 * @param array|null $tagvs parameters for setHtmlVSpace method
	 * @param array|null $tidy_options options for tidy_parse_string function
	 * @return string XHTML code cleaned up
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.017 (2010-11-16)
	 * @see setHtmlVSpace()
	 */
	public function fixHTMLCode($html, $default_css='', $tagvs=null, $tidy_options=null) {
		return LIMEPDF_STATIC::fixHTMLCode($html, $default_css, $tagvs, $tidy_options, $this->tagvspaces);
	}

	/**
	 * Returns the border width from CSS property
	 * @param string $width border width
	 * @return int with in user units
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderWidth($width) {
		if ($width == 'thin') {
			$width = (2 / $this->k);
		} elseif ($width == 'medium') {
			$width = (4 / $this->k);
		} elseif ($width == 'thick') {
			$width = (6 / $this->k);
		} else {
			$width = $this->getHTMLUnitToUnits($width, 1, 'px', false);
		}
		return $width;
	}

	/**
	 * Returns the border dash style from CSS property
	 * @param string $style border style to convert
	 * @return int sash style (return -1 in case of none or hidden border)
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderDashStyle($style) {
		switch (strtolower($style)) {
			case 'none':
			case 'hidden': {
				$dash = -1;
				break;
			}
			case 'dotted': {
				$dash = 1;
				break;
			}
			case 'dashed': {
				$dash = 3;
				break;
			}
			case 'double':
			case 'groove':
			case 'ridge':
			case 'inset':
			case 'outset':
			case 'solid':
			default: {
				$dash = 0;
				break;
			}
		}
		return $dash;
	}

	/**
	 * Returns the border style array from CSS border properties
	 * @param string $cssborder border properties
	 * @return array containing border properties
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderStyle($cssborder) {
		$bprop = preg_split('/[\s]+/', trim($cssborder));
		$count = count($bprop);
		if ($count > 0 && $bprop[$count - 1] === '!important') {
			unset($bprop[$count - 1]);
			--$count;
		}

		$border = array(); // value to be returned
		switch ($count) {
			case 2: {
				$width = 'medium';
				$style = $bprop[0];
				$color = $bprop[1];
				break;
			}
			case 1: {
				$width = 'medium';
				$style = $bprop[0];
				$color = 'black';
				break;
			}
			case 0: {
				$width = 'medium';
				$style = 'solid';
				$color = 'black';
				break;
			}
			default: {
				$width = $bprop[0];
				$style = $bprop[1];
				$color = $bprop[2];
				break;
			}
		}
		if ($style == 'none') {
			return array();
		}
		$border['cap'] = 'square';
		$border['join'] = 'miter';
		$border['dash'] = $this->getCSSBorderDashStyle($style);
		if ($border['dash'] < 0) {
			return array();
		}
		$border['width'] = $this->getCSSBorderWidth($width);
		$border['color'] = LIMEPDF_COLORS::convertHTMLColorToDec($color, $this->spot_colors);
		return $border;
	}

	/**
	 * Get the internal Cell padding from CSS attribute.
	 * @param string $csspadding padding properties
	 * @param float $width width of the containing element
	 * @return array of cell paddings
	 * @public
	 * @since 5.9.000 (2010-10-04)
	 */
	public function getCSSPadding($csspadding, $width=0) {
		$padding = preg_split('/[\s]+/', trim($csspadding));
		$cell_padding = array(); // value to be returned
		switch (count($padding)) {
			case 4: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[1];
				$cell_padding['B'] = $padding[2];
				$cell_padding['L'] = $padding[3];
				break;
			}
			case 3: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[1];
				$cell_padding['B'] = $padding[2];
				$cell_padding['L'] = $padding[1];
				break;
			}
			case 2: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[1];
				$cell_padding['B'] = $padding[0];
				$cell_padding['L'] = $padding[1];
				break;
			}
			case 1: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[0];
				$cell_padding['B'] = $padding[0];
				$cell_padding['L'] = $padding[0];
				break;
			}
			default: {
				return $this->cell_padding;
			}
		}
		if ($width == 0) {
			$width = $this->w - $this->lMargin - $this->rMargin;
		}
		$cell_padding['T'] = $this->getHTMLUnitToUnits($cell_padding['T'], $width, 'px', false);
		$cell_padding['R'] = $this->getHTMLUnitToUnits($cell_padding['R'], $width, 'px', false);
		$cell_padding['B'] = $this->getHTMLUnitToUnits($cell_padding['B'], $width, 'px', false);
		$cell_padding['L'] = $this->getHTMLUnitToUnits($cell_padding['L'], $width, 'px', false);
		return $cell_padding;
	}

	/**
	 * Get the internal Cell margin from CSS attribute.
	 * @param string $cssmargin margin properties
	 * @param float $width width of the containing element
	 * @return array of cell margins
	 * @public
	 * @since 5.9.000 (2010-10-04)
	 */
	public function getCSSMargin($cssmargin, $width=0) {
		$margin = preg_split('/[\s]+/', trim($cssmargin));
		$cell_margin = array(); // value to be returned
		switch (count($margin)) {
			case 4: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[1];
				$cell_margin['B'] = $margin[2];
				$cell_margin['L'] = $margin[3];
				break;
			}
			case 3: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[1];
				$cell_margin['B'] = $margin[2];
				$cell_margin['L'] = $margin[1];
				break;
			}
			case 2: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[1];
				$cell_margin['B'] = $margin[0];
				$cell_margin['L'] = $margin[1];
				break;
			}
			case 1: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[0];
				$cell_margin['B'] = $margin[0];
				$cell_margin['L'] = $margin[0];
				break;
			}
			default: {
				return $this->cell_margin;
			}
		}
		if ($width == 0) {
			$width = $this->w - $this->lMargin - $this->rMargin;
		}
		$cell_margin['T'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['T']), $width, 'px', false);
		$cell_margin['R'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['R']), $width, 'px', false);
		$cell_margin['B'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['B']), $width, 'px', false);
		$cell_margin['L'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['L']), $width, 'px', false);
		return $cell_margin;
	}

	/**
	 * Get the border-spacing from CSS attribute.
	 * @param string $cssbspace border-spacing CSS properties
	 * @param float $width width of the containing element
	 * @return array of border spacings
	 * @public
	 * @since 5.9.010 (2010-10-27)
	 */
	public function getCSSBorderMargin($cssbspace, $width=0) {
		$space = preg_split('/[\s]+/', trim($cssbspace));
		$border_spacing = array(); // value to be returned
		switch (count($space)) {
			case 2: {
				$border_spacing['H'] = $space[0];
				$border_spacing['V'] = $space[1];
				break;
			}
			case 1: {
				$border_spacing['H'] = $space[0];
				$border_spacing['V'] = $space[0];
				break;
			}
			default: {
				return array('H' => 0, 'V' => 0);
			}
		}
		if ($width == 0) {
			$width = $this->w - $this->lMargin - $this->rMargin;
		}
		$border_spacing['H'] = $this->getHTMLUnitToUnits($border_spacing['H'], $width, 'px', false);
		$border_spacing['V'] = $this->getHTMLUnitToUnits($border_spacing['V'], $width, 'px', false);
		return $border_spacing;
	}

	/**
	 * Returns the letter-spacing value from CSS value
	 * @param string $spacing letter-spacing value
	 * @param float $parent font spacing (tracking) value of the parent element
	 * @return float quantity to increases or decreases the space between characters in a text.
	 * @protected
	 * @since 5.9.000 (2010-10-02)
	 */
	protected function getCSSFontSpacing($spacing, $parent=0) {
		$val = 0; // value to be returned
		$spacing = trim($spacing);
		switch ($spacing) {
			case 'normal': {
				$val = 0;
				break;
			}
			case 'inherit': {
				if ($parent == 'normal') {
					$val = 0;
				} else {
					$val = $parent;
				}
				break;
			}
			default: {
				$val = $this->getHTMLUnitToUnits($spacing, 0, 'px', false);
			}
		}
		return $val;
	}

	/**
	 * Returns the percentage of font stretching from CSS value
	 * @param string $stretch stretch mode
	 * @param float $parent stretch value of the parent element
	 * @return float font stretching percentage
	 * @protected
	 * @since 5.9.000 (2010-10-02)
	 */
	protected function getCSSFontStretching($stretch, $parent=100) {
		$val = 100; // value to be returned
		$stretch = trim($stretch);
		switch ($stretch) {
			case 'ultra-condensed': {
				$val = 40;
				break;
			}
			case 'extra-condensed': {
				$val = 55;
				break;
			}
			case 'condensed': {
				$val = 70;
				break;
			}
			case 'semi-condensed': {
				$val = 85;
				break;
			}
			case 'normal': {
				$val = 100;
				break;
			}
			case 'semi-expanded': {
				$val = 115;
				break;
			}
			case 'expanded': {
				$val = 130;
				break;
			}
			case 'extra-expanded': {
				$val = 145;
				break;
			}
			case 'ultra-expanded': {
				$val = 160;
				break;
			}
			case 'wider': {
				$val = ($parent + 10);
				break;
			}
			case 'narrower': {
				$val = ($parent - 10);
				break;
			}
			case 'inherit': {
				if ($parent == 'normal') {
					$val = 100;
				} else {
					$val = $parent;
				}
				break;
			}
			default: {
				$val = $this->getHTMLUnitToUnits($stretch, 100, '%', false);
			}
		}
		return $val;
	}

	/**
	 * Convert HTML string containing font size value to points
	 * @param string $val String containing font size value and unit.
	 * @param float $refsize Reference font size in points.
	 * @param float $parent_size Parent font size in points.
	 * @param string $defaultunit Default unit (can be one of the following: %, em, ex, px, in, mm, pc, pt).
	 * @return float value in points
	 * @public
	 */
	public function getHTMLFontUnits($val, $refsize=12, $parent_size=12, $defaultunit='pt') {
		$refsize = LIMEPDF_FONT::getFontRefSize($refsize);
		$parent_size = LIMEPDF_FONT::getFontRefSize($parent_size, $refsize);
		switch ($val) {
			case 'xx-small': {
				$size = ($refsize - 4);
				break;
			}
			case 'x-small': {
				$size = ($refsize - 3);
				break;
			}
			case 'small': {
				$size = ($refsize - 2);
				break;
			}
			case 'medium': {
				$size = $refsize;
				break;
			}
			case 'large': {
				$size = ($refsize + 2);
				break;
			}
			case 'x-large': {
				$size = ($refsize + 4);
				break;
			}
			case 'xx-large': {
				$size = ($refsize + 6);
				break;
			}
			case 'smaller': {
				$size = ($parent_size - 3);
				break;
			}
			case 'larger': {
				$size = ($parent_size + 3);
				break;
			}
			default: {
				$parentSize = $this->getHTMLUnitToUnits($parent_size, $refsize, $defaultunit, true);
				$size = $this->getHTMLUnitToUnits($val, $parent_size, $defaultunit, true);
			}
		}
		return $size;
	}

	/**
	 * Returns the HTML DOM array.
	 * @param string $html html code
	 * @return array
	 * @protected
	 * @since 3.2.000 (2008-06-20)
	 */
	protected function getHtmlDomArray($html) {
		// set inheritable properties fot the first void element
		// possible inheritable properties are: azimuth, border-collapse, border-spacing, caption-side, color, cursor, direction, empty-cells, font, font-family, font-stretch, font-size, font-size-adjust, font-style, font-variant, font-weight, letter-spacing, line-height, list-style, list-style-image, list-style-position, list-style-type, orphans, page, page-break-inside, quotes, speak, speak-header, text-align, text-indent, text-transform, volume, white-space, widows, word-spacing
		$dom = array(
			array(
				'tag' => false,
				'block' => false,
				'value' => '',
				'parent' => 0,
				'hide' => false,
				'fontname' => $this->FontFamily,
				'fontstyle' => $this->FontStyle,
				'fontsize' => $this->FontSizePt,
				'font-stretch' => $this->font_stretching,
				'letter-spacing' => $this->font_spacing,
				'stroke' => $this->textstrokewidth,
				'fill' => (($this->textrendermode % 2) == 0),
				'clip' => ($this->textrendermode > 3),
				'line-height' => $this->cell_height_ratio,
				'bgcolor' => false,
				'fgcolor' => $this->fgcolor, // color
				'strokecolor' => $this->strokecolor,
				'align' => '',
				'listtype' => '',
				'text-indent' => 0,
				'text-transform' => '',
				'border' => array(),
				'dir' => $this->rtl?'rtl':'ltr',
				'width' => 0,
				'height' => 0,
				'x' => 0,
				'y' => 0,
				'w' => 0,
				'h' => 0,
				'l' => 0,
				't' => 0,
				'r' => 0,
				'b' => 0,
				'padding' => array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0),
				'margin' => array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0),
				'border-spacing' => array('H' => 0, 'V' => 0),
				'border-collapse' => 'separate',
			)
		);

		if($html === '' || $html === null) {
			return $dom;
		}
		// array of CSS styles ( selector => properties).
		$css = array();
		// get CSS array defined at previous call
		$matches = array();
		if (preg_match_all('/<cssarray>([^\<]*?)<\/cssarray>/is', $html, $matches) > 0) {
			if (isset($matches[1][0])) {
				$css = array_merge($css, json_decode($this->unhtmlentities($matches[1][0]), true));
			}
			$html = preg_replace('/<cssarray>(.*?)<\/cssarray>/is', '', $html);
		}
		// extract external CSS files
		$matches = array();
		if (preg_match_all('/<link([^\>]*?)>/is', $html, $matches) > 0) {
			foreach ($matches[1] as $key => $link) {
				$type = array();
				if (preg_match('/type[\s]*=[\s]*"text\/css"/', $link, $type)) {
					$type = array();
					preg_match('/media[\s]*=[\s]*"([^"]*)"/', $link, $type);
					// get 'all' and 'print' media, other media types are discarded
					// (all, braille, embossed, handheld, print, projection, screen, speech, tty, tv)
					if (empty($type) OR (isset($type[1]) AND (($type[1] == 'all') OR ($type[1] == 'print')))) {
						$type = array();
						if (preg_match('/href[\s]*=[\s]*"([^"]*)"/', $link, $type) > 0) {
							// read CSS data file
                            $cssdata = $this->getCachedFileContents(trim($type[1]));
							if (($cssdata !== FALSE) AND (strlen($cssdata) > 0)) {
								$css = array_merge($css, LIMEPDF_STATIC::extractCSSproperties($cssdata));
							}
						}
					}
				}
			}
		}
		// extract style tags
		$matches = array();
		if (preg_match_all('/<style([^\>]*?)>([^\<]*?)<\/style>/is', $html, $matches) > 0) {
			foreach ($matches[1] as $key => $media) {
				$type = array();
				preg_match('/media[\s]*=[\s]*"([^"]*)"/', $media, $type);
				// get 'all' and 'print' media, other media types are discarded
				// (all, braille, embossed, handheld, print, projection, screen, speech, tty, tv)
				if (empty($type) OR (isset($type[1]) AND (($type[1] == 'all') OR ($type[1] == 'print')))) {
					$cssdata = $matches[2][$key];
					$css = array_merge($css, LIMEPDF_STATIC::extractCSSproperties($cssdata));
				}
			}
		}
		// create a special tag to contain the CSS array (used for table content)
		$csstagarray = '<cssarray>'.htmlentities(json_encode($css)).'</cssarray>';
		// remove head and style blocks
		$html = preg_replace('/<head([^\>]*?)>(.*?)<\/head>/is', '', $html);
		$html = preg_replace('/<style([^\>]*?)>([^\<]*?)<\/style>/is', '', $html);
		// define block tags
		$blocktags = array('blockquote','br','dd','dl','div','dt','h1','h2','h3','h4','h5','h6','hr','li','ol','p','pre','ul','tcpdf','table','tr','td');
		// define self-closing tags
		$selfclosingtags = array('area','base','basefont','br','hr','input','img','link','meta');
		// remove all unsupported tags (the line below lists all supported tags)
		$html = strip_tags($html, '<marker/><a><b><blockquote><body><br><br/><dd><del><div><dl><dt><em><font><form><h1><h2><h3><h4><h5><h6><hr><hr/><i><img><input><label><li><ol><option><p><pre><s><select><small><span><strike><strong><sub><sup><table><tablehead><tcpdf><td><textarea><th><thead><tr><tt><u><ul>');
		//replace some blank characters
		$html = preg_replace('/<pre/', '<xre', $html); // preserve pre tag
		$html = preg_replace('/<(table|tr|td|th|tcpdf|blockquote|dd|div|dl|dt|form|h1|h2|h3|h4|h5|h6|br|hr|li|ol|ul|p)([^\>]*)>[\n\r\t]+/', '<\\1\\2>', $html);
		$html = preg_replace('@(\r\n|\r)@', "\n", $html);
		$repTable = array("\t" => ' ', "\0" => ' ', "\x0B" => ' ', "\\" => "\\\\");
		$html = strtr($html, $repTable);
		$offset = 0;
		while (($offset < strlen($html)) AND ($pos = strpos($html, '</pre>', $offset)) !== false) {
			$html_a = substr($html, 0, $offset);
			$html_b = substr($html, $offset, ($pos - $offset + 6));
			while (preg_match("'<xre([^\>]*)>(.*?)\n(.*?)</pre>'si", $html_b)) {
				// preserve newlines on <pre> tag
				$html_b = preg_replace("'<xre([^\>]*)>(.*?)\n(.*?)</pre>'si", "<xre\\1>\\2<br />\\3</pre>", $html_b);
			}
			while (preg_match("'<xre([^\>]*)>(.*?)".$this->re_space['p']."(.*?)</pre>'".$this->re_space['m'], $html_b)) {
				// preserve spaces on <pre> tag
				$html_b = preg_replace("'<xre([^\>]*)>(.*?)".$this->re_space['p']."(.*?)</pre>'".$this->re_space['m'], "<xre\\1>\\2&nbsp;\\3</pre>", $html_b);
			}
			$html = $html_a.$html_b.substr($html, $pos + 6);
			$offset = strlen($html_a.$html_b);
		}
		$offset = 0;
		while (($offset < strlen($html)) AND ($pos = strpos($html, '</textarea>', $offset)) !== false) {
			$html_a = substr($html, 0, $offset);
			$html_b = substr($html, $offset, ($pos - $offset + 11));
			while (preg_match("'<textarea([^\>]*)>(.*?)\n(.*?)</textarea>'si", $html_b)) {
				// preserve newlines on <textarea> tag
				$html_b = preg_replace("'<textarea([^\>]*)>(.*?)\n(.*?)</textarea>'si", "<textarea\\1>\\2<TBR>\\3</textarea>", $html_b);
				$html_b = preg_replace("'<textarea([^\>]*)>(.*?)[\"](.*?)</textarea>'si", "<textarea\\1>\\2''\\3</textarea>", $html_b);
			}
			$html = $html_a.$html_b.substr($html, $pos + 11);
			$offset = strlen($html_a.$html_b);
		}
		$html = preg_replace('/([\s]*)<option/si', '<option', $html);
		$html = preg_replace('/<\/option>([\s]*)/si', '</option>', $html);
		$offset = 0;
		while (($offset < strlen($html)) AND ($pos = strpos($html, '</option>', $offset)) !== false) {
			$html_a = substr($html, 0, $offset);
			$html_b = substr($html, $offset, ($pos - $offset + 9));
			while (preg_match("'<option([^\>]*)>(.*?)</option>'si", $html_b)) {
				$html_b = preg_replace("'<option([\s]+)value=\"([^\"]*)\"([^\>]*)>(.*?)</option>'si", "\\2#!TaB!#\\4#!NwL!#", $html_b);
				$html_b = preg_replace("'<option([^\>]*)>(.*?)</option>'si", "\\2#!NwL!#", $html_b);
			}
			$html = $html_a.$html_b.substr($html, $pos + 9);
			$offset = strlen($html_a.$html_b);
		}
		if (preg_match("'</select'si", $html)) {
			$html = preg_replace("'<select([^\>]*)>'si", "<select\\1 opt=\"", $html);
			$html = preg_replace("'#!NwL!#</select>'si", "\" />", $html);
		}
		$html = str_replace("\n", ' ', $html);
		// restore textarea newlines
		$html = str_replace('<TBR>', "\n", $html);
		// remove extra spaces from code
		$html = preg_replace('/[\s]+<\/(table|tr|ul|ol|dl)>/', '</\\1>', $html);
		$html = preg_replace('/'.$this->re_space['p'].'+<\/(td|th|li|dt|dd)>/'.$this->re_space['m'], '</\\1>', $html);
		$html = preg_replace('/[\s]+<(tr|td|th|li|dt|dd)/', '<\\1', $html);
		$html = preg_replace('/'.$this->re_space['p'].'+<(ul|ol|dl|br)/'.$this->re_space['m'], '<\\1', $html);
		$html = preg_replace('/<\/(table|tr|td|th|blockquote|dd|dt|dl|div|dt|h1|h2|h3|h4|h5|h6|hr|li|ol|ul|p)>[\s]+</', '</\\1><', $html);
		$html = preg_replace('/<\/(td|th)>/', '<marker style="font-size:0"/></\\1>', $html);
		$html = preg_replace('/<\/table>([\s]*)<marker style="font-size:0"\/>/', '</table>', $html);
		$html = preg_replace('/'.$this->re_space['p'].'+<img/'.$this->re_space['m'], chr(32).'<img', $html);
		$html = preg_replace('/<img([^\>]*)>[\s]+([^\<])/xi', '<img\\1>&nbsp;\\2', $html);
		$html = preg_replace('/<img([^\>]*)>/xi', '<img\\1><span><marker style="font-size:0"/></span>', $html);
		$html = preg_replace('/<xre/', '<pre', $html); // restore pre tag
		$html = preg_replace('/<textarea([^\>]*)>([^\<]*)<\/textarea>/xi', '<textarea\\1 value="\\2" />', $html);
		$html = preg_replace('/<li([^\>]*)><\/li>/', '<li\\1>&nbsp;</li>', $html);
		$html = preg_replace('/<li([^\>]*)>'.$this->re_space['p'].'*<img/'.$this->re_space['m'], '<li\\1><font size="1">&nbsp;</font><img', $html);
		$html = preg_replace('/<([^\>\/]*)>[\s]/', '<\\1>&nbsp;', $html); // preserve some spaces
		$html = preg_replace('/[\s]<\/([^\>]*)>/', '&nbsp;</\\1>', $html); // preserve some spaces
		$html = preg_replace('/<su([bp])/', '<zws/><su\\1', $html); // fix sub/sup alignment
		$html = preg_replace('/<\/su([bp])>/', '</su\\1><zws/>', $html); // fix sub/sup alignment
		$html = preg_replace('/'.$this->re_space['p'].'+/'.$this->re_space['m'], chr(32), $html); // replace multiple spaces with a single space
		// trim string
		$html = $this->stringTrim($html);
		// fix br tag after li
		$html = preg_replace('/<li><br([^\>]*)>/', '<li> <br\\1>', $html);
		// fix first image tag alignment
		$html = preg_replace('/^<img/', '<span style="font-size:0"><br /></span> <img', $html, 1);
		// pattern for generic tag
		$tagpattern = '/(<[^>]+>)/';
		// explodes the string
		$a = preg_split($tagpattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		// count elements
		$maxel = count($a);
		$elkey = 0;
		$thead = false; // true when we are inside the THEAD tag
		$key = 1;
		$level = array();
		array_push($level, 0); // root
		while ($elkey < $maxel) {
			$dom[$key] = array();
			$element = $a[$elkey];
			$dom[$key]['elkey'] = $elkey;
			if (preg_match($tagpattern, $element)) {
				// html tag
				$element = substr($element, 1, -1);
				// get tag name
				preg_match('/[\/]?([a-zA-Z0-9]*)/', $element, $tag);
				$tagname = strtolower($tag[1]);
				// check if we are inside a table header
				if ($tagname == 'thead') {
					if ($element[0] == '/') {
						$thead = false;
					} else {
						$thead = true;
					}
					++$elkey;
					continue;
				}
				$dom[$key]['tag'] = true;
				$dom[$key]['value'] = $tagname;
				if (in_array($dom[$key]['value'], $blocktags)) {
					$dom[$key]['block'] = true;
				} else {
					$dom[$key]['block'] = false;
				}
				if ($element[0] == '/') {
					// *** closing html tag
					$dom[$key]['opening'] = false;
					$dom[$key]['parent'] = end($level);
					array_pop($level);
					$dom[$key]['hide'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['hide'];
					$dom[$key]['fontname'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontname'];
					$dom[$key]['fontstyle'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontstyle'];
					$dom[$key]['fontsize'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontsize'];
					$dom[$key]['font-stretch'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['font-stretch'];
					$dom[$key]['letter-spacing'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['letter-spacing'];
					$dom[$key]['stroke'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['stroke'];
					$dom[$key]['fill'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fill'];
					$dom[$key]['clip'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['clip'];
					$dom[$key]['line-height'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['line-height'];
					$dom[$key]['bgcolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['bgcolor'];
					$dom[$key]['fgcolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fgcolor'];
					$dom[$key]['strokecolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['strokecolor'];
					$dom[$key]['align'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['align'];
					$dom[$key]['text-transform'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['text-transform'];
					$dom[$key]['dir'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['dir'];
					if (isset($dom[($dom[($dom[$key]['parent'])]['parent'])]['listtype'])) {
						$dom[$key]['listtype'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['listtype'];
					}
					// set the number of columns in table tag
					if (($dom[$key]['value'] == 'tr') AND (!isset($dom[($dom[($dom[$key]['parent'])]['parent'])]['cols']))) {
						$dom[($dom[($dom[$key]['parent'])]['parent'])]['cols'] = $dom[($dom[$key]['parent'])]['cols'];
					}
					if (($dom[$key]['value'] == 'td') OR ($dom[$key]['value'] == 'th')) {
						$dom[($dom[$key]['parent'])]['content'] = $csstagarray;
						for ($i = ($dom[$key]['parent'] + 1); $i < $key; ++$i) {
							$dom[($dom[$key]['parent'])]['content'] .= stripslashes($a[$dom[$i]['elkey']]);
						}
						$key = $i;
						// mark nested tables
						$dom[($dom[$key]['parent'])]['content'] = str_replace('<table', '<table nested="true"', $dom[($dom[$key]['parent'])]['content']);
						// remove thead sections from nested tables
						$dom[($dom[$key]['parent'])]['content'] = str_replace('<thead>', '', $dom[($dom[$key]['parent'])]['content']);
						$dom[($dom[$key]['parent'])]['content'] = str_replace('</thead>', '', $dom[($dom[$key]['parent'])]['content']);
					}
					// store header rows on a new table
					if (
						($dom[$key]['value'] === 'tr')
						&& !empty($dom[($dom[$key]['parent'])]['thead'])
						&& ($dom[($dom[$key]['parent'])]['thead'] === true)
					) {
						if (LIMEPDF_STATIC::empty_string($dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'])) {
							$dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'] = $csstagarray.$a[$dom[($dom[($dom[$key]['parent'])]['parent'])]['elkey']];
						}
						for ($i = $dom[$key]['parent']; $i <= $key; ++$i) {
							$dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'] .= $a[$dom[$i]['elkey']];
						}
						if (!isset($dom[($dom[$key]['parent'])]['attribute'])) {
							$dom[($dom[$key]['parent'])]['attribute'] = array();
						}
						// header elements must be always contained in a single page
						$dom[($dom[$key]['parent'])]['attribute']['nobr'] = 'true';
					}
					if (($dom[$key]['value'] == 'table') AND (!LIMEPDF_STATIC::empty_string($dom[($dom[$key]['parent'])]['thead']))) {
						// remove the nobr attributes from the table header
						$dom[($dom[$key]['parent'])]['thead'] = str_replace(' nobr="true"', '', $dom[($dom[$key]['parent'])]['thead']);
						$dom[($dom[$key]['parent'])]['thead'] .= '</tablehead>';
					}
				} else {
					// *** opening or self-closing html tag
					$dom[$key]['opening'] = true;
					$dom[$key]['parent'] = end($level);
					if ((substr($element, -1, 1) == '/') OR (in_array($dom[$key]['value'], $selfclosingtags))) {
						// self-closing tag
						$dom[$key]['self'] = true;
					} else {
						// opening tag
						array_push($level, $key);
						$dom[$key]['self'] = false;
					}
					// copy some values from parent
					$parentkey = 0;
					if ($key > 0) {
						$parentkey = $dom[$key]['parent'];
						$dom[$key]['hide'] = $dom[$parentkey]['hide'];
						$dom[$key]['fontname'] = $dom[$parentkey]['fontname'];
						$dom[$key]['fontstyle'] = $dom[$parentkey]['fontstyle'];
						$dom[$key]['fontsize'] = $dom[$parentkey]['fontsize'];
						$dom[$key]['font-stretch'] = $dom[$parentkey]['font-stretch'];
						$dom[$key]['letter-spacing'] = $dom[$parentkey]['letter-spacing'];
						$dom[$key]['stroke'] = $dom[$parentkey]['stroke'];
						$dom[$key]['fill'] = $dom[$parentkey]['fill'];
						$dom[$key]['clip'] = $dom[$parentkey]['clip'];
						$dom[$key]['line-height'] = $dom[$parentkey]['line-height'];
						$dom[$key]['bgcolor'] = $dom[$parentkey]['bgcolor'];
						$dom[$key]['fgcolor'] = $dom[$parentkey]['fgcolor'];
						$dom[$key]['strokecolor'] = $dom[$parentkey]['strokecolor'];
						$dom[$key]['align'] = $dom[$parentkey]['align'];
						$dom[$key]['listtype'] = $dom[$parentkey]['listtype'];
						$dom[$key]['text-indent'] = $dom[$parentkey]['text-indent'];
						$dom[$key]['text-transform'] = $dom[$parentkey]['text-transform'];
						$dom[$key]['border'] = array();
						$dom[$key]['dir'] = $dom[$parentkey]['dir'];
					}
					// get attributes
					preg_match_all('/([^=\s]*)[\s]*=[\s]*"([^"]*)"/', $element, $attr_array, PREG_PATTERN_ORDER);
					$dom[$key]['attribute'] = array(); // reset attribute array
                    foreach($attr_array[1] as $id => $name) {
                        $dom[$key]['attribute'][strtolower($name)] = $attr_array[2][$id];
                    }
					if (!empty($css)) {
						// merge CSS style to current style
						list($dom[$key]['csssel'], $dom[$key]['cssdata']) = LIMEPDF_STATIC::getCSSdataArray($dom, $key, $css);
						$dom[$key]['attribute']['style'] = LIMEPDF_STATIC::getTagStyleFromCSSarray($dom[$key]['cssdata']);
					}
					// split style attributes
					if (isset($dom[$key]['attribute']['style']) AND !empty($dom[$key]['attribute']['style'])) {
						// get style attributes
						preg_match_all('/([^;:\s]*):([^;]*)/', $dom[$key]['attribute']['style'], $style_array, PREG_PATTERN_ORDER);
						$dom[$key]['style'] = array(); // reset style attribute array
                        foreach($style_array[1] as $id => $name) {
                            // in case of duplicate attribute the last replace the previous
                            $dom[$key]['style'][strtolower($name)] = trim($style_array[2][$id]);
                        }
						// --- get some style attributes ---
						// text direction
						if (isset($dom[$key]['style']['direction'])) {
							$dom[$key]['dir'] = $dom[$key]['style']['direction'];
						}
						// display
						if (isset($dom[$key]['style']['display'])) {
							$dom[$key]['hide'] = (trim(strtolower($dom[$key]['style']['display'])) == 'none');
						}
						// font family
						if (isset($dom[$key]['style']['font-family'])) {
							$dom[$key]['fontname'] = $this->getFontFamilyName($dom[$key]['style']['font-family']);
						}
						// list-style-type
						if (isset($dom[$key]['style']['list-style-type'])) {
							$dom[$key]['listtype'] = trim(strtolower($dom[$key]['style']['list-style-type']));
							if ($dom[$key]['listtype'] == 'inherit') {
								$dom[$key]['listtype'] = $dom[$parentkey]['listtype'];
							}
						}
						// text-indent
						if (isset($dom[$key]['style']['text-indent'])) {
							$dom[$key]['text-indent'] = $this->getHTMLUnitToUnits($dom[$key]['style']['text-indent']);
							if ($dom[$key]['text-indent'] == 'inherit') {
								$dom[$key]['text-indent'] = $dom[$parentkey]['text-indent'];
							}
						}
						// text-transform
						if (isset($dom[$key]['style']['text-transform'])) {
							$dom[$key]['text-transform'] = $dom[$key]['style']['text-transform'];
						}
						// font size
						if (isset($dom[$key]['style']['font-size'])) {
							$fsize = trim($dom[$key]['style']['font-size']);
							$dom[$key]['fontsize'] = $this->getHTMLFontUnits($fsize, $dom[0]['fontsize'], $dom[$parentkey]['fontsize'], 'pt');
						}
						// font-stretch
						if (isset($dom[$key]['style']['font-stretch'])) {
							$dom[$key]['font-stretch'] = $this->getCSSFontStretching($dom[$key]['style']['font-stretch'], $dom[$parentkey]['font-stretch']);
						}
						// letter-spacing
						if (isset($dom[$key]['style']['letter-spacing'])) {
							$dom[$key]['letter-spacing'] = $this->getCSSFontSpacing($dom[$key]['style']['letter-spacing'], $dom[$parentkey]['letter-spacing']);
						}
						// line-height (internally is the cell height ratio)
						if (isset($dom[$key]['style']['line-height'])) {
							$lineheight = trim($dom[$key]['style']['line-height']);
							switch ($lineheight) {
								// A normal line height. This is default
								case 'normal': {
									$dom[$key]['line-height'] = $dom[0]['line-height'];
									break;
								}
								case 'inherit': {
									$dom[$key]['line-height'] = $dom[$parentkey]['line-height'];
								}
								default: {
									if (is_numeric($lineheight)) {
										// convert to percentage of font height
										$lineheight = ($lineheight * 100).'%';
									}
									$dom[$key]['line-height'] = $this->getHTMLUnitToUnits($lineheight, 1, '%', true);
									if (substr($lineheight, -1) !== '%') {
										if ($dom[$key]['fontsize'] <= 0) {
											$dom[$key]['line-height'] = 1;
										} else {
											$dom[$key]['line-height'] = (($dom[$key]['line-height'] - $this->cell_padding['T'] - $this->cell_padding['B']) / $dom[$key]['fontsize']);
										}
									}
								}
							}
						}
						// font style
						if (isset($dom[$key]['style']['font-weight'])) {
							if (strtolower($dom[$key]['style']['font-weight'][0]) == 'n') {
								if (strpos($dom[$key]['fontstyle'], 'B') !== false) {
									$dom[$key]['fontstyle'] = str_replace('B', '', $dom[$key]['fontstyle']);
								}
							} elseif (strtolower($dom[$key]['style']['font-weight'][0]) == 'b') {
								$dom[$key]['fontstyle'] .= 'B';
							}
						}
						if (isset($dom[$key]['style']['font-style']) AND (strtolower($dom[$key]['style']['font-style'][0]) == 'i')) {
							$dom[$key]['fontstyle'] .= 'I';
						}
						// font color
						if (isset($dom[$key]['style']['color']) AND (!LIMEPDF_STATIC::empty_string($dom[$key]['style']['color']))) {
							$dom[$key]['fgcolor'] = LIMEPDF_COLORS::convertHTMLColorToDec($dom[$key]['style']['color'], $this->spot_colors);
						} elseif ($dom[$key]['value'] == 'a') {
							$dom[$key]['fgcolor'] = $this->htmlLinkColorArray;
						}
						// background color
						if (isset($dom[$key]['style']['background-color']) AND (!LIMEPDF_STATIC::empty_string($dom[$key]['style']['background-color']))) {
							$dom[$key]['bgcolor'] = LIMEPDF_COLORS::convertHTMLColorToDec($dom[$key]['style']['background-color'], $this->spot_colors);
						}
						// text-decoration
						if (isset($dom[$key]['style']['text-decoration'])) {
							$decors = explode(' ', strtolower($dom[$key]['style']['text-decoration']));
							foreach ($decors as $dec) {
								$dec = trim($dec);
								if (!LIMEPDF_STATIC::empty_string($dec)) {
									if ($dec[0] == 'u') {
										// underline
										$dom[$key]['fontstyle'] .= 'U';
									} elseif ($dec[0] == 'l') {
										// line-through
										$dom[$key]['fontstyle'] .= 'D';
									} elseif ($dec[0] == 'o') {
										// overline
										$dom[$key]['fontstyle'] .= 'O';
									}
								}
							}
						} elseif ($dom[$key]['value'] == 'a') {
							$dom[$key]['fontstyle'] = $this->htmlLinkFontStyle;
						}
						// check for width attribute
						if (isset($dom[$key]['style']['width'])) {
							$dom[$key]['width'] = $dom[$key]['style']['width'];
						}
						// check for height attribute
						if (isset($dom[$key]['style']['height'])) {
							$dom[$key]['height'] = $dom[$key]['style']['height'];
						}
						// check for text alignment
						if (isset($dom[$key]['style']['text-align'])) {
							$dom[$key]['align'] = strtoupper($dom[$key]['style']['text-align'][0]);
						}
						// check for CSS border properties
						if (isset($dom[$key]['style']['border'])) {
							$borderstyle = $this->getCSSBorderStyle($dom[$key]['style']['border']);
							if (!empty($borderstyle)) {
								$dom[$key]['border']['LTRB'] = $borderstyle;
							}
						}
						if (isset($dom[$key]['style']['border-color'])) {
							$brd_colors = preg_split('/[\s]+/', trim($dom[$key]['style']['border-color']));
							if (isset($brd_colors[3])) {
								$dom[$key]['border']['L']['color'] = LIMEPDF_COLORS::convertHTMLColorToDec($brd_colors[3], $this->spot_colors);
							}
							if (isset($brd_colors[1])) {
								$dom[$key]['border']['R']['color'] = LIMEPDF_COLORS::convertHTMLColorToDec($brd_colors[1], $this->spot_colors);
							}
							if (isset($brd_colors[0])) {
								$dom[$key]['border']['T']['color'] = LIMEPDF_COLORS::convertHTMLColorToDec($brd_colors[0], $this->spot_colors);
							}
							if (isset($brd_colors[2])) {
								$dom[$key]['border']['B']['color'] = LIMEPDF_COLORS::convertHTMLColorToDec($brd_colors[2], $this->spot_colors);
							}
						}
						if (isset($dom[$key]['style']['border-width'])) {
							$brd_widths = preg_split('/[\s]+/', trim($dom[$key]['style']['border-width']));
							if (isset($brd_widths[3])) {
								$dom[$key]['border']['L']['width'] = $this->getCSSBorderWidth($brd_widths[3]);
							}
							if (isset($brd_widths[1])) {
								$dom[$key]['border']['R']['width'] = $this->getCSSBorderWidth($brd_widths[1]);
							}
							if (isset($brd_widths[0])) {
								$dom[$key]['border']['T']['width'] = $this->getCSSBorderWidth($brd_widths[0]);
							}
							if (isset($brd_widths[2])) {
								$dom[$key]['border']['B']['width'] = $this->getCSSBorderWidth($brd_widths[2]);
							}
						}
						if (isset($dom[$key]['style']['border-style'])) {
							$brd_styles = preg_split('/[\s]+/', trim($dom[$key]['style']['border-style']));
							if (isset($brd_styles[3]) AND ($brd_styles[3]!='none')) {
								$dom[$key]['border']['L']['cap'] = 'square';
								$dom[$key]['border']['L']['join'] = 'miter';
								$dom[$key]['border']['L']['dash'] = $this->getCSSBorderDashStyle($brd_styles[3]);
								if ($dom[$key]['border']['L']['dash'] < 0) {
									$dom[$key]['border']['L'] = array();
								}
							}
							if (isset($brd_styles[1])) {
								$dom[$key]['border']['R']['cap'] = 'square';
								$dom[$key]['border']['R']['join'] = 'miter';
								$dom[$key]['border']['R']['dash'] = $this->getCSSBorderDashStyle($brd_styles[1]);
								if ($dom[$key]['border']['R']['dash'] < 0) {
									$dom[$key]['border']['R'] = array();
								}
							}
							if (isset($brd_styles[0])) {
								$dom[$key]['border']['T']['cap'] = 'square';
								$dom[$key]['border']['T']['join'] = 'miter';
								$dom[$key]['border']['T']['dash'] = $this->getCSSBorderDashStyle($brd_styles[0]);
								if ($dom[$key]['border']['T']['dash'] < 0) {
									$dom[$key]['border']['T'] = array();
								}
							}
							if (isset($brd_styles[2])) {
								$dom[$key]['border']['B']['cap'] = 'square';
								$dom[$key]['border']['B']['join'] = 'miter';
								$dom[$key]['border']['B']['dash'] = $this->getCSSBorderDashStyle($brd_styles[2]);
								if ($dom[$key]['border']['B']['dash'] < 0) {
									$dom[$key]['border']['B'] = array();
								}
							}
						}
						$cellside = array('L' => 'left', 'R' => 'right', 'T' => 'top', 'B' => 'bottom');
						foreach ($cellside as $bsk => $bsv) {
							if (isset($dom[$key]['style']['border-'.$bsv])) {
								$borderstyle = $this->getCSSBorderStyle($dom[$key]['style']['border-'.$bsv]);
								if (!empty($borderstyle)) {
									$dom[$key]['border'][$bsk] = $borderstyle;
								}
							}
							if (isset($dom[$key]['style']['border-'.$bsv.'-color'])) {
								$dom[$key]['border'][$bsk]['color'] = LIMEPDF_COLORS::convertHTMLColorToDec($dom[$key]['style']['border-'.$bsv.'-color'], $this->spot_colors);
							}
							if (isset($dom[$key]['style']['border-'.$bsv.'-width'])) {
								$dom[$key]['border'][$bsk]['width'] = $this->getCSSBorderWidth($dom[$key]['style']['border-'.$bsv.'-width']);
							}
							if (isset($dom[$key]['style']['border-'.$bsv.'-style'])) {
								$dom[$key]['border'][$bsk]['dash'] = $this->getCSSBorderDashStyle($dom[$key]['style']['border-'.$bsv.'-style']);
								if ($dom[$key]['border'][$bsk]['dash'] < 0) {
									$dom[$key]['border'][$bsk] = array();
								}
							}
						}
						// check for CSS padding properties
						if (isset($dom[$key]['style']['padding'])) {
							$dom[$key]['padding'] = $this->getCSSPadding($dom[$key]['style']['padding']);
						} else {
							$dom[$key]['padding'] = $this->cell_padding;
						}
						foreach ($cellside as $psk => $psv) {
							if (isset($dom[$key]['style']['padding-'.$psv])) {
								$dom[$key]['padding'][$psk] = $this->getHTMLUnitToUnits($dom[$key]['style']['padding-'.$psv], 0, 'px', false);
							}
						}
						// check for CSS margin properties
						if (isset($dom[$key]['style']['margin'])) {
							$dom[$key]['margin'] = $this->getCSSMargin($dom[$key]['style']['margin']);
						} else {
							$dom[$key]['margin'] = $this->cell_margin;
						}
						foreach ($cellside as $psk => $psv) {
							if (isset($dom[$key]['style']['margin-'.$psv])) {
								$dom[$key]['margin'][$psk] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $dom[$key]['style']['margin-'.$psv]), 0, 'px', false);
							}
						}
						// check for CSS border-spacing properties
						if (isset($dom[$key]['style']['border-spacing'])) {
							$dom[$key]['border-spacing'] = $this->getCSSBorderMargin($dom[$key]['style']['border-spacing']);
						}
						// page-break-inside
						if (isset($dom[$key]['style']['page-break-inside']) AND ($dom[$key]['style']['page-break-inside'] == 'avoid')) {
							$dom[$key]['attribute']['nobr'] = 'true';
						}
						// page-break-before
						if (isset($dom[$key]['style']['page-break-before'])) {
							if ($dom[$key]['style']['page-break-before'] == 'always') {
								$dom[$key]['attribute']['pagebreak'] = 'true';
							} elseif ($dom[$key]['style']['page-break-before'] == 'left') {
								$dom[$key]['attribute']['pagebreak'] = 'left';
							} elseif ($dom[$key]['style']['page-break-before'] == 'right') {
								$dom[$key]['attribute']['pagebreak'] = 'right';
							}
						}
						// page-break-after
						if (isset($dom[$key]['style']['page-break-after'])) {
							if ($dom[$key]['style']['page-break-after'] == 'always') {
								$dom[$key]['attribute']['pagebreakafter'] = 'true';
							} elseif ($dom[$key]['style']['page-break-after'] == 'left') {
								$dom[$key]['attribute']['pagebreakafter'] = 'left';
							} elseif ($dom[$key]['style']['page-break-after'] == 'right') {
								$dom[$key]['attribute']['pagebreakafter'] = 'right';
							}
						}
					}
					if (isset($dom[$key]['attribute']['display'])) {
						$dom[$key]['hide'] = (trim(strtolower($dom[$key]['attribute']['display'])) == 'none');
					}
					if (isset($dom[$key]['attribute']['border']) AND ($dom[$key]['attribute']['border'] != 0)) {
						$borderstyle = $this->getCSSBorderStyle($dom[$key]['attribute']['border'].' solid black');
						if (!empty($borderstyle)) {
							$dom[$key]['border']['LTRB'] = $borderstyle;
						}
					}
					// check for font tag
					if ($dom[$key]['value'] == 'font') {
						// font family
						if (isset($dom[$key]['attribute']['face'])) {
							$dom[$key]['fontname'] = $this->getFontFamilyName($dom[$key]['attribute']['face']);
						}
						// font size
						if (isset($dom[$key]['attribute']['size'])) {
							if ($key > 0) {
								if ($dom[$key]['attribute']['size'][0] == '+') {
									$dom[$key]['fontsize'] = $dom[($dom[$key]['parent'])]['fontsize'] + intval(substr($dom[$key]['attribute']['size'], 1));
								} elseif ($dom[$key]['attribute']['size'][0] == '-') {
									$dom[$key]['fontsize'] = $dom[($dom[$key]['parent'])]['fontsize'] - intval(substr($dom[$key]['attribute']['size'], 1));
								} else {
									$dom[$key]['fontsize'] = intval($dom[$key]['attribute']['size']);
								}
							} else {
								$dom[$key]['fontsize'] = intval($dom[$key]['attribute']['size']);
							}
						}
					}
					// force natural alignment for lists
					if ((($dom[$key]['value'] == 'ul') OR ($dom[$key]['value'] == 'ol') OR ($dom[$key]['value'] == 'dl'))
						AND (!isset($dom[$key]['align']) OR LIMEPDF_STATIC::empty_string($dom[$key]['align']) OR ($dom[$key]['align'] != 'J'))) {
						if ($this->rtl) {
							$dom[$key]['align'] = 'R';
						} else {
							$dom[$key]['align'] = 'L';
						}
					}
					if (($dom[$key]['value'] == 'small') OR ($dom[$key]['value'] == 'sup') OR ($dom[$key]['value'] == 'sub')) {
						if (!isset($dom[$key]['attribute']['size']) AND !isset($dom[$key]['style']['font-size'])) {
							$dom[$key]['fontsize'] = $dom[$key]['fontsize'] * K_SMALL_RATIO;
						}
					}
					if (($dom[$key]['value'] == 'strong') OR ($dom[$key]['value'] == 'b')) {
						$dom[$key]['fontstyle'] .= 'B';
					}
					if (($dom[$key]['value'] == 'em') OR ($dom[$key]['value'] == 'i')) {
						$dom[$key]['fontstyle'] .= 'I';
					}
					if ($dom[$key]['value'] == 'u') {
						$dom[$key]['fontstyle'] .= 'U';
					}
					if (($dom[$key]['value'] == 'del') OR ($dom[$key]['value'] == 's') OR ($dom[$key]['value'] == 'strike')) {
						$dom[$key]['fontstyle'] .= 'D';
					}
					if (!isset($dom[$key]['style']['text-decoration']) AND ($dom[$key]['value'] == 'a')) {
						$dom[$key]['fontstyle'] = $this->htmlLinkFontStyle;
					}
					if (($dom[$key]['value'] == 'pre') OR ($dom[$key]['value'] == 'tt')) {
						$dom[$key]['fontname'] = $this->default_monospaced_font;
					}
					if (!empty($dom[$key]['value']) AND ($dom[$key]['value'][0] == 'h') AND (intval($dom[$key]['value'][1]) > 0) AND (intval($dom[$key]['value'][1]) < 7)) {
						// headings h1, h2, h3, h4, h5, h6
						if (!isset($dom[$key]['attribute']['size']) AND !isset($dom[$key]['style']['font-size'])) {
							$headsize = (4 - intval($dom[$key]['value'][1])) * 2;
							$dom[$key]['fontsize'] = $dom[0]['fontsize'] + $headsize;
						}
						if (!isset($dom[$key]['style']['font-weight'])) {
							$dom[$key]['fontstyle'] .= 'B';
						}
					}
					if (($dom[$key]['value'] == 'table')) {
						$dom[$key]['rows'] = 0; // number of rows
						$dom[$key]['trids'] = array(); // IDs of TR elements
						$dom[$key]['thead'] = ''; // table header rows
					}
					if (($dom[$key]['value'] == 'tr')) {
						$dom[$key]['cols'] = 0;
						if ($thead) {
							$dom[$key]['thead'] = true;
							// rows on thead block are printed as a separate table
						} else {
							$dom[$key]['thead'] = false;
							$parent = $dom[$key]['parent'];

							if (!isset($dom[$parent]['rows'])) {
								$dom[$parent]['rows'] = 0;
							}
							// store the number of rows on table element
							++$dom[$parent]['rows'];

							if (!isset($dom[$parent]['trids'])) {
								$dom[$parent]['trids'] = array();
							}

							// store the TR elements IDs on table element
							array_push($dom[$parent]['trids'], $key);
						}
					}
					if (($dom[$key]['value'] == 'th') OR ($dom[$key]['value'] == 'td')) {
						if (isset($dom[$key]['attribute']['colspan'])) {
							$colspan = intval($dom[$key]['attribute']['colspan']);
						} else {
							$colspan = 1;
						}
						$dom[$key]['attribute']['colspan'] = $colspan;
						$dom[($dom[$key]['parent'])]['cols'] += $colspan;
					}
					// text direction
					if (isset($dom[$key]['attribute']['dir'])) {
						$dom[$key]['dir'] = $dom[$key]['attribute']['dir'];
					}
					// set foreground color attribute
					if (isset($dom[$key]['attribute']['color']) AND (!LIMEPDF_STATIC::empty_string($dom[$key]['attribute']['color']))) {
						$dom[$key]['fgcolor'] = LIMEPDF_COLORS::convertHTMLColorToDec($dom[$key]['attribute']['color'], $this->spot_colors);
					} elseif (!isset($dom[$key]['style']['color']) AND ($dom[$key]['value'] == 'a')) {
						$dom[$key]['fgcolor'] = $this->htmlLinkColorArray;
					}
					// set background color attribute
					if (isset($dom[$key]['attribute']['bgcolor']) AND (!LIMEPDF_STATIC::empty_string($dom[$key]['attribute']['bgcolor']))) {
						$dom[$key]['bgcolor'] = LIMEPDF_COLORS::convertHTMLColorToDec($dom[$key]['attribute']['bgcolor'], $this->spot_colors);
					}
					// set stroke color attribute
					if (isset($dom[$key]['attribute']['strokecolor']) AND (!LIMEPDF_STATIC::empty_string($dom[$key]['attribute']['strokecolor']))) {
						$dom[$key]['strokecolor'] = LIMEPDF_COLORS::convertHTMLColorToDec($dom[$key]['attribute']['strokecolor'], $this->spot_colors);
					}
					// check for width attribute
					if (isset($dom[$key]['attribute']['width'])) {
						$dom[$key]['width'] = $dom[$key]['attribute']['width'];
					}
					// check for height attribute
					if (isset($dom[$key]['attribute']['height'])) {
						$dom[$key]['height'] = $dom[$key]['attribute']['height'];
					}
					// check for text alignment
					if (isset($dom[$key]['attribute']['align']) AND (!LIMEPDF_STATIC::empty_string($dom[$key]['attribute']['align'])) AND ($dom[$key]['value'] !== 'img')) {
						$dom[$key]['align'] = strtoupper($dom[$key]['attribute']['align'][0]);
					}
					// check for text rendering mode (the following attributes do not exist in HTML)
					if (isset($dom[$key]['attribute']['stroke'])) {
						// font stroke width
						$dom[$key]['stroke'] = $this->getHTMLUnitToUnits($dom[$key]['attribute']['stroke'], $dom[$key]['fontsize'], 'pt', true);
					}
					if (isset($dom[$key]['attribute']['fill'])) {
						// font fill
						if ($dom[$key]['attribute']['fill'] == 'true') {
							$dom[$key]['fill'] = true;
						} else {
							$dom[$key]['fill'] = false;
						}
					}
					if (isset($dom[$key]['attribute']['clip'])) {
						// clipping mode
						if ($dom[$key]['attribute']['clip'] == 'true') {
							$dom[$key]['clip'] = true;
						} else {
							$dom[$key]['clip'] = false;
						}
					}
				} // end opening tag
			} else {
				// text
				$dom[$key]['tag'] = false;
				$dom[$key]['block'] = false;
				$dom[$key]['parent'] = end($level);
				$dom[$key]['dir'] = $dom[$dom[$key]['parent']]['dir'];
				if (!empty($dom[$dom[$key]['parent']]['text-transform'])) {
					// text-transform for unicode requires mb_convert_case (Multibyte String Functions)
					if (function_exists('mb_convert_case')) {
						$ttm = array('capitalize' => MB_CASE_TITLE, 'uppercase' => MB_CASE_UPPER, 'lowercase' => MB_CASE_LOWER);
						if (isset($ttm[$dom[$dom[$key]['parent']]['text-transform']])) {
							$element = mb_convert_case($element, $ttm[$dom[$dom[$key]['parent']]['text-transform']], $this->encoding);
						}
					} elseif (!$this->isunicode) {
						switch ($dom[$dom[$key]['parent']]['text-transform']) {
							case 'capitalize': {
								$element = ucwords(strtolower($element));
								break;
							}
							case 'uppercase': {
								$element = strtoupper($element);
								break;
							}
							case 'lowercase': {
								$element = strtolower($element);
								break;
							}
						}
					}
					$element = preg_replace("/&NBSP;/i", "&nbsp;", $element);
				}
				$dom[$key]['value'] = stripslashes($this->unhtmlentities($element));
			}
			++$elkey;
			++$key;
		}
		return $dom;
	}

	/**
	 * Returns the string used to find spaces
	 * @return string
	 * @protected
	 * @author Nicola Asuni
	 * @since 4.8.024 (2010-01-15)
	 */
	protected function getSpaceString() {
		$spacestr = chr(32);
		if ($this->isUnicodeFont()) {
			$spacestr = chr(0).chr(32);
		}
		return $spacestr;
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
	 * Prints a cell (rectangular area) with optional borders, background color and html text string.
	 * The upper-left corner of the cell corresponds to the current position. After the call, the current position moves to the right or to the next line.<br />
	 * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done before outputting.
	 * IMPORTANT: The HTML must be well formatted - try to clean-up it using an application like HTML-Tidy before submitting.
	 * Supported tags are: a, b, blockquote, br, dd, del, div, dl, dt, em, font, h1, h2, h3, h4, h5, h6, hr, i, img, li, ol, p, pre, small, span, strong, sub, sup, table, tcpdf, td, th, thead, tr, tt, u, ul
	 * NOTE: all the HTML attributes must be enclosed in double-quote.
	 * @param float $w Cell width. If 0, the cell extends up to the right margin.
	 * @param float $h Cell minimum height. The cell extends automatically if needed.
	 * @param float|null $x upper-left corner X coordinate
	 * @param float|null $y upper-left corner Y coordinate
	 * @param string $html html text to print. Default value: empty string.
	 * @param mixed $border Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param int $ln Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right (or left for RTL language)</li><li>1: to the beginning of the next line</li><li>2: below</li></ul> Putting 1 is equivalent to putting 0 and calling Ln() just after. Default value: 0.
	 * @param boolean $fill Indicates if the cell background must be painted (true) or transparent (false).
	 * @param boolean $reseth if true reset the last cell height (default true).
	 * @param string $align Allows to center or align the text. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param boolean $autopadding if true, uses internal padding and automatically adjust it to account for line width.
	 * @see Multicell(), writeHTML()
	 * @public
	 */
	public function writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=false, $reseth=true, $align='', $autopadding=true) {
		return $this->MultiCell($w, $h, $html, $border, $align, $fill, $ln, $x, $y, $reseth, 0, true, $autopadding, 0, 'T', false);
	}

	/**
	 * Allows to preserve some HTML formatting (limited support).<br />
	 * IMPORTANT: The HTML must be well formatted - try to clean-up it using an application like HTML-Tidy before submitting.
	 * Supported tags are: a, b, blockquote, br, dd, del, div, dl, dt, em, font, h1, h2, h3, h4, h5, h6, hr, i, img, li, ol, p, pre, small, span, strong, sub, sup, table, tcpdf, td, th, thead, tr, tt, u, ul
	 * NOTE: all the HTML attributes must be enclosed in double-quote.
	 * @param string $html text to display
	 * @param boolean $ln if true add a new line after text (default = true)
	 * @param boolean $fill Indicates if the background must be painted (true) or transparent (false).
	 * @param boolean $reseth if true reset the last cell height (default false).
	 * @param boolean $cell if true add the current left (or right for RTL) padding to each Write (default false).
	 * @param string $align Allows to center or align the text. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @public
	 */
	public function writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='') {
		$gvars = $this->getGraphicVars();
		// store current values
		$prev_cell_margin = $this->cell_margin;
		$prev_cell_padding = $this->cell_padding;
		$prevPage = $this->page;
		$prevlMargin = $this->lMargin;
		$prevrMargin = $this->rMargin;
		$curfontname = $this->FontFamily;
		$curfontstyle = $this->FontStyle;
		$curfontsize = $this->FontSizePt;
		$curfontascent = $this->getFontAscent($curfontname, $curfontstyle, $curfontsize);
		$curfontdescent = $this->getFontDescent($curfontname, $curfontstyle, $curfontsize);
		$curfontstretcing = $this->font_stretching;
		$curfonttracking = $this->font_spacing;
		$this->newline = true;
		$newline = true;
		$startlinepage = $this->page;
		$minstartliney = $this->y;
		$maxbottomliney = 0;
		$startlinex = $this->x;
		$startliney = $this->y;
		$yshift = 0;
		$loop = 0;
		$curpos = 0;
		$this_method_vars = array();
		$undo = false;
		$fontaligned = false;
		$reverse_dir = false; // true when the text direction is reversed
		$this->premode = false;
		if ($this->inxobj) {
			// we are inside an XObject template
			$pask = count($this->xobjects[$this->xobjid]['annotations']);
		} elseif (isset($this->PageAnnots[$this->page])) {
			$pask = count($this->PageAnnots[$this->page]);
		} else {
			$pask = 0;
		}
		if ($this->inxobj) {
			// we are inside an XObject template
			$startlinepos = strlen($this->xobjects[$this->xobjid]['outdata']);
		} elseif (!$this->InFooter) {
			if (isset($this->footerlen[$this->page])) {
				$this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
			} else {
				$this->footerpos[$this->page] = $this->pagelen[$this->page];
			}
			$startlinepos = $this->footerpos[$this->page];
		} else {
			// we are inside the footer
			$startlinepos = $this->pagelen[$this->page];
		}
		$lalign = $align;
		$plalign = $align;
		if ($this->rtl) {
			$w = $this->x - $this->lMargin;
		} else {
			$w = $this->w - $this->rMargin - $this->x;
		}
		$w -= ($this->cell_padding['L'] + $this->cell_padding['R']);
		if ($cell) {
			if ($this->rtl) {
				$this->x -= $this->cell_padding['R'];
				$this->lMargin += $this->cell_padding['L'];
			} else {
				$this->x += $this->cell_padding['L'];
				$this->rMargin += $this->cell_padding['R'];
			}
		}
		if ($this->customlistindent >= 0) {
			$this->listindent = $this->customlistindent;
		} else {
			$this->listindent = $this->GetStringWidth('000000');
		}
		$this->listindentlevel = 0;
		// save previous states
		$prev_cell_height_ratio = $this->cell_height_ratio;
		$prev_listnum = $this->listnum;
		$prev_listordered = $this->listordered;
		$prev_listcount = $this->listcount;
		$prev_lispacer = $this->lispacer;
		$this->listnum = 0;
		$this->listordered = array();
		$this->listcount = array();
		$this->lispacer = '';
		if ((LIMEPDF_STATIC::empty_string($this->lasth)) OR ($reseth)) {
			// reset row height
			$this->resetLastH();
		}
		$dom = $this->getHtmlDomArray($html);
		$maxel = count($dom);
		$key = 0;
		while ($key < $maxel) {
			if ($dom[$key]['tag'] AND $dom[$key]['opening'] AND $dom[$key]['hide']) {
				// store the node key
				$hidden_node_key = $key;
				if ($dom[$key]['self']) {
					// skip just this self-closing tag
					++$key;
				} else {
					// skip this and all children tags
					while (($key < $maxel) AND (!$dom[$key]['tag'] OR $dom[$key]['opening'] OR ($dom[$key]['parent'] != $hidden_node_key))) {
						// skip hidden objects
						++$key;
					}
					++$key;
				}
			}
			if ($key == $maxel) break;
			if ($dom[$key]['tag'] AND $dom[$key]['opening'] AND !empty($dom[$key]['attribute']['id'])) {
				$this->setDestination($dom[$key]['attribute']['id']);
			}
			if ($dom[$key]['tag'] AND isset($dom[$key]['attribute']['pagebreak'])) {
				// check for pagebreak
				if (($dom[$key]['attribute']['pagebreak'] == 'true') OR ($dom[$key]['attribute']['pagebreak'] == 'left') OR ($dom[$key]['attribute']['pagebreak'] == 'right')) {
					// add a page (or trig AcceptPageBreak() for multicolumn mode)
					$this->checkPageBreak($this->PageBreakTrigger + 1);
					$this->htmlvspace = ($this->PageBreakTrigger + 1);
				}
				if ((($dom[$key]['attribute']['pagebreak'] == 'left') AND (((!$this->rtl) AND (($this->page % 2) == 0)) OR (($this->rtl) AND (($this->page % 2) != 0))))
					OR (($dom[$key]['attribute']['pagebreak'] == 'right') AND (((!$this->rtl) AND (($this->page % 2) != 0)) OR (($this->rtl) AND (($this->page % 2) == 0))))) {
					// add a page (or trig AcceptPageBreak() for multicolumn mode)
					$this->checkPageBreak($this->PageBreakTrigger + 1);
					$this->htmlvspace = ($this->PageBreakTrigger + 1);
				}
			}
			if ($dom[$key]['tag'] AND $dom[$key]['opening'] AND isset($dom[$key]['attribute']['nobr']) AND ($dom[$key]['attribute']['nobr'] == 'true')) {
				if (isset($dom[($dom[$key]['parent'])]['attribute']['nobr']) AND ($dom[($dom[$key]['parent'])]['attribute']['nobr'] == 'true')) {
					$dom[$key]['attribute']['nobr'] = false;
				} else {
					// store current object
					$this->startTransaction();
					// save this method vars
					$this_method_vars['html'] = $html;
					$this_method_vars['ln'] = $ln;
					$this_method_vars['fill'] = $fill;
					$this_method_vars['reseth'] = $reseth;
					$this_method_vars['cell'] = $cell;
					$this_method_vars['align'] = $align;
					$this_method_vars['gvars'] = $gvars;
					$this_method_vars['prevPage'] = $prevPage;
					$this_method_vars['prev_cell_margin'] = $prev_cell_margin;
					$this_method_vars['prev_cell_padding'] = $prev_cell_padding;
					$this_method_vars['prevlMargin'] = $prevlMargin;
					$this_method_vars['prevrMargin'] = $prevrMargin;
					$this_method_vars['curfontname'] = $curfontname;
					$this_method_vars['curfontstyle'] = $curfontstyle;
					$this_method_vars['curfontsize'] = $curfontsize;
					$this_method_vars['curfontascent'] = $curfontascent;
					$this_method_vars['curfontdescent'] = $curfontdescent;
					$this_method_vars['curfontstretcing'] = $curfontstretcing;
					$this_method_vars['curfonttracking'] = $curfonttracking;
					$this_method_vars['minstartliney'] = $minstartliney;
					$this_method_vars['maxbottomliney'] = $maxbottomliney;
					$this_method_vars['yshift'] = $yshift;
					$this_method_vars['startlinepage'] = $startlinepage;
					$this_method_vars['startlinepos'] = $startlinepos;
					$this_method_vars['startlinex'] = $startlinex;
					$this_method_vars['startliney'] = $startliney;
					$this_method_vars['newline'] = $newline;
					$this_method_vars['loop'] = $loop;
					$this_method_vars['curpos'] = $curpos;
					$this_method_vars['pask'] = $pask;
					$this_method_vars['lalign'] = $lalign;
					$this_method_vars['plalign'] = $plalign;
					$this_method_vars['w'] = $w;
					$this_method_vars['prev_cell_height_ratio'] = $prev_cell_height_ratio;
					$this_method_vars['prev_listnum'] = $prev_listnum;
					$this_method_vars['prev_listordered'] = $prev_listordered;
					$this_method_vars['prev_listcount'] = $prev_listcount;
					$this_method_vars['prev_lispacer'] = $prev_lispacer;
					$this_method_vars['fontaligned'] = $fontaligned;
					$this_method_vars['key'] = $key;
					$this_method_vars['dom'] = $dom;
				}
			}
			// print THEAD block
			if (($dom[$key]['value'] == 'tr') AND isset($dom[$key]['thead']) AND $dom[$key]['thead']) {
				if (isset($dom[$key]['parent']) AND isset($dom[$dom[$key]['parent']]['thead']) AND !LIMEPDF_STATIC::empty_string($dom[$dom[$key]['parent']]['thead'])) {
					$this->inthead = true;
					// print table header (thead)
					$this->writeHTML($this->thead, false, false, false, false, '');
					// check if we are on a new page or on a new column
					if (($this->y < $this->start_transaction_y) OR ($this->checkPageBreak($this->lasth, '', false))) {
						// we are on a new page or on a new column and the total object height is less than the available vertical space.
						// restore previous object
						$this->rollbackTransaction(true);
						// restore previous values
						foreach ($this_method_vars as $vkey => $vval) {
							$$vkey = $vval;
						}
						// disable table header
						$tmp_thead = $this->thead;
						$this->thead = '';
						// add a page (or trig AcceptPageBreak() for multicolumn mode)
						$pre_y = $this->y;
						if ((!$this->checkPageBreak($this->PageBreakTrigger + 1)) AND ($this->y < $pre_y)) {
							// fix for multicolumn mode
							$startliney = $this->y;
						}
						$this->start_transaction_page = $this->page;
						$this->start_transaction_y = $this->y;
						// restore table header
						$this->thead = $tmp_thead;
						// fix table border properties
						if (isset($dom[$dom[$key]['parent']]['attribute']['cellspacing'])) {
							$tmp_cellspacing = $this->getHTMLUnitToUnits($dom[$dom[$key]['parent']]['attribute']['cellspacing'], 1, 'px');
						} elseif (isset($dom[$dom[$key]['parent']]['border-spacing'])) {
							$tmp_cellspacing = $dom[$dom[$key]['parent']]['border-spacing']['V'];
						} else {
							$tmp_cellspacing = 0;
						}
						$dom[$dom[$key]['parent']]['borderposition']['page'] = $this->page;
						$dom[$dom[$key]['parent']]['borderposition']['column'] = $this->current_column;
						$dom[$dom[$key]['parent']]['borderposition']['y'] = $this->y + $tmp_cellspacing;
						$xoffset = ($this->x - $dom[$dom[$key]['parent']]['borderposition']['x']);
						$dom[$dom[$key]['parent']]['borderposition']['x'] += $xoffset;
						$dom[$dom[$key]['parent']]['borderposition']['xmax'] += $xoffset;
						// print table header (thead)
						$this->writeHTML($this->thead, false, false, false, false, '');
					}
				}
				// move $key index forward to skip THEAD block
				while ( ($key < $maxel) AND (!(
					($dom[$key]['tag'] AND $dom[$key]['opening'] AND ($dom[$key]['value'] == 'tr') AND (!isset($dom[$key]['thead']) OR !$dom[$key]['thead']))
					OR ($dom[$key]['tag'] AND (!$dom[$key]['opening']) AND ($dom[$key]['value'] == 'table'))) )) {
					++$key;
				}
			}
			if ($dom[$key]['tag'] OR ($key == 0)) {
				if ((($dom[$key]['value'] == 'table') OR ($dom[$key]['value'] == 'tr')) AND (isset($dom[$key]['align']))) {
					$dom[$key]['align'] = ($this->rtl) ? 'R' : 'L';
				}
				// vertically align image in line
				if ((!$this->newline) AND ($dom[$key]['value'] == 'img') AND (isset($dom[$key]['height'])) AND ($dom[$key]['height'] > 0)) {
					// get image height
					$imgh = $this->getHTMLUnitToUnits($dom[$key]['height'], ($dom[$key]['fontsize'] / $this->k), 'px');
					$autolinebreak = false;
					if (!empty($dom[$key]['width'])) {
						$imgw = $this->getHTMLUnitToUnits($dom[$key]['width'], ($dom[$key]['fontsize'] / $this->k), 'px', false);
						if (($imgw <= ($this->w - $this->lMargin - $this->rMargin - $this->cell_padding['L'] - $this->cell_padding['R']))
							AND ((($this->rtl) AND (($this->x - $imgw) < ($this->lMargin + $this->cell_padding['L'])))
							OR ((!$this->rtl) AND (($this->x + $imgw) > ($this->w - $this->rMargin - $this->cell_padding['R']))))) {
							// add automatic line break
							$autolinebreak = true;
							$this->Ln('', $cell);
							if ((!$dom[($key-1)]['tag']) AND ($dom[($key-1)]['value'] == ' ')) {
								// go back to evaluate this line break
								--$key;
							}
						}
					}
					if (!$autolinebreak) {
						if ($this->inPageBody()) {
							$pre_y = $this->y;
							// check for page break
							if ((!$this->checkPageBreak($imgh)) AND ($this->y < $pre_y)) {
								// fix for multicolumn mode
								$startliney = $this->y;
							}
						}
						if ($this->page > $startlinepage) {
							// fix line splitted over two pages
							if (isset($this->footerlen[$startlinepage])) {
								$curpos = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
							}
							// line to be moved one page forward
							$pagebuff = $this->getPageBuffer($startlinepage);
							$linebeg = substr($pagebuff, $startlinepos, ($curpos - $startlinepos));
							$tstart = substr($pagebuff, 0, $startlinepos);
							$tend = substr($this->getPageBuffer($startlinepage), $curpos);
							// remove line from previous page
							$this->setPageBuffer($startlinepage, $tstart.''.$tend);
							$pagebuff = $this->getPageBuffer($this->page);
							$tstart = substr($pagebuff, 0, $this->cntmrk[$this->page]);
							$tend = substr($pagebuff, $this->cntmrk[$this->page]);
							// add line start to current page
							$yshift = ($minstartliney - $this->y);
							if ($fontaligned) {
								$yshift += ($curfontsize / $this->k);
							}
							$try = sprintf('1 0 0 1 0 %F cm', ($yshift * $this->k));
							$this->setPageBuffer($this->page, $tstart."\nq\n".$try."\n".$linebeg."\nQ\n".$tend);
							// shift the annotations and links
							if (isset($this->PageAnnots[$this->page])) {
								$next_pask = count($this->PageAnnots[$this->page]);
							} else {
								$next_pask = 0;
							}
							if (isset($this->PageAnnots[$startlinepage])) {
								foreach ($this->PageAnnots[$startlinepage] as $pak => $pac) {
									if ($pak >= $pask) {
										$this->PageAnnots[$this->page][] = $pac;
										unset($this->PageAnnots[$startlinepage][$pak]);
										$npak = count($this->PageAnnots[$this->page]) - 1;
										$this->PageAnnots[$this->page][$npak]['y'] -= $yshift;
									}
								}
							}
							$pask = $next_pask;
							$startlinepos = $this->cntmrk[$this->page];
							$startlinepage = $this->page;
							$startliney = $this->y;
							$this->newline = false;
						}
						$this->y += ($this->getCellHeight($curfontsize / $this->k) - ($curfontdescent * $this->cell_height_ratio) - $imgh);
						$minstartliney = min($this->y, $minstartliney);
						$maxbottomliney = ($startliney + $this->getCellHeight($curfontsize / $this->k));
					}
				} elseif (isset($dom[$key]['fontname']) OR isset($dom[$key]['fontstyle']) OR isset($dom[$key]['fontsize']) OR isset($dom[$key]['line-height'])) {
					// account for different font size
					$pfontname = $curfontname;
					$pfontstyle = $curfontstyle;
					$pfontsize = $curfontsize;
					$fontname = (isset($dom[$key]['fontname']) ? $dom[$key]['fontname'] : $curfontname);
					$fontstyle = (isset($dom[$key]['fontstyle']) ? $dom[$key]['fontstyle'] : $curfontstyle);
					$fontsize = (isset($dom[$key]['fontsize']) ? $dom[$key]['fontsize'] : $curfontsize);
					$fontascent = $this->getFontAscent($fontname, $fontstyle, $fontsize);
					$fontdescent = $this->getFontDescent($fontname, $fontstyle, $fontsize);
					if (($fontname != $curfontname) OR ($fontstyle != $curfontstyle) OR ($fontsize != $curfontsize)
						OR ($this->cell_height_ratio != $dom[$key]['line-height'])
						OR ($dom[$key]['tag'] AND $dom[$key]['opening'] AND ($dom[$key]['value'] == 'li')) ) {
						if (($key < ($maxel - 1)) AND (
								($dom[$key]['tag'] AND $dom[$key]['opening'] AND ($dom[$key]['value'] == 'li'))
								OR ($this->cell_height_ratio != $dom[$key]['line-height'])
								OR (!$this->newline AND is_numeric($fontsize) AND is_numeric($curfontsize)
								AND ($fontsize >= 0) AND ($curfontsize >= 0)
								AND (($fontsize != $curfontsize) OR ($fontstyle != $curfontstyle) OR ($fontname != $curfontname)))
							)) {
							if ($this->page > $startlinepage) {
								// fix lines splitted over two pages
								if (isset($this->footerlen[$startlinepage])) {
									$curpos = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
								}
								// line to be moved one page forward
								$pagebuff = $this->getPageBuffer($startlinepage);
								$linebeg = substr($pagebuff, $startlinepos, ($curpos - $startlinepos));
								$tstart = substr($pagebuff, 0, $startlinepos);
								$tend = substr($this->getPageBuffer($startlinepage), $curpos);
								// remove line start from previous page
								$this->setPageBuffer($startlinepage, $tstart.''.$tend);
								$pagebuff = $this->getPageBuffer($this->page);
								$tstart = substr($pagebuff, 0, $this->cntmrk[$this->page]);
								$tend = substr($pagebuff, $this->cntmrk[$this->page]);
								// add line start to current page
								$yshift = ($minstartliney - $this->y);
								$try = sprintf('1 0 0 1 0 %F cm', ($yshift * $this->k));
								$this->setPageBuffer($this->page, $tstart."\nq\n".$try."\n".$linebeg."\nQ\n".$tend);
								// shift the annotations and links
								if (isset($this->PageAnnots[$this->page])) {
									$next_pask = count($this->PageAnnots[$this->page]);
								} else {
									$next_pask = 0;
								}
								if (isset($this->PageAnnots[$startlinepage])) {
									foreach ($this->PageAnnots[$startlinepage] as $pak => $pac) {
										if ($pak >= $pask) {
											$this->PageAnnots[$this->page][] = $pac;
											unset($this->PageAnnots[$startlinepage][$pak]);
											$npak = count($this->PageAnnots[$this->page]) - 1;
											$this->PageAnnots[$this->page][$npak]['y'] -= $yshift;
										}
									}
								}
								$pask = $next_pask;
								$startlinepos = $this->cntmrk[$this->page];
								$startlinepage = $this->page;
								$startliney = $this->y;
							}
							if (!isset($dom[$key]['line-height'])) {
								$dom[$key]['line-height'] = $this->cell_height_ratio;
							}
							if (!$dom[$key]['block']) {
								if (!(isset($dom[($key + 1)]) AND $dom[($key + 1)]['tag'] AND (!$dom[($key + 1)]['opening']) AND ($dom[($key + 1)]['value'] != 'li') AND $dom[$key]['tag'] AND (!$dom[$key]['opening']))) {
									$this->y += (((($curfontsize * $this->cell_height_ratio) - ($fontsize * $dom[$key]['line-height'])) / $this->k) + $curfontascent - $fontascent - $curfontdescent + $fontdescent) / 2;
								}
								if (($dom[$key]['value'] != 'sup') AND ($dom[$key]['value'] != 'sub')) {
									$current_line_align_data = array($key, $minstartliney, $maxbottomliney);
									if (isset($line_align_data) AND (($line_align_data[0] == ($key - 1)) OR (($line_align_data[0] == ($key - 2)) AND (isset($dom[($key - 1)])) AND (preg_match('/^([\s]+)$/', $dom[($key - 1)]['value']) > 0)))) {
										$minstartliney = min($this->y, $line_align_data[1]);
										$maxbottomliney = max(($this->y + $this->getCellHeight($fontsize / $this->k)), $line_align_data[2]);
									} else {
										$minstartliney = min($this->y, $minstartliney);
										$maxbottomliney = max(($this->y + $this->getCellHeight($fontsize / $this->k)), $maxbottomliney);
									}
									$line_align_data = $current_line_align_data;
								}
							}
							$this->cell_height_ratio = $dom[$key]['line-height'];
							$fontaligned = true;
						}
						$this->setFont($fontname, $fontstyle, $fontsize);
						// reset row height
						$this->resetLastH();
						$curfontname = $fontname;
						$curfontstyle = $fontstyle;
						$curfontsize = $fontsize;
						$curfontascent = $fontascent;
						$curfontdescent = $fontdescent;
					}
				}
				// set text rendering mode
				$textstroke = isset($dom[$key]['stroke']) ? $dom[$key]['stroke'] : $this->textstrokewidth;
				$textfill = isset($dom[$key]['fill']) ? $dom[$key]['fill'] : (($this->textrendermode % 2) == 0);
				$textclip = isset($dom[$key]['clip']) ? $dom[$key]['clip'] : ($this->textrendermode > 3);
				$this->setTextRenderingMode($textstroke, $textfill, $textclip);
				if (isset($dom[$key]['font-stretch']) AND ($dom[$key]['font-stretch'] !== false)) {
					$this->setFontStretching($dom[$key]['font-stretch']);
				}
				if (isset($dom[$key]['letter-spacing']) AND ($dom[$key]['letter-spacing'] !== false)) {
					$this->setFontSpacing($dom[$key]['letter-spacing']);
				}
				if (($plalign == 'J') AND $dom[$key]['block']) {
					$plalign = '';
				}
				// get current position on page buffer
				$curpos = $this->pagelen[$startlinepage];
				if (isset($dom[$key]['bgcolor']) AND ($dom[$key]['bgcolor'] !== false)) {
					$this->setFillColorArray($dom[$key]['bgcolor']);
					$wfill = true;
				} else {
					$wfill = $fill | false;
				}
				if (isset($dom[$key]['fgcolor']) AND ($dom[$key]['fgcolor'] !== false)) {
					$this->setTextColorArray($dom[$key]['fgcolor']);
				}
				if (isset($dom[$key]['strokecolor']) AND ($dom[$key]['strokecolor'] !== false)) {
					$this->setDrawColorArray($dom[$key]['strokecolor']);
				}
				if (isset($dom[$key]['align'])) {
					$lalign = $dom[$key]['align'];
				}
				if (LIMEPDF_STATIC::empty_string($lalign)) {
					$lalign = $align;
				}
			}
			// align lines
			if ($this->newline AND (strlen($dom[$key]['value']) > 0) AND ($dom[$key]['value'] != 'td') AND ($dom[$key]['value'] != 'th')) {
				$newline = true;
				$fontaligned = false;
				// we are at the beginning of a new line
				if (isset($startlinex)) {
					$yshift = ($minstartliney - $startliney);
					if (($yshift > 0) OR ($this->page > $startlinepage)) {
						$yshift = 0;
					}
					$t_x = 0;
					// the last line must be shifted to be aligned as requested
					$linew = abs($this->endlinex - $startlinex);
					if ($this->inxobj) {
						// we are inside an XObject template
						$pstart = substr($this->xobjects[$this->xobjid]['outdata'], 0, $startlinepos);
						if (isset($opentagpos)) {
							$midpos = $opentagpos;
						} else {
							$midpos = 0;
						}
						if ($midpos > 0) {
							$pmid = substr($this->xobjects[$this->xobjid]['outdata'], $startlinepos, ($midpos - $startlinepos));
							$pend = substr($this->xobjects[$this->xobjid]['outdata'], $midpos);
						} else {
							$pmid = substr($this->xobjects[$this->xobjid]['outdata'], $startlinepos);
							$pend = '';
						}
					} else {
						$pstart = substr($this->getPageBuffer($startlinepage), 0, $startlinepos);
						if (isset($opentagpos) AND isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
							$this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
							$midpos = min($opentagpos, $this->footerpos[$startlinepage]);
						} elseif (isset($opentagpos)) {
							$midpos = $opentagpos;
						} elseif (isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
							$this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
							$midpos = $this->footerpos[$startlinepage];
						} else {
							$midpos = 0;
						}
						if ($midpos > 0) {
							$pmid = substr($this->getPageBuffer($startlinepage), $startlinepos, ($midpos - $startlinepos));
							$pend = substr($this->getPageBuffer($startlinepage), $midpos);
						} else {
							$pmid = substr($this->getPageBuffer($startlinepage), $startlinepos);
							$pend = '';
						}
					}
					if ((((($plalign == 'C') OR ($plalign == 'J') OR (($plalign == 'R') AND (!$this->rtl)) OR (($plalign == 'L') AND ($this->rtl)))))) {
						// calculate shifting amount
						$tw = $w;
						if (($plalign == 'J') AND $this->isRTLTextDir() AND ($this->num_columns > 1)) {
							$tw += $this->cell_padding['R'];
						}
						if ($this->lMargin != $prevlMargin) {
							$tw += ($prevlMargin - $this->lMargin);
						}
						if ($this->rMargin != $prevrMargin) {
							$tw += ($prevrMargin - $this->rMargin);
						}
						$one_space_width = $this->GetStringWidth(chr(32));
						$no = 0; // number of spaces on a line contained on a single block
						if ($this->isRTLTextDir()) { // RTL
							// remove left space if exist
							$pos1 = LIMEPDF_STATIC::revstrpos($pmid, '[(');
							if ($pos1 > 0) {
								$pos1 = intval($pos1);
								if ($this->isUnicodeFont()) {
									$pos2 = intval(LIMEPDF_STATIC::revstrpos($pmid, '[('.chr(0).chr(32)));
									$spacelen = 2;
								} else {
									$pos2 = intval(LIMEPDF_STATIC::revstrpos($pmid, '[('.chr(32)));
									$spacelen = 1;
								}
								if ($pos1 == $pos2) {
									$pmid = substr($pmid, 0, ($pos1 + 2)).substr($pmid, ($pos1 + 2 + $spacelen));
									if (substr($pmid, $pos1, 4) == '[()]') {
										$linew -= $one_space_width;
									} elseif ($pos1 == strpos($pmid, '[(')) {
										$no = 1;
									}
								}
							}
						} else { // LTR
							// remove right space if exist
							$pos1 = LIMEPDF_STATIC::revstrpos($pmid, ')]');
							if ($pos1 > 0) {
								$pos1 = intval($pos1);
								if ($this->isUnicodeFont()) {
									$pos2 = intval(LIMEPDF_STATIC::revstrpos($pmid, chr(0).chr(32).')]')) + 2;
									$spacelen = 2;
								} else {
									$pos2 = intval(LIMEPDF_STATIC::revstrpos($pmid, chr(32).')]')) + 1;
									$spacelen = 1;
								}
								if ($pos1 == $pos2) {
									$pmid = substr($pmid, 0, ($pos1 - $spacelen)).substr($pmid, $pos1);
									$linew -= $one_space_width;
								}
							}
						}
						$mdiff = ($tw - $linew);
						if ($plalign == 'C') {
							if ($this->rtl) {
								$t_x = -($mdiff / 2);
							} else {
								$t_x = ($mdiff / 2);
							}
						} elseif ($plalign == 'R') {
							// right alignment on LTR document
							$t_x = $mdiff;
						} elseif ($plalign == 'L') {
							// left alignment on RTL document
							$t_x = -$mdiff;
						} elseif (($plalign == 'J') AND ($plalign == $lalign)) {
							// Justification
							if ($this->isRTLTextDir()) {
								// align text on the left
								$t_x = -$mdiff;
							}
							$ns = 0; // number of spaces
							$pmidtemp = $pmid;
							// escape special characters
							$pmidtemp = preg_replace('/[\\\][\(]/x', '\\#!#OP#!#', $pmidtemp);
							$pmidtemp = preg_replace('/[\\\][\)]/x', '\\#!#CP#!#', $pmidtemp);
							// search spaces
							if (preg_match_all('/\[\(([^\)]*)\)\]/x', $pmidtemp, $lnstring, PREG_PATTERN_ORDER)) {
								$spacestr = $this->getSpaceString();
								$maxkk = count($lnstring[1]) - 1;
								for ($kk=0; $kk <= $maxkk; ++$kk) {
									// restore special characters
									$lnstring[1][$kk] = str_replace('#!#OP#!#', '(', $lnstring[1][$kk]);
									$lnstring[1][$kk] = str_replace('#!#CP#!#', ')', $lnstring[1][$kk]);
									// store number of spaces on the strings
									$lnstring[2][$kk] = substr_count($lnstring[1][$kk], $spacestr);
									// count total spaces on line
									$ns += $lnstring[2][$kk];
									$lnstring[3][$kk] = $ns;
								}
								if ($ns == 0) {
									$ns = 1;
								}
								// calculate additional space to add to each existing space
								$spacewidth = ($mdiff / ($ns - $no)) * $this->k;
								if ($this->FontSize <= 0) {
									$this->FontSize = 1;
								}
								$spacewidthu = -1000 * ($mdiff + (($ns + $no) * $one_space_width)) / $ns / $this->FontSize;
								if ($this->font_spacing != 0) {
									// fixed spacing mode
									$osw = -1000 * $this->font_spacing / $this->FontSize;
									$spacewidthu += $osw;
								}
								$nsmax = $ns;
								$ns = 0;
								reset($lnstring);
								$offset = 0;
								$strcount = 0;
								$prev_epsposbeg = 0;
								$textpos = 0;
								if ($this->isRTLTextDir()) {
									$textpos = $this->wPt;
								}
								while (preg_match('/([0-9\.\+\-]*)[\s](Td|cm|m|l|c|re)[\s]/x', $pmid, $strpiece, PREG_OFFSET_CAPTURE, $offset) == 1) {
									// check if we are inside a string section '[( ... )]'
									$stroffset = strpos($pmid, '[(', $offset);
									if (($stroffset !== false) AND ($stroffset <= $strpiece[2][1])) {
										// set offset to the end of string section
										$offset = strpos($pmid, ')]', $stroffset);
										while (($offset !== false) AND ($pmid[($offset - 1)] == '\\')) {
											$offset = strpos($pmid, ')]', ($offset + 1));
										}
										if ($offset === false) {
											$this->Error('HTML Justification: malformed PDF code.');
										}
										continue;
									}
									if ($this->isRTLTextDir()) {
										$spacew = ($spacewidth * ($nsmax - $ns));
									} else {
										$spacew = ($spacewidth * $ns);
									}
									$offset = $strpiece[2][1] + strlen($strpiece[2][0]);
									$epsposend = strpos($pmid, $this->epsmarker.'Q', $offset);
									if ($epsposend !== null) {
										$epsposend += strlen($this->epsmarker.'Q');
										$epsposbeg = strpos($pmid, 'q'.$this->epsmarker, $offset);
										if ($epsposbeg === null) {
											$epsposbeg = strpos($pmid, 'q'.$this->epsmarker, ($prev_epsposbeg - 6));
											$prev_epsposbeg = $epsposbeg;
										}
										if (($epsposbeg > 0) AND ($epsposend > 0) AND ($offset > $epsposbeg) AND ($offset < $epsposend)) {
											// shift EPS images
											$trx = sprintf('1 0 0 1 %F 0 cm', $spacew);
											$pmid_b = substr($pmid, 0, $epsposbeg);
											$pmid_m = substr($pmid, $epsposbeg, ($epsposend - $epsposbeg));
											$pmid_e = substr($pmid, $epsposend);
											$pmid = $pmid_b."\nq\n".$trx."\n".$pmid_m."\nQ\n".$pmid_e;
											$offset = $epsposend;
											continue;
										}
									}
									$currentxpos = 0;
									// shift blocks of code
									switch ($strpiece[2][0]) {
										case 'Td':
										case 'cm':
										case 'm':
										case 'l': {
											// get current X position
											preg_match('/([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s]('.$strpiece[2][0].')([\s]*)/x', $pmid, $xmatches);
											if (!isset($xmatches[1])) {
												break;
											}
											$currentxpos = $xmatches[1];
											$textpos = $currentxpos;
											if (($strcount <= $maxkk) AND ($strpiece[2][0] == 'Td')) {
												$ns = $lnstring[3][$strcount];
												if ($this->isRTLTextDir()) {
													$spacew = ($spacewidth * ($nsmax - $ns));
												}
												++$strcount;
											}
											// justify block
											if (preg_match('/([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s]('.$strpiece[2][0].')([\s]*)/x', $pmid, $pmatch) == 1) {
												$newpmid = sprintf('%F',(floatval($pmatch[1]) + $spacew)).' '.$pmatch[2].' x*#!#*x'.$pmatch[3].$pmatch[4];
												$pmid = str_replace($pmatch[0], $newpmid, $pmid);
												unset($pmatch, $newpmid);
											}
											break;
										}
										case 're': {
											// justify block
											if (!LIMEPDF_STATIC::empty_string($this->lispacer)) {
												$this->lispacer = '';
												break;
											}
											preg_match('/([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s](re)([\s]*)/x', $pmid, $xmatches);
											if (!isset($xmatches[1])) {
												break;
											}
											$currentxpos = $xmatches[1];
											$x_diff = 0;
											$w_diff = 0;
											if ($this->isRTLTextDir()) { // RTL
												if ($currentxpos < $textpos) {
													$x_diff = ($spacewidth * ($nsmax - $lnstring[3][$strcount]));
													$w_diff = ($spacewidth * $lnstring[2][$strcount]);
												} else {
													if ($strcount > 0) {
														$x_diff = ($spacewidth * ($nsmax - $lnstring[3][($strcount - 1)]));
														$w_diff = ($spacewidth * $lnstring[2][($strcount - 1)]);
													}
												}
											} else { // LTR
												if ($currentxpos > $textpos) {
													if ($strcount > 0) {
														$x_diff = ($spacewidth * $lnstring[3][($strcount - 1)]);
													}
													$w_diff = ($spacewidth * $lnstring[2][$strcount]);
												} else {
													if ($strcount > 1) {
														$x_diff = ($spacewidth * $lnstring[3][($strcount - 2)]);
													}
													if ($strcount > 0) {
														$w_diff = ($spacewidth * $lnstring[2][($strcount - 1)]);
													}
												}
											}
											if (preg_match('/('.$xmatches[1].')[\s]('.$xmatches[2].')[\s]('.$xmatches[3].')[\s]('.$strpiece[1][0].')[\s](re)([\s]*)/x', $pmid, $pmatch) == 1) {
												$newx = sprintf('%F',(floatval($pmatch[1]) + $x_diff));
												$neww = sprintf('%F',(floatval($pmatch[3]) + $w_diff));
												$newpmid = $newx.' '.$pmatch[2].' '.$neww.' '.$pmatch[4].' x*#!#*x'.$pmatch[5].$pmatch[6];
												$pmid = str_replace($pmatch[0], $newpmid, $pmid);
												unset($pmatch, $newpmid, $newx, $neww);
											}
											break;
										}
										case 'c': {
											// get current X position
											preg_match('/([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s](c)([\s]*)/x', $pmid, $xmatches);
											if (!isset($xmatches[1])) {
												break;
											}
											$currentxpos = $xmatches[1];
											// justify block
											if (preg_match('/('.$xmatches[1].')[\s]('.$xmatches[2].')[\s]('.$xmatches[3].')[\s]('.$xmatches[4].')[\s]('.$xmatches[5].')[\s]('.$strpiece[1][0].')[\s](c)([\s]*)/x', $pmid, $pmatch) == 1) {
												$newx1 = sprintf('%F',(floatval($pmatch[1]) + $spacew));
												$newx2 = sprintf('%F',(floatval($pmatch[3]) + $spacew));
												$newx3 = sprintf('%F',(floatval($pmatch[5]) + $spacew));
												$newpmid = $newx1.' '.$pmatch[2].' '.$newx2.' '.$pmatch[4].' '.$newx3.' '.$pmatch[6].' x*#!#*x'.$pmatch[7].$pmatch[8];
												$pmid = str_replace($pmatch[0], $newpmid, $pmid);
												unset($pmatch, $newpmid, $newx1, $newx2, $newx3);
											}
											break;
										}
									}
									// shift the annotations and links
									$cxpos = ($currentxpos / $this->k);
									$lmpos = ($this->lMargin + $this->cell_padding['L'] + $this->feps);
									if ($this->inxobj) {
										// we are inside an XObject template
										foreach ($this->xobjects[$this->xobjid]['annotations'] as $pak => $pac) {
											if (($pac['y'] >= $minstartliney) AND (($pac['x'] * $this->k) >= ($currentxpos - $this->feps)) AND (($pac['x'] * $this->k) <= ($currentxpos + $this->feps))) {
												if ($cxpos > $lmpos) {
													$this->xobjects[$this->xobjid]['annotations'][$pak]['x'] += ($spacew / $this->k);
													$this->xobjects[$this->xobjid]['annotations'][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
												} else {
													$this->xobjects[$this->xobjid]['annotations'][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
												}
												break;
											}
										}
									} elseif (isset($this->PageAnnots[$this->page])) {
										foreach ($this->PageAnnots[$this->page] as $pak => $pac) {
											if (($pac['y'] >= $minstartliney) AND (($pac['x'] * $this->k) >= ($currentxpos - $this->feps)) AND (($pac['x'] * $this->k) <= ($currentxpos + $this->feps))) {
												if ($cxpos > $lmpos) {
													$this->PageAnnots[$this->page][$pak]['x'] += ($spacew / $this->k);
													$this->PageAnnots[$this->page][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
												} else {
													$this->PageAnnots[$this->page][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
												}
												break;
											}
										}
									}
								} // end of while
								// remove markers
								$pmid = str_replace('x*#!#*x', '', $pmid);
								if ($this->isUnicodeFont()) {
									// multibyte characters
									$spacew = $spacewidthu;
									if ($this->font_stretching != 100) {
										// word spacing is affected by stretching
										$spacew /= ($this->font_stretching / 100);
									}
									// escape special characters
									$pos = 0;
									$pmid = preg_replace('/[\\\][\(]/x', '\\#!#OP#!#', $pmid);
									$pmid = preg_replace('/[\\\][\)]/x', '\\#!#CP#!#', $pmid);
									if (preg_match_all('/\[\(([^\)]*)\)\]/x', $pmid, $pamatch) > 0) {
										foreach($pamatch[0] as $pk => $pmatch) {
											$replace = $pamatch[1][$pk];
											$replace = str_replace('#!#OP#!#', '(', $replace);
											$replace = str_replace('#!#CP#!#', ')', $replace);
											$newpmid = '[('.str_replace(chr(0).chr(32), ') '.sprintf('%F', $spacew).' (', $replace).')]';
											$pos = strpos($pmid, $pmatch, $pos);
											if ($pos !== FALSE) {
												$pmid = substr_replace($pmid, $newpmid, $pos, strlen($pmatch));
											}
											++$pos;
										}
										unset($pamatch);
									}
									if ($this->inxobj) {
										// we are inside an XObject template
										$this->xobjects[$this->xobjid]['outdata'] = $pstart."\n".$pmid."\n".$pend;
									} else {
										$this->setPageBuffer($startlinepage, $pstart."\n".$pmid."\n".$pend);
									}
									$endlinepos = strlen($pstart."\n".$pmid."\n");
								} else {
									// non-unicode (single-byte characters)
									if ($this->font_stretching != 100) {
										// word spacing (Tw) is affected by stretching
										$spacewidth /= ($this->font_stretching / 100);
									}
									$rs = sprintf('%F Tw', $spacewidth);
									$pmid = preg_replace("/\[\(/x", $rs.' [(', $pmid);
									if ($this->inxobj) {
										// we are inside an XObject template
										$this->xobjects[$this->xobjid]['outdata'] = $pstart."\n".$pmid."\nBT 0 Tw ET\n".$pend;
									} else {
										$this->setPageBuffer($startlinepage, $pstart."\n".$pmid."\nBT 0 Tw ET\n".$pend);
									}
									$endlinepos = strlen($pstart."\n".$pmid."\nBT 0 Tw ET\n");
								}
							}
						} // end of J
					} // end if $startlinex
					if (($t_x != 0) OR ($yshift < 0)) {
						// shift the line
						$trx = sprintf('1 0 0 1 %F %F cm', ($t_x * $this->k), ($yshift * $this->k));
						$pstart .= "\nq\n".$trx."\n".$pmid."\nQ\n";
						$endlinepos = strlen($pstart);
						if ($this->inxobj) {
							// we are inside an XObject template
							$this->xobjects[$this->xobjid]['outdata'] = $pstart.$pend;
							foreach ($this->xobjects[$this->xobjid]['annotations'] as $pak => $pac) {
								if ($pak >= $pask) {
									$this->xobjects[$this->xobjid]['annotations'][$pak]['x'] += $t_x;
									$this->xobjects[$this->xobjid]['annotations'][$pak]['y'] -= $yshift;
								}
							}
						} else {
							$this->setPageBuffer($startlinepage, $pstart.$pend);
							// shift the annotations and links
							if (isset($this->PageAnnots[$this->page])) {
								foreach ($this->PageAnnots[$this->page] as $pak => $pac) {
									if ($pak >= $pask) {
										$this->PageAnnots[$this->page][$pak]['x'] += $t_x;
										$this->PageAnnots[$this->page][$pak]['y'] -= $yshift;
									}
								}
							}
						}
						$this->y -= $yshift;
					}
				}
				$pbrk = $this->checkPageBreak($this->lasth);
				$this->newline = false;
				$startlinex = $this->x;
				$startliney = $this->y;
				if ($dom[$dom[$key]['parent']]['value'] == 'sup') {
					$startliney -= ((0.3 * $this->FontSizePt) / $this->k);
				} elseif ($dom[$dom[$key]['parent']]['value'] == 'sub') {
					$startliney -= (($this->FontSizePt / 0.7) / $this->k);
				} else {
					$minstartliney = $startliney;
					$maxbottomliney = ($this->y + $this->getCellHeight($fontsize / $this->k));
				}
				$startlinepage = $this->page;
				if (isset($endlinepos) AND (!$pbrk)) {
					$startlinepos = $endlinepos;
				} else {
					if ($this->inxobj) {
						// we are inside an XObject template
						$startlinepos = strlen($this->xobjects[$this->xobjid]['outdata']);
					} elseif (!$this->InFooter) {
						if (isset($this->footerlen[$this->page])) {
							$this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
						} else {
							$this->footerpos[$this->page] = $this->pagelen[$this->page];
						}
						$startlinepos = $this->footerpos[$this->page];
					} else {
						$startlinepos = $this->pagelen[$this->page];
					}
				}
				unset($endlinepos);
				$plalign = $lalign;
				if (isset($this->PageAnnots[$this->page])) {
					$pask = count($this->PageAnnots[$this->page]);
				} else {
					$pask = 0;
				}
				if (!($dom[$key]['tag'] AND !$dom[$key]['opening'] AND ($dom[$key]['value'] == 'table')
					AND (isset($this->emptypagemrk[$this->page]))
					AND ($this->emptypagemrk[$this->page] == $this->pagelen[$this->page]))) {
					$this->setFont($fontname, $fontstyle, $fontsize);
					if ($wfill) {
						$this->setFillColorArray($this->bgcolor);
					}
				}
			} // end newline
			if (isset($opentagpos)) {
				unset($opentagpos);
			}
			if ($dom[$key]['tag']) {
				if ($dom[$key]['opening']) {
					// get text indentation (if any)
					if (isset($dom[$key]['text-indent']) AND $dom[$key]['block']) {
						$this->textindent = $dom[$key]['text-indent'];
						$this->newline = true;
					}
					// table
					if (($dom[$key]['value'] == 'table') AND isset($dom[$key]['cols']) AND ($dom[$key]['cols'] > 0)) {
						// available page width
						if ($this->rtl) {
							$wtmp = $this->x - $this->lMargin;
						} else {
							$wtmp = $this->w - $this->rMargin - $this->x;
						}
						// get cell spacing
						if (isset($dom[$key]['attribute']['cellspacing'])) {
							$clsp = $this->getHTMLUnitToUnits($dom[$key]['attribute']['cellspacing'], 1, 'px');
							$cellspacing = array('H' => $clsp, 'V' => $clsp);
						} elseif (isset($dom[$key]['border-spacing'])) {
							$cellspacing = $dom[$key]['border-spacing'];
						} else {
							$cellspacing = array('H' => 0, 'V' => 0);
						}
						// table width
						if (isset($dom[$key]['width'])) {
							$table_width = $this->getHTMLUnitToUnits($dom[$key]['width'], $wtmp, 'px');
						} else {
							$table_width = $wtmp;
						}
						$table_width -= (2 * $cellspacing['H']);
						if (!$this->inthead) {
							$this->y += $cellspacing['V'];
						}
						if ($this->rtl) {
							$cellspacingx = -$cellspacing['H'];
						} else {
							$cellspacingx = $cellspacing['H'];
						}
						// total table width without cellspaces
						$table_columns_width = ($table_width - ($cellspacing['H'] * ($dom[$key]['cols'] - 1)));
						// minimum column width
						$table_min_column_width = ($table_columns_width / $dom[$key]['cols']);
						// array of custom column widths
						$table_colwidths = array_fill(0, $dom[$key]['cols'], $table_min_column_width);
					}
					// table row
					if ($dom[$key]['value'] == 'tr') {
						// reset column counter
						$colid = 0;
					}
					// table cell
					if (($dom[$key]['value'] == 'td') OR ($dom[$key]['value'] == 'th')) {
						$trid = $dom[$key]['parent'];
						$table_el = $dom[$trid]['parent'];
						if (!isset($dom[$table_el]['cols'])) {
							$dom[$table_el]['cols'] = $dom[$trid]['cols'];
						}
						// store border info
						$tdborder = 0;
						if (isset($dom[$key]['border']) AND !empty($dom[$key]['border'])) {
							$tdborder = $dom[$key]['border'];
						}
						$colspan = intval($dom[$key]['attribute']['colspan']);
						if ($colspan <= 0) {
							$colspan = 1;
						}
						$old_cell_padding = $this->cell_padding;
						if (isset($dom[($dom[$trid]['parent'])]['attribute']['cellpadding'])) {
							$crclpd = $this->getHTMLUnitToUnits($dom[($dom[$trid]['parent'])]['attribute']['cellpadding'], 1, 'px');
							$current_cell_padding = array('L' => $crclpd, 'T' => $crclpd, 'R' => $crclpd, 'B' => $crclpd);
						} elseif (isset($dom[($dom[$trid]['parent'])]['padding'])) {
							$current_cell_padding = $dom[($dom[$trid]['parent'])]['padding'];
						} else {
							$current_cell_padding = array('L' => 0, 'T' => 0, 'R' => 0, 'B' => 0);
						}
						$this->cell_padding = $current_cell_padding;
						if (isset($dom[$key]['height'])) {
							// minimum cell height
							$cellh = $this->getHTMLUnitToUnits($dom[$key]['height'], 0, 'px');
						} else {
							$cellh = 0;
						}
						if (isset($dom[$key]['content'])) {
							$cell_content = $dom[$key]['content'];
						} else {
							$cell_content = '&nbsp;';
						}
						$tagtype = $dom[$key]['value'];
						$parentid = $key;
						while (($key < $maxel) AND (!(($dom[$key]['tag']) AND (!$dom[$key]['opening']) AND ($dom[$key]['value'] == $tagtype) AND ($dom[$key]['parent'] == $parentid)))) {
							// move $key index forward
							++$key;
						}
						if (!isset($dom[$trid]['startpage'])) {
							$dom[$trid]['startpage'] = $this->page;
						} else {
							$this->setPage($dom[$trid]['startpage']);
						}
						if (!isset($dom[$trid]['startcolumn'])) {
							$dom[$trid]['startcolumn'] = $this->current_column;
						} elseif ($this->current_column != $dom[$trid]['startcolumn']) {
							$tmpx = $this->x;
							$this->selectColumn($dom[$trid]['startcolumn']);
							$this->x = $tmpx;
						}
						if (!isset($dom[$trid]['starty'])) {
							$dom[$trid]['starty'] = $this->y;
						} else {
							$this->y = $dom[$trid]['starty'];
						}
						if (!isset($dom[$trid]['startx'])) {
							$dom[$trid]['startx'] = $this->x;
							$this->x += $cellspacingx;
						} else {
							$this->x += ($cellspacingx / 2);
						}
						if (isset($dom[$parentid]['attribute']['rowspan'])) {
							$rowspan = intval($dom[$parentid]['attribute']['rowspan']);
						} else {
							$rowspan = 1;
						}
						// skip row-spanned cells started on the previous rows
						if (isset($dom[$table_el]['rowspans'])) {
							$rsk = 0;
							$rskmax = count($dom[$table_el]['rowspans']);
							while ($rsk < $rskmax) {
								$trwsp = $dom[$table_el]['rowspans'][$rsk];
								$rsstartx = $trwsp['startx'];
								$rsendx = $trwsp['endx'];
								// account for margin changes
								if ($trwsp['startpage'] < $this->page) {
									if (($this->rtl) AND ($this->pagedim[$this->page]['orm'] != $this->pagedim[$trwsp['startpage']]['orm'])) {
										$dl = ($this->pagedim[$this->page]['orm'] - $this->pagedim[$trwsp['startpage']]['orm']);
										$rsstartx -= $dl;
										$rsendx -= $dl;
									} elseif ((!$this->rtl) AND ($this->pagedim[$this->page]['olm'] != $this->pagedim[$trwsp['startpage']]['olm'])) {
										$dl = ($this->pagedim[$this->page]['olm'] - $this->pagedim[$trwsp['startpage']]['olm']);
										$rsstartx += $dl;
										$rsendx += $dl;
									}
								}
								if (($trwsp['rowspan'] > 0)
									AND ($rsstartx > ($this->x - $cellspacing['H'] - $current_cell_padding['L'] - $this->feps))
									AND ($rsstartx < ($this->x + $cellspacing['H'] + $current_cell_padding['R'] + $this->feps))
									AND (($trwsp['starty'] < ($this->y - $this->feps)) OR ($trwsp['startpage'] < $this->page) OR ($trwsp['startcolumn'] < $this->current_column))) {
									// set the starting X position of the current cell
									$this->x = $rsendx + $cellspacingx;
									// increment column indicator
									$colid += $trwsp['colspan'];
									if (($trwsp['rowspan'] == 1)
										AND (isset($dom[$trid]['endy']))
										AND (isset($dom[$trid]['endpage']))
										AND (isset($dom[$trid]['endcolumn']))
										AND ($trwsp['endpage'] == $dom[$trid]['endpage'])
										AND ($trwsp['endcolumn'] == $dom[$trid]['endcolumn'])) {
										// set ending Y position for row
										$dom[$table_el]['rowspans'][$rsk]['endy'] = max($dom[$trid]['endy'], $trwsp['endy']);
										$dom[$trid]['endy'] = $dom[$table_el]['rowspans'][$rsk]['endy'];
									}
									$rsk = 0;
								} else {
									++$rsk;
								}
							}
						}
						if (isset($dom[$parentid]['width'])) {
							// user specified width
							$cellw = $this->getHTMLUnitToUnits($dom[$parentid]['width'], $table_columns_width, 'px');
							$tmpcw = ($cellw / $colspan);
							for ($i = 0; $i < $colspan; ++$i) {
								$table_colwidths[($colid + $i)] = $tmpcw;
							}
						} else {
							// inherit column width
							$cellw = 0;
							for ($i = 0; $i < $colspan; ++$i) {
								$cellw += (isset($table_colwidths[($colid + $i)]) ? $table_colwidths[($colid + $i)] : 0);
							}
						}
						$cellw += (($colspan - 1) * $cellspacing['H']);
						// increment column indicator
						$colid += $colspan;
						// add rowspan information to table element
						if ($rowspan > 1) {
							$trsid = array_push($dom[$table_el]['rowspans'], array('trid' => $trid, 'rowspan' => $rowspan, 'mrowspan' => $rowspan, 'colspan' => $colspan, 'startpage' => $this->page, 'startcolumn' => $this->current_column, 'startx' => $this->x, 'starty' => $this->y));
						}
						$cellid = array_push($dom[$trid]['cellpos'], array('startx' => $this->x));
						if ($rowspan > 1) {
							$dom[$trid]['cellpos'][($cellid - 1)]['rowspanid'] = ($trsid - 1);
						}
						// push background colors
						if (isset($dom[$parentid]['bgcolor']) AND ($dom[$parentid]['bgcolor'] !== false)) {
							$dom[$trid]['cellpos'][($cellid - 1)]['bgcolor'] = $dom[$parentid]['bgcolor'];
						}
						// store border info
						if (!empty($tdborder)) {
							$dom[$trid]['cellpos'][($cellid - 1)]['border'] = $tdborder;
						}
						$prevLastH = $this->lasth;
						// store some info for multicolumn mode
						if ($this->rtl) {
							$this->colxshift['x'] = $this->w - $this->x - $this->rMargin;
						} else {
							$this->colxshift['x'] = $this->x - $this->lMargin;
						}
						$this->colxshift['s'] = $cellspacing;
						$this->colxshift['p'] = $current_cell_padding;
						// ****** write the cell content ******
						$this->MultiCell($cellw, $cellh, $cell_content, false, $lalign, false, 2, '', '', true, 0, true, true, 0, 'T', false);
						// restore some values
						$this->colxshift = array('x' => 0, 's' => array('H' => 0, 'V' => 0), 'p' => array('L' => 0, 'T' => 0, 'R' => 0, 'B' => 0));
						$this->lasth = $prevLastH;
						$this->cell_padding = $old_cell_padding;
						$dom[$trid]['cellpos'][($cellid - 1)]['endx'] = $this->x;
						// update the end of row position
						if ($rowspan <= 1) {
							if (isset($dom[$trid]['endy'])) {
								if (($this->page == $dom[$trid]['endpage']) AND ($this->current_column == $dom[$trid]['endcolumn'])) {
									$dom[$trid]['endy'] = max($this->y, $dom[$trid]['endy']);
								} elseif (($this->page > $dom[$trid]['endpage']) OR ($this->current_column > $dom[$trid]['endcolumn'])) {
									$dom[$trid]['endy'] = $this->y;
								}
							} else {
								$dom[$trid]['endy'] = $this->y;
							}
							if (isset($dom[$trid]['endpage'])) {
								$dom[$trid]['endpage'] = max($this->page, $dom[$trid]['endpage']);
							} else {
								$dom[$trid]['endpage'] = $this->page;
							}
							if (isset($dom[$trid]['endcolumn'])) {
								$dom[$trid]['endcolumn'] = max($this->current_column, $dom[$trid]['endcolumn']);
							} else {
								$dom[$trid]['endcolumn'] = $this->current_column;
							}
						} else {
							// account for row-spanned cells
							$dom[$table_el]['rowspans'][($trsid - 1)]['endx'] = $this->x;
							$dom[$table_el]['rowspans'][($trsid - 1)]['endy'] = $this->y;
							$dom[$table_el]['rowspans'][($trsid - 1)]['endpage'] = $this->page;
							$dom[$table_el]['rowspans'][($trsid - 1)]['endcolumn'] = $this->current_column;
						}
						if (isset($dom[$table_el]['rowspans'])) {
							// update endy and endpage on rowspanned cells
							foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
								if ($trwsp['rowspan'] > 0) {
									if (isset($dom[$trid]['endpage'])) {
										if (($trwsp['endpage'] == $dom[$trid]['endpage']) AND ($trwsp['endcolumn'] == $dom[$trid]['endcolumn'])) {
											$dom[$table_el]['rowspans'][$k]['endy'] = max($dom[$trid]['endy'], $trwsp['endy']);
										} elseif (($trwsp['endpage'] < $dom[$trid]['endpage']) OR ($trwsp['endcolumn'] < $dom[$trid]['endcolumn'])) {
											$dom[$table_el]['rowspans'][$k]['endy'] = $dom[$trid]['endy'];
											$dom[$table_el]['rowspans'][$k]['endpage'] = $dom[$trid]['endpage'];
											$dom[$table_el]['rowspans'][$k]['endcolumn'] = $dom[$trid]['endcolumn'];
										} else {
											$dom[$trid]['endy'] = $this->pagedim[$dom[$trid]['endpage']]['hk'] - $this->pagedim[$dom[$trid]['endpage']]['bm'];
										}
									}
								}
							}
						}
						$this->x += ($cellspacingx / 2);
					} else {
						// opening tag (or self-closing tag)
						if (!isset($opentagpos)) {
							if ($this->inxobj) {
								// we are inside an XObject template
								$opentagpos = strlen($this->xobjects[$this->xobjid]['outdata']);
							} elseif (!$this->InFooter) {
								if (isset($this->footerlen[$this->page])) {
									$this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
								} else {
									$this->footerpos[$this->page] = $this->pagelen[$this->page];
								}
								$opentagpos = $this->footerpos[$this->page];
							}
						}
						$dom = $this->openHTMLTagHandler($dom, $key, $cell);
					}
				} else { // closing tag
					$prev_numpages = $this->numpages;
					$old_bordermrk = $this->bordermrk[$this->page];
					$dom = $this->closeHTMLTagHandler($dom, $key, $cell, $maxbottomliney);
					if ($this->bordermrk[$this->page] > $old_bordermrk) {
						$startlinepos += ($this->bordermrk[$this->page] - $old_bordermrk);
					}
					if ($prev_numpages > $this->numpages) {
						$startlinepage = $this->page;
					}
				}
			} elseif (strlen($dom[$key]['value']) > 0) {
				// print list-item
				if (!LIMEPDF_STATIC::empty_string($this->lispacer) AND ($this->lispacer != '^')) {
					$this->setFont($pfontname, $pfontstyle, $pfontsize);
					$this->resetLastH();
					$minstartliney = $this->y;
					$maxbottomliney = ($startliney + $this->getCellHeight($this->FontSize));
					if (is_numeric($pfontsize) AND ($pfontsize > 0)) {
						$this->putHtmlListBullet($this->listnum, $this->lispacer, $pfontsize);
					}
					$this->setFont($curfontname, $curfontstyle, $curfontsize);
					$this->resetLastH();
					if (is_numeric($pfontsize) AND ($pfontsize > 0) AND is_numeric($curfontsize) AND ($curfontsize > 0) AND ($pfontsize != $curfontsize)) {
						$pfontascent = $this->getFontAscent($pfontname, $pfontstyle, $pfontsize);
						$pfontdescent = $this->getFontDescent($pfontname, $pfontstyle, $pfontsize);
						$this->y += ($this->getCellHeight(($pfontsize - $curfontsize) / $this->k) + $pfontascent - $curfontascent - $pfontdescent + $curfontdescent) / 2;
						$minstartliney = min($this->y, $minstartliney);
						$maxbottomliney = max(($this->y + $this->getCellHeight($pfontsize / $this->k)), $maxbottomliney);
					}
				}
				// text
				$this->htmlvspace = 0;
				$isRTLString = preg_match(LIMEPDF_FONT_DATA::$uni_RE_PATTERN_RTL, $dom[$key]['value']) || preg_match(LIMEPDF_FONT_DATA::$uni_RE_PATTERN_ARABIC, $dom[$key]['value']);
				if ((!$this->premode) AND $this->isRTLTextDir() AND !$isRTLString) {
					// reverse spaces order
					$lsp = ''; // left spaces
					$rsp = ''; // right spaces
					if (preg_match('/^('.$this->re_space['p'].'+)/'.$this->re_space['m'], $dom[$key]['value'], $matches)) {
						$lsp = $matches[1];
					}
					if (preg_match('/('.$this->re_space['p'].'+)$/'.$this->re_space['m'], $dom[$key]['value'], $matches)) {
						$rsp = $matches[1];
					}
					$dom[$key]['value'] = $rsp.$this->stringTrim($dom[$key]['value']).$lsp;
				}
				if ($newline) {
					if (!$this->premode) {
						$prelen = strlen($dom[$key]['value']);
						if ($this->isRTLTextDir() AND !$isRTLString) {
							// right trim except non-breaking space
							$dom[$key]['value'] = $this->stringRightTrim($dom[$key]['value']);
						} else {
							// left trim except non-breaking space
							$dom[$key]['value'] = $this->stringLeftTrim($dom[$key]['value']);
						}
						$postlen = strlen($dom[$key]['value']);
						if (($postlen == 0) AND ($prelen > 0)) {
							$dom[$key]['trimmed_space'] = true;
						}
					}
					$newline = false;
					$firstblock = true;
				} else {
					$firstblock = false;
					// replace empty multiple spaces string with a single space
					$dom[$key]['value'] = preg_replace('/^'.$this->re_space['p'].'+$/'.$this->re_space['m'], chr(32), $dom[$key]['value']);
				}
				$strrest = '';
				if ($this->rtl) {
					$this->x -= $this->textindent;
				} else {
					$this->x += $this->textindent;
				}
				if (!isset($dom[$key]['trimmed_space']) OR !$dom[$key]['trimmed_space']) {
					$strlinelen = $this->GetStringWidth($dom[$key]['value']);
					if (!empty($this->HREF) AND (isset($this->HREF['url']))) {
						// HTML <a> Link
						$hrefcolor = '';
						if (isset($dom[($dom[$key]['parent'])]['fgcolor']) AND ($dom[($dom[$key]['parent'])]['fgcolor'] !== false)) {
							$hrefcolor = $dom[($dom[$key]['parent'])]['fgcolor'];
						}
						$hrefstyle = -1;
						if (isset($dom[($dom[$key]['parent'])]['fontstyle']) AND ($dom[($dom[$key]['parent'])]['fontstyle'] !== false)) {
							$hrefstyle = $dom[($dom[$key]['parent'])]['fontstyle'];
						}
						$strrest = $this->addHtmlLink($this->HREF['url'], $dom[$key]['value'], $wfill, true, $hrefcolor, $hrefstyle, true);
					} else {
						$wadj = 0; // space to leave for block continuity
						if ($this->rtl) {
							$cwa = ($this->x - $this->lMargin);
						} else {
							$cwa = ($this->w - $this->rMargin - $this->x);
						}
						if (($strlinelen < $cwa) AND (isset($dom[($key + 1)])) AND ($dom[($key + 1)]['tag']) AND (!$dom[($key + 1)]['block'])) {
							// check the next text blocks for continuity
							$nkey = ($key + 1);
							$write_block = true;
							$same_textdir = true;
							$tmp_fontname = $this->FontFamily;
							$tmp_fontstyle = $this->FontStyle;
							$tmp_fontsize = $this->FontSizePt;
							while ($write_block AND isset($dom[$nkey])) {
								if ($dom[$nkey]['tag']) {
									if ($dom[$nkey]['block']) {
										// end of block
										$write_block = false;
									}
									$tmp_fontname = isset($dom[$nkey]['fontname']) ? $dom[$nkey]['fontname'] : $this->FontFamily;
									$tmp_fontstyle = isset($dom[$nkey]['fontstyle']) ? $dom[$nkey]['fontstyle'] : $this->FontStyle;
									$tmp_fontsize = isset($dom[$nkey]['fontsize']) ? $dom[$nkey]['fontsize'] : $this->FontSizePt;
									$same_textdir = ($dom[$nkey]['dir'] == $dom[$key]['dir']);
								} else {
									$nextstr = LIMEPDF_STATIC::pregSplit('/'.$this->re_space['p'].'+/', $this->re_space['m'], $dom[$nkey]['value']);
									if (isset($nextstr[0]) AND $same_textdir) {
										$wadj += $this->GetStringWidth($nextstr[0], $tmp_fontname, $tmp_fontstyle, $tmp_fontsize);
										if (isset($nextstr[1])) {
											$write_block = false;
										}
									}
								}
								++$nkey;
							}
						}
						if (($wadj > 0) AND (($strlinelen + $wadj) >= $cwa)) {
							$wadj = 0;
							$nextstr = LIMEPDF_STATIC::pregSplit('/'.$this->re_space['p'].'/', $this->re_space['m'], $dom[$key]['value']);
							$numblks = count($nextstr);
							if ($numblks > 1) {
								// try to split on blank spaces
								$wadj = ($cwa - $strlinelen + $this->GetStringWidth($nextstr[($numblks - 1)]));
							} else {
								// set the entire block on new line
								$wadj = $this->GetStringWidth($nextstr[0]);
							}
						}
						// check for reversed text direction
						if (($wadj > 0) AND (($this->rtl AND ($this->tmprtl === 'L')) OR (!$this->rtl AND ($this->tmprtl === 'R')))) {
							// LTR text on RTL direction or RTL text on LTR direction
							$reverse_dir = true;
							$this->rtl = !$this->rtl;
							$revshift = ($strlinelen + $wadj + 0.000001); // add little quantity for rounding problems
							if ($this->rtl) {
								$this->x += $revshift;
							} else {
								$this->x -= $revshift;
							}
							$xws = $this->x;
						}
						// ****** write only until the end of the line and get the rest ******
						$strrest = $this->Write($this->lasth, $dom[$key]['value'], '', $wfill, '', false, 0, true, $firstblock, 0, $wadj);
						// restore default direction
						if ($reverse_dir AND ($wadj == 0)) {
							$this->x = $xws; // @phpstan-ignore-line
							$this->rtl = !$this->rtl;
							$reverse_dir = false;
						}
					}
				}
				$this->textindent = 0;
				if (strlen($strrest) > 0) {
					// store the remaining string on the previous $key position
					$this->newline = true;
					if ($strrest == $dom[$key]['value']) {
						// used to avoid infinite loop
						++$loop;
					} else {
						$loop = 0;
					}
					$dom[$key]['value'] = $strrest;
					if ($cell) {
						if ($this->rtl) {
							$this->x -= $this->cell_padding['R'];
						} else {
							$this->x += $this->cell_padding['L'];
						}
					}
					if ($loop < 3) {
						--$key;
					}
				} else {
					$loop = 0;
					// add the positive font spacing of the last character (if any)
					 if ($this->font_spacing > 0) {
					 	if ($this->rtl) {
							$this->x -= $this->font_spacing;
						} else {
							$this->x += $this->font_spacing;
						}
					}
				}
			}
			++$key;
			if (isset($dom[$key]['tag']) AND $dom[$key]['tag'] AND (!isset($dom[$key]['opening']) OR !$dom[$key]['opening']) AND isset($dom[($dom[$key]['parent'])]['attribute']['nobr']) AND ($dom[($dom[$key]['parent'])]['attribute']['nobr'] == 'true')) {
				// check if we are on a new page or on a new column
				if ((!$undo) AND (($this->y < $this->start_transaction_y) OR (($dom[$key]['value'] == 'tr') AND ($dom[($dom[$key]['parent'])]['endy'] < $this->start_transaction_y)))) {
					// we are on a new page or on a new column and the total object height is less than the available vertical space.
					// restore previous object
					$this->rollbackTransaction(true);
					// restore previous values
					foreach ($this_method_vars as $vkey => $vval) {
						$$vkey = $vval;
					}
					if (!empty($dom[$key]['thead'])) {
						$this->inthead = true;
					}
					// add a page (or trig AcceptPageBreak() for multicolumn mode)
					$pre_y = $this->y;
					if ((!$this->checkPageBreak($this->PageBreakTrigger + 1)) AND ($this->y < $pre_y)) {
						$startliney = $this->y;
					}
					$undo = true; // avoid infinite loop
				} else {
					$undo = false;
				}
			}
		} // end for each $key
		// align the last line
		if (isset($startlinex)) {
			$yshift = ($minstartliney - $startliney);
			if (($yshift > 0) OR ($this->page > $startlinepage)) {
				$yshift = 0;
			}
			$t_x = 0;
			// the last line must be shifted to be aligned as requested
			$linew = abs($this->endlinex - $startlinex);
			if ($this->inxobj) {
				// we are inside an XObject template
				$pstart = substr($this->xobjects[$this->xobjid]['outdata'], 0, $startlinepos);
				if (isset($opentagpos)) {
					$midpos = $opentagpos;
				} else {
					$midpos = 0;
				}
				if ($midpos > 0) {
					$pmid = substr($this->xobjects[$this->xobjid]['outdata'], $startlinepos, ($midpos - $startlinepos));
					$pend = substr($this->xobjects[$this->xobjid]['outdata'], $midpos);
				} else {
					$pmid = substr($this->xobjects[$this->xobjid]['outdata'], $startlinepos);
					$pend = '';
				}
			} else {
				$pstart = substr($this->getPageBuffer($startlinepage), 0, $startlinepos);
				if (isset($opentagpos) AND isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
					$this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
					$midpos = min($opentagpos, $this->footerpos[$startlinepage]);
				} elseif (isset($opentagpos)) {
					$midpos = $opentagpos;
				} elseif (isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
					$this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
					$midpos = $this->footerpos[$startlinepage];
				} else {
					$midpos = 0;
				}
				if ($midpos > 0) {
					$pmid = substr($this->getPageBuffer($startlinepage), $startlinepos, ($midpos - $startlinepos));
					$pend = substr($this->getPageBuffer($startlinepage), $midpos);
				} else {
					$pmid = substr($this->getPageBuffer($startlinepage), $startlinepos);
					$pend = '';
				}
			}
			if ((((($plalign == 'C') OR (($plalign == 'R') AND (!$this->rtl)) OR (($plalign == 'L') AND ($this->rtl)))))) {
				// calculate shifting amount
				$tw = $w;
				if ($this->lMargin != $prevlMargin) {
					$tw += ($prevlMargin - $this->lMargin);
				}
				if ($this->rMargin != $prevrMargin) {
					$tw += ($prevrMargin - $this->rMargin);
				}
				$one_space_width = $this->GetStringWidth(chr(32));
				$no = 0; // number of spaces on a line contained on a single block
				if ($this->isRTLTextDir()) { // RTL
					// remove left space if exist
					$pos1 = LIMEPDF_STATIC::revstrpos($pmid, '[(');
					if ($pos1 > 0) {
						$pos1 = intval($pos1);
						if ($this->isUnicodeFont()) {
							$pos2 = intval(LIMEPDF_STATIC::revstrpos($pmid, '[('.chr(0).chr(32)));
							$spacelen = 2;
						} else {
							$pos2 = intval(LIMEPDF_STATIC::revstrpos($pmid, '[('.chr(32)));
							$spacelen = 1;
						}
						if ($pos1 == $pos2) {
							$pmid = substr($pmid, 0, ($pos1 + 2)).substr($pmid, ($pos1 + 2 + $spacelen));
							if (substr($pmid, $pos1, 4) == '[()]') {
								$linew -= $one_space_width;
							} elseif ($pos1 == strpos($pmid, '[(')) {
								$no = 1;
							}
						}
					}
				} else { // LTR
					// remove right space if exist
					$pos1 = LIMEPDF_STATIC::revstrpos($pmid, ')]');
					if ($pos1 > 0) {
						$pos1 = intval($pos1);
						if ($this->isUnicodeFont()) {
							$pos2 = intval(LIMEPDF_STATIC::revstrpos($pmid, chr(0).chr(32).')]')) + 2;
							$spacelen = 2;
						} else {
							$pos2 = intval(LIMEPDF_STATIC::revstrpos($pmid, chr(32).')]')) + 1;
							$spacelen = 1;
						}
						if ($pos1 == $pos2) {
							$pmid = substr($pmid, 0, ($pos1 - $spacelen)).substr($pmid, $pos1);
							$linew -= $one_space_width;
						}
					}
				}
				$mdiff = ($tw - $linew);
				if ($plalign == 'C') {
					if ($this->rtl) {
						$t_x = -($mdiff / 2);
					} else {
						$t_x = ($mdiff / 2);
					}
				} elseif ($plalign == 'R') {
					// right alignment on LTR document
					$t_x = $mdiff;
				} elseif ($plalign == 'L') {
					// left alignment on RTL document
					$t_x = -$mdiff;
				}
			} // end if startlinex
			if (($t_x != 0) OR ($yshift < 0)) {
				// shift the line
				$trx = sprintf('1 0 0 1 %F %F cm', ($t_x * $this->k), ($yshift * $this->k));
				$pstart .= "\nq\n".$trx."\n".$pmid."\nQ\n";
				$endlinepos = strlen($pstart);
				if ($this->inxobj) {
					// we are inside an XObject template
					$this->xobjects[$this->xobjid]['outdata'] = $pstart.$pend;
					foreach ($this->xobjects[$this->xobjid]['annotations'] as $pak => $pac) {
						if ($pak >= $pask) {
							$this->xobjects[$this->xobjid]['annotations'][$pak]['x'] += $t_x;
							$this->xobjects[$this->xobjid]['annotations'][$pak]['y'] -= $yshift;
						}
					}
				} else {
					$this->setPageBuffer($startlinepage, $pstart.$pend);
					// shift the annotations and links
					if (isset($this->PageAnnots[$this->page])) {
						foreach ($this->PageAnnots[$this->page] as $pak => $pac) {
							if ($pak >= $pask) {
								$this->PageAnnots[$this->page][$pak]['x'] += $t_x;
								$this->PageAnnots[$this->page][$pak]['y'] -= $yshift;
							}
						}
					}
				}
				$this->y -= $yshift;
				$yshift = 0;
			}
		}
		// restore previous values
		$this->setGraphicVars($gvars);
		if ($this->num_columns > 1) {
			$this->selectColumn();
		} elseif ($this->page > $prevPage) {
			$this->lMargin = $this->pagedim[$this->page]['olm'];
			$this->rMargin = $this->pagedim[$this->page]['orm'];
		}
		// restore previous list state
		$this->cell_height_ratio = $prev_cell_height_ratio;
		$this->listnum = $prev_listnum;
		$this->listordered = $prev_listordered;
		$this->listcount = $prev_listcount;
		$this->lispacer = $prev_lispacer;
		if ($ln AND (!($cell AND ($dom[$key-1]['value'] == 'table')))) {
			$this->Ln($this->lasth);
			if (($this->y < $maxbottomliney) AND ($startlinepage == $this->page)) {
				$this->y = $maxbottomliney;
			}
		}
		unset($dom);
	}

	/**
	 * Check if the path is relative.
	 * @param string $path path to check
	 * @return boolean true if the path is relative
	 * @protected
	 * @since 6.9.1
	 */
	protected function isRelativePath($path) {
		return (strpos(str_ireplace('%2E', '.', $this->unhtmlentities($path)), '..') !== false);
	}

	/**
	 * Check if it contains a non-allowed external protocol.
	 * @param string $path path to check
	 * @return boolean true if the protocol is not allowed.
	 * @protected
	 * @since 6.9.3
	 */
	protected function hasExtForbiddenProtocol($path) {
		return ((strpos($path, '://') !== false)
			&& (preg_match('|^https?://|', $path) !== 1));
	}

	/**
	 * Process opening tags.
	 * @param array $dom html dom array
	 * @param int $key current element id
	 * @param boolean $cell if true add the default left (or right if RTL) padding to each new line (default false).
	 * @return array $dom
	 * @protected
	 */
	protected function openHTMLTagHandler($dom, $key, $cell) {
		$tag = $dom[$key];
		$parent = $dom[($dom[$key]['parent'])];
		$firsttag = ($key == 1);
		// check for text direction attribute
		if (isset($tag['dir'])) {
			$this->setTempRTL($tag['dir']);
		} else {
			$this->tmprtl = false;
		}
		if ($tag['block']) {
			$hbz = 0; // distance from y to line bottom
			$hb = 0; // vertical space between block tags
			// calculate vertical space for block tags
			if (isset($this->tagvspaces[$tag['value']][0]['h']) && !empty($this->tagvspaces[$tag['value']][0]['h']) && ($this->tagvspaces[$tag['value']][0]['h'] >= 0)) {
				$cur_h = $this->tagvspaces[$tag['value']][0]['h'];
			} elseif (isset($tag['fontsize'])) {
				$cur_h = $this->getCellHeight($tag['fontsize'] / $this->k);
			} else {
				$cur_h = $this->getCellHeight($this->FontSize);
			}
			if (isset($this->tagvspaces[$tag['value']][0]['n'])) {
				$on = $this->tagvspaces[$tag['value']][0]['n'];
			} elseif (preg_match('/[h][0-9]/', $tag['value']) > 0) {
				$on = 0.6;
			} else {
				$on = 1;
			}
			if ((!isset($this->tagvspaces[$tag['value']])) AND (in_array($tag['value'], array('div', 'dt', 'dd', 'li', 'br', 'hr')))) {
				$hb = 0;
			} else {
				$hb = ($on * $cur_h);
			}
			if (($this->htmlvspace <= 0) AND ($on > 0)) {
				if (isset($parent['fontsize'])) {
					$hbz = (($parent['fontsize'] / $this->k) * $this->cell_height_ratio);
				} else {
					$hbz = $this->getCellHeight($this->FontSize);
				}
			}
			if (isset($dom[($key - 1)]) AND ($dom[($key - 1)]['value'] == 'table')) {
				// fix vertical space after table
				$hbz = 0;
			}
			// closing vertical space
			$hbc = 0;
			if (isset($this->tagvspaces[$tag['value']][1]['h']) && !empty($this->tagvspaces[$tag['value']][1]['h']) && ($this->tagvspaces[$tag['value']][1]['h'] >= 0)) {
				$pre_h = $this->tagvspaces[$tag['value']][1]['h'];
			} elseif (isset($parent['fontsize'])) {
				$pre_h = $this->getCellHeight($parent['fontsize'] / $this->k);
			} else {
				$pre_h = $this->getCellHeight($this->FontSize);
			}
			if (isset($this->tagvspaces[$tag['value']][1]['n'])) {
				$cn = $this->tagvspaces[$tag['value']][1]['n'];
			} elseif (preg_match('/[h][0-9]/', $tag['value']) > 0) {
				$cn = 0.6;
			} else {
				$cn = 1;
			}
			if (isset($this->tagvspaces[$tag['value']][1])) {
				$hbc = ($cn * $pre_h);
			}
		}
		// Opening tag
		switch($tag['value']) {
			case 'table': {
				$cp = 0;
				$cs = 0;
				$dom[$key]['rowspans'] = array();
				if (!isset($dom[$key]['attribute']['nested']) OR ($dom[$key]['attribute']['nested'] != 'true')) {
					$this->htmlvspace = 0;
					// set table header
					if (!LIMEPDF_STATIC::empty_string($dom[$key]['thead'])) {
						// set table header
						$this->thead = $dom[$key]['thead'];
						if (!isset($this->theadMargins) OR (empty($this->theadMargins))) {
							$this->theadMargins = array();
							$this->theadMargins['cell_padding'] = $this->cell_padding;
							$this->theadMargins['lmargin'] = $this->lMargin;
							$this->theadMargins['rmargin'] = $this->rMargin;
							$this->theadMargins['page'] = $this->page;
							$this->theadMargins['cell'] = $cell;
							$this->theadMargins['gvars'] = $this->getGraphicVars();
						}
					}
				}
				// store current margins and page
				$dom[$key]['old_cell_padding'] = $this->cell_padding;
				if (isset($tag['attribute']['cellpadding'])) {
					$pad = $this->getHTMLUnitToUnits($tag['attribute']['cellpadding'], 1, 'px');
					$this->setCellPadding($pad);
				} elseif (isset($tag['padding'])) {
					$this->cell_padding = $tag['padding'];
				}
				if (isset($tag['attribute']['cellspacing'])) {
					$cs = $this->getHTMLUnitToUnits($tag['attribute']['cellspacing'], 1, 'px');
				} elseif (isset($tag['border-spacing'])) {
					$cs = $tag['border-spacing']['V'];
				}
				$prev_y = $this->y;
				if ($this->checkPageBreak(((2 * $cp) + (2 * $cs) + $this->lasth), '', false) OR ($this->y < $prev_y)) {
					$this->inthead = true;
					// add a page (or trig AcceptPageBreak() for multicolumn mode)
					$this->checkPageBreak($this->PageBreakTrigger + 1);
				}
				break;
			}
			case 'tr': {
				// array of columns positions
				$dom[$key]['cellpos'] = array();
				break;
			}
			case 'hr': {
				if ((isset($tag['height'])) AND ($tag['height'] != '')) {
					$hrHeight = $this->getHTMLUnitToUnits($tag['height'], 1, 'px');
				} else {
					$hrHeight = $this->GetLineWidth();
				}
				$this->addHTMLVertSpace($hbz, max($hb, ($hrHeight / 2)), $cell, $firsttag);
				$x = $this->GetX();
				$y = $this->GetY();
				$wtmp = $this->w - $this->lMargin - $this->rMargin;
				if ($cell) {
					$wtmp -= ($this->cell_padding['L'] + $this->cell_padding['R']);
				}
				if ((isset($tag['width'])) AND ($tag['width'] != '')) {
					$hrWidth = $this->getHTMLUnitToUnits($tag['width'], $wtmp, 'px');
				} else {
					$hrWidth = $wtmp;
				}
				$prevlinewidth = $this->GetLineWidth();
				$this->setLineWidth($hrHeight);

				$lineStyle = array();
				if (isset($tag['fgcolor'])) {
					$lineStyle['color'] = $tag['fgcolor'];
				}

				if (isset($tag['fgcolor'])) {
					$lineStyle['color'] = $tag['fgcolor'];
				}

				if (isset($tag['style']['cap'])) {
					$lineStyle['cap'] = $tag['style']['cap'];
				}

				if (isset($tag['style']['join'])) {
					$lineStyle['join'] = $tag['style']['join'];
				}

				if (isset($tag['style']['dash'])) {
					$lineStyle['dash'] = $tag['style']['dash'];
				}

				if (isset($tag['style']['phase'])) {
					$lineStyle['phase'] = $tag['style']['phase'];
				}

				$lineStyle = array_filter($lineStyle);

				$this->Line($x, $y, $x + $hrWidth, $y, $lineStyle);
				$this->setLineWidth($prevlinewidth);
				$this->addHTMLVertSpace(max($hbc, ($hrHeight / 2)), 0, $cell, !isset($dom[($key + 1)]));
				break;
			}
			case 'a': {
				if (array_key_exists('href', $tag['attribute'])) {
					$this->HREF['url'] = $tag['attribute']['href'];
				}
				break;
			}
			case 'img': {
				if (empty($tag['attribute']['src'])) {
					break;
				}
				$imgsrc = $tag['attribute']['src'];
				if ($imgsrc[0] === '@') {
					// data stream
					$imgsrc = '@'.base64_decode(substr($imgsrc, 1));
					$type = preg_match('/<svg\s+[^>]*[^>]*>.*<\/svg>/is', $imgsrc) ? 'svg' : '';
				} else if (preg_match('@^data:image/([^;]*);base64,(.*)@', $imgsrc, $reg)) {
					$imgsrc = '@'.base64_decode($reg[2]);
					$type = $reg[1];
				} elseif ($this->isRelativePath($imgsrc)) {
					// accessing parent folders is not allowed
					break;
				} elseif ( $this->allowLocalFiles && substr($imgsrc, 0, 7) === 'file://') {
					// get image type from a local file path
					$imgsrc = substr($imgsrc, 7);
					$type = LIMEPDF_IMAGES::getImageFileType($imgsrc);
				} elseif ($this->hasExtForbiddenProtocol($imgsrc)) {
					break;
				} else {
					if (($imgsrc[0] === '/') AND !empty($_SERVER['DOCUMENT_ROOT']) AND ($_SERVER['DOCUMENT_ROOT'] != '/')) {
						// fix image path
						$findroot = strpos($imgsrc, $_SERVER['DOCUMENT_ROOT']);
						if (($findroot === false) OR ($findroot > 1)) {
							if (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') {
								$imgsrc = substr($_SERVER['DOCUMENT_ROOT'], 0, -1).$imgsrc;
							} else {
								$imgsrc = $_SERVER['DOCUMENT_ROOT'].$imgsrc;
							}
						}
						$imgsrc = urldecode($imgsrc);
						$testscrtype = @parse_url($imgsrc);
						if (empty($testscrtype['query'])) {
							// convert URL to server path
							$imgsrc = str_replace(K_PATH_URL, K_PATH_MAIN, $imgsrc);
						} elseif (preg_match('|^https?://|', $imgsrc) !== 1) {
							// convert URL to server path
							$imgsrc = str_replace(K_PATH_MAIN, K_PATH_URL, $imgsrc);
						}
					}
					// get image type
					$type = LIMEPDF_IMAGES::getImageFileType($imgsrc);
				}
				if (!isset($tag['width'])) {
					$tag['width'] = 0;
				}
				if (!isset($tag['height'])) {
					$tag['height'] = 0;
				}
				//if (!isset($tag['attribute']['align'])) {
					// the only alignment supported is "bottom"
					// further development is required for other modes.
					$tag['attribute']['align'] = 'bottom';
				//}
				switch($tag['attribute']['align']) {
					case 'top': {
						$align = 'T';
						break;
					}
					case 'middle': {
						$align = 'M';
						break;
					}
					case 'bottom': {
						$align = 'B';
						break;
					}
					default: {
						$align = 'B';
						break;
					}
				}
				$prevy = $this->y;
				$xpos = $this->x;
				$imglink = '';
				if (isset($this->HREF['url']) AND !LIMEPDF_STATIC::empty_string($this->HREF['url'])) {
					$imglink = $this->HREF['url'];
					if ($imglink[0] == '#' AND isset($imglink[1]) AND is_numeric($imglink[1])) {
						// convert url to internal link
						$lnkdata = explode(',', $imglink);
						if (isset($lnkdata[0])) {
							$page = intval(substr($lnkdata[0], 1));
							if (empty($page) OR ($page <= 0)) {
								$page = $this->page;
							}
							if (isset($lnkdata[1]) AND (strlen($lnkdata[1]) > 0)) {
								$lnky = floatval($lnkdata[1]);
							} else {
								$lnky = 0;
							}
							$imglink = $this->AddLink();
							$this->setLink($imglink, $lnky, $page);
						}
					}
				}
				$border = 0;
				if (isset($tag['border']) AND !empty($tag['border'])) {
					// currently only support 1 (frame) or a combination of 'LTRB'
					$border = $tag['border'];
				}
				$iw = '';
				if (isset($tag['width'])) {
					$iw = $this->getHTMLUnitToUnits($tag['width'], ($tag['fontsize'] / $this->k), 'px', false);
				}
				$ih = '';
				if (isset($tag['height'])) {
					$ih = $this->getHTMLUnitToUnits($tag['height'], ($tag['fontsize'] / $this->k), 'px', false);
				}
				if (($type == 'eps') OR ($type == 'ai')) {
					$this->ImageEps($imgsrc, $xpos, $this->y, $iw, $ih, $imglink, true, $align, '', $border, true);
				} elseif ($type == 'svg') {
					$this->ImageSVG($imgsrc, $xpos, $this->y, $iw, $ih, $imglink, $align, '', $border, true);
				} else {
					$this->Image($imgsrc, $xpos, $this->y, $iw, $ih, '', $imglink, $align, false, 300, '', false, false, $border, false, false, true);
				}
				switch($align) {
					case 'T': {
						$this->y = $prevy;
						break;
					}
					case 'M': {
						$this->y = (($this->img_rb_y + $prevy - ($this->getCellHeight($tag['fontsize'] / $this->k))) / 2);
						break;
					}
					case 'B': {
						$this->y = $this->img_rb_y - ($this->getCellHeight($tag['fontsize'] / $this->k) - ($this->getFontDescent($tag['fontname'], $tag['fontstyle'], $tag['fontsize']) * $this->cell_height_ratio));
						break;
					}
				}
				break;
			}
			case 'dl': {
				++$this->listnum;
				if ($this->listnum == 1) {
					$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				} else {
					$this->addHTMLVertSpace(0, 0, $cell, $firsttag);
				}
				break;
			}
			case 'dt': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			case 'dd': {
				if ($this->rtl) {
					$this->rMargin += $this->listindent;
				} else {
					$this->lMargin += $this->listindent;
				}
				++$this->listindentlevel;
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			case 'ul':
			case 'ol': {
				++$this->listnum;
				if ($tag['value'] == 'ol') {
					$this->listordered[$this->listnum] = true;
				} else {
					$this->listordered[$this->listnum] = false;
				}
				if (isset($tag['attribute']['start'])) {
					$this->listcount[$this->listnum] = intval($tag['attribute']['start']) - 1;
				} else {
					$this->listcount[$this->listnum] = 0;
				}
				if ($this->rtl) {
					$this->rMargin += $this->listindent;
					$this->x -= $this->listindent;
				} else {
					$this->lMargin += $this->listindent;
					$this->x += $this->listindent;
				}
				++$this->listindentlevel;
				if ($this->listnum == 1) {
					if ($key > 1) {
						$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
					}
				} else {
					$this->addHTMLVertSpace(0, 0, $cell, $firsttag);
				}
				break;
			}
			case 'li': {
				if ($key > 2) {
					$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				}
				if ($this->listordered[$this->listnum]) {
					// ordered item
					if (isset($parent['attribute']['type']) AND !LIMEPDF_STATIC::empty_string($parent['attribute']['type'])) {
						$this->lispacer = $parent['attribute']['type'];
					} elseif (isset($parent['listtype']) AND !LIMEPDF_STATIC::empty_string($parent['listtype'])) {
						$this->lispacer = $parent['listtype'];
					} elseif (isset($this->lisymbol) AND !LIMEPDF_STATIC::empty_string($this->lisymbol)) {
						$this->lispacer = $this->lisymbol;
					} else {
						$this->lispacer = '#';
					}
					++$this->listcount[$this->listnum];
					if (isset($tag['attribute']['value'])) {
						$this->listcount[$this->listnum] = intval($tag['attribute']['value']);
					}
				} else {
					// unordered item
					if (isset($parent['attribute']['type']) AND !LIMEPDF_STATIC::empty_string($parent['attribute']['type'])) {
						$this->lispacer = $parent['attribute']['type'];
					} elseif (isset($parent['listtype']) AND !LIMEPDF_STATIC::empty_string($parent['listtype'])) {
						$this->lispacer = $parent['listtype'];
					} elseif (isset($this->lisymbol) AND !LIMEPDF_STATIC::empty_string($this->lisymbol)) {
						$this->lispacer = $this->lisymbol;
					} else {
						$this->lispacer = '!';
					}
				}
				break;
			}
			case 'blockquote': {
				if ($this->rtl) {
					$this->rMargin += $this->listindent;
				} else {
					$this->lMargin += $this->listindent;
				}
				++$this->listindentlevel;
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			case 'br': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			case 'div': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			case 'p': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			case 'pre': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				$this->premode = true;
				break;
			}
			case 'sup': {
				$this->setXY($this->GetX(), $this->GetY() - ((0.7 * $this->FontSizePt) / $this->k));
				break;
			}
			case 'sub': {
				$this->setXY($this->GetX(), $this->GetY() + ((0.3 * $this->FontSizePt) / $this->k));
				break;
			}
			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, $firsttag);
				break;
			}
			// Form fields (since 4.8.000 - 2009-09-07)
			case 'form': {
				if (isset($tag['attribute']['action'])) {
					$this->form_action = $tag['attribute']['action'];
				} else {
					$this->Error('Please explicitly set action attribute path!');
				}
				if (isset($tag['attribute']['enctype'])) {
					$this->form_enctype = $tag['attribute']['enctype'];
				} else {
					$this->form_enctype = 'application/x-www-form-urlencoded';
				}
				if (isset($tag['attribute']['method'])) {
					$this->form_mode = $tag['attribute']['method'];
				} else {
					$this->form_mode = 'post';
				}
				break;
			}
			case 'input': {
				if (isset($tag['attribute']['name']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['name'])) {
					$name = $tag['attribute']['name'];
				} else {
					break;
				}
				$prop = array();
				$opt = array();
				if (isset($tag['attribute']['readonly']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['readonly'])) {
					$prop['readonly'] = true;
				}
				if (isset($tag['attribute']['value']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['value'])) {
					$value = $tag['attribute']['value'];
				}
				if (isset($tag['attribute']['maxlength']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['maxlength'])) {
					$opt['maxlen'] = intval($tag['attribute']['maxlength']);
				}
				$h = $this->getCellHeight($this->FontSize);
				if (isset($tag['attribute']['size']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['size'])) {
					$w = intval($tag['attribute']['size']) * $this->GetStringWidth(chr(32)) * 2;
				} else {
					$w = $h;
				}
				if (isset($tag['attribute']['checked']) AND (($tag['attribute']['checked'] == 'checked') OR ($tag['attribute']['checked'] == 'true'))) {
					$checked = true;
				} else {
					$checked = false;
				}
				if (isset($tag['align'])) {
					switch ($tag['align']) {
						case 'C': {
							$opt['q'] = 1;
							break;
						}
						case 'R': {
							$opt['q'] = 2;
							break;
						}
						case 'L':
						default: {
							break;
						}
					}
				}
				switch ($tag['attribute']['type']) {
					case 'text': {
						if (isset($value)) {
							$opt['v'] = $value;
						}
						$this->TextField($name, $w, $h, $prop, $opt, '', '', false);
						break;
					}
					case 'password': {
						if (isset($value)) {
							$opt['v'] = $value;
						}
						$prop['password'] = 'true';
						$this->TextField($name, $w, $h, $prop, $opt, '', '', false);
						break;
					}
					case 'checkbox': {
						if (!isset($value)) {
							break;
						}
						$this->CheckBox($name, $w, $checked, $prop, $opt, $value, '', '', false);
						break;
					}
					case 'radio': {
						if (!isset($value)) {
							break;
						}
						$this->RadioButton($name, $w, $prop, $opt, $value, $checked, '', '', false);
						break;
					}
					case 'submit': {
						if (!isset($value)) {
							$value = 'submit';
						}
						$w = $this->GetStringWidth($value) * 1.5;
						$h *= 1.6;
						$prop = array('lineWidth'=>1, 'borderStyle'=>'beveled', 'fillColor'=>array(196, 196, 196), 'strokeColor'=>array(255, 255, 255));
						$action = array();
						$action['S'] = 'SubmitForm';
						$action['F'] = $this->form_action;
						if ($this->form_enctype != 'FDF') {
							$action['Flags'] = array('ExportFormat');
						}
						if ($this->form_mode == 'get') {
							$action['Flags'] = array('GetMethod');
						}
						$this->Button($name, $w, $h, $value, $action, $prop, $opt, '', '', false);
						break;
					}
					case 'reset': {
						if (!isset($value)) {
							$value = 'reset';
						}
						$w = $this->GetStringWidth($value) * 1.5;
						$h *= 1.6;
						$prop = array('lineWidth'=>1, 'borderStyle'=>'beveled', 'fillColor'=>array(196, 196, 196), 'strokeColor'=>array(255, 255, 255));
						$this->Button($name, $w, $h, $value, array('S'=>'ResetForm'), $prop, $opt, '', '', false);
						break;
					}
					case 'file': {
						$prop['fileSelect'] = 'true';
						$this->TextField($name, $w, $h, $prop, $opt, '', '', false);
						if (!isset($value)) {
							$value = '*';
						}
						$w = $this->GetStringWidth($value) * 2;
						$h *= 1.2;
						$prop = array('lineWidth'=>1, 'borderStyle'=>'beveled', 'fillColor'=>array(196, 196, 196), 'strokeColor'=>array(255, 255, 255));
						$jsaction = 'var f=this.getField(\''.$name.'\'); f.browseForFileToSubmit();';
						$this->Button('FB_'.$name, $w, $h, $value, $jsaction, $prop, $opt, '', '', false);
						break;
					}
					case 'hidden': {
						if (isset($value)) {
							$opt['v'] = $value;
						}
						$opt['f'] = array('invisible', 'hidden');
						$this->TextField($name, 0, 0, $prop, $opt, '', '', false);
						break;
					}
					case 'image': {
						// THIS TYPE MUST BE FIXED
						if (isset($tag['attribute']['src']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['src'])) {
							$img = $tag['attribute']['src'];
						} else {
							break;
						}
						$value = 'img';
						//$opt['mk'] = array('i'=>$img, 'tp'=>1, 'if'=>array('sw'=>'A', 's'=>'A', 'fb'=>false));
						if (isset($tag['attribute']['onclick']) AND !empty($tag['attribute']['onclick'])) {
							$jsaction = $tag['attribute']['onclick'];
						} else {
							$jsaction = '';
						}
						$this->Button($name, $w, $h, $value, $jsaction, $prop, $opt, '', '', false);
						break;
					}
					case 'button': {
						if (!isset($value)) {
							$value = ' ';
						}
						$w = $this->GetStringWidth($value) * 1.5;
						$h *= 1.6;
						$prop = array('lineWidth'=>1, 'borderStyle'=>'beveled', 'fillColor'=>array(196, 196, 196), 'strokeColor'=>array(255, 255, 255));
						if (isset($tag['attribute']['onclick']) AND !empty($tag['attribute']['onclick'])) {
							$jsaction = $tag['attribute']['onclick'];
						} else {
							$jsaction = '';
						}
						$this->Button($name, $w, $h, $value, $jsaction, $prop, $opt, '', '', false);
						break;
					}
				}
				break;
			}
			case 'textarea': {
				$prop = array();
				$opt = array();
				if (isset($tag['attribute']['readonly']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['readonly'])) {
					$prop['readonly'] = true;
				}
				if (isset($tag['attribute']['name']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['name'])) {
					$name = $tag['attribute']['name'];
				} else {
					break;
				}
				if (isset($tag['attribute']['value']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['value'])) {
					$opt['v'] = $tag['attribute']['value'];
				}
				if (isset($tag['attribute']['cols']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['cols'])) {
					$w = intval($tag['attribute']['cols']) * $this->GetStringWidth(chr(32)) * 2;
				} else {
					$w = 40;
				}
				if (isset($tag['attribute']['rows']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['rows'])) {
					$h = intval($tag['attribute']['rows']) * $this->getCellHeight($this->FontSize);
				} else {
					$h = 10;
				}
				$prop['multiline'] = 'true';
				$this->TextField($name, $w, $h, $prop, $opt, '', '', false);
				break;
			}
			case 'select': {
				$h = $this->getCellHeight($this->FontSize);
				if (isset($tag['attribute']['size']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['size'])) {
					$h *= ($tag['attribute']['size'] + 1);
				}
				$prop = array();
				$opt = array();
				if (isset($tag['attribute']['name']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['name'])) {
					$name = $tag['attribute']['name'];
				} else {
					break;
				}
				$w = 0;
				if (isset($tag['attribute']['opt']) AND !LIMEPDF_STATIC::empty_string($tag['attribute']['opt'])) {
					$options = explode('#!NwL!#', $tag['attribute']['opt']);
					$values = array();
					foreach ($options as $val) {
						if (strpos($val, '#!TaB!#') !== false) {
							$opts = explode('#!TaB!#', $val);
							$values[] = $opts;
							$w = max($w, $this->GetStringWidth($opts[1]));
						} else {
							$values[] = $val;
							$w = max($w, $this->GetStringWidth($val));
						}
					}
				} else {
					break;
				}
				$w *= 2;
				if (isset($tag['attribute']['multiple']) AND ($tag['attribute']['multiple']='multiple')) {
					$prop['multipleSelection'] = 'true';
					$this->ListBox($name, $w, $h, $values, $prop, $opt, '', '', false);
				} else {
					$this->ComboBox($name, $w, $h, $values, $prop, $opt, '', '', false);
				}
				break;
			}
			case 'tcpdf': {
				if (defined('K_TCPDF_CALLS_IN_HTML') AND (K_TCPDF_CALLS_IN_HTML === true)) {
					// Special tag used to call TCPDF methods
					// This tag is disabled by default by the K_TCPDF_CALLS_IN_HTML constant on TCPDF configuration file.
					// Please use this feature only if you are in control of the HTML content and you are sure that it does not contain any harmful code.
					if (!empty($tag['attribute']['data'])) {
						$tcpdf_tag_data = $this->unserializeTCPDFtag($tag['attribute']['data']);
						if ($this->allowedTCPDFtag($tcpdf_tag_data['m'])) {
							call_user_func_array(array($this, $tcpdf_tag_data['m']), $tcpdf_tag_data['p']);
						}
						$this->newline = true;
					}
				}
				break;
			}
			default: {
				break;
			}
		}
		// define tags that support borders and background colors
		$bordertags = array('blockquote','br','dd','dl','div','dt','h1','h2','h3','h4','h5','h6','hr','li','ol','p','pre','ul','tcpdf','table');
		if (in_array($tag['value'], $bordertags)) {
			// set border
			$dom[$key]['borderposition'] = $this->getBorderStartPosition();
		}
		if ($dom[$key]['self'] AND isset($dom[$key]['attribute']['pagebreakafter'])) {
			$pba = $dom[$key]['attribute']['pagebreakafter'];
			// check for pagebreak
			if (($pba == 'true') OR ($pba == 'left') OR ($pba == 'right')) {
				// add a page (or trig AcceptPageBreak() for multicolumn mode)
				$this->checkPageBreak($this->PageBreakTrigger + 1);
			}
			if ((($pba == 'left') AND (((!$this->rtl) AND (($this->page % 2) == 0)) OR (($this->rtl) AND (($this->page % 2) != 0))))
				OR (($pba == 'right') AND (((!$this->rtl) AND (($this->page % 2) != 0)) OR (($this->rtl) AND (($this->page % 2) == 0))))) {
				// add a page (or trig AcceptPageBreak() for multicolumn mode)
				$this->checkPageBreak($this->PageBreakTrigger + 1);
			}
		}
		return $dom;
	}

	/**
	 * Process closing tags.
	 * @param array $dom html dom array
	 * @param int $key current element id
	 * @param boolean $cell if true add the default left (or right if RTL) padding to each new line (default false).
	 * @param int $maxbottomliney maximum y value of current line
	 * @return array $dom
	 * @protected
	 */
	protected function closeHTMLTagHandler($dom, $key, $cell, $maxbottomliney=0) {
		$tag = $dom[$key];
		$parent = $dom[($dom[$key]['parent'])];
		$lasttag = ((!isset($dom[($key + 1)])) OR ((!isset($dom[($key + 2)])) AND ($dom[($key + 1)]['value'] == 'marker')));
		$in_table_head = false;
		// maximum x position (used to draw borders)
		if ($this->rtl) {
			$xmax = $this->w;
		} else {
			$xmax = 0;
		}
		if ($tag['block']) {
			$hbz = 0; // distance from y to line bottom
			$hb = 0; // vertical space between block tags
			// calculate vertical space for block tags
			if (isset($this->tagvspaces[$tag['value']][1]['h']) && !empty($this->tagvspaces[$tag['value']][1]['h']) && ($this->tagvspaces[$tag['value']][1]['h'] >= 0)) {
				$pre_h = $this->tagvspaces[$tag['value']][1]['h'];
			} elseif (isset($parent['fontsize'])) {
				$pre_h = $this->getCellHeight($parent['fontsize'] / $this->k);
			} else {
				$pre_h = $this->getCellHeight($this->FontSize);
			}
			if (isset($this->tagvspaces[$tag['value']][1]['n'])) {
				$cn = $this->tagvspaces[$tag['value']][1]['n'];
			} elseif (preg_match('/[h][0-9]/', $tag['value']) > 0) {
				$cn = 0.6;
			} else {
				$cn = 1;
			}
			if ((!isset($this->tagvspaces[$tag['value']])) AND ($tag['value'] == 'div')) {
				$hb = 0;
			} else {
				$hb = ($cn * $pre_h);
			}
			if ($maxbottomliney > $this->PageBreakTrigger) {
				$hbz = $this->getCellHeight($this->FontSize);
			} elseif ($this->y < $maxbottomliney) {
				$hbz = ($maxbottomliney - $this->y);
			}
		}
		// Closing tag
		switch($tag['value']) {
			case 'tr': {
				$table_el = $dom[($dom[$key]['parent'])]['parent'];
				if (!isset($parent['endy'])) {
					$dom[($dom[$key]['parent'])]['endy'] = $this->y;
					$parent['endy'] = $this->y;
				}
				if (!isset($parent['endpage'])) {
					$dom[($dom[$key]['parent'])]['endpage'] = $this->page;
					$parent['endpage'] = $this->page;
				}
				if (!isset($parent['endcolumn'])) {
					$dom[($dom[$key]['parent'])]['endcolumn'] = $this->current_column;
					$parent['endcolumn'] = $this->current_column;
				}
				// update row-spanned cells
				if (isset($dom[$table_el]['rowspans'])) {
					foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
						$dom[$table_el]['rowspans'][$k]['rowspan'] -= 1;
						if ($dom[$table_el]['rowspans'][$k]['rowspan'] == 0) {
							if (($dom[$table_el]['rowspans'][$k]['endpage'] == $parent['endpage']) AND ($dom[$table_el]['rowspans'][$k]['endcolumn'] == $parent['endcolumn'])) {
								$dom[($dom[$key]['parent'])]['endy'] = max($dom[$table_el]['rowspans'][$k]['endy'], $parent['endy']);
							} elseif (($dom[$table_el]['rowspans'][$k]['endpage'] > $parent['endpage']) OR ($dom[$table_el]['rowspans'][$k]['endcolumn'] > $parent['endcolumn'])) {
								$dom[($dom[$key]['parent'])]['endy'] = $dom[$table_el]['rowspans'][$k]['endy'];
								$dom[($dom[$key]['parent'])]['endpage'] = $dom[$table_el]['rowspans'][$k]['endpage'];
								$dom[($dom[$key]['parent'])]['endcolumn'] = $dom[$table_el]['rowspans'][$k]['endcolumn'];
							}
						}
					}
					// report new endy and endpage to the rowspanned cells
					foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
						if ($dom[$table_el]['rowspans'][$k]['rowspan'] == 0) {
							$dom[$table_el]['rowspans'][$k]['endpage'] = max($dom[$table_el]['rowspans'][$k]['endpage'], $dom[($dom[$key]['parent'])]['endpage']);
							$dom[($dom[$key]['parent'])]['endpage'] = $dom[$table_el]['rowspans'][$k]['endpage'];
							$dom[$table_el]['rowspans'][$k]['endcolumn'] = max($dom[$table_el]['rowspans'][$k]['endcolumn'], $dom[($dom[$key]['parent'])]['endcolumn']);
							$dom[($dom[$key]['parent'])]['endcolumn'] = $dom[$table_el]['rowspans'][$k]['endcolumn'];
							$dom[$table_el]['rowspans'][$k]['endy'] = max($dom[$table_el]['rowspans'][$k]['endy'], $dom[($dom[$key]['parent'])]['endy']);
							$dom[($dom[$key]['parent'])]['endy'] = $dom[$table_el]['rowspans'][$k]['endy'];
						}
					}
					// update remaining rowspanned cells
					foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
						if ($dom[$table_el]['rowspans'][$k]['rowspan'] == 0) {
							$dom[$table_el]['rowspans'][$k]['endpage'] = $dom[($dom[$key]['parent'])]['endpage'];
							$dom[$table_el]['rowspans'][$k]['endcolumn'] = $dom[($dom[$key]['parent'])]['endcolumn'];
							$dom[$table_el]['rowspans'][$k]['endy'] = $dom[($dom[$key]['parent'])]['endy'];
						}
					}
				}
				$prev_page = $this->page;
				$this->setPage($dom[($dom[$key]['parent'])]['endpage']);
				if ($this->num_columns > 1) {
					if (($prev_page < $this->page)
						AND ((($this->current_column == 0) AND ($dom[($dom[$key]['parent'])]['endcolumn'] == ($this->num_columns - 1)))
							OR ($this->current_column == $dom[($dom[$key]['parent'])]['endcolumn']))) {
						// page jump
						$this->selectColumn(0);
						$dom[($dom[$key]['parent'])]['endcolumn'] = 0;
						$dom[($dom[$key]['parent'])]['endy'] = $this->y;
					} else {
						$this->selectColumn($dom[($dom[$key]['parent'])]['endcolumn']);
						$this->y = $dom[($dom[$key]['parent'])]['endy'];
					}
				} else {
					$this->y = $dom[($dom[$key]['parent'])]['endy'];
				}
				if (isset($dom[$table_el]['attribute']['cellspacing'])) {
					$this->y += $this->getHTMLUnitToUnits($dom[$table_el]['attribute']['cellspacing'], 1, 'px');
				} elseif (isset($dom[$table_el]['border-spacing'])) {
					$this->y += $dom[$table_el]['border-spacing']['V'];
				}
				$this->Ln(0, $cell);
				if ($this->current_column == $parent['startcolumn']) {
					$this->x = $parent['startx'];
				}
				// account for booklet mode
				if ($this->page > $parent['startpage']) {
					if (($this->rtl) AND ($this->pagedim[$this->page]['orm'] != $this->pagedim[$parent['startpage']]['orm'])) {
						$this->x -= ($this->pagedim[$this->page]['orm'] - $this->pagedim[$parent['startpage']]['orm']);
					} elseif ((!$this->rtl) AND ($this->pagedim[$this->page]['olm'] != $this->pagedim[$parent['startpage']]['olm'])) {
						$this->x += ($this->pagedim[$this->page]['olm'] - $this->pagedim[$parent['startpage']]['olm']);
					}
				}
				break;
			}
			case 'tablehead':
				// closing tag used for the thead part
				$in_table_head = true;
				$this->inthead = false;
			case 'table': {
				$table_el = $parent;
				// set default border
				if (isset($table_el['attribute']['border']) AND ($table_el['attribute']['border'] > 0)) {
					// set default border
					$border = array('LTRB' => array('width' => $this->getCSSBorderWidth($table_el['attribute']['border']), 'cap'=>'square', 'join'=>'miter', 'dash'=> 0, 'color'=>array(0,0,0)));
				} else {
					$border = 0;
				}
				$default_border = $border;
				// fix bottom line alignment of last line before page break
				foreach ($dom[($dom[$key]['parent'])]['trids'] as $j => $trkey) {
					// update row-spanned cells
					if (isset($dom[($dom[$key]['parent'])]['rowspans'])) {
						foreach ($dom[($dom[$key]['parent'])]['rowspans'] as $k => $trwsp) {
							if (isset($prevtrkey) AND ($trwsp['trid'] == $prevtrkey) AND ($trwsp['mrowspan'] > 0)) {
								$dom[($dom[$key]['parent'])]['rowspans'][$k]['trid'] = $trkey;
							}
							if ($dom[($dom[$key]['parent'])]['rowspans'][$k]['trid'] == $trkey) {
								$dom[($dom[$key]['parent'])]['rowspans'][$k]['mrowspan'] -= 1;
							}
						}
					}
					if (isset($prevtrkey) AND ($dom[$trkey]['startpage'] > $dom[$prevtrkey]['endpage'])) {
						$pgendy = $this->pagedim[$dom[$prevtrkey]['endpage']]['hk'] - $this->pagedim[$dom[$prevtrkey]['endpage']]['bm'];
						$dom[$prevtrkey]['endy'] = $pgendy;
						// update row-spanned cells
						if (isset($dom[($dom[$key]['parent'])]['rowspans'])) {
							foreach ($dom[($dom[$key]['parent'])]['rowspans'] as $k => $trwsp) {
								if (($trwsp['trid'] == $prevtrkey) AND ($trwsp['mrowspan'] >= 0) AND ($trwsp['endpage'] == $dom[$prevtrkey]['endpage'])) {
									$dom[($dom[$key]['parent'])]['rowspans'][$k]['endy'] = $pgendy;
									$dom[($dom[$key]['parent'])]['rowspans'][$k]['mrowspan'] = -1;
								}
							}
						}
					}
					$prevtrkey = $trkey;
					$table_el = $dom[($dom[$key]['parent'])];
				}
				// for each row
				if (!empty($table_el['trids'])) {
					unset($xmax);
				}
				foreach ($table_el['trids'] as $j => $trkey) {
					$parent = $dom[$trkey];
					if (!isset($xmax)) {
						$xmax = $parent['cellpos'][(count($parent['cellpos']) - 1)]['endx'];
					}
					// for each cell on the row
					foreach ($parent['cellpos'] as $k => $cellpos) {
						if (isset($cellpos['rowspanid']) AND ($cellpos['rowspanid'] >= 0)) {
							$cellpos['startx'] = $table_el['rowspans'][($cellpos['rowspanid'])]['startx'];
							$cellpos['endx'] = $table_el['rowspans'][($cellpos['rowspanid'])]['endx'];
							$endy = $table_el['rowspans'][($cellpos['rowspanid'])]['endy'];
							$startpage = $table_el['rowspans'][($cellpos['rowspanid'])]['startpage'];
							$endpage = $table_el['rowspans'][($cellpos['rowspanid'])]['endpage'];
							$startcolumn = $table_el['rowspans'][($cellpos['rowspanid'])]['startcolumn'];
							$endcolumn = $table_el['rowspans'][($cellpos['rowspanid'])]['endcolumn'];
						} else {
							$endy = $parent['endy'];
							$startpage = $parent['startpage'];
							$endpage = $parent['endpage'];
							$startcolumn = $parent['startcolumn'];
							$endcolumn = $parent['endcolumn'];
						}
						if ($this->num_columns == 0) {
							$this->num_columns = 1;
						}
						if (isset($cellpos['border'])) {
							$border = $cellpos['border'];
						}
						if (isset($cellpos['bgcolor']) AND ($cellpos['bgcolor']) !== false) {
							$this->setFillColorArray($cellpos['bgcolor']);
							$fill = true;
						} else {
							$fill = false;
						}
						$x = $cellpos['startx'];
						$y = $parent['starty'];
						$starty = $y;
						$w = abs($cellpos['endx'] - $cellpos['startx']);
						// get border modes
						$border_start = LIMEPDF_STATIC::getBorderMode($border, $position='start', $this->opencell);
						$border_end = LIMEPDF_STATIC::getBorderMode($border, $position='end', $this->opencell);
						$border_middle = LIMEPDF_STATIC::getBorderMode($border, $position='middle', $this->opencell);
						// design borders around HTML cells.
						for ($page = $startpage; $page <= $endpage; ++$page) { // for each page
							$ccode = '';
							$this->setPage($page);
							if ($this->num_columns < 2) {
								// single-column mode
								$this->x = $x;
								$this->y = $this->tMargin;
							}
							// account for margin changes
							if ($page > $startpage) {
								if (($this->rtl) AND ($this->pagedim[$page]['orm'] != $this->pagedim[$startpage]['orm'])) {
									$this->x -= ($this->pagedim[$page]['orm'] - $this->pagedim[$startpage]['orm']);
								} elseif ((!$this->rtl) AND ($this->pagedim[$page]['olm'] != $this->pagedim[$startpage]['olm'])) {
									$this->x += ($this->pagedim[$page]['olm'] - $this->pagedim[$startpage]['olm']);
								}
							}
							if ($startpage == $endpage) { // single page
								$deltacol = 0;
								$deltath = 0;
								for ($column = $startcolumn; $column <= $endcolumn; ++$column) { // for each column
									$this->selectColumn($column);
									if ($startcolumn == $endcolumn) { // single column
										$cborder = $border;
										$h = $endy - $parent['starty'];
										$this->y = $y;
										$this->x = $x;
									} elseif ($column == $startcolumn) { // first column
										$cborder = $border_start;
										$this->y = $starty;
										$this->x = $x;
										$h = $this->h - $this->y - $this->bMargin;
										if ($this->rtl) {
											$deltacol = $this->x + $this->rMargin - $this->w;
										} else {
											$deltacol = $this->x - $this->lMargin;
										}
									} elseif ($column == $endcolumn) { // end column
										$cborder = $border_end;
										if (isset($this->columns[$column]['th']['\''.$page.'\''])) {
											$this->y = $this->columns[$column]['th']['\''.$page.'\''];
										}
										$this->x += $deltacol;
										$h = $endy - $this->y;
									} else { // middle column
										$cborder = $border_middle;
										if (isset($this->columns[$column]['th']['\''.$page.'\''])) {
											$this->y = $this->columns[$column]['th']['\''.$page.'\''];
										}
										$this->x += $deltacol;
										$h = $this->h - $this->y - $this->bMargin;
									}
									$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
								} // end for each column
							} elseif ($page == $startpage) { // first page
								$deltacol = 0;
								$deltath = 0;
								for ($column = $startcolumn; $column < $this->num_columns; ++$column) { // for each column
									$this->selectColumn($column);
									if ($column == $startcolumn) { // first column
										$cborder = $border_start;
										$this->y = $starty;
										$this->x = $x;
										$h = $this->h - $this->y - $this->bMargin;
										if ($this->rtl) {
											$deltacol = $this->x + $this->rMargin - $this->w;
										} else {
											$deltacol = $this->x - $this->lMargin;
										}
									} else { // middle column
										$cborder = $border_middle;
										if (isset($this->columns[$column]['th']['\''.$page.'\''])) {
											$this->y = $this->columns[$column]['th']['\''.$page.'\''];
										}
										$this->x += $deltacol;
										$h = $this->h - $this->y - $this->bMargin;
									}
									$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
								} // end for each column
							} elseif ($page == $endpage) { // last page
								$deltacol = 0;
								$deltath = 0;
								for ($column = 0; $column <= $endcolumn; ++$column) { // for each column
									$this->selectColumn($column);
									if ($column == $endcolumn) { // end column
										$cborder = $border_end;
										if (isset($this->columns[$column]['th']['\''.$page.'\''])) {
											$this->y = $this->columns[$column]['th']['\''.$page.'\''];
										}
										$this->x += $deltacol;
										$h = $endy - $this->y;
									} else { // middle column
										$cborder = $border_middle;
										if (isset($this->columns[$column]['th']['\''.$page.'\''])) {
											$this->y = $this->columns[$column]['th']['\''.$page.'\''];
										}
										$this->x += $deltacol;
										$h = $this->h - $this->y - $this->bMargin;
									}
									$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
								} // end for each column
							} else { // middle page
								$deltacol = 0;
								$deltath = 0;
								for ($column = 0; $column < $this->num_columns; ++$column) { // for each column
									$this->selectColumn($column);
									$cborder = $border_middle;
									if (isset($this->columns[$column]['th']['\''.$page.'\''])) {
										$this->y = $this->columns[$column]['th']['\''.$page.'\''];
									}
									$this->x += $deltacol;
									$h = $this->h - $this->y - $this->bMargin;
									$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
								} // end for each column
							}
							if (!empty($cborder) OR !empty($fill)) {
								$offsetlen = strlen($ccode);
								// draw border and fill
								if ($this->inxobj) {
									// we are inside an XObject template
									if (end($this->xobjects[$this->xobjid]['transfmrk']) !== false) {
										$pagemarkkey = key($this->xobjects[$this->xobjid]['transfmrk']);
										$pagemark = $this->xobjects[$this->xobjid]['transfmrk'][$pagemarkkey];
										$this->xobjects[$this->xobjid]['transfmrk'][$pagemarkkey] += $offsetlen;
									} else {
										$pagemark = $this->xobjects[$this->xobjid]['intmrk'];
										$this->xobjects[$this->xobjid]['intmrk'] += $offsetlen;
									}
									$pagebuff = $this->xobjects[$this->xobjid]['outdata'];
									$pstart = substr($pagebuff, 0, $pagemark);
									$pend = substr($pagebuff, $pagemark);
									$this->xobjects[$this->xobjid]['outdata'] = $pstart.$ccode.$pend;
								} else {
									// draw border and fill
									if (end($this->transfmrk[$this->page]) !== false) {
										$pagemarkkey = key($this->transfmrk[$this->page]);
										$pagemark = $this->transfmrk[$this->page][$pagemarkkey];
									} elseif ($this->InFooter) {
										$pagemark = $this->footerpos[$this->page];
									} else {
										$pagemark = $this->intmrk[$this->page];
									}
									$pagebuff = $this->getPageBuffer($this->page);
									$pstart = substr($pagebuff, 0, $pagemark);
									$pend = substr($pagebuff, $pagemark);
									$this->setPageBuffer($this->page, $pstart.$ccode.$pend);
								}
							}
						} // end for each page
						// restore default border
						$border = $default_border;
					} // end for each cell on the row
					if (isset($table_el['attribute']['cellspacing'])) {
						$this->y += $this->getHTMLUnitToUnits($table_el['attribute']['cellspacing'], 1, 'px');
					} elseif (isset($table_el['border-spacing'])) {
						$this->y += $table_el['border-spacing']['V'];
					}
					$this->Ln(0, $cell);
					$this->x = $parent['startx'];
					if ($endpage > $startpage) {
						if (($this->rtl) AND ($this->pagedim[$endpage]['orm'] != $this->pagedim[$startpage]['orm'])) {
							$this->x += ($this->pagedim[$endpage]['orm'] - $this->pagedim[$startpage]['orm']);
						} elseif ((!$this->rtl) AND ($this->pagedim[$endpage]['olm'] != $this->pagedim[$startpage]['olm'])) {
							$this->x += ($this->pagedim[$endpage]['olm'] - $this->pagedim[$startpage]['olm']);
						}
					}
				}
				if (!$in_table_head) { // we are not inside a thead section
					$this->cell_padding = isset($table_el['old_cell_padding']) ? $table_el['old_cell_padding'] : array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0);
					// reset row height
					$this->resetLastH();
					if (($this->page == ($this->numpages - 1)) AND ($this->pageopen[$this->numpages])) {
						$plendiff = ($this->pagelen[$this->numpages] - $this->emptypagemrk[$this->numpages]);
						if (($plendiff > 0) AND ($plendiff < 60)) {
							$pagediff = substr($this->getPageBuffer($this->numpages), $this->emptypagemrk[$this->numpages], $plendiff);
							if (substr($pagediff, 0, 5) == 'BT /F') {
								// the difference is only a font setting
								$plendiff = 0;
							}
						}
						if ($plendiff == 0) {
							// remove last blank page
							$this->deletePage($this->numpages);
						}
					}
					if (isset($this->theadMargins['top'])) {
						// restore top margin
						$this->tMargin = $this->theadMargins['top'];
					}
					if (!isset($table_el['attribute']['nested']) OR ($table_el['attribute']['nested'] != 'true')) {
						// reset main table header
						$this->thead = '';
						$this->theadMargins = array();
						$this->pagedim[$this->page]['tm'] = $this->tMargin;
					}
				}
				$parent = $table_el;
				break;
			}
			case 'a': {
				$this->HREF = array();
				break;
			}
			case 'sup': {
				$this->setXY($this->GetX(), $this->GetY() + ((0.7 * $parent['fontsize']) / $this->k));
				break;
			}
			case 'sub': {
				$this->setXY($this->GetX(), $this->GetY() - ((0.3 * $parent['fontsize']) / $this->k));
				break;
			}
			case 'div': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				break;
			}
			case 'blockquote': {
				if ($this->rtl) {
					$this->rMargin -= $this->listindent;
				} else {
					$this->lMargin -= $this->listindent;
				}
				--$this->listindentlevel;
				$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				break;
			}
			case 'p': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				break;
			}
			case 'pre': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				$this->premode = false;
				break;
			}
			case 'dl': {
				--$this->listnum;
				if ($this->listnum <= 0) {
					$this->listnum = 0;
					$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				} else {
					$this->addHTMLVertSpace(0, 0, $cell, false, $lasttag);
				}
				$this->resetLastH();
				break;
			}
			case 'dt': {
				$this->lispacer = '';
				$this->addHTMLVertSpace(0, 0, $cell, false, $lasttag);
				break;
			}
			case 'dd': {
				$this->lispacer = '';
				if ($this->rtl) {
					$this->rMargin -= $this->listindent;
				} else {
					$this->lMargin -= $this->listindent;
				}
				--$this->listindentlevel;
				$this->addHTMLVertSpace(0, 0, $cell, false, $lasttag);
				break;
			}
			case 'ul':
			case 'ol': {
				--$this->listnum;
				$this->lispacer = '';
				if ($this->rtl) {
					$this->rMargin -= $this->listindent;
				} else {
					$this->lMargin -= $this->listindent;
				}
				--$this->listindentlevel;
				if ($this->listnum <= 0) {
					$this->listnum = 0;
					$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				} else {
					$this->addHTMLVertSpace(0, 0, $cell, false, $lasttag);
				}
				$this->resetLastH();
				break;
			}
			case 'li': {
				$this->lispacer = '';
				$this->addHTMLVertSpace(0, 0, $cell, false, $lasttag);
				break;
			}
			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6': {
				$this->addHTMLVertSpace($hbz, $hb, $cell, false, $lasttag);
				break;
			}
			// Form fields (since 4.8.000 - 2009-09-07)
			case 'form': {
				$this->form_action = '';
				$this->form_enctype = 'application/x-www-form-urlencoded';
				break;
			}
			default : {
				break;
			}
		}
		// draw border and background (if any)
		$this->drawHTMLTagBorder($parent, $xmax);
		if (isset($dom[($dom[$key]['parent'])]['attribute']['pagebreakafter'])) {
			$pba = $dom[($dom[$key]['parent'])]['attribute']['pagebreakafter'];
			// check for pagebreak
			if (($pba == 'true') OR ($pba == 'left') OR ($pba == 'right')) {
				// add a page (or trig AcceptPageBreak() for multicolumn mode)
				$this->checkPageBreak($this->PageBreakTrigger + 1);
			}
			if ((($pba == 'left') AND (((!$this->rtl) AND (($this->page % 2) == 0)) OR (($this->rtl) AND (($this->page % 2) != 0))))
				OR (($pba == 'right') AND (((!$this->rtl) AND (($this->page % 2) != 0)) OR (($this->rtl) AND (($this->page % 2) == 0))))) {
				// add a page (or trig AcceptPageBreak() for multicolumn mode)
				$this->checkPageBreak($this->PageBreakTrigger + 1);
			}
		}
		$this->tmprtl = false;
		return $dom;
	}

	/**
	 * Add vertical spaces if needed.
	 * @param string $hbz Distance between current y and line bottom.
	 * @param string $hb The height of the break.
	 * @param boolean $cell if true add the default left (or right if RTL) padding to each new line (default false).
	 * @param boolean $firsttag set to true when the tag is the first.
	 * @param boolean $lasttag set to true when the tag is the last.
	 * @protected
	 */
	protected function addHTMLVertSpace($hbz=0, $hb=0, $cell=false, $firsttag=false, $lasttag=false) {
		if ($firsttag) {
			$this->Ln(0, $cell);
			$this->htmlvspace = 0;
			return;
		}
		if ($lasttag) {
			$this->Ln($hbz, $cell);
			$this->htmlvspace = 0;
			return;
		}
		if ($hb < $this->htmlvspace) {
			$hd = 0;
		} else {
			$hd = $hb - $this->htmlvspace;
			$this->htmlvspace = $hb;
		}
		$this->Ln(($hbz + $hd), $cell);
	}

	/**
	 * Return the starting coordinates to draw an html border
	 * @return array containing top-left border coordinates
	 * @protected
	 * @since 5.7.000 (2010-08-03)
	 */
	protected function getBorderStartPosition() {
		if ($this->rtl) {
			$xmax = $this->lMargin;
		} else {
			$xmax = $this->w - $this->rMargin;
		}
		return array('page' => $this->page, 'column' => $this->current_column, 'x' => $this->x, 'y' => $this->y, 'xmax' => $xmax);
	}

	/**
	 * Draw an HTML block border and fill
	 * @param array $tag array of tag properties.
	 * @param int $xmax end X coordinate for border.
	 * @protected
	 * @since 5.7.000 (2010-08-03)
	 */
	protected function drawHTMLTagBorder($tag, $xmax) {
		if (!isset($tag['borderposition'])) {
			// nothing to draw
			return;
		}
		$prev_x = $this->x;
		$prev_y = $this->y;
		$prev_lasth = $this->lasth;
		$border = 0;
		$fill = false;
		$this->lasth = 0;
		if (isset($tag['border']) AND !empty($tag['border'])) {
			// get border style
			$border = $tag['border'];
			if (!LIMEPDF_STATIC::empty_string($this->thead) AND (!$this->inthead)) {
				// border for table header
				$border = LIMEPDF_STATIC::getBorderMode($border, $position='middle', $this->opencell);
			}
		}
		if (isset($tag['bgcolor']) AND ($tag['bgcolor'] !== false)) {
			// get background color
			$old_bgcolor = $this->bgcolor;
			$this->setFillColorArray($tag['bgcolor']);
			$fill = true;
		}
		if (!$border AND !$fill) {
			// nothing to draw
			return;
		}
		if (isset($tag['attribute']['cellspacing'])) {
			$clsp = $this->getHTMLUnitToUnits($tag['attribute']['cellspacing'], 1, 'px');
			$cellspacing = array('H' => $clsp, 'V' => $clsp);
		} elseif (isset($tag['border-spacing'])) {
			$cellspacing = $tag['border-spacing'];
		} else {
			$cellspacing = array('H' => 0, 'V' => 0);
		}
		if (($tag['value'] != 'table') AND (is_array($border)) AND (!empty($border))) {
			// draw the border externally respect the sqare edge.
			$border['mode'] = 'ext';
		}
		if ($this->rtl) {
			if ($xmax >= $tag['borderposition']['x']) {
				$xmax = $tag['borderposition']['xmax'];
			}
			$w = ($tag['borderposition']['x'] - $xmax);
		} else {
			if ($xmax <= $tag['borderposition']['x']) {
				$xmax = $tag['borderposition']['xmax'];
			}
			$w = ($xmax - $tag['borderposition']['x']);
		}
		if ($w <= 0) {
			return;
		}
		$w += $cellspacing['H'];
		$startpage = $tag['borderposition']['page'];
		$startcolumn = $tag['borderposition']['column'];
		$x = $tag['borderposition']['x'];
		$y = $tag['borderposition']['y'];
		$endpage = $this->page;
		$starty = $tag['borderposition']['y'] - $cellspacing['V'];
		$currentY = $this->y;
		$this->x = $x;
		// get latest column
		$endcolumn = $this->current_column;
		if ($this->num_columns == 0) {
			$this->num_columns = 1;
		}
		// get border modes
		$border_start = LIMEPDF_STATIC::getBorderMode($border, $position='start', $this->opencell);
		$border_end = LIMEPDF_STATIC::getBorderMode($border, $position='end', $this->opencell);
		$border_middle = LIMEPDF_STATIC::getBorderMode($border, $position='middle', $this->opencell);
		// temporary disable page regions
		$temp_page_regions = $this->page_regions;
		$this->page_regions = array();
		// design borders around HTML cells.
		for ($page = $startpage; $page <= $endpage; ++$page) { // for each page
			$ccode = '';
			$this->setPage($page);
			if ($this->num_columns < 2) {
				// single-column mode
				$this->x = $x;
				$this->y = $this->tMargin;
			}
			// account for margin changes
			if ($page > $startpage) {
				if (($this->rtl) AND ($this->pagedim[$page]['orm'] != $this->pagedim[$startpage]['orm'])) {
					$this->x -= ($this->pagedim[$page]['orm'] - $this->pagedim[$startpage]['orm']);
				} elseif ((!$this->rtl) AND ($this->pagedim[$page]['olm'] != $this->pagedim[$startpage]['olm'])) {
					$this->x += ($this->pagedim[$page]['olm'] - $this->pagedim[$startpage]['olm']);
				}
			}
			if ($startpage == $endpage) {
				// single page
				for ($column = $startcolumn; $column <= $endcolumn; ++$column) { // for each column
					$this->selectColumn($column);
					if ($startcolumn == $endcolumn) { // single column
						$cborder = $border;
						$h = ($currentY - $y) + $cellspacing['V'];
						$this->y = $starty;
					} elseif ($column == $startcolumn) { // first column
						$cborder = $border_start;
						$this->y = $starty;
						$h = $this->h - $this->y - $this->bMargin;
					} elseif ($column == $endcolumn) { // end column
						$cborder = $border_end;
						$h = $currentY - $this->y;
					} else { // middle column
						$cborder = $border_middle;
						$h = $this->h - $this->y - $this->bMargin;
					}
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			} elseif ($page == $startpage) { // first page
				for ($column = $startcolumn; $column < $this->num_columns; ++$column) { // for each column
					$this->selectColumn($column);
					if ($column == $startcolumn) { // first column
						$cborder = $border_start;
						$this->y = $starty;
						$h = $this->h - $this->y - $this->bMargin;
					} else { // middle column
						$cborder = $border_middle;
						$h = $this->h - $this->y - $this->bMargin;
					}
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			} elseif ($page == $endpage) { // last page
				for ($column = 0; $column <= $endcolumn; ++$column) { // for each column
					$this->selectColumn($column);
					if ($column == $endcolumn) {
						// end column
						$cborder = $border_end;
						$h = $currentY - $this->y;
					} else {
						// middle column
						$cborder = $border_middle;
						$h = $this->h - $this->y - $this->bMargin;
					}
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			} else { // middle page
				for ($column = 0; $column < $this->num_columns; ++$column) { // for each column
					$this->selectColumn($column);
					$cborder = $border_middle;
					$h = $this->h - $this->y - $this->bMargin;
					$ccode .= $this->getCellCode($w, $h, '', $cborder, 1, '', $fill, '', 0, true)."\n";
				} // end for each column
			}
			if ($cborder OR $fill) {
				$offsetlen = strlen($ccode);
				// draw border and fill
				if ($this->inxobj) {
					// we are inside an XObject template
					if (end($this->xobjects[$this->xobjid]['transfmrk']) !== false) {
						$pagemarkkey = key($this->xobjects[$this->xobjid]['transfmrk']);
						$pagemark = $this->xobjects[$this->xobjid]['transfmrk'][$pagemarkkey];
						$this->xobjects[$this->xobjid]['transfmrk'][$pagemarkkey] += $offsetlen;
					} else {
						$pagemark = $this->xobjects[$this->xobjid]['intmrk'];
						$this->xobjects[$this->xobjid]['intmrk'] += $offsetlen;
					}
					$pagebuff = $this->xobjects[$this->xobjid]['outdata'];
					$pstart = substr($pagebuff, 0, $pagemark);
					$pend = substr($pagebuff, $pagemark);
					$this->xobjects[$this->xobjid]['outdata'] = $pstart.$ccode.$pend;
				} else {
					if (end($this->transfmrk[$this->page]) !== false) {
						$pagemarkkey = key($this->transfmrk[$this->page]);
						$pagemark = $this->transfmrk[$this->page][$pagemarkkey];
					} elseif ($this->InFooter) {
						$pagemark = $this->footerpos[$this->page];
					} else {
						$pagemark = $this->intmrk[$this->page];
					}
					$pagebuff = $this->getPageBuffer($this->page);
					$pstart = substr($pagebuff, 0, $pagemark);
					$pend = substr($pagebuff, $pagemark);
					$this->setPageBuffer($this->page, $pstart.$ccode.$pend);
					$this->bordermrk[$this->page] += $offsetlen;
					$this->cntmrk[$this->page] += $offsetlen;
				}
			}
		} // end for each page
		// restore page regions
		$this->page_regions = $temp_page_regions;
		if (isset($old_bgcolor)) {
			// restore background color
			$this->setFillColorArray($old_bgcolor);
		}
		// restore pointer position
		$this->x = $prev_x;
		$this->y = $prev_y;
		$this->lasth = $prev_lasth;
	}

	/**
	 * Set the default bullet to be used as LI bullet symbol
	 * @param string $symbol character or string to be used (legal values are: '' = automatic, '!' = auto bullet, '#' = auto numbering, 'disc', 'disc', 'circle', 'square', '1', 'decimal', 'decimal-leading-zero', 'i', 'lower-roman', 'I', 'upper-roman', 'a', 'lower-alpha', 'lower-latin', 'A', 'upper-alpha', 'upper-latin', 'lower-greek', 'img|type|width|height|image.ext')
	 * @public
	 * @since 4.0.028 (2008-09-26)
	 */
	public function setLIsymbol($symbol='!') {
		// check for custom image symbol
		if (substr($symbol, 0, 4) == 'img|') {
			$this->lisymbol = $symbol;
			return;
		}
		$symbol = strtolower($symbol);
		$valid_symbols = array('!', '#', 'disc', 'circle', 'square', '1', 'decimal', 'decimal-leading-zero', 'i', 'lower-roman', 'I', 'upper-roman', 'a', 'lower-alpha', 'lower-latin', 'A', 'upper-alpha', 'upper-latin', 'lower-greek');
		if (in_array($symbol, $valid_symbols)) {
			$this->lisymbol = $symbol;
		} else {
			$this->lisymbol = '';
		}
	}

	/**
	 * Set the booklet mode for double-sided pages.
	 * @param boolean $booklet true set the booklet mode on, false otherwise.
	 * @param float $inner Inner page margin.
	 * @param float $outer Outer page margin.
	 * @public
	 * @since 4.2.000 (2008-10-29)
	 */
	public function setBooklet($booklet=true, $inner=-1, $outer=-1) {
		$this->booklet = $booklet;
		if ($inner >= 0) {
			$this->lMargin = $inner;
		}
		if ($outer >= 0) {
			$this->rMargin = $outer;
		}
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

	/**
	 * Set the vertical spaces for HTML tags.
	 * The array must have the following structure (example):
	 * $tagvs = array('h1' => array(0 => array('h' => '', 'n' => 2), 1 => array('h' => 1.3, 'n' => 1)));
	 * The first array level contains the tag names,
	 * the second level contains 0 for opening tags or 1 for closing tags,
	 * the third level contains the vertical space unit (h) and the number spaces to add (n).
	 * If the h parameter is not specified, default values are used.
	 * @param array $tagvs array of tags and relative vertical spaces.
	 * @public
	 * @since 4.2.001 (2008-10-30)
	 */
	public function setHtmlVSpace($tagvs) {
		$this->tagvspaces = $tagvs;
	}

	/**
	 * Set custom width for list indentation.
	 * @param float $width width of the indentation. Use negative value to disable it.
	 * @public
	 * @since 4.2.007 (2008-11-12)
	 */
	public function setListIndentWidth($width) {
		return $this->customlistindent = floatval($width);
	}

	/**
	 * Set the top/bottom cell sides to be open or closed when the cell cross the page.
	 * @param boolean $isopen if true keeps the top/bottom border open for the cell sides that cross the page.
	 * @public
	 * @since 4.2.010 (2008-11-14)
	 */
	public function setOpenCell($isopen) {
		$this->opencell = $isopen;
	}

	/**
	 * Set the color and font style for HTML links.
	 * @param array $color RGB array of colors
	 * @param string $fontstyle additional font styles to add
	 * @public
	 * @since 4.4.003 (2008-12-09)
	 */
	public function setHtmlLinksStyle($color=array(0,0,255), $fontstyle='U') {
		$this->htmlLinkColorArray = $color;
		$this->htmlLinkFontStyle = $fontstyle;
	}

	/**
	 * Convert HTML string containing value and unit of measure to user's units or points.
	 * @param string $htmlval String containing values and unit.
	 * @param string $refsize Reference value in points.
	 * @param string $defaultunit Default unit (can be one of the following: %, em, ex, px, in, mm, pc, pt).
	 * @param boolean $points If true returns points, otherwise returns value in user's units.
	 * @return float value in user's unit or point if $points=true
	 * @public
	 * @since 4.4.004 (2008-12-10)
	 */
	public function getHTMLUnitToUnits($htmlval, $refsize=1, $defaultunit='px', $points=false) {
		$supportedunits = array('%', 'em', 'ex', 'px', 'in', 'cm', 'mm', 'pc', 'pt');
		$retval = 0;
		$value = 0;
		$unit = 'px';
		if ($points) {
			$k = 1;
		} else {
			$k = $this->k;
		}
		if (in_array($defaultunit, $supportedunits)) {
			$unit = $defaultunit;
		}
		if (is_numeric($htmlval)) {
			$value = floatval($htmlval);
		} elseif (preg_match('/([0-9\.\-\+]+)/', $htmlval, $mnum)) {
			$value = floatval($mnum[1]);
			if (preg_match('/([a-z%]+)/', $htmlval, $munit)) {
				if (in_array($munit[1], $supportedunits)) {
					$unit = $munit[1];
				}
			}
		}
		switch ($unit) {
			// percentage
			case '%': {
				$retval = (($value * $refsize) / 100);
				break;
			}
			// relative-size
			case 'em': {
				$retval = ($value * $refsize);
				break;
			}
			// height of lower case 'x' (about half the font-size)
			case 'ex': {
				$retval = ($value * ($refsize / 2));
				break;
			}
			// absolute-size
			case 'in': {
				$retval = (($value * $this->dpi) / $k);
				break;
			}
			// centimeters
			case 'cm': {
				$retval = (($value / 2.54 * $this->dpi) / $k);
				break;
			}
			// millimeters
			case 'mm': {
				$retval = (($value / 25.4 * $this->dpi) / $k);
				break;
			}
			// one pica is 12 points
			case 'pc': {
				$retval = (($value * 12) / $k);
				break;
			}
			// points
			case 'pt': {
				$retval = ($value / $k);
				break;
			}
			// pixels
			case 'px': {
				$retval = $this->pixelsToUnits($value);
				if ($points) {
					$retval *= $this->k;
				}
				break;
			}
		}
		return $retval;
	}

	/**
	 * Output an HTML list bullet or ordered item symbol
	 * @param int $listdepth list nesting level
	 * @param string $listtype type of list
	 * @param float $size current font size
	 * @protected
	 * @since 4.4.004 (2008-12-10)
	 */
	protected function putHtmlListBullet($listdepth, $listtype='', $size=10) {
		if ($this->state != 2) {
			return;
		}
		$size /= $this->k;
		$fill = '';
		$bgcolor = $this->bgcolor;
		$color = $this->fgcolor;
		$strokecolor = $this->strokecolor;
		$width = 0;
		$textitem = '';
		$tmpx = $this->x;
		$lspace = $this->GetStringWidth('  ');
		if ($listtype == '^') {
			// special symbol used for avoid justification of rect bullet
			$this->lispacer = '';
			return;
		} elseif ($listtype == '!') {
			// set default list type for unordered list
			$deftypes = array('disc', 'circle', 'square');
			$listtype = $deftypes[($listdepth - 1) % 3];
		} elseif ($listtype == '#') {
			// set default list type for ordered list
			$listtype = 'decimal';
		} elseif (substr($listtype, 0, 4) == 'img|') {
			// custom image type ('img|type|width|height|image.ext')
			$img = explode('|', $listtype);
			$listtype = 'img';
		}
		switch ($listtype) {
			// unordered types
			case 'none': {
				break;
			}
			case 'disc': {
				$r = $size / 6;
				$lspace += (2 * $r);
				if ($this->rtl) {
					$this->x += $lspace;
				} else {
					$this->x -= $lspace;
				}
				$this->Circle(($this->x + $r), ($this->y + ($this->lasth / 2)), $r, 0, 360, 'F', array(), $color, 8);
				break;
			}
			case 'circle': {
				$r = $size / 6;
				$lspace += (2 * $r);
				if ($this->rtl) {
					$this->x += $lspace;
				} else {
					$this->x -= $lspace;
				}
				$prev_line_style = $this->linestyleWidth.' '.$this->linestyleCap.' '.$this->linestyleJoin.' '.$this->linestyleDash.' '.$this->DrawColor;
				$new_line_style = array('width' => ($r / 3), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'phase' => 0, 'color'=>$color);
				$this->Circle(($this->x + $r), ($this->y + ($this->lasth / 2)), ($r * (1 - (1/6))), 0, 360, 'D', $new_line_style, array(), 8);
				$this->_out($prev_line_style); // restore line settings
				break;
			}
			case 'square': {
				$l = $size / 3;
				$lspace += $l;
				if ($this->rtl) {;
					$this->x += $lspace;
				} else {
					$this->x -= $lspace;
				}
				$this->Rect($this->x, ($this->y + (($this->lasth - $l) / 2)), $l, $l, 'F', array(), $color);
				break;
			}
			case 'img': {
				// 1=>type, 2=>width, 3=>height, 4=>image.ext
				$lspace += $img[2];
				if ($this->rtl) {;
					$this->x += $lspace;
				} else {
					$this->x -= $lspace;
				}
				$imgtype = strtolower($img[1]);
				$prev_y = $this->y;
				switch ($imgtype) {
					case 'svg': {
						$this->ImageSVG($img[4], $this->x, ($this->y + (($this->lasth - $img[3]) / 2)), $img[2], $img[3], '', 'T', '', 0, false);
						break;
					}
					case 'ai':
					case 'eps': {
						$this->ImageEps($img[4], $this->x, ($this->y + (($this->lasth - $img[3]) / 2)), $img[2], $img[3], '', true, 'T', '', 0, false);
						break;
					}
					default: {
						$this->Image($img[4], $this->x, ($this->y + (($this->lasth - $img[3]) / 2)), $img[2], $img[3], $img[1], '', 'T', false, 300, '', false, false, 0, false, false, false);
						break;
					}
				}
				$this->y = $prev_y;
				break;
			}
			// ordered types
			// $this->listcount[$this->listnum];
			// $textitem
			case '1':
			case 'decimal': {
				$textitem = $this->listcount[$this->listnum];
				break;
			}
			case 'decimal-leading-zero': {
				$textitem = sprintf('%02d', $this->listcount[$this->listnum]);
				break;
			}
			case 'i':
			case 'lower-roman': {
				$textitem = strtolower(LIMEPDF_STATIC::intToRoman($this->listcount[$this->listnum]));
				break;
			}
			case 'I':
			case 'upper-roman': {
				$textitem = LIMEPDF_STATIC::intToRoman($this->listcount[$this->listnum]);
				break;
			}
			case 'a':
			case 'lower-alpha':
			case 'lower-latin': {
				$textitem = chr(97 + $this->listcount[$this->listnum] - 1);
				break;
			}
			case 'A':
			case 'upper-alpha':
			case 'upper-latin': {
				$textitem = chr(65 + $this->listcount[$this->listnum] - 1);
				break;
			}
			case 'lower-greek': {
				$textitem = LIMEPDF_FONT::unichr((945 + $this->listcount[$this->listnum] - 1), $this->isunicode);
				break;
			}
			/*
			// Types to be implemented (special handling)
			case 'hebrew': {
				break;
			}
			case 'armenian': {
				break;
			}
			case 'georgian': {
				break;
			}
			case 'cjk-ideographic': {
				break;
			}
			case 'hiragana': {
				break;
			}
			case 'katakana': {
				break;
			}
			case 'hiragana-iroha': {
				break;
			}
			case 'katakana-iroha': {
				break;
			}
			*/
			default: {
				$textitem = $this->listcount[$this->listnum];
			}
		}
		if (!LIMEPDF_STATIC::empty_string($textitem)) {
			// Check whether we need a new page or new column
			$prev_y = $this->y;
			$h = $this->getCellHeight($this->FontSize);
			if ($this->checkPageBreak($h) OR ($this->y < $prev_y)) {
				$tmpx = $this->x;
			}
			// print ordered item
			if ($this->rtl) {
				$textitem = '.'.$textitem;
			} else {
				$textitem = $textitem.'.';
			}
			$lspace += $this->GetStringWidth($textitem);
			if ($this->rtl) {
				$this->x += $lspace;
			} else {
				$this->x -= $lspace;
			}
			$this->Write($this->lasth, $textitem, '', false, '', false, 0, false);
		}
		$this->x = $tmpx;
		$this->lispacer = '^';
		// restore colors
		$this->setFillColorArray($bgcolor);
		$this->setDrawColorArray($strokecolor);
		$this->settextColorArray($color);
	}

	/**
	 * Returns current graphic variables as array.
	 * @return array of graphic variables
	 * @protected
	 * @since 4.2.010 (2008-11-14)
	 */
	protected function getGraphicVars() {
		$grapvars = array(
			'FontFamily' => $this->FontFamily,
			'FontStyle' => $this->FontStyle,
			'FontSizePt' => $this->FontSizePt,
			'rMargin' => $this->rMargin,
			'lMargin' => $this->lMargin,
			'cell_padding' => $this->cell_padding,
			'cell_margin' => $this->cell_margin,
			'LineWidth' => $this->LineWidth,
			'linestyleWidth' => $this->linestyleWidth,
			'linestyleCap' => $this->linestyleCap,
			'linestyleJoin' => $this->linestyleJoin,
			'linestyleDash' => $this->linestyleDash,
			'textrendermode' => $this->textrendermode,
			'textstrokewidth' => $this->textstrokewidth,
			'DrawColor' => $this->DrawColor,
			'FillColor' => $this->FillColor,
			'TextColor' => $this->TextColor,
			'ColorFlag' => $this->ColorFlag,
			'bgcolor' => $this->bgcolor,
			'fgcolor' => $this->fgcolor,
			'htmlvspace' => $this->htmlvspace,
			'listindent' => $this->listindent,
			'listindentlevel' => $this->listindentlevel,
			'listnum' => $this->listnum,
			'listordered' => $this->listordered,
			'listcount' => $this->listcount,
			'lispacer' => $this->lispacer,
			'cell_height_ratio' => $this->cell_height_ratio,
			'font_stretching' => $this->font_stretching,
			'font_spacing' => $this->font_spacing,
			'alpha' => $this->alpha,
			// extended
			'lasth' => $this->lasth,
			'tMargin' => $this->tMargin,
			'bMargin' => $this->bMargin,
			'AutoPageBreak' => $this->AutoPageBreak,
			'PageBreakTrigger' => $this->PageBreakTrigger,
			'x' => $this->x,
			'y' => $this->y,
			'w' => $this->w,
			'h' => $this->h,
			'wPt' => $this->wPt,
			'hPt' => $this->hPt,
			'fwPt' => $this->fwPt,
			'fhPt' => $this->fhPt,
			'page' => $this->page,
			'current_column' => $this->current_column,
			'num_columns' => $this->num_columns
			);
		return $grapvars;
	}

	/**
	 * Set graphic variables.
	 * @param array $gvars array of graphic variablesto restore
	 * @param boolean $extended if true restore extended graphic variables
	 * @protected
	 * @since 4.2.010 (2008-11-14)
	 */
	protected function setGraphicVars($gvars, $extended=false) {
		if ($this->state != 2) {
			 return;
		}
		$this->FontFamily = $gvars['FontFamily'];
		$this->FontStyle = $gvars['FontStyle'];
		$this->FontSizePt = $gvars['FontSizePt'];
		$this->rMargin = $gvars['rMargin'];
		$this->lMargin = $gvars['lMargin'];
		$this->cell_padding = $gvars['cell_padding'];
		$this->cell_margin = $gvars['cell_margin'];
		$this->LineWidth = $gvars['LineWidth'];
		$this->linestyleWidth = $gvars['linestyleWidth'];
		$this->linestyleCap = $gvars['linestyleCap'];
		$this->linestyleJoin = $gvars['linestyleJoin'];
		$this->linestyleDash = $gvars['linestyleDash'];
		$this->textrendermode = $gvars['textrendermode'];
		$this->textstrokewidth = $gvars['textstrokewidth'];
		$this->DrawColor = $gvars['DrawColor'];
		$this->FillColor = $gvars['FillColor'];
		$this->TextColor = $gvars['TextColor'];
		$this->ColorFlag = $gvars['ColorFlag'];
		$this->bgcolor = $gvars['bgcolor'];
		$this->fgcolor = $gvars['fgcolor'];
		$this->htmlvspace = $gvars['htmlvspace'];
		$this->listindent = $gvars['listindent'];
		$this->listindentlevel = $gvars['listindentlevel'];
		$this->listnum = $gvars['listnum'];
		$this->listordered = $gvars['listordered'];
		$this->listcount = $gvars['listcount'];
		$this->lispacer = $gvars['lispacer'];
		$this->cell_height_ratio = $gvars['cell_height_ratio'];
		$this->font_stretching = $gvars['font_stretching'];
		$this->font_spacing = $gvars['font_spacing'];
		$this->alpha = $gvars['alpha'];
		if ($extended) {
			// restore extended values
			$this->lasth = $gvars['lasth'];
			$this->tMargin = $gvars['tMargin'];
			$this->bMargin = $gvars['bMargin'];
			$this->AutoPageBreak = $gvars['AutoPageBreak'];
			$this->PageBreakTrigger = $gvars['PageBreakTrigger'];
			$this->x = $gvars['x'];
			$this->y = $gvars['y'];
			$this->w = $gvars['w'];
			$this->h = $gvars['h'];
			$this->wPt = $gvars['wPt'];
			$this->hPt = $gvars['hPt'];
			$this->fwPt = $gvars['fwPt'];
			$this->fhPt = $gvars['fhPt'];
			$this->page = $gvars['page'];
			$this->current_column = $gvars['current_column'];
			$this->num_columns = $gvars['num_columns'];
		}
		$this->_out(''.$this->linestyleWidth.' '.$this->linestyleCap.' '.$this->linestyleJoin.' '.$this->linestyleDash.' '.$this->DrawColor.' '.$this->FillColor.'');
		if (!LIMEPDF_STATIC::empty_string($this->FontFamily)) {
			$this->setFont($this->FontFamily, $this->FontStyle, $this->FontSizePt);
		}
	}

	/**
	 * Outputs the "save graphics state" operator 'q'
	 * @protected
	 */
	protected function _outSaveGraphicsState() {
		$this->_out('q');
	}

	/**
	 * Outputs the "restore graphics state" operator 'Q'
	 * @protected
	 */
	protected function _outRestoreGraphicsState() {
		$this->_out('Q');
	}

	/**
	 * Set buffer content (always append data).
	 * @param string $data data
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function setBuffer($data) {
		$this->bufferlen += strlen($data);
		$this->buffer .= $data;
	}

	/**
	 * Replace the buffer content
	 * @param string $data data
	 * @protected
	 * @since 5.5.000 (2010-06-22)
	 */
	protected function replaceBuffer($data) {
		$this->bufferlen = strlen($data);
		$this->buffer = $data;
	}

	/**
	 * Get buffer content.
	 * @return string buffer content
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function getBuffer() {
		return $this->buffer;
	}

	/**
	 * Set page buffer content.
	 * @param int $page page number
	 * @param string $data page data
	 * @param boolean $append if true append data, false replace.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function setPageBuffer($page, $data, $append=false) {
		if ($append) {
			$this->pages[$page] .= $data;
		} else {
			$this->pages[$page] = $data;
		}
		if ($append AND isset($this->pagelen[$page])) {
			$this->pagelen[$page] += strlen($data);
		} else {
			$this->pagelen[$page] = strlen($data);
		}
	}

	/**
	 * Get page buffer content.
	 * @param int $page page number
	 * @return string page buffer content or false in case of error
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function getPageBuffer($page) {
		if (isset($this->pages[$page])) {
			return $this->pages[$page];
		}
		return false;
	}

	/**
	 * Set image buffer content.
	 * @param string $image image key
	 * @param array $data image data
	 * @return int image index number
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function setImageBuffer($image, $data) {
		if (($data['i'] = array_search($image, $this->imagekeys)) === FALSE) {
			$this->imagekeys[$this->numimages] = $image;
			$data['i'] = $this->numimages;
			++$this->numimages;
		}
		$this->images[$image] = $data;
		return $data['i'];
	}

	/**
	 * Set image buffer content for a specified sub-key.
	 * @param string $image image key
	 * @param string $key image sub-key
	 * @param array $data image data
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	//protected function setImageSubBuffer($image, $key, $data) {
	public function setImageSubBuffer($image, $key, $data) {
		if (!isset($this->images[$image])) {
			$this->setImageBuffer($image, array());
		}
		$this->images[$image][$key] = $data;
	}

	/**
	 * Get image buffer content.
	 * @param string $image image key
	 * @return string|false image buffer content or false in case of error
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	// protected function getImageBuffer($image) {
	public function getImageBuffer($image) {
		if (isset($this->images[$image])) {
			return $this->images[$image];
		}
		return false;
	}



	/**
	 * Move a page to a previous position.
	 * @param int $frompage number of the source page
	 * @param int $topage number of the destination page (must be less than $frompage)
	 * @return bool true in case of success, false in case of error.
	 * @public
	 * @since 4.5.000 (2009-01-02)
	 */
	public function movePage($frompage, $topage) {
		if (($frompage > $this->numpages) OR ($frompage <= $topage)) {
			return false;
		}
		if ($frompage == $this->page) {
			// close the page before moving it
			$this->endPage();
		}
		// move all page-related states
		$tmppage = $this->getPageBuffer($frompage);
		$tmppagedim = $this->pagedim[$frompage];
		$tmppagelen = $this->pagelen[$frompage];
		$tmpintmrk = $this->intmrk[$frompage];
		$tmpbordermrk = $this->bordermrk[$frompage];
		$tmpcntmrk = $this->cntmrk[$frompage];
		$tmppageobjects = $this->pageobjects[$frompage];
		if (isset($this->footerpos[$frompage])) {
			$tmpfooterpos = $this->footerpos[$frompage];
		}
		if (isset($this->footerlen[$frompage])) {
			$tmpfooterlen = $this->footerlen[$frompage];
		}
		if (isset($this->transfmrk[$frompage])) {
			$tmptransfmrk = $this->transfmrk[$frompage];
		}
		if (isset($this->PageAnnots[$frompage])) {
			$tmpannots = $this->PageAnnots[$frompage];
		}
		if (isset($this->newpagegroup) AND !empty($this->newpagegroup)) {
			for ($i = $frompage; $i > $topage; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $frompage)) {
					--$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
			for ($i = $topage; $i > 0; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $topage)) {
					++$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
		}
		for ($i = $frompage; $i > $topage; --$i) {
			$j = $i - 1;
			// shift pages down
			$this->setPageBuffer($i, $this->getPageBuffer($j));
			$this->pagedim[$i] = $this->pagedim[$j];
			$this->pagelen[$i] = $this->pagelen[$j];
			$this->intmrk[$i] = $this->intmrk[$j];
			$this->bordermrk[$i] = $this->bordermrk[$j];
			$this->cntmrk[$i] = $this->cntmrk[$j];
			$this->pageobjects[$i] = $this->pageobjects[$j];
			if (isset($this->footerpos[$j])) {
				$this->footerpos[$i] = $this->footerpos[$j];
			} elseif (isset($this->footerpos[$i])) {
				unset($this->footerpos[$i]);
			}
			if (isset($this->footerlen[$j])) {
				$this->footerlen[$i] = $this->footerlen[$j];
			} elseif (isset($this->footerlen[$i])) {
				unset($this->footerlen[$i]);
			}
			if (isset($this->transfmrk[$j])) {
				$this->transfmrk[$i] = $this->transfmrk[$j];
			} elseif (isset($this->transfmrk[$i])) {
				unset($this->transfmrk[$i]);
			}
			if (isset($this->PageAnnots[$j])) {
				$this->PageAnnots[$i] = $this->PageAnnots[$j];
			} elseif (isset($this->PageAnnots[$i])) {
				unset($this->PageAnnots[$i]);
			}
			if (isset($this->newpagegroup[$j])) {
				$this->newpagegroup[$i] = $this->newpagegroup[$j];
				unset($this->newpagegroup[$j]);
			}
			if ($this->currpagegroup == $j) {
				$this->currpagegroup = $i;
			}
		}
		$this->setPageBuffer($topage, $tmppage);
		$this->pagedim[$topage] = $tmppagedim;
		$this->pagelen[$topage] = $tmppagelen;
		$this->intmrk[$topage] = $tmpintmrk;
		$this->bordermrk[$topage] = $tmpbordermrk;
		$this->cntmrk[$topage] = $tmpcntmrk;
		$this->pageobjects[$topage] = $tmppageobjects;
		if (isset($tmpfooterpos)) {
			$this->footerpos[$topage] = $tmpfooterpos;
		} elseif (isset($this->footerpos[$topage])) {
			unset($this->footerpos[$topage]);
		}
		if (isset($tmpfooterlen)) {
			$this->footerlen[$topage] = $tmpfooterlen;
		} elseif (isset($this->footerlen[$topage])) {
			unset($this->footerlen[$topage]);
		}
		if (isset($tmptransfmrk)) {
			$this->transfmrk[$topage] = $tmptransfmrk;
		} elseif (isset($this->transfmrk[$topage])) {
			unset($this->transfmrk[$topage]);
		}
		if (isset($tmpannots)) {
			$this->PageAnnots[$topage] = $tmpannots;
		} elseif (isset($this->PageAnnots[$topage])) {
			unset($this->PageAnnots[$topage]);
		}
		// adjust outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if (!$outline['f']) {
				if (($outline['p'] >= $topage) AND ($outline['p'] < $frompage)) {
					$this->outlines[$key]['p'] = ($outline['p'] + 1);
				} elseif ($outline['p'] == $frompage) {
					$this->outlines[$key]['p'] = $topage;
				}
			}
		}
		// adjust dests
		$tmpdests = $this->dests;
		foreach ($tmpdests as $key => $dest) {
			if (!$dest['f']) {
				if (($dest['p'] >= $topage) AND ($dest['p'] < $frompage)) {
					$this->dests[$key]['p'] = ($dest['p'] + 1);
				} elseif ($dest['p'] == $frompage) {
					$this->dests[$key]['p'] = $topage;
				}
			}
		}
		// adjust links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if (!$link['f']) {
				if (($link['p'] >= $topage) AND ($link['p'] < $frompage)) {
					$this->links[$key]['p'] = ($link['p'] + 1);
				} elseif ($link['p'] == $frompage) {
					$this->links[$key]['p'] = $topage;
				}
			}
		}
		// adjust javascript
		$jfrompage = $frompage;
		$jtopage = $topage;
		if (preg_match_all('/this\.addField\(\'([^\']*)\',\'([^\']*)\',([0-9]+)/', $this->javascript, $pamatch) > 0) {
			foreach($pamatch[0] as $pk => $pmatch) {
				$pagenum = intval($pamatch[3][$pk]) + 1;
				if (($pagenum >= $jtopage) AND ($pagenum < $jfrompage)) {
					$newpage = ($pagenum + 1);
				} elseif ($pagenum == $jfrompage) {
					$newpage = $jtopage;
				} else {
					$newpage = $pagenum;
				}
				--$newpage;
				$newjs = "this.addField(\'".$pamatch[1][$pk]."\',\'".$pamatch[2][$pk]."\',".$newpage;
				$this->javascript = str_replace($pmatch, $newjs, $this->javascript);
			}
			unset($pamatch);
		}
		// return to last page
		$this->lastPage(true);
		return true;
	}


	/**
	 * Remove the specified page.
	 * @param int $page page to remove
	 * @return bool true in case of success, false in case of error.
	 * @public
	 * @since 4.6.004 (2009-04-23)
	 */
	public function deletePage($page) {
		if (($page < 1) OR ($page > $this->numpages)) {
			return false;
		}
		// delete current page
		unset($this->pages[$page]);
		unset($this->pagedim[$page]);
		unset($this->pagelen[$page]);
		unset($this->intmrk[$page]);
		unset($this->bordermrk[$page]);
		unset($this->cntmrk[$page]);
		foreach ($this->pageobjects[$page] as $oid) {
			if (isset($this->offsets[$oid])){
				unset($this->offsets[$oid]);
			}
		}
		unset($this->pageobjects[$page]);
		if (isset($this->footerpos[$page])) {
			unset($this->footerpos[$page]);
		}
		if (isset($this->footerlen[$page])) {
			unset($this->footerlen[$page]);
		}
		if (isset($this->transfmrk[$page])) {
			unset($this->transfmrk[$page]);
		}
		if (isset($this->PageAnnots[$page])) {
			unset($this->PageAnnots[$page]);
		}
		if (isset($this->newpagegroup) AND !empty($this->newpagegroup)) {
			for ($i = $page; $i > 0; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $page)) {
					--$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
		}
		if (isset($this->pageopen[$page])) {
			unset($this->pageopen[$page]);
		}
		if ($page < $this->numpages) {
			// update remaining pages
			for ($i = $page; $i < $this->numpages; ++$i) {
				$j = $i + 1;
				// shift pages
				$this->setPageBuffer($i, $this->getPageBuffer($j));
				$this->pagedim[$i] = $this->pagedim[$j];
				$this->pagelen[$i] = $this->pagelen[$j];
				$this->intmrk[$i] = $this->intmrk[$j];
				$this->bordermrk[$i] = $this->bordermrk[$j];
				$this->cntmrk[$i] = $this->cntmrk[$j];
				$this->pageobjects[$i] = $this->pageobjects[$j];
				if (isset($this->footerpos[$j])) {
					$this->footerpos[$i] = $this->footerpos[$j];
				} elseif (isset($this->footerpos[$i])) {
					unset($this->footerpos[$i]);
				}
				if (isset($this->footerlen[$j])) {
					$this->footerlen[$i] = $this->footerlen[$j];
				} elseif (isset($this->footerlen[$i])) {
					unset($this->footerlen[$i]);
				}
				if (isset($this->transfmrk[$j])) {
					$this->transfmrk[$i] = $this->transfmrk[$j];
				} elseif (isset($this->transfmrk[$i])) {
					unset($this->transfmrk[$i]);
				}
				if (isset($this->PageAnnots[$j])) {
					$this->PageAnnots[$i] = $this->PageAnnots[$j];
				} elseif (isset($this->PageAnnots[$i])) {
					unset($this->PageAnnots[$i]);
				}
				if (isset($this->newpagegroup[$j])) {
					$this->newpagegroup[$i] = $this->newpagegroup[$j];
					unset($this->newpagegroup[$j]);
				}
				if ($this->currpagegroup == $j) {
					$this->currpagegroup = $i;
				}
				if (isset($this->pageopen[$j])) {
					$this->pageopen[$i] = $this->pageopen[$j];
				} elseif (isset($this->pageopen[$i])) {
					unset($this->pageopen[$i]);
				}
			}
			// remove last page
			unset($this->pages[$this->numpages]);
			unset($this->pagedim[$this->numpages]);
			unset($this->pagelen[$this->numpages]);
			unset($this->intmrk[$this->numpages]);
			unset($this->bordermrk[$this->numpages]);
			unset($this->cntmrk[$this->numpages]);
			foreach ($this->pageobjects[$this->numpages] as $oid) {
				if (isset($this->offsets[$oid])){
					unset($this->offsets[$oid]);
				}
			}
			unset($this->pageobjects[$this->numpages]);
			if (isset($this->footerpos[$this->numpages])) {
				unset($this->footerpos[$this->numpages]);
			}
			if (isset($this->footerlen[$this->numpages])) {
				unset($this->footerlen[$this->numpages]);
			}
			if (isset($this->transfmrk[$this->numpages])) {
				unset($this->transfmrk[$this->numpages]);
			}
			if (isset($this->PageAnnots[$this->numpages])) {
				unset($this->PageAnnots[$this->numpages]);
			}
			if (isset($this->newpagegroup[$this->numpages])) {
				unset($this->newpagegroup[$this->numpages]);
			}
			if ($this->currpagegroup == $this->numpages) {
				$this->currpagegroup = ($this->numpages - 1);
			}
			if (isset($this->pagegroups[$this->numpages])) {
				unset($this->pagegroups[$this->numpages]);
			}
			if (isset($this->pageopen[$this->numpages])) {
				unset($this->pageopen[$this->numpages]);
			}
		}
		--$this->numpages;
		$this->page = $this->numpages;
		// adjust outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if (!$outline['f']) {
				if ($outline['p'] > $page) {
					$this->outlines[$key]['p'] = $outline['p'] - 1;
				} elseif ($outline['p'] == $page) {
					unset($this->outlines[$key]);
				}
			}
		}
		// adjust dests
		$tmpdests = $this->dests;
		foreach ($tmpdests as $key => $dest) {
			if (!$dest['f']) {
				if ($dest['p'] > $page) {
					$this->dests[$key]['p'] = $dest['p'] - 1;
				} elseif ($dest['p'] == $page) {
					unset($this->dests[$key]);
				}
			}
		}
		// adjust links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if (!$link['f']) {
				if ($link['p'] > $page) {
					$this->links[$key]['p'] = $link['p'] - 1;
				} elseif ($link['p'] == $page) {
					unset($this->links[$key]);
				}
			}
		}
		// adjust javascript
		$jpage = $page;
		if (preg_match_all('/this\.addField\(\'([^\']*)\',\'([^\']*)\',([0-9]+)/', $this->javascript, $pamatch) > 0) {
			foreach($pamatch[0] as $pk => $pmatch) {
				$pagenum = intval($pamatch[3][$pk]) + 1;
				if ($pagenum >= $jpage) {
					$newpage = ($pagenum - 1);
				} elseif ($pagenum == $jpage) {
					$newpage = 1;
				} else {
					$newpage = $pagenum;
				}
				--$newpage;
				$newjs = "this.addField(\'".$pamatch[1][$pk]."\',\'".$pamatch[2][$pk]."\',".$newpage;
				$this->javascript = str_replace($pmatch, $newjs, $this->javascript);
			}
			unset($pamatch);
		}
		// return to last page
		if ($this->numpages > 0) {
			$this->lastPage(true);
		}
		return true;
	}

	/**
	 * Clone the specified page to a new page.
	 * @param int $page number of page to copy (0 = current page)
	 * @return bool true in case of success, false in case of error.
	 * @public
	 * @since 4.9.015 (2010-04-20)
	 */
	public function copyPage($page=0) {
		if ($page == 0) {
			// default value
			$page = $this->page;
		}
		if (($page < 1) OR ($page > $this->numpages)) {
			return false;
		}
		// close the last page
		$this->endPage();
		// copy all page-related states
		++$this->numpages;
		$this->page = $this->numpages;
		$this->setPageBuffer($this->page, $this->getPageBuffer($page));
		$this->pagedim[$this->page] = $this->pagedim[$page];
		$this->pagelen[$this->page] = $this->pagelen[$page];
		$this->intmrk[$this->page] = $this->intmrk[$page];
		$this->bordermrk[$this->page] = $this->bordermrk[$page];
		$this->cntmrk[$this->page] = $this->cntmrk[$page];
		$this->pageobjects[$this->page] = $this->pageobjects[$page];
		$this->pageopen[$this->page] = false;
		if (isset($this->footerpos[$page])) {
			$this->footerpos[$this->page] = $this->footerpos[$page];
		}
		if (isset($this->footerlen[$page])) {
			$this->footerlen[$this->page] = $this->footerlen[$page];
		}
		if (isset($this->transfmrk[$page])) {
			$this->transfmrk[$this->page] = $this->transfmrk[$page];
		}
		if (isset($this->PageAnnots[$page])) {
			$this->PageAnnots[$this->page] = $this->PageAnnots[$page];
		}
		if (isset($this->newpagegroup[$page])) {
			// start a new group
			$this->newpagegroup[$this->page] = sizeof($this->newpagegroup) + 1;
			$this->currpagegroup = $this->newpagegroup[$this->page];
			$this->pagegroups[$this->currpagegroup] = 1;
		} elseif (isset($this->currpagegroup) AND ($this->currpagegroup > 0)) {
			++$this->pagegroups[$this->currpagegroup];
		}
		// copy outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if ($outline['p'] == $page) {
				$this->outlines[] = array('t' => $outline['t'], 'l' => $outline['l'], 'x' => $outline['x'], 'y' => $outline['y'], 'p' => $this->page, 'f' => $outline['f'], 's' => $outline['s'], 'c' => $outline['c']);
			}
		}
		// copy links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if ($link['p'] == $page) {
				$this->links[] = array('p' => $this->page, 'y' => $link['y'], 'f' => $link['f']);
			}
		}
		// return to last page
		$this->lastPage(true);
		return true;
	}

	/**
	 * Output a Table of Content Index (TOC).
	 * This method must be called after all Bookmarks were set.
	 * Before calling this method you have to open the page using the addTOCPage() method.
	 * After calling this method you have to call endTOCPage() to close the TOC page.
	 * You can override this method to achieve different styles.
	 * @param int|null $page page number where this TOC should be inserted (leave empty for current page).
	 * @param string $numbersfont set the font for page numbers (please use monospaced font for better alignment).
	 * @param string $filler string used to fill the space between text and page number.
	 * @param string $toc_name name to use for TOC bookmark.
	 * @param string $style Font style for title: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param array $color RGB color array for bookmark title (values from 0 to 255).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.5.000 (2009-01-02)
	 * @see addTOCPage(), endTOCPage(), addHTMLTOC()
	 */
	public function addTOC($page=null, $numbersfont='', $filler='.', $toc_name='TOC', $style='', $color=array(0,0,0)) {
		$fontsize = $this->FontSizePt;
		$fontfamily = $this->FontFamily;
		$fontstyle = $this->FontStyle;
		$w = $this->w - $this->lMargin - $this->rMargin;
		$spacer = $this->GetStringWidth(chr(32)) * 4;
		$lmargin = $this->lMargin;
		$rmargin = $this->rMargin;
		$x_start = $this->GetX();
		$page_first = $this->page;
		$current_page = $this->page;
		$page_fill_start = false;
		$page_fill_end = false;
		$current_column = $this->current_column;
		if (LIMEPDF_STATIC::empty_string($numbersfont)) {
			$numbersfont = $this->default_monospaced_font;
		}
		if (LIMEPDF_STATIC::empty_string($filler)) {
			$filler = ' ';
		}
		if (LIMEPDF_STATIC::empty_string($page)) {
			$gap = ' ';
		} else {
			$gap = '';
			if ($page < 1) {
				$page = 1;
			}
		}
		$this->setFont($numbersfont, $fontstyle, $fontsize);
		$numwidth = $this->GetStringWidth('00000');
		$maxpage = 0; //used for pages on attached documents
		foreach ($this->outlines as $key => $outline) {
			// check for extra pages (used for attachments)
			if (($this->page > $page_first) AND ($outline['p'] >= $this->numpages)) {
				$outline['p'] += ($this->page - $page_first);
			}
			if ($this->rtl) {
				$aligntext = 'R';
				$alignnum = 'L';
			} else {
				$aligntext = 'L';
				$alignnum = 'R';
			}
			if ($outline['l'] == 0) {
				$this->setFont($fontfamily, $outline['s'].'B', $fontsize);
			} else {
				$this->setFont($fontfamily, $outline['s'], $fontsize - $outline['l']);
			}
			$this->setTextColorArray($outline['c']);
			// check for page break
			$this->checkPageBreak(2 * $this->getCellHeight($this->FontSize));
			// set margins and X position
			if (($this->page == $current_page) AND ($this->current_column == $current_column)) {
				$this->lMargin = $lmargin;
				$this->rMargin = $rmargin;
			} else {
				if ($this->current_column != $current_column) {
					if ($this->rtl) {
						$x_start = $this->w - $this->columns[$this->current_column]['x'];
					} else {
						$x_start = $this->columns[$this->current_column]['x'];
					}
				}
				$lmargin = $this->lMargin;
				$rmargin = $this->rMargin;
				$current_page = $this->page;
				$current_column = $this->current_column;
			}
			$this->setX($x_start);
			$indent = ($spacer * $outline['l']);
			if ($this->rtl) {
				$this->x -= $indent;
				$this->rMargin = $this->w - $this->x;
			} else {
				$this->x += $indent;
				$this->lMargin = $this->x;
			}
			$link = $this->AddLink();
			$this->setLink($link, $outline['y'], $outline['p']);
			// write the text
			if ($this->rtl) {
				$txt = ' '.$outline['t'];
			} else {
				$txt = $outline['t'].' ';
			}
			$this->Write(0, $txt, $link, false, $aligntext, false, 0, false, false, 0, $numwidth, '');
			if ($this->rtl) {
				$tw = $this->x - $this->lMargin;
			} else {
				$tw = $this->w - $this->rMargin - $this->x;
			}
			$this->setFont($numbersfont, $fontstyle, $fontsize);
			if (LIMEPDF_STATIC::empty_string($page)) {
				$pagenum = $outline['p'];
			} else {
				// placemark to be replaced with the correct number
				$pagenum = '{#'.($outline['p']).'}';
				if ($this->isUnicodeFont()) {
					$pagenum = '{'.$pagenum.'}';
				}
				$maxpage = max($maxpage, $outline['p']);
			}
			$fw = ($tw - $this->GetStringWidth($pagenum.$filler));
			$wfiller = $this->GetStringWidth($filler);
			if ($wfiller > 0) {
				$numfills = floor($fw / $wfiller);
			} else {
				$numfills = 0;
			}
			if ($numfills > 0) {
				$rowfill = str_repeat($filler, $numfills);
			} else {
				$rowfill = '';
			}
			if ($this->rtl) {
				$pagenum = $pagenum.$gap.$rowfill;
			} else {
				$pagenum = $rowfill.$gap.$pagenum;
			}
			// write the number
			$this->Cell($tw, 0, $pagenum, 0, 1, $alignnum, 0, $link, 0);
		}
		$page_last = $this->getPage();
		$numpages = ($page_last - $page_first + 1);
		// account for booklet mode
		if ($this->booklet) {
			// check if a blank page is required before TOC
			$page_fill_start = ((($page_first % 2) == 0) XOR (($page % 2) == 0));
			$page_fill_end = (!((($numpages % 2) == 0) XOR ($page_fill_start)));
			if ($page_fill_start) {
				// add a page at the end (to be moved before TOC)
				$this->addPage();
				++$page_last;
				++$numpages;
			}
			if ($page_fill_end) {
				// add a page at the end
				$this->addPage();
				++$page_last;
				++$numpages;
			}
		}
		$maxpage = max($maxpage, $page_last);
		if (!LIMEPDF_STATIC::empty_string($page)) {
			for ($p = $page_first; $p <= $page_last; ++$p) {
				// get page data
				$temppage = $this->getPageBuffer($p);
				for ($n = 1; $n <= $maxpage; ++$n) {
					// update page numbers
					$a = '{#'.$n.'}';
					// get page number aliases
					$pnalias = $this->getInternalPageNumberAliases($a);
					// calculate replacement number
					if (($n >= $page) AND ($n <= $this->numpages)) {
						$np = $n + $numpages;
					} else {
						$np = $n;
					}
					$na = LIMEPDF_STATIC::formatTOCPageNumber(($this->starting_page_number + $np - 1));
					$nu = LIMEPDF_FONT::UTF8ToUTF16BE($na, false, $this->isunicode, $this->CurrentFont);
					// replace aliases with numbers
					foreach ($pnalias['u'] as $u) {
						$sfill = str_repeat($filler, max(0, (strlen($u) - strlen($nu.' '))));
						if ($this->rtl) {
							$nr = $nu.LIMEPDF_FONT::UTF8ToUTF16BE(' '.$sfill, false, $this->isunicode, $this->CurrentFont);
						} else {
							$nr = LIMEPDF_FONT::UTF8ToUTF16BE($sfill.' ', false, $this->isunicode, $this->CurrentFont).$nu;
						}
						$temppage = str_replace($u, $nr, $temppage);
					}
					foreach ($pnalias['a'] as $a) {
						$sfill = str_repeat($filler, max(0, (strlen($a) - strlen($na.' '))));
						if ($this->rtl) {
							$nr = $na.' '.$sfill;
						} else {
							$nr = $sfill.' '.$na;
						}
						$temppage = str_replace($a, $nr, $temppage);
					}
				}
				// save changes
				$this->setPageBuffer($p, $temppage);
			}
			// move pages
			$this->Bookmark($toc_name, 0, 0, $page_first, $style, $color);
			if ($page_fill_start) {
				$this->movePage($page_last, $page_first);
			}
			for ($i = 0; $i < $numpages; ++$i) {
				$this->movePage($page_last, $page);
			}
		}
	}

	/**
	 * Output a Table Of Content Index (TOC) using HTML templates.
	 * This method must be called after all Bookmarks were set.
	 * Before calling this method you have to open the page using the addTOCPage() method.
	 * After calling this method you have to call endTOCPage() to close the TOC page.
	 * @param int|null $page page number where this TOC should be inserted (leave empty for current page).
	 * @param string $toc_name name to use for TOC bookmark.
	 * @param array $templates array of html templates. Use: "#TOC_DESCRIPTION#" for bookmark title, "#TOC_PAGE_NUMBER#" for page number.
	 * @param boolean $correct_align if true correct the number alignment (numbers must be in monospaced font like courier and right aligned on LTR, or left aligned on RTL)
	 * @param string $style Font style for title: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param array $color RGB color array for title (values from 0 to 255).
	 * @public
	 * @author Nicola Asuni
	 * @since 5.0.001 (2010-05-06)
	 * @see addTOCPage(), endTOCPage(), addTOC()
	 */
	public function addHTMLTOC($page=null, $toc_name='TOC', $templates=array(), $correct_align=true, $style='', $color=array(0,0,0)) {
		$filler = ' ';
		$prev_htmlLinkColorArray = $this->htmlLinkColorArray;
		$prev_htmlLinkFontStyle = $this->htmlLinkFontStyle;
		// set new style for link
		$this->htmlLinkColorArray = array();
		$this->htmlLinkFontStyle = '';
		$page_first = $this->getPage();
		$page_fill_start = false;
		$page_fill_end = false;
		// get the font type used for numbers in each template
		$current_font = $this->FontFamily;
		foreach ($templates as $level => $html) {
			$dom = $this->getHtmlDomArray($html);
			foreach ($dom as $key => $value) {
				if ($value['value'] == '#TOC_PAGE_NUMBER#') {
					$this->setFont($dom[($key - 1)]['fontname']);
					$templates['F'.$level] = $this->isUnicodeFont();
				}
			}
		}
		$this->setFont($current_font);
		$maxpage = 0; //used for pages on attached documents
		foreach ($this->outlines as $key => $outline) {
			// get HTML template
			$row = $templates[$outline['l']];
			if (LIMEPDF_STATIC::empty_string($page)) {
				$pagenum = $outline['p'];
			} else {
				// placemark to be replaced with the correct number
				$pagenum = '{#'.($outline['p']).'}';
				if (isset($templates['F'.$outline['l']]) && $templates['F'.$outline['l']]) {
					$pagenum = '{'.$pagenum.'}';
				}
				$maxpage = max($maxpage, $outline['p']);
			}
			// replace templates with current values
			$row = str_replace('#TOC_DESCRIPTION#', $outline['t'], $row);
			$row = str_replace('#TOC_PAGE_NUMBER#', $pagenum, $row);
			// add link to page
			$row = '<a href="#'.$outline['p'].','.$outline['y'].'">'.$row.'</a>';
			// write bookmark entry
			$this->writeHTML($row, false, false, true, false, '');
		}
		// restore link styles
		$this->htmlLinkColorArray = $prev_htmlLinkColorArray;
		$this->htmlLinkFontStyle = $prev_htmlLinkFontStyle;
		// move TOC page and replace numbers
		$page_last = $this->getPage();
		$numpages = ($page_last - $page_first + 1);
		// account for booklet mode
		if ($this->booklet) {
			// check if a blank page is required before TOC
			$page_fill_start = ((($page_first % 2) == 0) XOR (($page % 2) == 0));
			$page_fill_end = (!((($numpages % 2) == 0) XOR ($page_fill_start)));
			if ($page_fill_start) {
				// add a page at the end (to be moved before TOC)
				$this->addPage();
				++$page_last;
				++$numpages;
			}
			if ($page_fill_end) {
				// add a page at the end
				$this->addPage();
				++$page_last;
				++$numpages;
			}
		}
		$maxpage = max($maxpage, $page_last);
		if (!LIMEPDF_STATIC::empty_string($page)) {
			for ($p = $page_first; $p <= $page_last; ++$p) {
				// get page data
				$temppage = $this->getPageBuffer($p);
				for ($n = 1; $n <= $maxpage; ++$n) {
					// update page numbers
					$a = '{#'.$n.'}';
					// get page number aliases
					$pnalias = $this->getInternalPageNumberAliases($a);
					// calculate replacement number
					if ($n >= $page) {
						$np = $n + $numpages;
					} else {
						$np = $n;
					}
					$na = LIMEPDF_STATIC::formatTOCPageNumber(($this->starting_page_number + $np - 1));
					$nu = LIMEPDF_FONT::UTF8ToUTF16BE($na, false, $this->isunicode, $this->CurrentFont);
					// replace aliases with numbers
					foreach ($pnalias['u'] as $u) {
						if ($correct_align) {
							$sfill = str_repeat($filler, (strlen($u) - strlen($nu.' ')));
							if ($this->rtl) {
								$nr = $nu.LIMEPDF_FONT::UTF8ToUTF16BE(' '.$sfill, false, $this->isunicode, $this->CurrentFont);
							} else {
								$nr = LIMEPDF_FONT::UTF8ToUTF16BE($sfill.' ', false, $this->isunicode, $this->CurrentFont).$nu;
							}
						} else {
							$nr = $nu;
						}
						$temppage = str_replace($u, $nr, $temppage);
					}
					foreach ($pnalias['a'] as $a) {
						if ($correct_align) {
							$sfill = str_repeat($filler, (strlen($a) - strlen($na.' ')));
							if ($this->rtl) {
								$nr = $na.' '.$sfill;
							} else {
								$nr = $sfill.' '.$na;
							}
						} else {
							$nr = $na;
						}
						$temppage = str_replace($a, $nr, $temppage);
					}
				}
				// save changes
				$this->setPageBuffer($p, $temppage);
			}
			// move pages
			$this->Bookmark($toc_name, 0, 0, $page_first, $style, $color);
			if ($page_fill_start) {
				$this->movePage($page_last, $page_first);
			}
			for ($i = 0; $i < $numpages; ++$i) {
				$this->movePage($page_last, $page);
			}
		}
	}

	/**
	 * Stores a copy of the current TCPDF object used for undo operation.
	 * @public
	 * @since 4.5.029 (2009-03-19)
	 */
	public function startTransaction() {
		if (isset($this->objcopy)) {
			// remove previous copy
			$this->commitTransaction();
		}
		// record current page number and Y position
		$this->start_transaction_page = $this->page;
		$this->start_transaction_y = $this->y;
		// clone current object
		$this->objcopy = LIMEPDF_STATIC::objclone($this);
	}

	/**
	 * Delete the copy of the current TCPDF object used for undo operation.
	 * @public
	 * @since 4.5.029 (2009-03-19)
	 */
	public function commitTransaction() {
		if (isset($this->objcopy)) {
			$this->objcopy->_destroy(true, true);
			/* The unique file_id should not be used during cleanup again */
			$this->objcopy->file_id = NULL;
			unset($this->objcopy);
		}
	}

	/**
	 * This method allows to undo the latest transaction by returning the latest saved TCPDF object with startTransaction().
	 * @param boolean $self if true restores current class object to previous state without the need of reassignment via the returned value.
	 * @return TCPDF object.
	 * @public
	 * @since 4.5.029 (2009-03-19)
	 */
	public function rollbackTransaction($self=false) {
		if (!isset($this->objcopy)) {
			return $this;
		}
		$file_id = $this->file_id;
		$objcopy = $this->objcopy;
		$this->_destroy(true, true);
		if ($self) {
			$objvars = get_object_vars($objcopy);
			foreach ($objvars as $key => $value) {
				$this->$key = $value;
			}
			$objcopy->_destroy(true, true);
			unset($objcopy);
			return $this;
		}
		$this->file_id = $file_id;
		return $objcopy;
	}


	/**
	 * Set parameters for drop shadow effect for text.
	 * @param array $params Array of parameters: enabled (boolean) set to true to enable shadow; depth_w (float) shadow width in user units; depth_h (float) shadow height in user units; color (array) shadow color or false to use the stroke color; opacity (float) Alpha value: real value from 0 (transparent) to 1 (opaque); blend_mode (string) blend mode, one of the following: Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn, HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity.
	 * @since 5.9.174 (2012-07-25)
	 * @public
	*/
	public function setTextShadow($params=array('enabled'=>false, 'depth_w'=>0, 'depth_h'=>0, 'color'=>false, 'opacity'=>1, 'blend_mode'=>'Normal')) {
		if (isset($params['enabled'])) {
			$this->txtshadow['enabled'] = $params['enabled']?true:false;
		} else {
			$this->txtshadow['enabled'] = false;
		}
		if (isset($params['depth_w'])) {
			$this->txtshadow['depth_w'] = floatval($params['depth_w']);
		} else {
			$this->txtshadow['depth_w'] = 0;
		}
		if (isset($params['depth_h'])) {
			$this->txtshadow['depth_h'] = floatval($params['depth_h']);
		} else {
			$this->txtshadow['depth_h'] = 0;
		}
		if (isset($params['color']) AND ($params['color'] !== false) AND is_array($params['color'])) {
			$this->txtshadow['color'] = $params['color'];
		} else {
			$this->txtshadow['color'] = $this->strokecolor;
		}
		if (isset($params['opacity'])) {
			$this->txtshadow['opacity'] = min(1, max(0, floatval($params['opacity'])));
		} else {
			$this->txtshadow['opacity'] = 1;
		}
		if (isset($params['blend_mode']) AND in_array($params['blend_mode'], array('Normal', 'Multiply', 'Screen', 'Overlay', 'Darken', 'Lighten', 'ColorDodge', 'ColorBurn', 'HardLight', 'SoftLight', 'Difference', 'Exclusion', 'Hue', 'Saturation', 'Color', 'Luminosity'))) {
			$this->txtshadow['blend_mode'] = $params['blend_mode'];
		} else {
			$this->txtshadow['blend_mode'] = 'Normal';
		}
		if ((($this->txtshadow['depth_w'] == 0) AND ($this->txtshadow['depth_h'] == 0)) OR ($this->txtshadow['opacity'] == 0)) {
			$this->txtshadow['enabled'] = false;
		}
	}

	/**
	 * Return the text shadow parameters array.
	 * @return array array of parameters.
	 * @since 5.9.174 (2012-07-25)
	 * @public
	 */
	public function getTextShadow() {
		return $this->txtshadow;
	}

	/**
	 * Returns an array of chars containing soft hyphens.
	 * @param array $word array of chars
	 * @param array $patterns Array of hypenation patterns.
	 * @param array $dictionary Array of words to be returned without applying the hyphenation algorithm.
	 * @param int $leftmin Minimum number of character to leave on the left of the word without applying the hyphens.
	 * @param int $rightmin Minimum number of character to leave on the right of the word without applying the hyphens.
	 * @param int $charmin Minimum word length to apply the hyphenation algorithm.
	 * @param int $charmax Maximum length of broken piece of word.
	 * @return array text with soft hyphens
	 * @author Nicola Asuni
	 * @since 4.9.012 (2010-04-12)
	 * @protected
	 */
	protected function hyphenateWord($word, $patterns, $dictionary=array(), $leftmin=1, $rightmin=2, $charmin=1, $charmax=8) {
		$hyphenword = array(); // hyphens positions
		$numchars = count($word);
		if ($numchars <= $charmin) {
			return $word;
		}
		$word_string = LIMEPDF_FONT::UTF8ArrSubString($word, '', '', $this->isunicode);
		// some words will be returned as-is
		$pattern = '/^([a-zA-Z0-9_\.\-]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/';
		if (preg_match($pattern, $word_string) > 0) {
			// email
			return $word;
		}
		$pattern = '/(([a-zA-Z0-9\-]+\.)?)((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/';
		if (preg_match($pattern, $word_string) > 0) {
			// URL
			return $word;
		}
		if (isset($dictionary[$word_string])) {
			return LIMEPDF_FONT::UTF8StringToArray($dictionary[$word_string], $this->isunicode, $this->CurrentFont);
		}
		// surround word with '_' characters
		$tmpword = array_merge(array(46), $word, array(46));
		$tmpnumchars = $numchars + 2;
		$maxpos = $tmpnumchars - 1;
		for ($pos = 0; $pos < $maxpos; ++$pos) {
			$imax = min(($tmpnumchars - $pos), $charmax);
			for ($i = 1; $i <= $imax; ++$i) {
				$subword = strtolower(LIMEPDF_FONT::UTF8ArrSubString($tmpword, $pos, ($pos + $i), $this->isunicode));
				if (isset($patterns[$subword])) {
					$pattern = LIMEPDF_FONT::UTF8StringToArray($patterns[$subword], $this->isunicode, $this->CurrentFont);
					$pattern_length = count($pattern);
					$digits = 1;
					for ($j = 0; $j < $pattern_length; ++$j) {
						// check if $pattern[$j] is a number = hyphenation level (only numbers from 1 to 5 are valid)
						if (($pattern[$j] >= 48) AND ($pattern[$j] <= 57)) {
							if ($j == 0) {
								$zero = $pos - 1;
							} else {
								$zero = $pos + $j - $digits;
							}
							// get hyphenation level
							$level = ($pattern[$j] - 48);
							// if two levels from two different patterns match at the same point, the higher one is selected.
							if (!isset($hyphenword[$zero]) OR ($hyphenword[$zero] < $level)) {
								$hyphenword[$zero] = $level;
							}
							++$digits;
						}
					}
				}
			}
		}
		$inserted = 0;
		$maxpos = $numchars - $rightmin;
		for ($i = $leftmin; $i <= $maxpos; ++$i) {
			// only odd levels indicate allowed hyphenation points
			if (isset($hyphenword[$i]) AND (($hyphenword[$i] % 2) != 0)) {
				// 173 = soft hyphen character
				array_splice($word, $i + $inserted, 0, 173);
				++$inserted;
			}
		}
		return $word;
	}

	/**
	 * Returns text with soft hyphens.
	 * @param string $text text to process
	 * @param mixed $patterns Array of hypenation patterns or a TEX file containing hypenation patterns. TEX patterns can be downloaded from http://www.ctan.org/tex-archive/language/hyph-utf8/tex/generic/hyph-utf8/patterns/
	 * @param array $dictionary Array of words to be returned without applying the hyphenation algorithm.
	 * @param int $leftmin Minimum number of character to leave on the left of the word without applying the hyphens.
	 * @param int $rightmin Minimum number of character to leave on the right of the word without applying the hyphens.
	 * @param int $charmin Minimum word length to apply the hyphenation algorithm.
	 * @param int $charmax Maximum length of broken piece of word.
	 * @return string text with soft hyphens
	 * @author Nicola Asuni
	 * @since 4.9.012 (2010-04-12)
	 * @public
	 */
	public function hyphenateText($text, $patterns, $dictionary=array(), $leftmin=1, $rightmin=2, $charmin=1, $charmax=8) {
		$text = $this->unhtmlentities($text);
		$word = array(); // last word
		$txtarr = array(); // text to be returned
		$intag = false; // true if we are inside an HTML tag
		$skip = false; // true to skip hyphenation
		if (!is_array($patterns)) {
			$patterns = LIMEPDF_STATIC::getHyphenPatternsFromTEX($patterns);
		}
		// get array of characters
		$unichars = LIMEPDF_FONT::UTF8StringToArray($text, $this->isunicode, $this->CurrentFont);
		// for each char
		foreach ($unichars as $char) {
			if ((!$intag) AND (!$skip) AND LIMEPDF_FONT_DATA::$uni_type[$char] == 'L') {
				// letter character
				$word[] = $char;
			} else {
				// other type of character
				if (!LIMEPDF_STATIC::empty_string($word)) {
					// hypenate the word
					$txtarr = array_merge($txtarr, $this->hyphenateWord($word, $patterns, $dictionary, $leftmin, $rightmin, $charmin, $charmax));
					$word = array();
				}
				$txtarr[] = $char;
				if (chr($char) == '<') {
					// we are inside an HTML tag
					$intag = true;
				} elseif ($intag AND (chr($char) == '>')) {
					// end of HTML tag
					$intag = false;
					// check for style tag
					$expected = array(115, 116, 121, 108, 101); // = 'style'
					$current = array_slice($txtarr, -6, 5); // last 5 chars
					$compare = array_diff($expected, $current);
					if (empty($compare)) {
						// check if it is a closing tag
						$expected = array(47); // = '/'
						$current = array_slice($txtarr, -7, 1);
						$compare = array_diff($expected, $current);
						if (empty($compare)) {
							// closing style tag
							$skip = false;
						} else {
							// opening style tag
							$skip = true;
						}
					}
				}
			}
		}
		if (!LIMEPDF_STATIC::empty_string($word)) {
			// hypenate the word
			$txtarr = array_merge($txtarr, $this->hyphenateWord($word, $patterns, $dictionary, $leftmin, $rightmin, $charmin, $charmax));
		}
		// convert char array to string and return
		return LIMEPDF_FONT::UTF8ArrSubString($txtarr, '', '', $this->isunicode);
	}

	/**
	 * Enable/disable rasterization of vector images using ImageMagick library.
	 * @param boolean $mode if true enable rasterization, false otherwise.
	 * @public
	 * @since 5.0.000 (2010-04-27)
	 */
	public function setRasterizeVectorImages($mode) {
		$this->rasterize_vector_images = $mode;
	}


	/**
	 * Left trim the input string
	 * @param string $str string to trim
	 * @param string $replace string that replace spaces.
	 * @return string left trimmed string
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.000 (2010-08-11)
	 */
	public function stringLeftTrim($str, $replace='') {
		return preg_replace('/^'.$this->re_space['p'].'+/'.$this->re_space['m'], $replace, $str);
	}

	/**
	 * Right trim the input string
	 * @param string $str string to trim
	 * @param string $replace string that replace spaces.
	 * @return string right trimmed string
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.000 (2010-08-11)
	 */
	public function stringRightTrim($str, $replace='') {
		return preg_replace('/'.$this->re_space['p'].'+$/'.$this->re_space['m'], $replace, $str);
	}

	/**
	 * Trim the input string
	 * @param string $str string to trim
	 * @param string $replace string that replace spaces.
	 * @return string trimmed string
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.000 (2010-08-11)
	 */
	public function stringTrim($str, $replace='') {
		$str = $this->stringLeftTrim($str, $replace);
		$str = $this->stringRightTrim($str, $replace);
		return $str;
	}



} // END OF TCPDF CLASS

//============================================================+
// END OF FILE
//============================================================+
