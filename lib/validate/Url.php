<?php
class Abp01_Validate_Url {
	private $_allowEmpty = true;
	
	private $_allowedProtocols = array();
	
	public function __construct($allowEmpty = true, array $allowedProtocols = array('http://', 'https://', 'mailto:', 'ftp://', 'ftps://')) {
		$this->_allowedProtocols = $allowedProtocols;
		$this->_allowEmpty = !!$allowEmpty;
	}
	
	private function _checkProtocols($url) {
		if (!count($this->_allowedProtocols)) {
			return true;
		}
		foreach ($this->_allowedProtocols as $p) {
			if (stripos($url, $p) === 0) {
				return true;
			}
		}
		return false;
	}
	
	public function validate($url) {
		if (empty($url) && !$this->_allowEmpty) {
			return false;
		} 
		return filter_var($url, FILTER_VALIDATE_URL) !== false
			&& $this->_checkProtocols($url);
	}
}
