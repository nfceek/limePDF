<?php

namespace LimePDF\Utils;

trait MiscTrait {

	/**
	 * Set the block dimensions accounting for page breaks and page/column fitting
	 * @param float $w width
	 * @param float $h height
	 * @param float $x X coordinate
	 * @param float $y Y coodiante
	 * @param boolean $fitonpage if true the block is resized to not exceed page dimensions.
	 * @return array array($w, $h, $x, $y)
	 * @protected
	 * @since 5.5.009 (2010-07-05)
	 */
	protected function fitBlock($w, $h, $x, $y, $fitonpage=false) {
		if ($w <= 0) {
			// set maximum width
			$w = ($this->w - $this->lMargin - $this->rMargin);
			if ($w <= 0) {
				$w = 1;
			}
		}
		if ($h <= 0) {
			// set maximum height
			$h = ($this->PageBreakTrigger - $this->tMargin);
			if ($h <= 0) {
				$h = 1;
			}
		}
		// resize the block to be vertically contained on a single page or single column
		if ($fitonpage OR $this->AutoPageBreak) {
			$ratio_wh = ($w / $h);
			if ($h > ($this->PageBreakTrigger - $this->tMargin)) {
				$h = $this->PageBreakTrigger - $this->tMargin;
				$w = ($h * $ratio_wh);
			}
			// resize the block to be horizontally contained on a single page or single column
			if ($fitonpage) {
				$maxw = ($this->w - $this->lMargin - $this->rMargin);
				if ($w > $maxw) {
					$w = $maxw;
					$h = ($w / $ratio_wh);
				}
			}
		}
		// Check whether we need a new page or new column first as this does not fit
		$prev_x = $this->x;
		$prev_y = $this->y;
		if ($this->checkPageBreak($h, $y) OR ($this->y < $prev_y)) {
			$y = $this->y;
			if ($this->rtl) {
				$x += ($prev_x - $this->x);
			} else {
				$x += ($this->x - $prev_x);
			}
			$this->newline = true;
		}
		// resize the block to be contained on the remaining available page or column space
		if ($fitonpage) {
			// fallback to avoid division by zero
			$h = $h == 0 ? 1 : $h;
			$ratio_wh = ($w / $h);
			if (($y + $h) > $this->PageBreakTrigger) {
				$h = $this->PageBreakTrigger - $y;
				$w = ($h * $ratio_wh);
			}
			if ((!$this->rtl) AND (($x + $w) > ($this->w - $this->rMargin))) {
				$w = $this->w - $this->rMargin - $x;
				$h = ($w / $ratio_wh);
			} elseif (($this->rtl) AND (($x - $w) < ($this->lMargin))) {
				$w = $x - $this->lMargin;
				$h = ($w / $ratio_wh);
			}
		}
		return array($w, $h, $x, $y);
	}

	/**
	 * Replace right shift page number aliases with spaces to correct right alignment.
	 * This works perfectly only when using monospaced fonts.
	 * @param string $page Page content.
	 * @param array $aliases Array of page aliases.
	 * @param int $diff initial difference to add.
	 * @return string replaced page content.
	 * @protected
	 */
	protected function replaceRightShiftPageNumAliases($page, $aliases, $diff) {
		foreach ($aliases as $type => $alias) {
			foreach ($alias as $a) {
				// find position of compensation factor
				$startnum = (strpos($a, ':') + 1);
				$a = substr($a, 0, $startnum);
				if (($pos = strpos($page, $a)) !== false) {
					// end of alias
					$endnum = strpos($page, '}', $pos);
					// string to be replaced
					$aa = substr($page, $pos, ($endnum - $pos + 1));
					// get compensation factor
					$ratio = substr($page, ($pos + $startnum), ($endnum - $pos - $startnum));
					$ratio = preg_replace('/[^0-9\.]/', '', $ratio);
					$ratio = floatval($ratio);
					if ($type == 'u') {
						$chrdiff = floor(($diff + 12) * $ratio);
						$shift = str_repeat(' ', $chrdiff);
						$shift = LIMEPDF_FONT::UTF8ToUTF16BE($shift, false, $this->isunicode, $this->CurrentFont);
					} else {
						$chrdiff = floor(($diff + 11) * $ratio);
						$shift = str_repeat(' ', $chrdiff);
					}
					$page = str_replace($aa, $shift, $page);
				}
			}
		}
		return $page;
	}

