<?php

declare(strict_types=1);

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
//require_once(dirname(__FILE__).'/src//include/limePDF_Vars.php');
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

/**
 * Custom TCPDF Exception class
 */
class TCPDFException extends \Exception {}

class TCPDF 
{
	// Constants for character codes
	private const NEWLINE = 10;
	private const NON_BREAKING_SPACE = 160;
	private const SOFT_HYPHEN = 173;
	private const HYPHEN = 45;
	
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
	//use LIMEPDF_VARS;
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

	/**
	 * TCPDF constructor
	 * 
	 * @param string $orientation Page orientation (P=Portrait, L=Landscape)
	 * @param string $unit Unit of measure (mm, cm, in, pt)
	 * @param string|array $format Page format
	 * @param bool $unicode Enable unicode support
	 * @param string $encoding Character encoding
	 * @param bool $diskcache Enable disk caching
	 * @param bool|int $pdfa PDF/A mode (false, 1, or 3)
	 */
	public function __construct(
		string $orientation = 'P', 
		string $unit = 'mm', 
		$format = 'A4', 
		bool $unicode = true, 
		string $encoding = 'UTF-8', 
		bool $diskcache = false, 
		$pdfa = false
	) {
		$this->initializeFileIds($orientation, $unit, $format, $encoding);
		$this->initializePdfaMode($pdfa);
		$this->initializeRtlSettings();
		$this->performSystemChecks();
		$this->initializeBasicProperties($unicode);
		$this->initializeFontSettings();
		$this->initializeColorSettings();
		$this->initializeEncryptionSettings();
		$this->initializeCorefonts();
		$this->initializePageSettings($unit, $format, $orientation);
		$this->initializeMarginSettings();
		$this->initializeDrawingSettings();
		$this->initializeDisplaySettings();
		$this->initializeCompressionSettings();
		$this->initializeVersionSettings();
		$this->initializeLinkSettings();
		$this->initializeColorProperties();
		$this->initializeSignatureSettings();
		$this->initializeUserRightsSettings();
		$this->initializeImageSettings();
		$this->initializeFontProcessing();
		$this->initializeRegexSettings();
		$this->initializeFormSettings();
		$this->initializeTimestampSettings();
		$this->initializeGraphicSettings();
		$this->initializeHeaderSettings();
	}

	/**
	 * Default destructor.
	 */
	public function __destruct() 
	{
		$this->_destroy(true);
	}

	/**
	 * Reset the last cell height.
	 */
	public function resetLastH(): void 
	{
		$this->lasth = $this->getCellHeight($this->FontSize);
	}

	/**
	 * Throw an exception or print an error message and die
	 * 
	 * @param string $msg The error message
	 * @throws TCPDFException
	 */
	public function Error(string $msg): void 
	{
		// unset all class variables
		$this->_destroy(true);
		$msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
		
		if ($this->shouldThrowExceptions()) {
			throw new TCPDFException('TCPDF ERROR: ' . $msg);
		}
		
		$this->outputErrorAndDie($msg);
	}

	/**
	 * This method begins the generation of the PDF document.
	 * It is not necessary to call it explicitly because AddPage() does it automatically.
	 * Note: no page is created by this method
	 */
	public function Open(): void 
	{
		$this->state = 1;
	}

	/**
	 * Terminates the PDF document.
	 * It is not necessary to call this method explicitly because Output() does it automatically.
	 * If the document contains no page, AddPage() is called to prevent from getting an invalid document.
	 */
	public function Close(): void 
	{
		if ($this->state == 3) {
			return;
		}
		
		if ($this->page == 0) {
			$this->AddPage();
		}
		
		$this->endLayer();
		
		if ($this->tcpdflink) {
			$this->addTcpdfLink();
		}
		
		// close page
		$this->endPage();
		// close document
		$this->_enddoc();
		// unset all class variables (except critical ones)
		$this->_destroy(false);
	}

