<?php

namespace LimePDF\Pages;

use LimePDF\Include\FontTrait;
use LimePDF\Include\ImageTrait;
use LimePDF\Support\StaticTrait;

trait SectionsTrait {


	/**
	 * This method is used to render the page header.
	 * It is automatically called by AddPage() and could be overwritten in your own inherited class.
	 * @public
	 * 
	 * 2025 - PHP 7 / 8+ compliant rewrite
	 * @author Brad Smith
	 * @since ver 1.1
	 */
public function Header() {
    if ($this->header_xobjid === false) {
        $this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
        $headerfont = $this->getHeaderFont();
        $headerdata = $this->getHeaderData();

        $this->y = $this->header_margin;
        $this->x = $this->rtl ? $this->w - $this->original_rMargin : $this->original_lMargin;

        // normalize logo width
        $logoWidth = isset($headerdata['logo_width']) ? (float) $headerdata['logo_width'] : 0.0;

        // ✅ Always define $imgy safely
        $imgy = $this->y;

        if (!empty($headerdata['logo']) && $headerdata['logo'] !== K_BLANK_IMAGE) {
            $imgPath = $headerdata['logo'];

            if (!file_exists($imgPath)) {
                $imgPath = K_PATH_IMAGES . $headerdata['logo'];
            }

            if (file_exists($imgPath)) {
                $this->Image(
                    $imgPath,
                    $this->GetX(),
                    $this->GetY(),
                    $headerdata['logo_width']
                );
                $imgy = $this->getImageRBY();
            }
        }

        // text
        $cell_height = $this->getCellHeight($headerfont[2] / $this->k);
        $header_x = $this->getRTL()
            ? $this->original_rMargin + ($logoWidth * 1.1)
            : $this->original_lMargin + ($logoWidth * 1.1);

        $cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($logoWidth * 1.1);

        $this->setTextColorArray($this->header_text_color);

        // title
        $this->setFont($headerfont[0], 'B', $headerfont[2] + 1);
        $this->setX($header_x);
        $this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);

        // string
        $this->setFont($headerfont[0], $headerfont[1], $headerfont[2]);
        $this->setX($header_x);
        $this->MultiCell(
            $cw,
            $cell_height,
            $headerdata['string'],
            0,
            '',
            0,
            1,
            '',
            '',
            true,
            0,
            false,
            true,
            0,
            'T',
            false
        );

        // ending line
        $this->setLineStyle([
            'width' => 0.85 / $this->k,
            'cap'   => 'butt',
            'join'  => 'miter',
            'dash'  => 0,
            'color' => $headerdata['line_color'],
        ]);

        $this->setY((2.835 / $this->k) + max($imgy, $this->y));
        $this->setX($this->rtl ? $this->original_rMargin : $this->original_lMargin);
        $this->Cell($this->w - $this->original_lMargin - $this->original_rMargin, 0, '', 'T', 0, 'C');

        $this->endTemplate();
    }


// --	
// public function Header() {
//     if ($this->header_xobjid === false) {
//         // start a new XObject Template
//         $this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
//         $headerfont = $this->getHeaderFont();
//         $headerdata = $this->getHeaderData();

//         $this->y = $this->header_margin;
//         $this->x = $this->rtl ? $this->w - $this->original_rMargin : $this->original_lMargin;

//         // normalize logo width for arithmetic (safe for PHP 7 & 8+)
//         $logoWidth = isset($headerdata['logo_width']) ? (float) $headerdata['logo_width'] : 0.0;

// 		if (!empty($headerdata['logo']) && $headerdata['logo'] !== K_BLANK_IMAGE) {
// 			$imgPath = $headerdata['logo'];

// 			// If they only gave us a filename, try K_PATH_IMAGES
// 			if (!file_exists($imgPath)) {
// 				$imgPath = K_PATH_IMAGES . $headerdata['logo'];
// 			}

// 			if (file_exists($imgPath)) {
// 				$this->Image(
// 					$imgPath,
// 					$this->GetX(),
// 					$this->GetY(),
// 					$headerdata['logo_width']
// 				);
// 				$imgy = $this->getImageRBY();
// 			} else {
// 				$imgy = $this->y;
// 				//throw new \Exception("Header logo not found: " . $headerdata['logo']);
// 			}

//         // logo -- TODO remove after testing
//         // if (!empty($headerdata['logo']) && $headerdata['logo'] !== K_BLANK_IMAGE) {
//         //     $imgPath = K_PATH_IMAGES . $headerdata['logo'];
//         //     $imgtype = $this->getImageFileType($imgPath);

//         //     if ($imgtype === 'eps' || $imgtype === 'ai') {
//         //         $this->ImageEps($imgPath, '', '', $logoWidth);
//         //     } elseif ($imgtype === 'svg') {
//         //         $this->ImageSVG($imgPath, '', '', $logoWidth);
//         //     } else {
//         //         $this->Image($imgPath, '', '', $logoWidth);
//         //     }
//         //     $imgy = $this->getImageRBY();
//         // } else {
//         //     $imgy = $this->y;
//         // }

//         // text
//         $cell_height = $this->getCellHeight($headerfont[2] / $this->k);
//         $header_x = $this->getRTL()
//             ? $this->original_rMargin + ($logoWidth * 1.1)
//             : $this->original_lMargin + ($logoWidth * 1.1);

//         $cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($logoWidth * 1.1);

//         $this->setTextColorArray($this->header_text_color);

//         // title
//         $this->setFont($headerfont[0], 'B', $headerfont[2] + 1);
//         $this->setX($header_x);
//         $this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);

//         // string
//         $this->setFont($headerfont[0], $headerfont[1], $headerfont[2]);
//         $this->setX($header_x);
//         $this->MultiCell(
//             $cw,
//             $cell_height,
//             $headerdata['string'],
//             0,
//             '',
//             0,
//             1,
//             '',
//             '',
//             true,
//             0,
//             false,
//             true,
//             0,
//             'T',
//             false
//         );

//         // ending line
//         $this->setLineStyle([
//             'width' => 0.85 / $this->k,
//             'cap'   => 'butt',
//             'join'  => 'miter',
//             'dash'  => 0,
//             'color' => $headerdata['line_color'],
//         ]);

//         $this->setY((2.835 / $this->k) + max($imgy, $this->y));
//         $this->setX($this->rtl ? $this->original_rMargin : $this->original_lMargin);
//         $this->Cell($this->w - $this->original_lMargin - $this->original_rMargin, 0, '', 'T', 0, 'C');

//         $this->endTemplate();
//     }
//         // normalize logo width for arithmetic (safe for PHP 7 & 8+)
//         $logoWidth = isset($headerdata['logo_width']) ? (float) $headerdata['logo_width'] : 0.0;

// 		if (!empty($headerdata['logo']) && $headerdata['logo'] !== K_BLANK_IMAGE) {
// 			$imgPath = $headerdata['logo'];

// 			// If they only gave us a filename, try K_PATH_IMAGES
// 			if (!file_exists($imgPath)) {
// 				$imgPath = K_PATH_IMAGES . $headerdata['logo'];
// 			}

// 			if (file_exists($imgPath)) {
// 				$this->Image(
// 					$imgPath,
// 					$this->GetX(),
// 					$this->GetY(),
// 					$headerdata['logo_width']
// 				);
// 				$imgy = $this->getImageRBY();
// 			} else {
// 				$imgy = $this->y;
// 				//throw new \Exception("Header logo not found: " . $headerdata['logo']);
// 			}
// }
//         // logo -- TODO remove after testing
//         // if (!empty($headerdata['logo']) && $headerdata['logo'] !== K_BLANK_IMAGE) {
//         //     $imgPath = K_PATH_IMAGES . $headerdata['logo'];
//         //     $imgtype = $this->getImageFileType($imgPath);

//         //     if ($imgtype === 'eps' || $imgtype === 'ai') {
//         //         $this->ImageEps($imgPath, '', '', $logoWidth);
//         //     } elseif ($imgtype === 'svg') {
//         //         $this->ImageSVG($imgPath, '', '', $logoWidth);
//         //     } else {
//         //         $this->Image($imgPath, '', '', $logoWidth);
//         //     }
//         //     $imgy = $this->getImageRBY();
//         // } else {
//         //     $imgy = $this->y;
//         // }

//         // text
//         $cell_height = $this->getCellHeight($headerfont[2] / $this->k);
//         $header_x = $this->getRTL()
//             ? $this->original_rMargin + ($logoWidth * 1.1)
//             : $this->original_lMargin + ($logoWidth * 1.1);

//         $cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($logoWidth * 1.1);

//         $this->setTextColorArray($this->header_text_color);

//         // title
//         $this->setFont($headerfont[0], 'B', $headerfont[2] + 1);
//         $this->setX($header_x);
//         $this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);

//         // string
//         $this->setFont($headerfont[0], $headerfont[1], $headerfont[2]);
//         $this->setX($header_x);
//         $this->MultiCell(
//             $cw,
//             $cell_height,
//             $headerdata['string'],
//             0,
//             '',
//             0,
//             1,
//             '',
//             '',
//             true,
//             0,
//             false,
//             true,
//             0,
//             'T',
//             false
//         );

//         // ending line
//         $this->setLineStyle([
//             'width' => 0.85 / $this->k,
//             'cap'   => 'butt',
//             'join'  => 'miter',
//             'dash'  => 0,
//             'color' => $headerdata['line_color'],
//         ]);

//         $this->setY((2.835 / $this->k) + max($imgy, $this->y));
//         $this->setX($this->rtl ? $this->original_rMargin : $this->original_lMargin);
//         $this->Cell($this->w - $this->original_lMargin - $this->original_rMargin, 0, '', 'T', 0, 'C');

//         $this->endTemplate();
//     }

