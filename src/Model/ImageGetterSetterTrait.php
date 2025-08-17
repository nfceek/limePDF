<?php

namespace LimePDF\Model;

use LimePDF\Include\FontTrait;
use LimePDF\Support\StaticTrait;

trait ImageGetterSetterTrait {

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
		$txt = str_replace($this->unichr(160, $this->isunicode), ' ', $txt);
		$prev_cell_margin = $this->cell_margin;
		$prev_cell_padding = $this->cell_padding;
		$txt = $this->removeSHY($txt, $this->isunicode);
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
		if ($this->empty_string($w) OR ($w <= 0)) {
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
					$txt2 = $this->UTF8ToLatin1($txt2, $this->isunicode, $this->CurrentFont);
				} else {
					$unicode = $this->UTF8StringToArray($txt, $this->isunicode, $this->CurrentFont); // array of UTF-8 unicode values
					$unicode = $this->utf8Bidi($unicode, '', $this->tmprtl, $this->isunicode, $this->CurrentFont);
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
					$txt2 = $this->arrUTF8ToUTF16BE($unicode, false);
				}
			}
			$txt2 = $this->_escape($txt2);
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
						$topchr = $this->arrUTF8ToUTF16BE($uniarr, false);
						$topchr = $this->_escape($topchr);
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

}