<?php

namespace LimePDF\Fonts;

trait FontsTrait {	

	/**
	 * Imports a TrueType, Type1, core, or CID0 font and makes it available.
	 * It is necessary to generate a font definition file first (read /fonts/utils/README.TXT).
	 * The definition file (and the font file itself when embedding) must be present either in the current directory or in the one indicated by K_PATH_FONTS if the constant is defined. If it could not be found, the error "Could not include font definition file" is generated.
	 * @param string $family Font family. The name can be chosen arbitrarily. If it is a standard family name, it will override the corresponding font.
	 * @param string $style Font style. Possible values are (case insensitive):<ul><li>empty string: regular (default)</li><li>B: bold</li><li>I: italic</li><li>BI or IB: bold italic</li></ul>
	 * @param string $fontfile The font definition file. By default, the name is built from the family and style, in lower case with no spaces.
	 * @return array|false array containing the font data, or false in case of error.
	 * @param mixed $subset if true embedd only a subset of the font (stores only the information related to the used characters); if false embedd full font; if 'default' uses the default value set using setFontSubsetting(). This option is valid only for TrueTypeUnicode fonts. If you want to enable users to change the document, set this parameter to false. If you subset the font, the person who receives your PDF would need to have your same font in order to make changes to your PDF. The file size of the PDF would also be smaller because you are embedding only part of a font.
	 * @public
	 * @since 1.5
	 * @see SetFont(), setFontSubsetting()
	 */
	public function AddFont($family, $style='', $fontfile='', $subset='default') {
		if ($subset === 'default') {
			$subset = $this->font_subsetting;
		}
		if ($this->pdfa_mode) {
			$subset = false;
		}
		if (LIMEPDF_STATIC::empty_string($family)) {
			if (!LIMEPDF_STATIC::empty_string($this->FontFamily)) {
				$family = $this->FontFamily;
			} else {
				$this->Error('Empty font family');
			}
		}
		// move embedded styles on $style
		if (substr($family, -1) == 'I') {
			$style .= 'I';
			$family = substr($family, 0, -1);
		}
		if (substr($family, -1) == 'B') {
			$style .= 'B';
			$family = substr($family, 0, -1);
		}
		// normalize family name
		$family = strtolower($family);
		if ((!$this->isunicode) AND ($family == 'arial')) {
			$family = 'helvetica';
		}
		if (($family == 'symbol') OR ($family == 'zapfdingbats')) {
			$style = '';
		}
		if ($this->pdfa_mode AND (isset($this->CoreFonts[$family]))) {
			// all fonts must be embedded
			$family = 'pdfa'.$family;
		}
		$tempstyle = strtoupper($style === null ? '' : $style);
		$style = '';
		// underline
		if (strpos($tempstyle, 'U') !== false) {
			$this->underline = true;
		} else {
			$this->underline = false;
		}
		// line-through (deleted)
		if (strpos($tempstyle, 'D') !== false) {
			$this->linethrough = true;
		} else {
			$this->linethrough = false;
		}
		// overline
		if (strpos($tempstyle, 'O') !== false) {
			$this->overline = true;
		} else {
			$this->overline = false;
		}
		// bold
		if (strpos($tempstyle, 'B') !== false) {
			$style .= 'B';
		}
		// oblique
		if (strpos($tempstyle, 'I') !== false) {
			$style .= 'I';
		}
		$bistyle = $style;
		$fontkey = $family.$style;
		$font_style = $style.($this->underline ? 'U' : '').($this->linethrough ? 'D' : '').($this->overline ? 'O' : '');
		$fontdata = array('fontkey' => $fontkey, 'family' => $family, 'style' => $font_style);
		// check if the font has been already added
		$fb = $this->getFontBuffer($fontkey);
		if ($fb !== false) {
			if ($this->inxobj) {
				// we are inside an XObject template
				$this->xobjects[$this->xobjid]['fonts'][$fontkey] = $fb['i'];
			}
			return $fontdata;
		}
		// get specified font directory (if any)
		$fontdir = false;
		if (!LIMEPDF_STATIC::empty_string($fontfile)) {
			$fontdir = dirname($fontfile);
			if (LIMEPDF_STATIC::empty_string($fontdir) OR ($fontdir == '.')) {
				$fontdir = '';
			} else {
				$fontdir .= '/';
			}
		}
		// true when the font style variation is missing
		$missing_style = false;
		// search and include font file
		if (LIMEPDF_STATIC::empty_string($fontfile) OR (!@LIMEPDF_STATIC::file_exists($fontfile))) {
			// build a standard filenames for specified font
			$tmp_fontfile = str_replace(' ', '', $family).strtolower($style).'.php';
			$fontfile = LIMEPDF_FONT::getFontFullPath($tmp_fontfile, $fontdir);
			if (LIMEPDF_STATIC::empty_string($fontfile)) {
				$missing_style = true;
				// try to remove the style part
				$tmp_fontfile = str_replace(' ', '', $family).'.php';
				$fontfile = LIMEPDF_FONT::getFontFullPath($tmp_fontfile, $fontdir);
			}
		}
		// include font file
		if (!LIMEPDF_STATIC::empty_string($fontfile) AND (@LIMEPDF_STATIC::file_exists($fontfile))) {
			$type=null;
			$name=null;
			$desc=null;
			$up=-null;
			$ut=null;
			$cw=null;
			$cbbox=null;
			$dw=null;
			$enc=null;
			$cidinfo=null;
			$file=null;
			$ctg=null;
			$diff=null;
			$originalsize=null;
			$size1=null;
			$size2=null;
			include($fontfile);
		} else {
			$this->Error('Could not include font definition file: '.$family.'');
		}
		// check font parameters
		if ((!isset($type)) OR (!isset($cw))) {
			$this->Error('The font definition file has a bad format: '.$fontfile.'');
		}
		// SET default parameters
		if (!isset($file) OR LIMEPDF_STATIC::empty_string($file)) {
			$file = '';
		}
		if (!isset($enc) OR LIMEPDF_STATIC::empty_string($enc)) {
			$enc = '';
		}
		if (!isset($cidinfo) OR LIMEPDF_STATIC::empty_string($cidinfo)) {
			$cidinfo = array('Registry'=>'Adobe', 'Ordering'=>'Identity', 'Supplement'=>0);
			$cidinfo['uni2cid'] = array();
		}
		if (!isset($ctg) OR LIMEPDF_STATIC::empty_string($ctg)) {
			$ctg = '';
		}
		if (!isset($desc) OR LIMEPDF_STATIC::empty_string($desc)) {
			$desc = array();
		}
		if (!isset($up) OR LIMEPDF_STATIC::empty_string($up)) {
			$up = -100;
		}
		if (!isset($ut) OR LIMEPDF_STATIC::empty_string($ut)) {
			$ut = 50;
		}
		if (!isset($cw) OR LIMEPDF_STATIC::empty_string($cw)) {
			$cw = array();
		}
		if (!isset($dw) OR LIMEPDF_STATIC::empty_string($dw)) {
			// set default width
			if (isset($desc['MissingWidth']) AND ($desc['MissingWidth'] > 0)) {
				$dw = $desc['MissingWidth'];
			} elseif (isset($cw[32])) {
				$dw = $cw[32];
			} else {
				$dw = 600;
			}
		}
		++$this->numfonts;
		if ($type == 'core') {
			$name = $this->CoreFonts[$fontkey];
			$subset = false;
		} elseif (($type == 'TrueType') OR ($type == 'Type1')) {
			$subset = false;
		} elseif ($type == 'TrueTypeUnicode') {
			$enc = 'Identity-H';
		} elseif ($type == 'cidfont0') {
			if ($this->pdfa_mode) {
				$this->Error('All fonts must be embedded in PDF/A mode!');
			}
		} else {
			$this->Error('Unknow font type: '.$type.'');
		}
		// set name if unset
		if (empty($name)) {
			$name = $fontkey;
		}
		// create artificial font style variations if missing (only works with non-embedded fonts)
		if (($type != 'core') AND $missing_style) {
			// style variations
			$styles = array('' => '', 'B' => ',Bold', 'I' => ',Italic', 'BI' => ',BoldItalic');
			$name .= $styles[$bistyle];
			// artificial bold
			if (strpos($bistyle, 'B') !== false) {
				if (isset($desc['StemV'])) {
					// from normal to bold
					$desc['StemV'] = round($desc['StemV'] * 1.75);
				} else {
					// bold
					$desc['StemV'] = 123;
				}
			}
			// artificial italic
			if (strpos($bistyle, 'I') !== false) {
				if (isset($desc['ItalicAngle'])) {
					$desc['ItalicAngle'] -= 11;
				} else {
					$desc['ItalicAngle'] = -11;
				}
				if (isset($desc['Flags'])) {
					$desc['Flags'] |= 64; //bit 7
				} else {
					$desc['Flags'] = 64;
				}
			}
		}
		// check if the array of characters bounding boxes is defined
		if (!isset($cbbox)) {
			$cbbox = array();
		}
		// initialize subsetchars
		$subsetchars = array_fill(0, 255, true);
		$this->setFontBuffer($fontkey, array('fontkey' => $fontkey, 'i' => $this->numfonts, 'type' => $type, 'name' => $name, 'desc' => $desc, 'up' => $up, 'ut' => $ut, 'cw' => $cw, 'cbbox' => $cbbox, 'dw' => $dw, 'enc' => $enc, 'cidinfo' => $cidinfo, 'file' => $file, 'ctg' => $ctg, 'subset' => $subset, 'subsetchars' => $subsetchars));
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['fonts'][$fontkey] = $this->numfonts;
		}
		if (!empty($diff)) {
			//Search existing encodings
			$d = 0;
			$nb = count($this->diffs);
			for ($i=1; $i <= $nb; ++$i) {
				if ($this->diffs[$i] == $diff) {
					$d = $i;
					break;
				}
			}
			if ($d == 0) {
				$d = $nb + 1;
				$this->diffs[$d] = $diff;
			}
			$this->setFontSubBuffer($fontkey, 'diff', $d);
		}
		if (!LIMEPDF_STATIC::empty_string($file)) {
			if (!isset($this->FontFiles[$file])) {
				if ((strcasecmp($type,'TrueType') == 0) OR (strcasecmp($type, 'TrueTypeUnicode') == 0)) {
					$this->FontFiles[$file] = array('length1' => $originalsize, 'fontdir' => $fontdir, 'subset' => $subset, 'fontkeys' => array($fontkey));
				} elseif ($type != 'core') {
					$this->FontFiles[$file] = array('length1' => $size1, 'length2' => $size2, 'fontdir' => $fontdir, 'subset' => $subset, 'fontkeys' => array($fontkey));
				}
			} else {
				// update fontkeys that are sharing this font file
				$this->FontFiles[$file]['subset'] = ($this->FontFiles[$file]['subset'] AND $subset);
				if (!in_array($fontkey, $this->FontFiles[$file]['fontkeys'])) {
					$this->FontFiles[$file]['fontkeys'][] = $fontkey;
				}
			}
		}
		return $fontdata;
	}

	/**
	 * Sets the font used to print character strings.
	 * The font can be either a standard one or a font added via the AddFont() method. Standard fonts use Windows encoding cp1252 (Western Europe).
	 * The method can be called before the first page is created and the font is retained from page to page.
	 * If you just wish to change the current font size, it is simpler to call SetFontSize().
	 * Note: for the standard fonts, the font metric files must be accessible. There are three possibilities for this:<ul><li>They are in the current directory (the one where the running script lies)</li><li>They are in one of the directories defined by the include_path parameter</li><li>They are in the directory defined by the K_PATH_FONTS constant</li></ul><br />
	 * @param string $family Family font. It can be either a name defined by AddFont() or one of the standard Type1 families (case insensitive):<ul><li>times (Times-Roman)</li><li>timesb (Times-Bold)</li><li>timesi (Times-Italic)</li><li>timesbi (Times-BoldItalic)</li><li>helvetica (Helvetica)</li><li>helveticab (Helvetica-Bold)</li><li>helveticai (Helvetica-Oblique)</li><li>helveticabi (Helvetica-BoldOblique)</li><li>courier (Courier)</li><li>courierb (Courier-Bold)</li><li>courieri (Courier-Oblique)</li><li>courierbi (Courier-BoldOblique)</li><li>symbol (Symbol)</li><li>zapfdingbats (ZapfDingbats)</li></ul> It is also possible to pass an empty string. In that case, the current family is retained.
	 * @param string $style Font style. Possible values are (case insensitive):<ul><li>empty string: regular</li><li>B: bold</li><li>I: italic</li><li>U: underline</li><li>D: line through</li><li>O: overline</li></ul> or any combination. The default value is regular. Bold and italic styles do not apply to Symbol and ZapfDingbats basic fonts or other fonts when not defined.
	 * @param float|null $size Font size in points. The default value is the current size. If no size has been specified since the beginning of the document, the value taken is 12
	 * @param string $fontfile The font definition file. By default, the name is built from the family and style, in lower case with no spaces.
	 * @param mixed $subset if true embedd only a subset of the font (stores only the information related to the used characters); if false embedd full font; if 'default' uses the default value set using setFontSubsetting(). This option is valid only for TrueTypeUnicode fonts. If you want to enable users to change the document, set this parameter to false. If you subset the font, the person who receives your PDF would need to have your same font in order to make changes to your PDF. The file size of the PDF would also be smaller because you are embedding only part of a font.
	 * @param boolean $out if true output the font size command, otherwise only set the font properties.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.0
	 * @see AddFont(), SetFontSize()
	 */
	public function setFont($family, $style='', $size=null, $fontfile='', $subset='default', $out=true) {
		//Select a font; size given in points
		if ($size === null) {
			$size = $this->FontSizePt;
		}
		if ($size < 0) {
			$size = 0;
		}
		// try to add font (if not already added)
		$fontdata = $this->AddFont($family, $style, $fontfile, $subset);
		$this->FontFamily = $fontdata['family'];
		$this->FontStyle = $fontdata['style'];
		if (isset($this->CurrentFont['fontkey']) AND isset($this->CurrentFont['subsetchars'])) {
			// save subset chars of the previous font
			$this->setFontSubBuffer($this->CurrentFont['fontkey'], 'subsetchars', $this->CurrentFont['subsetchars']);
		}
		$this->CurrentFont = $this->getFontBuffer($fontdata['fontkey']);
		$this->setFontSize($size, $out);
	}


}