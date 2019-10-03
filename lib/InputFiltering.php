<?php
/**
 * Copyright (c) 2014-2019 Alexandru Boia
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

class Abp01_InputFiltering {
	public static function filterSingleValue($input, $asType) {
		if (get_magic_quotes_gpc()) {
			$input = stripslashes($input);
		}

		if (!is_numeric($input)) {
			$input = sanitize_text_field($input);
		}
		settype($input, $asType);

		if (is_numeric($input)) {
			$minVal = func_num_args() >= 3 ? func_get_arg(2) : -INF;
			$maxVal = func_num_args() == 4 ? func_get_arg(3) : INF;

			//we need to cast min and max limits to the given type as well
			//otherwise, we'll get another type after the min/max clipping
			//however, we only cast finite values

			if (!is_infinite($minVal)) {
				settype($minVal, $asType);
			}
			if (!is_infinite($maxVal)) {
				settype($maxVal, $asType);
			}

			$input = max($minVal, $input);
			$input = min($maxVal, $input);
		}
		return $input;
	}

	public static function filterValue($input, $asType) {
		$extraParams = array();
		$nArgs = func_num_args();
		$callback = array(__CLASS__, 'filterSingleValue');
		$callbackRecurse = array(__CLASS__, 'filterValue');

		for ($i = 2; $i < $nArgs; $i++) {
			$extraParams[] = func_get_arg($i);
		}

		if (is_array($input)) {
			$result = array();
			foreach ($input as $value) {
				$result[] = call_user_func_array($callbackRecurse, array_merge(array($value, $asType), $extraParams));
			}
			return $result;
		} else if (is_object($input)) {
			$props = get_object_vars($input);
			if (is_array($props)) {
				foreach ($props as $propName => $value) {
					$input->$propName = call_user_func_array($callbackRecurse, array_merge(array($value, $asType), $extraParams));
				}
			}
			return $input;
		} else {
			return call_user_func_array($callback, array_merge(array($input, $asType), $extraParams));
		}
	}

	/**
	 * Asserts that the given value is not empty and also runs the optional assertion given as the second optional parameter.
	 * If the check fails, the script execution halts.
	 * @param mixed $value The input value to check for
	 * @param callable $additionalValidator The custom assertion. Optiona. Defaults to null
	 * @return mixed The given input value
	 */
	private static function assertValueNotEmptyOrDie($value, $additionalValidator = null) {
		if (empty($value)) {
			die;
		}
		if (!empty($additionalValidator) && is_callable($additionalValidator) && !$additionalValidator($value)) {
			die;
		}
		return $value;
	}

	public static function getPOSTValueOrDie($key, $additionalValidator = null) {
		if (empty($key)) {
			throw new InvalidArgumentException();
		}
		$value = isset($_POST[$key]) ? $_POST[$key] : null;
		return self::assertValueNotEmptyOrDie($value, $additionalValidator);
	}

	public static function getGETvalueOrDie($key, $additionalValidator = null) {
		if (empty($key)) {
			throw new InvalidArgumentException();
		}
		$value = isset($_GET[$key]) ? $_GET[$key] : null;
		return self::assertValueNotEmptyOrDie($value, $additionalValidator);
	}
}