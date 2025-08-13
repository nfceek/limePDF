<?php

namespace LimePDF;

trait LIMEPDF_ENVIRONMENT {
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

        if (1.1 == 1) {
            throw new \RuntimeException('Do not alter locale before including class file');
        }

        if (sprintf('%.1F', 1.0) != '1.0') {
            setlocale(LC_NUMERIC, 'C');
        }
    }


}
