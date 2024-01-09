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

class MysqliDbTestWrapper extends MysqliDb {
	private static $_rawQueryFixtures = array();
	
	public static function setUpRawQueryFixtures(array $spec) {
		if (empty($spec['query']) || empty($spec['return'])) {
			return;
		}

		$bindParams = null;
		$query = self::_normalizeQuery(($spec['query']));

		if (!empty($spec['bindParams'])) {
			$bindParams = $spec['bindParams'];
		}

		self::$_rawQueryFixtures[] = array(
			'query' => $query,
			'bindParams' => self::_encodeVariable($bindParams),
			'return' => $spec['return']
		);
	}

	private static function _normalizeQuery($query) {
		$normalizedQuery = strtolower($query);
		return preg_replace('/\s/', '', $normalizedQuery);
	}

	private static function _encodeVariable($var) {
		return sha1(strtolower(serialize($var)));
	}

	public static function resetRawQueryFixtures() {
		self::$_rawQueryFixtures = array();
	}

	public function rawQuery($query, $bindParams = null) {
		$fixture = self::_findRawQueryReturn($query, $bindParams);
		if ($fixture['found']) {
			return self::_processReturnValue($fixture['return']);
		}

		return parent::rawQuery($query, $bindParams);
	}

	private static function _findRawQueryReturn($query, $bindParams) {
		$found = false;
		$return = null;
		$searchQuery = self::_normalizeQuery($query);
		$searchBindParams = self::_encodeVariable($bindParams);

		foreach (self::$_rawQueryFixtures as $f) {
			if ($f['query'] === $searchQuery 
				&& $f['bindParams'] === $searchBindParams) {
				$found = true;
				$return = $f['return'];
				break;
			}
		}

		return array(
			'found' => $found,
			'return' => $return
		);
	}

	private static function _processReturnValue($return) {
		if (is_a($return, Exception::class)) {
			throw $return;
		}

		return $return;
	}
}