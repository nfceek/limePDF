<?php

    class limePDF_Put {

        public static function putExtGStates(TCPDF $tcpdf) {
            $extgstates = &$tcpdf->getExtGStates(); // get reference

            foreach ($extgstates as $i => &$ext) {
                $ext['n'] = $tcpdf->_newobj();
                $out = '<< /Type /ExtGState';
                foreach ($ext['parms'] as $k => $v) {
                    if (is_float($v)) {
                        $v = sprintf('%F', $v);
                    } elseif ($v === true) {
                        $v = 'true';
                    } elseif ($v === false) {
                        $v = 'false';
                    }
                    $out .= ' /' . $k . ' ' . $v;
                }
                $out .= ' >>' . "\n" . 'endobj';
                $tcpdf->_out($out);
            }
        }

        public static function putOcg(TCPDF $tcpdf) {
            $pdflayers = &$tcpdf->getPdfLayers();
            if (empty($pdflayers)) {
                return;
            }
            foreach ($pdflayers as $key => $layer) {
                $pdflayers[$key]['objid'] = $tcpdf->_newobj();

                $out = '<< /Type /OCG';
                $out .= ' /Name ' . $tcpdf->_textstring($layer['name'], $pdflayers[$key]['objid']);
                $out .= ' /Usage <<';
                    if (isset($layer['print']) && $layer['print'] !== null) {
                        $out .= ' /Print <</PrintState /' . ($layer['print'] ? 'ON' : 'OFF') . '>>';
                    }
                $out .= ' /View <</ViewState /' . ($layer['view'] ? 'ON' : 'OFF') . '>>';
                $out .= ' >> >>' . "\n" . 'endobj';

                $tcpdf->_out($out);
            }
        }

        public static function putSpotColors(TCPDF $tcpdf) {
            $spot_colors = $tcpdf->getSpotColors();

            foreach ($spot_colors as $name => $color) {
                $this->_newobj();
                $this->spot_colors[$name]['n'] = $this->n;
                $out = '[/Separation /'.str_replace(' ', '#20', $name);
                $out .= ' /DeviceCMYK <<';
                $out .= ' /Range [0 1 0 1 0 1 0 1] /C0 [0 0 0 0]';
                $out .= ' '.sprintf('/C1 [%F %F %F %F] ', ($color['C'] / 100), ($color['M'] / 100), ($color['Y'] / 100), ($color['K'] / 100));
                $out .= ' /FunctionType 2 /Domain [0 1] /N 1>>]';
                $out .= "\n".'endobj';

                $tcpdf->_out($out);
            }
        }

        public function putEmbeddedFiles($tcpdf) {
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

        public function putImages(TCPDF $tcpdf) {
            $filter = ($tcpdf->compress) ? '/Filter /FlateDecode ' : '';

            foreach ($tcpdf->imagekeys as $file) {
                $info = $tcpdf->getImageBuffer($file);
                // set object for alternate images array
                $altoid = null;
                if ((!$tcpdf->pdfa_mode) AND isset($info['altimgs']) AND !empty($info['altimgs'])) {
                    $altoid = $tcpdf->_newobj();
                    $out = '[';
                    foreach ($info['altimgs'] as $altimage) {
                        if (isset($tcpdf->xobjects['I'.$altimage[0]]['n'])) {
                            $out .= ' << /Image '.$tcpdf->xobjects['I'.$altimage[0]]['n'].' 0 R';
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
                $currentObjectId = $tcpdf->getCurrentObjectId();
                $oid = $tcpdf->_newobj();
                $tcpdf->xobjects['I'.$info['i']] = array('n' => $oid);

                $tcpdf->setImageSubBuffer($file, 'n', $tcpdf->getObjectId());
                //$tcpdf->setImageSubBuffer($file, 'n', $tcpdf->n);
                $out = '<</Type /XObject';
                $out .= ' /Subtype /Image';
                $out .= ' /Width '.$info['w'];
                $out .= ' /Height '.$info['h'];
                if (array_key_exists('masked', $info)) {
                    $out .= ' /SMask '.($tcpdf->getObjectId() - 1).' 0 R';
                }
                // set color space
                $icc = false;
                if (isset($info['icc']) AND ($info['icc'] !== false)) {
                    // ICC Colour Space
                    $icc = true;
                    $out .= ' /ColorSpace [/ICCBased '.($tcpdf->getObjectId() + 1).' 0 R]';
                } elseif ($info['cs'] == 'Indexed') {
                    // Indexed Colour Space
                    $out .= ' /ColorSpace [/Indexed /DeviceRGB '.((strlen($info['pal']) / 3) - 1).' '.($tcpdf->getObjectId() + 1).' 0 R]';
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
                    $out .= ' /F << /FS /URL /F '.$tcpdf->_datastring($info['exurl'], $oid).' >>';
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
                    $stream = $tcpdf->getRawStream($info['data']); 
                    //$stream = $tcpdf->_getrawstream($info['data']);
                    $out .= ' /Length '.strlen($stream).' >>';
                    $out .= ' stream'."\n".$stream."\n".'endstream';
                }
                $out .= "\n".'endobj';
                $tcpdf->_out($out);
                if ($icc) {
                    // ICC colour profile
                    $tcpdf->_newobj();
                    $icc = ($tcpdf->compress) ? gzcompress($info['icc']) : $info['icc'];
                    $icc = $tcpdf->_getrawstream($icc);
                    $tcpdf->_out('<</N '.$info['ch'].' /Alternate /'.$info['cs'].' '.$filter.'/Length '.strlen($icc).'>> stream'."\n".$icc."\n".'endstream'."\n".'endobj');
                } elseif ($info['cs'] == 'Indexed') {
                    // colour palette
                    $tcpdf->_newobj();
                    $pal = ($tcpdf->compress) ? gzcompress($info['pal']) : $info['pal'];
                    $pal = $tcpdf->_getrawstream($pal);
                    $tcpdf->_out('<<'.$filter.'/Length '.strlen($pal).'>> stream'."\n".$pal."\n".'endstream'."\n".'endobj');
                }
            }
        }

        public function putFonts(TCPDF $tcpdf) {
            $nf = $tcpdf->getObjectId();
            $diffs = $tcpdf->getDiffs();
            $FontFiles = $tcpdf->getFontFiles();
            $fontkeys = $tcpdf->getFontKeys();

            foreach ($diffs as $diff) {
                //Encodings
                $tcpdf->_newobj();
                $tcpdf->_out('<< /Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.'] >>'."\n".'endobj');
            }
            foreach ($FontFiles as $file => $info) {
                // search and get font file to embedd
                $fontfile = TCPDF_FONTS::getFontFullPath($file, $info['fontdir']);
                if (!TCPDF_STATIC::empty_string($fontfile)) {
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
                            $fontinfo = $tcpdf->getFontBuffer($fontkey);
                            $subsetchars += $fontinfo['subsetchars'];
                        }
                        // rebuild a font subset
                        $font = TCPDF_FONTS::_getTrueTypeFontSubset($font, $subsetchars);
                        // calculate new font length
                        $info['length1'] = strlen($font);
                        if ($compressed) {
                            // recompress font
                            $font = gzcompress($font);
                        }
                    }
                    $tcpdf->_newobj();
                    $tcpdf->setFontFileN($file, $tcpdf->getN());
                    $stream = $tcpdf->_getrawstream($font);
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
                    $tcpdf->_out($out);
                }
            }
            foreach ($fontkeys as $k) {
                //Font objects
                $font = $tcpdf->getFontBuffer($k);
                $type = $font['type'];
                $name = $font['name'];
                if ($type == 'core') {
                    // standard core font
                    $obj_id = $tcpdf->getFontObjId($k);
                    $out = $tcpdf->_getobj($obj_id) . "\n";
                    $out .= '<</Type /Font';
                    $out .= ' /Subtype /Type1';
                    $out .= ' /BaseFont /'.$name;
                    $out .= ' /Name /F'.$font['i'];
                    if ((strtolower($name) != 'symbol') AND (strtolower($name) != 'zapfdingbats')) {
                        $out .= ' /Encoding /WinAnsiEncoding';
                    }
                    if ($k == 'helvetica') {
                        // add default font for annotations
                        $annotation_fonts = $tcpdf->getAnnotationFonts($k);
                        $annotation_fonts[$k] = $font['i'];
                    }
                    $out .= ' >>';
                    $out .= "\n".'endobj';
                    $tcpdf->_out($out);
                } elseif (($type == 'Type1') OR ($type == 'TrueType')) {
                    // additional Type1 or TrueType font
                    $obj_id = $tcpdf->getFontObjId($k);
                    $out = $tcpdf->_getobj($obj_id) . "\n";
                    $out .= '<</Type /Font';
                    $out .= ' /Subtype /'.$type;
                    $out .= ' /BaseFont /'.$name;
                    $out .= ' /Name /F'.$font['i'];
                    $out .= ' /FirstChar 32 /LastChar 255';
                    $out .= ' /Widths '.($tcpdf->n + 1).' 0 R';
                    $out .= ' /FontDescriptor '.($tcpdf->n + 2).' 0 R';
                    if ($font['enc']) {
                        if (isset($font['diff'])) {
                            $out .= ' /Encoding '.($nf + $font['diff']).' 0 R';
                        } else {
                            $out .= ' /Encoding /WinAnsiEncoding';
                        }
                    }
                    $out .= ' >>';
                    $out .= "\n".'endobj';
                    $tcpdf->_out($out);
                    // Widths
                    $tcpdf->_newobj();
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
                    $tcpdf->_out($s);
                    //Descriptor
                    $tcpdf->_newobj();
                    $s = '<</Type /FontDescriptor /FontName /'.$name;
                    foreach ($font['desc'] as $fdk => $fdv) {
                        if (is_float($fdv)) {
                            $fdv = sprintf('%F', $fdv);
                        }
                        $s .= ' /'.$fdk.' '.$fdv.'';
                    }
                    if (!TCPDF_STATIC::empty_string($font['file'])) {
                        $s .= ' /FontFile'.($type == 'Type1' ? '' : '2').' '.$FontFiles[$font['file']]['n'].' 0 R';
                    }
                    $s .= '>>';
                    $s .= "\n".'endobj';
                    $tcpdf->_out($s);
                } else {
                    // additional types
                    $mtd = '_put'.strtolower($type);
                    if (!method_exists($tcpdf, $mtd)) {
                        $tcpdf->Error('Unsupported font type: '.$type);
                    }
                    $tcpdf->$mtd($font);
                }
            }
        }

    // TODO - FIX THIS FUNCTION TO GET ENCRYPTION TO WORK
    // AND move it to utils/encrypt
	// public function putEncryption() {
	// 	if (!$this->encrypted) {
	// 		return;
	// 	}
	// 	$this->encryptdata['objid'] = $this->_newobj();
	// 	$out = '<<';
	// 	if (!isset($this->encryptdata['Filter']) OR empty($this->encryptdata['Filter'])) {
	// 		$this->encryptdata['Filter'] = 'Standard';
	// 	}
	// 	$out .= ' /Filter /'.$this->encryptdata['Filter'];
	// 	if (isset($this->encryptdata['SubFilter']) AND !empty($this->encryptdata['SubFilter'])) {
	// 		$out .= ' /SubFilter /'.$this->encryptdata['SubFilter'];
	// 	}
	// 	if (!isset($this->encryptdata['V']) OR empty($this->encryptdata['V'])) {
	// 		$this->encryptdata['V'] = 1;
	// 	}
	// 	// V is a code specifying the algorithm to be used in encrypting and decrypting the document
	// 	$out .= ' /V '.$this->encryptdata['V'];
	// 	if (isset($this->encryptdata['Length']) AND !empty($this->encryptdata['Length'])) {
	// 		// The length of the encryption key, in bits. The value shall be a multiple of 8, in the range 40 to 256
	// 		$out .= ' /Length '.$this->encryptdata['Length'];
	// 	} else {
	// 		$out .= ' /Length 40';
	// 	}
	// 	if ($this->encryptdata['V'] >= 4) {
	// 		if (!isset($this->encryptdata['StmF']) OR empty($this->encryptdata['StmF'])) {
	// 			$this->encryptdata['StmF'] = 'Identity';
	// 		}
	// 		if (!isset($this->encryptdata['StrF']) OR empty($this->encryptdata['StrF'])) {
	// 			// The name of the crypt filter that shall be used when decrypting all strings in the document.
	// 			$this->encryptdata['StrF'] = 'Identity';
	// 		}
	// 		// A dictionary whose keys shall be crypt filter names and whose values shall be the corresponding crypt filter dictionaries.
	// 		if (isset($this->encryptdata['CF']) AND !empty($this->encryptdata['CF'])) {
	// 			$out .= ' /CF <<';
	// 			$out .= ' /'.$this->encryptdata['StmF'].' <<';
	// 			$out .= ' /Type /CryptFilter';
	// 			if (isset($this->encryptdata['CF']['CFM']) AND !empty($this->encryptdata['CF']['CFM'])) {
	// 				// The method used
	// 				$out .= ' /CFM /'.$this->encryptdata['CF']['CFM'];
	// 				if ($this->encryptdata['pubkey']) {
	// 					$out .= ' /Recipients [';
	// 					foreach ($this->encryptdata['Recipients'] as $rec) {
	// 						$out .= ' <'.$rec.'>';
	// 					}
	// 					$out .= ' ]';
	// 					if (isset($this->encryptdata['CF']['EncryptMetadata']) AND (!$this->encryptdata['CF']['EncryptMetadata'])) {
	// 						$out .= ' /EncryptMetadata false';
	// 					} else {
	// 						$out .= ' /EncryptMetadata true';
	// 					}
	// 				}
	// 			} else {
	// 				$out .= ' /CFM /None';
	// 			}
	// 			if (isset($this->encryptdata['CF']['AuthEvent']) AND !empty($this->encryptdata['CF']['AuthEvent'])) {
	// 				// The event to be used to trigger the authorization that is required to access encryption keys used by this filter.
	// 				$out .= ' /AuthEvent /'.$this->encryptdata['CF']['AuthEvent'];
	// 			} else {
	// 				$out .= ' /AuthEvent /DocOpen';
	// 			}
	// 			if (isset($this->encryptdata['CF']['Length']) AND !empty($this->encryptdata['CF']['Length'])) {
	// 				// The bit length of the encryption key.
	// 				$out .= ' /Length '.$this->encryptdata['CF']['Length'];
	// 			}
	// 			$out .= ' >> >>';
	// 		}
	// 		// The name of the crypt filter that shall be used by default when decrypting streams.
	// 		$out .= ' /StmF /'.$this->encryptdata['StmF'];
	// 		// The name of the crypt filter that shall be used when decrypting all strings in the document.
	// 		$out .= ' /StrF /'.$this->encryptdata['StrF'];
	// 		if (isset($this->encryptdata['EFF']) AND !empty($this->encryptdata['EFF'])) {
	// 			// The name of the crypt filter that shall be used when encrypting embedded file streams that do not have their own crypt filter specifier.
	// 			$out .= ' /EFF /'.$this->encryptdata[''];
	// 		}
	// 	}
	// 	// Additional encryption dictionary entries for the standard security handler
	// 	if ($this->encryptdata['pubkey']) {
	// 		if (($this->encryptdata['V'] < 4) AND isset($this->encryptdata['Recipients']) AND !empty($this->encryptdata['Recipients'])) {
	// 			$out .= ' /Recipients [';
	// 			foreach ($this->encryptdata['Recipients'] as $rec) {
	// 				$out .= ' <'.$rec.'>';
	// 			}
	// 			$out .= ' ]';
	// 		}
	// 	} else {
	// 		$out .= ' /R';
	// 		if ($this->encryptdata['V'] == 5) { // AES-256
	// 			$out .= ' 5';
	// 			$out .= ' /OE ('.TCPDF_STATIC::_escape($this->encryptdata['OE']).')';
	// 			$out .= ' /UE ('.TCPDF_STATIC::_escape($this->encryptdata['UE']).')';
	// 			$out .= ' /Perms ('.TCPDF_STATIC::_escape($this->encryptdata['perms']).')';
	// 		} elseif ($this->encryptdata['V'] == 4) { // AES-128
	// 			$out .= ' 4';
	// 		} elseif ($this->encryptdata['V'] < 2) { // RC-40
	// 			$out .= ' 2';
	// 		} else { // RC-128
	// 			$out .= ' 3';
	// 		}
	// 		$out .= ' /O ('.TCPDF_STATIC::_escape($this->encryptdata['O']).')';
	// 		$out .= ' /U ('.TCPDF_STATIC::_escape($this->encryptdata['U']).')';
	// 		$out .= ' /P '.$this->encryptdata['P'];
	// 		if (isset($this->encryptdata['EncryptMetadata']) AND (!$this->encryptdata['EncryptMetadata'])) {
	// 			$out .= ' /EncryptMetadata false';
	// 		} else {
	// 			$out .= ' /EncryptMetadata true';
	// 		}
	// 	}
	// 	$out .= ' >>';
	// 	$out .= "\n".'endobj';
	// 	$this->_out($out);
	// }

        /**
         * Output annotations objects for all pages.
         * !!! THIS METHOD IS NOT YET COMPLETED !!!
         * See section 12.5 of PDF 32000_2008 reference.
         * @protected
         * @author Nicola Asuni
         * @since 4.0.018 (2008-08-06)
         */
        // public function _putannotsobjs() {
        //     // reset object counter
        //     for ($n=1; $n <= $this->numpages; ++$n) {
        //         if (isset($this->PageAnnots[$n])) {
        //             // set page annotations
        //             foreach ($this->PageAnnots[$n] as $key => $pl) {
        //                 $annot_obj_id = $this->PageAnnots[$n][$key]['n'];
        //                 // create annotation object for grouping radiobuttons
        //                 if (isset($this->radiobutton_groups[$n][$pl['txt']]) AND is_array($this->radiobutton_groups[$n][$pl['txt']])) {
        //                     $radio_button_obj_id = $this->radiobutton_groups[$n][$pl['txt']]['n'];
        //                     $annots = '<<';
        //                     $annots .= ' /Type /Annot';
        //                     $annots .= ' /Subtype /Widget';
        //                     $annots .= ' /Rect [0 0 0 0]';
        //                     if ($this->radiobutton_groups[$n][$pl['txt']]['#readonly#']) {
        //                         // read only
        //                         $annots .= ' /F 68';
        //                         $annots .= ' /Ff 49153';
        //                     } else {
        //                         $annots .= ' /F 4'; // default print for PDF/A
        //                         $annots .= ' /Ff 49152';
        //                     }
        //                     $annots .= ' /T '.$this->_datastring($pl['txt'], $radio_button_obj_id);
        //                     if (isset($pl['opt']['tu']) AND is_string($pl['opt']['tu'])) {
        //                         $annots .= ' /TU '.$this->_datastring($pl['opt']['tu'], $radio_button_obj_id);
        //                     }
        //                     $annots .= ' /FT /Btn';
        //                     $annots .= ' /Kids [';
        //                     $defval = '';
        //                     foreach ($this->radiobutton_groups[$n][$pl['txt']] as $key => $data) {
        //                         if (isset($data['kid'])) {
        //                             $annots .= ' '.$data['kid'].' 0 R';
        //                             if ($data['def'] !== 'Off') {
        //                                 $defval = $data['def'];
        //                             }
        //                         }
        //                     }
        //                     $annots .= ' ]';
        //                     if (!empty($defval)) {
        //                         $annots .= ' /V /'.$defval;
        //                     }
        //                     $annots .= ' >>';
        //                     $this->_out($this->_getobj($radio_button_obj_id)."\n".$annots."\n".'endobj');
        //                     $this->form_obj_id[] = $radio_button_obj_id;
        //                     // store object id to be used on Parent entry of Kids
        //                     $this->radiobutton_groups[$n][$pl['txt']] = $radio_button_obj_id;
        //                 }
        //                 $formfield = false;
        //                 $pl['opt'] = array_change_key_case($pl['opt'], CASE_LOWER);
        //                 $a = $pl['x'] * $this->k;
        //                 $b = $this->pagedim[$n]['h'] - (($pl['y'] + $pl['h']) * $this->k);
        //                 $c = $pl['w'] * $this->k;
        //                 $d = $pl['h'] * $this->k;
        //                 $rect = sprintf('%F %F %F %F', $a, $b, $a+$c, $b+$d);
        //                 // create new annotation object
        //                 $annots = '<</Type /Annot';
        //                 $annots .= ' /Subtype /'.$pl['opt']['subtype'];
        //                 $annots .= ' /Rect ['.$rect.']';
        //                 $ft = array('Btn', 'Tx', 'Ch', 'Sig');
        //                 if (isset($pl['opt']['ft']) AND in_array($pl['opt']['ft'], $ft)) {
        //                     $annots .= ' /FT /'.$pl['opt']['ft'];
        //                     $formfield = true;
        //                 }
        //                 if ($pl['opt']['subtype'] !== 'Link') {
        //                     $annots .= ' /Contents '.$this->_textstring($pl['txt'], $annot_obj_id);
        //                 }
        //                 $annots .= ' /P '.$this->page_obj_id[$n].' 0 R';
        //                 $annots .= ' /NM '.$this->_datastring(sprintf('%04u-%04u', $n, $key), $annot_obj_id);
        //                 $annots .= ' /M '.$this->_datestring($annot_obj_id, $this->doc_modification_timestamp);
        //                 if (isset($pl['opt']['f'])) {
        //                     $fval = 0;
        //                     if (is_array($pl['opt']['f'])) {
        //                         foreach ($pl['opt']['f'] as $f) {
        //                             switch (strtolower($f)) {
        //                                 case 'invisible': {
        //                                     $fval += 1 << 0;
        //                                     break;
        //                                 }
        //                                 case 'hidden': {
        //                                     $fval += 1 << 1;
        //                                     break;
        //                                 }
        //                                 case 'print': {
        //                                     $fval += 1 << 2;
        //                                     break;
        //                                 }
        //                                 case 'nozoom': {
        //                                     $fval += 1 << 3;
        //                                     break;
        //                                 }
        //                                 case 'norotate': {
        //                                     $fval += 1 << 4;
        //                                     break;
        //                                 }
        //                                 case 'noview': {
        //                                     $fval += 1 << 5;
        //                                     break;
        //                                 }
        //                                 case 'readonly': {
        //                                     $fval += 1 << 6;
        //                                     break;
        //                                 }
        //                                 case 'locked': {
        //                                     $fval += 1 << 7;
        //                                     break;
        //                                 }
        //                                 case 'togglenoview': {
        //                                     $fval += 1 << 8;
        //                                     break;
        //                                 }
        //                                 case 'lockedcontents': {
        //                                     $fval += 1 << 9;
        //                                     break;
        //                                 }
        //                                 default: {
        //                                     break;
        //                                 }
        //                             }
        //                         }
        //                     } else {
        //                         $fval = intval($pl['opt']['f']);
        //                     }
        //                 } else {
        //                     $fval = 4;
        //                 }
        //                 if ($this->pdfa_mode) {
        //                     // force print flag for PDF/A mode
        //                     $fval |= 4;
        //                 }
        //                 $annots .= ' /F '.intval($fval);
        //                 if (isset($pl['opt']['as']) AND is_string($pl['opt']['as'])) {
        //                     $annots .= ' /AS /'.$pl['opt']['as'];
        //                 }
        //                 if (isset($pl['opt']['ap'])) {
        //                     // appearance stream
        //                     $annots .= ' /AP <<';
        //                     if (is_array($pl['opt']['ap'])) {
        //                         foreach ($pl['opt']['ap'] as $apmode => $apdef) {
        //                             // $apmode can be: n = normal; r = rollover; d = down;
        //                             $annots .= ' /'.strtoupper($apmode);
        //                             if (is_array($apdef)) {
        //                                 $annots .= ' <<';
        //                                 foreach ($apdef as $apstate => $stream) {
        //                                     // reference to XObject that define the appearance for this mode-state
        //                                     $apsobjid = $this->_putAPXObject($c, $d, $stream);
        //                                     $annots .= ' /'.$apstate.' '.$apsobjid.' 0 R';
        //                                 }
        //                                 $annots .= ' >>';
        //                             } else {
        //                                 // reference to XObject that define the appearance for this mode
        //                                 $apsobjid = $this->_putAPXObject($c, $d, $apdef);
        //                                 $annots .= ' '.$apsobjid.' 0 R';
        //                             }
        //                         }
        //                     } else {
        //                         $annots .= $pl['opt']['ap'];
        //                     }
        //                     $annots .= ' >>';
        //                 }
        //                 if (isset($pl['opt']['bs']) AND (is_array($pl['opt']['bs']))) {
        //                     $annots .= ' /BS <<';
        //                     $annots .= ' /Type /Border';
        //                     if (isset($pl['opt']['bs']['w'])) {
        //                         $annots .= ' /W '.intval($pl['opt']['bs']['w']);
        //                     }
        //                     $bstyles = array('S', 'D', 'B', 'I', 'U');
        //                     if (isset($pl['opt']['bs']['s']) AND in_array($pl['opt']['bs']['s'], $bstyles)) {
        //                         $annots .= ' /S /'.$pl['opt']['bs']['s'];
        //                     }
        //                     if (isset($pl['opt']['bs']['d']) AND (is_array($pl['opt']['bs']['d']))) {
        //                         $annots .= ' /D [';
        //                         foreach ($pl['opt']['bs']['d'] as $cord) {
        //                             $annots .= ' '.intval($cord);
        //                         }
        //                         $annots .= ']';
        //                     }
        //                     $annots .= ' >>';
        //                 } else {
        //                     $annots .= ' /Border [';
        //                     if (isset($pl['opt']['border']) AND (count($pl['opt']['border']) >= 3)) {
        //                         $annots .= intval($pl['opt']['border'][0]).' ';
        //                         $annots .= intval($pl['opt']['border'][1]).' ';
        //                         $annots .= intval($pl['opt']['border'][2]);
        //                         if (isset($pl['opt']['border'][3]) AND is_array($pl['opt']['border'][3])) {
        //                             $annots .= ' [';
        //                             foreach ($pl['opt']['border'][3] as $dash) {
        //                                 $annots .= intval($dash).' ';
        //                             }
        //                             $annots .= ']';
        //                         }
        //                     } else {
        //                         $annots .= '0 0 0';
        //                     }
        //                     $annots .= ']';
        //                 }
        //                 if (isset($pl['opt']['be']) AND (is_array($pl['opt']['be']))) {
        //                     $annots .= ' /BE <<';
        //                     $bstyles = array('S', 'C');
        //                     if (isset($pl['opt']['be']['s']) AND in_array($pl['opt']['be']['s'], $bstyles)) {
        //                         $annots .= ' /S /'.$pl['opt']['bs']['s'];
        //                     } else {
        //                         $annots .= ' /S /S';
        //                     }
        //                     if (isset($pl['opt']['be']['i']) AND ($pl['opt']['be']['i'] >= 0) AND ($pl['opt']['be']['i'] <= 2)) {
        //                         $annots .= ' /I '.sprintf(' %F', $pl['opt']['be']['i']);
        //                     }
        //                     $annots .= '>>';
        //                 }
        //                 if (isset($pl['opt']['c']) AND (is_array($pl['opt']['c'])) AND !empty($pl['opt']['c'])) {
        //                     $annots .= ' /C '.TCPDF_COLORS::getColorStringFromArray($pl['opt']['c']);
        //                 }
        //                 //$annots .= ' /StructParent ';
        //                 //$annots .= ' /OC ';
        //                 $markups = array('text', 'freetext', 'line', 'square', 'circle', 'polygon', 'polyline', 'highlight', 'underline', 'squiggly', 'strikeout', 'stamp', 'caret', 'ink', 'fileattachment', 'sound');
        //                 if (in_array(strtolower($pl['opt']['subtype']), $markups)) {
        //                     // this is a markup type
        //                     if (isset($pl['opt']['t']) AND is_string($pl['opt']['t'])) {
        //                         $annots .= ' /T '.$this->_textstring($pl['opt']['t'], $annot_obj_id);
        //                     }
        //                     //$annots .= ' /Popup ';
        //                     if (isset($pl['opt']['ca'])) {
        //                         $annots .= ' /CA '.sprintf('%F', floatval($pl['opt']['ca']));
        //                     }
        //                     if (isset($pl['opt']['rc'])) {
        //                         $annots .= ' /RC '.$this->_textstring($pl['opt']['rc'], $annot_obj_id);
        //                     }
        //                     $annots .= ' /CreationDate '.$this->_datestring($annot_obj_id, $this->doc_creation_timestamp);
        //                     //$annots .= ' /IRT ';
        //                     if (isset($pl['opt']['subj'])) {
        //                         $annots .= ' /Subj '.$this->_textstring($pl['opt']['subj'], $annot_obj_id);
        //                     }
        //                     //$annots .= ' /RT ';
        //                     //$annots .= ' /IT ';
        //                     //$annots .= ' /ExData ';
        //                 }
        //                 $lineendings = array('Square', 'Circle', 'Diamond', 'OpenArrow', 'ClosedArrow', 'None', 'Butt', 'ROpenArrow', 'RClosedArrow', 'Slash');
        //                 // Annotation types
        //                 switch (strtolower($pl['opt']['subtype'])) {
        //                     case 'text': {
        //                         if (isset($pl['opt']['open'])) {
        //                             $annots .= ' /Open '. (strtolower($pl['opt']['open']) == 'true' ? 'true' : 'false');
        //                         }
        //                         $iconsapp = array('Comment', 'Help', 'Insert', 'Key', 'NewParagraph', 'Note', 'Paragraph');
        //                         if (isset($pl['opt']['name']) AND in_array($pl['opt']['name'], $iconsapp)) {
        //                             $annots .= ' /Name /'.$pl['opt']['name'];
        //                         } else {
        //                             $annots .= ' /Name /Note';
        //                         }
        //                         $hasStateModel = isset($pl['opt']['statemodel']);
        //                         $hasState = isset($pl['opt']['state']);
        //                         $statemodels = array('Marked', 'Review');
        //                         if (!$hasStateModel && !$hasState) {
        //                             break;
        //                         }
        //                         if ($hasStateModel AND in_array($pl['opt']['statemodel'], $statemodels)) {
        //                             $annots .= ' /StateModel /'.$pl['opt']['statemodel'];
        //                         } else {
        //                             $pl['opt']['statemodel'] = 'Marked';
        //                             $annots .= ' /StateModel /'.$pl['opt']['statemodel'];
        //                         }
        //                         if ($pl['opt']['statemodel'] == 'Marked') {
        //                             $states = array('Accepted', 'Unmarked');
        //                         } else {
        //                             $states = array('Accepted', 'Rejected', 'Cancelled', 'Completed', 'None');
        //                         }
        //                         if ($hasState AND in_array($pl['opt']['state'], $states)) {
        //                             $annots .= ' /State /'.$pl['opt']['state'];
        //                         } else {
        //                             if ($pl['opt']['statemodel'] == 'Marked') {
        //                                 $annots .= ' /State /Unmarked';
        //                             } else {
        //                                 $annots .= ' /State /None';
        //                             }
        //                         }
        //                         break;
        //                     }
        //                     case 'link': {
        //                         if (is_string($pl['txt']) && !empty($pl['txt'])) {
        //                             if ($pl['txt'][0] == '#') {
        //                                 // internal destination
        //                                 $annots .= ' /A <</S /GoTo /D /'.TCPDF_STATIC::encodeNameObject(substr($pl['txt'], 1)).'>>';
        //                             } elseif ($pl['txt'][0] == '%') {
        //                                 // embedded PDF file
        //                                 $filename = basename(substr($pl['txt'], 1));
        //                                 $annots .= ' /A << /S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P '.($n - 1).' /A '.$this->embeddedfiles[$filename]['a'].' >> >>';
        //                             } elseif ($pl['txt'][0] == '*') {
        //                                 // embedded generic file
        //                                 $filename = basename(substr($pl['txt'], 1));
        //                                 $jsa = 'var D=event.target.doc;var MyData=D.dataObjects;for (var i in MyData) if (MyData[i].path=="'.$filename.'") D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
        //                                 $annots .= ' /A << /S /JavaScript /JS '.$this->_textstring($jsa, $annot_obj_id).'>>';
        //                             } else {
        //                                 $parsedUrl = parse_url($pl['txt']);
        //                                 if (empty($parsedUrl['scheme']) AND (!empty($parsedUrl['path']) && strtolower(substr($parsedUrl['path'], -4)) == '.pdf')) {
        //                                     // relative link to a PDF file
        //                                     $dest = '[0 /Fit]'; // default page 0
        //                                     if (!empty($parsedUrl['fragment'])) {
        //                                         // check for named destination
        //                                         $tmp = explode('=', $parsedUrl['fragment']);
        //                                         $dest = '('.((count($tmp) == 2) ? $tmp[1] : $tmp[0]).')';
        //                                     }
        //                                     $annots .= ' /A <</S /GoToR /D '.$dest.' /F '.$this->_datastring($this->unhtmlentities($parsedUrl['path']), $annot_obj_id).' /NewWindow true>>';
        //                                 } else {
        //                                     // external URI link
        //                                     $annots .= ' /A <</S /URI /URI '.$this->_datastring($this->unhtmlentities($pl['txt']), $annot_obj_id).'>>';
        //                                 }
        //                             }
        //                         } elseif (isset($this->links[$pl['txt']])) {
        //                             // internal link ID
        //                             $l = $this->links[$pl['txt']];
        //                             if (isset($this->page_obj_id[($l['p'])])) {
        //                                 $annots .= sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $this->page_obj_id[($l['p'])], ($this->pagedim[$l['p']]['h'] - ($l['y'] * $this->k)));
        //                             }
        //                         }
        //                         $hmodes = array('N', 'I', 'O', 'P');
        //                         if (isset($pl['opt']['h']) AND in_array($pl['opt']['h'], $hmodes)) {
        //                             $annots .= ' /H /'.$pl['opt']['h'];
        //                         } else {
        //                             $annots .= ' /H /I';
        //                         }
        //                         //$annots .= ' /PA ';
        //                         //$annots .= ' /Quadpoints ';
        //                         break;
        //                     }
        //                     case 'freetext': {
        //                         if (isset($pl['opt']['da']) AND !empty($pl['opt']['da'])) {
        //                             $annots .= ' /DA '.$this->_datastring($pl['opt']['da']);
        //                         }
        //                         if (isset($pl['opt']['q']) AND ($pl['opt']['q'] >= 0) AND ($pl['opt']['q'] <= 2)) {
        //                             $annots .= ' /Q '.intval($pl['opt']['q']);
        //                         }
        //                         if (isset($pl['opt']['rc'])) {
        //                             $annots .= ' /RC '.$this->_textstring($pl['opt']['rc'], $annot_obj_id);
        //                         }
        //                         if (isset($pl['opt']['ds'])) {
        //                             $annots .= ' /DS '.$this->_textstring($pl['opt']['ds'], $annot_obj_id);
        //                         }
        //                         if (isset($pl['opt']['cl']) AND is_array($pl['opt']['cl'])) {
        //                             $annots .= ' /CL [';
        //                             foreach ($pl['opt']['cl'] as $cl) {
        //                                 $annots .= sprintf('%F ', $cl * $this->k);
        //                             }
        //                             $annots .= ']';
        //                         }
        //                         $tfit = array('FreeText', 'FreeTextCallout', 'FreeTextTypeWriter');
        //                         if (isset($pl['opt']['it']) AND in_array($pl['opt']['it'], $tfit)) {
        //                             $annots .= ' /IT /'.$pl['opt']['it'];
        //                         }
        //                         if (isset($pl['opt']['rd']) AND is_array($pl['opt']['rd'])) {
        //                             $l = $pl['opt']['rd'][0] * $this->k;
        //                             $r = $pl['opt']['rd'][1] * $this->k;
        //                             $t = $pl['opt']['rd'][2] * $this->k;
        //                             $b = $pl['opt']['rd'][3] * $this->k;
        //                             $annots .= ' /RD ['.sprintf('%F %F %F %F', $l, $r, $t, $b).']';
        //                         }
        //                         if (isset($pl['opt']['le']) AND in_array($pl['opt']['le'], $lineendings)) {
        //                             $annots .= ' /LE /'.$pl['opt']['le'];
        //                         }
        //                         break;
        //                     }
        //                     case 'line': {
        //                         break;
        //                     }
        //                     case 'square': {
        //                         break;
        //                     }
        //                     case 'circle': {
        //                         break;
        //                     }
        //                     case 'polygon': {
        //                         break;
        //                     }
        //                     case 'polyline': {
        //                         break;
        //                     }
        //                     case 'highlight': {
        //                         break;
        //                     }
        //                     case 'underline': {
        //                         break;
        //                     }
        //                     case 'squiggly': {
        //                         break;
        //                     }
        //                     case 'strikeout': {
        //                         break;
        //                     }
        //                     case 'stamp': {
        //                         break;
        //                     }
        //                     case 'caret': {
        //                         break;
        //                     }
        //                     case 'ink': {
        //                         break;
        //                     }
        //                     case 'popup': {
        //                         break;
        //                     }
        //                     case 'fileattachment': {
        //                         if ($this->pdfa_mode && $this->pdfa_version != 3) {
        //                             // embedded files are not allowed in PDF/A mode version 1 and 2
        //                             break;
        //                         }
        //                         if (!isset($pl['opt']['fs'])) {
        //                             break;
        //                         }
        //                         $filename = basename($pl['opt']['fs']);
        //                         if (isset($this->embeddedfiles[$filename]['f'])) {
        //                             $annots .= ' /FS '.$this->embeddedfiles[$filename]['f'].' 0 R';
        //                             $iconsapp = array('Graph', 'Paperclip', 'PushPin', 'Tag');
        //                             if (isset($pl['opt']['name']) AND in_array($pl['opt']['name'], $iconsapp)) {
        //                                 $annots .= ' /Name /'.$pl['opt']['name'];
        //                             } else {
        //                                 $annots .= ' /Name /PushPin';
        //                             }
        //                             // index (zero-based) of the annotation in the Annots array of this page
        //                             $this->embeddedfiles[$filename]['a'] = $key;
        //                         }
        //                         break;
        //                     }
        //                     case 'sound': {
        //                         if (!isset($pl['opt']['fs'])) {
        //                             break;
        //                         }
        //                         $filename = basename($pl['opt']['fs']);
        //                         if (isset($this->embeddedfiles[$filename]['f'])) {
        //                             // ... TO BE COMPLETED ...
        //                             // /R /C /B /E /CO /CP
        //                             $annots .= ' /Sound '.$this->embeddedfiles[$filename]['f'].' 0 R';
        //                             $iconsapp = array('Speaker', 'Mic');
        //                             if (isset($pl['opt']['name']) AND in_array($pl['opt']['name'], $iconsapp)) {
        //                                 $annots .= ' /Name /'.$pl['opt']['name'];
        //                             } else {
        //                                 $annots .= ' /Name /Speaker';
        //                             }
        //                         }
        //                         break;
        //                     }
        //                     case 'movie': {
        //                         break;
        //                     }
        //                     case 'widget': {
        //                         $hmode = array('N', 'I', 'O', 'P', 'T');
        //                         if (isset($pl['opt']['h']) AND in_array($pl['opt']['h'], $hmode)) {
        //                             $annots .= ' /H /'.$pl['opt']['h'];
        //                         }
        //                         if (isset($pl['opt']['mk']) AND (is_array($pl['opt']['mk'])) AND !empty($pl['opt']['mk'])) {
        //                             $annots .= ' /MK <<';
        //                             if (isset($pl['opt']['mk']['r'])) {
        //                                 $annots .= ' /R '.$pl['opt']['mk']['r'];
        //                             }
        //                             if (isset($pl['opt']['mk']['bc']) AND (is_array($pl['opt']['mk']['bc']))) {
        //                                 $annots .= ' /BC '.TCPDF_COLORS::getColorStringFromArray($pl['opt']['mk']['bc']);
        //                             }
        //                             if (isset($pl['opt']['mk']['bg']) AND (is_array($pl['opt']['mk']['bg']))) {
        //                                 $annots .= ' /BG '.TCPDF_COLORS::getColorStringFromArray($pl['opt']['mk']['bg']);
        //                             }
        //                             if (isset($pl['opt']['mk']['ca'])) {
        //                                 $annots .= ' /CA '.$pl['opt']['mk']['ca'];
        //                             }
        //                             if (isset($pl['opt']['mk']['rc'])) {
        //                                 $annots .= ' /RC '.$pl['opt']['mk']['rc'];
        //                             }
        //                             if (isset($pl['opt']['mk']['ac'])) {
        //                                 $annots .= ' /AC '.$pl['opt']['mk']['ac'];
        //                             }
        //                             if (isset($pl['opt']['mk']['i'])) {
        //                                 $info = $this->getImageBuffer($pl['opt']['mk']['i']);
        //                                 if ($info !== false) {
        //                                     $annots .= ' /I '.$info['n'].' 0 R';
        //                                 }
        //                             }
        //                             if (isset($pl['opt']['mk']['ri'])) {
        //                                 $info = $this->getImageBuffer($pl['opt']['mk']['ri']);
        //                                 if ($info !== false) {
        //                                     $annots .= ' /RI '.$info['n'].' 0 R';
        //                                 }
        //                             }
        //                             if (isset($pl['opt']['mk']['ix'])) {
        //                                 $info = $this->getImageBuffer($pl['opt']['mk']['ix']);
        //                                 if ($info !== false) {
        //                                     $annots .= ' /IX '.$info['n'].' 0 R';
        //                                 }
        //                             }
        //                             if (isset($pl['opt']['mk']['if']) AND (is_array($pl['opt']['mk']['if'])) AND !empty($pl['opt']['mk']['if'])) {
        //                                 $annots .= ' /IF <<';
        //                                 $if_sw = array('A', 'B', 'S', 'N');
        //                                 if (isset($pl['opt']['mk']['if']['sw']) AND in_array($pl['opt']['mk']['if']['sw'], $if_sw)) {
        //                                     $annots .= ' /SW /'.$pl['opt']['mk']['if']['sw'];
        //                                 }
        //                                 $if_s = array('A', 'P');
        //                                 if (isset($pl['opt']['mk']['if']['s']) AND in_array($pl['opt']['mk']['if']['s'], $if_s)) {
        //                                     $annots .= ' /S /'.$pl['opt']['mk']['if']['s'];
        //                                 }
        //                                 if (isset($pl['opt']['mk']['if']['a']) AND (is_array($pl['opt']['mk']['if']['a'])) AND !empty($pl['opt']['mk']['if']['a'])) {
        //                                     $annots .= sprintf(' /A [%F %F]', $pl['opt']['mk']['if']['a'][0], $pl['opt']['mk']['if']['a'][1]);
        //                                 }
        //                                 if (isset($pl['opt']['mk']['if']['fb']) AND ($pl['opt']['mk']['if']['fb'])) {
        //                                     $annots .= ' /FB true';
        //                                 }
        //                                 $annots .= '>>';
        //                             }
        //                             if (isset($pl['opt']['mk']['tp']) AND ($pl['opt']['mk']['tp'] >= 0) AND ($pl['opt']['mk']['tp'] <= 6)) {
        //                                 $annots .= ' /TP '.intval($pl['opt']['mk']['tp']);
        //                             }
        //                             $annots .= '>>';
        //                         } // end MK
        //                         // --- Entries for field dictionaries ---
        //                         if (isset($this->radiobutton_groups[$n][$pl['txt']])) {
        //                             // set parent
        //                             $annots .= ' /Parent '.$this->radiobutton_groups[$n][$pl['txt']].' 0 R';
        //                         }
        //                         if (isset($pl['opt']['t']) AND is_string($pl['opt']['t'])) {
        //                             $annots .= ' /T '.$this->_datastring($pl['opt']['t'], $annot_obj_id);
        //                         }
        //                         if (isset($pl['opt']['tu']) AND is_string($pl['opt']['tu'])) {
        //                             $annots .= ' /TU '.$this->_datastring($pl['opt']['tu'], $annot_obj_id);
        //                         }
        //                         if (isset($pl['opt']['tm']) AND is_string($pl['opt']['tm'])) {
        //                             $annots .= ' /TM '.$this->_datastring($pl['opt']['tm'], $annot_obj_id);
        //                         }
        //                         if (isset($pl['opt']['ff'])) {
        //                             if (is_array($pl['opt']['ff'])) {
        //                                 // array of bit settings
        //                                 $flag = 0;
        //                                 foreach($pl['opt']['ff'] as $val) {
        //                                     $flag += 1 << ($val - 1);
        //                                 }
        //                             } else {
        //                                 $flag = intval($pl['opt']['ff']);
        //                             }
        //                             $annots .= ' /Ff '.$flag;
        //                         }
        //                         if (isset($pl['opt']['maxlen'])) {
        //                             $annots .= ' /MaxLen '.intval($pl['opt']['maxlen']);
        //                         }
        //                         if (isset($pl['opt']['v'])) {
        //                             $annots .= ' /V';
        //                             if (is_array($pl['opt']['v'])) {
        //                                 foreach ($pl['opt']['v'] AS $optval) {
        //                                     if (is_float($optval)) {
        //                                         $optval = sprintf('%F', $optval);
        //                                     }
        //                                     $annots .= ' '.$optval;
        //                                 }
        //                             } else {
        //                                 $annots .= ' '.$this->_textstring($pl['opt']['v'], $annot_obj_id);
        //                             }
        //                         }
        //                         if (isset($pl['opt']['dv'])) {
        //                             $annots .= ' /DV';
        //                             if (is_array($pl['opt']['dv'])) {
        //                                 foreach ($pl['opt']['dv'] AS $optval) {
        //                                     if (is_float($optval)) {
        //                                         $optval = sprintf('%F', $optval);
        //                                     }
        //                                     $annots .= ' '.$optval;
        //                                 }
        //                             } else {
        //                                 $annots .= ' '.$this->_textstring($pl['opt']['dv'], $annot_obj_id);
        //                             }
        //                         }
        //                         if (isset($pl['opt']['rv'])) {
        //                             $annots .= ' /RV';
        //                             if (is_array($pl['opt']['rv'])) {
        //                                 foreach ($pl['opt']['rv'] AS $optval) {
        //                                     if (is_float($optval)) {
        //                                         $optval = sprintf('%F', $optval);
        //                                     }
        //                                     $annots .= ' '.$optval;
        //                                 }
        //                             } else {
        //                                 $annots .= ' '.$this->_textstring($pl['opt']['rv'], $annot_obj_id);
        //                             }
        //                         }
        //                         if (isset($pl['opt']['a']) AND !empty($pl['opt']['a'])) {
        //                             $annots .= ' /A << '.$pl['opt']['a'].' >>';
        //                         }
        //                         if (isset($pl['opt']['aa']) AND !empty($pl['opt']['aa'])) {
        //                             $annots .= ' /AA << '.$pl['opt']['aa'].' >>';
        //                         }
        //                         if (isset($pl['opt']['da']) AND !empty($pl['opt']['da'])) {
        //                             $annots .= ' /DA '.$this->_datastring($pl['opt']['da']);
        //                         }
        //                         if (isset($pl['opt']['q']) AND ($pl['opt']['q'] >= 0) AND ($pl['opt']['q'] <= 2)) {
        //                             $annots .= ' /Q '.intval($pl['opt']['q']);
        //                         }
        //                         if (isset($pl['opt']['opt']) AND (is_array($pl['opt']['opt'])) AND !empty($pl['opt']['opt'])) {
        //                             $annots .= ' /Opt [';
        //                             foreach($pl['opt']['opt'] AS $copt) {
        //                                 if (is_array($copt)) {
        //                                     $annots .= ' ['.$this->_textstring($copt[0], $annot_obj_id).' '.$this->_textstring($copt[1], $annot_obj_id).']';
        //                                 } else {
        //                                     $annots .= ' '.$this->_textstring($copt, $annot_obj_id);
        //                                 }
        //                             }
        //                             $annots .= ']';
        //                         }
        //                         if (isset($pl['opt']['ti'])) {
        //                             $annots .= ' /TI '.intval($pl['opt']['ti']);
        //                         }
        //                         if (isset($pl['opt']['i']) AND (is_array($pl['opt']['i'])) AND !empty($pl['opt']['i'])) {
        //                             $annots .= ' /I [';
        //                             foreach($pl['opt']['i'] AS $copt) {
        //                                 $annots .= intval($copt).' ';
        //                             }
        //                             $annots .= ']';
        //                         }
        //                         break;
        //                     }
        //                     case 'screen': {
        //                         break;
        //                     }
        //                     case 'printermark': {
        //                         break;
        //                     }
        //                     case 'trapnet': {
        //                         break;
        //                     }
        //                     case 'watermark': {
        //                         break;
        //                     }
        //                     case '3d': {
        //                         break;
        //                     }
        //                     default: {
        //                         break;
        //                     }
        //                 }
        //                 $annots .= '>>';
        //                 // create new annotation object
        //                 $this->_out($this->_getobj($annot_obj_id)."\n".$annots."\n".'endobj');
        //                 if ($formfield AND !isset($this->radiobutton_groups[$n][$pl['txt']])) {
        //                     // store reference of form object
        //                     $this->form_obj_id[] = $annot_obj_id;
        //                 }
        //             }
        //         }
        //     } // end for each page
        // }



    }