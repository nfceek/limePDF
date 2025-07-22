<?php
// File: src/Security/SecurityManager.php
namespace LimePDF\Security;

class EncryptionManager {
    private $tcpdf;
    
    public function __construct($tcpdf_instance) 
    {
        $this->tcpdf = $tcpdf_instance;
    }
    
    /**
     * Put encryption dictionary and related objects
     * MOVED FROM: protected function _putencryption()
     */
    public function putEncryption() {
		if (!$this->encrypted) {
			return;
		}
		$this->encryptdata['objid'] = $this->_newobj();
		$out = '<<';
		if (!isset($this->encryptdata['Filter']) OR empty($this->encryptdata['Filter'])) {
			$this->encryptdata['Filter'] = 'Standard';
		}
		$out .= ' /Filter /'.$this->encryptdata['Filter'];
		if (isset($this->encryptdata['SubFilter']) AND !empty($this->encryptdata['SubFilter'])) {
			$out .= ' /SubFilter /'.$this->encryptdata['SubFilter'];
		}
		if (!isset($this->encryptdata['V']) OR empty($this->encryptdata['V'])) {
			$this->encryptdata['V'] = 1;
		}
		// V is a code specifying the algorithm to be used in encrypting and decrypting the document
		$out .= ' /V '.$this->encryptdata['V'];
		if (isset($this->encryptdata['Length']) AND !empty($this->encryptdata['Length'])) {
			// The length of the encryption key, in bits. The value shall be a multiple of 8, in the range 40 to 256
			$out .= ' /Length '.$this->encryptdata['Length'];
		} else {
			$out .= ' /Length 40';
		}
		if ($this->encryptdata['V'] >= 4) {
			if (!isset($this->encryptdata['StmF']) OR empty($this->encryptdata['StmF'])) {
				$this->encryptdata['StmF'] = 'Identity';
			}
			if (!isset($this->encryptdata['StrF']) OR empty($this->encryptdata['StrF'])) {
				// The name of the crypt filter that shall be used when decrypting all strings in the document.
				$this->encryptdata['StrF'] = 'Identity';
			}
			// A dictionary whose keys shall be crypt filter names and whose values shall be the corresponding crypt filter dictionaries.
			if (isset($this->encryptdata['CF']) AND !empty($this->encryptdata['CF'])) {
				$out .= ' /CF <<';
				$out .= ' /'.$this->encryptdata['StmF'].' <<';
				$out .= ' /Type /CryptFilter';
				if (isset($this->encryptdata['CF']['CFM']) AND !empty($this->encryptdata['CF']['CFM'])) {
					// The method used
					$out .= ' /CFM /'.$this->encryptdata['CF']['CFM'];
					if ($this->encryptdata['pubkey']) {
						$out .= ' /Recipients [';
						foreach ($this->encryptdata['Recipients'] as $rec) {
							$out .= ' <'.$rec.'>';
						}
						$out .= ' ]';
						if (isset($this->encryptdata['CF']['EncryptMetadata']) AND (!$this->encryptdata['CF']['EncryptMetadata'])) {
							$out .= ' /EncryptMetadata false';
						} else {
							$out .= ' /EncryptMetadata true';
						}
					}
				} else {
					$out .= ' /CFM /None';
				}
				if (isset($this->encryptdata['CF']['AuthEvent']) AND !empty($this->encryptdata['CF']['AuthEvent'])) {
					// The event to be used to trigger the authorization that is required to access encryption keys used by this filter.
					$out .= ' /AuthEvent /'.$this->encryptdata['CF']['AuthEvent'];
				} else {
					$out .= ' /AuthEvent /DocOpen';
				}
				if (isset($this->encryptdata['CF']['Length']) AND !empty($this->encryptdata['CF']['Length'])) {
					// The bit length of the encryption key.
					$out .= ' /Length '.$this->encryptdata['CF']['Length'];
				}
				$out .= ' >> >>';
			}
			// The name of the crypt filter that shall be used by default when decrypting streams.
			$out .= ' /StmF /'.$this->encryptdata['StmF'];
			// The name of the crypt filter that shall be used when decrypting all strings in the document.
			$out .= ' /StrF /'.$this->encryptdata['StrF'];
			if (isset($this->encryptdata['EFF']) AND !empty($this->encryptdata['EFF'])) {
				// The name of the crypt filter that shall be used when encrypting embedded file streams that do not have their own crypt filter specifier.
				$out .= ' /EFF /'.$this->encryptdata[''];
			}
		}
		// Additional encryption dictionary entries for the standard security handler
		if ($this->encryptdata['pubkey']) {
			if (($this->encryptdata['V'] < 4) AND isset($this->encryptdata['Recipients']) AND !empty($this->encryptdata['Recipients'])) {
				$out .= ' /Recipients [';
				foreach ($this->encryptdata['Recipients'] as $rec) {
					$out .= ' <'.$rec.'>';
				}
				$out .= ' ]';
			}
		} else {
			$out .= ' /R';
			if ($this->encryptdata['V'] == 5) { // AES-256
				$out .= ' 5';
				$out .= ' /OE ('.TCPDF_STATIC::_escape($this->encryptdata['OE']).')';
				$out .= ' /UE ('.TCPDF_STATIC::_escape($this->encryptdata['UE']).')';
				$out .= ' /Perms ('.TCPDF_STATIC::_escape($this->encryptdata['perms']).')';
			} elseif ($this->encryptdata['V'] == 4) { // AES-128
				$out .= ' 4';
			} elseif ($this->encryptdata['V'] < 2) { // RC-40
				$out .= ' 2';
			} else { // RC-128
				$out .= ' 3';
			}
			$out .= ' /O ('.TCPDF_STATIC::_escape($this->encryptdata['O']).')';
			$out .= ' /U ('.TCPDF_STATIC::_escape($this->encryptdata['U']).')';
			$out .= ' /P '.$this->encryptdata['P'];
			if (isset($this->encryptdata['EncryptMetadata']) AND (!$this->encryptdata['EncryptMetadata'])) {
				$out .= ' /EncryptMetadata false';
			} else {
				$out .= ' /EncryptMetadata true';
			}
		}
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
    }
}