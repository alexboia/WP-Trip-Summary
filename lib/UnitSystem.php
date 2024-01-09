<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
 *
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 *	1. Redistributions of source code must retain the above copyright notice, 
 *		this list of conditions and the following disclaimer.
 *
 * 	2. Redistributions in binary form must reproduce the above copyright notice, 
 *		this list of conditions and the following disclaimer in the documentation 
 *		and/or other materials provided with the distribution.
 *
 *	3. Neither the name of the copyright holder nor the names of its contributors 
 *		may be used to endorse or promote products derived from this software without 
 *		specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, 
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

/**
 * This class implements a basic notion of a unit system.
 * It provides the basic interface required for this plug-in as well as a factory method to create concrete unit systems.
 * Currently supported unit systems are:
 * - metric;
 * - imperial.
 * 
 * @package WP-Trip-Summary
 * */
abstract class Abp01_UnitSystem {
	/**
	 * Represents the metric system.
	 * Use this as an argument of Abp01_UnitSystem::create() to get a new instance of the metric system class
	 * */
	const METRIC = 'metric';

	/**
	 * Represents the imperial system.
	 * Use this as an argument of Abp01_UnitSystem::create() to get a new instance of the imperial system class
	 * */
	const IMPERIAL = 'imperial';

	/**
	 * Creates a concrete unit system using the given key.
	 * If no unit system is supported for the given key, null is returned.
	 * @param string $system The unit system
	 * @return Abp01_UnitSystem A corresponding derived class or null if no corresponding class is found
	 * */
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

	/**
	 * Checks if a unit system for the given key is supported or not
	 * @param string $system The unit system key to check for
	 * @return bool True if supported, false otherwise
	 * */
	public static function isSupported($system) {
		return $system == self::METRIC || $system == self::IMPERIAL;
	}

	/**
	 * Returns an array of available unit systems. 
	 *  Keys are system identifiers, values are system labels.
	 * 
	 * @return string[] The available systems.
	 */
	public static function getAvailableUnitSystems() {
		return array(
			Abp01_UnitSystem::METRIC => ucfirst(Abp01_UnitSystem::METRIC),
			Abp01_UnitSystem::IMPERIAL => ucfirst(Abp01_UnitSystem::IMPERIAL)
		);
	}

	/**
	 * Converts the unit system to a plain stdClass object with the following properties:
	 *      - distanceUnit;
	 *      - lengthUnit;
	 *      - heightUnit.
	 * @return stdClass The data object that contains the above-mentioned properties.
	 */
	public function asPlainObject() {
		$data = new stdClass();
		$data->distanceUnit = $this->getDistanceUnit();
		$data->lengthUnit = $this->getLengthUnit();
		$data->heightUnit = $this->getHeightUnit();
		return $data;
	}

	/**
	 * Checks whether distance can be converted between 
	 *  the current unit system and the given unit system.
	 * 
	 * @param Abp01_UnitSystem $otherSystem The potential target system
	 * @return bool True if possible, false otherwise
	 */
	public function canConvertDistanceTo(Abp01_UnitSystem $otherSystem) {
		return $this->_canConvertBetweenUnits($this->getDistanceUnit(), 
			$otherSystem->getDistanceUnit());
	}

	/**
	 * Convert the given distance value tot he given unit system
	 * 
	 * @param mixed $value The value to convert
	 * @param Abp01_UnitSystem $otherSystem The target unit system
	 * 
	 * @return mixed The converted value
	 */
	public function convertDistanceTo($value, Abp01_UnitSystem $otherSystem) {
		return $this->_convertBetweenUnits($value, 
			$this->getDistanceUnit(), 
			$otherSystem->getDistanceUnit());
	}

	/**
	 * Checks whether length can be converted between 
	 *  the current unit system and the given unit system.
	 * 
	 * @param Abp01_UnitSystem $otherSystem The potential target system
	 * @return bool True if possible, false otherwise
	 */
	public function canConvertLengthTo(Abp01_UnitSystem $otherSystem) {
		return $this->_canConvertBetweenUnits($this->getLengthUnit(), 
			$otherSystem->getLengthUnit());
	}

	/**
	 * Convert the given length value tot he given unit system
	 * 
	 * @param mixed $value The value to convert
	 * @param Abp01_UnitSystem $otherSystem The target unit system
	 * 
	 * @return mixed The converted value
	 */
	public function convertLengthTo($value, Abp01_UnitSystem $otherSystem) {
		return $this->_convertBetweenUnits($value, 
			$this->getLengthUnit(), 
			$otherSystem->getLengthUnit());
	}

	/**
	 * Checks whether height can be converted between 
	 *  the current unit system and the given unit system.
	 * 
	 * @param Abp01_UnitSystem $otherSystem The potential target system
	 * @return bool True if possible, false otherwise
	 */
	public function canConvertHeightTo(Abp01_UnitSystem $otherSystem) {
		return $this->_canConvertBetweenUnits($this->getHeightUnit(), 
			$otherSystem->getHeightUnit());
	}

	/**
	 * Convert the given height value tot he given unit system
	 * 
	 * @param mixed $value The value to convert
	 * @param Abp01_UnitSystem $otherSystem The target unit system
	 * 
	 * @return mixed The converted value
	 */
	public function convertHeightTo($value, Abp01_UnitSystem $otherSystem) {
		return $this->_convertBetweenUnits($value, 
			$this->getHeightUnit(), 
			$otherSystem->getHeightUnit());
	}

	private function _canConvertBetweenUnits($fromUnit, $toUnit) {
		$conversions = $this->getConversions();
		return isset($conversions[$fromUnit]) && isset($conversions[$fromUnit][$toUnit]);
	}

	/**
	 * Convert a value between to units.
	 * 
	 * @param mixed $value
	 * @param string $fromUnit The unit in which the value is originally expressed
	 * @param string $toUnit The unit in which the value needs to be converted
	 * @return mixed The converted value
	 */
	private function _convertBetweenUnits($value, $fromUnit, $toUnit) {
		if ($fromUnit == $toUnit) {
			return $value;
		}

		$conversions = $this->getConversions();
		if (isset($conversions[$fromUnit])) {
			if (isset($conversions[$fromUnit][$toUnit])) {
				$conv = $conversions[$fromUnit][$toUnit];
				return round($value * $conv['factor'] + $conv['offset'], 2);
			} else {
				throw new InvalidArgumentException('No conversions available to unit "' . $toUnit . '"');
			}
		} else {
			throw new InvalidArgumentException('No conversions available from unit "' . $fromUnit . '"');
		}
	}

	/**
	 * Gets the available unit conversions, as an array 
	 *  with the following structure:
	 *  
	 *  array(
	 *      sourceUnit => array(
	 *          targetUnit => array('factor' => x, 'offset' => y)
	 *      )
	 *  )
	 * 
	 * Each conversion is expressed, in relation to another unit 
	 *  using a generic formula
	 * 
	 *  targetUnit = sourceUnit * factor + offset.0
	 * 
	 * @return array The available conversions
	 */
	abstract protected function getConversions();

	/**
	 * Returns the unit, as a symbol, used to measure distances
	 * @return string The distance measurement unit symbol
	 * */
	abstract public function getDistanceUnit();

	/**
	 * Returns the unit, as a symbol, used to measure lengths (linear object dimensions)
	 * @return string The length measurement unit symbol
	 * */
	abstract public function getLengthUnit();

	/**
	 * Returns the unit, as a symbol, used to measure heights
	 * @return string The height measurement unit symbol
	 * */
	abstract public function getHeightUnit();
}
