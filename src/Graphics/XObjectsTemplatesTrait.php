<?php

namespace LimePDF\Graphics;

trait XObjectsTemplatesTrait{

	/**
	 * Start a new XObject Template.
	 * An XObject Template is a PDF block that is a self-contained description of any sequence of graphics objects (including path objects, text objects, and sampled images).
	 * An XObject Template may be painted multiple times, either on several pages or at several locations on the same page and produces the same results each time, subject only to the graphics state at the time it is invoked.
	 * Note: X,Y coordinates will be reset to 0,0.
	 * @param int $w Template width in user units (empty string or zero = page width less margins).
	 * @param int $h Template height in user units (empty string or zero = page height less margins).
	 * @param mixed $group Set transparency group. Can be a boolean value or an array specifying optional parameters: 'CS' (solour space name), 'I' (boolean flag to indicate isolated group) and 'K' (boolean flag to indicate knockout group).
	 * @return string|false the XObject Template ID in case of success or false in case of error.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.017 (2010-08-24)
	 * @see endTemplate(), printTemplate()
	 */
	public function startTemplate($w=0, $h=0, $group=false) {
		if ($this->inxobj) {
			// we are already inside an XObject template
			return false;
		}
		$this->inxobj = true;
		++$this->n;
		// XObject ID
		$this->xobjid = 'XT'.$this->n;
		// object ID
		$this->xobjects[$this->xobjid] = array('n' => $this->n);
		// store current graphic state
		$this->xobjects[$this->xobjid]['gvars'] = $this->getGraphicVars();
		// initialize data
		$this->xobjects[$this->xobjid]['intmrk'] = 0;
		$this->xobjects[$this->xobjid]['transfmrk'] = array();
		$this->xobjects[$this->xobjid]['outdata'] = '';
		$this->xobjects[$this->xobjid]['xobjects'] = array();
		$this->xobjects[$this->xobjid]['images'] = array();
		$this->xobjects[$this->xobjid]['fonts'] = array();
		$this->xobjects[$this->xobjid]['annotations'] = array();
		$this->xobjects[$this->xobjid]['extgstates'] = array();
		$this->xobjects[$this->xobjid]['gradients'] = array();
		$this->xobjects[$this->xobjid]['spot_colors'] = array();
		// set new environment
		$this->num_columns = 1;
		$this->current_column = 0;
		$this->setAutoPageBreak(false);
		if (($w === '') OR ($w <= 0)) {
			$w = $this->w - $this->lMargin - $this->rMargin;
		}
		if (($h === '') OR ($h <= 0)) {
			$h = $this->h - $this->tMargin - $this->bMargin;
		}
		$this->xobjects[$this->xobjid]['x'] = 0;
		$this->xobjects[$this->xobjid]['y'] = 0;
		$this->xobjects[$this->xobjid]['w'] = $w;
		$this->xobjects[$this->xobjid]['h'] = $h;
		$this->w = $w;
		$this->h = $h;
		$this->wPt = $this->w * $this->k;
		$this->hPt = $this->h * $this->k;
		$this->fwPt = $this->wPt;
		$this->fhPt = $this->hPt;
		$this->x = 0;
		$this->y = 0;
		$this->lMargin = 0;
		$this->rMargin = 0;
		$this->tMargin = 0;
		$this->bMargin = 0;
		// set group mode
		$this->xobjects[$this->xobjid]['group'] = $group;
		return $this->xobjid;
	}

	/**
	 * End the current XObject Template started with startTemplate() and restore the previous graphic state.
	 * An XObject Template is a PDF block that is a self-contained description of any sequence of graphics objects (including path objects, text objects, and sampled images).
	 * An XObject Template may be painted multiple times, either on several pages or at several locations on the same page and produces the same results each time, subject only to the graphics state at the time it is invoked.
	 * @return string|false the XObject Template ID in case of success or false in case of error.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.017 (2010-08-24)
	 * @see startTemplate(), printTemplate()
	 */
	public function endTemplate() {
		if (!$this->inxobj) {
			// we are not inside a template
			return false;
		}
		$this->inxobj = false;
		// restore previous graphic state
		$this->setGraphicVars($this->xobjects[$this->xobjid]['gvars'], true);
		return $this->xobjid;
	}