	/**
	 * Returns a formatted date for meta information
	 * @param int $n Object ID.
	 * @param int $timestamp Timestamp to convert.
	 * @return string escaped date string.
	 * @protected
	 * @since 4.6.028 (2009-08-25)
	 */
	protected function _datestring($n=0, $timestamp=0) {
		if ((empty($timestamp)) OR ($timestamp < 0)) {
			$timestamp = $this->doc_creation_timestamp;
		}
		return $this->_datastring('D:'.LIMEPDF_STATIC::getFormattedDate($timestamp), $n);
	}

	/**
	 * Format a text string for meta information
	 * @param string $s string to escape.
	 * @param int $n object ID
	 * @return string escaped string.
	 * @protected
	 */
	protected function _textstring($s, $n=0) {
		if ($this->isunicode) {
			//Convert string to UTF-16BE
			$s = LIMEPDF_FONT::UTF8ToUTF16BE($s, true, $this->isunicode, $this->CurrentFont);
		}
		return $this->_datastring($s, $n);
	}

	/**
	 * get raw output stream.
	 * @param string $s string to output.
	 * @param int $n object reference for encryption mode
	 * @protected
	 * @author Nicola Asuni
	 * @since 5.5.000 (2010-06-22)
	 */
	public function _getrawstream($s, $n=0) {
		if ($n <= 0) {
			// default to current object
			$n = $this->n;
		}
		return $this->_encrypt_data($n, $s);
	}

    	/**
	 * Start a new pdf layer.
	 * @param string $name Layer name (only a-z letters and numbers). Leave empty for automatic name.
	 * @param boolean|null $print Set to TRUE to print this layer, FALSE to not print and NULL to not set this option
	 * @param boolean $view Set to true to view this layer.
	 * @param boolean $lock If true lock the layer
	 * @public
	 * @since 5.9.102 (2011-07-13)
	 */
	public function startLayer($name='', $print=true, $view=true, $lock=true) {
		if ($this->state != 2) {
			return;
		}
		$layer = sprintf('LYR%03d', (count($this->pdflayers) + 1));
		if (empty($name)) {
			$name = $layer;
		} else {
			$name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
		}
		$this->pdflayers[] = array('layer' => $layer, 'name' => $name, 'print' => $print, 'view' => $view, 'lock' => $lock);
		$this->openMarkedContent = true;
		$this->_out('/OC /'.$layer.' BDC');
	}

	/**
	 * End the current PDF layer.
	 * @public
	 * @since 5.9.102 (2011-07-13)
	 */
	public function endLayer() {
		if ($this->state != 2) {
			return;
		}
		if ($this->openMarkedContent) {
			// close existing open marked-content layer
			$this->_out('EMC');
			$this->openMarkedContent = false;
		}
	}

