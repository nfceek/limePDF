<?php
declare(strict_types=1);

namespace LimePDF\Include;
/*
*
* ImageTrait is Php 7 & Php 8.2 Compliant
*
*/
trait ImageTrait
{
    /**
     * List of inheritable SVG properties.
     * 
     *
     * @var array<string>
     */
    public static array $svginheritprop = [
        'clip-rule',
        'color',
        'color-interpolation',
        'color-interpolation-filters',
        'color-rendering',
        'cursor',
        'direction',
        'fill',
        'fill-opacity',
        'fill-rule',
        'font',
        'font-family',
        'font-size',
        'font-size-adjust',
        'font-stretch',
        'font-style',
        'font-variant',
        'font-weight',
        'image-rendering',
        'letter-spacing',
        'marker',
        'marker-end',
        'marker-mid',
        'marker-start',
        'pointer-events',
        'shape-rendering',
        'stroke',
        'stroke-dasharray',
        'stroke-dashoffset',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-miterlimit',
        'stroke-opacity',
        'stroke-width',
        'text-anchor',
        'text-rendering',
        'visibility',
        'word-spacing',
        'writing-mode',
    ];

    	/**
	 * Return the image type given the file name or array returned by getimagesize() function.
	 * @param string $imgfile image file name
	 * @param array $iminfo array of image information returned by getimagesize() function.
	 * @return string image type
	 * @since 4.8.017 (2009-11-27)
	 * @public static
	 */
	public static function getImageFileType($imgfile, $iminfo=array()) {
		$type = '';
		if (isset($iminfo['mime']) AND !empty($iminfo['mime'])) {
			$mime = explode('/', $iminfo['mime']);
			if ((count($mime) > 1) AND ($mime[0] == 'image') AND (!empty($mime[1]))) {
				$type = strtolower(trim($mime[1]));
			}
		}
		if (empty($type)) {
            $type = strtolower(trim(pathinfo(parse_url($imgfile, PHP_URL_PATH), PATHINFO_EXTENSION)));
		}
		if ($type == 'jpg') {
			$type = 'jpeg';
		}
		return $type;
	}

	/**
	 * Set the transparency for the given GD image.
	 * @param resource $new_image GD image object
	 * @param resource $image GD image object.
	 * @return resource GD image object $new_image
	 * @since 4.9.016 (2010-04-20)
	 * @public static
	 */
	public static function setGDImageTransparency($new_image, $image) {
		// default transparency color (white)
		$tcol = array('red' => 255, 'green' => 255, 'blue' => 255);
		// transparency index
		$tid = imagecolortransparent($image);
		$palletsize = imagecolorstotal($image);
		if (($tid >= 0) AND ($tid < $palletsize)) {
			// get the colors for the transparency index
			$tcol = imagecolorsforindex($image, $tid);
		}
		$tid = imagecolorallocate($new_image, $tcol['red'], $tcol['green'], $tcol['blue']);
		imagefill($new_image, 0, 0, $tid);
		imagecolortransparent($new_image, $tid);
		return $new_image;
	}

	/**
	 * Convert the loaded image to a PNG and then return a structure for the PDF creator.
	 * This function requires GD library and write access to the directory defined on K_PATH_CACHE constant.
	 * @param resource $image Image object.
	 * @param string $tempfile Temporary file name.
	 * return image PNG image object.
	 * @since 4.9.016 (2010-04-20)
	 * @public static
	 */

    public static function _toPNG($image, $tempfile) {
        // turn off interlaced mode (use bool instead of int in PHP 8+)
        imageinterlace($image, false);

        // create temporary PNG image
        imagepng($image, $tempfile);

        // remove image from memory
        imagedestroy($image);

        // get PNG image data
        $retvars = self::_parsepng($tempfile);

        // tidy up by removing temporary image
        unlink($tempfile);

        return $retvars;
    }


	// public static function _toPNG($image, $tempfile) {
	// 	// turn off interlaced mode
	// 	imageinterlace($image, 0);
	// 	// create temporary PNG image
	// 	imagepng($image, $tempfile);
	// 	// remove image from memory
	// 	imagedestroy($image);
	// 	// get PNG image data
	// 	$retvars = self::_parsepng($tempfile);
	// 	// tidy up by removing temporary image
	// 	unlink($tempfile);
	// 	return $retvars;
	// }

