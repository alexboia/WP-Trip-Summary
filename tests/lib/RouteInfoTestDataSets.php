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

 trait RouteInfoTestDataSets {
	use GenericTestHelpers;

    private $_lookupIndex = 1;

    public function _getPerTypeFields() {
		$data = array();
		foreach (Abp01_Route_Info::getSupportedTypes() as $type) {
			$info = new Abp01_Route_Info($type);
			foreach ($info->getValidFields() as $field => $descriptor) {
				$data[] = array($type, $field, $descriptor);
			}
		}
		return $data;
	}

    public function _getValidKeysDataSet() {
		$data = array();
		foreach (Abp01_Route_Info::getSupportedTypes() as $type) {
			$route = new Abp01_Route_Info($type);
			$fields = $route->getValidFields();
			foreach ($fields as $name => $descriptor) {
				$data[] = array(
					$type,
					$name,
					$this->_generateValue($descriptor)
				);
			}
		}
		return $data;
	}

    public function _generateInvalidFieldKeysDataSet() {
		$data = array();
		foreach (Abp01_Route_Info::getSupportedTypes() as $type) {
			$info = new Abp01_Route_Info($type);
			$data[] = array(
				$type, 
				$this->_generateWord($info->getValidFieldNames()),
				$this->_generateValue(null)
			);
		}
		return $data;
	}

    public function _getPerTypeRouteInfoDataSets() {
		$data = array();
		foreach (Abp01_Route_Info::getSupportedTypes() as $type) {
			$data[] = $this->_generateRandomRouteInfoWithType($type);
		}
		return $data;
    }

    public function _generateRandomRouteInfoWithType($type = null) {
        $faker = self::_getFaker();
        
        $type = empty($type) 
            ? $faker->randomElement(Abp01_Route_Info::getSupportedTypes()) 
            : $type;

        return array(
            $type,
            $this->_generateRandomRouteInfoForType($type)
        );
    }

    public function _generateRandomRouteInfoForType($type) {
        $values = array();
        $info = new Abp01_Route_Info($type);

        foreach ($info->getValidFields() as $name => $descriptor) {
            $values[$name] = $this->_generateValue($descriptor);
        }

        return $values;
    }
    
    public function _generateInvalidTypes() {
		$count = 5;
		$data = array();
		$types = Abp01_Route_Info::getSupportedTypes();
		while ($count > 0) {
			$data[] = array($this->_generateWord($types));
			$count --;
		}
		return $data;
	}

	public function _getValidTypes() {
		$data = array();
		$types = Abp01_Route_Info::getSupportedTypes();
		foreach ($types as $type) {
			$data[] = array($type);
		}
		return $data;
    }

    protected function _generateValue($fieldDescriptor) {
		$faker = self::_getFaker();
		if (!$fieldDescriptor) {
			$fieldDescriptor = array(
				'type' => $faker->randomElement(array('int', 'float', 'string')),
				'multiple' => $faker->randomElement(array(true, false))
			);
		}
		
		$type = $fieldDescriptor['type'];
		$multiple = isset($fieldDescriptor['multiple']) ? $fieldDescriptor['multiple'] : false;
		$value = null;

        if (!empty($fieldDescriptor['lookup'])) {
            $value = $this->_lookupIndex++;
        } else {
            switch ($type) {
                case 'int':
                    $value = $faker->numberBetween(0, PHP_INT_MAX);
                    break;
                case 'float':
                    $value = $faker->randomFloat(2, 0, null);
                    break;
                case 'string':
                    $value = $faker->word;
                    break;
            }
        }

		return $multiple ? array($value) : $value;
    }
    
    protected function _generateWord($excluded) {
		$faker = self::_getFaker();
		$word = $faker->word;
		while (in_array($word, $excluded)) {
			$word = $faker->word;
		}
		return $word;
    }

    protected function _getProjSphericalMercator() {
        return new Abp01_Route_SphericalMercator();
    }
 }