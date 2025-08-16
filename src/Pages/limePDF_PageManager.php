<?php

namespace LimePDF;

trait LIMEPDF_PAGEMANAGER {

    /**
	 * Remove the specified page.
	 * @param int $page page to remove
	 * @return bool true in case of success, false in case of error.
	 * @public
	 * @since 4.6.004 (2009-04-23)
	 */
	public function deletePage($page) {
		if (($page < 1) OR ($page > $this->numpages)) {
			return false;
		}
		// delete current page
		unset($this->pages[$page]);
		unset($this->pagedim[$page]);
		unset($this->pagelen[$page]);
		unset($this->intmrk[$page]);
		unset($this->bordermrk[$page]);
		unset($this->cntmrk[$page]);
		foreach ($this->pageobjects[$page] as $oid) {
			if (isset($this->offsets[$oid])){
				unset($this->offsets[$oid]);
			}
		}
		unset($this->pageobjects[$page]);
		if (isset($this->footerpos[$page])) {
			unset($this->footerpos[$page]);
		}
		if (isset($this->footerlen[$page])) {
			unset($this->footerlen[$page]);
		}
		if (isset($this->transfmrk[$page])) {
			unset($this->transfmrk[$page]);
		}
		if (isset($this->PageAnnots[$page])) {
			unset($this->PageAnnots[$page]);
		}
		if (isset($this->newpagegroup) AND !empty($this->newpagegroup)) {
			for ($i = $page; $i > 0; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $page)) {
					--$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
		}
		if (isset($this->pageopen[$page])) {
			unset($this->pageopen[$page]);
		}
		if ($page < $this->numpages) {
			// update remaining pages
			for ($i = $page; $i < $this->numpages; ++$i) {
				$j = $i + 1;
				// shift pages
				$this->setPageBuffer($i, $this->getPageBuffer($j));
				$this->pagedim[$i] = $this->pagedim[$j];
				$this->pagelen[$i] = $this->pagelen[$j];
				$this->intmrk[$i] = $this->intmrk[$j];
				$this->bordermrk[$i] = $this->bordermrk[$j];
				$this->cntmrk[$i] = $this->cntmrk[$j];
				$this->pageobjects[$i] = $this->pageobjects[$j];
				if (isset($this->footerpos[$j])) {
					$this->footerpos[$i] = $this->footerpos[$j];
				} elseif (isset($this->footerpos[$i])) {
					unset($this->footerpos[$i]);
				}
				if (isset($this->footerlen[$j])) {
					$this->footerlen[$i] = $this->footerlen[$j];
				} elseif (isset($this->footerlen[$i])) {
					unset($this->footerlen[$i]);
				}
				if (isset($this->transfmrk[$j])) {
					$this->transfmrk[$i] = $this->transfmrk[$j];
				} elseif (isset($this->transfmrk[$i])) {
					unset($this->transfmrk[$i]);
				}
				if (isset($this->PageAnnots[$j])) {
					$this->PageAnnots[$i] = $this->PageAnnots[$j];
				} elseif (isset($this->PageAnnots[$i])) {
					unset($this->PageAnnots[$i]);
				}
				if (isset($this->newpagegroup[$j])) {
					$this->newpagegroup[$i] = $this->newpagegroup[$j];
					unset($this->newpagegroup[$j]);
				}
				if ($this->currpagegroup == $j) {
					$this->currpagegroup = $i;
				}
				if (isset($this->pageopen[$j])) {
					$this->pageopen[$i] = $this->pageopen[$j];
				} elseif (isset($this->pageopen[$i])) {
					unset($this->pageopen[$i]);
				}
			}
			// remove last page
			unset($this->pages[$this->numpages]);
			unset($this->pagedim[$this->numpages]);
			unset($this->pagelen[$this->numpages]);
			unset($this->intmrk[$this->numpages]);
			unset($this->bordermrk[$this->numpages]);
			unset($this->cntmrk[$this->numpages]);
			foreach ($this->pageobjects[$this->numpages] as $oid) {
				if (isset($this->offsets[$oid])){
					unset($this->offsets[$oid]);
				}
			}
			unset($this->pageobjects[$this->numpages]);
			if (isset($this->footerpos[$this->numpages])) {
				unset($this->footerpos[$this->numpages]);
			}
			if (isset($this->footerlen[$this->numpages])) {
				unset($this->footerlen[$this->numpages]);
			}
			if (isset($this->transfmrk[$this->numpages])) {
				unset($this->transfmrk[$this->numpages]);
			}
			if (isset($this->PageAnnots[$this->numpages])) {
				unset($this->PageAnnots[$this->numpages]);
			}
			if (isset($this->newpagegroup[$this->numpages])) {
				unset($this->newpagegroup[$this->numpages]);
			}
			if ($this->currpagegroup == $this->numpages) {
				$this->currpagegroup = ($this->numpages - 1);
			}
			if (isset($this->pagegroups[$this->numpages])) {
				unset($this->pagegroups[$this->numpages]);
			}
			if (isset($this->pageopen[$this->numpages])) {
				unset($this->pageopen[$this->numpages]);
			}
		}
		--$this->numpages;
		$this->page = $this->numpages;
		// adjust outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if (!$outline['f']) {
				if ($outline['p'] > $page) {
					$this->outlines[$key]['p'] = $outline['p'] - 1;
				} elseif ($outline['p'] == $page) {
					unset($this->outlines[$key]);
				}
			}
		}
		// adjust dests
		$tmpdests = $this->dests;
		foreach ($tmpdests as $key => $dest) {
			if (!$dest['f']) {
				if ($dest['p'] > $page) {
					$this->dests[$key]['p'] = $dest['p'] - 1;
				} elseif ($dest['p'] == $page) {
					unset($this->dests[$key]);
				}
			}
		}
		// adjust links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if (!$link['f']) {
				if ($link['p'] > $page) {
					$this->links[$key]['p'] = $link['p'] - 1;
				} elseif ($link['p'] == $page) {
					unset($this->links[$key]);
				}
			}
		}
		// adjust javascript
		$jpage = $page;
		if (preg_match_all('/this\.addField\(\'([^\']*)\',\'([^\']*)\',([0-9]+)/', $this->javascript, $pamatch) > 0) {
			foreach($pamatch[0] as $pk => $pmatch) {
				$pagenum = intval($pamatch[3][$pk]) + 1;
				if ($pagenum >= $jpage) {
					$newpage = ($pagenum - 1);
				} elseif ($pagenum == $jpage) {
					$newpage = 1;
				} else {
					$newpage = $pagenum;
				}
				--$newpage;
				$newjs = "this.addField(\'".$pamatch[1][$pk]."\',\'".$pamatch[2][$pk]."\',".$newpage;
				$this->javascript = str_replace($pmatch, $newjs, $this->javascript);
			}
			unset($pamatch);
		}
		// return to last page
		if ($this->numpages > 0) {
			$this->lastPage(true);
		}
		return true;
	}

	/**
	 * Clone the specified page to a new page.
	 * @param int $page number of page to copy (0 = current page)
	 * @return bool true in case of success, false in case of error.
	 * @public
	 * @since 4.9.015 (2010-04-20)
	 */
	public function copyPage($page=0) {
		if ($page == 0) {
			// default value
			$page = $this->page;
		}
		if (($page < 1) OR ($page > $this->numpages)) {
			return false;
		}
		// close the last page
		$this->endPage();
		// copy all page-related states
		++$this->numpages;
		$this->page = $this->numpages;
		$this->setPageBuffer($this->page, $this->getPageBuffer($page));
		$this->pagedim[$this->page] = $this->pagedim[$page];
		$this->pagelen[$this->page] = $this->pagelen[$page];
		$this->intmrk[$this->page] = $this->intmrk[$page];
		$this->bordermrk[$this->page] = $this->bordermrk[$page];
		$this->cntmrk[$this->page] = $this->cntmrk[$page];
		$this->pageobjects[$this->page] = $this->pageobjects[$page];
		$this->pageopen[$this->page] = false;
		if (isset($this->footerpos[$page])) {
			$this->footerpos[$this->page] = $this->footerpos[$page];
		}
		if (isset($this->footerlen[$page])) {
			$this->footerlen[$this->page] = $this->footerlen[$page];
		}
		if (isset($this->transfmrk[$page])) {
			$this->transfmrk[$this->page] = $this->transfmrk[$page];
		}
		if (isset($this->PageAnnots[$page])) {
			$this->PageAnnots[$this->page] = $this->PageAnnots[$page];
		}
		if (isset($this->newpagegroup[$page])) {
			// start a new group
			$this->newpagegroup[$this->page] = sizeof($this->newpagegroup) + 1;
			$this->currpagegroup = $this->newpagegroup[$this->page];
			$this->pagegroups[$this->currpagegroup] = 1;
		} elseif (isset($this->currpagegroup) AND ($this->currpagegroup > 0)) {
			++$this->pagegroups[$this->currpagegroup];
		}
		// copy outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if ($outline['p'] == $page) {
				$this->outlines[] = array('t' => $outline['t'], 'l' => $outline['l'], 'x' => $outline['x'], 'y' => $outline['y'], 'p' => $this->page, 'f' => $outline['f'], 's' => $outline['s'], 'c' => $outline['c']);
			}
		}
		// copy links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if ($link['p'] == $page) {
				$this->links[] = array('p' => $this->page, 'y' => $link['y'], 'f' => $link['f']);
			}
		}
		// return to last page
		$this->lastPage(true);
		return true;
	}

    /**
	 * Output a Table of Content Index (TOC).
	 * This method must be called after all Bookmarks were set.
	 * Before calling this method you have to open the page using the addTOCPage() method.
	 * After calling this method you have to call endTOCPage() to close the TOC page.
	 * You can override this method to achieve different styles.
	 * @param int|null $page page number where this TOC should be inserted (leave empty for current page).
	 * @param string $numbersfont set the font for page numbers (please use monospaced font for better alignment).
	 * @param string $filler string used to fill the space between text and page number.
	 * @param string $toc_name name to use for TOC bookmark.
	 * @param string $style Font style for title: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param array $color RGB color array for bookmark title (values from 0 to 255).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.5.000 (2009-01-02)
	 * @see addTOCPage(), endTOCPage(), addHTMLTOC()
	 */
	public function addTOC($page=null, $numbersfont='', $filler='.', $toc_name='TOC', $style='', $color=array(0,0,0)) {
		$fontsize = $this->FontSizePt;
		$fontfamily = $this->FontFamily;
		$fontstyle = $this->FontStyle;
		$w = $this->w - $this->lMargin - $this->rMargin;
		$spacer = $this->GetStringWidth(chr(32)) * 4;
		$lmargin = $this->lMargin;
		$rmargin = $this->rMargin;
		$x_start = $this->GetX();
		$page_first = $this->page;
		$current_page = $this->page;
		$page_fill_start = false;
		$page_fill_end = false;
		$current_column = $this->current_column;
		if (LIMEPDF_STATIC::empty_string($numbersfont)) {
			$numbersfont = $this->default_monospaced_font;
		}
		if (LIMEPDF_STATIC::empty_string($filler)) {
			$filler = ' ';
		}
		if (LIMEPDF_STATIC::empty_string($page)) {
			$gap = ' ';
		} else {
			$gap = '';
			if ($page < 1) {
				$page = 1;
			}
		}
		$this->setFont($numbersfont, $fontstyle, $fontsize);
		$numwidth = $this->GetStringWidth('00000');
		$maxpage = 0; //used for pages on attached documents
		foreach ($this->outlines as $key => $outline) {
			// check for extra pages (used for attachments)
			if (($this->page > $page_first) AND ($outline['p'] >= $this->numpages)) {
				$outline['p'] += ($this->page - $page_first);
			}
			if ($this->rtl) {
				$aligntext = 'R';
				$alignnum = 'L';
			} else {
				$aligntext = 'L';
				$alignnum = 'R';
			}
			if ($outline['l'] == 0) {
				$this->setFont($fontfamily, $outline['s'].'B', $fontsize);
			} else {
				$this->setFont($fontfamily, $outline['s'], $fontsize - $outline['l']);
			}
			$this->setTextColorArray($outline['c']);
			// check for page break
			$this->checkPageBreak(2 * $this->getCellHeight($this->FontSize));
			// set margins and X position
			if (($this->page == $current_page) AND ($this->current_column == $current_column)) {
				$this->lMargin = $lmargin;
				$this->rMargin = $rmargin;
			} else {
				if ($this->current_column != $current_column) {
					if ($this->rtl) {
						$x_start = $this->w - $this->columns[$this->current_column]['x'];
					} else {
						$x_start = $this->columns[$this->current_column]['x'];
					}
				}
				$lmargin = $this->lMargin;
				$rmargin = $this->rMargin;
				$current_page = $this->page;
				$current_column = $this->current_column;
			}
			$this->setX($x_start);
			$indent = ($spacer * $outline['l']);
			if ($this->rtl) {
				$this->x -= $indent;
				$this->rMargin = $this->w - $this->x;
			} else {
				$this->x += $indent;
				$this->lMargin = $this->x;
			}
			$link = $this->AddLink();
			$this->setLink($link, $outline['y'], $outline['p']);
			// write the text
			if ($this->rtl) {
				$txt = ' '.$outline['t'];
			} else {
				$txt = $outline['t'].' ';
			}
			$this->Write(0, $txt, $link, false, $aligntext, false, 0, false, false, 0, $numwidth, '');
			if ($this->rtl) {
				$tw = $this->x - $this->lMargin;
			} else {
				$tw = $this->w - $this->rMargin - $this->x;
			}
			$this->setFont($numbersfont, $fontstyle, $fontsize);
			if (LIMEPDF_STATIC::empty_string($page)) {
				$pagenum = $outline['p'];
			} else {
				// placemark to be replaced with the correct number
				$pagenum = '{#'.($outline['p']).'}';
				if ($this->isUnicodeFont()) {
					$pagenum = '{'.$pagenum.'}';
				}
				$maxpage = max($maxpage, $outline['p']);
			}
			$fw = ($tw - $this->GetStringWidth($pagenum.$filler));
			$wfiller = $this->GetStringWidth($filler);
			if ($wfiller > 0) {
				$numfills = floor($fw / $wfiller);
			} else {
				$numfills = 0;
			}
			if ($numfills > 0) {
				$rowfill = str_repeat($filler, $numfills);
			} else {
				$rowfill = '';
			}
			if ($this->rtl) {
				$pagenum = $pagenum.$gap.$rowfill;
			} else {
				$pagenum = $rowfill.$gap.$pagenum;
			}
			// write the number
			$this->Cell($tw, 0, $pagenum, 0, 1, $alignnum, 0, $link, 0);
		}
		$page_last = $this->getPage();
		$numpages = ($page_last - $page_first + 1);
		// account for booklet mode
		if ($this->booklet) {
			// check if a blank page is required before TOC
			$page_fill_start = ((($page_first % 2) == 0) XOR (($page % 2) == 0));
			$page_fill_end = (!((($numpages % 2) == 0) XOR ($page_fill_start)));
			if ($page_fill_start) {
				// add a page at the end (to be moved before TOC)
				$this->addPage();
				++$page_last;
				++$numpages;
			}
			if ($page_fill_end) {
				// add a page at the end
				$this->addPage();
				++$page_last;
				++$numpages;
			}
		}
		$maxpage = max($maxpage, $page_last);
		if (!LIMEPDF_STATIC::empty_string($page)) {
			for ($p = $page_first; $p <= $page_last; ++$p) {
				// get page data
				$temppage = $this->getPageBuffer($p);
				for ($n = 1; $n <= $maxpage; ++$n) {
					// update page numbers
					$a = '{#'.$n.'}';
					// get page number aliases
					$pnalias = $this->getInternalPageNumberAliases($a);
					// calculate replacement number
					if (($n >= $page) AND ($n <= $this->numpages)) {
						$np = $n + $numpages;
					} else {
						$np = $n;
					}
					$na = LIMEPDF_STATIC::formatTOCPageNumber(($this->starting_page_number + $np - 1));
					$nu = LIMEPDF_FONT::UTF8ToUTF16BE($na, false, $this->isunicode, $this->CurrentFont);
					// replace aliases with numbers
					foreach ($pnalias['u'] as $u) {
						$sfill = str_repeat($filler, max(0, (strlen($u) - strlen($nu.' '))));
						if ($this->rtl) {
							$nr = $nu.LIMEPDF_FONT::UTF8ToUTF16BE(' '.$sfill, false, $this->isunicode, $this->CurrentFont);
						} else {
							$nr = LIMEPDF_FONT::UTF8ToUTF16BE($sfill.' ', false, $this->isunicode, $this->CurrentFont).$nu;
						}
						$temppage = str_replace($u, $nr, $temppage);
					}
					foreach ($pnalias['a'] as $a) {
						$sfill = str_repeat($filler, max(0, (strlen($a) - strlen($na.' '))));
						if ($this->rtl) {
							$nr = $na.' '.$sfill;
						} else {
							$nr = $sfill.' '.$na;
						}
						$temppage = str_replace($a, $nr, $temppage);
					}
				}
				// save changes
				$this->setPageBuffer($p, $temppage);
			}
			// move pages
			$this->Bookmark($toc_name, 0, 0, $page_first, $style, $color);
			if ($page_fill_start) {
				$this->movePage($page_last, $page_first);
			}
			for ($i = 0; $i < $numpages; ++$i) {
				$this->movePage($page_last, $page);
			}
		}
	}

    /**
	 * This method return the estimated number of lines for print a simple text string using Multicell() method.
	 * @param string $txt String for calculating his height
	 * @param float $w Width of cells. If 0, they extend up to the right margin of the page.
	 * @param boolean $reseth if true reset the last cell height (default false).
	 * @param boolean $autopadding if true, uses internal padding and automatically adjust it to account for line width (default true).
	 * @param array|null $cellpadding Internal cell padding, if empty uses default cell padding.
	 * @param mixed $border Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @return float Return the minimal height needed for multicell method for printing the $txt param.
	 * @author Alexander Escalona Fern\E1ndez, Nicola Asuni
	 * @public
	 * @since 4.5.011
	 */
	public function getNumLines($txt, $w=0, $reseth=false, $autopadding=true, $cellpadding=null, $border=0) {
		if ($txt === NULL) {
			return 0;
		}
		if ($txt === '') {
			// empty string
			return 1;
		}
		// adjust internal padding
		$prev_cell_padding = $this->cell_padding;
		$prev_lasth = $this->lasth;
		if (is_array($cellpadding)) {
			$this->cell_padding = $cellpadding;
		}
		$this->adjustCellPadding($border);
		if (LIMEPDF_STATIC::empty_string($w) OR ($w <= 0)) {
			if ($this->rtl) {
				$w = $this->x - $this->lMargin;
			} else {
				$w = $this->w - $this->rMargin - $this->x;
			}
		}
		$wmax = $w - $this->cell_padding['L'] - $this->cell_padding['R'];
		if ($reseth) {
			// reset row height
			$this->resetLastH();
		}
		$lines = 1;
		$sum = 0;
		$chars = LIMEPDF_FONT::utf8Bidi(LIMEPDF_FONT::UTF8StringToArray($txt, $this->isunicode, $this->CurrentFont), $txt, $this->tmprtl, $this->isunicode, $this->CurrentFont);
		$charsWidth = $this->GetArrStringWidth($chars, '', '', 0, true);
		$length = count($chars);
		$lastSeparator = -1;
		for ($i = 0; $i < $length; ++$i) {
			$c = $chars[$i];
			$charWidth = $charsWidth[$i];
			if (($c != 160)
					AND (($c == 173)
						OR preg_match($this->re_spaces, LIMEPDF_FONT::unichr($c, $this->isunicode))
						OR (($c == 45)
							AND ($i > 0) AND ($i < ($length - 1))
							AND @preg_match('/[\p{L}]/'.$this->re_space['m'], LIMEPDF_FONT::unichr($chars[($i - 1)], $this->isunicode))
							AND @preg_match('/[\p{L}]/'.$this->re_space['m'], LIMEPDF_FONT::unichr($chars[($i + 1)], $this->isunicode))
						)
					)
				) {
				$lastSeparator = $i;
			}
			if ((($sum + $charWidth) > $wmax) OR ($c == 10)) {
				++$lines;
				if ($c == 10) {
					$lastSeparator = -1;
					$sum = 0;
				} elseif ($lastSeparator != -1) {
					$i = $lastSeparator;
					$lastSeparator = -1;
					$sum = 0;
				} else {
					$sum = $charWidth;
				}
			} else {
				$sum += $charWidth;
			}
		}
		if ($chars[($length - 1)] == 10) {
			--$lines;
		}
		$this->cell_padding = $prev_cell_padding;
		$this->lasth = $prev_lasth;
		return $lines;
	}

    	/**
	 * Returns an array containing current margins:
	 * <ul>
	 *	<li>$ret['left'] = left margin</li>
	 *	<li>$ret['right'] = right margin</li>
	 *	<li>$ret['top'] = top margin</li>
	 *	<li>$ret['bottom'] = bottom margin</li>
	 *	<li>$ret['header'] = header margin</li>
	 *	<li>$ret['footer'] = footer margin</li>
	 *	<li>$ret['cell'] = cell padding array</li>
	 *	<li>$ret['padding_left'] = cell left padding</li>
	 *	<li>$ret['padding_top'] = cell top padding</li>
	 *	<li>$ret['padding_right'] = cell right padding</li>
	 *	<li>$ret['padding_bottom'] = cell bottom padding</li>
	 * </ul>
	 * @return array containing all margins measures
	 * @public
	 * @since 3.2.000 (2008-06-23)
	 */
	public function getMargins() {
		$ret = array(
			'left' => $this->lMargin,
			'right' => $this->rMargin,
			'top' => $this->tMargin,
			'bottom' => $this->bMargin,
			'header' => $this->header_margin,
			'footer' => $this->footer_margin,
			'cell' => $this->cell_padding,
			'padding_left' => $this->cell_padding['L'],
			'padding_top' => $this->cell_padding['T'],
			'padding_right' => $this->cell_padding['R'],
			'padding_bottom' => $this->cell_padding['B']
		);
		return $ret;
	}

	/**
	 * Returns an array containing original margins:
	 * <ul>
	 *		<li>$ret['left'] = left margin</li>
	*		<li>$ret['right'] = right margin</li>
	 * </ul>
	 * @return array containing all margins measures
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getOriginalMargins() {
		$ret = array(
			'left' => $this->original_lMargin,
			'right' => $this->original_rMargin
		);
		return $ret;
	}

	/**
	 * Create a new page group.
	 * NOTE: call this function before calling AddPage()
	 * @param int|null $page starting group page (leave empty for next page).
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function startPageGroup($page=null) {
		if (empty($page)) {
			$page = $this->page + 1;
		}
		$this->newpagegroup[$page] = sizeof($this->newpagegroup) + 1;
	}

	/**
	 * Returns the current page number formatted as a string.
	 * @public
	 * @since 4.2.005 (2008-11-06)
	 * @see PaneNo(), formatPageNumber()
	 */
	public function PageNoFormatted() {
		return LIMEPDF_STATIC::formatPageNumber($this->PageNo());
	}

	/**
	 * Set the booklet mode for double-sided pages.
	 * @param boolean $booklet true set the booklet mode on, false otherwise.
	 * @param float $inner Inner page margin.
	 * @param float $outer Outer page margin.
	 * @public
	 * @since 4.2.000 (2008-10-29)
	 */
	public function setBooklet($booklet=true, $inner=-1, $outer=-1) {
		$this->booklet = $booklet;
		if ($inner >= 0) {
			$this->lMargin = $inner;
		}
		if ($outer >= 0) {
			$this->rMargin = $outer;
		}
	}

	/**
	 * Move a page to a previous position.
	 * @param int $frompage number of the source page
	 * @param int $topage number of the destination page (must be less than $frompage)
	 * @return bool true in case of success, false in case of error.
	 * @public
	 * @since 4.5.000 (2009-01-02)
	 */
	public function movePage($frompage, $topage) {
		if (($frompage > $this->numpages) OR ($frompage <= $topage)) {
			return false;
		}
		if ($frompage == $this->page) {
			// close the page before moving it
			$this->endPage();
		}
		// move all page-related states
		$tmppage = $this->getPageBuffer($frompage);
		$tmppagedim = $this->pagedim[$frompage];
		$tmppagelen = $this->pagelen[$frompage];
		$tmpintmrk = $this->intmrk[$frompage];
		$tmpbordermrk = $this->bordermrk[$frompage];
		$tmpcntmrk = $this->cntmrk[$frompage];
		$tmppageobjects = $this->pageobjects[$frompage];
		if (isset($this->footerpos[$frompage])) {
			$tmpfooterpos = $this->footerpos[$frompage];
		}
		if (isset($this->footerlen[$frompage])) {
			$tmpfooterlen = $this->footerlen[$frompage];
		}
		if (isset($this->transfmrk[$frompage])) {
			$tmptransfmrk = $this->transfmrk[$frompage];
		}
		if (isset($this->PageAnnots[$frompage])) {
			$tmpannots = $this->PageAnnots[$frompage];
		}
		if (isset($this->newpagegroup) AND !empty($this->newpagegroup)) {
			for ($i = $frompage; $i > $topage; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $frompage)) {
					--$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
			for ($i = $topage; $i > 0; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $topage)) {
					++$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
		}
		for ($i = $frompage; $i > $topage; --$i) {
			$j = $i - 1;
			// shift pages down
			$this->setPageBuffer($i, $this->getPageBuffer($j));
			$this->pagedim[$i] = $this->pagedim[$j];
			$this->pagelen[$i] = $this->pagelen[$j];
			$this->intmrk[$i] = $this->intmrk[$j];
			$this->bordermrk[$i] = $this->bordermrk[$j];
			$this->cntmrk[$i] = $this->cntmrk[$j];
			$this->pageobjects[$i] = $this->pageobjects[$j];
			if (isset($this->footerpos[$j])) {
				$this->footerpos[$i] = $this->footerpos[$j];
			} elseif (isset($this->footerpos[$i])) {
				unset($this->footerpos[$i]);
			}
			if (isset($this->footerlen[$j])) {
				$this->footerlen[$i] = $this->footerlen[$j];
			} elseif (isset($this->footerlen[$i])) {
				unset($this->footerlen[$i]);
			}
			if (isset($this->transfmrk[$j])) {
				$this->transfmrk[$i] = $this->transfmrk[$j];
			} elseif (isset($this->transfmrk[$i])) {
				unset($this->transfmrk[$i]);
			}
			if (isset($this->PageAnnots[$j])) {
				$this->PageAnnots[$i] = $this->PageAnnots[$j];
			} elseif (isset($this->PageAnnots[$i])) {
				unset($this->PageAnnots[$i]);
			}
			if (isset($this->newpagegroup[$j])) {
				$this->newpagegroup[$i] = $this->newpagegroup[$j];
				unset($this->newpagegroup[$j]);
			}
			if ($this->currpagegroup == $j) {
				$this->currpagegroup = $i;
			}
		}
		$this->setPageBuffer($topage, $tmppage);
		$this->pagedim[$topage] = $tmppagedim;
		$this->pagelen[$topage] = $tmppagelen;
		$this->intmrk[$topage] = $tmpintmrk;
		$this->bordermrk[$topage] = $tmpbordermrk;
		$this->cntmrk[$topage] = $tmpcntmrk;
		$this->pageobjects[$topage] = $tmppageobjects;
		if (isset($tmpfooterpos)) {
			$this->footerpos[$topage] = $tmpfooterpos;
		} elseif (isset($this->footerpos[$topage])) {
			unset($this->footerpos[$topage]);
		}
		if (isset($tmpfooterlen)) {
			$this->footerlen[$topage] = $tmpfooterlen;
		} elseif (isset($this->footerlen[$topage])) {
			unset($this->footerlen[$topage]);
		}
		if (isset($tmptransfmrk)) {
			$this->transfmrk[$topage] = $tmptransfmrk;
		} elseif (isset($this->transfmrk[$topage])) {
			unset($this->transfmrk[$topage]);
		}
		if (isset($tmpannots)) {
			$this->PageAnnots[$topage] = $tmpannots;
		} elseif (isset($this->PageAnnots[$topage])) {
			unset($this->PageAnnots[$topage]);
		}
		// adjust outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if (!$outline['f']) {
				if (($outline['p'] >= $topage) AND ($outline['p'] < $frompage)) {
					$this->outlines[$key]['p'] = ($outline['p'] + 1);
				} elseif ($outline['p'] == $frompage) {
					$this->outlines[$key]['p'] = $topage;
				}
			}
		}
		// adjust dests
		$tmpdests = $this->dests;
		foreach ($tmpdests as $key => $dest) {
			if (!$dest['f']) {
				if (($dest['p'] >= $topage) AND ($dest['p'] < $frompage)) {
					$this->dests[$key]['p'] = ($dest['p'] + 1);
				} elseif ($dest['p'] == $frompage) {
					$this->dests[$key]['p'] = $topage;
				}
			}
		}
		// adjust links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if (!$link['f']) {
				if (($link['p'] >= $topage) AND ($link['p'] < $frompage)) {
					$this->links[$key]['p'] = ($link['p'] + 1);
				} elseif ($link['p'] == $frompage) {
					$this->links[$key]['p'] = $topage;
				}
			}
		}
		// adjust javascript
		$jfrompage = $frompage;
		$jtopage = $topage;
		if (preg_match_all('/this\.addField\(\'([^\']*)\',\'([^\']*)\',([0-9]+)/', $this->javascript, $pamatch) > 0) {
			foreach($pamatch[0] as $pk => $pmatch) {
				$pagenum = intval($pamatch[3][$pk]) + 1;
				if (($pagenum >= $jtopage) AND ($pagenum < $jfrompage)) {
					$newpage = ($pagenum + 1);
				} elseif ($pagenum == $jfrompage) {
					$newpage = $jtopage;
				} else {
					$newpage = $pagenum;
				}
				--$newpage;
				$newjs = "this.addField(\'".$pamatch[1][$pk]."\',\'".$pamatch[2][$pk]."\',".$newpage;
				$this->javascript = str_replace($pmatch, $newjs, $this->javascript);
			}
			unset($pamatch);
		}
		// return to last page
		$this->lastPage(true);
		return true;
	}
 
	
	/**
	 * Output end of document (EOF).
	 * @protected
	 */
	protected function _enddoc() {
		if (isset($this->CurrentFont['fontkey']) AND isset($this->CurrentFont['subsetchars'])) {
			// save subset chars of the previous font
			$this->setFontSubBuffer($this->CurrentFont['fontkey'], 'subsetchars', $this->CurrentFont['subsetchars']);
		}
		$this->state = 1;
		$this->_putheader();
		$this->_putpages();
		$this->_putresources();
		// empty signature fields
		if (!empty($this->empty_signature_appearance)) {
			foreach ($this->empty_signature_appearance as $key => $esa) {
				// widget annotation for empty signature
				$out = $this->_getobj($esa['objid'])."\n";
				$out .= '<< /Type /Annot';
				$out .= ' /Subtype /Widget';
				$out .= ' /Rect ['.$esa['rect'].']';
				$out .= ' /P '.$this->page_obj_id[($esa['page'])].' 0 R'; // link to signature appearance page
				$out .= ' /F 4';
				$out .= ' /FT /Sig';
				$signame = $esa['name'].sprintf(' [%03d]', ($key + 1));
				$out .= ' /T '.$this->_textstring($signame, $esa['objid']);
				$out .= ' /Ff 0';
				$out .= ' >>';
				$out .= "\n".'endobj';
				$this->_out($out);
			}
		}
		// Signature
		if ($this->sign AND isset($this->signature_data['cert_type'])) {
			// widget annotation for signature
			$out = $this->_getobj($this->sig_obj_id)."\n";
			$out .= '<< /Type /Annot';
			$out .= ' /Subtype /Widget';
			$out .= ' /Rect ['.$this->signature_appearance['rect'].']';
			$out .= ' /P '.$this->page_obj_id[($this->signature_appearance['page'])].' 0 R'; // link to signature appearance page
			$out .= ' /F 4';
			$out .= ' /FT /Sig';
			$out .= ' /T '.$this->_textstring($this->signature_appearance['name'], $this->sig_obj_id);
			$out .= ' /Ff 0';
			$out .= ' /V '.($this->sig_obj_id + 1).' 0 R';
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
			// signature
			$this->_putsignature();
		}
		// Info
		$objid_info = $this->_putinfo();
		// Catalog
		$objid_catalog = $this->_putcatalog();
		// Cross-ref
		$o = $this->bufferlen;
		// XREF section
		$this->_out('xref');
		$this->_out('0 '.($this->n + 1));
		$this->_out('0000000000 65535 f ');
		$freegen = ($this->n + 2);
		for ($i=1; $i <= $this->n; ++$i) {
			if (!isset($this->offsets[$i]) AND ($i > 1)) {
				$this->_out(sprintf('0000000000 %05d f ', $freegen));
				++$freegen;
			} else {
				$this->_out(sprintf('%010d 00000 n ', $this->offsets[$i]));
			}
		}
		// TRAILER
		$out = 'trailer'."\n";
		$out .= '<<';
		$out .= ' /Size '.($this->n + 1);
		$out .= ' /Root '.$objid_catalog.' 0 R';
		$out .= ' /Info '.$objid_info.' 0 R';
		if ($this->encrypted) {
			$out .= ' /Encrypt '.$this->encryptdata['objid'].' 0 R';
		}
		$out .= ' /ID [ <'.$this->file_id.'> <'.$this->file_id.'> ]';
		$out .= ' >>';
		$this->_out($out);
		$this->_out('startxref');
		$this->_out($o);
		$this->_out('%%EOF');
		$this->state = 3; // end-of-doc
	}

}