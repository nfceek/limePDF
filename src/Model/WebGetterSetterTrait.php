<?php

namespace LimePDF\Model;

use LimePDF\Include\ColorsTrait;
use LimePDF\Include\FontTrait;
use LimePDF\Support\StaticTrait;

trait WebGetterSetterTrait {

    /**
	 * Returns the border width from CSS property
	 * @param string $width border width
	 * @return int with in user units
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderWidth($width) {
		if ($width == 'thin') {
			$width = (2 / $this->k);
		} elseif ($width == 'medium') {
			$width = (4 / $this->k);
		} elseif ($width == 'thick') {
			$width = (6 / $this->k);
		} else {
			$width = $this->getHTMLUnitToUnits($width, 1, 'px', false);
		}
		return $width;
	}

	/**
	 * Returns the border dash style from CSS property
	 * @param string $style border style to convert
	 * @return int sash style (return -1 in case of none or hidden border)
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderDashStyle($style) {
		switch (strtolower($style)) {
			case 'none':
			case 'hidden': {
				$dash = -1;
				break;
			}
			case 'dotted': {
				$dash = 1;
				break;
			}
			case 'dashed': {
				$dash = 3;
				break;
			}
			case 'double':
			case 'groove':
			case 'ridge':
			case 'inset':
			case 'outset':
			case 'solid':
			default: {
				$dash = 0;
				break;
			}
		}
		return $dash;
	}

	/**
	 * Returns the border style array from CSS border properties
	 * @param string $cssborder border properties
	 * @return array containing border properties
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderStyle($cssborder) {
		$bprop = preg_split('/[\s]+/', trim($cssborder));
		$count = count($bprop);
		if ($count > 0 && $bprop[$count - 1] === '!important') {
			unset($bprop[$count - 1]);
			--$count;
		}

		$border = array(); // value to be returned
		switch ($count) {
			case 2: {
				$width = 'medium';
				$style = $bprop[0];
				$color = $bprop[1];
				break;
			}
			case 1: {
				$width = 'medium';
				$style = $bprop[0];
				$color = 'black';
				break;
			}
			case 0: {
				$width = 'medium';
				$style = 'solid';
				$color = 'black';
				break;
			}
			default: {
				$width = $bprop[0];
				$style = $bprop[1];
				$color = $bprop[2];
				break;
			}
		}
		if ($style == 'none') {
			return array();
		}
		$border['cap'] = 'square';
		$border['join'] = 'miter';
		$border['dash'] = $this->getCSSBorderDashStyle($style);
		if ($border['dash'] < 0) {
			return array();
		}
		$border['width'] = $this->getCSSBorderWidth($width);
		$border['color'] = $this->convertHTMLColorToDec($color, $this->spot_colors);
		return $border;
	}

	/**
	 * Get the internal Cell padding from CSS attribute.
	 * @param string $csspadding padding properties
	 * @param float $width width of the containing element
	 * @return array of cell paddings
	 * @public
	 * @since 5.9.000 (2010-10-04)
	 */
	public function getCSSPadding($csspadding, $width=0) {
		$padding = preg_split('/[\s]+/', trim($csspadding));
		$cell_padding = array(); // value to be returned
		switch (count($padding)) {
			case 4: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[1];
				$cell_padding['B'] = $padding[2];
				$cell_padding['L'] = $padding[3];
				break;
			}
			case 3: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[1];
				$cell_padding['B'] = $padding[2];
				$cell_padding['L'] = $padding[1];
				break;
			}
			case 2: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[1];
				$cell_padding['B'] = $padding[0];
				$cell_padding['L'] = $padding[1];
				break;
			}
			case 1: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[0];
				$cell_padding['B'] = $padding[0];
				$cell_padding['L'] = $padding[0];
				break;
			}
			default: {
				return $this->cell_padding;
			}
		}
		if ($width == 0) {
			$width = $this->w - $this->lMargin - $this->rMargin;
		}
		$cell_padding['T'] = $this->getHTMLUnitToUnits($cell_padding['T'], $width, 'px', false);
		$cell_padding['R'] = $this->getHTMLUnitToUnits($cell_padding['R'], $width, 'px', false);
		$cell_padding['B'] = $this->getHTMLUnitToUnits($cell_padding['B'], $width, 'px', false);
		$cell_padding['L'] = $this->getHTMLUnitToUnits($cell_padding['L'], $width, 'px', false);
		return $cell_padding;
	}

	/**
	 * Get the internal Cell margin from CSS attribute.
	 * @param string $cssmargin margin properties
	 * @param float $width width of the containing element
	 * @return array of cell margins
	 * @public
	 * @since 5.9.000 (2010-10-04)
	 */
	public function getCSSMargin($cssmargin, $width=0) {
		$margin = preg_split('/[\s]+/', trim($cssmargin));
		$cell_margin = array(); // value to be returned
		switch (count($margin)) {
			case 4: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[1];
				$cell_margin['B'] = $margin[2];
				$cell_margin['L'] = $margin[3];
				break;
			}
			case 3: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[1];
				$cell_margin['B'] = $margin[2];
				$cell_margin['L'] = $margin[1];
				break;
			}
			case 2: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[1];
				$cell_margin['B'] = $margin[0];
				$cell_margin['L'] = $margin[1];
				break;
			}
			case 1: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[0];
				$cell_margin['B'] = $margin[0];
				$cell_margin['L'] = $margin[0];
				break;
			}
			default: {
				return $this->cell_margin;
			}
		}
		if ($width == 0) {
			$width = $this->w - $this->lMargin - $this->rMargin;
		}
		$cell_margin['T'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['T']), $width, 'px', false);
		$cell_margin['R'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['R']), $width, 'px', false);
		$cell_margin['B'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['B']), $width, 'px', false);
		$cell_margin['L'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['L']), $width, 'px', false);
		return $cell_margin;
	}

	/**
	 * Get the border-spacing from CSS attribute.
	 * @param string $cssbspace border-spacing CSS properties
	 * @param float $width width of the containing element
	 * @return array of border spacings
	 * @public
	 * @since 5.9.010 (2010-10-27)
	 */
	public function getCSSBorderMargin($cssbspace, $width=0) {
		$space = preg_split('/[\s]+/', trim($cssbspace));
		$border_spacing = array(); // value to be returned
		switch (count($space)) {
			case 2: {
				$border_spacing['H'] = $space[0];
				$border_spacing['V'] = $space[1];
				break;
			}
			case 1: {
				$border_spacing['H'] = $space[0];
				$border_spacing['V'] = $space[0];
				break;
			}
			default: {
				return array('H' => 0, 'V' => 0);
			}
		}
		if ($width == 0) {
			$width = $this->w - $this->lMargin - $this->rMargin;
		}
		$border_spacing['H'] = $this->getHTMLUnitToUnits($border_spacing['H'], $width, 'px', false);
		$border_spacing['V'] = $this->getHTMLUnitToUnits($border_spacing['V'], $width, 'px', false);
		return $border_spacing;
	}

	/**
	 * Returns the letter-spacing value from CSS value
	 * @param string $spacing letter-spacing value
	 * @param float $parent font spacing (tracking) value of the parent element
	 * @return float quantity to increases or decreases the space between characters in a text.
	 * @protected
	 * @since 5.9.000 (2010-10-02)
	 */
	protected function getCSSFontSpacing($spacing, $parent=0) {
		$val = 0; // value to be returned
		$spacing = trim($spacing);
		switch ($spacing) {
			case 'normal': {
				$val = 0;
				break;
			}
			case 'inherit': {
				if ($parent == 'normal') {
					$val = 0;
				} else {
					$val = $parent;
				}
				break;
			}
			default: {
				$val = $this->getHTMLUnitToUnits($spacing, 0, 'px', false);
			}
		}
		return $val;
	}

	/**
	 * Returns the percentage of font stretching from CSS value
	 * @param string $stretch stretch mode
	 * @param float $parent stretch value of the parent element
	 * @return float font stretching percentage
	 * @protected
	 * @since 5.9.000 (2010-10-02)
	 */
	protected function getCSSFontStretching($stretch, $parent=100) {
		$val = 100; // value to be returned
		$stretch = trim($stretch);
		switch ($stretch) {
			case 'ultra-condensed': {
				$val = 40;
				break;
			}
			case 'extra-condensed': {
				$val = 55;
				break;
			}
			case 'condensed': {
				$val = 70;
				break;
			}
			case 'semi-condensed': {
				$val = 85;
				break;
			}
			case 'normal': {
				$val = 100;
				break;
			}
			case 'semi-expanded': {
				$val = 115;
				break;
			}
			case 'expanded': {
				$val = 130;
				break;
			}
			case 'extra-expanded': {
				$val = 145;
				break;
			}
			case 'ultra-expanded': {
				$val = 160;
				break;
			}
			case 'wider': {
				$val = ($parent + 10);
				break;
			}
			case 'narrower': {
				$val = ($parent - 10);
				break;
			}
			case 'inherit': {
				if ($parent == 'normal') {
					$val = 100;
				} else {
					$val = $parent;
				}
				break;
			}
			default: {
				$val = $this->getHTMLUnitToUnits($stretch, 100, '%', false);
			}
		}
		return $val;
	}

	/**
	 * Convert HTML string containing font size value to points
	 * @param string $val String containing font size value and unit.
	 * @param float $refsize Reference font size in points.
	 * @param float $parent_size Parent font size in points.
	 * @param string $defaultunit Default unit (can be one of the following: %, em, ex, px, in, mm, pc, pt).
	 * @return float value in points
	 * @public
	 */
	public function getHTMLFontUnits($val, $refsize=12, $parent_size=12, $defaultunit='pt') {
		$refsize = $this->getFontRefSize($refsize);
		$parent_size = $this->getFontRefSize($parent_size, $refsize);
		switch ($val) {
			case 'xx-small': {
				$size = ($refsize - 4);
				break;
			}
			case 'x-small': {
				$size = ($refsize - 3);
				break;
			}
			case 'small': {
				$size = ($refsize - 2);
				break;
			}
			case 'medium': {
				$size = $refsize;
				break;
			}
			case 'large': {
				$size = ($refsize + 2);
				break;
			}
			case 'x-large': {
				$size = ($refsize + 4);
				break;
			}
			case 'xx-large': {
				$size = ($refsize + 6);
				break;
			}
			case 'smaller': {
				$size = ($parent_size - 3);
				break;
			}
			case 'larger': {
				$size = ($parent_size + 3);
				break;
			}
			default: {
				$parentSize = $this->getHTMLUnitToUnits($parent_size, $refsize, $defaultunit, true);
				$size = $this->getHTMLUnitToUnits($val, $parent_size, $defaultunit, true);
			}
		}
		return $size;
	}

	/**
	 * Returns the HTML DOM array.
	 * @param string $html html code
	 * @return array
	 * @protected
	 * @since 3.2.000 (2008-06-20)
	 */
	protected function getHtmlDomArray($html) {
		// set inheritable properties fot the first void element
		// possible inheritable properties are: azimuth, border-collapse, border-spacing, caption-side, color, cursor, direction, empty-cells, font, font-family, font-stretch, font-size, font-size-adjust, font-style, font-variant, font-weight, letter-spacing, line-height, list-style, list-style-image, list-style-position, list-style-type, orphans, page, page-break-inside, quotes, speak, speak-header, text-align, text-indent, text-transform, volume, white-space, widows, word-spacing
		$dom = array(
			array(
				'tag' => false,
				'block' => false,
				'value' => '',
				'parent' => 0,
				'hide' => false,
				'fontname' => $this->FontFamily,
				'fontstyle' => $this->FontStyle,
				'fontsize' => $this->FontSizePt,
				'font-stretch' => $this->font_stretching,
				'letter-spacing' => $this->font_spacing,
				'stroke' => $this->textstrokewidth,
				'fill' => (($this->textrendermode % 2) == 0),
				'clip' => ($this->textrendermode > 3),
				'line-height' => $this->cell_height_ratio,
				'bgcolor' => false,
				'fgcolor' => $this->fgcolor, // color
				'strokecolor' => $this->strokecolor,
				'align' => '',
				'listtype' => '',
				'text-indent' => 0,
				'text-transform' => '',
				'border' => array(),
				'dir' => $this->rtl?'rtl':'ltr',
				'width' => 0,
				'height' => 0,
				'x' => 0,
				'y' => 0,
				'w' => 0,
				'h' => 0,
				'l' => 0,
				't' => 0,
				'r' => 0,
				'b' => 0,
				'padding' => array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0),
				'margin' => array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0),
				'border-spacing' => array('H' => 0, 'V' => 0),
				'border-collapse' => 'separate',
			)
		);

		if($html === '' || $html === null) {
			return $dom;
		}
		// array of CSS styles ( selector => properties).
		$css = array();
		// get CSS array defined at previous call
		$matches = array();
		if (preg_match_all('/<cssarray>([^\<]*?)<\/cssarray>/is', $html, $matches) > 0) {
			if (isset($matches[1][0])) {
				$css = array_merge($css, json_decode($this->unhtmlentities($matches[1][0]), true));
			}
			$html = preg_replace('/<cssarray>(.*?)<\/cssarray>/is', '', $html);
		}
		// extract external CSS files
		$matches = array();
		if (preg_match_all('/<link([^\>]*?)>/is', $html, $matches) > 0) {
			foreach ($matches[1] as $key => $link) {
				$type = array();
				if (preg_match('/type[\s]*=[\s]*"text\/css"/', $link, $type)) {
					$type = array();
					preg_match('/media[\s]*=[\s]*"([^"]*)"/', $link, $type);
					// get 'all' and 'print' media, other media types are discarded
					// (all, braille, embossed, handheld, print, projection, screen, speech, tty, tv)
					if (empty($type) OR (isset($type[1]) AND (($type[1] == 'all') OR ($type[1] == 'print')))) {
						$type = array();
						if (preg_match('/href[\s]*=[\s]*"([^"]*)"/', $link, $type) > 0) {
							// read CSS data file
                            $cssdata = $this->getCachedFileContents(trim($type[1]));
							if (($cssdata !== FALSE) AND (strlen($cssdata) > 0)) {
								$css = array_merge($css, $this->extractCSSproperties($cssdata));
							}
						}
					}
				}
			}
		}
		// extract style tags
		$matches = array();
		if (preg_match_all('/<style([^\>]*?)>([^\<]*?)<\/style>/is', $html, $matches) > 0) {
			foreach ($matches[1] as $key => $media) {
				$type = array();
				preg_match('/media[\s]*=[\s]*"([^"]*)"/', $media, $type);
				// get 'all' and 'print' media, other media types are discarded
				// (all, braille, embossed, handheld, print, projection, screen, speech, tty, tv)
				if (empty($type) OR (isset($type[1]) AND (($type[1] == 'all') OR ($type[1] == 'print')))) {
					$cssdata = $matches[2][$key];
					$css = array_merge($css, $this->extractCSSproperties($cssdata));
				}
			}
		}
		// create a special tag to contain the CSS array (used for table content)
		$csstagarray = '<cssarray>'.htmlentities(json_encode($css)).'</cssarray>';
		// remove head and style blocks
		$html = preg_replace('/<head([^\>]*?)>(.*?)<\/head>/is', '', $html);
		$html = preg_replace('/<style([^\>]*?)>([^\<]*?)<\/style>/is', '', $html);
		// define block tags
		$blocktags = array('blockquote','br','dd','dl','div','dt','h1','h2','h3','h4','h5','h6','hr','li','ol','p','pre','ul','tcpdf','table','tr','td');
		// define self-closing tags
		$selfclosingtags = array('area','base','basefont','br','hr','input','img','link','meta');
		// remove all unsupported tags (the line below lists all supported tags)
		$html = strip_tags($html, '<marker/><a><b><blockquote><body><br><br/><dd><del><div><dl><dt><em><font><form><h1><h2><h3><h4><h5><h6><hr><hr/><i><img><input><label><li><ol><option><p><pre><s><select><small><span><strike><strong><sub><sup><table><tablehead><tcpdf><td><textarea><th><thead><tr><tt><u><ul>');
		//replace some blank characters
		$html = preg_replace('/<pre/', '<xre', $html); // preserve pre tag
		$html = preg_replace('/<(table|tr|td|th|tcpdf|blockquote|dd|div|dl|dt|form|h1|h2|h3|h4|h5|h6|br|hr|li|ol|ul|p)([^\>]*)>[\n\r\t]+/', '<\\1\\2>', $html);
		$html = preg_replace('@(\r\n|\r)@', "\n", $html);
		$repTable = array("\t" => ' ', "\0" => ' ', "\x0B" => ' ', "\\" => "\\\\");
		$html = strtr($html, $repTable);
		$offset = 0;
		while (($offset < strlen($html)) AND ($pos = strpos($html, '</pre>', $offset)) !== false) {
			$html_a = substr($html, 0, $offset);
			$html_b = substr($html, $offset, ($pos - $offset + 6));
			while (preg_match("'<xre([^\>]*)>(.*?)\n(.*?)</pre>'si", $html_b)) {
				// preserve newlines on <pre> tag
				$html_b = preg_replace("'<xre([^\>]*)>(.*?)\n(.*?)</pre>'si", "<xre\\1>\\2<br />\\3</pre>", $html_b);
			}
			while (preg_match("'<xre([^\>]*)>(.*?)".$this->re_space['p']."(.*?)</pre>'".$this->re_space['m'], $html_b)) {
				// preserve spaces on <pre> tag
				$html_b = preg_replace("'<xre([^\>]*)>(.*?)".$this->re_space['p']."(.*?)</pre>'".$this->re_space['m'], "<xre\\1>\\2&nbsp;\\3</pre>", $html_b);
			}
			$html = $html_a.$html_b.substr($html, $pos + 6);
			$offset = strlen($html_a.$html_b);
		}
		$offset = 0;
		while (($offset < strlen($html)) AND ($pos = strpos($html, '</textarea>', $offset)) !== false) {
			$html_a = substr($html, 0, $offset);
			$html_b = substr($html, $offset, ($pos - $offset + 11));
			while (preg_match("'<textarea([^\>]*)>(.*?)\n(.*?)</textarea>'si", $html_b)) {
				// preserve newlines on <textarea> tag
				$html_b = preg_replace("'<textarea([^\>]*)>(.*?)\n(.*?)</textarea>'si", "<textarea\\1>\\2<TBR>\\3</textarea>", $html_b);
				$html_b = preg_replace("'<textarea([^\>]*)>(.*?)[\"](.*?)</textarea>'si", "<textarea\\1>\\2''\\3</textarea>", $html_b);
			}
			$html = $html_a.$html_b.substr($html, $pos + 11);
			$offset = strlen($html_a.$html_b);
		}
		$html = preg_replace('/([\s]*)<option/si', '<option', $html);
		$html = preg_replace('/<\/option>([\s]*)/si', '</option>', $html);
		$offset = 0;
		while (($offset < strlen($html)) AND ($pos = strpos($html, '</option>', $offset)) !== false) {
			$html_a = substr($html, 0, $offset);
			$html_b = substr($html, $offset, ($pos - $offset + 9));
			while (preg_match("'<option([^\>]*)>(.*?)</option>'si", $html_b)) {
				$html_b = preg_replace("'<option([\s]+)value=\"([^\"]*)\"([^\>]*)>(.*?)</option>'si", "\\2#!TaB!#\\4#!NwL!#", $html_b);
				$html_b = preg_replace("'<option([^\>]*)>(.*?)</option>'si", "\\2#!NwL!#", $html_b);
			}
			$html = $html_a.$html_b.substr($html, $pos + 9);
			$offset = strlen($html_a.$html_b);
		}
		if (preg_match("'</select'si", $html)) {
			$html = preg_replace("'<select([^\>]*)>'si", "<select\\1 opt=\"", $html);
			$html = preg_replace("'#!NwL!#</select>'si", "\" />", $html);
		}
		$html = str_replace("\n", ' ', $html);
		// restore textarea newlines
		$html = str_replace('<TBR>', "\n", $html);
		// remove extra spaces from code
		$html = preg_replace('/[\s]+<\/(table|tr|ul|ol|dl)>/', '</\\1>', $html);
		$html = preg_replace('/'.$this->re_space['p'].'+<\/(td|th|li|dt|dd)>/'.$this->re_space['m'], '</\\1>', $html);
		$html = preg_replace('/[\s]+<(tr|td|th|li|dt|dd)/', '<\\1', $html);
		$html = preg_replace('/'.$this->re_space['p'].'+<(ul|ol|dl|br)/'.$this->re_space['m'], '<\\1', $html);
		$html = preg_replace('/<\/(table|tr|td|th|blockquote|dd|dt|dl|div|dt|h1|h2|h3|h4|h5|h6|hr|li|ol|ul|p)>[\s]+</', '</\\1><', $html);
		$html = preg_replace('/<\/(td|th)>/', '<marker style="font-size:0"/></\\1>', $html);
		$html = preg_replace('/<\/table>([\s]*)<marker style="font-size:0"\/>/', '</table>', $html);
		$html = preg_replace('/'.$this->re_space['p'].'+<img/'.$this->re_space['m'], chr(32).'<img', $html);
		$html = preg_replace('/<img([^\>]*)>[\s]+([^\<])/xi', '<img\\1>&nbsp;\\2', $html);
		$html = preg_replace('/<img([^\>]*)>/xi', '<img\\1><span><marker style="font-size:0"/></span>', $html);
		$html = preg_replace('/<xre/', '<pre', $html); // restore pre tag
		$html = preg_replace('/<textarea([^\>]*)>([^\<]*)<\/textarea>/xi', '<textarea\\1 value="\\2" />', $html);
		$html = preg_replace('/<li([^\>]*)><\/li>/', '<li\\1>&nbsp;</li>', $html);
		$html = preg_replace('/<li([^\>]*)>'.$this->re_space['p'].'*<img/'.$this->re_space['m'], '<li\\1><font size="1">&nbsp;</font><img', $html);
		$html = preg_replace('/<([^\>\/]*)>[\s]/', '<\\1>&nbsp;', $html); // preserve some spaces
		$html = preg_replace('/[\s]<\/([^\>]*)>/', '&nbsp;</\\1>', $html); // preserve some spaces
		$html = preg_replace('/<su([bp])/', '<zws/><su\\1', $html); // fix sub/sup alignment
		$html = preg_replace('/<\/su([bp])>/', '</su\\1><zws/>', $html); // fix sub/sup alignment
		$html = preg_replace('/'.$this->re_space['p'].'+/'.$this->re_space['m'], chr(32), $html); // replace multiple spaces with a single space
		// trim string
		$html = $this->stringTrim($html);
		// fix br tag after li
		$html = preg_replace('/<li><br([^\>]*)>/', '<li> <br\\1>', $html);
		// fix first image tag alignment
		$html = preg_replace('/^<img/', '<span style="font-size:0"><br /></span> <img', $html, 1);
		// pattern for generic tag
		$tagpattern = '/(<[^>]+>)/';
		// explodes the string
		$a = preg_split($tagpattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		// count elements
		$maxel = count($a);
		$elkey = 0;
		$thead = false; // true when we are inside the THEAD tag
		$key = 1;
		$level = array();
		array_push($level, 0); // root
		while ($elkey < $maxel) {
			$dom[$key] = array();
			$element = $a[$elkey];
			$dom[$key]['elkey'] = $elkey;
			if (preg_match($tagpattern, $element)) {
				// html tag
				$element = substr($element, 1, -1);
				// get tag name
				preg_match('/[\/]?([a-zA-Z0-9]*)/', $element, $tag);
				$tagname = strtolower($tag[1]);
				// check if we are inside a table header
				if ($tagname == 'thead') {
					if ($element[0] == '/') {
						$thead = false;
					} else {
						$thead = true;
					}
					++$elkey;
					continue;
				}
				$dom[$key]['tag'] = true;
				$dom[$key]['value'] = $tagname;
				if (in_array($dom[$key]['value'], $blocktags)) {
					$dom[$key]['block'] = true;
				} else {
					$dom[$key]['block'] = false;
				}
				if ($element[0] == '/') {
					// *** closing html tag
					$dom[$key]['opening'] = false;
					$dom[$key]['parent'] = end($level);
					array_pop($level);
					$dom[$key]['hide'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['hide'];
					$dom[$key]['fontname'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontname'];
					$dom[$key]['fontstyle'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontstyle'];
					$dom[$key]['fontsize'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontsize'];
					$dom[$key]['font-stretch'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['font-stretch'];
					$dom[$key]['letter-spacing'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['letter-spacing'];
					$dom[$key]['stroke'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['stroke'];
					$dom[$key]['fill'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fill'];
					$dom[$key]['clip'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['clip'];
					$dom[$key]['line-height'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['line-height'];
					$dom[$key]['bgcolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['bgcolor'];
					$dom[$key]['fgcolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fgcolor'];
					$dom[$key]['strokecolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['strokecolor'];
					$dom[$key]['align'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['align'];
					$dom[$key]['text-transform'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['text-transform'];
					$dom[$key]['dir'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['dir'];
					if (isset($dom[($dom[($dom[$key]['parent'])]['parent'])]['listtype'])) {
						$dom[$key]['listtype'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['listtype'];
					}
					// set the number of columns in table tag
					if (($dom[$key]['value'] == 'tr') AND (!isset($dom[($dom[($dom[$key]['parent'])]['parent'])]['cols']))) {
						$dom[($dom[($dom[$key]['parent'])]['parent'])]['cols'] = $dom[($dom[$key]['parent'])]['cols'];
					}
					if (($dom[$key]['value'] == 'td') OR ($dom[$key]['value'] == 'th')) {
						$dom[($dom[$key]['parent'])]['content'] = $csstagarray;
						for ($i = ($dom[$key]['parent'] + 1); $i < $key; ++$i) {
							$dom[($dom[$key]['parent'])]['content'] .= stripslashes($a[$dom[$i]['elkey']]);
						}
						$key = $i;
						// mark nested tables
						$dom[($dom[$key]['parent'])]['content'] = str_replace('<table', '<table nested="true"', $dom[($dom[$key]['parent'])]['content']);
						// remove thead sections from nested tables
						$dom[($dom[$key]['parent'])]['content'] = str_replace('<thead>', '', $dom[($dom[$key]['parent'])]['content']);
						$dom[($dom[$key]['parent'])]['content'] = str_replace('</thead>', '', $dom[($dom[$key]['parent'])]['content']);
					}
					// store header rows on a new table
					if (
						($dom[$key]['value'] === 'tr')
						&& !empty($dom[($dom[$key]['parent'])]['thead'])
						&& ($dom[($dom[$key]['parent'])]['thead'] === true)
					) {
						if ($this->empty_string($dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'])) {
							$dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'] = $csstagarray.$a[$dom[($dom[($dom[$key]['parent'])]['parent'])]['elkey']];
						}
						for ($i = $dom[$key]['parent']; $i <= $key; ++$i) {
							$dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'] .= $a[$dom[$i]['elkey']];
						}
						if (!isset($dom[($dom[$key]['parent'])]['attribute'])) {
							$dom[($dom[$key]['parent'])]['attribute'] = array();
						}
						// header elements must be always contained in a single page
						$dom[($dom[$key]['parent'])]['attribute']['nobr'] = 'true';
					}
					if (($dom[$key]['value'] == 'table') AND (!$this->empty_string($dom[($dom[$key]['parent'])]['thead']))) {
						// remove the nobr attributes from the table header
						$dom[($dom[$key]['parent'])]['thead'] = str_replace(' nobr="true"', '', $dom[($dom[$key]['parent'])]['thead']);
						$dom[($dom[$key]['parent'])]['thead'] .= '</tablehead>';
					}
				} else {
					// *** opening or self-closing html tag
					$dom[$key]['opening'] = true;
					$dom[$key]['parent'] = end($level);
					if ((substr($element, -1, 1) == '/') OR (in_array($dom[$key]['value'], $selfclosingtags))) {
						// self-closing tag
						$dom[$key]['self'] = true;
					} else {
						// opening tag
						array_push($level, $key);
						$dom[$key]['self'] = false;
					}
					// copy some values from parent
					$parentkey = 0;
					if ($key > 0) {
						$parentkey = $dom[$key]['parent'];
						$dom[$key]['hide'] = $dom[$parentkey]['hide'];
						$dom[$key]['fontname'] = $dom[$parentkey]['fontname'];
						$dom[$key]['fontstyle'] = $dom[$parentkey]['fontstyle'];
						$dom[$key]['fontsize'] = $dom[$parentkey]['fontsize'];
						$dom[$key]['font-stretch'] = $dom[$parentkey]['font-stretch'];
						$dom[$key]['letter-spacing'] = $dom[$parentkey]['letter-spacing'];
						$dom[$key]['stroke'] = $dom[$parentkey]['stroke'];
						$dom[$key]['fill'] = $dom[$parentkey]['fill'];
						$dom[$key]['clip'] = $dom[$parentkey]['clip'];
						$dom[$key]['line-height'] = $dom[$parentkey]['line-height'];
						$dom[$key]['bgcolor'] = $dom[$parentkey]['bgcolor'];
						$dom[$key]['fgcolor'] = $dom[$parentkey]['fgcolor'];
						$dom[$key]['strokecolor'] = $dom[$parentkey]['strokecolor'];
						$dom[$key]['align'] = $dom[$parentkey]['align'];
						$dom[$key]['listtype'] = $dom[$parentkey]['listtype'];
						$dom[$key]['text-indent'] = $dom[$parentkey]['text-indent'];
						$dom[$key]['text-transform'] = $dom[$parentkey]['text-transform'];
						$dom[$key]['border'] = array();
						$dom[$key]['dir'] = $dom[$parentkey]['dir'];
					}
					// get attributes
					preg_match_all('/([^=\s]*)[\s]*=[\s]*"([^"]*)"/', $element, $attr_array, PREG_PATTERN_ORDER);
					$dom[$key]['attribute'] = array(); // reset attribute array
                    foreach($attr_array[1] as $id => $name) {
                        $dom[$key]['attribute'][strtolower($name)] = $attr_array[2][$id];
                    }
					if (!empty($css)) {
						// merge CSS style to current style
						list($dom[$key]['csssel'], $dom[$key]['cssdata']) = $this->getCSSdataArray($dom, $key, $css);
						$dom[$key]['attribute']['style'] = $this->getTagStyleFromCSSarray($dom[$key]['cssdata']);
					}
					// split style attributes
					if (isset($dom[$key]['attribute']['style']) AND !empty($dom[$key]['attribute']['style'])) {
						// get style attributes
						preg_match_all('/([^;:\s]*):([^;]*)/', $dom[$key]['attribute']['style'], $style_array, PREG_PATTERN_ORDER);
						$dom[$key]['style'] = array(); // reset style attribute array
                        foreach($style_array[1] as $id => $name) {
                            // in case of duplicate attribute the last replace the previous
                            $dom[$key]['style'][strtolower($name)] = trim($style_array[2][$id]);
                        }
						// --- get some style attributes ---
						// text direction
						if (isset($dom[$key]['style']['direction'])) {
							$dom[$key]['dir'] = $dom[$key]['style']['direction'];
						}
						// display
						if (isset($dom[$key]['style']['display'])) {
							$dom[$key]['hide'] = (trim(strtolower($dom[$key]['style']['display'])) == 'none');
						}
						// font family
						if (isset($dom[$key]['style']['font-family'])) {
							$dom[$key]['fontname'] = $this->getFontFamilyName($dom[$key]['style']['font-family']);
						}
						// list-style-type
						if (isset($dom[$key]['style']['list-style-type'])) {
							$dom[$key]['listtype'] = trim(strtolower($dom[$key]['style']['list-style-type']));
							if ($dom[$key]['listtype'] == 'inherit') {
								$dom[$key]['listtype'] = $dom[$parentkey]['listtype'];
							}
						}
						// text-indent
						if (isset($dom[$key]['style']['text-indent'])) {
							$dom[$key]['text-indent'] = $this->getHTMLUnitToUnits($dom[$key]['style']['text-indent']);
							if ($dom[$key]['text-indent'] == 'inherit') {
								$dom[$key]['text-indent'] = $dom[$parentkey]['text-indent'];
							}
						}
						// text-transform
						if (isset($dom[$key]['style']['text-transform'])) {
							$dom[$key]['text-transform'] = $dom[$key]['style']['text-transform'];
						}
						// font size
						if (isset($dom[$key]['style']['font-size'])) {
							$fsize = trim($dom[$key]['style']['font-size']);
							$dom[$key]['fontsize'] = $this->getHTMLFontUnits($fsize, $dom[0]['fontsize'], $dom[$parentkey]['fontsize'], 'pt');
						}
						// font-stretch
						if (isset($dom[$key]['style']['font-stretch'])) {
							$dom[$key]['font-stretch'] = $this->getCSSFontStretching($dom[$key]['style']['font-stretch'], $dom[$parentkey]['font-stretch']);
						}
						// letter-spacing
						if (isset($dom[$key]['style']['letter-spacing'])) {
							$dom[$key]['letter-spacing'] = $this->getCSSFontSpacing($dom[$key]['style']['letter-spacing'], $dom[$parentkey]['letter-spacing']);
						}
						// line-height (internally is the cell height ratio)
						if (isset($dom[$key]['style']['line-height'])) {
							$lineheight = trim($dom[$key]['style']['line-height']);
							switch ($lineheight) {
								// A normal line height. This is default
								case 'normal': {
									$dom[$key]['line-height'] = $dom[0]['line-height'];
									break;
								}
								case 'inherit': {
									$dom[$key]['line-height'] = $dom[$parentkey]['line-height'];
								}
								default: {
									if (is_numeric($lineheight)) {
										// convert to percentage of font height
										$lineheight = ($lineheight * 100).'%';
									}
									$dom[$key]['line-height'] = $this->getHTMLUnitToUnits($lineheight, 1, '%', true);
									if (substr($lineheight, -1) !== '%') {
										if ($dom[$key]['fontsize'] <= 0) {
											$dom[$key]['line-height'] = 1;
										} else {
											$dom[$key]['line-height'] = (($dom[$key]['line-height'] - $this->cell_padding['T'] - $this->cell_padding['B']) / $dom[$key]['fontsize']);
										}
									}
								}
							}
						}
						// font style
						if (isset($dom[$key]['style']['font-weight'])) {
							if (strtolower($dom[$key]['style']['font-weight'][0]) == 'n') {
								if (strpos($dom[$key]['fontstyle'], 'B') !== false) {
									$dom[$key]['fontstyle'] = str_replace('B', '', $dom[$key]['fontstyle']);
								}
							} elseif (strtolower($dom[$key]['style']['font-weight'][0]) == 'b') {
								$dom[$key]['fontstyle'] .= 'B';
							}
						}
						if (isset($dom[$key]['style']['font-style']) AND (strtolower($dom[$key]['style']['font-style'][0]) == 'i')) {
							$dom[$key]['fontstyle'] .= 'I';
						}
						// font color
						if (isset($dom[$key]['style']['color']) AND (!$this->empty_string($dom[$key]['style']['color']))) {
							$dom[$key]['fgcolor'] = $this->convertHTMLColorToDec($dom[$key]['style']['color'], $this->spot_colors);
						} elseif ($dom[$key]['value'] == 'a') {
							$dom[$key]['fgcolor'] = $this->htmlLinkColorArray;
						}
						// background color
						if (isset($dom[$key]['style']['background-color']) AND (!$this->empty_string($dom[$key]['style']['background-color']))) {
							$dom[$key]['bgcolor'] = $this->convertHTMLColorToDec($dom[$key]['style']['background-color'], $this->spot_colors);
						}
						// text-decoration
						if (isset($dom[$key]['style']['text-decoration'])) {
							$decors = explode(' ', strtolower($dom[$key]['style']['text-decoration']));
							foreach ($decors as $dec) {
								$dec = trim($dec);
								if (!$this->empty_string($dec)) {
									if ($dec[0] == 'u') {
										// underline
										$dom[$key]['fontstyle'] .= 'U';
									} elseif ($dec[0] == 'l') {
										// line-through
										$dom[$key]['fontstyle'] .= 'D';
									} elseif ($dec[0] == 'o') {
										// overline
										$dom[$key]['fontstyle'] .= 'O';
									}
								}
							}
						} elseif ($dom[$key]['value'] == 'a') {
							$dom[$key]['fontstyle'] = $this->htmlLinkFontStyle;
						}
						// check for width attribute
						if (isset($dom[$key]['style']['width'])) {
							$dom[$key]['width'] = $dom[$key]['style']['width'];
						}
						// check for height attribute
						if (isset($dom[$key]['style']['height'])) {
							$dom[$key]['height'] = $dom[$key]['style']['height'];
						}
						// check for text alignment
						if (isset($dom[$key]['style']['text-align'])) {
							$dom[$key]['align'] = strtoupper($dom[$key]['style']['text-align'][0]);
						}
						// check for CSS border properties
						if (isset($dom[$key]['style']['border'])) {
							$borderstyle = $this->getCSSBorderStyle($dom[$key]['style']['border']);
							if (!empty($borderstyle)) {
								$dom[$key]['border']['LTRB'] = $borderstyle;
							}
						}
						if (isset($dom[$key]['style']['border-color'])) {
							$brd_colors = preg_split('/[\s]+/', trim($dom[$key]['style']['border-color']));
							if (isset($brd_colors[3])) {
								$dom[$key]['border']['L']['color'] = $this->convertHTMLColorToDec($brd_colors[3], $this->spot_colors);
							}
							if (isset($brd_colors[1])) {
								$dom[$key]['border']['R']['color'] = $this->convertHTMLColorToDec($brd_colors[1], $this->spot_colors);
							}
							if (isset($brd_colors[0])) {
								$dom[$key]['border']['T']['color'] = $this->convertHTMLColorToDec($brd_colors[0], $this->spot_colors);
							}
							if (isset($brd_colors[2])) {
								$dom[$key]['border']['B']['color'] = $this->convertHTMLColorToDec($brd_colors[2], $this->spot_colors);
							}
						}
						if (isset($dom[$key]['style']['border-width'])) {
							$brd_widths = preg_split('/[\s]+/', trim($dom[$key]['style']['border-width']));
							if (isset($brd_widths[3])) {
								$dom[$key]['border']['L']['width'] = $this->getCSSBorderWidth($brd_widths[3]);
							}
							if (isset($brd_widths[1])) {
								$dom[$key]['border']['R']['width'] = $this->getCSSBorderWidth($brd_widths[1]);
							}
							if (isset($brd_widths[0])) {
								$dom[$key]['border']['T']['width'] = $this->getCSSBorderWidth($brd_widths[0]);
							}
							if (isset($brd_widths[2])) {
								$dom[$key]['border']['B']['width'] = $this->getCSSBorderWidth($brd_widths[2]);
							}
						}
						if (isset($dom[$key]['style']['border-style'])) {
							$brd_styles = preg_split('/[\s]+/', trim($dom[$key]['style']['border-style']));
							if (isset($brd_styles[3]) AND ($brd_styles[3]!='none')) {
								$dom[$key]['border']['L']['cap'] = 'square';
								$dom[$key]['border']['L']['join'] = 'miter';
								$dom[$key]['border']['L']['dash'] = $this->getCSSBorderDashStyle($brd_styles[3]);
								if ($dom[$key]['border']['L']['dash'] < 0) {
									$dom[$key]['border']['L'] = array();
								}
							}
							if (isset($brd_styles[1])) {
								$dom[$key]['border']['R']['cap'] = 'square';
								$dom[$key]['border']['R']['join'] = 'miter';
								$dom[$key]['border']['R']['dash'] = $this->getCSSBorderDashStyle($brd_styles[1]);
								if ($dom[$key]['border']['R']['dash'] < 0) {
									$dom[$key]['border']['R'] = array();
								}
							}
							if (isset($brd_styles[0])) {
								$dom[$key]['border']['T']['cap'] = 'square';
								$dom[$key]['border']['T']['join'] = 'miter';
								$dom[$key]['border']['T']['dash'] = $this->getCSSBorderDashStyle($brd_styles[0]);
								if ($dom[$key]['border']['T']['dash'] < 0) {
									$dom[$key]['border']['T'] = array();
								}
							}
							if (isset($brd_styles[2])) {
								$dom[$key]['border']['B']['cap'] = 'square';
								$dom[$key]['border']['B']['join'] = 'miter';
								$dom[$key]['border']['B']['dash'] = $this->getCSSBorderDashStyle($brd_styles[2]);
								if ($dom[$key]['border']['B']['dash'] < 0) {
									$dom[$key]['border']['B'] = array();
								}
							}
						}
						$cellside = array('L' => 'left', 'R' => 'right', 'T' => 'top', 'B' => 'bottom');
						foreach ($cellside as $bsk => $bsv) {
							if (isset($dom[$key]['style']['border-'.$bsv])) {
								$borderstyle = $this->getCSSBorderStyle($dom[$key]['style']['border-'.$bsv]);
								if (!empty($borderstyle)) {
									$dom[$key]['border'][$bsk] = $borderstyle;
								}
							}
							if (isset($dom[$key]['style']['border-'.$bsv.'-color'])) {
								$dom[$key]['border'][$bsk]['color'] = $this->convertHTMLColorToDec($dom[$key]['style']['border-'.$bsv.'-color'], $this->spot_colors);
							}
							if (isset($dom[$key]['style']['border-'.$bsv.'-width'])) {
								$dom[$key]['border'][$bsk]['width'] = $this->getCSSBorderWidth($dom[$key]['style']['border-'.$bsv.'-width']);
							}
							if (isset($dom[$key]['style']['border-'.$bsv.'-style'])) {
								$dom[$key]['border'][$bsk]['dash'] = $this->getCSSBorderDashStyle($dom[$key]['style']['border-'.$bsv.'-style']);
								if ($dom[$key]['border'][$bsk]['dash'] < 0) {
									$dom[$key]['border'][$bsk] = array();
								}
							}
						}
						// check for CSS padding properties
						if (isset($dom[$key]['style']['padding'])) {
							$dom[$key]['padding'] = $this->getCSSPadding($dom[$key]['style']['padding']);
						} else {
							$dom[$key]['padding'] = $this->cell_padding;
						}
						foreach ($cellside as $psk => $psv) {
							if (isset($dom[$key]['style']['padding-'.$psv])) {
								$dom[$key]['padding'][$psk] = $this->getHTMLUnitToUnits($dom[$key]['style']['padding-'.$psv], 0, 'px', false);
							}
						}
						// check for CSS margin properties
						if (isset($dom[$key]['style']['margin'])) {
							$dom[$key]['margin'] = $this->getCSSMargin($dom[$key]['style']['margin']);
						} else {
							$dom[$key]['margin'] = $this->cell_margin;
						}
						foreach ($cellside as $psk => $psv) {
							if (isset($dom[$key]['style']['margin-'.$psv])) {
								$dom[$key]['margin'][$psk] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $dom[$key]['style']['margin-'.$psv]), 0, 'px', false);
							}
						}
						// check for CSS border-spacing properties
						if (isset($dom[$key]['style']['border-spacing'])) {
							$dom[$key]['border-spacing'] = $this->getCSSBorderMargin($dom[$key]['style']['border-spacing']);
						}
						// page-break-inside
						if (isset($dom[$key]['style']['page-break-inside']) AND ($dom[$key]['style']['page-break-inside'] == 'avoid')) {
							$dom[$key]['attribute']['nobr'] = 'true';
						}
						// page-break-before
						if (isset($dom[$key]['style']['page-break-before'])) {
							if ($dom[$key]['style']['page-break-before'] == 'always') {
								$dom[$key]['attribute']['pagebreak'] = 'true';
							} elseif ($dom[$key]['style']['page-break-before'] == 'left') {
								$dom[$key]['attribute']['pagebreak'] = 'left';
							} elseif ($dom[$key]['style']['page-break-before'] == 'right') {
								$dom[$key]['attribute']['pagebreak'] = 'right';
							}
						}
						// page-break-after
						if (isset($dom[$key]['style']['page-break-after'])) {
							if ($dom[$key]['style']['page-break-after'] == 'always') {
								$dom[$key]['attribute']['pagebreakafter'] = 'true';
							} elseif ($dom[$key]['style']['page-break-after'] == 'left') {
								$dom[$key]['attribute']['pagebreakafter'] = 'left';
							} elseif ($dom[$key]['style']['page-break-after'] == 'right') {
								$dom[$key]['attribute']['pagebreakafter'] = 'right';
							}
						}
					}
					if (isset($dom[$key]['attribute']['display'])) {
						$dom[$key]['hide'] = (trim(strtolower($dom[$key]['attribute']['display'])) == 'none');
					}
					if (isset($dom[$key]['attribute']['border']) AND ($dom[$key]['attribute']['border'] != 0)) {
						$borderstyle = $this->getCSSBorderStyle($dom[$key]['attribute']['border'].' solid black');
						if (!empty($borderstyle)) {
							$dom[$key]['border']['LTRB'] = $borderstyle;
						}
					}
					// check for font tag
					if ($dom[$key]['value'] == 'font') {
						// font family
						if (isset($dom[$key]['attribute']['face'])) {
							$dom[$key]['fontname'] = $this->getFontFamilyName($dom[$key]['attribute']['face']);
						}
						// font size
						if (isset($dom[$key]['attribute']['size'])) {
							if ($key > 0) {
								if ($dom[$key]['attribute']['size'][0] == '+') {
									$dom[$key]['fontsize'] = $dom[($dom[$key]['parent'])]['fontsize'] + intval(substr($dom[$key]['attribute']['size'], 1));
								} elseif ($dom[$key]['attribute']['size'][0] == '-') {
									$dom[$key]['fontsize'] = $dom[($dom[$key]['parent'])]['fontsize'] - intval(substr($dom[$key]['attribute']['size'], 1));
								} else {
									$dom[$key]['fontsize'] = intval($dom[$key]['attribute']['size']);
								}
							} else {
								$dom[$key]['fontsize'] = intval($dom[$key]['attribute']['size']);
							}
						}
					}
					// force natural alignment for lists
					if ((($dom[$key]['value'] == 'ul') OR ($dom[$key]['value'] == 'ol') OR ($dom[$key]['value'] == 'dl'))
						AND (!isset($dom[$key]['align']) OR $this->empty_string($dom[$key]['align']) OR ($dom[$key]['align'] != 'J'))) {
						if ($this->rtl) {
							$dom[$key]['align'] = 'R';
						} else {
							$dom[$key]['align'] = 'L';
						}
					}
					if (($dom[$key]['value'] == 'small') OR ($dom[$key]['value'] == 'sup') OR ($dom[$key]['value'] == 'sub')) {
						if (!isset($dom[$key]['attribute']['size']) AND !isset($dom[$key]['style']['font-size'])) {
							$dom[$key]['fontsize'] = $dom[$key]['fontsize'] * K_SMALL_RATIO;
						}
					}
					if (($dom[$key]['value'] == 'strong') OR ($dom[$key]['value'] == 'b')) {
						$dom[$key]['fontstyle'] .= 'B';
					}
					if (($dom[$key]['value'] == 'em') OR ($dom[$key]['value'] == 'i')) {
						$dom[$key]['fontstyle'] .= 'I';
					}
					if ($dom[$key]['value'] == 'u') {
						$dom[$key]['fontstyle'] .= 'U';
					}
					if (($dom[$key]['value'] == 'del') OR ($dom[$key]['value'] == 's') OR ($dom[$key]['value'] == 'strike')) {
						$dom[$key]['fontstyle'] .= 'D';
					}
					if (!isset($dom[$key]['style']['text-decoration']) AND ($dom[$key]['value'] == 'a')) {
						$dom[$key]['fontstyle'] = $this->htmlLinkFontStyle;
					}
					if (($dom[$key]['value'] == 'pre') OR ($dom[$key]['value'] == 'tt')) {
						$dom[$key]['fontname'] = $this->default_monospaced_font;
					}
					if (!empty($dom[$key]['value']) AND ($dom[$key]['value'][0] == 'h') AND (intval($dom[$key]['value'][1]) > 0) AND (intval($dom[$key]['value'][1]) < 7)) {
						// headings h1, h2, h3, h4, h5, h6
						if (!isset($dom[$key]['attribute']['size']) AND !isset($dom[$key]['style']['font-size'])) {
							$headsize = (4 - intval($dom[$key]['value'][1])) * 2;
							$dom[$key]['fontsize'] = $dom[0]['fontsize'] + $headsize;
						}
						if (!isset($dom[$key]['style']['font-weight'])) {
							$dom[$key]['fontstyle'] .= 'B';
						}
					}
					if (($dom[$key]['value'] == 'table')) {
						$dom[$key]['rows'] = 0; // number of rows
						$dom[$key]['trids'] = array(); // IDs of TR elements
						$dom[$key]['thead'] = ''; // table header rows
					}
					if (($dom[$key]['value'] == 'tr')) {
						$dom[$key]['cols'] = 0;
						if ($thead) {
							$dom[$key]['thead'] = true;
							// rows on thead block are printed as a separate table
						} else {
							$dom[$key]['thead'] = false;
							$parent = $dom[$key]['parent'];

							if (!isset($dom[$parent]['rows'])) {
								$dom[$parent]['rows'] = 0;
							}
							// store the number of rows on table element
							++$dom[$parent]['rows'];

							if (!isset($dom[$parent]['trids'])) {
								$dom[$parent]['trids'] = array();
							}

							// store the TR elements IDs on table element
							array_push($dom[$parent]['trids'], $key);
						}
					}
					if (($dom[$key]['value'] == 'th') OR ($dom[$key]['value'] == 'td')) {
						if (isset($dom[$key]['attribute']['colspan'])) {
							$colspan = intval($dom[$key]['attribute']['colspan']);
						} else {
							$colspan = 1;
						}
						$dom[$key]['attribute']['colspan'] = $colspan;
						$dom[($dom[$key]['parent'])]['cols'] += $colspan;
					}
					// text direction
					if (isset($dom[$key]['attribute']['dir'])) {
						$dom[$key]['dir'] = $dom[$key]['attribute']['dir'];
					}
					// set foreground color attribute
					if (isset($dom[$key]['attribute']['color']) AND (!$this->empty_string($dom[$key]['attribute']['color']))) {
						$dom[$key]['fgcolor'] = $this->convertHTMLColorToDec($dom[$key]['attribute']['color'], $this->spot_colors);
					} elseif (!isset($dom[$key]['style']['color']) AND ($dom[$key]['value'] == 'a')) {
						$dom[$key]['fgcolor'] = $this->htmlLinkColorArray;
					}
					// set background color attribute
					if (isset($dom[$key]['attribute']['bgcolor']) AND (!$this->empty_string($dom[$key]['attribute']['bgcolor']))) {
						$dom[$key]['bgcolor'] = $this->convertHTMLColorToDec($dom[$key]['attribute']['bgcolor'], $this->spot_colors);
					}
					// set stroke color attribute
					if (isset($dom[$key]['attribute']['strokecolor']) AND (!$this->empty_string($dom[$key]['attribute']['strokecolor']))) {
						$dom[$key]['strokecolor'] = $this->convertHTMLColorToDec($dom[$key]['attribute']['strokecolor'], $this->spot_colors);
					}
					// check for width attribute
					if (isset($dom[$key]['attribute']['width'])) {
						$dom[$key]['width'] = $dom[$key]['attribute']['width'];
					}
					// check for height attribute
					if (isset($dom[$key]['attribute']['height'])) {
						$dom[$key]['height'] = $dom[$key]['attribute']['height'];
					}
					// check for text alignment
					if (isset($dom[$key]['attribute']['align']) AND (!$this->empty_string($dom[$key]['attribute']['align'])) AND ($dom[$key]['value'] !== 'img')) {
						$dom[$key]['align'] = strtoupper($dom[$key]['attribute']['align'][0]);
					}
					// check for text rendering mode (the following attributes do not exist in HTML)
					if (isset($dom[$key]['attribute']['stroke'])) {
						// font stroke width
						$dom[$key]['stroke'] = $this->getHTMLUnitToUnits($dom[$key]['attribute']['stroke'], $dom[$key]['fontsize'], 'pt', true);
					}
					if (isset($dom[$key]['attribute']['fill'])) {
						// font fill
						if ($dom[$key]['attribute']['fill'] == 'true') {
							$dom[$key]['fill'] = true;
						} else {
							$dom[$key]['fill'] = false;
						}
					}
					if (isset($dom[$key]['attribute']['clip'])) {
						// clipping mode
						if ($dom[$key]['attribute']['clip'] == 'true') {
							$dom[$key]['clip'] = true;
						} else {
							$dom[$key]['clip'] = false;
						}
					}
				} // end opening tag
			} else {
				// text
				$dom[$key]['tag'] = false;
				$dom[$key]['block'] = false;
				$dom[$key]['parent'] = end($level);
				$dom[$key]['dir'] = $dom[$dom[$key]['parent']]['dir'];
				if (!empty($dom[$dom[$key]['parent']]['text-transform'])) {
					// text-transform for unicode requires mb_convert_case (Multibyte String Functions)
					if (function_exists('mb_convert_case')) {
						$ttm = array('capitalize' => MB_CASE_TITLE, 'uppercase' => MB_CASE_UPPER, 'lowercase' => MB_CASE_LOWER);
						if (isset($ttm[$dom[$dom[$key]['parent']]['text-transform']])) {
							$element = mb_convert_case($element, $ttm[$dom[$dom[$key]['parent']]['text-transform']], $this->encoding);
						}
					} elseif (!$this->isunicode) {
						switch ($dom[$dom[$key]['parent']]['text-transform']) {
							case 'capitalize': {
								$element = ucwords(strtolower($element));
								break;
							}
							case 'uppercase': {
								$element = strtoupper($element);
								break;
							}
							case 'lowercase': {
								$element = strtolower($element);
								break;
							}
						}
					}
					$element = preg_replace("/&NBSP;/i", "&nbsp;", $element);
				}
				$dom[$key]['value'] = stripslashes($this->unhtmlentities($element));
			}
			++$elkey;
			++$key;
		}
		return $dom;
	}

    /**
	 * Set the color and font style for HTML links.
	 * @param array $color RGB array of colors
	 * @param string $fontstyle additional font styles to add
	 * @public
	 * @since 4.4.003 (2008-12-09)
	 */
	public function setHtmlLinksStyle($color=array(0,0,255), $fontstyle='U') {
		$this->htmlLinkColorArray = $color;
		$this->htmlLinkFontStyle = $fontstyle;
	}

	/**
	 * Convert HTML string containing value and unit of measure to user's units or points.
	 * @param string $htmlval String containing values and unit.
	 * @param string $refsize Reference value in points.
	 * @param string $defaultunit Default unit (can be one of the following: %, em, ex, px, in, mm, pc, pt).
	 * @param boolean $points If true returns points, otherwise returns value in user's units.
	 * @return float value in user's unit or point if $points=true
	 * @public
	 * @since 4.4.004 (2008-12-10)
	 */
	public function getHTMLUnitToUnits($htmlval, $refsize=1, $defaultunit='px', $points=false) {
		$supportedunits = array('%', 'em', 'ex', 'px', 'in', 'cm', 'mm', 'pc', 'pt');
		$retval = 0;
		$value = 0;
		$unit = 'px';
		if ($points) {
			$k = 1;
		} else {
			$k = $this->k;
		}
		if (in_array($defaultunit, $supportedunits)) {
			$unit = $defaultunit;
		}
		if (is_numeric($htmlval)) {
			$value = floatval($htmlval);
		} elseif (preg_match('/([0-9\.\-\+]+)/', $htmlval, $mnum)) {
			$value = floatval($mnum[1]);
			if (preg_match('/([a-z%]+)/', $htmlval, $munit)) {
				if (in_array($munit[1], $supportedunits)) {
					$unit = $munit[1];
				}
			}
		}
		switch ($unit) {
			// percentage
			case '%': {
				$retval = (($value * $refsize) / 100);
				break;
			}
			// relative-size
			case 'em': {
				$retval = ($value * $refsize);
				break;
			}
			// height of lower case 'x' (about half the font-size)
			case 'ex': {
				$retval = ($value * ($refsize / 2));
				break;
			}
			// absolute-size
			case 'in': {
				$retval = (($value * $this->dpi) / $k);
				break;
			}
			// centimeters
			case 'cm': {
				$retval = (($value / 2.54 * $this->dpi) / $k);
				break;
			}
			// millimeters
			case 'mm': {
				$retval = (($value / 25.4 * $this->dpi) / $k);
				break;
			}
			// one pica is 12 points
			case 'pc': {
				$retval = (($value * 12) / $k);
				break;
			}
			// points
			case 'pt': {
				$retval = ($value / $k);
				break;
			}
			// pixels
			case 'px': {
				$retval = $this->pixelsToUnits($value);
				if ($points) {
					$retval *= $this->k;
				}
				break;
			}
		}
		return $retval;
	}

    /**
	 * Set the vertical spaces for HTML tags.
	 * The array must have the following structure (example):
	 * $tagvs = array('h1' => array(0 => array('h' => '', 'n' => 2), 1 => array('h' => 1.3, 'n' => 1)));
	 * The first array level contains the tag names,
	 * the second level contains 0 for opening tags or 1 for closing tags,
	 * the third level contains the vertical space unit (h) and the number spaces to add (n).
	 * If the h parameter is not specified, default values are used.
	 * @param array $tagvs array of tags and relative vertical spaces.
	 * @public
	 * @since 4.2.001 (2008-10-30)
	 */
	public function setHtmlVSpace($tagvs) {
		$this->tagvspaces = $tagvs;
	}

	/**
	 * Set the top/bottom cell sides to be open or closed when the cell cross the page.
	 * @param boolean $isopen if true keeps the top/bottom border open for the cell sides that cross the page.
	 * @public
	 * @since 4.2.010 (2008-11-14)
	 */
	public function setOpenCell($isopen) {
		$this->opencell = $isopen;
	}

	/**
	 * Defines the page and position a link points to.
	 * @param int $link The link identifier returned by AddLink()
	 * @param float $y Ordinate of target position; -1 indicates the current position. The default value is 0 (top of page)
	 * @param int|string $page Number of target page; -1 indicates the current page (default value). If you prefix a page number with the * character, then this page will not be changed when adding/deleting/moving pages.
	 * @public
	 * @since 1.5
	 * @see AddLink()
	 */
	public function setLink($link, $y=0, $page=-1) {
		$fixed = false;
		if (!empty($page) AND (substr($page, 0, 1) == '*')) {
			$page = intval(substr($page, 1));
			// this page number will not be changed when moving/add/deleting pages
			$fixed = true;
		}
		if ($page < 0) {
			$page = $this->page;
		}
		if ($y == -1) {
			$y = $this->y;
		}
		$this->links[$link] = array('p' => $page, 'y' => $y, 'f' => $fixed);
	}
}