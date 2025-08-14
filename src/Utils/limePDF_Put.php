<?php

namespace LimePDF;

trait LIMEPDF_PUT {

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
	 * Put encryption on PDF document.
	 * @protected
	 * @author Nicola Asuni
	 * @since 2.0.000 (2008-01-02)
	 */
	protected function _putencryption() {
		if (!$this->encrypted) {
			return;
		}
		$this->encryptdata['objid'] = $this->_newobj();
		$out = '<<';
		if (!isset($this->encryptdata['Filter']) OR empty($this->encryptdata['Filter'])) {
			$this->encryptdata['Filter'] = 'Standard';
		}
		$out .= ' /Filter /'.$this->encryptdata['Filter'];
		if (isset($this->encryptdata['SubFilter']) AND !empty($this->encryptdata['SubFilter'])) {
			$out .= ' /SubFilter /'.$this->encryptdata['SubFilter'];
		}
		if (!isset($this->encryptdata['V']) OR empty($this->encryptdata['V'])) {
			$this->encryptdata['V'] = 1;
		}
		// V is a code specifying the algorithm to be used in encrypting and decrypting the document
		$out .= ' /V '.$this->encryptdata['V'];
		if (isset($this->encryptdata['Length']) AND !empty($this->encryptdata['Length'])) {
			// The length of the encryption key, in bits. The value shall be a multiple of 8, in the range 40 to 256
			$out .= ' /Length '.$this->encryptdata['Length'];
		} else {
			$out .= ' /Length 40';
		}
		if ($this->encryptdata['V'] >= 4) {
			if (!isset($this->encryptdata['StmF']) OR empty($this->encryptdata['StmF'])) {
				$this->encryptdata['StmF'] = 'Identity';
			}
			if (!isset($this->encryptdata['StrF']) OR empty($this->encryptdata['StrF'])) {
				// The name of the crypt filter that shall be used when decrypting all strings in the document.
				$this->encryptdata['StrF'] = 'Identity';
			}
			// A dictionary whose keys shall be crypt filter names and whose values shall be the corresponding crypt filter dictionaries.
			if (isset($this->encryptdata['CF']) AND !empty($this->encryptdata['CF'])) {
				$out .= ' /CF <<';
				$out .= ' /'.$this->encryptdata['StmF'].' <<';
				$out .= ' /Type /CryptFilter';
				if (isset($this->encryptdata['CF']['CFM']) AND !empty($this->encryptdata['CF']['CFM'])) {
					// The method used
					$out .= ' /CFM /'.$this->encryptdata['CF']['CFM'];
					if ($this->encryptdata['pubkey']) {
						$out .= ' /Recipients [';
						foreach ($this->encryptdata['Recipients'] as $rec) {
							$out .= ' <'.$rec.'>';
						}
						$out .= ' ]';
						if (isset($this->encryptdata['CF']['EncryptMetadata']) AND (!$this->encryptdata['CF']['EncryptMetadata'])) {
							$out .= ' /EncryptMetadata false';
						} else {
							$out .= ' /EncryptMetadata true';
						}
					}
				} else {
					$out .= ' /CFM /None';
				}
				if (isset($this->encryptdata['CF']['AuthEvent']) AND !empty($this->encryptdata['CF']['AuthEvent'])) {
					// The event to be used to trigger the authorization that is required to access encryption keys used by this filter.
					$out .= ' /AuthEvent /'.$this->encryptdata['CF']['AuthEvent'];
				} else {
					$out .= ' /AuthEvent /DocOpen';
				}
				if (isset($this->encryptdata['CF']['Length']) AND !empty($this->encryptdata['CF']['Length'])) {
					// The bit length of the encryption key.
					$out .= ' /Length '.$this->encryptdata['CF']['Length'];
				}
				$out .= ' >> >>';
			}
			// The name of the crypt filter that shall be used by default when decrypting streams.
			$out .= ' /StmF /'.$this->encryptdata['StmF'];
			// The name of the crypt filter that shall be used when decrypting all strings in the document.
			$out .= ' /StrF /'.$this->encryptdata['StrF'];
			if (isset($this->encryptdata['EFF']) AND !empty($this->encryptdata['EFF'])) {
				// The name of the crypt filter that shall be used when encrypting embedded file streams that do not have their own crypt filter specifier.
				$out .= ' /EFF /'.$this->encryptdata[''];
			}
		}
		// Additional encryption dictionary entries for the standard security handler
		if ($this->encryptdata['pubkey']) {
			if (($this->encryptdata['V'] < 4) AND isset($this->encryptdata['Recipients']) AND !empty($this->encryptdata['Recipients'])) {
				$out .= ' /Recipients [';
				foreach ($this->encryptdata['Recipients'] as $rec) {
					$out .= ' <'.$rec.'>';
				}
				$out .= ' ]';
			}
		} else {
			$out .= ' /R';
			if ($this->encryptdata['V'] == 5) { // AES-256
				$out .= ' 5';
				$out .= ' /OE ('.TCPDF_STATIC::_escape($this->encryptdata['OE']).')';
				$out .= ' /UE ('.TCPDF_STATIC::_escape($this->encryptdata['UE']).')';
				$out .= ' /Perms ('.TCPDF_STATIC::_escape($this->encryptdata['perms']).')';
			} elseif ($this->encryptdata['V'] == 4) { // AES-128
				$out .= ' 4';
			} elseif ($this->encryptdata['V'] < 2) { // RC-40
				$out .= ' 2';
			} else { // RC-128
				$out .= ' 3';
			}
			$out .= ' /O ('.TCPDF_STATIC::_escape($this->encryptdata['O']).')';
			$out .= ' /U ('.TCPDF_STATIC::_escape($this->encryptdata['U']).')';
			$out .= ' /P '.$this->encryptdata['P'];
			if (isset($this->encryptdata['EncryptMetadata']) AND (!$this->encryptdata['EncryptMetadata'])) {
				$out .= ' /EncryptMetadata false';
			} else {
				$out .= ' /EncryptMetadata true';
			}
		}
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
	}