	/**
	 * Set the visibility of the successive elements.
	 * This can be useful, for instance, to put a background
	 * image or color that will show on screen but won't print.
	 * @param string $v visibility mode. Legal values are: all, print, screen or view.
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function setVisibility($v) {
		if ($this->state != 2) {
			return;
		}
		$this->endLayer();
		switch($v) {
			case 'print': {
				$this->startLayer('Print', true, false);
				break;
			}
			case 'view':
			case 'screen': {
				$this->startLayer('View', false, true);
				break;
			}
			case 'all': {
				$this->_out('');
				break;
			}
			default: {
				$this->Error('Incorrect visibility: '.$v);
				break;
			}
		}
	}

	/**
	 * Add transparency parameters to the current extgstate
	 * @param array $parms parameters
	 * @return int|void the number of extgstates
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function addExtGState($parms) {
		if (($this->pdfa_mode && $this->pdfa_version < 2) || ($this->state != 2)) {
			// transparency is not allowed in PDF/A-1 mode
			return;
		}
		// check if this ExtGState already exist
		foreach ($this->extgstates as $i => $ext) {
			if ($ext['parms'] == $parms) {
				if ($this->inxobj) {
					// we are inside an XObject template
					$this->xobjects[$this->xobjid]['extgstates'][$i] = $ext;
				}
				// return reference to existing ExtGState
				return $i;
			}
		}
		$n = (count($this->extgstates) + 1);
		$this->extgstates[$n] = array('parms' => $parms);
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['extgstates'][$n] = $this->extgstates[$n];
		}
		return $n;
	}

	/**
	 * Paints color transition registration bars
	 * @param float $x abscissa of the top left corner of the rectangle.
	 * @param float $y ordinate of the top left corner of the rectangle.
	 * @param float $w width of the rectangle.
	 * @param float $h height of the rectangle.
	 * @param boolean $transition if true prints tcolor transitions to white.
	 * @param boolean $vertical if true prints bar vertically.
	 * @param string $colors colors to print separated by comma. Valid values are: A,W,R,G,B,C,M,Y,K,RGB,CMYK,ALL,ALLSPOT,<SPOT_COLOR_NAME>. Where: A = grayscale black, W = grayscale white, R = RGB red, G RGB green, B RGB blue, C = CMYK cyan, M = CMYK magenta, Y = CMYK yellow, K = CMYK key/black, RGB = RGB registration color, CMYK = CMYK registration color, ALL = Spot registration color, ALLSPOT = print all defined spot colors, <SPOT_COLOR_NAME> = name of the spot color to print.
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-26)
	 * @public
	 */
	public function colorRegistrationBar($x, $y, $w, $h, $transition=true, $vertical=false, $colors='A,R,G,B,C,M,Y,K') {
		if (strpos($colors, 'ALLSPOT') !== false) {
			// expand spot colors
			$spot_colors = '';
			foreach ($this->spot_colors as $spot_color_name => $v) {
				$spot_colors .= ','.$spot_color_name;
			}
			if (!empty($spot_colors)) {
				$spot_colors = substr($spot_colors, 1);
				$colors = str_replace('ALLSPOT', $spot_colors, $colors);
			} else {
				$colors = str_replace('ALLSPOT', 'NONE', $colors);
			}
		}
		$bars = explode(',', $colors);
		$numbars = count($bars); // number of bars to print
		if ($numbars <= 0) {
			return;
		}
		// set bar measures
		if ($vertical) {
			$coords = array(0, 0, 0, 1);
			$wb = $w / $numbars; // bar width
			$hb = $h; // bar height
			$xd = $wb; // delta x
			$yd = 0; // delta y
		} else {
			$coords = array(1, 0, 0, 0);
			$wb = $w; // bar width
			$hb = $h / $numbars; // bar height
			$xd = 0; // delta x
			$yd = $hb; // delta y
		}
		$xb = $x;
		$yb = $y;
		foreach ($bars as $col) {
			switch ($col) {
				// set transition colors
				case 'A': { // BLACK (GRAYSCALE)
					$col_a = array(255);
					$col_b = array(0);
					break;
				}
				case 'W': { // WHITE (GRAYSCALE)
					$col_a = array(0);
					$col_b = array(255);
					break;
				}
				case 'R': { // RED (RGB)
					$col_a = array(255,255,255);
					$col_b = array(255,0,0);
					break;
				}
				case 'G': { // GREEN (RGB)
					$col_a = array(255,255,255);
					$col_b = array(0,255,0);
					break;
				}
				case 'B': { // BLUE (RGB)
					$col_a = array(255,255,255);
					$col_b = array(0,0,255);
					break;
				}
				case 'C': { // CYAN (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(100,0,0,0);
					break;
				}
				case 'M': { // MAGENTA (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(0,100,0,0);
					break;
				}
				case 'Y': { // YELLOW (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(0,0,100,0);
					break;
				}
				case 'K': { // KEY - BLACK (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(0,0,0,100);
					break;
				}
				case 'RGB': { // BLACK REGISTRATION (RGB)
					$col_a = array(255,255,255);
					$col_b = array(0,0,0);
					break;
				}
				case 'CMYK': { // BLACK REGISTRATION (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(100,100,100,100);
					break;
				}
				case 'ALL': { // SPOT COLOR REGISTRATION
					$col_a = array(0,0,0,0,'None');
					$col_b = array(100,100,100,100,'All');
					break;
				}
				case 'NONE': { // SKIP THIS COLOR
					$col_a = array(0,0,0,0,'None');
					$col_b = array(0,0,0,0,'None');
					break;
				}
				default: { // SPECIFIC SPOT COLOR NAME
					$col_a = array(0,0,0,0,'None');
					$col_b = LIMEPDF_COLORS::getSpotColor($col, $this->spot_colors);
					if ($col_b === false) {
						// in case of error defaults to the registration color
						$col_b = array(100,100,100,100,'All');
					}
					break;
				}
			}
			if ($col != 'NONE') {
				if ($transition) {
					// color gradient
					$this->LinearGradient($xb, $yb, $wb, $hb, $col_a, $col_b, $coords);
				} else {
					$this->setFillColorArray($col_b);
					// colored rectangle
					$this->Rect($xb, $yb, $wb, $hb, 'F', array());
				}
				$xb += $xd;
				$yb += $yd;
			}
		}
	}

