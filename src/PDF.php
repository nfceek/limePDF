<?php

namespace LimePDF;

	// Include Composer autoloader FIRST
	require_once dirname(__FILE__, 2) . '/vendor/autoload.php';

	// limePDF configuration
	require_once(dirname(__FILE__) . '/legacy.php');

	// includes Vars
	use LimePDF\Barcodes\BarcodeTrait;

	use LimePDF\Encryption\EncryptionTrait;

	use LimePDF\Graphics\ColumnsTrait;
	use LimePDF\Graphics\DrawTrait;
	use LimePDF\Graphics\GraphicsTrait;
	use LimePDF\Graphics\ImageAddTrait;	
	use LimePDF\Graphics\SVGTrait;
	use LimePDF\Graphics\TransformationsTrait;
	use LimePDF\Graphics\XObjectsTemplatesTrait;

	use LimePDF\Include\ColorsTrait;
	use LimePDF\Include\FiltersTrait;	
	use LimePDF\Include\FontTrait;
	use LimePDF\Include\FontDataTrait;
	use LimePDF\Include\ImageTrait;
	use LimePDF\Include\ImageToolsTrait;

	use LimePDF\Model\BarcodeGetterSetterTrait;
	use LimePDF\Model\FontGetterSetterTrait;	
	use LimePDF\Model\ImageGetterSetterTrait;		
	use LimePDF\Model\PageGetterSetterTrait;
	use LimePDF\Model\TextGetterSetterTrait;	
	use LimePDF\Model\UtilGetterSetterTrait;
	use LimePDF\Model\VarsGetterSetterTrait;
	use LimePDF\Model\WebGetterSetterTrait;

	use LimePDF\Pages\AnnotationsTrait;
	use LimePDF\Pages\BookmarksTrait;	
	use LimePDF\Pages\MarginsTrait;
	use LimePDF\Pages\PagesTrait;
	use LimePDF\Pages\PageColorsTrait;
	use LimePDF\Pages\PageManagerTrait;	
	use LimePDF\Pages\SectionsTrait;

	use LimePDF\Support\StaticTrait;	
	use LimePDF\Support\VarsTrait;

	use LimePDF\Text\TextTrait;	
	use LimePDF\Text\WriteTrait;

	use LimePDF\Util\BinaryToolsTrait;	
	use LimePDF\Utils\FontManagerTrait;
	use LimePDF\Utils\FontAddTrait;	
	use LimePDF\Utils\JavascriptTrait;
	use LimePDF\Utils\EnvironmentTrait;
	use LimePDF\Utils\MiscTrait;	
	use LimePDF\Utils\PutTrait;
	use LimePDF\Utils\SignatureTrait;

	use LimePDF\View\FormsTrait;
	use LimePDF\View\OutPutTrait;
	use LimePDF\View\SetupTrait;

	use LimePDF\Web\CellTrait;
	use LimePDF\Web\HtmlTrait;
	use LimePDF\Web\WebTrait;	

class PDF {

	public const THROW_EXCEPTION_ERROR = false;

	use ColorsTrait;
	use FiltersTrait;	
	use FontTrait;
	use FontDataTrait;
	use ImageTrait;

	use AnnotationsTrait;

	use BookmarksTrait;
	use BarcodeTrait;

	use CellTrait;
	use ColumnsTrait;

	use DrawTrait;

	use EncryptionTrait;
	use EnvironmentTrait;

	use FontManagerTrait;	
	use FontAddTrait;	
	use FormsTrait;

	use GraphicsTrait;

	use ImageTrait;
	use ImageAddTrait;
	use ImagetoolsTrait;
	
	use HtmlTrait;

	use JavascriptTrait;

	use MarginsTrait;
	use MiscTrait;

	use OutputTrait;

	use PagesTrait;
	use PageManagerTrait;		
	use PageColorsTrait;
	use PutTrait;

	use SectionsTrait;
	use SetupTrait;
	use SignatureTrait;
	use StaticTrait;
	use SVGTrait;

	use TextTrait;
	use TransformationsTrait;

	use VarsTrait;

	use WebTrait;
	use WriteTrait;

	use XObjectsTemplatesTrait;

	use BarcodeGetterSetterTrait;
	use FontGetterSetterTrait;	
	use ImageGetterSetterTrait;		
	use PageGetterSetterTrait;
	use TextGetterSetterTrait;	
	use UtilGetterSetterTrait;
	use VarsGetterSetterTrait;
	use WebGetterSetterTrait;

	public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
    
		// set file ID for trailer
		$serformat = (is_array($format) ? json_encode($format) : $format);
		$this->file_id = md5($this->getRandomSeed('TCPDF'.$orientation.$unit.$serformat.$encoding));
		$this->hash_key = hash_hmac('sha256', $this->getRandomSeed($this->file_id), $this->getRandomSeed('TCPDF'), false);
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
		$this->utf8Bidi(array(), '', false, $this->isunicode, $this->CurrentFont);
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
	 * Begin a new object and return the object number.
	 * @return int object number
	 * @protected
	 */
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
		if (self::empty_string($objid)) {
			++$this->n;
			$objid = $this->n;
		}
		$this->offsets[$objid] = $this->bufferlen;
		$this->pageobjects[$this->page][] = $objid;
		return $objid.' 0 obj';
	}

	/**
	 * Output error message
	 * @param string $msg The error message
	 * @protected
	 */
	protected function outputError(string $msg): void {
		// Choose one of these approaches:
		
		// Option A: Simple error output
		echo "<strong>LimePDF Error:</strong> " . $msg;
		
		// Option B: More formatted error
		echo "<!DOCTYPE html><html><head><title>LimePDF Error</title></head><body>";
		echo "<h1 style='color: red;'>LimePDF Error</h1>";
		echo "<p style='font-family: monospace; background: #f0f0f0; padding: 10px;'>" . $msg . "</p>";
		echo "</body></html>";
		
		// Option C: Log to error log and display simple message
		error_log("LimePDF Error: " . strip_tags($msg));
		echo "LimePDF Error: " . $msg;
	}
	
	/**
	 * 
	 * 8/2025 -- 8+ --> error improvement
	 * 
	 * Throw an exception or print an error message rewritten to remove Global var
	 * @param string $msg The error message
	 * @public
	 * @since 1.0
	 */
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
        // No global defines anymore
        return self::THROW_EXCEPTION_ERROR;
    }


	public static function unichrWrapper($c, $unicode = true) {
		return self::unichr($c, $unicode);
	}


} // END OF  CLASS

//============================================================+
// END OF FILE
//============================================================+