	/**
	 * Return true if the character is present in the specified font.
	 * 
	 * @param int|string $char Character to check (integer value or string)
	 * @param string $font Font name (family name)
	 * @param string $style Font style
	 * @return bool true if the char is defined, false otherwise
	 */
	public function isCharDefined($char, string $font = '', string $style = ''): bool 
	{
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
	 * 
	 * @param string $text Text to process
	 * @param string $font Font name (family name)
	 * @param string $style Font style
	 * @param array $subs Array of possible character substitutions
	 * @return string Processed text
	 */
	public function replaceMissingChars(string $text, string $font = '', string $style = '', array $subs = []): string 
	{
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
	 * Prints a text cell at the specified position.
	 * 
	 * @param float $x Abscissa of the cell origin
	 * @param float $y Ordinate of the cell origin
	 * @param string $txt String to print
	 * @param int $fstroke outline size in user units (0 = disable)
	 * @param bool $fclip if true activate clipping mode
	 * @param bool $ffill if true fills the text
	 * @param mixed $border Indicates if borders must be drawn around the cell
	 * @param int $ln Indicates where the current position should go after the call
	 * @param string $align Allows to center or align the text
	 * @param bool $fill Indicates if the cell background must be painted
	 * @param mixed $link URL or identifier returned by AddLink()
	 * @param int $stretch font stretch mode
	 * @param bool $ignore_min_height if true ignore automatic minimum height value
	 * @param string $calign cell vertical alignment relative to the specified Y value
	 * @param string $valign text vertical alignment inside the cell
	 * @param bool $rtloff if true uses the page top-left corner as origin of axis
	 */
	public function Text(
		float $x, 
		float $y, 
		string $txt, 
		int $fstroke = 0, 
		bool $fclip = false, 
		bool $ffill = true, 
		$border = 0, 
		int $ln = 0, 
		string $align = '', 
		bool $fill = false, 
		$link = '', 
		int $stretch = 0, 
		bool $ignore_min_height = false, 
		string $calign = 'T', 
		string $valign = 'M', 
		bool $rtloff = false
	): void {
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
	 * Whenever a page break condition is met, the method is called
	 * 
	 * @return bool
	 */
	public function AcceptPageBreak(): bool 
	{
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
	 * 
	 * @param float $h Cell height
	 * @param float|null $y starting y position, leave empty for current position
	 * @param bool $addpage if true add a page, otherwise only return the true/false state
	 * @return bool true in case of page break, false otherwise
	 */
	protected function checkPageBreak(float $h = 0, ?float $y = null, bool $addpage = true): bool 
	{
		if ($y === null) {
			$y = $this->y;
		}
		
		$current_page = $this->page;
		
		if ((($y + $h) > $this->PageBreakTrigger) && ($this->inPageBody()) && ($this->AcceptPageBreak())) {
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
	 * 
	 * @param int $oldchar Integer code (unicode) of the character to replace
	 * @param int $newchar Integer code (unicode) of the new character
	 * @return int the replaced char or the old char in case the new char is not defined
	 */
	protected function replaceChar(int $oldchar, int $newchar): int 
	{
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
	 * This method prints text from the current position.
	 * 
	 * @param float $h Line height
	 * @param string $txt String to print
	 * @param mixed $link URL or identifier returned by AddLink()
	 * @param bool $fill Indicates if the cell background must be painted
	 * @param string $align Allows to center or align the text
	 * @param bool $ln if true set cursor at the bottom of the line
	 * @param int $stretch font stretch mode
	 * @param bool $firstline if true prints only the first line and return the remaining string
	 * @param bool $firstblock if true the string is the starting of a line
	 * @param float $maxh maximum height
	 * @param float $wadj first line width will be reduced by this amount
	 * @param array|null $margin margin array of the parent container
	 * @return mixed Return the number of cells or the remaining string if $firstline = true
	 */
	public function Write(
		float $h, 
		string $txt, 
		$link = '', 
		bool $fill = false, 
		string $align = '', 
		bool $ln = false, 
		int $stretch = 0, 
		bool $firstline = false, 
		bool $firstblock = false, 
		float $maxh = 0, 
		float $wadj = 0, 
		?array $margin = null
	) {
		// check page for no-write regions and adapt page margins if necessary
		[$this->x, $this->y] = $this->checkPageRegions($h, $this->x, $this->y);
		
		if (strlen($txt) == 0) {
			// fix empty text
			$txt = ' ';
		}
		
		if (!is_array($margin)) {
			// set default margins
			$margin = $this->cell_margin;
		}
		
		return $this->processTextWrite($h, $txt, $link, $fill, $align, $ln, $stretch, $firstline, $firstblock, $maxh, $wadj, $margin);
	}

	/**
	 * Set the block dimensions accounting for page breaks and page/column fitting
	 * 
	 * @param float $w width
	 * @param float $h height
	 * @param float $x X coordinate
	 * @param float $y Y coordinate
	 * @param bool $fitonpage if true the block is resized to not exceed page dimensions
	 * @return array array($w, $h, $x, $y)
	 */
	protected function fitBlock(float $w, float $h, float $x, float $y, bool $fitonpage = false): array 
	{
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
		if ($fitonpage || $this->AutoPageBreak) {
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
		
		if ($this->checkPageBreak($h, $y) || ($this->y < $prev_y)) {
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
			
			if ((!$this->rtl) && (($x + $w) > ($this->w - $this->rMargin))) {
				$w = $this->w - $this->rMargin - $x;
				$h = ($w / $ratio_wh);
			} elseif (($this->rtl) && (($x - $w) < ($this->lMargin))) {
				$w = $x - $this->lMargin;
				$h = ($w / $ratio_wh);
			}
		}
		
		return [$w, $h, $x, $y];
	}

	// Private helper methods for constructor initialization
	
	private function initializeFileIds(string $orientation, string $unit, $format, string $encoding): void 
	{
		$serformat = (is_array($format) ? json_encode($format) : $format);
		$this->file_id = md5(LIMEPDF_STATIC::getRandomSeed('TCPDF'.$orientation.$unit.$serformat.$encoding));
		$this->hash_key = hash_hmac('sha256', LIMEPDF_STATIC::getRandomSeed($this->file_id), LIMEPDF_STATIC::getRandomSeed('TCPDF'), false);
		$this->font_obj_ids = [];
		$this->page_obj_id = [];
		$this->form_obj_id = [];
	}
	
	private function initializePdfaMode($pdfa): void 
	{
		if ($pdfa != false) {
			$this->pdfa_mode = true;
			$this->pdfa_version = $pdfa;  // 1 or 3
		} else {
			$this->pdfa_mode = false;
		}
	}
	
	private function initializeRtlSettings(): void 
	{
		$this->force_srgb = false;
		$this->rtl = false;
		$this->tmprtl = false;
	}
	
	private function performSystemChecks(): void 
	{
		$this->_doChecks();
	}
	
	private function initializeBasicProperties(bool $unicode): void 
	{
		$this->isunicode = $unicode;
		$this->page = 0;
		$this->transfmrk[0] = [];
		$this->pagedim = [];
		$this->n = 2;
		$this->buffer = '';
		$this->pages = [];
		$this->state = 0;
		$this->fonts = [];
		$this->FontFiles = [];
		$this->diffs = [];
		$this->images = [];
		$this->links = [];
		$this->gradients = [];
		$this->InFooter = false;
		$this->lasth = 0;
	}
	
	private function initializeFontSettings(): void 
	{
		$this->FontFamily = defined('PDF_FONT_NAME_MAIN') ? PDF_FONT_NAME_MAIN : 'helvetica';
		$this->FontStyle = '';
		$this->FontSizePt = 12;
		$this->underline = false;
		$this->overline = false;
		$this->linethrough = false;
	}
	
	private function initializeColorSettings(): void 
	{
		$this->DrawColor = '0 G';
		$this->FillColor = '0 g';
		$this->TextColor = '0 g';
		$this->ColorFlag = false;
		$this->pdflayers = [];
	}
	
	private function initializeEncryptionSettings(): void 
	{
		$this->encrypted = false;
		$this->last_enc_key = '';
	}
	
	private function initializeCorefonts(): void 
	{
		$this->CoreFonts = [
			'courier' => 'Courier',
			'courierB' => 'Courier-Bold',
			'courierI' => 'Courier-Oblique',
			'courierBI' => 'Courier-BoldOblique',
			'helvetica' => 'Helvetica',
			'helveticaB' => 'Helvetica-Bold',
			'helveticaI' => 'Helvetica-Oblique',
			'helveticaBI' => 'Helvetica-BoldOblique',
			'times' => 'Times-Roman',
			'timesB' => 'Times-Bold',
			'timesI' => 'Times-Italic',
			'timesBI' => 'Times-BoldItalic',
			'symbol' => 'Symbol',
			'zapfdingbats' => 'ZapfDingbats'
		];
	}
	
	private function initializePageSettings(string $unit, $format, string $orientation): void 
	{
		$this->setPageUnit($unit);
		$this->setPageFormat($format, $orientation);
	}
	
	private function initializeMarginSettings(): void 
	{
		// Add this before the division
		error_log("TCPDF k value: " . $this->k);
		error_log("TCPDF unit: " . $this->unit);

		if ($this->k <= 0) {
			error_log("Invalid k value detected");
			$this->k = 2.83464567; // Default for 'mm' unit
		}
		$margin = 28.35 / $this->k;
		$this->setMargins($margin, $margin);
		$this->clMargin = $this->lMargin;
		$this->crMargin = $this->rMargin;
		
		$cpadding = $margin / 10;
		$this->setCellPaddings($cpadding, 0, $cpadding, 0);
		$this->setCellMargins(0, 0, 0, 0);
	}
	
	private function initializeDrawingSettings(): void 
	{
		$this->LineWidth = 0.57 / $this->k;
		$this->linestyleWidth = sprintf('%F w', ($this->LineWidth * $this->k));
		$this->linestyleCap = '0 J';
		$this->linestyleJoin = '0 j';
		$this->linestyleDash = '[] 0 d';
	}
	
	private function initializeDisplaySettings(): void 
	{
		$margin = 28.35 / $this->k;
		$this->setAutoPageBreak(true, (2 * $margin));
		$this->setDisplayMode('fullwidth');
	}
	
	private function initializeCompressionSettings(): void 
	{
		$this->setCompression();
	}
	
	private function initializeVersionSettings(): void 
	{
		$this->setPDFVersion();
		$this->tcpdflink = true;
	}
	
	private function initializeLinkSettings(): void 
	{
		$this->HREF = [];
		$this->getFontsList();
	}
	
	private function initializeColorProperties(): void 
	{
		$this->fgcolor = ['R' => 0, 'G' => 0, 'B' => 0];
		$this->strokecolor = ['R' => 0, 'G' => 0, 'B' => 0];
		$this->bgcolor = ['R' => 255, 'G' => 255, 'B' => 255];
		$this->extgstates = [];
		$this->setTextShadow();
	}
	
	private function initializeSignatureSettings(): void 
	{
		$this->sign = false;
		$this->tsa_timestamp = false;
		$this->tsa_data = [];
		$this->signature_appearance = ['page' => 1, 'rect' => '0 0 0 0', 'name' => 'Signature'];
		$this->empty_signature_appearance = [];
	}
	
	private function initializeUserRightsSettings(): void 
	{
		$this->ur['enabled'] = false;
		$this->ur['document'] = '/FullSave';
		$this->ur['annots'] = '/Create/Delete/Modify/Copy/Import/Export';
		$this->ur['form'] = '/Add/Delete/FillIn/Import/Export/SubmitStandalone/SpawnTemplate';
		$this->ur['signature'] = '/Modify';
		$this->ur['ef'] = '/Create/Delete/Modify/Import';
		$this->ur['formex'] = '';
	}
	
	private function initializeImageSettings(): void 
	{
		$this->jpeg_quality = 75;
	}
	
	private function initializeFontProcessing(): void 
	{
		LIMEPDF_FONT::utf8Bidi([], '', false, $this->isunicode, $this->CurrentFont);
		$this->setFont($this->FontFamily, $this->FontStyle, $this->FontSizePt);
		$this->setHeaderFont([$this->FontFamily, $this->FontStyle, $this->FontSizePt]);
		$this->setFooterFont([$this->FontFamily, $this->FontStyle, $this->FontSizePt]);
	}
	
	private function initializeRegexSettings(): void 
	{
		if ($this->isunicode && (@preg_match('/\pL/u', 'a') == 1)) {
			// PCRE unicode support is turned ON
			$this->setSpacesRE('/(?!\xa0)[\s\p{Z}]/u');
		} else {
			// PCRE unicode support is turned OFF
			$this->setSpacesRE('/[^\S\xa0]/');
		}
	}
	
	private function initializeFormSettings(): void 
	{
		$this->default_form_prop = [
			'lineWidth' => 1, 
			'borderStyle' => 'solid', 
			'fillColor' => [255, 255, 255], 
			'strokeColor' => [128, 128, 128]
		];
	}
	
	private function initializeTimestampSettings(): void 
	{
		$this->doc_creation_timestamp = time();
		$this->doc_modification_timestamp = $this->doc_creation_timestamp;
	}
	
	private function initializeGraphicSettings(): void 
	{
		$this->default_graphic_vars = $this->getGraphicVars();
		$this->header_xobj_autoreset = false;
		$this->custom_xmp = '';
		$this->custom_xmp_rdf = '';
	}
	
	private function initializeHeaderSettings(): void 
	{
		$this->header_xobj_autoreset = false;
		$this->custom_xmp = '';
		$this->custom_xmp_rdf = '';
	}

	/**
	 * Check if exceptions should be thrown
	 */
	private function shouldThrowExceptions(): bool 
	{
		return !defined('K_TCPDF_THROW_EXCEPTION_ERROR') || K_TCPDF_THROW_EXCEPTION_ERROR;
	}

	/**
	 * Output error message and terminate
	 */
	private function outputErrorAndDie(string $msg): void 
	{
		die('<strong>TCPDF ERROR: </strong>' . $msg);
	}

	/**
	 * Add TCPDF link to document
	 */
	private function addTcpdfLink(): void 
	{
		// save current graphic settings
		$gvars = $this->getGraphicVars();
		$this->setEqualColumns();
		$this->lastpage(true);
		$this->setAutoPageBreak(false);
		$this->x = 0;
		$this->y = $this->h - (1 / $this->k);
		$this->lMargin = 0;
		$this->_outSaveGraphicsState();
		$font = defined('PDF_FONT_NAME_MAIN') ? PDF_FONT_NAME_MAIN : 'helvetica';
		$this->setFont($font, '', 1);
		$this->setTextRenderingMode(0, false, false);
		$msg = "\x50\x6f\x77\x65\x72\x65\x64\x20\x62\x79\x20\x54\x43\x50\x44\x46\x20\x28\x77\x77\x77\x2e\x74\x63\x70\x64\x66\x2e\x6f\x72\x67\x29";
		$lnk = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x74\x63\x70\x64\x66\x2e\x6f\x72\x67";
		$this->Cell(0, 0, $msg, 0, 0, 'L', 0, $lnk, 0, false, 'D', 'B');
		$this->_outRestoreGraphicsState();
		// restore graphic settings
		$this->setGraphicVars($gvars);
	}

	/**
	 * Process text for the Write method (extracted from the massive Write method)
	 * 
	 * @param float $h Line height
	 * @param string $txt String to print
	 * @param mixed $link URL or identifier
	 * @param bool $fill Fill background
	 * @param string $align Text alignment
	 * @param bool $ln Line break
	 * @param int $stretch Font stretch
	 * @param bool $firstline First line only
	 * @param bool $firstblock First block
	 * @param float $maxh Maximum height
	 * @param float $wadj Width adjustment
	 * @param array $margin Margin settings
	 * @return mixed
	 */
	private function processTextWrite(
		float $h, 
		string $txt, 
		$link, 
		bool $fill, 
		string $align, 
		bool $ln, 
		int $stretch, 
		bool $firstline, 
		bool $firstblock, 
		float $maxh, 
		float $wadj, 
		array $margin
	) {
		// remove carriage returns
		$s = str_replace("\r", '', $txt);
		
		// check if string contains arabic or RTL text
		$arabic = $this->containsArabicText($s);
		$rtlmode = $this->isRtlMode($s, $arabic);
		
		// get character processing data
		$processingData = $this->prepareTextProcessingData($s, $h, $maxh, $wadj, $firstline);
		
		// process each character
		return $this->processCharacters($processingData, $h, $link, $fill, $align, $ln, $stretch, $firstline, $firstblock, $maxh, $wadj, $margin, $rtlmode, $arabic);
	}

	/**
	 * Check if text contains Arabic characters
	 */
	private function containsArabicText(string $text): bool 
	{
		return (bool)preg_match(LIMEPDF_FONT_DATA::$uni_RE_PATTERN_ARABIC, $text);
	}

	/**
	 * Determine if RTL mode should be used
	 */
	private function isRtlMode(string $text, bool $arabic): bool 
	{
		return $arabic || ($this->tmprtl == 'R') || (bool)preg_match(LIMEPDF_FONT_DATA::$uni_RE_PATTERN_RTL, $text);
	}

	/**
	 * Prepare data needed for text processing
	 * 
	 * @param string $s Text to process
	 * @param float $h Line height
	 * @param float $maxh Maximum height
	 * @param float $wadj Width adjustment
	 * @param bool $firstline First line flag
	 * @return array Processing data
	 */
	private function prepareTextProcessingData(string $s, float $h, float $maxh, float $wadj, bool $firstline): array 
	{
		// get character width and array of unicode values
		$chrwidth = $this->GetCharWidth(46); // dot character
		$chars = LIMEPDF_FONT::UTF8StringToArray($s, $this->isunicode, $this->CurrentFont);
		$chrw = $this->GetArrStringWidth($chars, '', '', 0, true);
		
		if (is_array($chrw)) {
			array_walk($chrw, [$this, 'getRawCharWidth']);
			$maxchwidth = count($chrw) > 0 ? max($chrw) : 0;
		} else {
			$maxchwidth = 0;
		}
		
		$uchars = LIMEPDF_FONT::UTF8ArrayToUniArray($chars, $this->isunicode);
		$nb = count($chars);
		
		// Calculate page and width dimensions
		$pw = $w = $this->w - $this->lMargin - $this->rMargin;
		$w = $this->rtl ? ($this->x - $this->lMargin) : ($this->w - $this->rMargin - $this->x);
		$wmax = $w - $wadj;
		
		if (!$firstline) {
			$wmax -= ($this->cell_padding['L'] + $this->cell_padding['R']);
		}
		
		$row_height = max($h, $this->getCellHeight($this->FontSize));
		$maxy = $this->y + $maxh - max($row_height, $h);
		
		return [
			'chars' => $chars,
			'uchars' => $uchars,
			'nb' => $nb,
			'chrwidth' => $chrwidth,
			'maxchwidth' => $maxchwidth,
			'pw' => $pw,
			'w' => $w,
			'wmax' => $wmax,
			'row_height' => $row_height,
			'maxy' => $maxy
		];
	}

	/**
	 * Process individual characters in the text
	 * 
	 * @param array $data Processing data
	 * @param float $h Line height
	 * @param mixed $link Link
	 * @param bool $fill Fill flag
	 * @param string $align Alignment
	 * @param bool $ln Line break flag
	 * @param int $stretch Stretch mode
	 * @param bool $firstline First line flag
	 * @param bool $firstblock First block flag
	 * @param float $maxh Maximum height
	 * @param float $wadj Width adjustment
	 * @param array $margin Margin array
	 * @param bool $rtlmode RTL mode flag
	 * @param bool $arabic Arabic text flag
	 * @return mixed
	 */
	private function processCharacters(
		array $data, 
		float $h, 
		$link, 
		bool $fill, 
		string $align, 
		bool $ln, 
		int $stretch, 
		bool $firstline, 
		bool $firstblock, 
		float $maxh, 
		float $wadj, 
		array $margin, 
		bool $rtlmode, 
		bool $arabic
	) {
		$chars = $data['chars'];
		$uchars = $data['uchars'];
		$nb = $data['nb'];
		$wmax = $data['wmax'];
		$chrwidth = $data['chrwidth'];
		$maxchwidth = $data['maxchwidth'];
		$pw = $data['pw'];
		$maxy = $data['maxy'];
		
		// Return empty string if character doesn't fit
		if ((!$firstline) && (($chrwidth > $wmax) || ($maxchwidth > $wmax))) {
			return '';
		}
		
		// Character processing variables
		$i = 0; // character position
		$j = 0; // current starting position  
		$sep = -1; // position of the last blank space
		$prevsep = $sep; // previous separator
		$shy = false; // true if the last blank is a soft hyphen (SHY)
		$prevshy = $shy; // previous shy mode
		$l = 0; // current string length
		$nl = 0; // number of lines
		$linebreak = false;
		$pc = 0; // previous character
		
		$start_page = $this->page;
		
		// Soft hyphen replacement settings
		$shy_replacement = self::HYPHEN;
		$shy_replacement_char = LIMEPDF_FONT::unichr($shy_replacement, $this->isunicode);
		$shy_replacement_width = $this->GetCharWidth($shy_replacement);
		
		// Process each character
		while ($i < $nb) {
			if (($maxh > 0) && ($this->y > $maxy)) {
				break;
			}
			
			$c = $chars[$i];
			
			if ($c == self::NEWLINE) {
				// Handle explicit line break
				$result = $this->handleLineBreak($chars, $uchars, $i, $j, $h, $align, $fill, $link, $stretch, $firstline, $firstblock, $rtlmode, $margin, $maxh);
				
				if ($result !== null) {
					return $result;
				}
				
				++$nl;
				$j = $i + 1;
				$l = 0;
				$sep = -1;
				$prevsep = $sep;
				$shy = false;
				
				$this->updateMarginsAfterPageBreak($margin);
				$w = $this->getRemainingWidth();
				$wmax = ($w - $this->cell_padding['L'] - $this->cell_padding['R']);
				
			} else {
				// Handle regular characters and word breaking
				$breakInfo = $this->checkForWordBreak($c, $i, $chars, $pc, $nb);
				
				if ($breakInfo['is_break']) {
					$prevsep = $sep;
					$sep = $i;
					
					if ($breakInfo['is_shy']) {
						$prevshy = $shy;
						$shy = true;
						$tmp_shy_replacement_width = ($pc == self::HYPHEN) ? 0 : $shy_replacement_width;
						$tmp_shy_replacement_char = ($pc == self::HYPHEN) ? '' : $shy_replacement_char;
					} else {
						$shy = false;
					}
				}
				
				// Update string length
				$l = $this->updateStringLength($chars, $j, $i, $arabic, $rtlmode, $l, $c);
				
				// Check if we need to wrap
				if (($l > $wmax) || (($c == self::SOFT_HYPHEN) && (($l + ($tmp_shy_replacement_width ?? 0)) >= $wmax))) {
					$wrapResult = $this->handleWordWrap($chars, $uchars, $i, $j, $sep, $shy, $prevsep, $prevshy, $h, $align, $fill, $link, $stretch, $firstline, $firstblock, $rtlmode, $maxh, $pw, $margin, $tmp_shy_replacement_width ?? 0, $tmp_shy_replacement_char ?? '');
					
					if ($wrapResult !== null) {
						return $wrapResult;
					}
					
					$this->updateAfterWrap($margin);
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
		}
		
		// Handle remaining text
		return $this->handleRemainingText($chars, $uchars, $j, $nb, $l, $align, $h, $fill, $link, $stretch, $firstline, $firstblock, $rtlmode, $ln, $nl, $maxh);
	}

	/**
	 * Handle line breaks in text processing
	 */
	private function handleLineBreak(
		array $chars, 
		array $uchars, 
		int $i, 
		int $j, 
		float $h, 
		string $align, 
		bool $fill, 
		$link, 
		int $stretch, 
		bool $firstline, 
		bool $firstblock, 
		bool $rtlmode, 
		array $margin, 
		float $maxh
	): ?string {
		$talign = ($align == 'J') ? ($this->rtl ? 'R' : 'L') : $align;
		$tmpstr = LIMEPDF_FONT::UniArrSubString($uchars, $j, $i);
		
		if ($firstline) {
			return $this->handleFirstLine($chars, $j, $i, $tmpstr, $rtlmode, $h, $maxh);
		}
		
		if ($firstblock && $this->isRTLTextDir()) {
			$tmpstr = $this->stringRightTrim($tmpstr);
		}
		
		// Skip newlines at the beginning of a page or column
		if (!empty($tmpstr) || ($this->y < ($this->PageBreakTrigger - $this->getCellHeight($this->FontSize)))) {
			$this->Cell($this->getRemainingWidth(), $h, $tmpstr, 0, 1, $talign, $fill, $link, $stretch);
		}
		
		return null;
	}

	/**
	 * Handle first line processing
	 */
	private function handleFirstLine(
		array $chars, 
		int $j, 
		int $i, 
		string $tmpstr, 
		bool $rtlmode, 
		float $h, 
		float $maxh
	): string {
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
		
		$tmpcellpadding = $this->cell_padding;
		if ($maxh == 0) {
			$this->setCellPadding(0);
		}
		
		$this->Cell($linew, $h, $tmpstr, 0, 1, '', false, '', 0);
		$this->cell_padding = $tmpcellpadding;
		
		return LIMEPDF_FONT::UniArrSubString($this->uchars ?? [], $i);
	}

	/**
	 * Check if character should cause word break
	 */
	private function checkForWordBreak(int $c, int $i, array $chars, int $pc, int $nb): array 
	{
		// 160 is non-breaking space, 173 is SHY (Soft Hyphen)
		if ($c != self::NON_BREAKING_SPACE && (
			($c == self::SOFT_HYPHEN) ||
			preg_match($this->re_spaces, LIMEPDF_FONT::unichr($c, $this->isunicode)) ||
			(($c == self::HYPHEN) && ($i < ($nb - 1)) &&
				@preg_match('/[\p{L}]/'.$this->re_space['m'], LIMEPDF_FONT::unichr($pc, $this->isunicode)) &&
				@preg_match('/[\p{L}]/'.$this->re_space['m'], LIMEPDF_FONT::unichr($chars[($i + 1)], $this->isunicode))
			)
		)) {
			return [
				'is_break' => true,
				'is_shy' => ($c == self::SOFT_HYPHEN) || ($c == self::HYPHEN)
			];
		}
		
		return ['is_break' => false, 'is_shy' => false];
	}

	/**
	 * Update string length calculation
	 */
	private function updateStringLength(
		array $chars, 
		int $j, 
		int $i, 
		bool $arabic, 
		bool $rtlmode, 
		float $l, 
		int $c
	): float {
		if ($this->isUnicodeFont() && $arabic) {
			// with bidirectional algorithm some chars may be changed affecting the line length
			return $this->GetArrStringWidth(LIMEPDF_FONT::utf8Bidi(
				array_slice($chars, $j, ($i - $j)), 
				'', 
				$this->tmprtl, 
				$this->isunicode, 
				$this->CurrentFont
			));
		} else {
			return $l + $this->GetCharWidth($c, ($i + 1 < count($chars)));
		}
	}

	/**
	 * Handle word wrapping logic
	 */
	private function handleWordWrap(
		array $chars, 
		array $uchars, 
		int &$i, 
		int &$j, 
		int &$sep, 
		bool &$shy, 
		int $prevsep, 
		bool $prevshy, 
		float $h, 
		string $align, 
		bool $fill, 
		$link, 
		int $stretch, 
		bool $firstline, 
		bool $firstblock, 
		bool $rtlmode, 
		float $maxh, 
		float $pw, 
		array $margin, 
		float $shy_width, 
		string $shy_char
	): ?string {
		if ($sep == -1) {
			// No suitable break point found - handle truncation
			return $this->handleTruncation($chars, $uchars, $i, $j, $h, $align, $fill, $link, $stretch, $firstline, $firstblock, $rtlmode, $maxh);
		} else {
			// Handle word wrapping
			return $this->handleWordBreakWrap($chars, $uchars, $i, $j, $sep, $shy, $h, $align, $fill, $link, $stretch, $firstline, $firstblock, $rtlmode, $maxh, $pw, $shy_width, $shy_char);
		}
	}

	/**
	 * Handle text truncation when no break point available
	 */
	private function handleTruncation(
		array $chars, 
		array $uchars, 
		int &$i, 
		int &$j, 
		float $h, 
		string $align, 
		bool $fill, 
		$link, 
		int $stretch, 
		bool $firstline, 
		bool $firstblock, 
		bool $rtlmode, 
		float $maxh
	): ?string {
		// Check if the line was already started
		$cell_padding_offset = $this->cell_padding['R'] ?? 0;
		$margin_offset = 0; // This would need to be passed or calculated
		$chrwidth = $this->GetCharWidth(46); // dot character width
		
		if (($this->rtl && ($this->x <= ($this->w - $this->rMargin - $cell_padding_offset - $margin_offset - $chrwidth))) ||
			((!$this->rtl) && ($this->x >= ($this->lMargin + ($this->cell_padding['L'] ?? 0) + $margin_offset + $chrwidth)))) {
			// print a void cell and go to next line
			$this->Cell($this->getRemainingWidth(), $h, '', 0, 1);
			
			if ($firstline) {
				return LIMEPDF_FONT::UniArrSubString($uchars, $j);
			}
		} else {
			// truncate the word because it doesn't fit on column
			return $this->truncateWord($chars, $uchars, $i, $j, $h, $align, $fill, $link, $stretch, $firstline, $firstblock, $rtlmode, $maxh);
		}
		
		return null;
	}

	/**
	 * Truncate word that doesn't fit
	 */
	private function truncateWord(
		array $chars, 
		array $uchars, 
		int &$i, 
		int &$j, 
		float $h, 
		string $align, 
		bool $fill, 
		$link, 
		int $stretch, 
		bool $firstline, 
		bool $firstblock, 
		bool $rtlmode, 
		float $maxh
	): ?string {
		$tmpstr = LIMEPDF_FONT::UniArrSubString($uchars, $j, $i);
		
		if ($firstline) {
			return $this->handleFirstLine($chars, $j, $i, $tmpstr, $rtlmode, $h, $maxh);
		}
		
		if ($firstblock && $this->isRTLTextDir()) {
			$tmpstr = $this->stringRightTrim($tmpstr);
		}
		
		$this->Cell($this->getRemainingWidth(), $h, $tmpstr, 0, 1, $align, $fill, $link, $stretch);
		$j = $i;
		--$i;
		
		return null;
	}

	/**
	 * Handle word break wrapping
	 */
	private function handleWordBreakWrap(
		array $chars, 
		array $uchars, 
		int &$i, 
		int &$j, 
		int &$sep, 
		bool &$shy, 
		float $h, 
		string $align, 
		bool $fill, 
		$link, 
		int $stretch, 
		bool $firstline, 
		bool $firstblock, 
		bool $rtlmode, 
		float $maxh, 
		float $pw, 
		float $shy_width, 
		string $shy_char
	): ?string {
		$endspace = ($this->rtl && (!$firstblock) && ($sep < $i)) ? 1 : 0;
		
		// Check the length of the next string
		$strrest = LIMEPDF_FONT::UniArrSubString($uchars, ($sep + $endspace));
		$nextstr = LIMEPDF_STATIC::pregSplit('/'.$this->re_space['p'].'/', $this->re_space['m'], $this->stringTrim($strrest));
		
		if (isset($nextstr[0]) && ($this->GetStringWidth($nextstr[0]) > $pw)) {
			// truncate the word because it doesn't fit on a full page width
			return $this->truncateWord($chars, $uchars, $i, $j, $h, $align, $fill, $link, $stretch, $firstline, $firstblock, $rtlmode, $maxh);
		} else {
			// Standard word wrapping
			return $this->performWordWrap($chars, $uchars, $i, $j, $sep, $shy, $endspace, $h, $align, $fill, $link, $stretch, $firstline, $firstblock, $rtlmode, $maxh, $shy_width, $shy_char);
		}
	}

	/**
	 * Perform the actual word wrapping
	 */
	private function performWordWrap(
		array $chars, 
		array $uchars, 
		int &$i, 
		int &$j, 
		int &$sep, 
		bool &$shy, 
		int $endspace, 
		float $h, 
		string $align, 
		bool $fill, 
		$link, 
		int $stretch, 
		bool $firstline, 
		bool $firstblock, 
		bool $rtlmode, 
		float $maxh, 
		float $shy_width, 
		string $shy_char
	): ?string {
		if ($shy) {
			if ($this->rtl) {
				$shy_char_left = $shy_char;
				$shy_char_right = '';
			} else {
				$shy_char_left = '';
				$shy_char_right = $shy_char;
			}
		} else {
			$shy_char_left = '';
			$shy_char_right = '';
		}
		
		$tmpstr = LIMEPDF_FONT::UniArrSubString($uchars, $j, ($sep + $endspace));
		
		if ($firstline) {
			return $this->handleFirstLineWrap($chars, $j, $sep, $endspace, $tmpstr, $rtlmode, $h, $maxh, $shy_width, $shy_char_left, $shy_char_right);
		}
		
		// Print the line
		if ($firstblock && $this->isRTLTextDir()) {
			$tmpstr = $this->stringRightTrim($tmpstr);
		}
		
		$this->Cell($this->getRemainingWidth(), $h, $shy_char_left . $tmpstr . $shy_char_right, 0, 1, $align, $fill, $link, $stretch);
		
		$i = $sep;
		$sep = -1;
		$shy = false;
		$j = ($i + 1);
		
		return null;
	}

	/**
	 * Handle first line word wrap
	 */
	private function handleFirstLineWrap(
		array $chars, 
		int $j, 
		int $sep, 
		int $endspace, 
		string $tmpstr, 
		bool $rtlmode, 
		float $h, 
		float $maxh, 
		float $shy_width, 
		string $shy_char_left, 
		string $shy_char_right
	): string {
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
		
		$tmpcellpadding = $this->cell_padding;
		if ($maxh == 0) {
			$this->setCellPadding(0);
		}
		
		$this->Cell($linew, $h, $shy_char_left . $tmpstr . $shy_char_right, 0, 1, '', false, '', 0);
		
		if ($chars[$sep] == self::HYPHEN) {
			$endspace += 1;
		}
		
		// return the remaining text
		$this->cell_padding = $tmpcellpadding;
		return LIMEPDF_FONT::UniArrSubString($this->uchars ?? [], ($sep + $endspace));
	}

	/**
	 * Update margins after page break
	 */
	private function updateMarginsAfterPageBreak(array $margin): void 
	{
		if ((($this->y + $this->lasth) > $this->PageBreakTrigger) && ($this->inPageBody())) {
			if ($this->AcceptPageBreak()) {
				if ($this->rtl) {
					$this->x -= $margin['R'] ?? 0;
				} else {
					$this->x += $margin['L'] ?? 0;
				}
				$this->lMargin += $margin['L'] ?? 0;
				$this->rMargin += $margin['R'] ?? 0;
			}
		}
	}

	/**
	 * Update after word wrap
	 */
	private function updateAfterWrap(array $margin): void 
	{
		if ((($this->y + $this->lasth) > $this->PageBreakTrigger) && ($this->inPageBody())) {
			if ($this->AcceptPageBreak()) {
				if ($this->rtl) {
					$this->x -= $margin['R'] ?? 0;
				} else {
					$this->x += $margin['L'] ?? 0;
				}
				$this->lMargin += $margin['L'] ?? 0;
				$this->rMargin += $margin['R'] ?? 0;
			}
		}
	}

	/**
	 * Handle remaining text after character processing
	 */
	private function handleRemainingText(
		array $chars, 
		array $uchars, 
		int $j, 
		int $nb, 
		float $l, 
		string $align, 
		float $h, 
		bool $fill, 
		$link, 
		int $stretch, 
		bool $firstline, 
		bool $firstblock, 
		bool $rtlmode, 
		bool $ln, 
		int $nl, 
		float $maxh
	) {
		// print last substring (if any)
		if ($l > 0) {
			$w = $this->calculateFinalWidth($align, $l);
			$tmpstr = LIMEPDF_FONT::UniArrSubString($uchars, $j, $nb);
			
			if ($firstline) {
				return $this->handleFinalFirstLine($chars, $j, $nb, $tmpstr, $rtlmode, $h, $maxh, $w);
			}
			
			if ($firstblock && $this->isRTLTextDir()) {
				$tmpstr = $this->stringRightTrim($tmpstr);
			}
			
			$this->Cell($w, $h, $tmpstr, 0, $ln, $align, $fill, $link, $stretch);
			++$nl;
		}
		
		if ($firstline) {
			return '';
		}
		
		return $nl;
	}

	/**
	 * Calculate final width based on alignment
	 */
	private function calculateFinalWidth(string $align, float $l): float 
	{
		switch ($align) {
			case 'J':
			case 'C':
				return $this->getRemainingWidth();
			case 'L':
				return !$this->rtl ? $l : $this->getRemainingWidth();
			case 'R':
				return $this->rtl ? $l : $this->getRemainingWidth();
			default:
				return $l;
		}
	}

	/**
	 * Handle final first line processing
	 */
	private function handleFinalFirstLine(
		array $chars, 
		int $j, 
		int $nb, 
		string $tmpstr, 
		bool $rtlmode, 
		float $h, 
		float $maxh, 
		float $w
	): string {
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
		
		$tmpcellpadding = $this->cell_padding;
		if ($maxh == 0) {
			$this->setCellPadding(0);
		}
		
		$this->Cell($w, $h, $tmpstr, 0, 0, '', false, '', 0);
		$this->cell_padding = $tmpcellpadding;
		
		return LIMEPDF_FONT::UniArrSubString($this->uchars ?? [], $nb);
	}

	/**
	 * Get remaining width for current line
	 */
	private function getRemainingWidth(): float 
	{
		if ($this->rtl) {
			return $this->x - $this->lMargin;
		} else {
			return $this->w - $this->rMargin - $this->x;
		}
	}

	// Additional helper methods that would be needed by the extracted functionality
	
	/**
	 * Check if current position is in page body
	 */
	private function inPageBody(): bool 
	{
		// This method would need to be implemented based on the original TCPDF logic
		// Placeholder implementation
		return $this->state > 0;
	}

	/**
	 * Check if using unicode font
	 */
	private function isUnicodeFont(): bool 
	{
		return $this->isunicode;
	}

	/**
	 * Check if RTL text direction is enabled
	 */
	private function isRTLTextDir(): bool 
	{
		return $this->rtl;
	}

	/**
	 * Trim string from right (RTL specific)
	 */
	private function stringRightTrim(string $str): string 
	{
		return rtrim($str);
	}

	/**
	 * Trim string (generic)
	 */
	private function stringTrim(string $str): string 
	{
		return trim($str);
	}

	/**
	 * Get cell height for given font size
	 */
	private function getCellHeight(float $fontSize): float 
	{
		// This would need to be implemented based on original TCPDF logic
		// Placeholder implementation
		return $fontSize * 1.2; // Typical line height multiplier
	}

	/**
	 * Check page regions for no-write areas
	 */
	private function checkPageRegions(float $h, float $x, float $y): array 
	{
		// This would need to be implemented based on original TCPDF logic
		// Placeholder implementation
		return [$x, $y];
	}

	// Placeholder methods for traits and functionality that would be implemented elsewhere
	
	private function _doChecks(): void {}
	private function _destroy(bool $destroyall): void {}
	private function setPageUnit(string $unit): void {}
	private function setPageFormat($format, string $orientation): void {}
	private function setMargins(float $left, float $right, ?float $top = null): void {}
	private function setCellPaddings(float $left, float $top, float $right, float $bottom): void {}
	private function setCellMargins(float $left, float $top, float $right, float $bottom): void {}
	private function setAutoPageBreak(bool $auto, float $margin = 0): void {}
	private function setDisplayMode(string $zoom, string $layout = 'SinglePage'): void {}
	private function setCompression(bool $compress = true): void {}
	private function setPDFVersion(string $version = '1.4'): void {}
	private function getFontsList(): void {}
	private function setTextShadow(array $params = []): void {}
	private function setFont(string $family, string $style = '', float $size = 0): void {}
	private function setHeaderFont(array $font): void {}
	private function setFooterFont(array $font): void {}
	private function setSpacesRE(string $re): void {}
	private function getGraphicVars(): array { return []; }
	private function AddFont(string $family, string $style = ''): array { return ['fontkey' => '']; }
	private function getFontBuffer(string $fontkey): array { return ['cw' => []]; }
	private function setTextRenderingMode(int $stroke, bool $fill, bool $clip): void {}
	private function setXY(float $x, float $y, bool $rtloff = false): void {}
	private function Cell(float $w, float $h = 0, string $txt = '', $border = 0, int $ln = 0, string $align = '', bool $fill = false, $link = '', int $stretch = 0, bool $ignore_min_height = false, string $calign = 'T', string $valign = 'M'): void {}
	private function selectColumn(int $col): void {}
	private function AddPage(string $orientation = '', $format = '', bool $keepmargins = false): void {}
	private function endLayer(): void {}
	private function lastpage(bool $resetmargins = false): void {}
	private function _outSaveGraphicsState(): void {}
	private function _outRestoreGraphicsState(): void {}
	private function endPage(): void {}
	private function _enddoc(): void {}
	private function setEqualColumns(int $numcols = 0, float $width = 0, float $y = 0): void {}
	private function setGraphicVars(array $gvars): void {}
	private function GetCharWidth(int $char, bool $notlast = true): float { return 1.0; }
	private function GetArrStringWidth(array $chars, string $fontname = '', string $fontstyle = '', float $fontsize = 0, bool $getarray = false) { return $getarray ? [] : 0.0; }
	private function getRawCharWidth(&$char): void {}
	private function GetStringWidth(string $s, string $fontname = '', string $fontstyle = '', float $fontsize = 0): float { return 1.0; }
	private function setCellPadding(float $pad): void {}

	// Properties that would be defined in the original class or traits
	public $file_id;
	public $hash_key;
	public $font_obj_ids;
	public $page_obj_id;
	public $form_obj_id;
	public $pdfa_mode;
	public $pdfa_version;
	public $force_srgb;
	public $rtl;
	public $tmprtl;
	public $isunicode;
	public $page;
	public $transfmrk;
	public $pagedim;
	public $n;
	public $buffer;
	public $pages;
	public $state;
	public $fonts;
	public $FontFiles;
	public $diffs;
	public $images;
	public $links;
	public $gradients;
	public $InFooter;
	public $lasth;
	public $FontFamily;
	public $FontStyle;
	public $FontSizePt;
	public $FontSize;
	public $underline;
	public $overline;
	public $linethrough;
	public $DrawColor;
	public $FillColor;
	public $TextColor;
	public $ColorFlag;
	public $pdflayers;
	public $encrypted;
	public $last_enc_key;
	public $CoreFonts;
	public $k;
	public $lMargin;
	public $rMargin;
	public $tMargin;
	public $clMargin;
	public $crMargin;
	public $cell_padding;
	public $cell_margin;
	public $LineWidth;
	public $linestyleWidth;
	public $linestyleCap;
	public $linestyleJoin;
	public $linestyleDash;
	public $AutoPageBreak;
	public $PageBreakTrigger;
	public $w;
	public $h;
	public $CurOrientation;
	public $tcpdflink;
	public $encoding;
	public $HREF;
	public $fgcolor;
	public $strokecolor;
	public $bgcolor;
	public $extgstates;
	public $sign;
	public $tsa_timestamp;
	public $tsa_data;
	public $signature_appearance;
	public $empty_signature_appearance;
	public $ur;
	public $jpeg_quality;
	public $CurrentFont;
	public $re_spaces;
	public $re_space;
	public $default_form_prop;
	public $doc_creation_timestamp;
	public $doc_modification_timestamp;
	public $default_graphic_vars;
	public $header_xobj_autoreset;
	public $custom_xmp;
	public $custom_xmp_rdf;
	public $textrendermode;
	public $textstrokewidth;
	public $x;
	public $y;
	public $num_columns;
	public $current_column;
	public $endlinex;
	public $newline;
	public $uchars;
}