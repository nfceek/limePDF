<?php
namespace LimePDF\Utils;

class Environment {
    public static function doChecks(): void {
        if (!extension_loaded('mbstring')) {
            throw new \RuntimeException('The mbstring extension is required.');
        }
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('The GD extension is required.');
        }
        if (!extension_loaded('zlib')) {
            throw new \RuntimeException('The Zlib extension is required.');
        }
		//Check for locale-related bug
		if (1.1 == 1) {
			$this->Error('Don\'t alter the locale before including class file');
		}
		//Check for decimal separator
		if (sprintf('%.1F', 1.0) != '1.0') {
			setlocale(LC_NUMERIC, 'C');
		}
    }
}
