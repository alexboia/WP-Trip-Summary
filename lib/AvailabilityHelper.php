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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_AvailabilityHelper {
	public const string POST_TYPE_POST = 'post';

	public const string POST_TYPE_PAGE = 'page';

	public static function isEditorAvailableForPostType(string $postType): bool {
		return in_array($postType, self::getTripSummaryAvailableForPostTypes());
	}

	/**
	 * @return string[]
	 */
	public static function getTripSummaryAvailableForPostTypes(): array {
		$postTypes = array(
			self::POST_TYPE_POST, 
			self::POST_TYPE_PAGE
		);

		/**
		 * Filters the post types for which trip summary is available.
		 * Initial value is [ 'post', 'page' ].
		 * Can be empty, but must be array. If non-array returned, initial value will be used.
		 * 
		 * @since 0.3.2
		 * @category Trip Summary Management
		 * 
		 * @param string[] $postTypes The post types for which trip summary is available
		 */
		$filteredPostTypes = apply_filters('abp01_trip_summary_available_for_post_types', 
			$postTypes);

		if (!is_array($filteredPostTypes)) {
			$filteredPostTypes = $postTypes;
		}

		return $filteredPostTypes;
	}
}