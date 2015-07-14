<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_UnitSystem_Metric extends Abp01_UnitSystem {
	public function getDistanceUnit() {
		return 'km';
	}
	
	public function getLengthUnit() {
		return 'mm';
	}	
	
	public function getHeightUnit() {
		return 'm';
	}
}