	/**
	 * Convert the loaded image to a JPEG and then return a structure for the PDF creator.
	 * This function requires GD library and write access to the directory defined on K_PATH_CACHE constant.
	 * @param resource $image Image object.
	 * @param int $quality JPEG quality.
	 * @param string $tempfile Temporary file name.
	 * return array|false image JPEG image object.
	 * @public static
	 */
	public static function _toJPEG($image, $quality, $tempfile) {
		imagejpeg($image, $tempfile, $quality);
		imagedestroy($image);
		$retvars = self::_parsejpeg($tempfile);
		// tidy up by removing temporary image
		unlink($tempfile);
		return $retvars;
	}

    /**
     * Extract info from a JPEG file without using the GD library.
     *
     * @param string $file Image file to parse.
     * @return array<string, mixed>|false Structure containing the image data or false on failure.
     */
    public static function _parseJpeg(string $file): array|false
    {
        if (!file_exists($file)) {
            return false;
        }

        $a = getimagesize($file);
        if (empty($a) || $a[2] !== IMAGETYPE_JPEG) {
            return false;
        }

        $bpc = isset($a['bits']) ? (int) $a['bits'] : 8;
        $channels = $a['channels'] ?? 3;

        $colspace = match ($channels) {
            1 => 'DeviceGray',
            3 => 'DeviceRGB',
            4 => 'DeviceCMYK',
            default => 'DeviceRGB',
        };

        $data = file_get_contents($file);
        if ($data === false) {
            return false;
        }

        // Extract ICC profile
        $icc = [];
        $offset = 0;
        while (($pos = strpos($data, "ICC_PROFILE\0", $offset)) !== false) {
            $length = (self::getUShort($data, $pos - 2) - 16);
            $msn = max(1, ord($data[$pos + 12]));
            $icc[$msn - 1] = substr($data, $pos + 14, $length);
            $offset = $pos + 14 + $length;
        }

        $iccProfile = false;
        if (!empty($icc)) {
            ksort($icc);
            $iccProfile = implode('', $icc);
            if (
                ord($iccProfile[36] ?? "\0") !== 0x61 ||
                ord($iccProfile[37] ?? "\0") !== 0x63 ||
                ord($iccProfile[38] ?? "\0") !== 0x73 ||
                ord($iccProfile[39] ?? "\0") !== 0x70
            ) {
                $iccProfile = false;
            }
        }

        return [
            'w' => $a[0],
            'h' => $a[1],
            'ch' => $channels,
            'icc' => $iccProfile,
            'cs' => $colspace,
            'bpc' => $bpc,
            'f' => 'DCTDecode',
            'data' => $data,
        ];
    }

    /**
     * Safe file existence check (local or stream wrapper).
     */
    protected static function safeFileExists(string $file): bool {
        // works with URLs and local files
        if (preg_match('/^(http|https|ftp):\/\//i', $file)) {
            // remote file check: try opening stream
            $headers = @get_headers($file);
            return is_array($headers) && strpos($headers[0], '200') !== false;
        }
        return file_exists($file);
    }

    /**
     * Extract info from a PNG file without using the GD library.
     *
     * @param string $file image file to parse
     * @return array|string|false structure containing the image data, 
     *                             'pngalpha' for alpha PNGs, 
     *                             false on error
     */
    public static function _parsepng(string $file) {
        $f = fopen($file, 'rb');
        if ($f === false) {
            return false; // Can't open image
        }

        // Check signature
        $sig = fread($f, 8);
        if ($sig === false || $sig !== chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10)) {
            fclose($f);
            return false; // Not a PNG
        }

        // Read header
        fread($f, 4); // chunk length
        if (fread($f, 4) !== 'IHDR') {
            fclose($f);
            return false; // Invalid PNG
        }

        $w   = self::_freadint($f);
        $h   = self::_freadint($f);
        $bpc = ord(fread($f, 1) ?: "\0");
        $ct  = ord(fread($f, 1) ?: "\0");

