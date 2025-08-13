<?php

namespace LimePDF;

trait LIMEPDF_TEXT_GETTERSETTER {

	/**
	 * Set parameters for drop shadow effect for text.
	 * @param array $params Array of parameters: enabled (boolean) set to true to enable shadow; depth_w (float) shadow width in user units; depth_h (float) shadow height in user units; color (array) shadow color or false to use the stroke color; opacity (float) Alpha value: real value from 0 (transparent) to 1 (opaque); blend_mode (string) blend mode, one of the following: Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn, HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity.
	 * @since 5.9.174 (2012-07-25)
	 * @public
	*/
	public function setTextShadow($params=array('enabled'=>false, 'depth_w'=>0, 'depth_h'=>0, 'color'=>false, 'opacity'=>1, 'blend_mode'=>'Normal')) {
		if (isset($params['enabled'])) {
			$this->txtshadow['enabled'] = $params['enabled']?true:false;
		} else {
			$this->txtshadow['enabled'] = false;
		}
		if (isset($params['depth_w'])) {
			$this->txtshadow['depth_w'] = floatval($params['depth_w']);
		} else {
			$this->txtshadow['depth_w'] = 0;
		}
		if (isset($params['depth_h'])) {
			$this->txtshadow['depth_h'] = floatval($params['depth_h']);
		} else {
			$this->txtshadow['depth_h'] = 0;
		}
		if (isset($params['color']) AND ($params['color'] !== false) AND is_array($params['color'])) {
			$this->txtshadow['color'] = $params['color'];
		} else {
			$this->txtshadow['color'] = $this->strokecolor;
		}
		if (isset($params['opacity'])) {
			$this->txtshadow['opacity'] = min(1, max(0, floatval($params['opacity'])));
		} else {
			$this->txtshadow['opacity'] = 1;
		}
		if (isset($params['blend_mode']) AND in_array($params['blend_mode'], array('Normal', 'Multiply', 'Screen', 'Overlay', 'Darken', 'Lighten', 'ColorDodge', 'ColorBurn', 'HardLight', 'SoftLight', 'Difference', 'Exclusion', 'Hue', 'Saturation', 'Color', 'Luminosity'))) {
			$this->txtshadow['blend_mode'] = $params['blend_mode'];
		} else {
			$this->txtshadow['blend_mode'] = 'Normal';
		}
		if ((($this->txtshadow['depth_w'] == 0) AND ($this->txtshadow['depth_h'] == 0)) OR ($this->txtshadow['opacity'] == 0)) {
			$this->txtshadow['enabled'] = false;
		}
	}

	/**
	 * Return the text shadow parameters array.
	 * @return array array of parameters.
	 * @since 5.9.174 (2012-07-25)
	 * @public
	 */
	public function getTextShadow() {
		return $this->txtshadow;
	}

	/**
	 * Returns an array of chars containing soft hyphens.
	 * @param array $word array of chars
	 * @param array $patterns Array of hypenation patterns.
	 * @param array $dictionary Array of words to be returned without applying the hyphenation algorithm.
	 * @param int $leftmin Minimum number of character to leave on the left of the word without applying the hyphens.
	 * @param int $rightmin Minimum number of character to leave on the right of the word without applying the hyphens.
	 * @param int $charmin Minimum word length to apply the hyphenation algorithm.
	 * @param int $charmax Maximum length of broken piece of word.
	 * @return array text with soft hyphens
	 * @author Nicola Asuni
	 * @since 4.9.012 (2010-04-12)
	 * @protected
	 */
	protected function hyphenateWord($word, $patterns, $dictionary=array(), $leftmin=1, $rightmin=2, $charmin=1, $charmax=8) {
		$hyphenword = array(); // hyphens positions
		$numchars = count($word);
		if ($numchars <= $charmin) {
			return $word;
		}
		$word_string = LIMEPDF_FONT::UTF8ArrSubString($word, '', '', $this->isunicode);
		// some words will be returned as-is
		$pattern = '/^([a-zA-Z0-9_\.\-]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/';
		if (preg_match($pattern, $word_string) > 0) {
			// email
			return $word;
		}
		$pattern = '/(([a-zA-Z0-9\-]+\.)?)((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/';
		if (preg_match($pattern, $word_string) > 0) {
			// URL
			return $word;
		}
		if (isset($dictionary[$word_string])) {
			return LIMEPDF_FONT::UTF8StringToArray($dictionary[$word_string], $this->isunicode, $this->CurrentFont);
		}
		// surround word with '_' characters
		$tmpword = array_merge(array(46), $word, array(46));
		$tmpnumchars = $numchars + 2;
		$maxpos = $tmpnumchars - 1;
		for ($pos = 0; $pos < $maxpos; ++$pos) {
			$imax = min(($tmpnumchars - $pos), $charmax);
			for ($i = 1; $i <= $imax; ++$i) {
				$subword = strtolower(LIMEPDF_FONT::UTF8ArrSubString($tmpword, $pos, ($pos + $i), $this->isunicode));
				if (isset($patterns[$subword])) {
					$pattern = LIMEPDF_FONT::UTF8StringToArray($patterns[$subword], $this->isunicode, $this->CurrentFont);
					$pattern_length = count($pattern);
					$digits = 1;
					for ($j = 0; $j < $pattern_length; ++$j) {
						// check if $pattern[$j] is a number = hyphenation level (only numbers from 1 to 5 are valid)
						if (($pattern[$j] >= 48) AND ($pattern[$j] <= 57)) {
							if ($j == 0) {
								$zero = $pos - 1;
							} else {
								$zero = $pos + $j - $digits;
							}
							// get hyphenation level
							$level = ($pattern[$j] - 48);
							// if two levels from two different patterns match at the same point, the higher one is selected.
							if (!isset($hyphenword[$zero]) OR ($hyphenword[$zero] < $level)) {
								$hyphenword[$zero] = $level;
							}
							++$digits;
						}
					}
				}
			}
		}
		$inserted = 0;
		$maxpos = $numchars - $rightmin;
		for ($i = $leftmin; $i <= $maxpos; ++$i) {
			// only odd levels indicate allowed hyphenation points
			if (isset($hyphenword[$i]) AND (($hyphenword[$i] % 2) != 0)) {
				// 173 = soft hyphen character
				array_splice($word, $i + $inserted, 0, 173);
				++$inserted;
			}
		}
		return $word;
	}