	/**
	 * Paints crop marks.
	 * @param float $x abscissa of the crop mark center.
	 * @param float $y ordinate of the crop mark center.
	 * @param float $w width of the crop mark.
	 * @param float $h height of the crop mark.
	 * @param string $type type of crop mark, one symbol per type separated by comma: T = TOP, F = BOTTOM, L = LEFT, R = RIGHT, TL = A = TOP-LEFT, TR = B = TOP-RIGHT, BL = C = BOTTOM-LEFT, BR = D = BOTTOM-RIGHT.
	 * @param array $color crop mark color (default spot registration color).
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-26)
	 * @public
	 */
	public function cropMark($x, $y, $w, $h, $type='T,R,B,L', $color=array(100,100,100,100,'All')) {
		$this->setLineStyle(array('width' => (0.5 / $this->k), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $color));
		$type = strtoupper($type);
		$type = preg_replace('/[^A-Z\-\,]*/', '', $type);
		// split type in single components
		$type = str_replace('-', ',', $type);
		$type = str_replace('TL', 'T,L', $type);
		$type = str_replace('TR', 'T,R', $type);
		$type = str_replace('BL', 'F,L', $type);
		$type = str_replace('BR', 'F,R', $type);
		$type = str_replace('A', 'T,L', $type);
		$type = str_replace('B', 'T,R', $type);
		$type = str_replace('T,RO', 'BO', $type);
		$type = str_replace('C', 'F,L', $type);
		$type = str_replace('D', 'F,R', $type);
		$crops = explode(',', strtoupper($type));
		// remove duplicates
		$crops = array_unique($crops);
		$dw = ($w / 4); // horizontal space to leave before the intersection point
		$dh = ($h / 4); // vertical space to leave before the intersection point
		foreach ($crops as $crop) {
			switch ($crop) {
				case 'T':
				case 'TOP': {
					$x1 = $x;
					$y1 = ($y - $h);
					$x2 = $x;
					$y2 = ($y - $dh);
					break;
				}
				case 'F':
				case 'BOTTOM': {
					$x1 = $x;
					$y1 = ($y + $dh);
					$x2 = $x;
					$y2 = ($y + $h);
					break;
				}
				case 'L':
				case 'LEFT': {
					$x1 = ($x - $w);
					$y1 = $y;
					$x2 = ($x - $dw);
					$y2 = $y;
					break;
				}
				case 'R':
				case 'RIGHT': {
					$x1 = ($x + $dw);
					$y1 = $y;
					$x2 = ($x + $w);
					$y2 = $y;
					break;
				}
			}
			$this->Line($x1, $y1, $x2, $y2);
		}
	}