        if ($ct === 0) {
            $colspace = 'DeviceGray';
        } elseif ($ct === 2) {
            $colspace = 'DeviceRGB';
        } elseif ($ct === 3) {
            $colspace = 'Indexed';
        } else {
            fclose($f);
            return 'pngalpha'; // has alpha channel
        }

        if (ord(fread($f, 1) ?: "\0") !== 0) { fclose($f); return false; }
        if (ord(fread($f, 1) ?: "\0") !== 0) { fclose($f); return false; }
        if (ord(fread($f, 1) ?: "\0") !== 0) { fclose($f); return false; }
        fread($f, 4);

        $channels = ($ct === 2 ? 3 : 1);
        $parms = '/DecodeParms << /Predictor 15 /Colors '.$channels.' /BitsPerComponent '.$bpc.' /Columns '.$w.' >>';

        $pal = '';
        $trns = [];
        $data = '';
        $icc = false;

        $n = self::_freadint($f);
        while ($n > 0) {
            $type = fread($f, 4);
            if ($type === false) break;

            if ($type === 'PLTE') {
                $pal = self::rfread($f, $n);
                fread($f, 4);
            } elseif ($type === 'tRNS') {
                $t = self::rfread($f, $n);
                if ($ct === 0 && isset($t[1])) {
                    $trns = [ord($t[1])];
                } elseif ($ct === 2 && strlen($t) >= 6) {
                    $trns = [ord($t[1]), ord($t[3]), ord($t[5])];
                } elseif ($ct === 3) {
                    for ($i = 0; $i < $n; $i++) {
                        if (isset($t[$i])) {
                            $trns[] = ord($t[$i]);
                        }
                    }
                }
                fread($f, 4);
            } elseif ($type === 'IDAT') {
                $dataBlock = self::rfread($f, $n);
                if ($dataBlock !== false) {
                    $data .= $dataBlock;
                }
                fread($f, 4);
            } elseif ($type === 'iCCP') {
                $len = 0;
                while (($b = fread($f, 1)) !== false && ord($b) !== 0 && $len < 80) {
                    $len++;
                }
                if (ord(fread($f, 1) ?: "\0") !== 0) {
                    fclose($f);
                    return false;
                }
                $iccRaw = self::rfread($f, $n - $len - 2);
                if ($iccRaw !== false) {
                    $icc = gzuncompress($iccRaw);
                    if ($icc === false) $icc = null;
                }
                fread($f, 4);
            } elseif ($type === 'IEND') {
                break;
            } else {
                self::rfread($f, $n + 4);
            }

            $n = self::_freadint($f);
        }

        fclose($f);

        if ($colspace === 'Indexed' && $pal === '') {
            return false; // missing palette
        }

