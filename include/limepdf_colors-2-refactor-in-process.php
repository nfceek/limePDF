<?php

// example 6 & 37


class TCPDF_COLORS {


	/**
	 * Array of valid JavaScript color names
	 * @public static
	 */
	public static $jscolor = array ('transparent', 'black', 'white', 'red', 'green', 'blue', 'cyan', 'magenta', 'yellow', 'dkGray', 'gray', 'ltGray');

	/**
	 * Array of Spot colors (C,M,Y,K,name)
	 * Color keys must be in lowercase and without spaces.
	 * As long as no open standard for spot colours exists, you have to buy a colour book by one of the colour manufacturers and insert the values and names of spot colours directly.
	 * Common industry standard spot colors are: ANPA-COLOR, DIC, FOCOLTONE, GCMI, HKS, PANTONE, TOYO, TRUMATCH.
	 * @public static
	 */
	public static $spotcolor = array (
		// special registration colors
		'none'    => array(  0,   0,   0,   0, 'None'),
		'all'     => array(100, 100, 100, 100, 'All'),
		// standard CMYK colors
		'cyan'    => array(100,   0,   0,   0, 'Cyan'),
		'magenta' => array(  0, 100,   0,   0, 'Magenta'),
		'yellow'  => array(  0,   0, 100,   0, 'Yellow'),
		'key'     => array(  0,   0,   0, 100, 'Key'),
		// alias
		'white'   => array(  0,   0,   0,   0, 'White'),
		'black'   => array(  0,   0,   0, 100, 'Black'),
		// standard RGB colors
		'red'     => array(  0, 100, 100,   0, 'Red'),
		'green'   => array(100,   0, 100,   0, 'Green'),
		'blue'    => array(100, 100,   0,   0, 'Blue'),
		// Add here standard spot colors or dynamically define them with AddSpotColor()
		// ...
	); // end of spot colors

	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

	/**
	 * Return the Spot color array.
	 * @param string $name Name of the spot color.
	 * @param array $spotc Reference to an array of spot colors.
	 * @return array|false Spot color array or false if not defined.
	 * @since 5.9.125 (2011-10-03)
	 * @public static
	 */
	public static function getSpotColor($name, &$spotc) {
		if (isset($spotc[$name])) {
			return $spotc[$name];
		}
		$color = preg_replace('/[\s]*/', '', $name); // remove extra spaces
		$color = strtolower($color);
		if (isset(self::$spotcolor[$color])) {
			if (!isset($spotc[$name])) {
				$i = (1 + count($spotc));
				$spotc[$name] = array('C' => self::$spotcolor[$color][0], 'M' => self::$spotcolor[$color][1], 'Y' => self::$spotcolor[$color][2], 'K' => self::$spotcolor[$color][3], 'name' => self::$spotcolor[$color][4], 'i' => $i);
			}
			return $spotc[self::$spotcolor[$color][4]];
		}
		return false;
	}

