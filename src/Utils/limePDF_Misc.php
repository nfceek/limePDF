<?php

class limePDF_Misc {
    
    /**
	 * Output Form XObjects Templates.
	 * @author Nicola Asuni
	 * @since 5.8.017 (2010-08-24)
	 * @protected
	 * @see startTemplate(), endTemplate(), printTemplate()
	 */
	protected function putXObjects() {
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
	 * Output gradient shaders.
	 * @author Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @protected
	 */
	function putShaders() {
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


    //TODO - FIX THIS FUNCTION
	public function putBookmarks() {
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
}