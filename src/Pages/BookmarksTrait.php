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

trait BookmarksTrait
{
    /**
     * Adds a bookmark - alias for Bookmark().
     */
    public function setBookmark(
        string $txt,
        int $level = 0,
        float $y = -1,
        int|string $page = '',
        string $style = '',
        array $color = [0, 0, 0],
        float $x = -1,
        mixed $link = ''
    ): void {
        $this->Bookmark($txt, $level, $y, $page, $style, $color, $x, $link);
    }

    /**
     * Adds a bookmark.
     */
    public function Bookmark(
        string $txt,
        int $level = 0,
        float $y = -1,
        int|string $page = '',
        string $style = '',
        array $color = [0, 0, 0],
        float $x = -1,
        mixed $link = ''
    ): void {
        if ($level < 0) {
            $level = 0;
        }

        if (isset($this->outlines[0])) {
            $lastoutline = end($this->outlines);
            $maxlevel = $lastoutline['l'] + 1;
        } else {
            $maxlevel = 0;
        }

        if ($level > $maxlevel) {
            $level = $maxlevel;
        }

        if ($y === -1) {
            $y = $this->GetY();
        } elseif ($y < 0) {
            $y = 0;
        } elseif ($y > $this->h) {
            $y = $this->h;
        }

        if ($x === -1) {
            $x = $this->GetX();
        } elseif ($x < 0) {
            $x = 0;
        } elseif ($x > $this->w) {
            $x = $this->w;
        }

        $fixed = false;
        $pageAsString = (string)$page;
        if ($pageAsString !== '' && $pageAsString[0] === '*') {
            $page = (int)substr($page, 1);
            $fixed = true; // this page number will not be changed
        }

        if ($page === '' || $page === 0) {
            $page = $this->PageNo();
            if (empty($page)) {
                return;
            }
        }

        $this->outlines[] = [
            't' => $txt,
            'l' => $level,
            'x' => $x,
            'y' => $y,
            'p' => $page,
            'f' => $fixed,
            's' => strtoupper($style),
            'c' => $color,
            'u' => $link,
        ];
    }

    /**
     * Sort bookmarks for page and key.
     */
    protected function sortBookmarks(): void
    {
        $outline_p = [];
        $outline_k = [];

        foreach ($this->outlines as $key => $row) {
            $outline_p[$key] = $row['p'];
            $outline_k[$key] = $key;
        }

        array_multisort(
            $outline_p,
            SORT_NUMERIC,
            SORT_ASC,
            $outline_k,
            SORT_NUMERIC,
            SORT_ASC,
            $this->outlines
        );
    }

    /**
     * Put bookmarks in PDF output.
     */
    public function putBookmarks(): void
    {
        $nb = count($this->outlines);
        if ($nb === 0) {
            return;
        }

        $this->sortBookmarks();

        $lru = [];
        $level = 0;

        foreach ($this->outlines as $i => $o) {
            if ($o['l'] > 0) {
                $parent = $lru[$o['l'] - 1];
                $this->outlines[$i]['parent'] = $parent;
                $this->outlines[$parent]['last'] = $i;

                if ($o['l'] > $level) {
                    $this->outlines[$parent]['first'] = $i;
                }
            } else {
                $this->outlines[$i]['parent'] = $nb;
            }

            if ($o['l'] <= $level && $i > 0) {
                $prev = $lru[$o['l']];
                $this->outlines[$prev]['next'] = $i;
                $this->outlines[$i]['prev'] = $prev;
            }

            $lru[$o['l']] = $i;
            $level = $o['l'];
        }
		//Outline items
		$n = $this->n + 1;
		$nltags = '/<br[\s]?\/>|<\/(blockquote|dd|dl|div|dt|h1|h2|h3|h4|h5|h6|hr|li|ol|p|pre|ul|tcpdf|table|tr|td)>/si';
		foreach ($this->outlines as $i => $o) {
			$oid = $this->_newobj();
			// covert HTML title to string
			$title = preg_replace($nltags, "\n", $o['t']);
			$title = preg_replace("/[\r]+/si", '', $title);
			$title = preg_replace("/[\n]+/si", "\n", $title);
			$title = strip_tags($title);
			$title = $this->stringTrim($title);
			$out = '<</Title '.$this->_textstring($title, $oid);
			$out .= ' /Parent '.($n + $o['parent']).' 0 R';
			if (isset($o['prev'])) {
				$out .= ' /Prev '.($n + $o['prev']).' 0 R';
			}
			if (isset($o['next'])) {
				$out .= ' /Next '.($n + $o['next']).' 0 R';
			}
			if (isset($o['first'])) {
				$out .= ' /First '.($n + $o['first']).' 0 R';
			}
			if (isset($o['last'])) {
				$out .= ' /Last '.($n + $o['last']).' 0 R';
			}
			if (isset($o['u']) AND !empty($o['u'])) {
				// link
				if (is_string($o['u'])) {
					if ($o['u'][0] == '#') {
						// internal destination
						$out .= ' /Dest /'.TCPDF_STATIC::encodeNameObject(substr($o['u'], 1));
					} elseif ($o['u'][0] == '%') {
						// embedded PDF file
						$filename = basename(substr($o['u'], 1));
						$out .= ' /A <</S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P '.($o['p'] - 1).' /A '.$this->embeddedfiles[$filename]['a'].' >> >>';
					} elseif ($o['u'][0] == '*') {
						// embedded generic file
						$filename = basename(substr($o['u'], 1));
						$jsa = 'var D=event.target.doc;var MyData=D.dataObjects;for (var i in MyData) if (MyData[i].path=="'.$filename.'") D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
						$out .= ' /A <</S /JavaScript /JS '.$this->_textstring($jsa, $oid).'>>';
					} else {
						// external URI link
						$out .= ' /A <</S /URI /URI '.$this->_datastring($this->unhtmlentities($o['u']), $oid).'>>';
					}
				} elseif (isset($this->links[$o['u']])) {
					// internal link ID
					$l = $this->links[$o['u']];
					if (isset($this->page_obj_id[($l['p'])])) {
						$out .= sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $this->page_obj_id[($l['p'])], ($this->pagedim[$l['p']]['h'] - ($l['y'] * $this->k)));
					}
				}
			} elseif (isset($this->page_obj_id[($o['p'])])) {
				// link to a page
				$out .= ' '.sprintf('/Dest [%u 0 R /XYZ %F %F null]', $this->page_obj_id[($o['p'])], ($o['x'] * $this->k), ($this->pagedim[$o['p']]['h'] - ($o['y'] * $this->k)));
			}
			// set font style
			$style = 0;
			if (!empty($o['s'])) {
				// bold
				if (strpos($o['s'], 'B') !== false) {
					$style |= 2;
				}
				// oblique
				if (strpos($o['s'], 'I') !== false) {
					$style |= 1;
				}
			}
			$out .= sprintf(' /F %d', $style);
			// set bookmark color
			if (isset($o['c']) AND is_array($o['c']) AND (count($o['c']) == 3)) {
				$color = array_values($o['c']);
				$out .= sprintf(' /C [%F %F %F]', ($color[0] / 255), ($color[1] / 255), ($color[2] / 255));
			} else {
				// black
				$out .= ' /C [0.0 0.0 0.0]';
			}
			$out .= ' /Count 0'; // normally closed item
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
		//Outline root
		$this->OutlineRoot = $this->_newobj();
		$this->_out('<< /Type /Outlines /First '.$n.' 0 R /Last '.($n + $lru[0]).' 0 R >>'."\n".'endobj');
    }
}