        return [
            'w'     => $w,
            'h'     => $h,
            'ch'    => $channels,
            'icc'   => $icc,
            'cs'    => $colspace,
            'bpc'   => $bpc,
            'f'     => 'FlateDecode',
            'parms' => $parms,
            'pal'   => $pal,
            'trns'  => $trns,
            'data'  => $data,
        ];
    }

    // /**
    //  * Read an unsigned short (2 bytes) from binary string.
    //  */
    // protected static function _getUSHORT(string $s, int $pos): int {
    //     return (ord($s[$pos]) << 8) | ord($s[$pos + 1]);
    // }

	// /**
	//  * Extract info from a PNG file without using the GD library.
	//  * @param string $file image file to parse
	//  * @return array|false structure containing the image data
	//  * @public static
	//  */
	// public static function _parsepng($file) {
	// 	$f = @fopen($file, 'rb');
	// 	if ($f === false) {
	// 		// Can't open image file
	// 		return false;
	// 	}
	// 	//Check signature
	// 	if (fread($f, 8) != chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10)) {
	// 		// Not a PNG file
	// 		return false;
	// 	}
	// 	//Read header chunk
	// 	fread($f, 4);
	// 	if (fread($f, 4) != 'IHDR') {
	// 		//Incorrect PNG file
	// 		return false;
	// 	}
	// 	$w = self::_freadint($f);
	// 	$h = self::_freadint($f);
	// 	$bpc = ord(fread($f, 1));
	// 	$ct = ord(fread($f, 1));
	// 	if ($ct == 0) {
	// 		$colspace = 'DeviceGray';
	// 	} elseif ($ct == 2) {
	// 		$colspace = 'DeviceRGB';
	// 	} elseif ($ct == 3) {
	// 		$colspace = 'Indexed';
	// 	} else {
	// 		// alpha channel
	// 		fclose($f);
	// 		return 'pngalpha';
	// 	}
	// 	if (ord(fread($f, 1)) != 0) {
	// 		// Unknown compression method
	// 		fclose($f);
	// 		return false;
	// 	}
	// 	if (ord(fread($f, 1)) != 0) {
	// 		// Unknown filter method
	// 		fclose($f);
	// 		return false;
	// 	}
	// 	if (ord(fread($f, 1)) != 0) {
	// 		// Interlacing not supported
	// 		fclose($f);
	// 		return false;
	// 	}
	// 	fread($f, 4);
	// 	$channels = ($ct == 2 ? 3 : 1);
	// 	$parms = '/DecodeParms << /Predictor 15 /Colors '.$channels.' /BitsPerComponent '.$bpc.' /Columns '.$w.' >>';
	// 	//Scan chunks looking for palette, transparency and image data
	// 	$pal = '';
	// 	$trns = '';
	// 	$data = '';
	// 	$icc = false;
	// 	$n = self::_freadint($f);
	// 	do {
	// 		$type = fread($f, 4);
	// 		if ($type == 'PLTE') {
	// 			// read palette
	// 			$pal = self::rfread($f, $n);
	// 			fread($f, 4);
	// 		} elseif ($type == 'tRNS') {
	// 			// read transparency info
	// 			$t = self::rfread($f, $n);
	// 			if ($ct == 0) { // DeviceGray
	// 				$trns = array(ord($t[1]));
	// 			} elseif ($ct == 2) { // DeviceRGB
	// 				$trns = array(ord($t[1]), ord($t[3]), ord($t[5]));
	// 			} else { // Indexed
	// 				if ($n > 0) {
	// 					$trns = array();
	// 					for ($i = 0; $i < $n; ++ $i) {
	// 						$trns[] = ord($t[$i]);
	// 					}
	// 				}
	// 			}
	// 			fread($f, 4);
	// 		} elseif ($type == 'IDAT') {
	// 			// read image data block
	// 			$data .= self::rfread($f, $n);
	// 			fread($f, 4);
	// 		} elseif ($type == 'iCCP') {
	// 			// skip profile name
	// 			$len = 0;
	// 			while ((ord(fread($f, 1)) != 0) AND ($len < 80)) {
	// 				++$len;
	// 			}
	// 			// get compression method
	// 			if (ord(fread($f, 1)) != 0) {
	// 				// Unknown filter method
	// 				fclose($f);
	// 				return false;
	// 			}
	// 			// read ICC Color Profile
	// 			$icc = self::rfread($f, ($n - $len - 2));
	// 			// decompress profile
	// 			$icc = gzuncompress($icc);
	// 			fread($f, 4);
	// 		} elseif ($type == 'IEND') {
	// 			break;
	// 		} else {
	// 			self::rfread($f, $n + 4);
	// 		}
	// 		$n = self::_freadint($f);
	// 	} while ($n);
	// 	if (($colspace == 'Indexed') AND (empty($pal))) {
	// 		// Missing palette
	// 		fclose($f);
	// 		return false;
	// 	}
	// 	fclose($f);
	// 	return array('w' => $w, 'h' => $h, 'ch' => $channels, 'icc' => $icc, 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'FlateDecode', 'parms' => $parms, 'pal' => $pal, 'trns' => $trns, 'data' => $data);
	// }

// } 
    /**
     * Get unsigned short from binary string.
     *
     * @param string $s
     * @param int $pos
     * @return int
     */
    // private static function getUShort(string $s, int $pos): int
    // {
    //     return (ord($s[$pos]) << 8) + ord($s[$pos + 1]);
    // }
}
