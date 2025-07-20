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

    protected function getDefaults(): array
    {
        return [
            'margin_top' => 27,
            'margin_bottom' => 25,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_header' => 5,
            'margin_footer' => 10,
            'font_family' => 'helvetica',
            'font_size_main' => 10,
            'font_size_data' => 8,
            'font_monospaced' => 'courier',
            'image_scale_ratio' => 1.25,
            'page_format' => 'A4',
            'page_orientation' => 'P',
            'creator' => 'limePDF',
            'author' => 'limePDF',
            'header_title' => 'limePDF Example',
            'header_string' => "limePDF\nwww.limePDF.com",
            'unit' => 'mm',
            'timezone' => 'UTC',
            'head_magnification' => 1.1,
            'cell_height_ratio' => 1.25,
            'title_magnification' => 1.3,
            'small_ratio' => 2/3,
            'thai_topchars' => true,
            'html_calls_enabled' => false,
            'allowed_html_tags' => '',
            'throw_exception_on_error' => false,
            'blank_image' => '_blank.png',
        ];
    }
}