	/**
	 * Embedd the attached files.
	 * @since 4.4.000 (2008-12-07)
	 * @protected
	 * @see Annotation()
	 */
	protected function _putEmbeddedFiles() {
		if ($this->pdfa_mode && $this->pdfa_version != 3)  {
			// embedded files are not allowed in PDF/A mode version 1 and 2
			return;
		}
		reset($this->embeddedfiles);
		foreach ($this->embeddedfiles as $filename => $filedata) {
			$data = false;
			if (isset($filedata['file']) && !empty($filedata['file'])) {
				$data = $this->getCachedFileContents($filedata['file']);
			} elseif ($filedata['content'] && !empty($filedata['content'])) {
				$data = $filedata['content'];
			}
			if ($data !== FALSE) {
				$rawsize = strlen($data);
				if ($rawsize > 0) {
					// update name tree
					$this->efnames[$filename] = $filedata['f'].' 0 R';
					// embedded file specification object
					$out = $this->_getobj($filedata['f'])."\n";
					$out .= '<</Type /Filespec /F '.$this->_datastring($filename, $filedata['f']);
					$out .= ' /UF '.$this->_datastring($filename, $filedata['f']);
					$out .= ' /AFRelationship /Source';
					$out .= ' /EF <</F '.$filedata['n'].' 0 R>> >>';
					$out .= "\n".'endobj';
					$this->_out($out);
					// embedded file object
					$filter = '';
					if ($this->compress) {
						$data = gzcompress($data);
						$filter = ' /Filter /FlateDecode';
					}

					if ($this->pdfa_version == 3) {
						$filter = ' /Subtype /text#2Fxml';
					}

					$stream = $this->_getrawstream($data, $filedata['n']);
					$out = $this->_getobj($filedata['n'])."\n";
					$out .= '<< /Type /EmbeddedFile'.$filter.' /Length '.strlen($stream).' /Params <</Size '.$rawsize.'>> >>';
					$out .= ' stream'."\n".$stream."\n".'endstream';
					$out .= "\n".'endobj';
					$this->_out($out);
				}
			}
		}
	}

