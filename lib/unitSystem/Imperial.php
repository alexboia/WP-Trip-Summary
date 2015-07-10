<?php
class Abp10_UnitSystem_Imperial extends Abp01_UnitSystem {
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