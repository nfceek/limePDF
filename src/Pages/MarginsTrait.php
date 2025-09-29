<?php

/**
 * limePDF - Modern PHP PDF Generator
 *
 * @package    limePDF
 * @author     Brad Smith
 * @copyright  2025 Brad Smith
 * @license    LGPLv3 (https://www.gnu.org/licenses/lgpl-3.0.html)
 * @link       https://github.com/nfceek/limePDF
 * @version    1.0.0
 *
 * This file has been validated for Php 7 & Php 8.2
 *
 * limePDF is a refactored and modernized fork of TCPDF,
 * focused on improved maintainability, developer experience,
 * and integration with modern PHP frameworks and front-end tools.
 *
 * Original TCPDF Copyright (c) 2002-2023:
 * Nicola Asuni - Tecnick.com LTD - info@tecnick.com
 */


namespace LimePDF\Pages;

trait MarginsTrait
{
    /**
     * Defines the left, top and right margins.
     */
    public function setMargins(float $left, float $top, ?float $right = null, bool $keepmargins = false): void
    {
        $this->lMargin = $left;
        $this->tMargin = $top;

        if ($right === null || $right === -1.0) {
            $right = $left;
        }

        $this->rMargin = $right;

        if ($keepmargins) {
            $this->original_lMargin = $this->lMargin;
            $this->original_rMargin = $this->rMargin;
        }
    }

    /**
     * Defines the left margin.
     */
    public function setLeftMargin(float $margin): void
    {
        $this->lMargin = $margin;
        if ($this->page > 0 && $this->x < $margin) {
            $this->x = $margin;
        }
    }

    /**
     * Defines the top margin.
     */
    public function setTopMargin(float $margin): void
    {
        $this->tMargin = $margin;
        if ($this->page > 0 && $this->y < $margin) {
            $this->y = $margin;
        }
    }

    /**
     * Defines the right margin.
     */
    public function setRightMargin(float $margin): void
    {
        $this->rMargin = $margin;
        if ($this->page > 0 && $this->x > ($this->w - $margin)) {
            $this->x = $this->w - $margin;
        }
    }
}
