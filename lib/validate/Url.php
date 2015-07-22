<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

/**
 * Class used to validate a URL. The validation has two stages:
 * - check that it has a valid url format;
 * - check that the URL starts with one of the accepted protocols.
 * If the list of accepted protocols is empty, then it is considered that any protocol is accepted
 * */
class Abp01_Validate_Url {
	/**
	 * @var boolean Whether empty strings are considered valid or not
	 * */
	private $_allowEmpty = true;

	/**
	 * @var array The list of allowed protocols. If set to empty, any protocol is allowed
	 * */
	private $_allowedProtocols = array();

	/**
	 * Initializes a new instance.
	 * @param boolean $allowEmpty Whether empty strings are considered valid or not. Defaults to true.
	 * @param array $allowedProtocols The list of allowed protocols. If set to empty, any protocol is allowed
	 * */ 
	public function __construct($allowEmpty = true, array $allowedProtocols = array('http://', 'https://', 'mailto:', 'ftp://', 'ftps://')) {
		$this->_allowedProtocols = $allowedProtocols;
		$this->_allowEmpty = !!$allowEmpty;
	}

	/**
	 * Checks that the given URL starts with one of the allowed protocols
	 * @param string $url The URL to check
	 * @return boolean True if valid, false otherwise
	 * */
	private function _checkProtocols($url) {
		//no allowed protocols configured - anything goes
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

	/**
	 * Validates the given URL. See class description for more details
	 * @param string $url The URL to validate
	 * @return boolean True if it's valid, false otherwise
	 * */
	public function validate($url) {
		$url = trim($url);
		if (empty($url)) {
			return $this->_allowEmpty;
		} 
		return filter_var($url, FILTER_VALIDATE_URL) !== false
			&& $this->_checkProtocols($url);
	}
}