    // print header template
    $dx = 0;
    if (!$this->header_xobj_autoreset && $this->booklet && (($this->page % 2) === 0)) {
        $dx = ($this->original_lMargin - $this->original_rMargin);
    }

    $x = $this->rtl ? $this->w + $dx : 0 + $dx;

    $this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);

    if ($this->header_xobj_autoreset) {
        // reset header xobject template at each page
        $this->header_xobjid = false;
    }
}


	/**
	 * This method is used to render the page footer.
	 * It is automatically called by AddPage() and could be overwritten in your own inherited class.
	 * @public
	 */
	public function Footer() {
		$cur_y = $this->y;
		$this->setTextColorArray($this->footer_text_color);
		//set style for cell border
		$line_width = (0.85 / $this->k);
		$this->setLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
		//print document barcode
		$barcode = $this->getBarcode();
		if (!empty($barcode)) {
			$this->Ln($line_width);
			$barcode_width = round(($this->w - $this->original_lMargin - $this->original_rMargin) / 3);
			$style = array(
				'position' => $this->rtl?'R':'L',
				'align' => $this->rtl?'R':'L',
				'stretch' => false,
				'fitwidth' => true,
				'cellfitalign' => '',
				'border' => false,
				'padding' => 0,
				'fgcolor' => array(0,0,0),
				'bgcolor' => false,
				'text' => false
			);
			$this->write1DBarcode($barcode, 'C128', '', $cur_y + $line_width, '', (($this->footer_margin / 3) - $line_width), 0.3, $style, '');
		}
		$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
		if (empty($this->pagegroups)) {
			$pagenumtxt = $w_page.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $w_page.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
		}
		$this->setY($cur_y);
		//Print page number
		if ($this->getRTL()) {
			$this->setX($this->original_rMargin);
			$this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
		} else {
			$this->setX($this->original_lMargin);
			$this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, 'T', 0, 'R');
		}
	}

	/**
	 * This method is used to render the page header.
	 * @protected
	 * @since 4.0.012 (2008-07-24)
	 */
	protected function setHeader() {
		if (!$this->print_header OR ($this->state != 2)) {
			return;
		}
		$this->InHeader = true;
		$this->setGraphicVars($this->default_graphic_vars);
		$temp_thead = $this->thead;
		$temp_theadMargins = $this->theadMargins;
		$lasth = $this->lasth;
		$newline = $this->newline;
		$this->_outSaveGraphicsState();
		$this->rMargin = $this->original_rMargin;
		$this->lMargin = $this->original_lMargin;
		$this->setCellPadding(0);
		//set current position
		if ($this->rtl) {
			$this->setXY($this->original_rMargin, $this->header_margin);
		} else {
			$this->setXY($this->original_lMargin, $this->header_margin);
		}
		$this->setFont($this->header_font[0], $this->header_font[1], $this->header_font[2]);
		$this->Header();
		//restore position
		if ($this->rtl) {
			$this->setXY($this->original_rMargin, $this->tMargin);
		} else {
			$this->setXY($this->original_lMargin, $this->tMargin);
		}
		$this->_outRestoreGraphicsState();
		$this->lasth = $lasth;
		$this->thead = $temp_thead;
		$this->theadMargins = $temp_theadMargins;
		$this->newline = $newline;
		$this->InHeader = false;
	}

	/**
	 * This method is used to render the page footer.
	 * @protected
	 * @since 4.0.012 (2008-07-24)
	 */
	protected function setFooter() {
		if ($this->state != 2) {
			return;
		}
		$this->InFooter = true;
		// save current graphic settings
		$gvars = $this->getGraphicVars();
		// mark this point
		$this->footerpos[$this->page] = $this->pagelen[$this->page];
		$this->_out("\n");
		if ($this->print_footer) {
			$this->setGraphicVars($this->default_graphic_vars);
			$this->current_column = 0;
			$this->num_columns = 1;
			$temp_thead = $this->thead;
			$temp_theadMargins = $this->theadMargins;
			$lasth = $this->lasth;
			$this->_outSaveGraphicsState();
			$this->rMargin = $this->original_rMargin;
			$this->lMargin = $this->original_lMargin;
			$this->setCellPadding(0);
			//set current position
			$footer_y = $this->h - $this->footer_margin;
			if ($this->rtl) {
				$this->setXY($this->original_rMargin, $footer_y);
			} else {
				$this->setXY($this->original_lMargin, $footer_y);
			}
			$this->setFont($this->footer_font[0], $this->footer_font[1], $this->footer_font[2]);
			$this->Footer();
			//restore position
			if ($this->rtl) {
				$this->setXY($this->original_rMargin, $this->tMargin);
			} else {
				$this->setXY($this->original_lMargin, $this->tMargin);
			}
			$this->_outRestoreGraphicsState();
			$this->lasth = $lasth;
			$this->thead = $temp_thead;
			$this->theadMargins = $temp_theadMargins;
		}
		// restore graphic settings
		$this->setGraphicVars($gvars);
		$this->current_column = $gvars['current_column'];
		$this->num_columns = $gvars['num_columns'];
		// calculate footer length
		$this->footerlen[$this->page] = $this->pagelen[$this->page] - $this->footerpos[$this->page] + 1;
		$this->InFooter = false;
	}

	/**
	 * Check if we are on the page body (excluding page header and footer).
	 * @return bool true if we are not in page header nor in page footer, false otherwise.
	 * @protected
	 * @since 5.9.091 (2011-06-15)
	 */
	protected function inPageBody() {
		return (($this->InHeader === false) AND ($this->InFooter === false));
	}

	/**
	 * This method is used to render the table header on new page (if any).
	 * @protected
	 * @since 4.5.030 (2009-03-25)
	 */
	protected function setTableHeader() {
		if ($this->num_columns > 1) {
			// multi column mode
			return;
		}
		if (isset($this->theadMargins['top'])) {
			// restore the original top-margin
			$this->tMargin = $this->theadMargins['top'];
			$this->pagedim[$this->page]['tm'] = $this->tMargin;
			$this->y = $this->tMargin;
		}
		if (!$this->empty_string($this->thead) AND (!$this->inthead)) {
			// set margins
			$prev_lMargin = $this->lMargin;
			$prev_rMargin = $this->rMargin;
			$prev_cell_padding = $this->cell_padding;
			$this->lMargin = $this->theadMargins['lmargin'] + ($this->pagedim[$this->page]['olm'] - $this->pagedim[$this->theadMargins['page']]['olm']);
			$this->rMargin = $this->theadMargins['rmargin'] + ($this->pagedim[$this->page]['orm'] - $this->pagedim[$this->theadMargins['page']]['orm']);
			$this->cell_padding = $this->theadMargins['cell_padding'];
			if ($this->rtl) {
				$this->x = $this->w - $this->rMargin;
			} else {
				$this->x = $this->lMargin;
			}
			// account for special "cell" mode
			if ($this->theadMargins['cell']) {
				if ($this->rtl) {
					$this->x -= $this->cell_padding['R'];
				} else {
					$this->x += $this->cell_padding['L'];
				}
			}
			$gvars = $this->getGraphicVars();
			if (!empty($this->theadMargins['gvars'])) {
				// set the correct graphic style
				$this->setGraphicVars($this->theadMargins['gvars']);
				$this->rMargin = $gvars['rMargin'];
				$this->lMargin = $gvars['lMargin'];
			}
			// print table header
			$this->writeHTML($this->thead, false, false, false, false, '');
			$this->setGraphicVars($gvars);
			// set new top margin to skip the table headers
			if (!isset($this->theadMargins['top'])) {
				$this->theadMargins['top'] = $this->tMargin;
			}
			// store end of header position
			if (!isset($this->columns[0]['th'])) {
				$this->columns[0]['th'] = array();
			}
			$this->columns[0]['th']['\''.$this->page.'\''] = $this->y;
			$this->tMargin = $this->y;
			$this->pagedim[$this->page]['tm'] = $this->tMargin;
			$this->lasth = 0;
			$this->lMargin = $prev_lMargin;
			$this->rMargin = $prev_rMargin;
			$this->cell_padding = $prev_cell_padding;
		}
	}

	/**
	 * Returns the current page number.
	 * @return int page number
	 * @public
	 * @since 1.0
	 * @see getAliasNbPages()
	 */
	public function PageNo() {
		return $this->page;
	}

	/**
	 * Returns the length of a string in user unit. A font must be selected.<br>
	 * @param string $s The string whose length is to be computed
	 * @param string $fontname Family font. It can be either a name defined by AddFont() or one of the standard families. It is also possible to pass an empty string, in that case, the current family is retained.
	 * @param string $fontstyle Font style. Possible values are (case insensitive):<ul><li>empty string: regular</li><li>B: bold</li><li>I: italic</li><li>U: underline</li><li>D: line-through</li><li>O: overline</li></ul> or any combination. The default value is regular.
	 * @param float $fontsize Font size in points. The default value is the current size.
	 * @param boolean $getarray if true returns an array of characters widths, if false returns the total length.
	 * @return float[]|float total string length or array of characted widths
	 * @phpstan-return ($getarray is true ? float[] : float) total string length or array of characted widths
	 * @author Nicola Asuni
	 * @public
	 * @since 1.2
	 */
	public function GetStringWidth($s, $fontname='', $fontstyle='', $fontsize=0, $getarray=false) {
		return $this->GetArrStringWidth($this->utf8Bidi($this->UTF8StringToArray($s, $this->isunicode, $this->CurrentFont), $s, $this->tmprtl, $this->isunicode, $this->CurrentFont), $fontname, $fontstyle, $fontsize, $getarray);
	}

	/**
	 * Returns the string length of an array of chars in user unit or an array of characters widths. A font must be selected.<br>
	 * @param array $sa The array of chars whose total length is to be computed
	 * @param string $fontname Family font. It can be either a name defined by AddFont() or one of the standard families. It is also possible to pass an empty string, in that case, the current family is retained.
	 * @param string $fontstyle Font style. Possible values are (case insensitive):<ul><li>empty string: regular</li><li>B: bold</li><li>I: italic</li><li>U: underline</li><li>D: line through</li><li>O: overline</li></ul> or any combination. The default value is regular.
	 * @param float $fontsize Font size in points. The default value is the current size.
	 * @param boolean $getarray if true returns an array of characters widths, if false returns the total length.
	 * @return float[]|float total string length or array of characted widths
	 * @phpstan-return ($getarray is true ? float[] : float) total string length or array of characted widths
	 * @author Nicola Asuni
	 * @public
	 * @since 2.4.000 (2008-03-06)
	 */
	public function GetArrStringWidth($sa, $fontname='', $fontstyle='', $fontsize=0, $getarray=false) {
		// store current values
		if (!$this->empty_string($fontname)) {
			$prev_FontFamily = $this->FontFamily;
			$prev_FontStyle = $this->FontStyle;
			$prev_FontSizePt = $this->FontSizePt;
			$this->setFont($fontname, $fontstyle, $fontsize, '', 'default', false);
		}
		// convert UTF-8 array to Latin1 if required
		if ($this->isunicode AND (!$this->isUnicodeFont())) {
			$sa = $this->UTF8ArrToLatin1Arr($sa);
		}
		$w = 0; // total width
		$wa = array(); // array of characters widths
		foreach ($sa as $ck => $char) {
			// character width
			$cw = $this->GetCharWidth($char, isset($sa[($ck + 1)]));
			$wa[] = $cw;
			$w += $cw;
		}
		// restore previous values
		if (!$this->empty_string($fontname)) {
			$this->setFont($prev_FontFamily, $prev_FontStyle, $prev_FontSizePt, '', 'default', false);
		}
		if ($getarray) {
			return $wa;
		}
		return $w;
	}

	/**
	 * Returns the length of the char in user unit for the current font considering current stretching and spacing (tracking).
	 * @param int $char The char code whose length is to be returned
	 * @param boolean $notlast If false ignore the font-spacing.
	 * @return float char width
	 * @author Nicola Asuni
	 * @public
	 * @since 2.4.000 (2008-03-06)
	 */
	public function GetCharWidth($char, $notlast=true) {
		// get raw width
		$chw = $this->getRawCharWidth($char);
		if (($this->font_spacing < 0) OR (($this->font_spacing > 0) AND $notlast)) {
			// increase/decrease font spacing
			$chw += $this->font_spacing;
		}
		if ($this->font_stretching != 100) {
			// fixed stretching mode
			$chw *= ($this->font_stretching / 100);
		}
		return $chw;
	}

	/**
	 * Returns the length of the char in user unit for the current font.
	 * @param int $char The char code whose length is to be returned
	 * @return float char width
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-28)
	 */
	public function getRawCharWidth($char) {
		if ($char == 173) {
			// SHY character will not be printed
			return (0);
		}
		if (isset($this->CurrentFont['cw'][intval($char)])) {
			$w = $this->CurrentFont['cw'][intval($char)];
		} elseif (isset($this->CurrentFont['dw'])) {
			// default width
			$w = $this->CurrentFont['dw'];
		} elseif (isset($this->CurrentFont['cw'][32])) {
			// default width
			$w = $this->CurrentFont['cw'][32];
		} else {
			$w = 600;
		}
		return $this->getAbsFontMeasure($w);
	}

	/**
	 * Returns the numbero of characters in a string.
	 * @param string $s The input string.
	 * @return int number of characters
	 * @public
	 * @since 2.0.0001 (2008-01-07)
	 */
	public function GetNumChars($s) {
		if ($this->isUnicodeFont()) {
			return count($this->UTF8StringToArray($s, $this->isunicode, $this->CurrentFont));
		}
		return strlen($s);
	}

	/**
	 * Fill the list of available fonts ($this->fontlist).
	 * @protected
	 * @since 4.0.013 (2008-07-28)
	 */
	protected function getFontsList() {
		if (($fontsdir = opendir($this->_getfontpath())) !== false) {
			while (($file = readdir($fontsdir)) !== false) {
				if (substr($file, -4) == '.php') {
					array_push($this->fontlist, strtolower(basename($file, '.php')));
				}
			}
			closedir($fontsdir);
		}
	}


}