	/**
	 * Returns an array (RGB or CMYK) from an html color name, or a six-digit (i.e. #3FE5AA), or three-digit (i.e. #7FF) hexadecimal color, or a javascript color array, or javascript color name.
	 * @param string $hcolor HTML color.
	 * @param array $spotc Reference to an array of spot colors.
	 * @param array $defcol Color to return in case of error.
	 * @return array|false RGB or CMYK color, or false in case of error.
	 * @public static
	 */
	public static function convertHTMLColorToDec($hcolor, &$spotc, $defcol=array('R'=>128,'G'=>128,'B'=>128)) {
		$color = preg_replace('/[\s]*/', '', $hcolor); // remove extra spaces
		$color = strtolower($color);
		// check for javascript color array syntax
		if (strpos($color, '[') !== false) {
			if (preg_match('/[\[][\"\'](t|g|rgba|rgb|cmyk)[\"\'][\,]?([0-9\.]*+)[\,]?([0-9\.]*+)[\,]?([0-9\.]*+)[\,]?([0-9\.]*+)[\]]/', $color, $m) > 0) {
				$returncolor = array();
				switch ($m[1]) {
					case 'cmyk': {
						// RGB
						$returncolor['C'] = max(0, min(100, (floatval($m[2]) * 100)));
						$returncolor['M'] = max(0, min(100, (floatval($m[3]) * 100)));
						$returncolor['Y'] = max(0, min(100, (floatval($m[4]) * 100)));
						$returncolor['K'] = max(0, min(100, (floatval($m[5]) * 100)));
						break;
					}
					case 'rgb':
					case 'rgba': {
						// RGB
						$returncolor['R'] = max(0, min(255, (floatval($m[2]) * 255)));
						$returncolor['G'] = max(0, min(255, (floatval($m[3]) * 255)));
						$returncolor['B'] = max(0, min(255, (floatval($m[4]) * 255)));
						break;
					}
					case 'g': {
						// grayscale
						$returncolor['G'] = max(0, min(255, (floatval($m[2]) * 255)));
						break;
					}
					case 't':
					default: {
						// transparent (empty array)
						break;
					}
				}
				return $returncolor;
			}
		} elseif ((substr($color, 0, 4) != 'cmyk') AND (substr($color, 0, 3) != 'rgb') AND (($dotpos = strpos($color, '.')) !== false)) {
			// remove class parent (i.e.: color.red)
			$color = substr($color, ($dotpos + 1));
			if ($color == 'transparent') {
				// transparent (empty array)
				return array();
			}
		}
		if (strlen($color) == 0) {
			return $defcol;
		}
		// RGBA ARRAY
		if (substr($color, 0, 4) == 'rgba') {
			$codes = substr($color, 5);
			$codes = str_replace(')', '', $codes);
			$returncolor = explode(',', $codes);
			// remove alpha component
			array_pop($returncolor);
			foreach ($returncolor as $key => $val) {
				if (strpos($val, '%') > 0) {
					// percentage
					$returncolor[$key] = (255 * intval($val) / 100);
				} else {
					$returncolor[$key] = intval($val); /* floatize */
				}
				// normalize value
				$returncolor[$key] = max(0, min(255, $returncolor[$key]));
			}
			return $returncolor;
		}
		// RGB ARRAY
		if (substr($color, 0, 3) == 'rgb') {
			$codes = substr($color, 4);
			$codes = str_replace(')', '', $codes);
			$returncolor = explode(',', $codes);
			foreach ($returncolor as $key => $val) {
				if (strpos($val, '%') > 0) {
					// percentage
					$returncolor[$key] = (255 * intval($val) / 100);
				} else {
					$returncolor[$key] = intval($val);
				}
				// normalize value
				$returncolor[$key] = max(0, min(255, $returncolor[$key]));
			}
			return $returncolor;
		}
		// CMYK ARRAY
		if (substr($color, 0, 4) == 'cmyk') {
			$codes = substr($color, 5);
			$codes = str_replace(')', '', $codes);
			$returncolor = explode(',', $codes);
			foreach ($returncolor as $key => $val) {
				if (strpos($val, '%') !== false) {
					// percentage
					$returncolor[$key] = (100 * intval($val) / 100);
				} else {
					$returncolor[$key] = intval($val);
				}
				// normalize value
				$returncolor[$key] = max(0, min(100, $returncolor[$key]));
			}
			return $returncolor;
		}
		if ($color[0] != '#') {
			// COLOR NAME
			if (isset(self::$webcolor[$color])) {
				// web color
				$color_code = self::$webcolor[$color];
			} else {
				// spot color
				$returncolor = self::getSpotColor($hcolor, $spotc);
				if ($returncolor === false) {
					$returncolor = $defcol;
				}
				return $returncolor;
			}
		} else {
			$color_code = substr($color, 1);
		}
		// HEXADECIMAL REPRESENTATION
		switch (strlen($color_code)) {
			case 3: {
				// 3-digit RGB hexadecimal representation
				$r = substr($color_code, 0, 1);
				$g = substr($color_code, 1, 1);
				$b = substr($color_code, 2, 1);
				$returncolor = array();
				$returncolor['R'] = max(0, min(255, hexdec($r.$r)));
				$returncolor['G'] = max(0, min(255, hexdec($g.$g)));
				$returncolor['B'] = max(0, min(255, hexdec($b.$b)));
				break;
			}
			case 6: {
				// 6-digit RGB hexadecimal representation
				$returncolor = array();
				$returncolor['R'] = max(0, min(255, hexdec(substr($color_code, 0, 2))));
				$returncolor['G'] = max(0, min(255, hexdec(substr($color_code, 2, 2))));
				$returncolor['B'] = max(0, min(255, hexdec(substr($color_code, 4, 2))));
				break;
			}
			case 8: {
				// 8-digit CMYK hexadecimal representation
				$returncolor = array();
				$returncolor['C'] = max(0, min(100, round(hexdec(substr($color_code, 0, 2)) / 2.55)));
				$returncolor['M'] = max(0, min(100, round(hexdec(substr($color_code, 2, 2)) / 2.55)));
				$returncolor['Y'] = max(0, min(100, round(hexdec(substr($color_code, 4, 2)) / 2.55)));
				$returncolor['K'] = max(0, min(100, round(hexdec(substr($color_code, 6, 2)) / 2.55)));
				break;
			}
			default: {
				$returncolor = $defcol;
				break;
			}
		}
		return $returncolor;
	}

	/**
	 * Convert a color array into a string representation.
	 * @param array $c Array of colors.
	 * @return string The color array representation.
	 * @since 5.9.137 (2011-12-01)
	 * @public static
	 */
	public static function getColorStringFromArray($c) {
		$c = array_values($c);
		$color = '[';
		switch (count($c)) {
			case 4: {
				// CMYK
				$color .= sprintf('%F %F %F %F', (max(0, min(100, floatval($c[0]))) / 100), (max(0, min(100, floatval($c[1]))) / 100), (max(0, min(100, floatval($c[2]))) / 100), (max(0, min(100, floatval($c[3]))) / 100));
				break;
			}
			case 3: {
				// RGB
				$color .= sprintf('%F %F %F', (max(0, min(255, floatval($c[0]))) / 255), (max(0, min(255, floatval($c[1]))) / 255), (max(0, min(255, floatval($c[2]))) / 255));
				break;
			}
			case 1: {
				// grayscale
				$color .= sprintf('%F', (max(0, min(255, floatval($c[0]))) / 255));
				break;
			}
		}
		$color .= ']';
		return $color;
	}

	/**
	 * Convert color to javascript color.
	 * @param string $color color name or "#RRGGBB"
	 * @protected
	 * @since 2.1.002 (2008-02-12)
	 * @public static
	 */
	public static function _JScolor($color) {
		if (substr($color, 0, 1) == '#') {
			return sprintf("['RGB',%F,%F,%F]", (hexdec(substr($color, 1, 2)) / 255), (hexdec(substr($color, 3, 2)) / 255), (hexdec(substr($color, 5, 2)) / 255));
		}
		if (!in_array($color, self::$jscolor)) {
			// default transparent color
			$color = self::$jscolor[0];
		}
		return 'color.'.$color;
	}


} // END OF TCPDF_COLORS CLASS

//============================================================+
// END OF FILE
//============================================================+
