<?php

namespace LimePDF\Utils;

trait FontManagerTrait {
    // protected array $config = [];
    // protected array $fonts = [];
    // protected array $fontkeys = [];
    // protected int $n = 0;
    // protected array $font_obj_ids = [];
    // protected array $page_obj_id = [];
    // protected array $form_obj_id = [];

    /**
	 * Return true if the current font is unicode type.
	 * @return bool true for unicode font, false otherwise.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.002 (2010-08-14)
	 */
	public function isUnicodeFont() {
		return (($this->CurrentFont['type'] == 'TrueTypeUnicode') OR ($this->CurrentFont['type'] == 'cidfont0'));
	}

}