	/**
	 * Returns text with soft hyphens.
	 * @param string $text text to process
	 * @param mixed $patterns Array of hypenation patterns or a TEX file containing hypenation patterns. TEX patterns can be downloaded from http://www.ctan.org/tex-archive/language/hyph-utf8/tex/generic/hyph-utf8/patterns/
	 * @param array $dictionary Array of words to be returned without applying the hyphenation algorithm.
	 * @param int $leftmin Minimum number of character to leave on the left of the word without applying the hyphens.
	 * @param int $rightmin Minimum number of character to leave on the right of the word without applying the hyphens.
	 * @param int $charmin Minimum word length to apply the hyphenation algorithm.
	 * @param int $charmax Maximum length of broken piece of word.
	 * @return string text with soft hyphens
	 * @author Nicola Asuni
	 * @since 4.9.012 (2010-04-12)
	 * @public
	 */
	public function hyphenateText($text, $patterns, $dictionary=array(), $leftmin=1, $rightmin=2, $charmin=1, $charmax=8) {
		$text = $this->unhtmlentities($text);
		$word = array(); // last word
		$txtarr = array(); // text to be returned
		$intag = false; // true if we are inside an HTML tag
		$skip = false; // true to skip hyphenation
		if (!is_array($patterns)) {
			$patterns = LIMEPDF_STATIC::getHyphenPatternsFromTEX($patterns);
		}
		// get array of characters
		$unichars = LIMEPDF_FONT::UTF8StringToArray($text, $this->isunicode, $this->CurrentFont);
		// for each char
		foreach ($unichars as $char) {
			if ((!$intag) AND (!$skip) AND LIMEPDF_FONT_DATA::$uni_type[$char] == 'L') {
				// letter character
				$word[] = $char;
			} else {
				// other type of character
				if (!LIMEPDF_STATIC::empty_string($word)) {
					// hypenate the word
					$txtarr = array_merge($txtarr, $this->hyphenateWord($word, $patterns, $dictionary, $leftmin, $rightmin, $charmin, $charmax));
					$word = array();
				}
				$txtarr[] = $char;
				if (chr($char) == '<') {
					// we are inside an HTML tag
					$intag = true;
				} elseif ($intag AND (chr($char) == '>')) {
					// end of HTML tag
					$intag = false;
					// check for style tag
					$expected = array(115, 116, 121, 108, 101); // = 'style'
					$current = array_slice($txtarr, -6, 5); // last 5 chars
					$compare = array_diff($expected, $current);
					if (empty($compare)) {
						// check if it is a closing tag
						$expected = array(47); // = '/'
						$current = array_slice($txtarr, -7, 1);
						$compare = array_diff($expected, $current);
						if (empty($compare)) {
							// closing style tag
							$skip = false;
						} else {
							// opening style tag
							$skip = true;
						}
					}
				}
			}
		}
		if (!LIMEPDF_STATIC::empty_string($word)) {
			// hypenate the word
			$txtarr = array_merge($txtarr, $this->hyphenateWord($word, $patterns, $dictionary, $leftmin, $rightmin, $charmin, $charmax));
		}
		// convert char array to string and return
		return LIMEPDF_FONT::UTF8ArrSubString($txtarr, '', '', $this->isunicode);
	}

	/**
	 * Enable/disable rasterization of vector images using ImageMagick library.
	 * @param boolean $mode if true enable rasterization, false otherwise.
	 * @public
	 * @since 5.0.000 (2010-04-27)
	 */
	public function setRasterizeVectorImages($mode) {
		$this->rasterize_vector_images = $mode;
	}


	/**
	 * Left trim the input string
	 * @param string $str string to trim
	 * @param string $replace string that replace spaces.
	 * @return string left trimmed string
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.000 (2010-08-11)
	 */
	public function stringLeftTrim($str, $replace='') {
		return preg_replace('/^'.$this->re_space['p'].'+/'.$this->re_space['m'], $replace, $str);
	}

	/**
	 * Right trim the input string
	 * @param string $str string to trim
	 * @param string $replace string that replace spaces.
	 * @return string right trimmed string
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.000 (2010-08-11)
	 */
	public function stringRightTrim($str, $replace='') {
		return preg_replace('/'.$this->re_space['p'].'+$/'.$this->re_space['m'], $replace, $str);
	}

	/**
	 * Trim the input string
	 * @param string $str string to trim
	 * @param string $replace string that replace spaces.
	 * @return string trimmed string
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.000 (2010-08-11)
	 */
	public function stringTrim($str, $replace='') {
		$str = $this->stringLeftTrim($str, $replace);
		$str = $this->stringRightTrim($str, $replace);
		return $str;
	}


}
