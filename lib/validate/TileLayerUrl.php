<?php
class Abp01_Validate_TileLayerUrl extends Abp01_Validate_Url {
	private $_checkPlaceholders = array('{z}', '{x}', '{y}');

	public function __construct() {
		parent::__construct(false, array('http://', 'https://', 'ftp://', 'ftps://'));
	}
	
	private function _prepareForValidation($tileLayerUrl) {
		return preg_replace('/(\{([a-zA-z0-9]+)\})/','$2', $tileLayerUrl);
	}

	public function validate($tileLayerUrl) {
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