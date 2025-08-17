<?php

namespace LimePDF\View;

use LimePDF\Support\StaticTrait;

trait OutputTrait {

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
			$byterange_string_len = strlen($this->$byterange_string);
			// define the ByteRange
			$byte_range = array();
			$byte_range[0] = 0;
			$byte_range[1] = strpos($pdfdoc, $this->$byterange_string) + $byterange_string_len + 10;
			$byte_range[2] = $byte_range[1] + $this->signature_max_length + 2;
			$byte_range[3] = strlen($pdfdoc) - $byte_range[2];
			$pdfdoc = substr($pdfdoc, 0, $byte_range[1]).substr($pdfdoc, $byte_range[2]);
			// replace the ByteRange
			$byterange = sprintf('/ByteRange[0 %u %u %u]', $byte_range[1], $byte_range[2], $byte_range[3]);
			$byterange .= str_repeat(' ', ($byterange_string_len - strlen($byterange)));
			$pdfdoc = str_replace($this->$byterange_string, $byterange, $pdfdoc);
			// write the document to a temporary folder
			$tempdoc = $this->getObjFilename('doc', $this->file_id);
			$f = $this->fopenLocal($tempdoc, 'wb');
			if (!$f) {
				$this->Error('Unable to create temporary file: '.$tempdoc);
			}
			$pdfdoc_length = strlen($pdfdoc);
			fwrite($f, $pdfdoc, $pdfdoc_length);
			fclose($f);
			// get digital signature via openssl library
			$tempsign = $this->getObjFilename('sig', $this->file_id);
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
					$this->sendOutputData($this->getBuffer(), $this->bufferlen);
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
				$this->sendOutputData($this->getBuffer(), $this->bufferlen);
				break;
			}
			case 'F':
			case 'FI':
			case 'FD': {
				// save PDF to a local file
				$f = $this->fopenLocal($name, 'wb');
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
					$this->sendOutputData(file_get_contents($name), filesize($name));
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
					$this->sendOutputData(file_get_contents($name), filesize($name));
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
		if (isset($this->LayoutMode) AND (!$this->empty_string($this->LayoutMode))) {
			$out .= ' /PageLayout /'.$this->LayoutMode;
		}
		if (isset($this->PageMode) AND (!$this->empty_string($this->PageMode))) {
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
		if (isset($vp['Duplex']) AND (!$this->empty_string($vp['Duplex']))) {
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

}