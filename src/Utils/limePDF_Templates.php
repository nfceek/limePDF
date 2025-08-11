<?php

    namespace LimePDF\Utils;

	Use LimePDF\TCPDF;

class limePDF_Templates {

    // /**
	//  * Output Form XObjects Templates.
	//  * @author Nicola Asuni
	//  * @since 5.8.017 (2010-08-24)
	//  * @protected
	//  * @see startTemplate(), endTemplate(), printTemplate()
	//  */
	// public static function putXObjects(\LimePDF\TCPDF $tcpdf){

	// 	$getXObjects = $tcpdf->getXObjects();

	// 	foreach ($getXObjects as $key => $data) {
	// 		if (isset($data['outdata'])) {
	// 			$stream = str_replace($this->epsmarker, '', trim($data['outdata']));
	// 			$out = $this->_getobj($data['n'])."\n";
	// 			$out .= '<<';
	// 			$out .= ' /Type /XObject';
	// 			$out .= ' /Subtype /Form';
	// 			$out .= ' /FormType 1';
	// 			if ($this->compress) {
	// 				$stream = gzcompress($stream);
	// 				$out .= ' /Filter /FlateDecode';
	// 			}
	// 			$out .= sprintf(' /BBox [%F %F %F %F]', ($data['x'] * $this->k), (-$data['y'] * $this->k), (($data['w'] + $data['x']) * $this->k), (($data['h'] - $data['y']) * $this->k));
	// 			$out .= ' /Matrix [1 0 0 1 0 0]';
	// 			$out .= ' /Resources <<';
	// 			$out .= ' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]';
	// 			if (!$this->pdfa_mode || $this->pdfa_version >= 2) {
	// 				// transparency
	// 				if (isset($data['extgstates']) AND !empty($data['extgstates'])) {
	// 					$out .= ' /ExtGState <<';
	// 					foreach ($data['extgstates'] as $k => $extgstate) {
	// 						if (isset($this->extgstates[$k]['name'])) {
	// 							$out .= ' /'.$this->extgstates[$k]['name'];
	// 						} else {
	// 							$out .= ' /GS'.$k;
	// 						}
	// 						$out .= ' '.$this->extgstates[$k]['n'].' 0 R';
	// 					}
	// 					$out .= ' >>';
	// 				}
	// 				if (isset($data['gradients']) AND !empty($data['gradients'])) {
	// 					$gp = '';
	// 					$gs = '';
	// 					foreach ($data['gradients'] as $id => $grad) {
	// 						// gradient patterns
	// 						$gp .= ' /p'.$id.' '.$this->gradients[$id]['pattern'].' 0 R';
	// 						// gradient shadings
	// 						$gs .= ' /Sh'.$id.' '.$this->gradients[$id]['id'].' 0 R';
	// 					}
	// 					$out .= ' /Pattern <<'.$gp.' >>';
	// 					$out .= ' /Shading <<'.$gs.' >>';
	// 				}
	// 			}
	// 			// spot colors
	// 			if (isset($data['spot_colors']) AND !empty($data['spot_colors'])) {
	// 				$out .= ' /ColorSpace <<';
	// 				foreach ($data['spot_colors'] as $name => $color) {
	// 					$out .= ' /CS'.$color['i'].' '.$this->spot_colors[$name]['n'].' 0 R';
	// 				}
	// 				$out .= ' >>';
	// 			}
	// 			// fonts
	// 			if (!empty($data['fonts'])) {
	// 				$out .= ' /Font <<';
	// 				foreach ($data['fonts'] as $fontkey => $fontid) {
	// 					$out .= ' /F'.$fontid.' '.$this->font_obj_ids[$fontkey].' 0 R';
	// 				}
	// 				$out .= ' >>';
	// 			}
	// 			// images or nested xobjects
	// 			if (!empty($data['images']) OR !empty($data['xobjects'])) {
	// 				$out .= ' /XObject <<';
	// 				foreach ($data['images'] as $imgid) {
	// 					$out .= ' /I'.$imgid.' '.$this->xobjects['I'.$imgid]['n'].' 0 R';
	// 				}
	// 				foreach ($data['xobjects'] as $sub_id => $sub_objid) {
	// 					$out .= ' /'.$sub_id.' '.$sub_objid['n'].' 0 R';
	// 				}
	// 				$out .= ' >>';
	// 			}
	// 			$out .= ' >>'; //end resources
	// 			if (isset($data['group']) AND ($data['group'] !== false)) {
	// 				// set transparency group
	// 				$out .= ' /Group << /Type /Group /S /Transparency';
	// 				if (is_array($data['group'])) {
	// 					if (isset($data['group']['CS']) AND !empty($data['group']['CS'])) {
	// 						$out .= ' /CS /'.$data['group']['CS'];
	// 					}
	// 					if (isset($data['group']['I'])) {
	// 						$out .= ' /I /'.($data['group']['I']===true?'true':'false');
	// 					}
	// 					if (isset($data['group']['K'])) {
	// 						$out .= ' /K /'.($data['group']['K']===true?'true':'false');
	// 					}
	// 				}
	// 				$out .= ' >>';
	// 			}
	// 			$stream = $this->_getrawstream($stream, $data['n']);
	// 			$out .= ' /Length '.strlen($stream);
	// 			$out .= ' >>';
	// 			$out .= ' stream'."\n".$stream."\n".'endstream';
	// 			$out .= "\n".'endobj';
	// 			$this->_out($out);
	// 		}
	// 	}
	}