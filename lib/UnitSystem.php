<?php
abstract class Abp01_UnitSystem {
	const METRIC = 'metric';
	
	const IMPERIAL = 'imperial';
	
	public static function create($system) {
		if (!self::isSupported($system)) {
			return null;
		}
		$className = 'Abp01_UnitSystem_' . ucfirst($system);
		if (class_exists($className)) {
			return new $className();
		} else {
			return null;
		}
	}
	
	public static function isSupported($system) {
		return $system == self::METRIC || $system == self::IMPERIAL;
	}
	
	abstract public function getDistanceUnit();
	
	abstract public function getLengthUnit();	
	
	abstract public function getHeightUnit();
}