	/**
	 * Paints a registration mark
	 * @param float $x abscissa of the registration mark center.
	 * @param float $y ordinate of the registration mark center.
	 * @param float $r radius of the crop mark.
	 * @param boolean $double if true print two concentric crop marks.
	 * @param array $cola crop mark color (default spot registration color 'All').
	 * @param array $colb second crop mark color (default spot registration color 'None').
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-26)
	 * @public
	 */
	public function registrationMark($x, $y, $r, $double=false, $cola=array(100,100,100,100,'All'), $colb=array(0,0,0,0,'None')) {
		$line_style = array('width' => max((0.5 / $this->k),($r / 30)), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $cola);
		$this->setFillColorArray($cola);
		$this->PieSector($x, $y, $r, 90, 180, 'F');
		$this->PieSector($x, $y, $r, 270, 360, 'F');
		$this->Circle($x, $y, $r, 0, 360, 'C', $line_style, array(), 8);
		if ($double) {
			$ri = $r * 0.5;
			$this->setFillColorArray($colb);
			$this->PieSector($x, $y, $ri, 90, 180, 'F');
			$this->PieSector($x, $y, $ri, 270, 360, 'F');
			$this->setFillColorArray($cola);
			$this->PieSector($x, $y, $ri, 0, 90, 'F');
			$this->PieSector($x, $y, $ri, 180, 270, 'F');
			$this->Circle($x, $y, $ri, 0, 360, 'C', $line_style, array(), 8);
		}
	}

	/**
	 * Paints a CMYK registration mark
	 * @param float $x abscissa of the registration mark center.
	 * @param float $y ordinate of the registration mark center.
	 * @param float $r radius of the crop mark.
	 * @author Nicola Asuni
	 * @since 6.0.038 (2013-09-30)
	 * @public
	 */
	public function registrationMarkCMYK($x, $y, $r) {
		// line width
		$lw = max((0.5 / $this->k),($r / 8));
		// internal radius
		$ri = ($r * 0.6);
		// external radius
		$re = ($r * 1.3);
		// Cyan
		$this->setFillColorArray(array(100,0,0,0));
		$this->PieSector($x, $y, $ri, 270, 360, 'F');
		// Magenta
		$this->setFillColorArray(array(0,100,0,0));
		$this->PieSector($x, $y, $ri, 0, 90, 'F');
		// Yellow
		$this->setFillColorArray(array(0,0,100,0));
		$this->PieSector($x, $y, $ri, 90, 180, 'F');
		// Key - black
		$this->setFillColorArray(array(0,0,0,100));
		$this->PieSector($x, $y, $ri, 180, 270, 'F');
		// registration color
		$line_style = array('width' => $lw, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(100,100,100,100,'All'));
		$this->setFillColorArray(array(100,100,100,100,'All'));
		// external circle
		$this->Circle($x, $y, $r, 0, 360, 'C', $line_style, array(), 8);
		// cross lines
		$this->Line($x, ($y - $re), $x, ($y - $ri));
		$this->Line($x, ($y + $ri), $x, ($y + $re));
		$this->Line(($x - $re), $y, ($x - $ri), $y);
		$this->Line(($x + $ri), $y, ($x + $re), $y);
	}

