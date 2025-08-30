<?php
/**
 * --------------------------------------------------------------------------------------------------
 *  File:          ImageTrait.php
 *  Description:   Handles image parsing for JPEG, PNG, GIF inside LimePDF
 *  Author:        Brad Smith
 *                 (c) Copyright 2025, Brad Smith - LimePDF.com
 *
 *  Creation Date: 2025-08-27
 *
 *  Original TCPDF Copyright (c) 2002-2023, Nicola Asuni - Tecnick.com LTD
 * --------------------------------------------------------------------------------------------------
 */

namespace LimePDF\Include;

trait ImageTrait
{

	/**
	 * Convert the loaded image to a PNG and then return a structure for the PDF creator.
	 * This function requires GD library and write access to the directory defined on K_PATH_CACHE constant.
	 * @param resource $image Image object.
	 * @param string $tempfile Temporary file name.
	 * return image PNG image object.
	 * 
     *  Php 7 / Php 8.2 Compliant
	 */
    public function _toPNG($image, string $tempfile): array {
        // turn off interlaced mode
        imageinterlace($image, 0);
        // create temporary PNG image
        imagepng($image, $tempfile);
        // remove image from memory
        imagedestroy($image);
        // get PNG image data - use static call instead of $this
        $retvars = self::_parsepng($tempfile);
        // tidy up by removing temporary image
        unlink($tempfile);
        return $retvars;
    }

	/**
	 * Convert the loaded image to a JPEG and then return a structure for the PDF creator.
	 * This function requires GD library and write access to the directory defined on K_PATH_CACHE constant.
	 * @param resource $image Image object.
	 * @param int $quality JPEG quality.
	 * @param string $tempfile Temporary file name.
	 * return array|false image JPEG image object.
    *
    *  Php 7 / Php 8.2 Compliant
	 */
	public function _toJPEG($image, $quality, $tempfile): array {
		imagejpeg($image, $tempfile, $quality);
		imagedestroy($image);
		$retvars = self::_parsejpeg($tempfile);
		// tidy up by removing temporary image
		unlink($tempfile);
		return $retvars;
	}
    /**
     * Parse JPEG image file.
     *
     * @param string $file
     * @return array|false
     */
    public function _parsejpeg($file)
    {
        if (!is_file($file)) {
            return false;
        }

        $a = getimagesize($file);
        if (empty($a) || $a[2] != IMAGETYPE_JPEG) {
            return false;
        }

        $bpc = isset($a['bits']) ? intval($a['bits']) : 8;

        $channels = isset($a['channels']) ? intval($a['channels']) : 3;

        switch ($channels) {
            case 1: $colspace = 'DeviceGray'; break;
            case 3: $colspace = 'DeviceRGB';  break;
            case 4: $colspace = 'DeviceCMYK'; break;
            default:
                $channels = 3;
                $colspace = 'DeviceRGB';
        }

        $data = file_get_contents($file);

        // check for embedded ICC profile
        $icc = [];
        $offset = 0;
        while (($pos = strpos($data, "ICC_PROFILE\0", $offset)) !== false) {
            $length = ($this->_getUSHORT($data, ($pos - 2)) - 16);
            $msn    = max(1, ord($data[($pos + 12)]));
            $icc[($msn - 1)] = substr($data, ($pos + 14), $length);
            $offset = ($pos + 14 + $length);
        }

        if (count($icc) > 0) {
            ksort($icc);
            $icc = implode('', $icc);
            if (
                ord($icc[36]) !== 0x61 ||
                ord($icc[37]) !== 0x63 ||
                ord($icc[38]) !== 0x73 ||
                ord($icc[39]) !== 0x70
            ) {
                $icc = false;
            }
        } else {
            $icc = false;
        }

        return [
            'w'    => $a[0],
            'h'    => $a[1],
            'ch'   => $channels,
            'icc'  => $icc,
            'cs'   => $colspace,
            'bpc'  => $bpc,
            'f'    => 'DCTDecode',
            'data' => $data,
        ];
    }

    /**
     * Parse PNG image file.
     *
     * @param string $file
     * @return array|false
     */
    public function _parsepng($file)
    {
        if (!is_file($file)) {
            return false;
        }

        $info = getimagesize($file);
        if (empty($info) || $info[2] !== IMAGETYPE_PNG) {
            return false;
        }

        $data = file_get_contents($file);

        return [
            'w'    => $info[0],
            'h'    => $info[1],
            'cs'   => 'DeviceRGB',
            'bpc'  => 8,
            'f'    => 'FlateDecode',
            'data' => $data,
        ];
    }

    /**
     * Parse GIF image file.
     *
     * @param string $file
     * @return array|false
     */
    public function _parsegif($file)
    {
        if (!is_file($file)) {
            return false;
        }

        $info = getimagesize($file);
        if (empty($info) || $info[2] !== IMAGETYPE_GIF) {
            return false;
        }

        $data = file_get_contents($file);

        return [
            'w'    => $info[0],
            'h'    => $info[1],
            'cs'   => 'DeviceRGB',
            'bpc'  => 8,
            'f'    => 'LZWDecode',
            'data' => $data,
        ];
    }
}
