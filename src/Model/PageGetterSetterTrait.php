<?php

namespace LimePDF\Model;

use LimePDF\Include\FontTrait;
use LimePDF\Support\StaticTrait;

trait PageGetterSetterTrait {

	/**
	 * Defines the title of the document.
	 * @param string $title The title.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetCreator(), SetKeywords(), SetSubject()
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Defines the subject of the document.
	 * @param string $subject The subject.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetCreator(), SetKeywords(), SetTitle()
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
	}

	/**
	 * Defines the author of the document.
	 * @param string $author The name of the author.
	 * @public
	 * @since 1.2
	 * @see SetCreator(), SetKeywords(), SetSubject(), SetTitle()
	 */
	public function setAuthor($author) {
		$this->author = $author;
	}

	/**
	 * Associates keywords with the document, generally in the form 'keyword1 keyword2 ...'.
	 * @param string $keywords The list of keywords.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetCreator(), SetSubject(), SetTitle()
	 */
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}

	/**
	 * Defines the creator of the document. This is typically the name of the application that generates the PDF.
	 * @param string $creator The name of the creator.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetKeywords(), SetSubject(), SetTitle()
	 */
	public function setCreator($creator) {
		$this->creator = $creator;
	}

	/**
	 * Whether to allow local file path in image html tags, when prefixed with file://
	 *
	 * @param bool $allowLocalFiles true, when local files should be allowed. Otherwise false.
	 * @public
	 * @since 6.4
	 */
	public function setAllowLocalFiles($allowLocalFiles) {
		$this->allowLocalFiles = (bool) $allowLocalFiles;
	}

	/**
	 * Set header data.
	 * @param string $ln header image logo
	 * @param int $lw header image logo width in mm
	 * @param string $ht string to print as title on document header
	 * @param string $hs string to print on document header
	 * @param int[] $tc RGB array color for text.
	 * @param int[] $lc RGB array color for line.
	 * @public
	 */
	public function setHeaderData($ln='', $lw=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0)) {
		$this->header_logo = $ln;
		$this->header_logo_width = $lw;
		$this->header_title = $ht;
		$this->header_string = $hs;
		$this->header_text_color = $tc;
		$this->header_line_color = $lc;
	}

	/**
	 * Set footer data.
	 * @param int[] $tc RGB array color for text.
	 * @param int[] $lc RGB array color for line.
	 * @public
	 */
	public function setFooterData($tc=array(0,0,0), $lc=array(0,0,0)) {
		$this->footer_text_color = $tc;
		$this->footer_line_color = $lc;
	}

	/**
	 * Returns header data:
	 * <ul><li>$ret['logo'] = logo image</li><li>$ret['logo_width'] = width of the image logo in user units</li><li>$ret['title'] = header title</li><li>$ret['string'] = header description string</li></ul>
	 * @return array<string,mixed>
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getHeaderData() {
		$ret = array();
		$ret['logo'] = $this->header_logo;
		$ret['logo_width'] = $this->header_logo_width;
		$ret['title'] = $this->header_title;
		$ret['string'] = $this->header_string;
		$ret['text_color'] = $this->header_text_color;
		$ret['line_color'] = $this->header_line_color;
		return $ret;
	}

	/**
	 * Set header margin.
	 * (minimum distance between header and top page margin)
	 * @param float $hm distance in user units
	 * @public
	 */
	public function setHeaderMargin($hm=10) {
		$this->header_margin = $hm;
	}

	/**
	 * Returns header margin in user units.
	 * @return float
	 * @since 4.0.012 (2008-07-24)
	 * @public
	 */
	public function getHeaderMargin() {
		return $this->header_margin;
	}

	/**
	 * Set footer margin.
	 * (minimum distance between footer and bottom page margin)
	 * @param float $fm distance in user units
	 * @public
	 */
	public function setFooterMargin($fm=10) {
		$this->footer_margin = $fm;
	}

	/**
	 * Returns footer margin in user units.
	 * @return float
	 * @since 4.0.012 (2008-07-24)
	 * @public
	 */
	public function getFooterMargin() {
		return $this->footer_margin;
	}
	/**
	 * Set a flag to print page header.
	 * @param boolean $val set to true to print the page header (default), false otherwise.
	 * @public
	 */
	public function setPrintHeader($val=true) {
		$this->print_header = $val ? true : false;
	}

	/**
	 * Set a flag to print page footer.
	 * @param boolean $val set to true to print the page footer (default), false otherwise.
	 * @public
	 */
	public function setPrintFooter($val=true) {
		$this->print_footer = $val ? true : false;
	}

	/**
	 * Return the right-bottom (or left-bottom for RTL) corner X coordinate of last inserted image
	 * @return float
	 * @public
	 */
	public function getImageRBX() {
		return $this->img_rb_x;
	}

	/**
	 * Return the right-bottom (or left-bottom for RTL) corner Y coordinate of last inserted image
	 * @return float
	 * @public
	 */
	public function getImageRBY() {
		return $this->img_rb_y;
	}

	/**
	 * Reset the xobject template used by Header() method.
	 * @public
	 */
	public function resetHeaderTemplate() {
		$this->header_xobjid = false;
	}

	/**
	 * Set a flag to automatically reset the xobject template used by Header() method at each page.
	 * @param boolean $val set to true to reset Header xobject template at each page, false otherwise.
	 * @public
	 */
	public function setHeaderTemplateAutoreset($val=true) {
		$this->header_xobj_autoreset = $val ? true : false;
	}

	/**
	 * Returns the string alias used right align page numbers.
	 * If the current font is unicode type, the returned string wil contain an additional open curly brace.
	 * @return string
	 * @since 5.9.099 (2011-06-27)
	 * @public
	 */
	public function getAliasRightShift() {
		// calculate aproximatively the ratio between widths of aliases and replacements.
		$ref = '{'.self::$alias_right_shift.'}{'.self::$alias_tot_pages.'}{'.self::$alias_num_page.'}';
		$rep = str_repeat(' ', $this->GetNumChars($ref));
		$wrep = $this->GetStringWidth($rep);
		if ($wrep > 0) {
			$wdiff = max(1, ($this->GetStringWidth($ref) / $wrep));
		} else {
			$wdiff = 1;
		}
		$sdiff = sprintf('%F', $wdiff);
		$alias = self::$alias_right_shift.$sdiff.'}';
		if ($this->isUnicodeFont()) {
			$alias = '{'.$alias;
		}
		return $alias;
	}

	/**
	 * Returns the string alias used for the total number of pages.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the total number of pages in the document.
	 * @return string
	 * @since 4.0.018 (2008-08-08)
	 * @public
	 */
	public function getAliasNbPages() {
		if ($this->isUnicodeFont()) {
			return '{'.$this->$alias_tot_pages.'}';
		}
		return self::$alias_tot_pages;
	}

	/**
	 * Returns the string alias used for the page number.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the page number.
	 * @return string
	 * @since 4.5.000 (2009-01-02)
	 * @public
	 */
	public function getAliasNumPage() {
		if ($this->isUnicodeFont()) {
			return '{'.$this->alias_num_page.'}';
		}
		return self::$alias_num_page;
	}

	/**
	 * Return the alias for the total number of pages in the current page group.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the total number of pages in this group.
	 * @return string alias of the current page group
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function getPageGroupAlias() {
		if ($this->isUnicodeFont()) {
			return '{'.$this->$alias_group_tot_pages.'}';
		}
		return $this->$alias_group_tot_pages;
	}

	/**
	 * Return the alias for the page number on the current page group.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the page number (relative to the belonging group).
	 * @return string alias of the current page group
	 * @public
	 * @since 4.5.000 (2009-01-02)
	 */
	public function getPageNumGroupAlias() {
		if ($this->isUnicodeFont()) {
			return '{'.$this->$alias_group_num_page.'}';
		}
		return $this->$alias_group_num_page;
	}

	/**
	 * Return the current page in the group.
	 * @return int current page in the group
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function getGroupPageNo() {
		return $this->pagegroups[$this->currpagegroup];
	}

	/**
	 * Returns the current group page number formatted as a string.
	 * @public
	 * @since 4.3.003 (2008-11-18)
	 * @see PaneNo(), formatPageNumber()
	 */
	public function getGroupPageNoFormatted() {
		return $this->formatPageNumber($this->getGroupPageNo());
	}

	/**
	 * Return an array containing variations for the basic page number alias.
	 * @param string $a Base alias.
	 * @return array of page number aliases
	 * @protected
	 */
	protected function getInternalPageNumberAliases($a= '') {
		$alias = array();
		// build array of Unicode + ASCII variants (the order is important)
		$alias = array('u' => array(), 'a' => array());
		$u = '{'.$a.'}';
		$alias['u'][] = $this->_escape($u);
		if ($this->isunicode) {
			$alias['u'][] = $this->_escape($this->UTF8ToLatin1($u, $this->isunicode, $this->CurrentFont));
			$alias['u'][] = $this->_escape($this->utf8StrRev($u, false, $this->tmprtl, $this->isunicode, $this->CurrentFont));
			$alias['a'][] = $this->_escape($this->UTF8ToLatin1($a, $this->isunicode, $this->CurrentFont));
			$alias['a'][] = $this->_escape($this->utf8StrRev($a, false, $this->tmprtl, $this->isunicode, $this->CurrentFont));
		}
		$alias['a'][] = $this->_escape($a);
		return $alias;
	}

	/**
	 * Return an array containing all internal page aliases.
	 * @return array of page number aliases
	 * @protected
	 */
	protected function getAllInternalPageNumberAliases() {
		$basic_alias = array(self::$alias_tot_pages, self::$alias_num_page, self::$alias_group_tot_pages, self::$alias_group_num_page, self::$alias_right_shift);
		$pnalias = array();
		foreach($basic_alias as $k => $a) {
			$pnalias[$k] = $this->getInternalPageNumberAliases($a);
		}
		return $pnalias;
	}

	/**
	 * Set the starting page number.
	 * @param int $num Starting page number.
	 * @since 5.9.093 (2011-06-16)
	 * @public
	 */
	public function setStartingPageNumber($num=1) {
		$this->starting_page_number = max(0, intval($num));
	}

	/**
	 * Set page buffer content.
	 * @param int $page page number
	 * @param string $data page data
	 * @param boolean $append if true append data, false replace.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function setPageBuffer($page, $data, $append=false) {
		if ($append) {
			$this->pages[$page] .= $data;
		} else {
			$this->pages[$page] = $data;
		}
		if ($append AND isset($this->pagelen[$page])) {
			$this->pagelen[$page] += strlen($data);
		} else {
			$this->pagelen[$page] = strlen($data);
		}
	}

	/**
	 * Get page buffer content.
	 * @param int $page page number
	 * @return string page buffer content or false in case of error
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function getPageBuffer($page) {
		if (isset($this->pages[$page])) {
			return $this->pages[$page];
		}
		return false;
	}

}