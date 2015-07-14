<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_UnitSystem_Imperial extends Abp01_UnitSystem {
	public function getDistanceUnit() {
		return 'mi';
	}
	
	public function getLengthUnit() {
		return 'in';
	}	
	
	public function getHeightUnit() {
		return 'ft';
	}
}