	/**
	 * Print an XObject Template.
	 * You can print an XObject Template inside the currently opened Template.
	 * An XObject Template is a PDF block that is a self-contained description of any sequence of graphics objects (including path objects, text objects, and sampled images).
	 * An XObject Template may be painted multiple times, either on several pages or at several locations on the same page and produces the same results each time, subject only to the graphics state at the time it is invoked.
	 * @param string $id The ID of XObject Template to print.
	 * @param float|null $x X position in user units (empty string = current x position)
	 * @param float|null $y Y position in user units (empty string = current y position)
	 * @param float $w Width in user units (zero = remaining page width)
	 * @param float $h Height in user units (zero = remaining page height)
	 * @param string $align Indicates the alignment of the pointer next to template insertion relative to template height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param string $palign Allows to center or align the template on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param boolean $fitonpage If true the template is resized to not exceed page dimensions.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.017 (2010-08-24)
	 * @see startTemplate(), endTemplate()
	 */
	public function printTemplate($id, $x=null, $y=null, $w=0, $h=0, $align='', $palign='', $fitonpage=false) {
		if ($this->state != 2) {
			 return;
		}
		if (!isset($this->xobjects[$id])) {
			$this->Error('The XObject Template \''.$id.'\' doesn\'t exist!');
		}
		if ($this->inxobj) {
			if ($id == $this->xobjid) {
				// close current template
				$this->endTemplate();
			} else {
				// use the template as resource for the template currently opened
				$this->xobjects[$this->xobjid]['xobjects'][$id] = $this->xobjects[$id];
			}
		}
		// set default values
		if (LIMEPDF_STATIC::empty_string($x)) {
			$x = $this->x;
		}
		if (LIMEPDF_STATIC::empty_string($y)) {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		$ow = $this->xobjects[$id]['w'];
		if ($ow <= 0) {
			$ow = 1;
		}
		$oh = $this->xobjects[$id]['h'];
		if ($oh <= 0) {
			$oh = 1;
		}
		// calculate template width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			$w = $ow;
			$h = $oh;
		} elseif ($w <= 0) {
			$w = $h * $ow / $oh;
		} elseif ($h <= 0) {
			$h = $w * $oh / $ow;
		}
		// fit the template on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		// set page alignment
		$rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($palign == 'L') {
				$xt = $this->lMargin;
			} elseif ($palign == 'C') {
				$xt = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$xt = $this->w - $this->rMargin - $w;
			} else {
				$xt = $x - $w;
			}
			$rb_x = $xt;
		} else {
			if ($palign == 'L') {
				$xt = $this->lMargin;
			} elseif ($palign == 'C') {
				$xt = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$xt = $this->w - $this->rMargin - $w;
			} else {
				$xt = $x;
			}
			$rb_x = $xt + $w;
		}
		// print XObject Template + Transformation matrix
		$this->StartTransform();
		// translate and scale
		$sx = ($w / $ow);
		$sy = ($h / $oh);
		$tm = array();
		$tm[0] = $sx;
		$tm[1] = 0;
		$tm[2] = 0;
		$tm[3] = $sy;
		$tm[4] = $xt * $this->k;
		$tm[5] = ($this->h - $h - $y) * $this->k;
		$this->Transform($tm);
		// set object
		$this->_out('/'.$id.' Do');
		$this->StopTransform();
		// add annotations
		if (!empty($this->xobjects[$id]['annotations'])) {
			foreach ($this->xobjects[$id]['annotations'] as $annot) {
				// transform original coordinates
				$coordlt = LIMEPDF_STATIC::getTransformationMatrixProduct($tm, array(1, 0, 0, 1, ($annot['x'] * $this->k), (-$annot['y'] * $this->k)));
				$ax = ($coordlt[4] / $this->k);
				$ay = ($this->h - $h - ($coordlt[5] / $this->k));
				$coordrb = LIMEPDF_STATIC::getTransformationMatrixProduct($tm, array(1, 0, 0, 1, (($annot['x'] + $annot['w']) * $this->k), ((-$annot['y'] - $annot['h']) * $this->k)));
				$aw = ($coordrb[4] / $this->k) - $ax;
				$ah = ($this->h - $h - ($coordrb[5] / $this->k)) - $ay;
				$this->Annotation($ax, $ay, $aw, $ah, $annot['text'], $annot['opt'], $annot['spaces']);
			}
		}
		// set pointer to align the next text/objects
		switch($align) {
			case 'T': {
				$this->y = $y;
				$this->x = $rb_x;
				break;
			}
			case 'M': {
				$this->y = $y + round($h/2);
				$this->x = $rb_x;
				break;
			}
			case 'B': {
				$this->y = $rb_y;
				$this->x = $rb_x;
				break;
			}
			case 'N': {
				$this->setY($rb_y);
				break;
			}
			default:{
				break;
			}
		}
	}

	/**
	 * Set the percentage of character stretching.
	 * @param int $perc percentage of stretching (100 = no stretching)
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-29)
	 */
	public function setFontStretching($perc=100) {
		$this->font_stretching = $perc;
	}

	/**
	 * Get the percentage of character stretching.
	 * @return float stretching value
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-29)
	 */
	public function getFontStretching() {
		return $this->font_stretching;
	}

	/**
	 * Set the amount to increase or decrease the space between characters in a text.
	 * @param float $spacing amount to increase or decrease the space between characters in a text (0 = default spacing)
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-29)
	 */
	public function setFontSpacing($spacing=0) {
		$this->font_spacing = $spacing;
	}

	/**
	 * Get the amount to increase or decrease the space between characters in a text.
	 * @return int font spacing (tracking) value
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-29)
	 */
	public function getFontSpacing() {
		return $this->font_spacing;
	}

	/**
	 * Return an array of no-write page regions
	 * @return array of no-write page regions
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.003 (2010-10-13)
	 * @see setPageRegions(), addPageRegion()
	 */
	public function getPageRegions() {
		return $this->page_regions;
	}

	/**
	 * Set no-write regions on page.
	 * A no-write region is a portion of the page with a rectangular or trapezium shape that will not be covered when writing text or html code.
	 * A region is always aligned on the left or right side of the page ad is defined using a vertical segment.
	 * You can set multiple regions for the same page.
	 * @param array $regions array of no-write regions. For each region you can define an array as follow: ('page' => page number or empy for current page, 'xt' => X top, 'yt' => Y top, 'xb' => X bottom, 'yb' => Y bottom, 'side' => page side 'L' = left or 'R' = right). Omit this parameter to remove all regions.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.003 (2010-10-13)
	 * @see addPageRegion(), getPageRegions()
	 */
	public function setPageRegions($regions=array()) {
		// empty current regions array
		$this->page_regions = array();
		// add regions
		foreach ($regions as $data) {
			$this->addPageRegion($data);
		}
	}

	/**
	 * Add a single no-write region on selected page.
	 * A no-write region is a portion of the page with a rectangular or trapezium shape that will not be covered when writing text or html code.
	 * A region is always aligned on the left or right side of the page ad is defined using a vertical segment.
	 * You can set multiple regions for the same page.
	 * @param array $region array of a single no-write region array: ('page' => page number or empy for current page, 'xt' => X top, 'yt' => Y top, 'xb' => X bottom, 'yb' => Y bottom, 'side' => page side 'L' = left or 'R' = right).
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.003 (2010-10-13)
	 * @see setPageRegions(), getPageRegions()
	 */
	public function addPageRegion($region) {
		if (!isset($region['page']) OR empty($region['page'])) {
			$region['page'] = $this->page;
		}
		if (isset($region['xt']) AND isset($region['xb']) AND ($region['xt'] > 0) AND ($region['xb'] > 0)
			AND isset($region['yt'])  AND isset($region['yb']) AND ($region['yt'] >= 0) AND ($region['yt'] < $region['yb'])
			AND isset($region['side']) AND (($region['side'] == 'L') OR ($region['side'] == 'R'))) {
			$this->page_regions[] = $region;
		}
	}

	/**
	 * Remove a single no-write region.
	 * @param int $key region key
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.003 (2010-10-13)
	 * @see setPageRegions(), getPageRegions()
	 */
	public function removePageRegion($key) {
		if (isset($this->page_regions[$key])) {
			unset($this->page_regions[$key]);
		}
	}

	/**
	 * Check page for no-write regions and adapt current coordinates and page margins if necessary.
	 * A no-write region is a portion of the page with a rectangular or trapezium shape that will not be covered when writing text or html code.
	 * A region is always aligned on the left or right side of the page ad is defined using a vertical segment.
	 * @param float $h height of the text/image/object to print in user units
	 * @param float $x current X coordinate in user units
	 * @param float $y current Y coordinate in user units
	 * @return float[] array($x, $y)
	 * @author Nicola Asuni
	 * @protected
	 * @since 5.9.003 (2010-10-13)
	 */
	protected function checkPageRegions($h, $x, $y) {
		// set default values
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		if (!$this->check_page_regions OR empty($this->page_regions)) {
			// no page regions defined
			return array($x, $y);
		}
		if (empty($h)) {
			$h = $this->getCellHeight($this->FontSize);
		}
		// check for page break
		if ($this->checkPageBreak($h, $y)) {
			// the content will be printed on a new page
			$x = $this->x;
			$y = $this->y;
		}
		if ($this->num_columns > 1) {
			if ($this->rtl) {
				$this->lMargin = ($this->columns[$this->current_column]['x'] - $this->columns[$this->current_column]['w']);
			} else {
				$this->rMargin = ($this->w - $this->columns[$this->current_column]['x'] - $this->columns[$this->current_column]['w']);
			}
		} else {
			if ($this->rtl) {
				$this->lMargin = max($this->clMargin, $this->original_lMargin);
			} else {
				$this->rMargin = max($this->crMargin, $this->original_rMargin);
			}
		}
		// adjust coordinates and page margins
		foreach ($this->page_regions as $regid => $regdata) {
			if ($regdata['page'] == $this->page) {
				// check region boundaries
				if (($y > ($regdata['yt'] - $h)) AND ($y <= $regdata['yb'])) {
					// Y is inside the region
					$minv = ($regdata['xb'] - $regdata['xt']) / ($regdata['yb'] - $regdata['yt']); // inverse of angular coefficient
					$yt = max($y, $regdata['yt']);
					$yb = min(($yt + $h), $regdata['yb']);
					$xt = (($yt - $regdata['yt']) * $minv) + $regdata['xt'];
					$xb = (($yb - $regdata['yt']) * $minv) + $regdata['xt'];
					if ($regdata['side'] == 'L') { // left side
						$new_margin = max($xt, $xb);
						if ($this->lMargin < $new_margin) {
							if ($this->rtl) {
								// adjust left page margin
								$this->lMargin = max(0, $new_margin);
							}
							if ($x < $new_margin) {
								// adjust x position
								$x = $new_margin;
								if ($new_margin > ($this->w - $this->rMargin)) {
									// adjust y position
									$y = $regdata['yb'] - $h;
								}
							}
						}
					} elseif ($regdata['side'] == 'R') { // right side
						$new_margin = min($xt, $xb);
						if (($this->w - $this->rMargin) > $new_margin) {
							if (!$this->rtl) {
								// adjust right page margin
								$this->rMargin = max(0, ($this->w - $new_margin));
							}
							if ($x > $new_margin) {
								// adjust x position
								$x = $new_margin;
								if ($new_margin > $this->lMargin) {
									// adjust y position
									$y = $regdata['yb'] - $h;
								}
							}
						}
					}
				}
			}
		}
		return array($x, $y);
	}



    /**
     * Keeps files in memory, so it doesn't need to downloaded everytime in a loop
     * @param string $file
     * @return string
     */
    protected function getCachedFileContents($file)
    {
        if (!isset($this->fileContentCache[$file])) {
            $this->fileContentCache[$file] = LIMEPDF_STATIC::fileGetContents($file);
        }
        return $this->fileContentCache[$file];
    }

    /**
     * Avoid multiple calls to an external server to see if a file exists
     * @param string $file
     * @return bool
     */
    protected function fileExists($file)
    {
        if (isset($this->fileContentCache[$file]) || false !== $this->getImageBuffer($file)) {
            return true;
        }

        return LIMEPDF_STATIC::file_exists($file);
    }

	/**
	 * Wrapper for unlink with disabled protocols.
	 * @param string $file
	 * @return bool
	 */
	protected function _unlink($file)
	{
		if ((strpos($file, '://') !== false) && ((substr($file, 0, 7) !== 'file://') || (!$this->allowLocalFiles))) {
			// forbidden protocol
			return false;
		}
		return @unlink($file);
	}


}