<?php

namespace LimePDF\Graphics;

trait ColumnsTrait{

	//----- php 7 & php 8 ------------------------------

	/**
     * Column tracking data.
     * Structure example:
     * [
     *   1 => ['y' => 0, 'pages' => [1 => ['y' => 10, 'm' => 0]], 'nb' => 0]
     * ]
     */


    /**
     * Add a column to the document
     */
    public function addColumn(int $col, float $y = 0.0): void
    {
        if (isset($this->columns[$col])) {
            return;
        }

        if (\LimePDF\LIMEPDF_STATIC::empty_string($y)) {
            $y = $this->y;
        }

        $this->columns[$col] = [
            'y'     => $y,
            'pages' => [$this->page => ['y' => $y, 'm' => 0]],
            'nb'    => 0
        ];

        $this->current_column = $col;
        $this->num_columns++;
    }

    /**
     * Get the current column number
     */
    public function getCurrentColumn(): int
    {
        return $this->current_column;
    }

    /**
     * Get total number of columns
     */
    public function getNumColumns(): int
    {
        return $this->num_columns;
    }

    /**
     * Get all column data
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Set the table header (used when rendering)
     */
    public function setThead(?string $thead): void
    {
        $this->thead = $thead;
    }

    /**
     * Get the table header
     */
    public function getThead(): ?string
    {
        return $this->thead;
    }

	// --- MULTI COLUMNS METHODS -----------------------

	/**
	 * Set multiple columns of the same size
	 * @param int $numcols number of columns (set to zero to disable columns mode)
	 * @param int $width column width
	 * @param int|null $y column starting Y position (leave empty for current Y position)
	 * @public
	 * @since 4.9.001 (2010-03-28)
	 */
	public function setEqualColumns($numcols=0, $width=0, $y=null) {
		$this->columns = array();
		if ($numcols < 2) {
			$numcols = 0;
			$this->columns = array();
		} else {
			// maximum column width
			$maxwidth = ($this->w - $this->original_lMargin - $this->original_rMargin) / $numcols;
			if (($width == 0) OR ($width > $maxwidth)) {
				$width = $maxwidth;
			}
			if (LIMEPDF_STATIC::empty_string($y)) {
				$y = $this->y;
			}
			// space between columns
			$space = (($this->w - $this->original_lMargin - $this->original_rMargin - ($numcols * $width)) / ($numcols - 1));
			// fill the columns array (with, space, starting Y position)
			for ($i = 0; $i < $numcols; ++$i) {
				$this->columns[$i] = array('w' => $width, 's' => $space, 'y' => $y);
			}
		}
		$this->num_columns = $numcols;
		$this->current_column = 0;
		$this->column_start_page = $this->page;
		$this->selectColumn(0);
	}

	/**
	 * Remove columns and reset page margins.
	 * @public
	 * @since 5.9.072 (2011-04-26)
	 */
	public function resetColumns() {
		$this->lMargin = $this->original_lMargin;
		$this->rMargin = $this->original_rMargin;
		$this->setEqualColumns();
	}

	/**
	 * Set columns array.
	 * Each column is represented by an array of arrays with the following keys: (w = width, s = space between columns, y = column top position).
	 * @param array $columns
	 * @public
	 * @since 4.9.001 (2010-03-28)
	 */
	public function setColumnsArray($columns) {
		$this->columns = $columns;
		$this->num_columns = count($columns);
		$this->current_column = 0;
		$this->column_start_page = $this->page;
		$this->selectColumn(0);
	}