	/**
	 * Output Spot Colors Resources.
	 * @protected
	 * @since 4.0.024 (2008-09-12)
	 */
	protected function _putspotcolors() {
		foreach ($this->spot_colors as $name => $color) {
			$this->_newobj();
			$this->spot_colors[$name]['n'] = $this->n;
			$out = '[/Separation /'.str_replace(' ', '#20', $name);
			$out .= ' /DeviceCMYK <<';
			$out .= ' /Range [0 1 0 1 0 1 0 1] /C0 [0 0 0 0]';
			$out .= ' '.sprintf('/C1 [%F %F %F %F] ', ($color['C'] / 100), ($color['M'] / 100), ($color['Y'] / 100), ($color['K'] / 100));
			$out .= ' /FunctionType 2 /Domain [0 1] /N 1>>]';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
	}

	/**
	 * Output images.
	 * @protected
	 */
	protected function _putimages() {
		$filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
		foreach ($this->imagekeys as $file) {
			$info = $this->getImageBuffer($file);
			// set object for alternate images array
			$altoid = null;
			if ((!$this->pdfa_mode) AND isset($info['altimgs']) AND !empty($info['altimgs'])) {
				$altoid = $this->_newobj();
				$out = '[';
				foreach ($info['altimgs'] as $altimage) {
					if (isset($this->xobjects['I'.$altimage[0]]['n'])) {
						$out .= ' << /Image '.$this->xobjects['I'.$altimage[0]]['n'].' 0 R';
						$out .= ' /DefaultForPrinting';
						if ($altimage[1] === true) {
							$out .= ' true';
						} else {
							$out .= ' false';
						}
						$out .= ' >>';
					}
				}
				$out .= ' ]';
				$out .= "\n".'endobj';
				$this->_out($out);
			}
			// set image object
			$oid = $this->_newobj();
			$this->xobjects['I'.$info['i']] = array('n' => $oid);
			$this->setImageSubBuffer($file, 'n', $this->n);
			$out = '<</Type /XObject';
			$out .= ' /Subtype /Image';
			$out .= ' /Width '.$info['w'];
			$out .= ' /Height '.$info['h'];
			if (array_key_exists('masked', $info)) {
				$out .= ' /SMask '.($this->n - 1).' 0 R';
			}
			// set color space
			$icc = false;
			if (isset($info['icc']) AND ($info['icc'] !== false)) {
				// ICC Colour Space
				$icc = true;
				$out .= ' /ColorSpace [/ICCBased '.($this->n + 1).' 0 R]';
			} elseif ($info['cs'] == 'Indexed') {
				// Indexed Colour Space
				$out .= ' /ColorSpace [/Indexed /DeviceRGB '.((strlen($info['pal']) / 3) - 1).' '.($this->n + 1).' 0 R]';
			} else {
				// Device Colour Space
				$out .= ' /ColorSpace /'.$info['cs'];
			}
			if ($info['cs'] == 'DeviceCMYK') {
				$out .= ' /Decode [1 0 1 0 1 0 1 0]';
			}
			$out .= ' /BitsPerComponent '.$info['bpc'];
			if ($altoid > 0) {
				// reference to alternate images dictionary
				$out .= ' /Alternates '.$altoid.' 0 R';
			}
			if (isset($info['exurl']) AND !empty($info['exurl'])) {
				// external stream
				$out .= ' /Length 0';
				$out .= ' /F << /FS /URL /F '.$this->_datastring($info['exurl'], $oid).' >>';
				if (isset($info['f'])) {
					$out .= ' /FFilter /'.$info['f'];
				}
				$out .= ' >>';
				$out .= ' stream'."\n".'endstream';
			} else {
				if (isset($info['f'])) {
					$out .= ' /Filter /'.$info['f'];
				}
				if (isset($info['parms'])) {
					$out .= ' '.$info['parms'];
				}
				if (isset($info['trns']) AND is_array($info['trns'])) {
					$trns = '';
					$count_info = count($info['trns']);
					if ($info['cs'] == 'Indexed') {
						$maxval =(pow(2, $info['bpc']) - 1);
						for ($i = 0; $i < $count_info; ++$i) {
							if (($info['trns'][$i] != 0) AND ($info['trns'][$i] != $maxval)) {
								// this is not a binary type mask @TODO: create a SMask
								$trns = '';
								break;
							} elseif (empty($trns) AND ($info['trns'][$i] == 0)) {
								// store the first fully transparent value
								$trns .= $i.' '.$i.' ';
							}
						}
					} else {
						// grayscale or RGB
						for ($i = 0; $i < $count_info; ++$i) {
							if ($info['trns'][$i] == 0) {
								$trns .= $info['trns'][$i].' '.$info['trns'][$i].' ';
							}
						}
					}
					// Colour Key Masking
					if (!empty($trns)) {
						$out .= ' /Mask ['.$trns.']';
					}
				}
				$stream = $this->_getrawstream($info['data']);
				$out .= ' /Length '.strlen($stream).' >>';
				$out .= ' stream'."\n".$stream."\n".'endstream';
			}
			$out .= "\n".'endobj';
			$this->_out($out);
			if ($icc) {
				// ICC colour profile
				$this->_newobj();
				$icc = ($this->compress) ? gzcompress($info['icc']) : $info['icc'];
				$icc = $this->_getrawstream($icc);
				$this->_out('<</N '.$info['ch'].' /Alternate /'.$info['cs'].' '.$filter.'/Length '.strlen($icc).'>> stream'."\n".$icc."\n".'endstream'."\n".'endobj');
			} elseif ($info['cs'] == 'Indexed') {
				// colour palette
				$this->_newobj();
				$pal = ($this->compress) ? gzcompress($info['pal']) : $info['pal'];
				$pal = $this->_getrawstream($pal);
				$this->_out('<<'.$filter.'/Length '.strlen($pal).'>> stream'."\n".$pal."\n".'endstream'."\n".'endobj');
			}
		}
	}
	

	/**
	 * Output fonts.
	 * @author Nicola Asuni
	 * @protected
	 */
	protected function _putfonts() {
		$nf = $this->n;
		foreach ($this->diffs as $diff) {
			//Encodings
			$this->_newobj();
			$this->_out('<< /Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.'] >>'."\n".'endobj');
		}
		foreach ($this->FontFiles as $file => $info) {
			// search and get font file to embedd
			$fontfile = LIMEPDF_FONT::getFontFullPath($file, $info['fontdir']);
			if (!LIMEPDF_STATIC::empty_string($fontfile)) {
				$font = file_get_contents($fontfile);
				$compressed = (substr($file, -2) == '.z');
				if ((!$compressed) AND (isset($info['length2']))) {
					$header = (ord($font[0]) == 128);
					if ($header) {
						// strip first binary header
						$font = substr($font, 6);
					}
					if ($header AND (ord($font[$info['length1']]) == 128)) {
						// strip second binary header
						$font = substr($font, 0, $info['length1']).substr($font, ($info['length1'] + 6));
					}
				} elseif ($info['subset'] AND ((!$compressed) OR ($compressed AND function_exists('gzcompress')))) {
					if ($compressed) {
						// uncompress font
						$font = gzuncompress($font);
					}
					// merge subset characters
					$subsetchars = array(); // used chars
					foreach ($info['fontkeys'] as $fontkey) {
						$fontinfo = $this->getFontBuffer($fontkey);
						$subsetchars += $fontinfo['subsetchars'];
					}
					// rebuild a font subset
					$font = LIMEPDF_FONT::_getTrueTypeFontSubset($font, $subsetchars);
					// calculate new font length
					$info['length1'] = strlen($font);
					if ($compressed) {
						// recompress font
						$font = gzcompress($font);
					}
				}
				$this->_newobj();
				$this->FontFiles[$file]['n'] = $this->n;
				$stream = $this->_getrawstream($font);
				$out = '<< /Length '.strlen($stream);
				if ($compressed) {
					$out .= ' /Filter /FlateDecode';
				}
				$out .= ' /Length1 '.$info['length1'];
				if (isset($info['length2'])) {
					$out .= ' /Length2 '.$info['length2'].' /Length3 0';
				}
				$out .= ' >>';
				$out .= ' stream'."\n".$stream."\n".'endstream';
				$out .= "\n".'endobj';
				$this->_out($out);
			}
		}
		foreach ($this->fontkeys as $k) {
			//Font objects
			$font = $this->getFontBuffer($k);
			$type = $font['type'];
			$name = $font['name'];
			if ($type == 'core') {
				// standard core font
				$out = $this->_getobj($this->font_obj_ids[$k])."\n";
				$out .= '<</Type /Font';
				$out .= ' /Subtype /Type1';
				$out .= ' /BaseFont /'.$name;
				$out .= ' /Name /F'.$font['i'];
				if ((strtolower($name) != 'symbol') AND (strtolower($name) != 'zapfdingbats')) {
					$out .= ' /Encoding /WinAnsiEncoding';
				}
				if ($k == 'helvetica') {
					// add default font for annotations
					$this->annotation_fonts[$k] = $font['i'];
				}
				$out .= ' >>';
				$out .= "\n".'endobj';
				$this->_out($out);
			} elseif (($type == 'Type1') OR ($type == 'TrueType')) {
				// additional Type1 or TrueType font
				$out = $this->_getobj($this->font_obj_ids[$k])."\n";
				$out .= '<</Type /Font';
				$out .= ' /Subtype /'.$type;
				$out .= ' /BaseFont /'.$name;
				$out .= ' /Name /F'.$font['i'];
				$out .= ' /FirstChar 32 /LastChar 255';
				$out .= ' /Widths '.($this->n + 1).' 0 R';
				$out .= ' /FontDescriptor '.($this->n + 2).' 0 R';
				if ($font['enc']) {
					if (isset($font['diff'])) {
						$out .= ' /Encoding '.($nf + $font['diff']).' 0 R';
					} else {
						$out .= ' /Encoding /WinAnsiEncoding';
					}
				}
				$out .= ' >>';
				$out .= "\n".'endobj';
				$this->_out($out);
				// Widths
				$this->_newobj();
				$s = '[';
				for ($i = 32; $i < 256; ++$i) {
					if (isset($font['cw'][$i])) {
						$s .= $font['cw'][$i].' ';
					} else {
						$s .= $font['dw'].' ';
					}
				}
				$s .= ']';
				$s .= "\n".'endobj';
				$this->_out($s);
				//Descriptor
				$this->_newobj();
				$s = '<</Type /FontDescriptor /FontName /'.$name;
				foreach ($font['desc'] as $fdk => $fdv) {
					if (is_float($fdv)) {
						$fdv = sprintf('%F', $fdv);
					}
					$s .= ' /'.$fdk.' '.$fdv.'';
				}
				if (!TCPDF_STATIC::empty_string($font['file'])) {
					$s .= ' /FontFile'.($type == 'Type1' ? '' : '2').' '.$this->FontFiles[$font['file']]['n'].' 0 R';
				}
				$s .= '>>';
				$s .= "\n".'endobj';
				$this->_out($s);
			} else {
				// additional types
				$mtd = '_put'.strtolower($type);
				if (!method_exists($this, $mtd)) {
					$this->Error('Unsupported font type: '.$type);
				}
				$this->$mtd($font);
			}
		}
	}	
	/**
	 * Put pdf layers.
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function _putocg() {
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
	 * Put extgstates for object transparency
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function _putextgstates() {
		foreach ($this->extgstates as $i => $ext) {
			$this->extgstates[$i]['n'] = $this->_newobj();
			$out = '<< /Type /ExtGState';
			foreach ($ext['parms'] as $k => $v) {
				if (is_float($v)) {
					$v = sprintf('%F', $v);
				} elseif ($v === true) {
					$v = 'true';
				} elseif ($v === false) {
					$v = 'false';
				}
				$out .= ' /'.$k.' '.$v;
			}
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
	}	

	/**
	 * Output Resources Dictionary.
	 * @protected
	 */
	protected function _putresourcedict() {
		$out = $this->_getobj(2)."\n";
		$out .= '<< /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]';
		$out .= ' /Font <<';
		foreach ($this->fontkeys as $fontkey) {
			$font = $this->getFontBuffer($fontkey);
			$out .= ' /F'.$font['i'].' '.$font['n'].' 0 R';
		}
		$out .= ' >>';
		$out .= ' /XObject <<';
		$out .= $this->_getxobjectdict();
		$out .= ' >>';
		// layers
		if (!empty($this->pdflayers)) {
			$out .= ' /Properties <<';
			foreach ($this->pdflayers as $layer) {
				$out .= ' /'.$layer['layer'].' '.$layer['objid'].' 0 R';
			}
			$out .= ' >>';
		}
		if (!$this->pdfa_mode || $this->pdfa_version >= 2) {
			// transparency
			if (isset($this->extgstates) AND !empty($this->extgstates)) {
				$out .= ' /ExtGState <<';
				foreach ($this->extgstates as $k => $extgstate) {
					if (isset($extgstate['name'])) {
						$out .= ' /'.$extgstate['name'];
					} else {
						$out .= ' /GS'.$k;
					}
					$out .= ' '.$extgstate['n'].' 0 R';
				}
				$out .= ' >>';
			}
			if (isset($this->gradients) AND !empty($this->gradients)) {
				$gp = '';
				$gs = '';
				foreach ($this->gradients as $id => $grad) {
					// gradient patterns
					$gp .= ' /p'.$id.' '.$grad['pattern'].' 0 R';
					// gradient shadings
					$gs .= ' /Sh'.$id.' '.$grad['id'].' 0 R';
				}
				$out .= ' /Pattern <<'.$gp.' >>';
				$out .= ' /Shading <<'.$gs.' >>';
			}
		}
		// spot colors
		if (isset($this->spot_colors) AND !empty($this->spot_colors)) {
			$out .= ' /ColorSpace <<';
			foreach ($this->spot_colors as $color) {
				$out .= ' /CS'.$color['i'].' '.$color['n'].' 0 R';
			}
			$out .= ' >>';
		}
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
	}	

	/**
	 * Insert Named Destinations.
	 * @protected
	 * @author Johannes G\FCntert, Nicola Asuni
	 * @since 5.9.098 (2011-06-23)
	 */
	protected function _putdests() {
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
	 * Create a bookmark PDF string.
	 * @protected
	 * @author Olivier Plathey, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	protected function _putbookmarks() {
		$nb = count($this->outlines);
		if ($nb == 0) {
			return;
		}
		// sort bookmarks
		$this->sortBookmarks();
		$lru = array();
		$level = 0;
		foreach ($this->outlines as $i => $o) {
			if ($o['l'] > 0) {
				$parent = $lru[($o['l'] - 1)];
				//Set parent and last pointers
				$this->outlines[$i]['parent'] = $parent;
				$this->outlines[$parent]['last'] = $i;
				if ($o['l'] > $level) {
					//Level increasing: set first pointer
					$this->outlines[$parent]['first'] = $i;
				}
			} else {
				$this->outlines[$i]['parent'] = $nb;
			}
			if (($o['l'] <= $level) AND ($i > 0)) {
				//Set prev and next pointers
				$prev = $lru[$o['l']];
				$this->outlines[$prev]['next'] = $i;
				$this->outlines[$i]['prev'] = $prev;
			}
			$lru[$o['l']] = $i;
			$level = $o['l'];
		}
		//Outline items
		$n = $this->n + 1;
		$nltags = '/<br[\s]?\/>|<\/(blockquote|dd|dl|div|dt|h1|h2|h3|h4|h5|h6|hr|li|ol|p|pre|ul|tcpdf|table|tr|td)>/si';
		foreach ($this->outlines as $i => $o) {
			$oid = $this->_newobj();
			// covert HTML title to string
			$title = preg_replace($nltags, "\n", $o['t']);
			$title = preg_replace("/[\r]+/si", '', $title);
			$title = preg_replace("/[\n]+/si", "\n", $title);
			$title = strip_tags($title);
			$title = $this->stringTrim($title);
			$out = '<</Title '.$this->_textstring($title, $oid);
			$out .= ' /Parent '.($n + $o['parent']).' 0 R';
			if (isset($o['prev'])) {
				$out .= ' /Prev '.($n + $o['prev']).' 0 R';
			}
			if (isset($o['next'])) {
				$out .= ' /Next '.($n + $o['next']).' 0 R';
			}
			if (isset($o['first'])) {
				$out .= ' /First '.($n + $o['first']).' 0 R';
			}
			if (isset($o['last'])) {
				$out .= ' /Last '.($n + $o['last']).' 0 R';
			}
			if (isset($o['u']) AND !empty($o['u'])) {
				// link
				if (is_string($o['u'])) {
					if ($o['u'][0] == '#') {
						// internal destination
						$out .= ' /Dest /'.TCPDF_STATIC::encodeNameObject(substr($o['u'], 1));
					} elseif ($o['u'][0] == '%') {
						// embedded PDF file
						$filename = basename(substr($o['u'], 1));
						$out .= ' /A <</S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P '.($o['p'] - 1).' /A '.$this->embeddedfiles[$filename]['a'].' >> >>';
					} elseif ($o['u'][0] == '*') {
						// embedded generic file
						$filename = basename(substr($o['u'], 1));
						$jsa = 'var D=event.target.doc;var MyData=D.dataObjects;for (var i in MyData) if (MyData[i].path=="'.$filename.'") D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
						$out .= ' /A <</S /JavaScript /JS '.$this->_textstring($jsa, $oid).'>>';
					} else {
						// external URI link
						$out .= ' /A <</S /URI /URI '.$this->_datastring($this->unhtmlentities($o['u']), $oid).'>>';
					}
				} elseif (isset($this->links[$o['u']])) {
					// internal link ID
					$l = $this->links[$o['u']];
					if (isset($this->page_obj_id[($l['p'])])) {
						$out .= sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $this->page_obj_id[($l['p'])], ($this->pagedim[$l['p']]['h'] - ($l['y'] * $this->k)));
					}
				}
			} elseif (isset($this->page_obj_id[($o['p'])])) {
				// link to a page
				$out .= ' '.sprintf('/Dest [%u 0 R /XYZ %F %F null]', $this->page_obj_id[($o['p'])], ($o['x'] * $this->k), ($this->pagedim[$o['p']]['h'] - ($o['y'] * $this->k)));
			}
			// set font style
			$style = 0;
			if (!empty($o['s'])) {
				// bold
				if (strpos($o['s'], 'B') !== false) {
					$style |= 2;
				}
				// oblique
				if (strpos($o['s'], 'I') !== false) {
					$style |= 1;
				}
			}
			$out .= sprintf(' /F %d', $style);
			// set bookmark color
			if (isset($o['c']) AND is_array($o['c']) AND (count($o['c']) == 3)) {
				$color = array_values($o['c']);
				$out .= sprintf(' /C [%F %F %F]', ($color[0] / 255), ($color[1] / 255), ($color[2] / 255));
			} else {
				// black
				$out .= ' /C [0.0 0.0 0.0]';
			}
			$out .= ' /Count 0'; // normally closed item
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
		//Outline root
		$this->OutlineRoot = $this->_newobj();
		$this->_out('<< /Type /Outlines /First '.$n.' 0 R /Last '.($n + $lru[0]).' 0 R >>'."\n".'endobj');
	}

	/**
	 * Output gradient shaders.
	 * @author Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @protected
	 */
	function _putshaders() {
		if ($this->pdfa_mode && $this->pdfa_version < 2) {
			return;
		}
		$idt = count($this->gradients); //index for transparency gradients
		foreach ($this->gradients as $id => $grad) {
			if (($grad['type'] == 2) OR ($grad['type'] == 3)) {
				$fc = $this->_newobj();
				$out = '<<';
				$out .= ' /FunctionType 3';
				$out .= ' /Domain [0 1]';
				$functions = '';
				$bounds = '';
				$encode = '';
				$i = 1;
				$num_cols = count($grad['colors']);
				$lastcols = $num_cols - 1;
				for ($i = 1; $i < $num_cols; ++$i) {
					$functions .= ($fc + $i).' 0 R ';
					if ($i < $lastcols) {
						$bounds .= sprintf('%F ', $grad['colors'][$i]['offset']);
					}
					$encode .= '0 1 ';
				}
				$out .= ' /Functions ['.trim($functions).']';
				$out .= ' /Bounds ['.trim($bounds).']';
				$out .= ' /Encode ['.trim($encode).']';
				$out .= ' >>';
				$out .= "\n".'endobj';
				$this->_out($out);
				for ($i = 1; $i < $num_cols; ++$i) {
					$this->_newobj();
					$out = '<<';
					$out .= ' /FunctionType 2';
					$out .= ' /Domain [0 1]';
					$out .= ' /C0 ['.$grad['colors'][($i - 1)]['color'].']';
					$out .= ' /C1 ['.$grad['colors'][$i]['color'].']';
					$out .= ' /N '.$grad['colors'][$i]['exponent'];
					$out .= ' >>';
					$out .= "\n".'endobj';
					$this->_out($out);
				}
				// set transparency functions
				if ($grad['transparency']) {
					$ft = $this->_newobj();
					$out = '<<';
					$out .= ' /FunctionType 3';
					$out .= ' /Domain [0 1]';
					$functions = '';
					$i = 1;
					$num_cols = count($grad['colors']);
					for ($i = 1; $i < $num_cols; ++$i) {
						$functions .= ($ft + $i).' 0 R ';
					}
					$out .= ' /Functions ['.trim($functions).']';
					$out .= ' /Bounds ['.trim($bounds).']';
					$out .= ' /Encode ['.trim($encode).']';
					$out .= ' >>';
					$out .= "\n".'endobj';
					$this->_out($out);
					for ($i = 1; $i < $num_cols; ++$i) {
						$this->_newobj();
						$out = '<<';
						$out .= ' /FunctionType 2';
						$out .= ' /Domain [0 1]';
						$out .= ' /C0 ['.$grad['colors'][($i - 1)]['opacity'].']';
						$out .= ' /C1 ['.$grad['colors'][$i]['opacity'].']';
						$out .= ' /N '.$grad['colors'][$i]['exponent'];
						$out .= ' >>';
						$out .= "\n".'endobj';
						$this->_out($out);
					}
				}
			}
			// set shading object
			$this->_newobj();
			$out = '<< /ShadingType '.$grad['type'];
			if (isset($grad['colspace'])) {
				$out .= ' /ColorSpace /'.$grad['colspace'];
			} else {
				$out .= ' /ColorSpace /DeviceRGB';
			}
			if (isset($grad['background']) AND !empty($grad['background'])) {
				$out .= ' /Background ['.$grad['background'].']';
			}
			if (isset($grad['antialias']) AND ($grad['antialias'] === true)) {
				$out .= ' /AntiAlias true';
			}
			if ($grad['type'] == 2) {
				$out .= ' '.sprintf('/Coords [%F %F %F %F]', $grad['coords'][0], $grad['coords'][1], $grad['coords'][2], $grad['coords'][3]);
				$out .= ' /Domain [0 1]';
				$out .= ' /Function '.$fc.' 0 R';
				$out .= ' /Extend [true true]';
				$out .= ' >>';
			} elseif ($grad['type'] == 3) {
				//x0, y0, r0, x1, y1, r1
				//at this this time radius of inner circle is 0
				$out .= ' '.sprintf('/Coords [%F %F 0 %F %F %F]', $grad['coords'][0], $grad['coords'][1], $grad['coords'][2], $grad['coords'][3], $grad['coords'][4]);
				$out .= ' /Domain [0 1]';
				$out .= ' /Function '.$fc.' 0 R';
				$out .= ' /Extend [true true]';
				$out .= ' >>';
			} elseif ($grad['type'] == 6) {
				$out .= ' /BitsPerCoordinate 16';
				$out .= ' /BitsPerComponent 8';
				$out .= ' /Decode[0 1 0 1 0 1 0 1 0 1]';
				$out .= ' /BitsPerFlag 8';
				$stream = $this->_getrawstream($grad['stream']);
				$out .= ' /Length '.strlen($stream);
				$out .= ' >>';
				$out .= ' stream'."\n".$stream."\n".'endstream';
			}
			$out .= "\n".'endobj';
			$this->_out($out);
			if ($grad['transparency']) {
				$shading_transparency = preg_replace('/\/ColorSpace \/[^\s]+/si', '/ColorSpace /DeviceGray', $out);
				$shading_transparency = preg_replace('/\/Function [0-9]+ /si', '/Function '.$ft.' ', $shading_transparency);
			}
			$this->gradients[$id]['id'] = $this->n;
			// set pattern object
			$this->_newobj();
			$out = '<< /Type /Pattern /PatternType 2';
			$out .= ' /Shading '.$this->gradients[$id]['id'].' 0 R';
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
			$this->gradients[$id]['pattern'] = $this->n;
			// set shading and pattern for transparency mask
			if ($grad['transparency']) {
				// luminosity pattern
				$idgs = $id + $idt;
				$this->_newobj();
				$this->_out($shading_transparency);
				$this->gradients[$idgs]['id'] = $this->n;
				$this->_newobj();
				$out = '<< /Type /Pattern /PatternType 2';
				$out .= ' /Shading '.$this->gradients[$idgs]['id'].' 0 R';
				$out .= ' >>';
				$out .= "\n".'endobj';
				$this->_out($out);
				$this->gradients[$idgs]['pattern'] = $this->n;
				// luminosity XObject
				$oid = $this->_newobj();
				$this->xobjects['LX'.$oid] = array('n' => $oid);
				$filter = '';
				$stream = 'q /a0 gs /Pattern cs /p'.$idgs.' scn 0 0 '.$this->wPt.' '.$this->hPt.' re f Q';
				if ($this->compress) {
					$filter = ' /Filter /FlateDecode';
					$stream = gzcompress($stream);
				}
				$stream = $this->_getrawstream($stream);
				$out = '<< /Type /XObject /Subtype /Form /FormType 1'.$filter;
				$out .= ' /Length '.strlen($stream);
				$rect = sprintf('%F %F', $this->wPt, $this->hPt);
				$out .= ' /BBox [0 0 '.$rect.']';
				$out .= ' /Group << /Type /Group /S /Transparency /CS /DeviceGray >>';
				$out .= ' /Resources <<';
				$out .= ' /ExtGState << /a0 << /ca 1 /CA 1 >> >>';
				$out .= ' /Pattern << /p'.$idgs.' '.$this->gradients[$idgs]['pattern'].' 0 R >>';
				$out .= ' >>';
				$out .= ' >> ';
				$out .= ' stream'."\n".$stream."\n".'endstream';
				$out .= "\n".'endobj';
				$this->_out($out);
				// SMask
				$this->_newobj();
				$out = '<< /Type /Mask /S /Luminosity /G '.($this->n - 1).' 0 R >>'."\n".'endobj';
				$this->_out($out);
				// ExtGState
				$this->_newobj();
				$out = '<< /Type /ExtGState /SMask '.($this->n - 1).' 0 R /AIS false >>'."\n".'endobj';
				$this->_out($out);
				$this->extgstates[] = array('n' => $this->n, 'name' => 'TGS'.$id);
			}
		}
	}
	   	/**
	 * Output Form XObjects Templates.
	 * @author Nicola Asuni
	 * @since 5.8.017 (2010-08-24)
	 * @protected
	 * @see startTemplate(), endTemplate(), printTemplate()
	 */
	protected function _putxobjects() {
		foreach ($this->xobjects as $key => $data) {
			if (isset($data['outdata'])) {
				$stream = str_replace($this->epsmarker, '', trim($data['outdata']));
				$out = $this->_getobj($data['n'])."\n";
				$out .= '<<';
				$out .= ' /Type /XObject';
				$out .= ' /Subtype /Form';
				$out .= ' /FormType 1';
				if ($this->compress) {
					$stream = gzcompress($stream);
					$out .= ' /Filter /FlateDecode';
				}
				$out .= sprintf(' /BBox [%F %F %F %F]', ($data['x'] * $this->k), (-$data['y'] * $this->k), (($data['w'] + $data['x']) * $this->k), (($data['h'] - $data['y']) * $this->k));
				$out .= ' /Matrix [1 0 0 1 0 0]';
				$out .= ' /Resources <<';
				$out .= ' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]';
				if (!$this->pdfa_mode || $this->pdfa_version >= 2) {
					// transparency
					if (isset($data['extgstates']) AND !empty($data['extgstates'])) {
						$out .= ' /ExtGState <<';
						foreach ($data['extgstates'] as $k => $extgstate) {
							if (isset($this->extgstates[$k]['name'])) {
								$out .= ' /'.$this->extgstates[$k]['name'];
							} else {
								$out .= ' /GS'.$k;
							}
							$out .= ' '.$this->extgstates[$k]['n'].' 0 R';
						}
						$out .= ' >>';
					}
					if (isset($data['gradients']) AND !empty($data['gradients'])) {
						$gp = '';
						$gs = '';
						foreach ($data['gradients'] as $id => $grad) {
							// gradient patterns
							$gp .= ' /p'.$id.' '.$this->gradients[$id]['pattern'].' 0 R';
							// gradient shadings
							$gs .= ' /Sh'.$id.' '.$this->gradients[$id]['id'].' 0 R';
						}
						$out .= ' /Pattern <<'.$gp.' >>';
						$out .= ' /Shading <<'.$gs.' >>';
					}
				}
				// spot colors
				if (isset($data['spot_colors']) AND !empty($data['spot_colors'])) {
					$out .= ' /ColorSpace <<';
					foreach ($data['spot_colors'] as $name => $color) {
						$out .= ' /CS'.$color['i'].' '.$this->spot_colors[$name]['n'].' 0 R';
					}
					$out .= ' >>';
				}
				// fonts
				if (!empty($data['fonts'])) {
					$out .= ' /Font <<';
					foreach ($data['fonts'] as $fontkey => $fontid) {
						$out .= ' /F'.$fontid.' '.$this->font_obj_ids[$fontkey].' 0 R';
					}
					$out .= ' >>';
				}
				// images or nested xobjects
				if (!empty($data['images']) OR !empty($data['xobjects'])) {
					$out .= ' /XObject <<';
					foreach ($data['images'] as $imgid) {
						$out .= ' /I'.$imgid.' '.$this->xobjects['I'.$imgid]['n'].' 0 R';
					}
					foreach ($data['xobjects'] as $sub_id => $sub_objid) {
						$out .= ' /'.$sub_id.' '.$sub_objid['n'].' 0 R';
					}
					$out .= ' >>';
				}
				$out .= ' >>'; //end resources
				if (isset($data['group']) AND ($data['group'] !== false)) {
					// set transparency group
					$out .= ' /Group << /Type /Group /S /Transparency';
					if (is_array($data['group'])) {
						if (isset($data['group']['CS']) AND !empty($data['group']['CS'])) {
							$out .= ' /CS /'.$data['group']['CS'];
						}
						if (isset($data['group']['I'])) {
							$out .= ' /I /'.($data['group']['I']===true?'true':'false');
						}
						if (isset($data['group']['K'])) {
							$out .= ' /K /'.($data['group']['K']===true?'true':'false');
						}
					}
					$out .= ' >>';
				}
				$stream = $this->_getrawstream($stream, $data['n']);
				$out .= ' /Length '.strlen($stream);
				$out .= ' >>';
				$out .= ' stream'."\n".$stream."\n".'endstream';
				$out .= "\n".'endobj';
				$this->_out($out);
			}
		}
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

}