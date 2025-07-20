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

namespace LimePDF\Config;

class ConfigManager
{
    protected array $config = [];

    public function loadFromArray(array $settings): void
    {
        $this->config = array_merge($this->getDefaults(), $settings);
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->config[$key] = $value;
    }

    public function getAll(): array
    {
        return $this->config;
    }

    public function toArray(): array {
    return $this->config;
    }

    protected function getDefaults(): array
    {
        return [

            'allowedTcpdfTags' => '',            
            'author' => 'limePdf',

            'blankimage' => '_blank.png',

            'cellHeightRatio' => 1.25,            
            'creator' => 'limePDF',
            'curlOpts' => 'array()',

            'fontNameMain' => 'helvetica',
            'fontSizeMain' => 10,
            'fontNameData' => 'helvetica',
            'fontSizeData' => 8,
            'fontMonospaced' => 'courier',                     

            'headMagnification' => 1.1,
            'headerLogo' => '$tcpdf_header_logo',
            'headerLogoWidth' => 0,
            'headerTitle' => 'limePDF Example',
            'headerString' => "limePDF.com",
            'headerLogoWidth' => 30,            
 
            'imageScaleRatio' => 1.25,

            'keywords' => 'limePDF,TCPDF, PDF, example, test, guide',  

            'marginHeader' => 5,
            'marginFooter' => 10,
            'marginTop' => 27,
            'marginBottom' => 25,
            'marginLeft' => 15,
            'marginRight' => 15,            

            'pageFormat' => 'A4',
            'pageOrientation' => 'P',
            'pathMain' => 'dirname(__FILE__)/',
            'pathFonts' => '$k_path_main/fonts/',
            'pathUrl' => '$k_path_url',
            'pathImages' => '$tcpdf_images_path',
            'pathCache' => '$K_PATH_CACHE',

            'subject' => 'limePDF Tutorial',
            'smallRatio' => '2/3',

            'titleMagnification' => 1.3,       
            'thaiTopChars' => true,
            'tcpdfcAllsinhtml' => false,
            'tcpdfThrowExceptionerror' => false,
            'timeZone' => '@date_default_timezone_get()',

            'unit' => 'mm',


        ];
    }
}