	/**
	 * Set position at a given column
	 * @param int|null $col column number (from 0 to getNumberOfColumns()-1); empty string = current column.
	 * @public
	 * @since 4.9.001 (2010-03-28)
	 */
	public function selectColumn($col=null) {
		if (LIMEPDF_STATIC::empty_string($col)) {
			$col = $this->current_column;
		} elseif ($col >= $this->num_columns) {
			$col = 0;
		}
		$xshift = array('x' => 0, 's' => array('H' => 0, 'V' => 0), 'p' => array('L' => 0, 'T' => 0, 'R' => 0, 'B' => 0));
		$enable_thead = false;
		if ($this->num_columns > 1) {
			if ($col != $this->current_column) {
				// move Y pointer at the top of the column
				if ($this->column_start_page == $this->page) {
					$this->y = $this->columns[$col]['y'];
				} else {
					$this->y = $this->tMargin;
				}
				// Avoid to write table headers more than once
				if (($this->page > $this->maxselcol['page']) OR (($this->page == $this->maxselcol['page']) AND ($col > $this->maxselcol['column']))) {
					$enable_thead = true;
					$this->maxselcol['page'] = $this->page;
					$this->maxselcol['column'] = $col;
				}
			}
			$xshift = $this->colxshift;
			// set X position of the current column by case
			$listindent = ($this->listindentlevel * $this->listindent);
			// calculate column X position
			$colpos = 0;
			for ($i = 0; $i < $col; ++$i) {
				$colpos += ($this->columns[$i]['w'] + $this->columns[$i]['s']);
			}
			if ($this->rtl) {
				$x = $this->w - $this->original_rMargin - $colpos;
				$this->rMargin = ($this->w - $x + $listindent);
				$this->lMargin = ($x - $this->columns[$col]['w']);
				$this->x = $x - $listindent;
			} else {
				$x = $this->original_lMargin + $colpos;
				$this->lMargin = ($x + $listindent);
				$this->rMargin = ($this->w - $x - $this->columns[$col]['w']);
				$this->x = $x + $listindent;
			}
			$this->columns[$col]['x'] = $x;
		}
		$this->current_column = $col;
		// fix for HTML mode
		$this->newline = true;
		// print HTML table header (if any)
		if ((!LIMEPDF_STATIC::empty_string($this->thead)) AND (!$this->inthead)) {
			if ($enable_thead) {
				// print table header
				$this->writeHTML($this->thead, false, false, false, false, '');
				$this->y += $xshift['s']['V'];
				// store end of header position
				if (!isset($this->columns[$col]['th'])) {
					$this->columns[$col]['th'] = array();
				}
				$this->columns[$col]['th']['\''.$this->page.'\''] = $this->y;
				$this->lasth = 0;
			} elseif (isset($this->columns[$col]['th']['\''.$this->page.'\''])) {
				$this->y = $this->columns[$col]['th']['\''.$this->page.'\''];
			}
		}
		// account for an html table cell over multiple columns
		if ($this->rtl) {
			$this->rMargin += $xshift['x'];
			$this->x -= ($xshift['x'] + $xshift['p']['R']);
		} else {
			$this->lMargin += $xshift['x'];
			$this->x += $xshift['x'] + $xshift['p']['L'];
		}
	}

	/**
	 * Return the current column number
	 * @return int current column number
	 * @public
	 * @since 5.5.011 (2010-07-08)
	 */
	public function getColumn() {
		return $this->current_column;
	}

	/**
	 * Return the current number of columns.
	 * @return int number of columns
	 * @public
	 * @since 5.8.018 (2010-08-25)
	 */
	public function getNumberOfColumns() {
		return $this->num_columns;
	}

	/**
	 * Set Text rendering mode.
	 * @param int $stroke outline size in user units (0 = disable).
	 * @param boolean $fill if true fills the text (default).
	 * @param boolean $clip if true activate clipping mode
	 * @public
	 * @since 4.9.008 (2009-04-02)
	 */
	public function setTextRenderingMode($stroke=0, $fill=true, $clip=false) {
		// Ref.: PDF 32000-1:2008 - 9.3.6 Text Rendering Mode
		// convert text rendering parameters
		if ($stroke < 0 || !is_numeric($stroke)) {
			$stroke = 0;
		}
		if ($fill === true) {
			if ($stroke > 0) {
				if ($clip === true) {
					// Fill, then stroke text and add to path for clipping
					$textrendermode = 6;
				} else {
					// Fill, then stroke text
					$textrendermode = 2;
				}
				$textstrokewidth = $stroke;
			} else {
				if ($clip === true) {
					// Fill text and add to path for clipping
					$textrendermode = 4;
				} else {
					// Fill text
					$textrendermode = 0;
				}
			}
		} else {
			if ($stroke > 0) {
				if ($clip === true) {
					// Stroke text and add to path for clipping
					$textrendermode = 5;
				} else {
					// Stroke text
					$textrendermode = 1;
				}
				$textstrokewidth = $stroke;
			} else {
				if ($clip === true) {
					// Add text to path for clipping
					$textrendermode = 7;
				} else {
					// Neither fill nor stroke text (invisible)
					$textrendermode = 3;
				}
			}
		}
		$this->textrendermode = $textrendermode;
		$this->textstrokewidth = $stroke;
	}
}