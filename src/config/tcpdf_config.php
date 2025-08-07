 <?php
        /**
         * limePDF - Modern PHP PDF Generator
         *
         * @package    limePDF
         * @author     Brad Smith <youremail@example.com>
         * @copyright  2025 Brad Smith
         * @license    LGPLv3 (https://www.gnu.org/licenses/lgpl-3.0.html)
         * @link       https://github.com/yourusername/limePDF
         * @version    1.0.0
         *
         * This project is a maintained fork of TCPDF by Nicola Asuni.
         * Original TCPDF Repository: https://github.com/tecnickcom/TCPDF
         *
         * limePDF is a refactored and modernized fork of TCPDF,
         * focused on improved maintainability, developer experience,
         * and integration with modern PHP frameworks and front-end tools.
         *
         * Original TCPDF Copyright (c) 2002-2023:
         * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
         */


        /**
         * Configuration file for limePDF
         * @author Brad Smith
         * @package com.limepdf
         * @version 1.0.0
         * @since 2025-07-19
         */

        // IMPORTANT:
        // If you define the constant K_TCPDF_EXTERNAL_CONFIG, all the following settings will be ignored.
        // If you use the tcpdf_autoconfig.php, then you can overwrite some values here.


        /**
         * Installation path (/var/www/limepdf/).
         * By default it is automatically calculated but you can also set it as a fixed string to improve performances.
         */
        //define ('K_PATH_MAIN', '');

        /**
         * URL path to tcpdf installation folder (http://localhost/limepdf/).
         * By default it is automatically set but you can also set it as a fixed string to improve performances.
         */
        //define ('K_PATH_URL', '');

        /**
         * Path for PDF fonts.
         * By default it is automatically set but you can also set it as a fixed string to improve performances.
         */
        //define ('K_PATH_FONTS', K_PATH_MAIN.'fonts/');

        /**
         * Default images directory.
         * By default it is automatically set but you can also set it as a fixed string to improve performances.
         */
        //define ('K_PATH_IMAGES', '');

        /**
         * Deafult image logo used be the default Header() method.
         * Please set here your own logo or an empty string to disable it.
         */
        //define ('PDF_HEADER_LOGO', '');

        /**
         * Header logo image width in user units.
         */
        //define ('PDF_HEADER_LOGO_WIDTH', 0);

        /**
         * Cache directory for temporary files (full path).
         */
        //define ('K_PATH_CACHE', '/tmp/');

        /**
         * Generic name for a blank image.
         */
        define ('K_BLANK_IMAGE', '_blank.png');

        /**
         * Page format.
         */
        define ('PDF_PAGE_FORMAT', 'A4');

        /**
         * Page orientation (P=portrait, L=landscape).
         */
        define ('PDF_PAGE_ORIENTATION', 'P');

        /**
         * Document creator.
         */
        define ('PDF_CREATOR', 'limePDF');

        /**
         * Document author.
         */
        define ('PDF_AUTHOR', 'limePDF');

        /**
         * Header title.
         */
        define ('PDF_HEADER_TITLE', 'limePDF Example');

        /**
         * Header description string.
         */
        define ('PDF_HEADER_STRING', "limePDF\nwww.limePDF.com");

        /**
         * Document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch].
         */
        define ('PDF_UNIT', 'mm');

        /**
         * Header margin.
         */
        define ('PDF_MARGIN_HEADER', 5);

        /**
         * Footer margin.
         */
        define ('PDF_MARGIN_FOOTER', 10);

        /**
         * Top margin.
         */
        define ('PDF_MARGIN_TOP', 27);

        /**
         * Bottom margin.
         */
        define ('PDF_MARGIN_BOTTOM', 25);

        /**
         * Left margin.
         */
        define ('PDF_MARGIN_LEFT', 15);

        /**
         * Right margin.
         */
        define ('PDF_MARGIN_RIGHT', 15);

        /**
         * Default main font name.
         */
        define ('PDF_FONT_NAME_MAIN', 'helvetica');

        /**
         * Default main font size.
         */
        define ('PDF_FONT_SIZE_MAIN', 10);

        /**
         * Default data font name.
         */
        define ('PDF_FONT_NAME_DATA', 'helvetica');

        /**
         * Default data font size.
         */
        define ('PDF_FONT_SIZE_DATA', 8);

        /**
         * Default monospaced font name.
         */
        define ('PDF_FONT_MONOSPACED', 'courier');

        /**
         * Ratio used to adjust the conversion of pixels to user units.
         */
        define ('PDF_IMAGE_SCALE_RATIO', 1.25);

        /**
         * Magnification factor for titles.
         */
        define('HEAD_MAGNIFICATION', 1.1);

        /**
         * Height of cell respect font height.
         */
        define('K_CELL_HEIGHT_RATIO', 1.25);

        /**
         * Title magnification respect main font size.
         */
        define('K_TITLE_MAGNIFICATION', 1.3);

        /**
         * Reduction factor for small font.
         */
        define('K_SMALL_RATIO', 2/3);

        /**
         * Set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language.
         */
        define('K_THAI_TOPCHARS', true);

        /**
         * If true allows to call TCPDF methods using HTML syntax
         * IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
         */
        define('K_TCPDF_CALLS_IN_HTML', false);

        /**
         * List of TCPDF methods that are allowed to be called using HTML syntax.
         * Note: each method name must end with surrounded with | (pipe) character.
         * The constant K_TCPDF_CALLS_IN_HTML must be set to true.
         * IMPORTANT: For security reason, disable this feature if you are allowing user HTML content.
         */
        define('K_ALLOWED_TCPDF_TAGS', '');

        /**
         * If true and PHP version is greater than 5, then the Error() method throw new exception instead of terminating the execution.
         */
        define('K_TCPDF_THROW_EXCEPTION_ERROR', false);

        /**
         * Default timezone for datetime functions
         */
        define('K_TIMEZONE', 'UTC');

        //============================================================+
        // END OF FILE
        //============================================================+