	/**
	 * Paints a linear colour gradient.
	 * @param float $x abscissa of the top left corner of the rectangle.
	 * @param float $y ordinate of the top left corner of the rectangle.
	 * @param float $w width of the rectangle.
	 * @param float $h height of the rectangle.
	 * @param array $col1 first color (Grayscale, RGB or CMYK components).
	 * @param array $col2 second color (Grayscale, RGB or CMYK components).
	 * @param array $coords array of the form (x1, y1, x2, y2) which defines the gradient vector (see linear_gradient_coords.jpg). The default value is from left to right (x1=0, y1=0, x2=1, y2=0).
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function LinearGradient($x, $y, $w, $h, $col1=array(), $col2=array(), $coords=array(0,0,1,0)) {
		$this->Clip($x, $y, $w, $h);
		$this->Gradient(2, $coords, array(array('color' => $col1, 'offset' => 0, 'exponent' => 1), array('color' => $col2, 'offset' => 1, 'exponent' => 1)), array(), false);
	}

	/**
	 * Paints a radial colour gradient.
	 * @param float $x abscissa of the top left corner of the rectangle.
	 * @param float $y ordinate of the top left corner of the rectangle.
	 * @param float $w width of the rectangle.
	 * @param float $h height of the rectangle.
	 * @param array $col1 first color (Grayscale, RGB or CMYK components).
	 * @param array $col2 second color (Grayscale, RGB or CMYK components).
	 * @param array $coords array of the form (fx, fy, cx, cy, r) where (fx, fy) is the starting point of the gradient with color1, (cx, cy) is the center of the circle with color2, and r is the radius of the circle (see radial_gradient_coords.jpg). (fx, fy) should be inside the circle, otherwise some areas will not be defined.
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function RadialGradient($x, $y, $w, $h, $col1=array(), $col2=array(), $coords=array(0.5,0.5,0.5,0.5,1)) {
		$this->Clip($x, $y, $w, $h);
		$this->Gradient(3, $coords, array(array('color' => $col1, 'offset' => 0, 'exponent' => 1), array('color' => $col2, 'offset' => 1, 'exponent' => 1)), array(), false);
	}

	/**
	 * Paints a coons patch mesh.
	 * @param float $x abscissa of the top left corner of the rectangle.
	 * @param float $y ordinate of the top left corner of the rectangle.
	 * @param float $w width of the rectangle.
	 * @param float $h height of the rectangle.
	 * @param array $col1 first color (lower left corner) (RGB components).
	 * @param array $col2 second color (lower right corner) (RGB components).
	 * @param array $col3 third color (upper right corner) (RGB components).
	 * @param array $col4 fourth color (upper left corner) (RGB components).
	 * @param array $coords <ul><li>for one patch mesh: array(float x1, float y1, .... float x12, float y12): 12 pairs of coordinates (normally from 0 to 1) which specify the Bezier control points that define the patch. First pair is the lower left edge point, next is its right control point (control point 2). Then the other points are defined in the order: control point 1, edge point, control point 2 going counter-clockwise around the patch. Last (x12, y12) is the first edge point's left control point (control point 1).</li><li>for two or more patch meshes: array[number of patches]: arrays with the following keys for each patch: f: where to put that patch (0 = first patch, 1, 2, 3 = right, top and left of precedent patch - I didn't figure this out completely - just try and error ;-) points: 12 pairs of coordinates of the Bezier control points as above for the first patch, 8 pairs of coordinates for the following patches, ignoring the coordinates already defined by the precedent patch (I also didn't figure out the order of these - also: try and see what's happening) colors: must be 4 colors for the first patch, 2 colors for the following patches</li></ul>
	 * @param array $coords_min minimum value used by the coordinates. If a coordinate's value is smaller than this it will be cut to coords_min. default: 0
	 * @param array $coords_max maximum value used by the coordinates. If a coordinate's value is greater than this it will be cut to coords_max. default: 1
	 * @param boolean $antialias A flag indicating whether to filter the shading function to prevent aliasing artifacts.
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function CoonsPatchMesh($x, $y, $w, $h, $col1=array(), $col2=array(), $col3=array(), $col4=array(), $coords=array(0.00,0.0,0.33,0.00,0.67,0.00,1.00,0.00,1.00,0.33,1.00,0.67,1.00,1.00,0.67,1.00,0.33,1.00,0.00,1.00,0.00,0.67,0.00,0.33), $coords_min=0, $coords_max=1, $antialias=false) {
		if (($this->pdfa_mode && $this->pdfa_version < 2) OR ($this->state != 2)) {
			return;
		}
		$this->Clip($x, $y, $w, $h);
		$n = count($this->gradients) + 1;
		$this->gradients[$n] = array();
		$this->gradients[$n]['type'] = 6; //coons patch mesh
		$this->gradients[$n]['coords'] = array();
		$this->gradients[$n]['antialias'] = $antialias;
		$this->gradients[$n]['colors'] = array();
		$this->gradients[$n]['transparency'] = false;
		//check the coords array if it is the simple array or the multi patch array
		if (!isset($coords[0]['f'])) {
			//simple array -> convert to multi patch array
			if (!isset($col1[1])) {
				$col1[1] = $col1[2] = $col1[0];
			}
			if (!isset($col2[1])) {
				$col2[1] = $col2[2] = $col2[0];
			}
			if (!isset($col3[1])) {
				$col3[1] = $col3[2] = $col3[0];
			}
			if (!isset($col4[1])) {
				$col4[1] = $col4[2] = $col4[0];
			}
			$patch_array[0]['f'] = 0;
			$patch_array[0]['points'] = $coords;
			$patch_array[0]['colors'][0]['r'] = $col1[0];
			$patch_array[0]['colors'][0]['g'] = $col1[1];
			$patch_array[0]['colors'][0]['b'] = $col1[2];
			$patch_array[0]['colors'][1]['r'] = $col2[0];
			$patch_array[0]['colors'][1]['g'] = $col2[1];
			$patch_array[0]['colors'][1]['b'] = $col2[2];
			$patch_array[0]['colors'][2]['r'] = $col3[0];
			$patch_array[0]['colors'][2]['g'] = $col3[1];
			$patch_array[0]['colors'][2]['b'] = $col3[2];
			$patch_array[0]['colors'][3]['r'] = $col4[0];
			$patch_array[0]['colors'][3]['g'] = $col4[1];
			$patch_array[0]['colors'][3]['b'] = $col4[2];
		} else {
			//multi patch array
			$patch_array = $coords;
		}
		$bpcd = 65535; //16 bits per coordinate
		//build the data stream
		$this->gradients[$n]['stream'] = '';
		$count_patch = count($patch_array);
		for ($i=0; $i < $count_patch; ++$i) {
			$this->gradients[$n]['stream'] .= chr($patch_array[$i]['f']); //start with the edge flag as 8 bit
			$count_points = count($patch_array[$i]['points']);
			for ($j=0; $j < $count_points; ++$j) {
				//each point as 16 bit
				$patch_array[$i]['points'][$j] = (($patch_array[$i]['points'][$j] - $coords_min) / ($coords_max - $coords_min)) * $bpcd;
				if ($patch_array[$i]['points'][$j] < 0) {
					$patch_array[$i]['points'][$j] = 0;
				}
				if ($patch_array[$i]['points'][$j] > $bpcd) {
					$patch_array[$i]['points'][$j] = $bpcd;
				}
				$this->gradients[$n]['stream'] .= chr((int) floor($patch_array[$i]['points'][$j] / 256));
				$this->gradients[$n]['stream'] .= chr((int) floor(intval($patch_array[$i]['points'][$j]) % 256));
			}
			$count_cols = count($patch_array[$i]['colors']);
			for ($j=0; $j < $count_cols; ++$j) {
				//each color component as 8 bit
				$this->gradients[$n]['stream'] .= chr($patch_array[$i]['colors'][$j]['r']);
				$this->gradients[$n]['stream'] .= chr($patch_array[$i]['colors'][$j]['g']);
				$this->gradients[$n]['stream'] .= chr($patch_array[$i]['colors'][$j]['b']);
			}
		}
		//paint the gradient
		$this->_out('/Sh'.$n.' sh');
		//restore previous Graphic State
		$this->_outRestoreGraphicsState();
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['gradients'][$n] = $this->gradients[$n];
		}
	}

	/**
	 * Replace the buffer content
	 * @param string $data data
	 * @protected
	 * @since 5.5.000 (2010-06-22)
	 */
	protected function replaceBuffer($data) {
		$this->bufferlen = strlen($data);
		$this->buffer = $data;
	}

