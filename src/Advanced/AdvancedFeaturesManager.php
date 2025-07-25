<?php

/**
 * TCPDF Advanced Features Manager
 * 
 * Handles bookmarks, forms, JavaScript, signatures, and other advanced PDF features.
 * This class is LAZY LOADED - only instantiated when advanced features are used.
 */
class AdvancedFeaturesManager
{
    /**
     * Reference to main TCPDF instance for accessing core properties
     */
    private $tcpdf2;
    
    /**
     * Form fields collection
     */
    private $form_fields = array();
    
    /**
     * JavaScript code storage
     */
    private $javascript_code = '';
    
    /**
     * Signature configuration
     */
    private $signature_config = array();
    
    /**
     * Destinations for internal links
     */
    private $destinations = array();

    /**
     * Constructor - receives TCPDF instance for accessing needed properties
     */
    public function __construct($tcpdf_instance)
    {
        $this->tcpdf = $tcpdf_instance;
        $this->initializeAdvancedFeatures();
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
     * Put destinations for internal links
     * MOVED FROM: protected function _putdests()
     */
    public function putDestinations()
    {
		if (empty($this->dests)) {
			return;
		}
		$this->n_dests = $this->_newobj();
		$out = ' <<';
		foreach($this->dests as $name => $o) {
			$out .= ' /'.$name.' '.sprintf('[%u 0 R /XYZ %F %F null]', $this->page_obj_id[($o['p'])], ($o['x'] * $this->k), ($this->pagedim[$o['p']]['h'] - ($o['y'] * $this->k)));
		}
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
    }


    
	/**
	 * Adds a javascript
	 * @param string $script Javascript code
	 * @public
	 * @author Johannes G\FCntert, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	public function IncludeJS($script) {
		$this->javascript .= $script;
	}

    /**
	 * Adds a javascript object and return object ID
	 * @param string $script Javascript code
	 * @param boolean $onload if true executes this object when opening the document
	 * @return int internal object ID
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function addJavascriptObject($script, $onload=false) {
		if ($this->pdfa_mode) {
			// javascript is not allowed in PDF/A mode
			return false;
		}
		++$this->n;
		$this->js_objects[$this->n] = array('n' => $this->n, 'js' => $script, 'onload' => $onload);
		return $this->n;
	}


    /**
     * Put JavaScript code in PDF
     * MOVED FROM: protected function _putjavascript()
     */
    public function putJavaScript()
    {
		if ($this->pdfa_mode OR (empty($this->javascript) AND empty($this->js_objects))) {
			return;
		}
		if (strpos($this->javascript, 'this.addField') > 0) {
			if (!$this->ur['enabled']) {
				//$this->setUserRights();
			}
			// the following two lines are used to avoid form fields duplication after saving
			// The addField method only works when releasing user rights (UR3)
			$jsa = sprintf("ftcpdfdocsaved=this.addField('%s','%s',%d,[%F,%F,%F,%F]);", 'tcpdfdocsaved', 'text', 0, 0, 1, 0, 1);
			$jsb = "getField('tcpdfdocsaved').value='saved';";
			$this->javascript = $jsa."\n".$this->javascript."\n".$jsb;
		}
		// name tree for javascript
		$this->n_js = '<< /Names [';
		if (!empty($this->javascript)) {
			$this->n_js .= ' (EmbeddedJS) '.($this->n + 1).' 0 R';
		}
		if (!empty($this->js_objects)) {
			foreach ($this->js_objects as $key => $val) {
				if ($val['onload']) {
					$this->n_js .= ' (JS'.$key.') '.$key.' 0 R';
				}
			}
		}
		$this->n_js .= ' ] >>';
		// default Javascript object
		if (!empty($this->javascript)) {
			$obj_id = $this->_newobj();
			$out = '<< /S /JavaScript';
			$out .= ' /JS '.$this->_textstring($this->javascript, $obj_id);
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
		// additional Javascript objects
		if (!empty($this->js_objects)) {
			foreach ($this->js_objects as $key => $val) {
				$out = $this->_getobj($key)."\n".' << /S /JavaScript /JS '.$this->_textstring($val['js'], $key).' >>'."\n".'endobj';
				$this->_out($out);
			}
		}
    }

    /**
     * Add form field to PDF
     * MOVED FROM: protected function _addfield($type, $name, $x, $y, $w, $h, $prop)
     *
     * Adds a javascript form field.
	 * @param string $type field type
	 * @param string $name field name
	 * @param int $x horizontal position
	 * @param int $y vertical position
	 * @param int $w width
	 * @param int $h height
	 * @param array $prop javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @protected
	 * @author Denis Van Nuffelen, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
    public function addFormField($field_type, $field_name, $x, $y, $width, $height, $properties)
    {
		if ($this->rtl) {
			$x = $x - $w;
		}
		// the followind avoid fields duplication after saving the document
		$this->javascript .= "if (getField('tcpdfdocsaved').value != 'saved') {";
		$k = $this->k;
		$this->javascript .= sprintf("f".$field_name."=this.addField('%s','%s',%u,[%F,%F,%F,%F]);", $field_name, $field_type, $this->PageNo()-1, $x*$k, ($this->h-$y)*$k+1, ($x+$w)*$k, ($this->h-$y-$height)*$k+1)."\n";
		$this->javascript .= 'f'.$field_name.'.textSize='.$this->FontSizePt.";\n";
		foreach($properties as $key => $val) {
			if (strcmp(substr($key, -5), 'Color') == 0) {
				$val = TCPDF_COLORS::_JScolor($val);
			} else {
				$val = "'".$val."'";
			}
			$this->javascript .= 'f'.$name.'.'.$key.'='.$val.";\n";
		}
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
		$this->javascript .= '}';
	}



    /**
     * Add certification signature (DocMDP or UR3)
	 * You can set only one signature type
     * Put digital signature in PDF
     * MOVED FROM: protected function _putsignature()
     */
    public function putDigitalSignature()
    {
		if ((!$this->sign) OR (!isset($this->signature_data['cert_type']))) {
			return;
		}
		$sigobjid = ($this->sig_obj_id + 1);
		$out = $this->_getobj($sigobjid)."\n";
		$out .= '<< /Type /Sig';
		$out .= ' /Filter /Adobe.PPKLite';
		$out .= ' /SubFilter /adbe.pkcs7.detached';
		$out .= ' '.TCPDF_STATIC::$byterange_string;
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
				if (!TCPDF_STATIC::empty_string($this->ur['document'])) {
					$out .= ' /Document['.$this->ur['document'].']';
				}
				if (!TCPDF_STATIC::empty_string($this->ur['form'])) {
					$out .= ' /Form['.$this->ur['form'].']';
				}
				if (!TCPDF_STATIC::empty_string($this->ur['signature'])) {
					$out .= ' /Signature['.$this->ur['signature'].']';
				}
				if (!TCPDF_STATIC::empty_string($this->ur['annots'])) {
					$out .= ' /Annots['.$this->ur['annots'].']';
				}
				if (!TCPDF_STATIC::empty_string($this->ur['ef'])) {
					$out .= ' /EF['.$this->ur['ef'].']';
				}
				if (!TCPDF_STATIC::empty_string($this->ur['formex'])) {
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
		if (isset($this->signature_data['info']['Name']) AND !TCPDF_STATIC::empty_string($this->signature_data['info']['Name'])) {
			$out .= ' /Name '.$this->_textstring($this->signature_data['info']['Name'], $sigobjid);
		}
		if (isset($this->signature_data['info']['Location']) AND !TCPDF_STATIC::empty_string($this->signature_data['info']['Location'])) {
			$out .= ' /Location '.$this->_textstring($this->signature_data['info']['Location'], $sigobjid);
		}
		if (isset($this->signature_data['info']['Reason']) AND !TCPDF_STATIC::empty_string($this->signature_data['info']['Reason'])) {
			$out .= ' /Reason '.$this->_textstring($this->signature_data['info']['Reason'], $sigobjid);
		}
		if (isset($this->signature_data['info']['ContactInfo']) AND !TCPDF_STATIC::empty_string($this->signature_data['info']['ContactInfo'])) {
			$out .= ' /ContactInfo '.$this->_textstring($this->signature_data['info']['ContactInfo'], $sigobjid);
		}
		$out .= ' /M '.$this->_datestring($sigobjid, $this->doc_modification_timestamp);
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
    }

    /**
     * Get signature appearance array
     * MOVED FROM: protected function getSignatureAppearanceArray($x=0, $y=0, $w=0, $h=0, $page=-1, $name='')
     */
    public function getSignatureAppearance($x = 0, $y = 0, $width = 0, $height = 0, $page = -1, $signature_name = '')
    {
		$sigapp = array();
		if (($page < 1) OR ($page > $this->numpages)) {
			$sigapp['page'] = $this->page;
		} else {
			$sigapp['page'] = intval($page);
		}
		if (empty($signature_name)) {
			$sigapp['name'] = 'Signature';
		} else {
			$sigapp['name'] = $signature_name;
		}
		$a = $x * $this->k;
		$b = $this->pagedim[($sigapp['page'])]['h'] - (($y + $height) * $this->k);
		$c = $width * $this->k;
		$d = $height * $this->k;
		$sigapp['rect'] = sprintf('%F %F %F %F', $a, $b, ($a + $c), ($b + $d));
		return $sigapp;
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
     * Set signature configuration
     */
    // public function setSignature($signing_cert = '', $private_key = '', $private_key_password = '', $extracerts = '', $cert_type = 2, $info = array())
    // {
    //     // Public method to configure signature
    //     $this->signature_config = array(
    //         'signing_cert' => $signing_cert,
    //         'private_key' => $private_key,
    //         'private_key_password' => $private_key_password,
    //         'extracerts' => $extracerts,
    //         'cert_type' => $cert_type,
    //         'info' => $info
    //     );
    // }

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
	public function setSignatureAppearance($x=0, $y=0, $width=0, $height=0, $page=-1, $signature_name='') {
		$this->signature_appearance = $this->getSignatureAppearance($x, $y, $width, $height, $page, $signature_name);
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
	public function addEmptySignatureAppearance($x=0, $y=0, $width=0, $height=0, $page=-1, $signature_name='') {
		++$this->n;
		$this->empty_signature_appearance[] = array('objid' => $this->n) + $this->getSignatureAppearance($x, $y, $width, $height, $page, $signature_name);
	}


    /**
     * Apply Time Stamp Authority to signature
     * MOVED FROM: protected function applyTSA($signature)
     */
    public function applyTimeStampAuthority($signature_data)
    {
		if (!$this->applyTimeStampAuthority) {
			return $signature;
		}
		//@TODO: implement this feature
		return $signature;
    }

    /**
     * Put Optional Content Groups (layers)
     * MOVED FROM: protected function _putocg()
     */
    public function putOptionalContentGroups()
    {
		if (empty($this->pdflayers)) {
			return;
		}
		foreach ($this->pdflayers as $key => $layer) {
			 $this->pdflayers[$key]['objid'] = $this->_newobj();
			 $out = '<< /Type /OCG';
			 $out .= ' /Name '.$this->_textstring($layer['name'], $this->pdflayers[$key]['objid']);
			 $out .= ' /Usage <<';
			 if (isset($layer['print']) AND ($layer['print'] !== NULL)) {
				$out .= ' /Print <</PrintState /'.($layer['print']?'ON':'OFF').'>>';
			 }
			 $out .= ' /View <</ViewState /'.($layer['view']?'ON':'OFF').'>>';
			 $out .= ' >> >>';
			 $out .= "\n".'endobj';
			 $this->_out($out);
		}
    }

    /**
     * Add bookmark to collection
     */
    public function addBookmark($title, $level = 0, $y = -1, $page = '', $style = '', $color = array(0, 0, 0))
    {
        // Public method to add bookmarks
        // This would be called from main TCPDF class
        $this->bookmarks[] = array(
            'title' => $title,
            'level' => $level,
            'y' => $y,
            'page' => $page,
            'style' => $style,
            'color' => $color
        );
    }

    /**
     * Add JavaScript code to PDF
     */
    public function addJavaScript($javascript_code)
    {
        // Public method to add JavaScript
        $this->javascript_code .= $javascript_code . "\n";
    }

    /**
     * Add destination for internal links
     */
    public function addDestination($name, $x = '', $y = '', $page = '', $zoom = null)
    {
        // Public method to add link destinations
        $this->destinations[$name] = array(
            'x' => $x,
            'y' => $y, 
            'page' => $page,
            'zoom' => $zoom
        );
    }

    /**
     * Check if any bookmarks are defined
     */
    public function hasBookmarks()
    {
        return !empty($this->bookmarks);
    }

    /**
     * Check if JavaScript is defined
     */
    public function hasJavaScript()
    {
        return !empty($this->javascript_code);
    }

    /**
     * Check if form fields are defined
     */
    public function hasFormFields()
    {
        return !empty($this->form_fields);
    }

    /**
     * Check if signature is configured
     */
    public function hasSignature()
    {
        return !empty($this->signature_config);
    }

    /**
     * Get bookmarks count
     */
    public function getBookmarksCount()
    {
        return count($this->bookmarks);
    }

    /**
     * Get form fields count
     */
    public function getFormFieldsCount()
    {
        return count($this->form_fields);
    }

    /**
     * Initialize advanced features state
     */
    private function initializeAdvancedFeatures()
    {
        $this->bookmarks = array();
        $this->form_fields = array();
        $this->javascript_code = '';
        $this->signature_config = array();
        $this->destinations = array();
    }

    /**
     * Clean up advanced features state
     */
    public function resetAdvancedFeatures()
    {
        $this->initializeAdvancedFeatures();
    }

    /**
     * Validate form field properties
     */
    private function validateFormFieldProperties($properties)
    {
        // Helper method to validate form field configuration
        return is_array($properties);
    }

    /**
     * Generate unique form field ID
     */
    private function generateFormFieldId($field_name)
    {
        // Helper method to generate unique field identifiers
        return 'field_' . md5($field_name . microtime());
    }
}

/*


ADDITIONAL PUBLIC METHODS TO ADD:
================================

10. addBookmark() - Public interface for adding bookmarks
11. addJavaScript() - Public interface for adding JavaScript
12. setSignature() - Public interface for configuring signatures
13. addDestination() - Public interface for adding link destinations

INTEGRATION POINTS:
==================

Main TCPDF class changes needed:
- Add lazy loading property: private ?AdvancedFeaturesManager $advancedFeatures = null;
- Add getter: private function getAdvancedFeatures(): AdvancedFeaturesManager { return $this->advancedFeatures ??= new AdvancedFeaturesManager($this); }
- Update advanced feature calls to use: $this->getAdvancedFeatures()->methodName()
- Keep advanced feature properties in main class for backward compatibility

EXAMPLE USAGE IN MAIN CLASS:
============================

class TCPDF {
    private ?AdvancedFeaturesManager $advancedFeatures = null;
    
    private function getAdvancedFeatures(): AdvancedFeaturesManager {
        return $this->advancedFeatures ??= new AdvancedFeaturesManager($this);
    }
    
    // Public methods that trigger lazy loading
    public function Bookmark($txt, $level=0, $y=-1, $page='', $style='', $color=array(0,0,0)) {
        return $this->getAdvancedFeatures()->addBookmark($txt, $level, $y, $page, $style, $color);
    }
    
    public function IncludeJS($script) {
        return $this->getAdvancedFeatures()->addJavaScript($script);
    }
    
    public function setSignature($signing_cert='', $private_key='', $private_key_password='', $extracerts='', $cert_type=2, $info=array()) {
        return $this->getAdvancedFeatures()->setSignature($signing_cert, $private_key, $private_key_password, $extracerts, $cert_type, $info);
    }
    
    // Internal method delegation
    protected function _putbookmarks() {
        if ($this->advancedFeatures && $this->advancedFeatures->hasBookmarks()) {
            return $this->getAdvancedFeatures()->putBookmarks();
        }
    }
    
    protected function _putjavascript() {
        if ($this->advancedFeatures && $this->advancedFeatures->hasJavaScript()) {
            return $this->getAdvancedFeatures()->putJavaScript();
        }
    }
}
*/