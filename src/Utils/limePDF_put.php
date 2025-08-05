<?php


	/**
	 * Embedd the attached files.
	 * @since 4.4.000 (2008-12-07)
	 * @protected
	 * @see Annotation()
	 */

    class limePDF_put {


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

        public function _putimages(TCPDF $tcpdf) {
            $filter = ($tcpdf->compress) ? '/Filter /FlateDecode ' : '';
            //$filter = ($tcpdf->isCompressionEnabled()) ? '/Filter /FlateDecode ' : '';

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

        public function _putfonts(TCPDF $tcpdf) {
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



    }