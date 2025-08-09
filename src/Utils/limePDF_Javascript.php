<?php


class limePDF_Javascript {

	/**
	 * Create a javascript PDF string.
	 * @protected
	 * @author Johannes G\FCntert, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	protected function _putjavascript() {
		if ($this->pdfa_mode OR (empty($this->javascript) AND empty($this->js_objects))) {
			return;
		}
		if (strpos($this->javascript, 'this.addField') > 0) {
			if (!$this->ur['enabled']) {
				//$this->setUserRights();
			}
			// the following two lines are used to avoid form fields duplication after saving
			// The addField method only works when releasing user rights (UR3)
			$jsa = sprintf("ftcpdfdocsaved=this.addField('%s','%s',%d,[%F,%F,%F,%F]);", 'tcpdfdocsaved', 'text', 0, 0, 1, 0, 1);
			$jsb = "getField('tcpdfdocsaved').value='saved';";
			$this->javascript = $jsa."\n".$this->javascript."\n".$jsb;
		}
		// name tree for javascript
		$this->n_js = '<< /Names [';
		if (!empty($this->javascript)) {
			$this->n_js .= ' (EmbeddedJS) '.($this->n + 1).' 0 R';
		}
		if (!empty($this->js_objects)) {
			foreach ($this->js_objects as $key => $val) {
				if ($val['onload']) {
					$this->n_js .= ' (JS'.$key.') '.$key.' 0 R';
				}
			}
		}
		$this->n_js .= ' ] >>';
		// default Javascript object
		if (!empty($this->javascript)) {
			$obj_id = $this->_newobj();
			$out = '<< /S /JavaScript';
			$out .= ' /JS '.$this->_textstring($this->javascript, $obj_id);
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
		// additional Javascript objects
		if (!empty($this->js_objects)) {
			foreach ($this->js_objects as $key => $val) {
				$out = $this->_getobj($key)."\n".' << /S /JavaScript /JS '.$this->_textstring($val['js'], $key).' >>'."\n".'endobj';
				$this->_out($out);
			}
		}
	}    

}