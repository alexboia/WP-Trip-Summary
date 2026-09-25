<?php
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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

declare(strict_types=1);

namespace WpTripSummary\Io {
	if (!defined('ABP01_LOADED')) {
		exit;
	}

	class NginxAccessDirectives {
		private static function _normalizePath(string $urlPath): string {
			// Trim pasted spaces before decoding so encoded trailing spaces survive.
			// URL paths use percent encoding; a literal plus must remain a plus.
			$normalizedPath = rawurldecode(trim($urlPath, ' '));

			if ($normalizedPath === '' || $normalizedPath[0] !== '/') {
				throw new \InvalidArgumentException('An absolute, non-empty URL path is required.');
			}

			if (preg_match('/[\x00-\x1F\x7F]/', $normalizedPath)) {
				throw new \InvalidArgumentException('The URL path must not contain control characters.');
			}

			$normalizedPath = rtrim($normalizedPath, '/');
			if ($normalizedPath === '') {
				$normalizedPath = '/';
			}

			// Nginx matches normalized request URIs. Reject paths that would not match
			// after dot-segment resolution or the default merging of internal slashes.
			if (str_contains($normalizedPath, '//')
				|| preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $normalizedPath)) {
				throw new \InvalidArgumentException('The URL path must not contain dot segments or repeated internal slashes.');
			}

			$normalizedPath = strtr($normalizedPath, array(
				"\\" => "\\\\", // Nginx gets \\ for a literal backslash
				"'" => "\\'" // Nginx gets \' for a single quote
			));

			return $normalizedPath;
		}

		/**
		 * Generate rules for an absolute URL path, optionally including descendants.
		 *
		 * Percent encoding is decoded once. Trailing slashes and surrounding literal
		 * spaces are removed, except that the root path remains "/".
		 * Paths with control characters, dot segments or repeated internal slashes
		 * are rejected. Response codes outside 1..999 fall back to 403.
		 *
		 * @throws \InvalidArgumentException If the URL path is invalid.
		 */
		public static function generateDenyAccessRules(string $urlPath, int $httpCode = 403, bool $includeChildren = true): string {
			$parts = array();
			$normalizedPath = self::_normalizePath($urlPath);

			if ($httpCode <= 0 || $httpCode > 999) {
				$httpCode = 403;
			}

			$parts[] = '# Forbid access to the exact path (without a trailing slash, except for root).';
			$parts[] = "location = '$normalizedPath' {";
			$parts[] = "\treturn $httpCode;";
			$parts[] = "}";

			if ($includeChildren) {
				$childrenPath = $normalizedPath === '/' ? '/' : $normalizedPath . '/';

				$parts[] = '# Forbid access to the directory with a trailing slash and all files and subdirectories beneath it.';
				$parts[] = "location ^~ '$childrenPath' {";
				$parts[] = "\treturn $httpCode;";
				$parts[] = "}";
			}

			return join("\n", $parts);
		}
	}
}