	/**
	 * Stores a copy of the current TCPDF object used for undo operation.
	 * @public
	 * @since 4.5.029 (2009-03-19)
	 */
	public function startTransaction() {
		if (isset($this->objcopy)) {
			// remove previous copy
			$this->commitTransaction();
		}
		// record current page number and Y position
		$this->start_transaction_page = $this->page;
		$this->start_transaction_y = $this->y;
		// clone current object
		$this->objcopy = LIMEPDF_STATIC::objclone($this);
	}

	
	/**
	 * Unset all class variables except the following critical variables.
	 * @param boolean $destroyall if true destroys all class variables, otherwise preserves critical variables.
	 * @param boolean $preserve_objcopy if true preserves the objcopy variable
	 * @public
	 * @since 4.5.016 (2009-02-24)
	 */
	public function _destroy($destroyall=false, $preserve_objcopy=false) {
		if (isset(self::$cleaned_ids[$this->file_id])) {
			$destroyall = false;
		}
		if ($destroyall AND !$preserve_objcopy && isset($this->file_id)) {
			self::$cleaned_ids[$this->file_id] = true;
			// remove all temporary files
			if ($handle = @opendir(K_PATH_CACHE)) {
				while ( false !== ( $file_name = readdir( $handle ) ) ) {
					if (strpos($file_name, '__tcpdf_'.$this->file_id.'_') === 0) {
						$this->_unlink(K_PATH_CACHE.$file_name);
					}
				}
				closedir($handle);
			}
			if (isset($this->imagekeys)) {
				foreach($this->imagekeys as $file) {
					if ((strpos($file,  K_PATH_CACHE.'__tcpdf_'.$this->file_id.'_') === 0)
						&& LIMEPDF_STATIC::file_exists($file)) {
							$this->_unlink($file);
					}
				}
			}
		}
		$preserve = array(
			'file_id',
			'state',
			'bufferlen',
			'buffer',
			'cached_files',
			'imagekeys',
			'sign',
			'signature_data',
			'signature_max_length',
			'byterange_string',
			'tsa_timestamp',
			'tsa_data'
		);
		foreach (array_keys(get_object_vars($this)) as $val) {
			if ($destroyall OR !in_array($val, $preserve)) {
				if ((!$preserve_objcopy OR ($val != 'objcopy')) AND ($val != 'file_id') AND isset($this->$val)) {
					unset($this->$val);
				}
			}
		}
	}

