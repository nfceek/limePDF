<?php

namespace LimePDF;

trait LIMEPDF_FONT_GETTERSETTER {

	/**
	 * Defines the size of the current font.
	 * @param float $size The font size in points.
	 * @param boolean $out if true output the font size command, otherwise only set the font properties.
	 * @public
	 * @since 1.0
	 * @see SetFont()
	 */
	public function setFontSize($size, $out=true) {
		$size = (float)$size;
		// font size in points
		$this->FontSizePt = $size;
		// font size in user units
		$this->FontSize = $size / $this->k;
		// calculate some font metrics
		if (isset($this->CurrentFont['desc']['FontBBox'])) {
			$bbox = explode(' ', substr($this->CurrentFont['desc']['FontBBox'], 1, -1));
			$font_height = ((intval($bbox[3]) - intval($bbox[1])) * $size / 1000);
		} else {
			$font_height = $size * 1.219;
		}
		if (isset($this->CurrentFont['desc']['Ascent']) AND ($this->CurrentFont['desc']['Ascent'] > 0)) {
			$font_ascent = ($this->CurrentFont['desc']['Ascent'] * $size / 1000);
		}
		if (isset($this->CurrentFont['desc']['Descent']) AND ($this->CurrentFont['desc']['Descent'] <= 0)) {
			$font_descent = (- $this->CurrentFont['desc']['Descent'] * $size / 1000);
		}
		if (!isset($font_ascent) AND !isset($font_descent)) {
			// core font
			$font_ascent = 0.76 * $font_height;
			$font_descent = $font_height - $font_ascent;
		} elseif (!isset($font_descent)) {
			$font_descent = $font_height - $font_ascent;
		} elseif (!isset($font_ascent)) {
			$font_ascent = $font_height - $font_descent;
		}
		$this->FontAscent = ($font_ascent / $this->k);
		$this->FontDescent = ($font_descent / $this->k);
		if ($out AND ($this->page > 0) AND (isset($this->CurrentFont['i'])) AND ($this->state == 2)) {
			$this->_out(sprintf('BT /F%d %F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
		}
	}

	/**
	 * Returns the bounding box of the current font in user units.
	 * @return array
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getFontBBox() {
		$fbbox = array();
		if (isset($this->CurrentFont['desc']['FontBBox'])) {
			$tmpbbox = explode(' ', substr($this->CurrentFont['desc']['FontBBox'], 1, -1));
			$fbbox = array_map(array($this,'getAbsFontMeasure'), $tmpbbox);
		} else {
			// Find max width
			if (isset($this->CurrentFont['desc']['MaxWidth'])) {
				$maxw = $this->getAbsFontMeasure(intval($this->CurrentFont['desc']['MaxWidth']));
			} else {
				$maxw = 0;
				if (isset($this->CurrentFont['desc']['MissingWidth'])) {
					$maxw = max($maxw, $this->CurrentFont['desc']['MissingWidth']);
				}
				if (isset($this->CurrentFont['desc']['AvgWidth'])) {
					$maxw = max($maxw, $this->CurrentFont['desc']['AvgWidth']);
				}
				if (isset($this->CurrentFont['dw'])) {
					$maxw = max($maxw, $this->CurrentFont['dw']);
				}
				foreach ($this->CurrentFont['cw'] as $char => $w) {
					$maxw = max($maxw, $w);
				}
				if ($maxw == 0) {
					$maxw = 600;
				}
				$maxw = $this->getAbsFontMeasure($maxw);
			}
			$fbbox = array(0, (0 - $this->FontDescent), $maxw, $this->FontAscent);
		}
		return $fbbox;
	}

	/**
	 * Enable or disable default option for font subsetting.
	 * @param boolean $enable if true enable font subsetting by default.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.3.002 (2010-06-07)
	 */
	public function setFontSubsetting($enable=true) {
		if ($this->pdfa_mode) {
			$this->font_subsetting = false;
		} else {
			$this->font_subsetting = $enable ? true : false;
		}
	}

	/**
	 * Return the default option for font subsetting.
	 * @return bool default font subsetting state.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.3.002 (2010-06-07)
	 */
	public function getFontSubsetting() {
		return $this->font_subsetting;
	}

   	/**
	 * Set font buffer content.
	 * @param string $font font key
	 * @param array $data font data
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function setFontBuffer($font, $data) {
		$this->fonts[$font] = $data;
		if (!in_array($font, $this->fontkeys)) {
			$this->fontkeys[] = $font;
			// store object ID for current font
			++$this->n;
			$this->font_obj_ids[$font] = $this->n;
			$this->setFontSubBuffer($font, 'n', $this->n);
		}
	}

	/**
	 * Set font buffer content.
	 * @param string $font font key
	 * @param string $key font sub-key
	 * @param mixed $data font data
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function setFontSubBuffer($font, $key, $data) {
		if (!isset($this->fonts[$font])) {
			$this->setFontBuffer($font, array());
		}
		$this->fonts[$font][$key] = $data;
	}

	/**
	 * Get font buffer content.
	 * @param string $font font key
	 * @return string|false font buffer content or false in case of error
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	public function getFontBuffer($font) {
		if (isset($this->fonts[$font])) {
			return $this->fonts[$font];
		}
		return false;
	} 

	/**
	 * Returns the current font size.
	 * @return float current font size
	 * @public
	 * @since 3.2.000 (2008-06-23)
	 */
	public function getFontSize() {
		return $this->FontSize;
	}

	/**
	 * Returns the current font size in points unit.
	 * @return int current font size in points unit
	 * @public
	 * @since 3.2.000 (2008-06-23)
	 */
	public function getFontSizePt() {
		return $this->FontSizePt;
	}

	/**
	 * Returns the current font family name.
	 * @return string current font family name
	 * @public
	 * @since 4.3.008 (2008-12-05)
	 */
	public function getFontFamily() {
		return $this->FontFamily;
	}

	/**
	 * Returns the current font style.
	 * @return string current font style
	 * @public
	 * @since 4.3.008 (2008-12-05)
	 */
	public function getFontStyle() {
		return $this->FontStyle;
	}

	/**
	 * Return the font descent value
	 * @param string $font font name
	 * @param string $style font style
	 * @param float $size The size (in points)
	 * @return int font descent
	 * @public
	 * @author Nicola Asuni
	 * @since 4.9.003 (2010-03-30)
	 */
	public function getFontDescent($font, $style='', $size=0) {
		$fontdata = $this->AddFont($font, $style);
		$fontinfo = $this->getFontBuffer($fontdata['fontkey']);
		if (isset($fontinfo['desc']['Descent']) AND ($fontinfo['desc']['Descent'] <= 0)) {
			$descent = (- $fontinfo['desc']['Descent'] * $size / 1000);
		} else {
			$descent = (1.219 * 0.24 * $size);
		}
		return ($descent / $this->k);
	}

	/**
	 * Return the font ascent value.
	 * @param string $font font name
	 * @param string $style font style
	 * @param float $size The size (in points)
	 * @return int font ascent
	 * @public
	 * @author Nicola Asuni
	 * @since 4.9.003 (2010-03-30)
	 */
	public function getFontAscent($font, $style='', $size=0) {
		$fontdata = $this->AddFont($font, $style);
		$fontinfo = $this->getFontBuffer($fontdata['fontkey']);
		if (isset($fontinfo['desc']['Ascent']) AND ($fontinfo['desc']['Ascent'] > 0)) {
			$ascent = ($fontinfo['desc']['Ascent'] * $size / 1000);
		} else {
			$ascent = 1.219 * 0.76 * $size;
		}
		return ($ascent / $this->k);
	}

	/**
	 * Return normalized font name
	 * @param string $fontfamily property string containing font family names
	 * @return string normalized font name
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.004 (2010-08-17)
	 */
	public function getFontFamilyName($fontfamily) {
		// remove spaces and symbols
		$fontfamily = preg_replace('/[^a-z0-9_\,]/', '', strtolower($fontfamily));
		// extract all font names
		$fontslist = preg_split('/[,]/', $fontfamily);
		// find first valid font name
		foreach ($fontslist as $font) {
			// replace font variations
			$font = preg_replace('/regular$/', '', $font);
			$font = preg_replace('/italic$/', 'I', $font);
			$font = preg_replace('/oblique$/', 'I', $font);
			$font = preg_replace('/bold([I]?)$/', 'B\\1', $font);
			// replace common family names and core fonts
			$pattern = array();
			$replacement = array();
			$pattern[] = '/^serif|^cursive|^fantasy|^timesnewroman/';
			$replacement[] = 'times';
			$pattern[] = '/^sansserif/';
			$replacement[] = 'helvetica';
			$pattern[] = '/^monospace/';
			$replacement[] = 'courier';
			$font = preg_replace($pattern, $replacement, $font);
			if (in_array(strtolower($font), $this->fontlist) OR in_array($font, $this->fontkeys)) {
				return $font;
			}
		}
		// return current font as default
		return $this->CurrentFont['fontkey'];
	}
 
	/**
	 * Return the cell height
	 * @param int $fontsize Font size in internal units
	 * @param boolean $padding If true add cell padding
	 * @public
	 * @return float
	 */
	public function getCellHeight($fontsize, $padding=TRUE) {
		$height = ($fontsize * $this->cell_height_ratio);
		if ($padding && !empty($this->cell_padding)) {
			$height += ($this->cell_padding['T'] + $this->cell_padding['B']);
		}
		return round($height, 6);
	}
}