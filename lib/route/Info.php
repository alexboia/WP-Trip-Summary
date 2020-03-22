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

class Abp01_Route_Info {
    const BIKE = 'bike';

    const HIKING = 'hiking';

    const TRAIN_RIDE = 'trainRide';

    private $_data = array();

    private $_type;

    private $_fields = array(
        self::BIKE => array(
            'bikeDistance' => array(
                'type' => 'float',
                'minVal' => 0
            ),
            'bikeTotalClimb' => array(
                'type' => 'float'
            ),
            'bikeDifficultyLevel' => array(
                'type' => 'int',
                'lookup' => Abp01_Lookup::DIFFICULTY_LEVEL,
                'minVal' => 0
            ),
            'bikeAccess' => array(
                'type' => 'string'
            ),
            'bikeRecommendedSeasons' => array(
                'type' => 'int',
                'lookup' => Abp01_Lookup::RECOMMEND_SEASONS,
                'minVal' => 0,
                'multiple' => true
            ),
            'bikePathSurfaceType' => array(
                'type' => 'int',
                'lookup' => Abp01_Lookup::PATH_SURFACE_TYPE,
                'minVal' => 0,
                'multiple' => true
            ),
            'bikeBikeType' => array(
                'type' => 'int',
                'lookup' => Abp01_Lookup::BIKE_TYPE,
                'minVal' => 0,
                'multiple' => true
            )
        ),
        self::HIKING => array(
            'hikingDistance' => array(
                'type' => 'float',
                'minVal' => 0
            ),
            'hikingTotalClimb' => array(
                'type' => 'float'
            ),
            'hikingDifficultyLevel' => array(
                'type' => 'int',
                'lookup' => Abp01_Lookup::DIFFICULTY_LEVEL,
                'minVal' => 0
            ),
            'hikingAccess' => array(
                'type' => 'string'
            ),
            'hikingRecommendedSeasons' => array(
                'type' => 'int',
                'lookup' => Abp01_Lookup::RECOMMEND_SEASONS,
                'minVal' => 0,
                'multiple' => true
            ),
            'hikingSurfaceType' => array(
                'type' => 'int',
                'lookup' => Abp01_Lookup::PATH_SURFACE_TYPE,
                'minVal' => 0,
                'multiple' => true
            ),
            'hikingRouteMarkers' => array(
                'type' => 'string'
            )
        ),
        self::TRAIN_RIDE => array(
            'trainRideDistance' => array(
                'type' => 'float',
                'minVal' => 0
            ),
            'trainRideChangeNumber' => array(
                'type' => 'int',
                'minVal' => 0
            ),
            'trainRideGauge' => array(
                'type' => 'float',
                'minVal' => 0
            ),
            'trainRideOperator' => array(
                'type' => 'int',
                'lookup' => Abp01_Lookup::RAILROAD_OPERATOR,
                'minVal' => 0,
                'multiple' => true
            ),
            'trainRideLineStatus' => array(
                'type' => 'int',
                'lookup' => Abp01_Lookup::RAILROAD_LINE_STATUS,
                'minVal' => 0,
                'multiple' => true
            ),
            'trainRideElectrificationStatus' => array(
                'type' => 'int',
                'lookup' => Abp01_Lookup::RAILROAD_ELECTRIFICATION,
                'minVal' => 0,
                'multiple' => true
            ),
            'trainRideLineType' => array(
                'type' => 'int',
                'lookup' => Abp01_Lookup::RAILROAD_LINE_TYPE,
                'minVal' => 0,
                'multiple' => true
            )
        )
    );

    public static function isTypeSupported($type) {
        return in_array($type, self::getSupportedTypes());
    }

	public static function getSupportedTypes() {
		return array(
			self::BIKE,
            self::TRAIN_RIDE,
            self::HIKING
		);
	}

    public static function fromJson($type, $json) {
        if (empty($json)) {
            throw new InvalidArgumentException();
        }

        $data = json_decode($json, true);
        if ($data === null || !is_array($data)) {
            return null;
        }

        $routeDetails = new self($type);
        foreach ($data as $k => $v) {
            $routeDetails->__set($k, $v);
        }

        return $routeDetails;
    }

    public function __construct($type) {
        if (empty($type) || !self::isTypeSupported($type)) {
            throw new InvalidArgumentException();
        }
        $this->_type = $type;
    }

    private function _filterFieldValue($field, $value) {
        if (!$this->isFieldValid($field)) {
            return null;
        }
        $def = $this->_fields[$this->_type][$field];
        if (!is_array($def)) {
            if (is_string($def)) {
                $def = array(
                    'type' => $def
                );
            }
        }

        if (!isset($def['type'])) {
            $def['type'] = 'string';
        }
        if (!isset($def['minVal'])) {
            $def['minVal'] = -INF;
        }
		if (!isset($def['maxVal'])) {
			$def['maxVal'] = INF;
		}

        if (isset($def['multiple']) && $def['multiple'] === true) {
            if (!is_array($value)) {
                $value = array($value);
            }
        }
	
		return Abp01_InputFiltering::filterValue($value, 
			$def['type'], 
			$def['minVal'], 
			$def['maxVal']
		);
    }

    private function _getValidFields() {
        return $this->_fields[$this->_type];
    }

    private function _assertKeyValid($k) {
        if (empty($k) || !$this->isFieldValid($k)) {
            throw new InvalidArgumentException('Invalid field key: "' . $k . '"');
        }
    }

    public function setData(array $data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function isFieldValid($field) {
        $validFields = $this->_getValidFields();
        return array_key_exists($field, $validFields);
    }

    public function __set($k, $v) {
        $this->_assertKeyValid($k);
        $this->_data[$k] = $this->_filterFieldValue($k, $v);
    }

    public function __get($k) {
        $this->_assertKeyValid($k);
        return isset($this->_data[$k]) ? $this->_data[$k] : null;
    }

    public function isLookupKey($field) {
        return !empty($this->getLookupKey($field));
    }

    public function getAllLookupFields() {
        $lookupKeys = array();

        foreach ($this->_fields[$this->_type] as $field => $def) {
            if (isset($def['lookup'])) {
                $lookupKeys[] = $field;
            }
        }

        return $lookupKeys;
    }

    public function getLookupKey($field) {
        if (!isset($this->_fields[$this->_type][$field])) {
            return null;
        }

        $def = $this->_fields[$this->_type][$field];
        return isset($def['lookup']) ? $def['lookup'] : null;
    }

    public function getData() {
        return $this->_data;
    }

    public function isBikingTour() {
        return $this->_type == self::BIKE;
    }

    public function isHikingTour() {
        return $this->_type == self::HIKING;
    }

    public function isTrainRideTour() {
        return $this->_type == self::TRAIN_RIDE;
    }

    public function getValidFieldNames() {
        return array_keys($this->_getValidFields());
    }

	public function getValidFields() {
		return $this->_getValidFields();
	}

    public function toJson() {
        return json_encode($this->_data);
    }

    public function getType() {
        return $this->_type;
    }
}