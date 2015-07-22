<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

/**
 * Uses the core functionality from the simple url validator (@see Abp01_Validate_Url) with the following changes:
 * - protocols are restricted to http://, https://, ftp:// and ftps://
 * - it checks for presence of the required placeholders: {z} - zoom, {x} - tile X, {y} - tile Y
 * - it does not allow empty values
 * */
class Abp01_Validate_TileLayerUrl extends Abp01_Validate_Url {
	/**
	 * @var array The placeholders to check for
	 * */
	private $_checkPlaceholders = array('{z}', '{x}', '{y}');

	/**
	 * Initializes a new instance
	 * */
	public function __construct() {
		parent::__construct(false, array('http://', 'https://', 'ftp://', 'ftps://'));
	}

	/**
	 * Prepares a tile layer URL for basic URL format validation. 
	 * The issue is that the domain main contain a placeholder for the subdomain and this would fail the validation.
	 * To overcome this, we remove the "{" and "}" from the placeholders.
	 * @param string $tileLayerUrl The URL to prepare
	 * @return string The prepared URl
	 * */
	private function _prepareForValidation($tileLayerUrl) {
		return preg_replace('/(\{([a-zA-z0-9]+)\})/','$2', $tileLayerUrl);
	}

	/**
	 * Validates that a given URL is valid as a tile layer URL template
	 * @param string $tileLayerUrl The URL to check for
	 * @return boolean True if it's valid, false otherwise
	 * */
	public function validate($tileLayerUrl) {
		//prepare URL for basic format testing
		$safeTestUrl = $this->_prepareForValidation($tileLayerUrl);
		//should not be empty and should be valid URL format
		if (!parent::validate($safeTestUrl)) {
			return false;
		}

		//test for required placeholders
		foreach ($this->_checkPlaceholders as $p) {
			if (stripos($tileLayerUrl, $p) === false) {
				return false;
			}
		}
		
		return true;
	}
}