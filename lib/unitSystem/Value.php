<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
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
 * @package WP-Trip-Summary
 */
abstract class Abp01_UnitSystem_Value {
    /** 
     * The value amount
     * 
     *  @var int|float 
     */    
    protected $_value;

    /**
     * The unit system in which the value is expressed
     * 
     * @var Abp01_UnitSystem
     */
    protected $_unitSystem;

    protected function __construct($value, $unitSystem = null) {
        if (!($unitSystem instanceof Abp01_UnitSystem)) {
            if (!empty($unitSystem)) {
                $unitSystem = $this->_createUnitSystemInstanceOrThrow($unitSystem);
            } else {
                $unitSystem = Abp01_UnitSystem::create(Abp01_UnitSystem::METRIC);
            }
        }

        $this->_value = $value;
        $this->_unitSystem = $unitSystem;
    }

    private function _createUnitSystemInstanceOrThrow($unitSystem) {
        if (!Abp01_UnitSystem::isSupported($unitSystem)) {
            throw new InvalidArgumentException('Unsupported unit system: "' . $unitSystem . '"');
        }

        return Abp01_UnitSystem::create($unitSystem);
    }

    /**
     * Converts the value to the given unit system
     * 
     * @param Abp01_UnitSystem|string $otherSystem The target unit system, either as an instance or a supported descriptor value.
     * @return Abp01_UnitSystem_Value The converted value
     */
    public function convertTo($otherSystem) {
        $otherSystem = !($otherSystem instanceof Abp01_UnitSystem) 
            ? $this->_createUnitSystemInstanceOrThrow($otherSystem) 
            : $otherSystem;

        $className = get_class($this);
        $convertedValue = $this->convertValueTo($otherSystem);

        return new $className($convertedValue, $otherSystem);
    }

    public function toPlainObject() {
        $data = new stdClass();
        $data->value = $this->_value;
        $data->unit = $this->getUnit();
        return $data;
    }

    /**
     * Format the value along with the unit in which is expressed
     * 
     * @return string The formatted value
     */
    public function format() {
        return sprintf('%s %s', $this->_value, $this->getUnit());
    }

    public function __toString() {
        return $this->format();
    }

    /**
     * Retrieves the value amount
     * 
     * @return int|float The value amount
     */
    public function getValue() {
        return $this->_value;
    }

    /**
     * Retrieves the unit system in which the value is expressed.
     * 
     * @return Abp01_UnitSystem The unit system
     */
    public function getUnitSystem() {
        return $this->_unitSystem;
    }

    /**
     * Converts the value to the given unit system.
     * 
     * @return Abp01_UnitSystem_Value The new value
     */
    abstract protected function convertValueTo(Abp01_UnitSystem $otherSystem);

    /**
     * Retrieves the unit in which the value is measured.
     * This is strongly tied to the unit system in which the value is expressed.
     * 
     * @return string The unit
     */
    abstract protected function getUnit();
}