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

            'allowedtcpdftags' => '',            
            'author' => 'limePdf',

            'blankimage' => '_blank.png',

            'cellheightratio' => 1.25,            
            'creator' => 'limePDF',
            'curlopts' => 'array()',

            'fontnamemain' => 'helvetica',
            'fontsizemain' => 10,
            'fontnamedata' => 'helvetica',
            'fontsizedata' => 8,
            'fontmonospaced' => 'courier',                     

            'headmagnification' => 1.1,
            'headerlogo' => '$tcpdf_header_logo',
            'headerlogowidth' => 0,
            'headertitle' => 'limePDF Example',
            'headerstring' => "limePDF.com",
            'headerlogowidth' => 30,            
 
            'imagescaleratio' => 1.25,

            'keywords' => 'limePDF,TCPDF, PDF, example, test, guide',  

            'marginheader' => 5,
            'marginfooter' => 10,
            'margintop' => 27,
            'marginbottom' => 25,
            'marginleft' => 15,
            'marginright' => 15,            

            'pageformat' => 'A4',
            'pageorientation' => 'P',
            'pathmain' => 'dirname(__FILE__)/',
            'pathfonts' => '$k_path_main/fonts/',
            'pathurl' => '$k_path_url',
            'pathimages' => '$tcpdf_images_path',
            'pathcache' => '$K_PATH_CACHE',

            'subject' => 'limePDF Tutorial',
            'smallratio' => '2/3',

            'titlemagnification' => 1.3,       
            'thaitopchars' => true,
            'tcpdfcallsinhtml' => false,
            'tcpdfthrowexceptionerror' => false,
            'timezone' => '@date_default_timezone_get()',

            'unit' => 'mm',


        ];
    }
}