	/**
	 * Delete the copy of the current TCPDF object used for undo operation.
	 * @public
	 * @since 4.5.029 (2009-03-19)
	 */
	public function commitTransaction() {
		if (isset($this->objcopy)) {
			$this->objcopy->_destroy(true, true);
			/* The unique file_id should not be used during cleanup again */
			$this->objcopy->file_id = NULL;
			unset($this->objcopy);
		}
	}

	/**
	 * This method allows to undo the latest transaction by returning the latest saved TCPDF object with startTransaction().
	 * @param boolean $self if true restores current class object to previous state without the need of reassignment via the returned value.
	 * @return TCPDF object.
	 * @public
	 * @since 4.5.029 (2009-03-19)
	 */
	public function rollbackTransaction($self=false) {
		if (!isset($this->objcopy)) {
			return $this;
		}
		$file_id = $this->file_id;
		$objcopy = $this->objcopy;
		$this->_destroy(true, true);
		if ($self) {
			$objvars = get_object_vars($objcopy);
			foreach ($objvars as $key => $value) {
				$this->$key = $value;
			}
			$objcopy->_destroy(true, true);
			unset($objcopy);
			return $this;
		}
		$this->file_id = $file_id;
		return $objcopy;
	}

    /**
	 * Calculates the hash value of the given data.
	 *
	 * @param string $data The data to be hashed.
	 * @return string The hashed value of the data.
	 */
	protected function hashTCPDFtag($data) {
		return hash_hmac('sha256', $data, $this->hash_key, false);
	}

    /**
	 * Serialize data to be used with TCPDF tag in HTML code.
	 * @param string $method TCPDF method name
	 * @param array $params Method parameters
	 * @return string Serialized data
	 * @public static
	 */
	public function serializeTCPDFtag($method, $params=array()) {
		$data = array('m' => $method, 'p' => $params);
		$encoded = urlencode(json_encode($data));
		$hash = $this->hashTCPDFtag($encoded);
		return strlen($hash).'+'.$hash.'+'.$encoded;
	}

	/**
	 * Unserialize data to be used with TCPDF tag in HTML code.
	 * @param string $data serialized data
	 * @return array containing unserialized data
	 * @protected static
	 */
	protected function unserializeTCPDFtag($data) {
		$hpos = strpos($data, '+');
		$hlen = intval(substr($data, 0, $hpos));
		$hash = substr($data, $hpos + 1, $hlen);
		$encoded = substr($data, $hpos + 2 + $hlen);
		if (!hash_equals( $this->hashTCPDFtag($encoded), $hash)) {
			$this->Error('Invalid parameters');
		}
		return json_decode(urldecode($encoded), true);
	}

	/**
	 * Check if a TCPDF tag is allowed
	 * @param string $method TCPDF method name
	 * @return boolean
	 * @protected
	 */
	protected function allowedTCPDFtag($method) {
		if (defined('K_ALLOWED_TCPDF_TAGS')) {
			return (strpos(K_ALLOWED_TCPDF_TAGS, '|'.$method.'|') !== false);
		}
		return false;
	}

}