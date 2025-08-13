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

}