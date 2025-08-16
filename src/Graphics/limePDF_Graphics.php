<?php

namespace LimePDF;

trait LIMEPDF_GRAPHICS {

	// START GRAPHIC FUNCTIONS SECTION ---------------------
	// The following section is based on the code provided by David Hernandez Sanz

	/**
	 * Defines the line width. By default, the value equals 0.2 mm. The method can be called before the first page is created and the value is retained from page to page.
	 * @param float $width The width.
	 * @public
	 * @since 1.0
	 * @see Line(), Rect(), Cell(), MultiCell()
	 */
	public function setLineWidth($width) {
		//Set line width
		$this->LineWidth = $width;
		$this->linestyleWidth = sprintf('%F w', ($width * $this->k));
		if ($this->state == 2) {
			$this->_out($this->linestyleWidth);
		}
	}

	/**
	 * Returns the current the line width.
	 * @return int Line width
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see Line(), SetLineWidth()
	 */
	public function GetLineWidth() {
		return $this->LineWidth;
	}

	/**
	 * Set line style.
	 * @param array $style Line style. Array with keys among the following:
	 * <ul>
	 *	 <li>width (float): Width of the line in user units.</li>
	 *	 <li>cap (string): Type of cap to put on the line. Possible values are:
	 * butt, round, square. The difference between "square" and "butt" is that
	 * "square" projects a flat end past the end of the line.</li>
	 *	 <li>join (string): Type of join. Possible values are: miter, round,
	 * bevel.</li>
	 *	 <li>dash (mixed): Dash pattern. Is 0 (without dash) or string with
	 * series of length values, which are the lengths of the on and off dashes.
	 * For example: "2" represents 2 on, 2 off, 2 on, 2 off, ...; "2,1" is 2 on,
	 * 1 off, 2 on, 1 off, ...</li>
	 *	 <li>phase (integer): Modifier on the dash pattern which is used to shift
	 * the point at which the pattern starts.</li>
	 *	 <li>color (array): Draw color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName).</li>
	 * </ul>
	 * @param boolean $ret if true do not send the command.
	 * @return string the PDF command
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function setLineStyle($style, $ret=false) {
		$s = ''; // string to be returned
		if (!is_array($style)) {
			return $s;
		}
		if (isset($style['width'])) {
			$this->LineWidth = $style['width'];
			$this->linestyleWidth = sprintf('%F w', ($style['width'] * $this->k));
			$s .= $this->linestyleWidth.' ';
		}
		if (isset($style['cap'])) {
			$ca = array('butt' => 0, 'round'=> 1, 'square' => 2);
			if (isset($ca[$style['cap']])) {
				$this->linestyleCap = $ca[$style['cap']].' J';
				$s .= $this->linestyleCap.' ';
			}
		}
		if (isset($style['join'])) {
			$ja = array('miter' => 0, 'round' => 1, 'bevel' => 2);
			if (isset($ja[$style['join']])) {
				$this->linestyleJoin = $ja[$style['join']].' j';
				$s .= $this->linestyleJoin.' ';
			}
		}
		if (isset($style['dash'])) {
			$dash_string = '';
			if ($style['dash']) {
				if (preg_match('/^.+,/', $style['dash']) > 0) {
					$tab = explode(',', $style['dash']);
				} else {
					$tab = array($style['dash']);
				}
				$dash_string = '';
				foreach ($tab as $i => $v) {
					if ($i) {
						$dash_string .= ' ';
					}
					$dash_string .= sprintf('%F', $v);
				}
			}
			if (!isset($style['phase']) OR !$style['dash']) {
				$style['phase'] = 0;
			}
			$this->linestyleDash = sprintf('[%s] %F d', $dash_string, $style['phase']);
			$s .= $this->linestyleDash.' ';
		}
		if (isset($style['color'])) {
			$s .= $this->setDrawColorArray($style['color'], true).' ';
		}
		if (!$ret AND ($this->state == 2)) {
			$this->_out($s);
		}
		return $s;
	}

	/**
	 * Begin a new subpath by moving the current point to coordinates (x, y), omitting any connecting line segment.
	 * @param float $x Abscissa of point.
	 * @param float $y Ordinate of point.
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outPoint($x, $y) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F m', ($x * $this->k), (($this->h - $y) * $this->k)));
		}
	}

	/**
	 * Append a straight line segment from the current point to the point (x, y).
	 * The new current point shall be (x, y).
	 * @param float $x Abscissa of end point.
	 * @param float $y Ordinate of end point.
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outLine($x, $y) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F l', ($x * $this->k), (($this->h - $y) * $this->k)));
		}
	}

	/**
	 * Append a rectangle to the current path as a complete subpath, with lower-left corner (x, y) and dimensions widthand height in user space.
	 * @param float $x Abscissa of upper-left corner.
	 * @param float $y Ordinate of upper-left corner.
	 * @param float $w Width.
	 * @param float $h Height.
	 * @param string $op options
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outRect($x, $y, $w, $h, $op) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F %F %F re %s', ($x * $this->k), (($this->h - $y) * $this->k), ($w * $this->k), (-$h * $this->k), $op));
		}
	}

	/**
	 * Append a cubic Bezier curve to the current path. The curve shall extend from the current point to the point (x3, y3), using (x1, y1) and (x2, y2) as the Bezier control points.
	 * The new current point shall be (x3, y3).
	 * @param float $x1 Abscissa of control point 1.
	 * @param float $y1 Ordinate of control point 1.
	 * @param float $x2 Abscissa of control point 2.
	 * @param float $y2 Ordinate of control point 2.
	 * @param float $x3 Abscissa of end point.
	 * @param float $y3 Ordinate of end point.
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outCurve($x1, $y1, $x2, $y2, $x3, $y3) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F %F %F %F %F c', ($x1 * $this->k), (($this->h - $y1) * $this->k), ($x2 * $this->k), (($this->h - $y2) * $this->k), ($x3 * $this->k), (($this->h - $y3) * $this->k)));
		}
	}

	/**
	 * Append a cubic Bezier curve to the current path. The curve shall extend from the current point to the point (x3, y3), using the current point and (x2, y2) as the Bezier control points.
	 * The new current point shall be (x3, y3).
	 * @param float $x2 Abscissa of control point 2.
	 * @param float $y2 Ordinate of control point 2.
	 * @param float $x3 Abscissa of end point.
	 * @param float $y3 Ordinate of end point.
	 * @protected
	 * @since 4.9.019 (2010-04-26)
	 */
	protected function _outCurveV($x2, $y2, $x3, $y3) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F %F %F v', ($x2 * $this->k), (($this->h - $y2) * $this->k), ($x3 * $this->k), (($this->h - $y3) * $this->k)));
		}
	}

	/**
	 * Append a cubic Bezier curve to the current path. The curve shall extend from the current point to the point (x3, y3), using (x1, y1) and (x3, y3) as the Bezier control points.
	 * The new current point shall be (x3, y3).
	 * @param float $x1 Abscissa of control point 1.
	 * @param float $y1 Ordinate of control point 1.
	 * @param float $x3 Abscissa of end point.
	 * @param float $y3 Ordinate of end point.
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outCurveY($x1, $y1, $x3, $y3) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F %F %F y', ($x1 * $this->k), (($this->h - $y1) * $this->k), ($x3 * $this->k), (($this->h - $y3) * $this->k)));
		}
	}

	/**
	 * Draws a line between two points.
	 * @param float $x1 Abscissa of first point.
	 * @param float $y1 Ordinate of first point.
	 * @param float $x2 Abscissa of second point.
	 * @param float $y2 Ordinate of second point.
	 * @param array $style Line style. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @public
	 * @since 1.0
	 * @see SetLineWidth(), SetDrawColor(), SetLineStyle()
	 */
	public function Line($x1, $y1, $x2, $y2, $style=array()) {
		if ($this->state != 2) {
			return;
		}
		if (is_array($style)) {
			$this->setLineStyle($style);
		}
		$this->_outPoint($x1, $y1);
		$this->_outLine($x2, $y2);
		$this->_out('S');
	}

	/**
	 * Draws a rectangle.
	 * @param float $x Abscissa of upper-left corner.
	 * @param float $y Ordinate of upper-left corner.
	 * @param float $w Width.
	 * @param float $h Height.
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param array $border_style Border style of rectangle. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all borders. Array like for SetLineStyle().</li>
	 *	 <li>L, T, R, B or combinations: Line style of left, top, right or bottom border. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, the correspondent border is not drawn. Default value: default line style (empty array).
	 * @param array $fill_color Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @since 1.0
	 * @see SetLineStyle()
	 */
	public function Rect($x, $y, $w, $h, $style='', $border_style=array(), $fill_color=array()) {
		if ($this->state != 2) {
			return;
		}
		if (empty($style)) {
			$style = 'S';
		}
		if (!(strpos($style, 'F') === false) AND !empty($fill_color)) {
			// set background color
			$this->setFillColorArray($fill_color);
		}
		if (!empty($border_style)) {
			if (isset($border_style['all']) AND !empty($border_style['all'])) {
				//set global style for border
				$this->setLineStyle($border_style['all']);
				$border_style = array();
			} else {
				// remove stroke operator from style
				$opnostroke = array('S' => '', 'D' => '', 's' => '', 'd' => '', 'B' => 'F', 'FD' => 'F', 'DF' => 'F', 'B*' => 'F*', 'F*D' => 'F*', 'DF*' => 'F*', 'b' => 'f', 'fd' => 'f', 'df' => 'f', 'b*' => 'f*', 'f*d' => 'f*', 'df*' => 'f*' );
				if (isset($opnostroke[$style])) {
					$style = $opnostroke[$style];
				}
			}
		}
		if (!empty($style)) {
			$op = LIMEPDF_STATIC::getPathPaintOperator($style);
			$this->_outRect($x, $y, $w, $h, $op);
		}
		if (!empty($border_style)) {
			$border_style2 = array();
			foreach ($border_style as $line => $value) {
				$length = strlen($line);
				for ($i = 0; $i < $length; ++$i) {
					$border_style2[$line[$i]] = $value;
				}
			}
			$border_style = $border_style2;
			if (isset($border_style['L']) AND $border_style['L']) {
				$this->Line($x, $y, $x, $y + $h, $border_style['L']);
			}
			if (isset($border_style['T']) AND $border_style['T']) {
				$this->Line($x, $y, $x + $w, $y, $border_style['T']);
			}
			if (isset($border_style['R']) AND $border_style['R']) {
				$this->Line($x + $w, $y, $x + $w, $y + $h, $border_style['R']);
			}
			if (isset($border_style['B']) AND $border_style['B']) {
				$this->Line($x, $y + $h, $x + $w, $y + $h, $border_style['B']);
			}
		}
	}

	/**
	 * Draws a Bezier curve.
	 * The Bezier curve is a tangent to the line between the control points at
	 * either end of the curve.
	 * @param float $x0 Abscissa of start point.
	 * @param float $y0 Ordinate of start point.
	 * @param float $x1 Abscissa of control point 1.
	 * @param float $y1 Ordinate of control point 1.
	 * @param float $x2 Abscissa of control point 2.
	 * @param float $y2 Ordinate of control point 2.
	 * @param float $x3 Abscissa of end point.
	 * @param float $y3 Ordinate of end point.
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param array $line_style Line style of curve. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param array $fill_color Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @see SetLineStyle()
	 * @since 2.1.000 (2008-01-08)
	 */
	public function Curve($x0, $y0, $x1, $y1, $x2, $y2, $x3, $y3, $style='', $line_style=array(), $fill_color=array()) {
		if ($this->state != 2) {
			return;
		}
		if (!(false === strpos($style, 'F')) AND is_array($fill_color)) {
			$this->setFillColorArray($fill_color);
		}
		$op = LIMEPDF_STATIC::getPathPaintOperator($style);
		if ($line_style) {
			$this->setLineStyle($line_style);
		}
		$this->_outPoint($x0, $y0);
		$this->_outCurve($x1, $y1, $x2, $y2, $x3, $y3);
		$this->_out($op);
	}

	/**
	 * Draws a poly-Bezier curve.
	 * Each Bezier curve segment is a tangent to the line between the control points at
	 * either end of the curve.
	 * @param float $x0 Abscissa of start point.
	 * @param float $y0 Ordinate of start point.
	 * @param float[] $segments An array of bezier descriptions. Format: array(x1, y1, x2, y2, x3, y3).
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param array $line_style Line style of curve. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param array $fill_color Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @see SetLineStyle()
	 * @since 3.0008 (2008-05-12)
	 */
	public function Polycurve($x0, $y0, $segments, $style='', $line_style=array(), $fill_color=array()) {
		if ($this->state != 2) {
			return;
		}
		if (!(false === strpos($style, 'F')) AND is_array($fill_color)) {
			$this->setFillColorArray($fill_color);
		}
		$op = LIMEPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		if ($line_style) {
			$this->setLineStyle($line_style);
		}
		$this->_outPoint($x0, $y0);
		foreach ($segments as $segment) {
			list($x1, $y1, $x2, $y2, $x3, $y3) = $segment;
			$this->_outCurve($x1, $y1, $x2, $y2, $x3, $y3);
		}
		$this->_out($op);
	}

	/**
	 * Draws an ellipse.
	 * An ellipse is formed from n Bezier curves.
	 * @param float $x0 Abscissa of center point.
	 * @param float $y0 Ordinate of center point.
	 * @param float $rx Horizontal radius.
	 * @param float $ry Vertical radius (if ry = 0 then is a circle, see Circle()). Default value: 0.
	 * @param float $angle Angle oriented (anti-clockwise). Default value: 0.
	 * @param float $astart Angle start of draw line. Default value: 0.
	 * @param float $afinish Angle finish of draw line. Default value: 360.
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param array $line_style Line style of ellipse. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param array $fill_color Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @param integer $nc Number of curves used to draw a 90 degrees portion of ellipse.
	 * @author Nicola Asuni
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function Ellipse($x0, $y0, $rx, $ry=0, $angle=0, $astart=0, $afinish=360, $style='', $line_style=array(), $fill_color=array(), $nc=2) {
		if ($this->state != 2) {
			return;
		}
		if (LIMEPDF_STATIC::empty_string($ry) OR ($ry == 0)) {
			$ry = $rx;
		}
		if (!(false === strpos($style, 'F')) AND is_array($fill_color)) {
			$this->setFillColorArray($fill_color);
		}
		$op = LIMEPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		if ($line_style) {
			$this->setLineStyle($line_style);
		}
		$this->_outellipticalarc($x0, $y0, $rx, $ry, $angle, $astart, $afinish, false, $nc, true, true, false);
		$this->_out($op);
	}

	/**
	 * Append an elliptical arc to the current path.
	 * An ellipse is formed from n Bezier curves.
	 * @param float $xc Abscissa of center point.
	 * @param float $yc Ordinate of center point.
	 * @param float $rx Horizontal radius.
	 * @param float $ry Vertical radius (if ry = 0 then is a circle, see Circle()). Default value: 0.
	 * @param float $xang Angle between the X-axis and the major axis of the ellipse. Default value: 0.
	 * @param float $angs Angle start of draw line. Default value: 0.
	 * @param float $angf Angle finish of draw line. Default value: 360.
	 * @param boolean $pie if true do not mark the border point (used to draw pie sectors).
	 * @param integer $nc Number of curves used to draw a 90 degrees portion of ellipse.
	 * @param boolean $startpoint if true output a starting point.
	 * @param boolean $ccw if true draws in counter-clockwise.
	 * @param boolean $svg if true the angles are in svg mode (already calculated).
	 * @return array bounding box coordinates (x min, y min, x max, y max)
	 * @author Nicola Asuni
	 * @protected
	 * @since 4.9.019 (2010-04-26)
	 */
	protected function _outellipticalarc($xc, $yc, $rx, $ry, $xang=0, $angs=0, $angf=360, $pie=false, $nc=2, $startpoint=true, $ccw=true, $svg=false) {
		if (($rx <= 0) OR ($ry < 0)) {
			return;
		}
		$k = $this->k;
		if ($nc < 2) {
			$nc = 2;
		}
		$xmin = 2147483647;
		$ymin = 2147483647;
		$xmax = 0;
		$ymax = 0;
		if ($pie) {
			// center of the arc
			$this->_outPoint($xc, $yc);
		}
		$xang = deg2rad((float) $xang);
		$angs = deg2rad((float) $angs);
		$angf = deg2rad((float) $angf);
		if ($svg) {
			$as = $angs;
			$af = $angf;
		} else {
			$as = atan2((sin($angs) / $ry), (cos($angs) / $rx));
			$af = atan2((sin($angf) / $ry), (cos($angf) / $rx));
		}
		if ($as < 0) {
			$as += (2 * M_PI);
		}
		if ($af < 0) {
			$af += (2 * M_PI);
		}
		if ($ccw AND ($as > $af)) {
			// reverse rotation
			$as -= (2 * M_PI);
		} elseif (!$ccw AND ($as < $af)) {
			// reverse rotation
			$af -= (2 * M_PI);
		}
		$total_angle = ($af - $as);
		if ($nc < 2) {
			$nc = 2;
		}
		// total arcs to draw
		$nc *= (2 * abs($total_angle) / M_PI);
		$nc = round($nc) + 1;
		// angle of each arc
		$arcang = ($total_angle / $nc);
		// center point in PDF coordinates
		$x0 = $xc;
		$y0 = ($this->h - $yc);
		// starting angle
		$ang = $as;
		$alpha = sin($arcang) * ((sqrt(4 + (3 * pow(tan(($arcang) / 2), 2))) - 1) / 3);
		$cos_xang = cos($xang);
		$sin_xang = sin($xang);
		$cos_ang = cos($ang);
		$sin_ang = sin($ang);
		// first arc point
		$px1 = $x0 + ($rx * $cos_xang * $cos_ang) - ($ry * $sin_xang * $sin_ang);
		$py1 = $y0 + ($rx * $sin_xang * $cos_ang) + ($ry * $cos_xang * $sin_ang);
		// first Bezier control point
		$qx1 = ($alpha * ((-$rx * $cos_xang * $sin_ang) - ($ry * $sin_xang * $cos_ang)));
		$qy1 = ($alpha * ((-$rx * $sin_xang * $sin_ang) + ($ry * $cos_xang * $cos_ang)));
		if ($pie) {
			// line from center to arc starting point
			$this->_outLine($px1, $this->h - $py1);
		} elseif ($startpoint) {
			// arc starting point
			$this->_outPoint($px1, $this->h - $py1);
		}
		// draw arcs
		for ($i = 1; $i <= $nc; ++$i) {
			// starting angle
			$ang = $as + ($i * $arcang);
			if ($i == $nc) {
				$ang = $af;
			}
			$cos_ang = cos($ang);
			$sin_ang = sin($ang);
			// second arc point
			$px2 = $x0 + ($rx * $cos_xang * $cos_ang) - ($ry * $sin_xang * $sin_ang);
			$py2 = $y0 + ($rx * $sin_xang * $cos_ang) + ($ry * $cos_xang * $sin_ang);
			// second Bezier control point
			$qx2 = ($alpha * ((-$rx * $cos_xang * $sin_ang) - ($ry * $sin_xang * $cos_ang)));
			$qy2 = ($alpha * ((-$rx * $sin_xang * $sin_ang) + ($ry * $cos_xang * $cos_ang)));
			// draw arc
			$cx1 = ($px1 + $qx1);
			$cy1 = ($this->h - ($py1 + $qy1));
			$cx2 = ($px2 - $qx2);
			$cy2 = ($this->h - ($py2 - $qy2));
			$cx3 = $px2;
			$cy3 = ($this->h - $py2);
			$this->_outCurve($cx1, $cy1, $cx2, $cy2, $cx3, $cy3);
			// get bounding box coordinates
			$xmin = min($xmin, $cx1, $cx2, $cx3);
			$ymin = min($ymin, $cy1, $cy2, $cy3);
			$xmax = max($xmax, $cx1, $cx2, $cx3);
			$ymax = max($ymax, $cy1, $cy2, $cy3);
			// move to next point
			$px1 = $px2;
			$py1 = $py2;
			$qx1 = $qx2;
			$qy1 = $qy2;
		}
		if ($pie) {
			$this->_outLine($xc, $yc);
			// get bounding box coordinates
			$xmin = min($xmin, $xc);
			$ymin = min($ymin, $yc);
			$xmax = max($xmax, $xc);
			$ymax = max($ymax, $yc);
		}
		return array($xmin, $ymin, $xmax, $ymax);
	}

	/**
	 * Draws a circle.
	 * A circle is formed from n Bezier curves.
	 * @param float $x0 Abscissa of center point.
	 * @param float $y0 Ordinate of center point.
	 * @param float $r Radius.
	 * @param float $angstr Angle start of draw line. Default value: 0.
	 * @param float $angend Angle finish of draw line. Default value: 360.
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param array $line_style Line style of circle. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param array $fill_color Fill color. Format: array(red, green, blue). Default value: default color (empty array).
	 * @param integer $nc Number of curves used to draw a 90 degrees portion of circle.
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function Circle($x0, $y0, $r, $angstr=0, $angend=360, $style='', $line_style=array(), $fill_color=array(), $nc=2) {
		$this->Ellipse($x0, $y0, $r, $r, 0, $angstr, $angend, $style, $line_style, $fill_color, $nc);
	}

	/**
	 * Draws a polygonal line
	 * @param array $p Points 0 to ($np - 1). Array with values (x0, y0, x1, y1,..., x(np-1), y(np - 1))
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param array $line_style Line style of polygon. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all lines. Array like for SetLineStyle().</li>
	 *	 <li>0 to ($np - 1): Line style of each line. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, not draws the line. Default value is default line style (empty array).
	 * @param array $fill_color Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @since 4.8.003 (2009-09-15)
	 * @public
	 */
	public function PolyLine($p, $style='', $line_style=array(), $fill_color=array()) {
		$this->Polygon($p, $style, $line_style, $fill_color, false);
	}

	/**
	 * Draws a polygon.
	 * @param array $p Points 0 to ($np - 1). Array with values (x0, y0, x1, y1,..., x(np-1), y(np - 1))
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param array $line_style Line style of polygon. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all lines. Array like for SetLineStyle().</li>
	 *	 <li>0 to ($np - 1): Line style of each line. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, not draws the line. Default value is default line style (empty array).
	 * @param array $fill_color Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @param boolean $closed if true the polygon is closes, otherwise will remain open
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function Polygon($p, $style='', $line_style=array(), $fill_color=array(), $closed=true) {
		if ($this->state != 2) {
			return;
		}
		$nc = count($p); // number of coordinates
		$np = $nc / 2; // number of points
		if ($closed) {
			// close polygon by adding the first 2 points at the end (one line)
			for ($i = 0; $i < 4; ++$i) {
				$p[$nc + $i] = $p[$i];
			}
			// copy style for the last added line
			if (isset($line_style[0])) {
				$line_style[$np] = $line_style[0];
			}
			$nc += 4;
		}
		if (!(false === strpos($style, 'F')) AND is_array($fill_color)) {
			$this->setFillColorArray($fill_color);
		}
		$op = LIMEPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		$draw = true;
		if ($line_style) {
			if (isset($line_style['all'])) {
				$this->setLineStyle($line_style['all']);
			} else {
				$draw = false;
				if ($op == 'B') {
					// draw fill
					$op = 'f';
					$this->_outPoint($p[0], $p[1]);
					for ($i = 2; $i < $nc; $i = $i + 2) {
						$this->_outLine($p[$i], $p[$i + 1]);
					}
					$this->_out($op);
				}
				// draw outline
				$this->_outPoint($p[0], $p[1]);
				for ($i = 2; $i < $nc; $i = $i + 2) {
					$line_num = ($i / 2) - 1;
					if (isset($line_style[$line_num])) {
						if ($line_style[$line_num] != 0) {
							if (is_array($line_style[$line_num])) {
								$this->_out('S');
								$this->setLineStyle($line_style[$line_num]);
								$this->_outPoint($p[$i - 2], $p[$i - 1]);
								$this->_outLine($p[$i], $p[$i + 1]);
								$this->_out('S');
								$this->_outPoint($p[$i], $p[$i + 1]);
							} else {
								$this->_outLine($p[$i], $p[$i + 1]);
							}
						}
					} else {
						$this->_outLine($p[$i], $p[$i + 1]);
					}
				}
				$this->_out($op);
			}
		}
		if ($draw) {
			$this->_outPoint($p[0], $p[1]);
			for ($i = 2; $i < $nc; $i = $i + 2) {
				$this->_outLine($p[$i], $p[$i + 1]);
			}
			$this->_out($op);
		}
	}

	/**
	 * Draws a regular polygon.
	 * @param float $x0 Abscissa of center point.
	 * @param float $y0 Ordinate of center point.
	 * @param float $r Radius of inscribed circle.
	 * @param integer $ns Number of sides.
	 * @param float $angle Angle oriented (anti-clockwise). Default value: 0.
	 * @param boolean $draw_circle Draw inscribed circle or not. Default value: false.
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param array $line_style Line style of polygon sides. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all sides. Array like for SetLineStyle().</li>
	 *	 <li>0 to ($ns - 1): Line style of each side. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, not draws the side. Default value is default line style (empty array).
	 * @param array $fill_color Fill color. Format: array(red, green, blue). Default value: default color (empty array).
	 * @param string $circle_style Style of rendering of inscribed circle (if draws). Possible values are:
	 * <ul>
	 *	 <li>D or empty string: Draw (default).</li>
	 *	 <li>F: Fill.</li>
	 *	 <li>DF or FD: Draw and fill.</li>
	 *	 <li>CNZ: Clipping mode (using the even-odd rule to determine which regions lie inside the clipping path).</li>
	 *	 <li>CEO: Clipping mode (using the nonzero winding number rule to determine which regions lie inside the clipping path).</li>
	 * </ul>
	 * @param array $circle_outLine_style Line style of inscribed circle (if draws). Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param array $circle_fill_color Fill color of inscribed circle (if draws). Format: array(red, green, blue). Default value: default color (empty array).
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function RegularPolygon($x0, $y0, $r, $ns, $angle=0, $draw_circle=false, $style='', $line_style=array(), $fill_color=array(), $circle_style='', $circle_outLine_style=array(), $circle_fill_color=array()) {
		if (3 > $ns) {
			$ns = 3;
		}
		if ($draw_circle) {
			$this->Circle($x0, $y0, $r, 0, 360, $circle_style, $circle_outLine_style, $circle_fill_color);
		}
		$p = array();
		for ($i = 0; $i < $ns; ++$i) {
			$a = $angle + ($i * 360 / $ns);
			$a_rad = deg2rad((float) $a);
			$p[] = $x0 + ($r * sin($a_rad));
			$p[] = $y0 + ($r * cos($a_rad));
		}
		$this->Polygon($p, $style, $line_style, $fill_color);
	}

	/**
	 * Draws a star polygon
	 * @param float $x0 Abscissa of center point.
	 * @param float $y0 Ordinate of center point.
	 * @param float $r Radius of inscribed circle.
	 * @param integer $nv Number of vertices.
	 * @param integer $ng Number of gap (if ($ng % $nv = 1) then is a regular polygon).
	 * @param float $angle Angle oriented (anti-clockwise). Default value: 0.
	 * @param boolean $draw_circle Draw inscribed circle or not. Default value is false.
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param array $line_style Line style of polygon sides. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all sides. Array like for
	 * SetLineStyle().</li>
	 *	 <li>0 to (n - 1): Line style of each side. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, not draws the side. Default value is default line style (empty array).
	 * @param array $fill_color Fill color. Format: array(red, green, blue). Default value: default color (empty array).
	 * @param string $circle_style Style of rendering of inscribed circle (if draws). Possible values are:
	 * <ul>
	 *	 <li>D or empty string: Draw (default).</li>
	 *	 <li>F: Fill.</li>
	 *	 <li>DF or FD: Draw and fill.</li>
	 *	 <li>CNZ: Clipping mode (using the even-odd rule to determine which regions lie inside the clipping path).</li>
	 *	 <li>CEO: Clipping mode (using the nonzero winding number rule to determine which regions lie inside the clipping path).</li>
	 * </ul>
	 * @param array $circle_outLine_style Line style of inscribed circle (if draws). Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param array $circle_fill_color Fill color of inscribed circle (if draws). Format: array(red, green, blue). Default value: default color (empty array).
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function StarPolygon($x0, $y0, $r, $nv, $ng, $angle=0, $draw_circle=false, $style='', $line_style=array(), $fill_color=array(), $circle_style='', $circle_outLine_style=array(), $circle_fill_color=array()) {
		if ($nv < 2) {
			$nv = 2;
		}
		if ($draw_circle) {
			$this->Circle($x0, $y0, $r, 0, 360, $circle_style, $circle_outLine_style, $circle_fill_color);
		}
		$p2 = array();
		$visited = array();
		for ($i = 0; $i < $nv; ++$i) {
			$a = $angle + ($i * 360 / $nv);
			$a_rad = deg2rad((float) $a);
			$p2[] = $x0 + ($r * sin($a_rad));
			$p2[] = $y0 + ($r * cos($a_rad));
			$visited[] = false;
		}
		$p = array();
		$i = 0;
		do {
			$p[] = $p2[$i * 2];
			$p[] = $p2[($i * 2) + 1];
			$visited[$i] = true;
			$i += $ng;
			$i %= $nv;
		} while (!$visited[$i]);
		$this->Polygon($p, $style, $line_style, $fill_color);
	}

	/**
	 * Draws a rounded rectangle.
	 * @param float $x Abscissa of upper-left corner.
	 * @param float $y Ordinate of upper-left corner.
	 * @param float $w Width.
	 * @param float $h Height.
	 * @param float $r the radius of the circle used to round off the corners of the rectangle.
	 * @param string $round_corner Draws rounded corner or not. String with a 0 (not rounded i-corner) or 1 (rounded i-corner) in i-position. Positions are, in order and begin to 0: top right, bottom right, bottom left and top left. Default value: all rounded corner ("1111").
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param array $border_style Border style of rectangle. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param array $fill_color Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function RoundedRect($x, $y, $w, $h, $r, $round_corner='1111', $style='', $border_style=array(), $fill_color=array()) {
		$this->RoundedRectXY($x, $y, $w, $h, $r, $r, $round_corner, $style, $border_style, $fill_color);
	}

	/**
	 * Draws a rounded rectangle.
	 * @param float $x Abscissa of upper-left corner.
	 * @param float $y Ordinate of upper-left corner.
	 * @param float $w Width.
	 * @param float $h Height.
	 * @param float $rx the x-axis radius of the ellipse used to round off the corners of the rectangle.
	 * @param float $ry the y-axis radius of the ellipse used to round off the corners of the rectangle.
	 * @param string $round_corner Draws rounded corner or not. String with a 0 (not rounded i-corner) or 1 (rounded i-corner) in i-position. Positions are, in order and begin to 0: top right, bottom right, bottom left and top left. Default value: all rounded corner ("1111").
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param array $border_style Border style of rectangle. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param array $fill_color Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @since 4.9.019 (2010-04-22)
	 */
	public function RoundedRectXY($x, $y, $w, $h, $rx, $ry, $round_corner='1111', $style='', $border_style=array(), $fill_color=array()) {
		if ($this->state != 2) {
			return;
		}
		if (($round_corner == '0000') OR (($rx == $ry) AND ($rx == 0))) {
			// Not rounded
			$this->Rect($x, $y, $w, $h, $style, $border_style, $fill_color);
			return;
		}
		// Rounded
		if (!(false === strpos($style, 'F')) AND is_array($fill_color)) {
			$this->setFillColorArray($fill_color);
		}
		$op = LIMEPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$border_style = array();
		}
		if ($border_style) {
			$this->setLineStyle($border_style);
		}
		$MyArc = 4 / 3 * (sqrt(2) - 1);
		$this->_outPoint($x + $rx, $y);
		$xc = $x + $w - $rx;
		$yc = $y + $ry;
		$this->_outLine($xc, $y);
		if ($round_corner[0]) {
			$this->_outCurve($xc + ($rx * $MyArc), $yc - $ry, $xc + $rx, $yc - ($ry * $MyArc), $xc + $rx, $yc);
		} else {
			$this->_outLine($x + $w, $y);
		}
		$xc = $x + $w - $rx;
		$yc = $y + $h - $ry;
		$this->_outLine($x + $w, $yc);
		if ($round_corner[1]) {
			$this->_outCurve($xc + $rx, $yc + ($ry * $MyArc), $xc + ($rx * $MyArc), $yc + $ry, $xc, $yc + $ry);
		} else {
			$this->_outLine($x + $w, $y + $h);
		}
		$xc = $x + $rx;
		$yc = $y + $h - $ry;
		$this->_outLine($xc, $y + $h);
		if ($round_corner[2]) {
			$this->_outCurve($xc - ($rx * $MyArc), $yc + $ry, $xc - $rx, $yc + ($ry * $MyArc), $xc - $rx, $yc);
		} else {
			$this->_outLine($x, $y + $h);
		}
		$xc = $x + $rx;
		$yc = $y + $ry;
		$this->_outLine($x, $yc);
		if ($round_corner[3]) {
			$this->_outCurve($xc - $rx, $yc - ($ry * $MyArc), $xc - ($rx * $MyArc), $yc - $ry, $xc, $yc - $ry);
		} else {
			$this->_outLine($x, $y);
			$this->_outLine($x + $rx, $y);
		}
		$this->_out($op);
	}

	/**
	 * Draws a grahic arrow.
	 * @param float $x0 Abscissa of first point.
	 * @param float $y0 Ordinate of first point.
	 * @param float $x1 Abscissa of second point.
	 * @param float $y1 Ordinate of second point.
	 * @param int $head_style (0 = draw only arrowhead arms, 1 = draw closed arrowhead, but no fill, 2 = closed and filled arrowhead, 3 = filled arrowhead)
	 * @param float $arm_size length of arrowhead arms
	 * @param int $arm_angle angle between an arm and the shaft
	 * @author Piotr Galecki, Nicola Asuni, Andy Meier
	 * @since 4.6.018 (2009-07-10)
	 */
	public function Arrow($x0, $y0, $x1, $y1, $head_style=0, $arm_size=5, $arm_angle=15) {
		// getting arrow direction angle
		// 0 deg angle is when both arms go along X axis. angle grows clockwise.
		$dir_angle = atan2(($y0 - $y1), ($x0 - $x1));
		if ($dir_angle < 0) {
			$dir_angle += (2 * M_PI);
		}
		$arm_angle = deg2rad($arm_angle);
		$sx1 = $x1;
		$sy1 = $y1;
		if ($head_style > 0) {
			// calculate the stopping point for the arrow shaft
			$sx1 = $x1 + (($arm_size - $this->LineWidth) * cos($dir_angle));
			$sy1 = $y1 + (($arm_size - $this->LineWidth) * sin($dir_angle));
		}
		// main arrow line / shaft
		$this->Line($x0, $y0, $sx1, $sy1);
		// left arrowhead arm tip
		$x2L = $x1 + ($arm_size * cos($dir_angle + $arm_angle));
		$y2L = $y1 + ($arm_size * sin($dir_angle + $arm_angle));
		// right arrowhead arm tip
		$x2R = $x1 + ($arm_size * cos($dir_angle - $arm_angle));
		$y2R = $y1 + ($arm_size * sin($dir_angle - $arm_angle));
		$mode = 'D';
		$style = array();
		switch ($head_style) {
			case 0: {
				// draw only arrowhead arms
				$mode = 'D';
				$style = array(1, 1, 0);
				break;
			}
			case 1: {
				// draw closed arrowhead, but no fill
				$mode = 'D';
				break;
			}
			case 2: {
				// closed and filled arrowhead
				$mode = 'DF';
				break;
			}
			case 3: {
				// filled arrowhead
				$mode = 'F';
				break;
			}
		}
		$this->Polygon(array($x2L, $y2L, $x1, $y1, $x2R, $y2R), $mode, $style, array());
	}

		/**
	 * Set a rectangular clipping area.
	 * @param float $x abscissa of the top left corner of the rectangle (or top right corner for RTL mode).
	 * @param float $y ordinate of the top left corner of the rectangle.
	 * @param float $w width of the rectangle.
	 * @param float $h height of the rectangle.
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @protected
	 */
	protected function Clip($x, $y, $w, $h) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rtl) {
			$x = $this->w - $x - $w;
		}
		//save current Graphic State
		$s = 'q';
		//set clipping area
		$s .= sprintf(' %F %F %F %F re W n', $x*$this->k, ($this->h-$y)*$this->k, $w*$this->k, -$h*$this->k);
		//set up transformation matrix for gradient
		$s .= sprintf(' %F 0 0 %F %F %F cm', $w*$this->k, $h*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k);
		$this->_out($s);
	}

	/**
	 * Output gradient.
	 * @param int $type type of gradient (1 Function-based shading; 2 Axial shading; 3 Radial shading; 4 Free-form Gouraud-shaded triangle mesh; 5 Lattice-form Gouraud-shaded triangle mesh; 6 Coons patch mesh; 7 Tensor-product patch mesh). (Not all types are currently supported)
	 * @param array $coords array of coordinates.
	 * @param array $stops array gradient color components: color = array of GRAY, RGB or CMYK color components; offset = (0 to 1) represents a location along the gradient vector; exponent = exponent of the exponential interpolation function (default = 1).
	 * @param array $background An array of colour components appropriate to the colour space, specifying a single background colour value.
	 * @param boolean $antialias A flag indicating whether to filter the shading function to prevent aliasing artifacts.
	 * @author Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function Gradient($type, $coords, $stops, $background=array(), $antialias=false) {
		if (($this->pdfa_mode && $this->pdfa_version < 2) OR ($this->state != 2)) {
			return;
		}
		$n = count($this->gradients) + 1;
		$this->gradients[$n] = array();
		$this->gradients[$n]['type'] = $type;
		$this->gradients[$n]['coords'] = $coords;
		$this->gradients[$n]['antialias'] = $antialias;
		$this->gradients[$n]['colors'] = array();
		$this->gradients[$n]['transparency'] = false;
		// color space
		$numcolspace = count($stops[0]['color']);
		$bcolor = array_values($background);
		switch($numcolspace) {
			case 5:   // SPOT
			case 4: { // CMYK
				$this->gradients[$n]['colspace'] = 'DeviceCMYK';
				if (!empty($background)) {
					$this->gradients[$n]['background'] = sprintf('%F %F %F %F', $bcolor[0]/100, $bcolor[1]/100, $bcolor[2]/100, $bcolor[3]/100);
				}
				break;
			}
			case 3: { // RGB
				$this->gradients[$n]['colspace'] = 'DeviceRGB';
				if (!empty($background)) {
					$this->gradients[$n]['background'] = sprintf('%F %F %F', $bcolor[0]/255, $bcolor[1]/255, $bcolor[2]/255);
				}
				break;
			}
			case 1: { // GRAY SCALE
				$this->gradients[$n]['colspace'] = 'DeviceGray';
				if (!empty($background)) {
					$this->gradients[$n]['background'] = sprintf('%F', $bcolor[0]/255);
				}
				break;
			}
		}
		$num_stops = count($stops);
		$last_stop_id = $num_stops - 1;
		foreach ($stops as $key => $stop) {
			$this->gradients[$n]['colors'][$key] = array();
			// offset represents a location along the gradient vector
			if (isset($stop['offset'])) {
				$this->gradients[$n]['colors'][$key]['offset'] = $stop['offset'];
			} else {
				if ($key == 0) {
					$this->gradients[$n]['colors'][$key]['offset'] = 0;
				} elseif ($key == $last_stop_id) {
					$this->gradients[$n]['colors'][$key]['offset'] = 1;
				} else {
					$offsetstep = (1 - $this->gradients[$n]['colors'][($key - 1)]['offset']) / ($num_stops - $key);
					$this->gradients[$n]['colors'][$key]['offset'] = $this->gradients[$n]['colors'][($key - 1)]['offset'] + $offsetstep;
				}
			}
			if (isset($stop['opacity'])) {
				$this->gradients[$n]['colors'][$key]['opacity'] = $stop['opacity'];
				if ((!($this->pdfa_mode && $this->pdfa_version < 2)) AND ($stop['opacity'] < 1)) {
					$this->gradients[$n]['transparency'] = true;
				}
			} else {
				$this->gradients[$n]['colors'][$key]['opacity'] = 1;
			}
			// exponent for the exponential interpolation function
			if (isset($stop['exponent'])) {
				$this->gradients[$n]['colors'][$key]['exponent'] = $stop['exponent'];
			} else {
				$this->gradients[$n]['colors'][$key]['exponent'] = 1;
			}
			// set colors
			$color = array_values($stop['color']);
			switch($numcolspace) {
				case 5:   // SPOT
				case 4: { // CMYK
					$this->gradients[$n]['colors'][$key]['color'] = sprintf('%F %F %F %F', $color[0]/100, $color[1]/100, $color[2]/100, $color[3]/100);
					break;
				}
				case 3: { // RGB
					$this->gradients[$n]['colors'][$key]['color'] = sprintf('%F %F %F', $color[0]/255, $color[1]/255, $color[2]/255);
					break;
				}
				case 1: { // GRAY SCALE
					$this->gradients[$n]['colors'][$key]['color'] = sprintf('%F', $color[0]/255);
					break;
				}
			}
		}
		if ($this->gradients[$n]['transparency']) {
			// paint luminosity gradient
			$this->_out('/TGS'.$n.' gs');
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
	 * Draw the sector of a circle.
	 * It can be used for instance to render pie charts.
	 * @param float $xc abscissa of the center.
	 * @param float $yc ordinate of the center.
	 * @param float $r radius.
	 * @param float $a start angle (in degrees).
	 * @param float $b end angle (in degrees).
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param float $cw indicates whether to go clockwise (default: true).
	 * @param float $o origin of angles (0 for 3 o'clock, 90 for noon, 180 for 9 o'clock, 270 for 6 o'clock). Default: 90.
	 * @author Maxime Delorme, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function PieSector($xc, $yc, $r, $a, $b, $style='FD', $cw=true, $o=90) {
		$this->PieSectorXY($xc, $yc, $r, $r, $a, $b, $style, $cw, $o);
	}

	/**
	 * Draw the sector of an ellipse.
	 * It can be used for instance to render pie charts.
	 * @param float $xc abscissa of the center.
	 * @param float $yc ordinate of the center.
	 * @param float $rx the x-axis radius.
	 * @param float $ry the y-axis radius.
	 * @param float $a start angle (in degrees).
	 * @param float $b end angle (in degrees).
	 * @param string $style Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param float $cw indicates whether to go clockwise.
	 * @param float $o origin of angles (0 for 3 o'clock, 90 for noon, 180 for 9 o'clock, 270 for 6 o'clock).
	 * @param integer $nc Number of curves used to draw a 90 degrees portion of arc.
	 * @author Maxime Delorme, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function PieSectorXY($xc, $yc, $rx, $ry, $a, $b, $style='FD', $cw=false, $o=0, $nc=2) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rtl) {
			$xc = ($this->w - $xc);
		}
		$op = LIMEPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		if ($cw) {
			$d = $b;
			$b = (360 - $a + $o);
			$a = (360 - $d + $o);
		} else {
			$b += $o;
			$a += $o;
		}
		$this->_outellipticalarc($xc, $yc, $rx, $ry, 0, $a, $b, true, $nc);
		$this->_out($op);
	}

	/**
	 * Embed vector-based Adobe Illustrator (AI) or AI-compatible EPS files.
	 * NOTE: EPS is not yet fully implemented, use the setRasterizeVectorImages() method to enable/disable rasterization of vector images using ImageMagick library.
	 * Only vector drawing is supported, not text or bitmap.
	 * Although the script was successfully tested with various AI format versions, best results are probably achieved with files that were exported in the AI3 format (tested with Illustrator CS2, Freehand MX and Photoshop CS2).
	 * @param string $file Name of the file containing the image or a '@' character followed by the EPS/AI data string.
	 * @param float|null $x Abscissa of the upper-left corner.
	 * @param float|null $y Ordinate of the upper-left corner.
	 * @param float $w Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param float $h Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param mixed $link URL or identifier returned by AddLink().
	 * @param boolean $useBoundingBox specifies whether to position the bounding box (true) or the complete canvas (false) at location (x,y). Default value is true.
	 * @param string $align Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param string $palign Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param mixed $border Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param boolean $fitonpage if true the image is resized to not exceed page dimensions.
	 * @param boolean $fixoutvals if true remove values outside the bounding box.
	 * @author Valentin Schmidt, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function ImageEps($file, $x=null, $y=null, $w=0, $h=0, $link='', $useBoundingBox=true, $align='', $palign='', $border=0, $fitonpage=false, $fixoutvals=false) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rasterize_vector_images AND ($w > 0) AND ($h > 0)) {
			// convert EPS to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'EPS', $link, $align, true, 300, $palign, false, false, $border, false, false, $fitonpage);
		}
		if (LIMEPDF_STATIC::empty_string($x)) {
			$x = $this->x;
		}
		if (LIMEPDF_STATIC::empty_string($y)) {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		$k = $this->k;
		if ($file[0] === '@') { // image from string
			$data = substr($file, 1);
		} else { // EPS/AI file
            $data = $this->getCachedFileContents($file);
		}
		if ($data === FALSE) {
			$this->Error('EPS file not found: '.$file);
		}
		$regs = array();
		// EPS/AI compatibility check (only checks files created by Adobe Illustrator!)
		preg_match("/%%Creator:([^\r\n]+)/", $data, $regs); # find Creator
		if (count($regs) > 1) {
			$version_str = trim($regs[1]); # e.g. "Adobe Illustrator(R) 8.0"
			if (strpos($version_str, 'Adobe Illustrator') !== false) {
				$versexp = explode(' ', $version_str);
				$version = (float)array_pop($versexp);
				if ($version >= 9) {
					$this->Error('This version of Adobe Illustrator file is not supported: '.$file);
				}
			}
		}
		// strip binary bytes in front of PS-header
		$start = strpos($data, '%!PS-Adobe');
		if ($start > 0) {
			$data = substr($data, $start);
		}
		// find BoundingBox params
		preg_match("/%%BoundingBox:([^\r\n]+)/", $data, $regs);
		if (count($regs) > 1) {
			list($x1, $y1, $x2, $y2) = explode(' ', trim($regs[1]));
		} else {
			$this->Error('No BoundingBox found in EPS/AI file: '.$file);
		}
		$start = strpos($data, '%%EndSetup');
		if ($start === false) {
			$start = strpos($data, '%%EndProlog');
		}
		if ($start === false) {
			$start = strpos($data, '%%BoundingBox');
		}
		$data = substr($data, $start);
		$end = strpos($data, '%%PageTrailer');
		if ($end===false) {
			$end = strpos($data, 'showpage');
		}
		if ($end) {
			$data = substr($data, 0, $end);
		}
		// calculate image width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			$w = ($x2 - $x1) / $k;
			$h = ($y2 - $y1) / $k;
		} elseif ($w <= 0) {
			$w = ($x2-$x1) / $k * ($h / (($y2 - $y1) / $k));
		} elseif ($h <= 0) {
			$h = ($y2 - $y1) / $k * ($w / (($x2 - $x1) / $k));
		}
		// fit the image on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		if ($this->rasterize_vector_images) {
			// convert EPS to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'EPS', $link, $align, true, 300, $palign, false, false, $border, false, false, $fitonpage);
		}
		// set scaling factors
		$scale_x = $w / (($x2 - $x1) / $k);
		$scale_y = $h / (($y2 - $y1) / $k);
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x - $w;
			}
			$this->img_rb_x = $ximg;
		} else {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x;
			}
			$this->img_rb_x = $ximg + $w;
		}
		if ($useBoundingBox) {
			$dx = $ximg * $k - $x1;
			$dy = $y * $k - $y1;
		} else {
			$dx = $ximg * $k;
			$dy = $y * $k;
		}
		// save the current graphic state
		$this->_out('q'.$this->epsmarker);
		// translate
		$this->_out(sprintf('%F %F %F %F %F %F cm', 1, 0, 0, 1, $dx, $dy + ($this->hPt - (2 * $y * $k) - ($y2 - $y1))));
		// scale
		$this->_out(sprintf('%F %F %F %F %F %F cm', $scale_x, 0, 0, $scale_y, $x1 * (1 - $scale_x), $y2 * (1 - $scale_y)));
		// handle pc/unix/mac line endings
		$lines = preg_split('/[\r\n]+/si', $data, -1, PREG_SPLIT_NO_EMPTY);
		$u=0;
		$cnt = count($lines);
		for ($i=0; $i < $cnt; ++$i) {
			$line = $lines[$i];
			if (($line == '') OR ($line[0] == '%')) {
				continue;
			}
			$len = strlen($line);
			// check for spot color names
			$color_name = '';
			if (strcasecmp('x', substr(trim($line), -1)) == 0) {
				if (preg_match('/\([^\)]*\)/', $line, $matches) > 0) {
					// extract spot color name
					$color_name = $matches[0];
					// remove color name from string
					$line = str_replace(' '.$color_name, '', $line);
					// remove pharentesis from color name
					$color_name = substr($color_name, 1, -1);
				}
			}
			$chunks = explode(' ', $line);
			$cmd = trim(array_pop($chunks));
			// RGB
			if (($cmd == 'Xa') OR ($cmd == 'XA')) {
				$b = array_pop($chunks);
				$g = array_pop($chunks);
				$r = array_pop($chunks);
				$this->_out(''.$r.' '.$g.' '.$b.' '.($cmd=='Xa'?'rg':'RG')); //substr($line, 0, -2).'rg' -> in EPS (AI8): c m y k r g b rg!
				continue;
			}
			$skip = false;
			if ($fixoutvals) {
				// check for values outside the bounding box
				switch ($cmd) {
					case 'm':
					case 'l':
					case 'L': {
						// skip values outside bounding box
						foreach ($chunks as $key => $val) {
							if ((($key % 2) == 0) AND (($val < $x1) OR ($val > $x2))) {
								$skip = true;
							} elseif ((($key % 2) != 0) AND (($val < $y1) OR ($val > $y2))) {
								$skip = true;
							}
						}
					}
				}
			}
			switch ($cmd) {
				case 'm':
				case 'l':
				case 'v':
				case 'y':
				case 'c':
				case 'k':
				case 'K':
				case 'g':
				case 'G':
				case 's':
				case 'S':
				case 'J':
				case 'j':
				case 'w':
				case 'M':
				case 'd':
				case 'n': {
					if ($skip) {
						break;
					}
					$this->_out($line);
					break;
				}
				case 'x': {// custom fill color
					if (empty($color_name)) {
						// CMYK color
						list($col_c, $col_m, $col_y, $col_k) = $chunks;
						$this->_out(''.$col_c.' '.$col_m.' '.$col_y.' '.$col_k.' k');
					} else {
						// Spot Color (CMYK + tint)
						list($col_c, $col_m, $col_y, $col_k, $col_t) = $chunks;
						$this->AddSpotColor($color_name, ($col_c * 100), ($col_m * 100), ($col_y * 100), ($col_k * 100));
						$color_cmd = sprintf('/CS%d cs %F scn', $this->spot_colors[$color_name]['i'], (1 - $col_t));
						$this->_out($color_cmd);
					}
					break;
				}
				case 'X': { // custom stroke color
					if (empty($color_name)) {
						// CMYK color
						list($col_c, $col_m, $col_y, $col_k) = $chunks;
						$this->_out(''.$col_c.' '.$col_m.' '.$col_y.' '.$col_k.' K');
					} else {
						// Spot Color (CMYK + tint)
						list($col_c, $col_m, $col_y, $col_k, $col_t) = $chunks;
						$this->AddSpotColor($color_name, ($col_c * 100), ($col_m * 100), ($col_y * 100), ($col_k * 100));
						$color_cmd = sprintf('/CS%d CS %F SCN', $this->spot_colors[$color_name]['i'], (1 - $col_t));
						$this->_out($color_cmd);
					}
					break;
				}
				case 'Y':
				case 'N':
				case 'V':
				case 'L':
				case 'C': {
					if ($skip) {
						break;
					}
					$line[($len - 1)] = strtolower($cmd);
					$this->_out($line);
					break;
				}
				case 'b':
				case 'B': {
					$this->_out($cmd . '*');
					break;
				}
				case 'f':
				case 'F': {
					if ($u > 0) {
						$isU = false;
						$max = min(($i + 5), $cnt);
						for ($j = ($i + 1); $j < $max; ++$j) {
							$isU = ($isU OR (($lines[$j] == 'U') OR ($lines[$j] == '*U')));
						}
						if ($isU) {
							$this->_out('f*');
						}
					} else {
						$this->_out('f*');
					}
					break;
				}
				case '*u': {
					++$u;
					break;
				}
				case '*U': {
					--$u;
					break;
				}
			}
		}
		// restore previous graphic state
		$this->_out($this->epsmarker.'Q');
		if (!empty($border)) {
			$bx = $this->x;
			$by = $this->y;
			$this->x = $ximg;
			if ($this->rtl) {
				$this->x += $w;
			}
			$this->y = $y;
			$this->Cell($w, $h, '', $border, 0, '', 0, '', 0, true);
			$this->x = $bx;
			$this->y = $by;
		}
		if ($link) {
			$this->Link($ximg, $y, $w, $h, $link, 0);
		}
		// set pointer to align the next text/objects
		switch($align) {
			case 'T':{
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M':{
				$this->y = $y + round($h/2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B':{
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N':{
				$this->setY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
	}

	/**
	 * Outputs the "save graphics state" operator 'q'
	 * @protected
	 */
	protected function _outSaveGraphicsState() {
		$this->_out('q');
	}

	/**
	 * Outputs the "restore graphics state" operator 'Q'
	 * @protected
	 */
	protected function _outRestoreGraphicsState() {
		$this->_out('Q');
	}
	// END GRAPHIC FUNCTIONS SECTION -----------------------

}