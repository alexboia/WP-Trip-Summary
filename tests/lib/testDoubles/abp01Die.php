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

class Abp01DieState {
	private static $_lastDieCall = null;

	public static function reportDieCalled(array $args) {
		self::$_lastDieCall = $args;
	}

	public static function hasDieBeenCalled() {
		return self::$_lastDieCall !== null;
	}

	public static function hasDieBeenCalledWithArgs() {
		$args = func_get_args();
		$hasBeenCalledWithArgs = false;

		if (self::$_lastDieCall !== null) {
			$countExpectedArgs = count(self::$_lastDieCall);
			if ($countExpectedArgs == count($args)) {
				$hasBeenCalledWithArgs = true;
				for ($iArg = 0; $iArg < $countExpectedArgs; $iArg ++) {
					if (self::$_lastDieCall[$iArg] != $args[$iArg]) {
						$hasBeenCalledWithArgs = false;
						break;
					}
				}
			}
		}

		return $hasBeenCalledWithArgs;
	}

	public static function resetDieCall() {
		self::$_lastDieCall = null;
	}
}

function abp01_die() {
	Abp01DieState::reportDieCalled(func_get_args());
}