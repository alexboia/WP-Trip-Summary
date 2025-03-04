<?php
/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
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

class Abp01_Validation_Rule_Composite implements Abp01_Validation_Rule {
    /**
     * @var array
     */
    private $_inputValueKeyValidationRules;

    public function __construct(array $inputValueKeyValidationRules) {
        $this->_inputValueKeyValidationRules = $inputValueKeyValidationRules;
    }

    public function validateInputAndGetMessage($input) {
        if ($input == null) {
            throw new InvalidArgumentException('Input may not be null');
        }

        if (!is_object($input) && !is_array($input)) {
            throw new InvalidArgumentException('Input must be either an object or an array');
        }

        $message = null;
        $input = $this->_prepareInput($input);

        foreach ($this->_inputValueKeyValidationRules as $key => $rules) {
            $valueToValidate = isset($input[$key]) 
                ? $input[$key] 
                : null;

            if (!is_array($rules)) {
                $rules = array($rules);
            }

            $message = null;
            foreach ($rules as $rule) {
                $message = $rule->validateInputAndGetMessage($valueToValidate);
                if (!empty($message)) {
                    break 2;
                }
            }
        }

        return $message;
    }

    private function _prepareInput($input) {
        return is_object($input) 
            ? get_object_vars($input) 
            : $input;
    }
}