<?php

namespace LimePDF;

trait LIMEPDF_BARCODE_GETTERSETTER {

	/**
	 * Set document barcode.
	 * @param string $bc barcode
	 * @public
	 */
	public function setBarcode($bc='') {
		$this->barcode = $bc;
	}

	/**
	 * Get current barcode.
	 * @return string
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getBarcode() {
		return $this->barcode;
	}

}